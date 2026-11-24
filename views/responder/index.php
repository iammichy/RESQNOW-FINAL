<?php
$missions    = $missions ?? [];
$actionCodes = $actionCodes ?? [];
$flash       = $flash ?? null;

$triageBadge = fn(string $l) => match($l) {
    'critical'             => '<span class="badge bg-danger">Critical</span>',
    'high'                 => '<span class="badge bg-orange">High</span>',
    'moderate'             => '<span class="badge bg-amber text-dark">Moderate</span>',
    'low'                  => '<span class="badge bg-success">Low</span>',
    'pending_reassessment' => '<span class="badge bg-dark">Pending</span>',
    default                => '<span class="badge bg-secondary">' . e($l) . '</span>',
};

$statusBadge = fn(string $s) => match($s) {
    'assigned'     => '<span class="badge bg-blue">Assigned</span>',
    'acknowledged' => '<span class="badge bg-cyan">Acknowledged</span>',
    'on_scene'     => '<span class="badge bg-teal">On Scene</span>',
    'in_progress'  => '<span class="badge bg-amber text-dark">In Progress</span>',
    default        => '<span class="badge bg-secondary">' . e(ucwords(str_replace('_',' ',$s))) . '</span>',
};

$hazardIcon = fn(string $h) => match($h) {
    'fire'      => '🔥', 'flood'     => '🌊', 'earthquake' => '🌐',
    'medical'   => '⚕️', 'accident'  => '🚗', 'evacuation' => '👥',
    'security'  => '🛡️', default     => '⚠️',
};
?>

<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">My Missions</h2>
        <div class="text-muted mt-1"><?= count($missions) ?> active assignment<?= count($missions) !== 1 ? 's' : '' ?></div>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'info' ? 'info' : 'danger') ?> alert-dismissible">
      <?= e($flash['message']) ?> <a href="#" class="btn-close" data-bs-dismiss="alert"></a>
    </div>
  <?php endif; ?>

  <?php if (empty($missions)): ?>
    <div class="card">
      <div class="card-body text-center py-6">
        <div style="font-size:3rem;">✅</div>
        <h3 class="mt-3">No active missions</h3>
        <p class="text-muted">You have no active assignments right now.</p>
      </div>
    </div>
  <?php else: ?>
    <?php foreach ($missions as $m): ?>
      <?php
        $aid = (int) $m['id'];
        $incStatus = $m['incident_status'] ?? $m['status'];
        $isAcked = !empty($m['acknowledged_at']);
      ?>
      <div class="card mb-4">
        <div class="card-header">
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <span style="font-size:1.4rem;"><?= $hazardIcon($m['hazard_type']) ?></span>
            <strong><?= e($m['code']) ?></strong>
            <?= $triageBadge($m['triage_level']) ?>
            <?= $statusBadge($incStatus) ?>
            <span class="badge bg-secondary ms-1"><?= e($m['team_name']) ?></span>
            <span class="text-muted small ms-auto">Assigned <?= e($m['assigned_at']) ?></span>
          </div>
        </div>
        <div class="card-body">

          <div class="row mb-3">
            <div class="col-md-6">
              <div class="text-muted small">Hazard</div>
              <div class="fw-semibold text-capitalize"><?= e(str_replace('_',' ',$m['hazard_type'])) ?></div>
            </div>
            <div class="col-md-6">
              <div class="text-muted small">Location</div>
              <div><?= e($m['address'] ?? '—') ?></div>
            </div>
            <?php if (!empty($m['description'])): ?>
              <div class="col-12 mt-2">
                <div class="text-muted small">Details</div>
                <div><?= nl2br(e($m['description'])) ?></div>
              </div>
            <?php endif; ?>
          </div>

          <!-- Action Buttons -->
          <div class="row g-2">

            <!-- Acknowledge -->
            <?php if (!$isAcked && $incStatus === 'assigned'): ?>
              <div class="col-auto">
                <form method="post" action="<?= base_url('responder/acknowledge/' . $aid) ?>">
                  <?= csrf_field() ?>
                  <button class="btn btn-success">✔ Acknowledge</button>
                </form>
              </div>
            <?php endif; ?>

            <!-- En Route -->
            <?php if ($isAcked && in_array($incStatus, ['acknowledged', 'on_scene', 'in_progress'], true)): ?>
              <div class="col-auto">
                <form method="post" action="<?= base_url('responder/en-route/' . $aid) ?>">
                  <?= csrf_field() ?>
                  <button class="btn btn-info text-white">🚗 En Route</button>
                </form>
              </div>
            <?php endif; ?>

            <!-- On Scene -->
            <?php if (in_array($incStatus, ['assigned','acknowledged'], true)): ?>
              <div class="col-auto">
                <form method="post" action="<?= base_url('responder/on-scene/' . $aid) ?>">
                  <?= csrf_field() ?>
                  <button class="btn btn-teal text-white">📍 On Scene</button>
                </form>
              </div>
            <?php endif; ?>

            <!-- In Progress -->
            <?php if (in_array($incStatus, ['assigned','acknowledged','on_scene','in_progress'], true)): ?>
              <div class="col-auto">
                <button class="btn btn-warning text-dark" data-bs-toggle="modal"
                        data-bs-target="#inProgressModal<?= $aid ?>">
                  ⚙ In Progress
                </button>
              </div>
            <?php endif; ?>

            <!-- Request Support -->
            <?php if (in_array($incStatus, ['assigned','acknowledged','on_scene','in_progress'], true)): ?>
              <div class="col-auto">
                <button class="btn btn-danger" data-bs-toggle="modal"
                        data-bs-target="#supportModal<?= $aid ?>">
                  🆘 Request Support
                </button>
              </div>
            <?php endif; ?>

            <!-- Mark Resolved -->
            <?php if (in_array($incStatus, ['on_scene','in_progress','acknowledged'], true)): ?>
              <div class="col-auto">
                <button class="btn btn-outline-success" data-bs-toggle="modal"
                        data-bs-target="#resolveModal<?= $aid ?>">
                  ✅ Mark Resolved
                </button>
              </div>
            <?php endif; ?>

            <!-- View Report -->
            <div class="col-auto ms-auto">
              <a href="<?= base_url('reports/' . e($m['code'])) ?>" class="btn btn-outline-secondary" target="_blank">
                View Report ↗
              </a>
            </div>

          </div>
        </div>
      </div>

      <!-- In Progress Modal -->
      <div class="modal fade" id="inProgressModal<?= $aid ?>" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <form method="post" action="<?= base_url('responder/in-progress/' . $aid) ?>">
              <?= csrf_field() ?>
              <div class="modal-header">
                <h5 class="modal-title">In Progress Update — <?= e($m['code']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <div class="mb-3">
                  <label class="form-label">Action <span class="text-danger">*</span></label>
                  <select name="action_code" class="form-select" required>
                    <option value="">— Select action —</option>
                    <?php foreach ($actionCodes as $code => $label): ?>
                      <option value="<?= e($code) ?>"><?= e($label) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label">Remarks (optional)</label>
                  <textarea name="remarks" class="form-control" rows="2" placeholder="Additional details..."></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-warning">Submit Update</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Support Request Modal -->
      <div class="modal fade" id="supportModal<?= $aid ?>" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <form method="post" action="<?= base_url('responder/request-support/' . $aid) ?>">
              <?= csrf_field() ?>
              <div class="modal-header">
                <h5 class="modal-title">🆘 Request Support — <?= e($m['code']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <div class="alert alert-warning">This will alert all admin and personnel immediately.</div>
                <div class="mb-3">
                  <label class="form-label">Support Type <span class="text-danger">*</span></label>
                  <select name="support_type" class="form-select" required>
                    <option value="">— Select —</option>
                    <option value="backup">Backup personnel</option>
                    <option value="ambulance">Ambulance</option>
                    <option value="fire_truck">Fire truck</option>
                    <option value="additional_personnel">Additional personnel</option>
                  </select>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Send Request</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Resolve Modal -->
      <div class="modal fade" id="resolveModal<?= $aid ?>" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <form method="post" action="<?= base_url('responder/resolve/' . $aid) ?>">
              <?= csrf_field() ?>
              <div class="modal-header">
                <h5 class="modal-title">✅ Mark Resolved — <?= e($m['code']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <div class="mb-3">
                  <label class="form-label">Resolution Summary <span class="text-danger">*</span></label>
                  <textarea name="resolution_summary" class="form-control" rows="4" required
                            placeholder="Describe how the incident was resolved..."></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success">Confirm Resolved</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    <?php endforeach; ?>
  <?php endif; ?>

</div>
