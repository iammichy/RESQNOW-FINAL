<?php
$incident            = $incident ?? [];
$answers             = $answers ?? [];
$reporter            = $reporter ?? null;
$statusHistory       = $statusHistory ?? [];
$verificationRecords = $verificationRecords ?? [];
$responseUpdates     = $responseUpdates ?? [];
$assignmentHistory   = $assignmentHistory ?? [];
$activeAssignment    = $activeAssignment ?? null;
$availableTeams      = $availableTeams ?? [];
$canVerify           = $canVerify ?? false;
$canAssign           = $canAssign ?? false;
$flash               = $flash ?? null;

$triageBadge = function (string $level): string {
    $map = [
        'critical'             => ['bg-red-lt text-red border-red-subtle', 'Critical'],
        'high'                 => ['bg-orange-lt text-orange border-orange-subtle', 'High'],
        'moderate'             => ['bg-yellow-lt text-yellow border-yellow-subtle', 'Moderate'],
        'low'                  => ['bg-green-lt text-green border-green-subtle', 'Low'],
        'pending_reassessment' => ['bg-slate-lt text-slate border-slate-subtle', 'Pending'],
    ];
    [$cls, $label] = $map[$level] ?? ['bg-light text-muted', ucfirst($level)];
    return '<span class="badge ' . $cls . ' border fw-bold px-2 py-1" style="font-size:0.65rem; border-radius: 6px;">' . e($label) . '</span>';
};

$statusBadge = function (string $status): string {
    $map = [
        'submitted'    => ['bg-orange-lt text-orange', 'For Verification'],
        'verified'     => ['bg-blue-lt text-blue', 'Verified'],
        'rejected'     => ['bg-red-lt text-red', 'Rejected'],
        'duplicate'    => ['bg-light text-muted', 'Duplicate'],
        'triaged'      => ['bg-purple-lt text-purple', 'Triaged'],
        'assigned'     => ['bg-indigo-lt text-indigo', 'Assigned'],
        'acknowledged' => ['bg-azure-lt text-azure', 'Acknowledged'],
        'on_scene'     => ['bg-teal-lt text-teal', 'On Scene'],
        'in_progress'  => ['bg-amber-lt text-amber', 'In Progress'],
        'resolved'     => ['bg-green-lt text-green', 'Resolved'],
        'closed'       => ['bg-dark-lt text-dark', 'Closed'],
    ];
    [$cls, $label] = $map[$status] ?? ['bg-light text-muted', ucwords(str_replace('_', ' ', $status))];
    return '<span class="badge ' . $cls . ' fw-bold px-2 py-1" style="font-size:0.65rem; border-radius: 4px;">' . e($label) . '</span>';
};

$hazardIcon = function (string $type): string {
    $icons = [
        'fire'       => '<div class="avatar avatar-sm rounded bg-red-lt fw-bold text-red">F</div>',
        'flood'      => '<div class="avatar avatar-sm rounded bg-azure-lt fw-bold text-azure">W</div>',
        'earthquake' => '<div class="avatar avatar-sm rounded bg-warning-lt fw-bold text-warning">E</div>',
        'medical'    => '<div class="avatar avatar-sm rounded bg-red-lt fw-bold text-red">M</div>',
        'accident'   => '<div class="avatar avatar-sm rounded bg-orange-lt fw-bold text-orange">A</div>',
        'evacuation' => '<div class="avatar avatar-sm rounded bg-blue-lt fw-bold text-blue">V</div>',
        'security'   => '<div class="avatar avatar-sm rounded bg-indigo-lt fw-bold text-indigo">S</div>',
        'other'      => '<div class="avatar avatar-sm rounded bg-muted-lt fw-bold text-muted">O</div>',
    ];
    return $icons[$type] ?? $icons['other'];
};

$rejectionReason = null;
foreach (array_reverse($verificationRecords) as $v) {
    if (($v['action'] ?? '') === 'rejected') {
        $rejectionReason = $v['reason'];
        break;
    }
}

$hasMap = !empty($incident['location_lat']) && !empty($incident['location_lng']);
$isPendingReassessment = ($incident['triage_level'] ?? '') === 'pending_reassessment';
$isSubmitted = ($incident['status'] ?? '') === 'submitted';
$isEscalated = !empty($incident['is_escalated']);
$isAssignable = in_array($incident['status'] ?? '', ['verified', 'triaged', 'submitted'], true);
$isAssigned   = in_array($incident['status'] ?? '', ['assigned','acknowledged','on_scene','in_progress','resolved','closed'], true);
?>

<style>
    .info-label { font-size: 0.65rem; font-weight: 800; text-transform: uppercase; color: #64748b; letter-spacing: 0.05em; margin-bottom: 0.35rem; }
    .info-value { font-size: 0.875rem; font-weight: 700; color: #1e293b; }
    .premium-card { border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); transition: all 0.2s; background: #fff; }
    .triage-score-circle { width: 84px; height: 84px; border-radius: 50%; border: 6px solid #fee2e2; display: flex; align-items: center; justify-content: center; margin: 0 auto; background: #fff; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05); }
    .triage-score-value { font-size: 2.25rem; font-weight: 900; color: #dc2626; line-height: 1; letter-spacing: -0.02em; }
    .timeline-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: #f1f5f9; font-size: 1.1rem; }
    #detail-map { height: 380px; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 12px -2px rgba(0,0,0,0.05); }
</style>

<div class="page-header d-print-none mb-4">
  <div class="container-fluid px-lg-4">
    <div class="row g-2 align-items-center">
      <div class="col">
        <div class="mb-1">
          <a href="<?= base_url('reports') ?>" class="text-muted small text-decoration-none fw-bold text-uppercase tracking-wider">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="m15 18-6-6 6-6"/></svg> Back to Reports
          </a>
        </div>
        <div class="d-flex align-items-center gap-3">
          <?= $hazardIcon($incident['hazard_type'] ?? '') ?>
          <div>
            <h2 class="page-title fs-2 fw-bold text-slate mb-0">Report <?= e($incident['code'] ?? '') ?></h2>
            <div class="text-muted small">
                Submitted <?= date('M d, Y h:i A', strtotime($incident['created_at'])) ?>
                <?php if ($isEscalated): ?>
                    <span class="badge bg-red-lt text-red border border-red-subtle ms-2 fw-bold" style="font-size:0.6rem;">ESCALATED</span>
                <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <div class="col-auto ms-auto d-flex align-items-center gap-3">
        <?= $statusBadge($incident['status'] ?? 'submitted') ?>
      </div>
    </div>
  </div>
</div>

<div class="container-fluid px-lg-4">
  <?php if ($flash): ?>
    <div class="alert alert-<?= e($flash['type'] === 'success' ? 'success' : ($flash['type'] === 'info' ? 'info' : 'danger')) ?> alert-dismissible shadow-sm border-0 mb-4" role="alert">
      <?= e($flash['message']) ?>
      <a href="#" class="btn-close" data-bs-dismiss="alert"></a>
    </div>
  <?php endif; ?>

  <div class="row g-4">
    <!-- LEFT COLUMN -->
    <div class="col-lg-8">

      <!-- Reporter Info -->
      <div class="card premium-card mb-4 border-0">
        <div class="card-header bg-transparent py-3 border-bottom-wide"><h3 class="card-title fw-bold text-slate">Reporter Information</h3></div>
        <div class="card-body">
          <?php if ($reporter): ?>
            <div class="row g-3">
              <div class="col-md-3">
                <div class="info-label">Name</div>
                <div class="info-value"><?= e($reporter['name']) ?></div>
              </div>
              <div class="col-md-3">
                <div class="info-label">Role</div>
                <div class="info-value text-capitalize"><?= e($reporter['role']) ?></div>
              </div>
              <div class="col-md-3">
                <div class="info-label">Email</div>
                <div class="info-value text-truncate"><?= e($reporter['email'] ?? '—') ?></div>
              </div>
              <div class="col-md-3">
                <div class="info-label">Phone</div>
                <div class="info-value"><?= e($reporter['phone'] ?? '—') ?></div>
              </div>
            </div>
          <?php else: ?>
            <div class="text-muted text-center py-2">Anonymous report.</div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Incident Details -->
      <div class="card premium-card mb-4 border-0">
        <div class="card-header bg-transparent py-3 border-bottom-wide"><h3 class="card-title fw-bold text-slate">Incident Details</h3></div>
        <div class="card-body">
          <div class="row g-3 mb-4">
            <div class="col-md-3">
              <div class="info-label">Hazard Type</div>
              <div class="info-value text-capitalize"><?= e($incident['hazard_type'] ?? '') ?></div>
            </div>
            <div class="col-md-3">
              <div class="info-label">Emergency</div>
              <div class="info-value"><?= !empty($incident['is_emergency']) ? 'Yes' : 'No' ?></div>
            </div>
            <div class="col-md-3">
              <div class="info-label">Source</div>
              <div class="info-value text-capitalize"><?= e(str_replace('_', ' ', $incident['source'] ?? '')) ?></div>
            </div>
            <div class="col-md-3">
              <div class="info-label">Assigned Team</div>
              <div class="info-value"><?= e($incident['assigned_team'] ?? '—') ?></div>
            </div>
          </div>
          <?php if (!empty($incident['description'])): ?>
            <div class="info-label">Description</div>
            <div class="bg-light p-3 rounded-3 text-slate small mb-3"><?= nl2br(e($incident['description'])) ?></div>
          <?php endif; ?>
          <?php if ($rejectionReason !== null): ?>
            <div class="alert alert-danger border-0 shadow-sm mb-0">
              <div class="fw-bold small text-uppercase mb-1">Rejection Reason</div>
              <div class="small"><?= e($rejectionReason) ?></div>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Location -->
      <div class="card premium-card mb-4 border-0">
        <div class="card-header bg-transparent py-3 border-bottom-wide"><h3 class="card-title fw-bold text-slate">Incident Location</h3></div>
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <div class="info-label">Reported Address</div>
                <div class="info-value"><?= e($incident['address'] ?? '—') ?></div>
            </div>
            <?php 
            $distWarning = false;
            if (!empty($reporter['home_lat']) && !empty($incident['location_lat'])) {
                $lat1 = (float)$reporter['home_lat']; $lng1 = (float)$reporter['home_lng'];
                $lat2 = (float)$incident['location_lat']; $lng2 = (float)$incident['location_lng'];
                $dist = sqrt(pow($lat1-$lat2, 2) + pow($lng1-$lng2, 2)) * 111.32;
                if ($dist > 0.5) $distWarning = true;
            }
            ?>
            <?php if ($distWarning): ?>
              <span class="badge bg-warning-lt text-warning border border-warning-subtle fw-bold px-2 py-1" style="font-size:0.65rem;">
                ⚠️ PIN MISMATCH (>500M)
              </span>
            <?php endif; ?>
          </div>
          <?php if ($hasMap): ?>
            <div id="detail-map"></div>
            <!-- Responder live tracking info bar -->
            <div id="responder-info-bar"
                 style="margin-top:8px;padding:8px 12px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;font-size:.78rem;color:#1e40af;min-height:32px;display:flex;align-items:center;gap:6px;">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
              Checking responder location…
            </div>
            <div class="mt-3 d-flex gap-4">
                <div class="d-flex align-items-center gap-2 small fw-bold text-muted">
                    <span style="width:10px;height:10px;background:#dc2626;border-radius:50%;"></span> INCIDENT PIN
                </div>
                <?php if (!empty($reporter['home_lat'])): ?>
                <div class="d-flex align-items-center gap-2 small fw-bold text-muted">
                    <span style="width:10px;height:10px;background:#2563eb;border-radius:50%;"></span> RESIDENT HOME
                </div>
                <?php endif; ?>
            </div>
          <?php else: ?>
            <div class="text-center text-muted py-5 bg-light rounded-3">No GPS coordinates available.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- RIGHT COLUMN -->
    <div class="col-lg-4">

      <!-- Triage Breakdown -->
      <div class="card premium-card mb-4 border-0 overflow-hidden">
        <div class="card-header py-3 bg-red text-white">
          <h3 class="card-title fw-bold text-white mb-0">Triage Breakdown</h3>
        </div>
        <div class="card-body text-center py-4">
          <div class="triage-score-circle mb-3">
            <div class="triage-score-value"><?= (int) ($incident['triage_score'] ?? 0) ?></div>
          </div>
          <div class="info-label mb-2">Total Priority Score</div>
          <div class="mb-3"><?= $triageBadge($incident['triage_level'] ?? 'pending_reassessment') ?></div>
          <p class="text-muted small mb-0 px-3">
            <strong>Score formula:</strong> &Sigma;(answer points &times; question weight) + vulnerability bonus<br>
            Max score: 16 points &middot; Capped if exceeded.<br>
            <strong>Tiers:</strong> Critical &ge; 13 &middot; High 9&ndash;12 &middot; Moderate 5&ndash;8 &middot; Low 1&ndash;4.<br>
            <span class="text-muted">If &ge;50% answers are "Unsure", level becomes <strong>Needs Reassessment</strong> regardless of score.</span>
          </p>
        </div>

        <?php if (!empty($incident['unsure_ratio']) && (float) $incident['unsure_ratio'] >= 0.5): ?>
          <div class="alert alert-warning mx-3 mb-3 border-0 small">
            <strong>⚠️ High Uncertainty:</strong> <?= round((float) $incident['unsure_ratio'] * 100, 0) ?>% of answers were "Unsure."
            <br><br>
            "Unsure" does <strong>not</strong> mean no risk — it means the reporter could not determine the severity.
            The actual risk may be <strong>higher</strong> than the score suggests. Personnel must conduct a reassessment to determine the true priority level.
          </div>
        <?php endif; ?>

        <div class="list-group list-group-flush border-top">
          <?php foreach ($answers as $a):
            $isVuln = ($a['question_key'] ?? '') === 'vulnerability';
            $isUnsure = !empty($a['is_unsure']);
            $weighted = (int)$a['points_scored'] * (int)$a['weight'];
          ?>
            <div class="list-group-item px-4 py-3 <?= $isVuln ? 'bg-amber-lt' : '' ?>">
              <div class="d-flex justify-content-between align-items-center">
                <div class="flex-grow-1 pe-3">
                  <div class="info-label" style="font-size:0.6rem;"><?= e($a['question_text']) ?></div>
                  <div class="fw-bold text-slate small">
                    <?= e($a['answer_label']) ?>
                    <?php if ($isUnsure): ?>
                      <span class="badge bg-slate-lt text-slate ms-1 fw-bold" style="font-size:0.55rem;">UNSURE</span>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="text-end">
                  <div class="fw-bold text-red" style="font-size:0.9rem;"><?= (int) $a['points_scored'] ?></div>
                  <div class="text-muted x-small">pts &times; <?= (int) $a['weight'] ?> wt = <strong><?= $weighted ?></strong></div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
          <div class="list-group-item px-4 py-3 bg-light">
            <div class="d-flex justify-content-between align-items-center">
              <div class="info-label" style="font-size:0.6rem;margin-bottom:0;">Total Score</div>
              <div class="fw-bold text-red" style="font-size:1.1rem;"><?= (int) ($incident['triage_score'] ?? 0) ?>/16</div>
            </div>
          </div>
        </div>
      </div>

<?php
$currentUser = auth();
$isAdmin = ($currentUser['role'] ?? '') === 'admin';
$isAutoVerified = ($incident['source'] ?? '') === 'admin_manual';
?>

      <!-- Verification Actions -->
      <?php if ($canVerify): ?>
      <div class="card premium-card mb-4 border-0">
        <div class="card-header bg-transparent py-3"><h3 class="card-title fw-bold text-slate">Verification Actions</h3></div>
        <div class="card-body pt-0">

          <?php if ($isAutoVerified): ?>
            <!-- Admin manual report — auto-verified -->
            <div class="alert bg-blue-lt text-blue border-0 small mb-0 d-flex align-items-center gap-2">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>
              <div>
                <strong>Auto-verified.</strong> This report was created by an admin and does not require verification.
              </div>
            </div>

          <?php elseif (!$isSubmitted): ?>
            <!-- Already verified — simplified status -->
            <div class="alert bg-slate-lt text-slate border-0 small mb-3">
              Report status: <strong><?= e(ucfirst($incident['status'])) ?></strong>.
              No further verification actions needed.
            </div>
            <form method="post" action="<?= base_url('reports/' . e($incident['code']) . '/reopen') ?>">
              <?= csrf_field() ?>
              <div class="mb-2">
                <label class="info-label" style="font-size:0.6rem;">Reason for reopening (required)</label>
                <input type="text" name="reason" class="form-control form-control-sm shadow-none" placeholder="e.g. New information received, incorrect verification…" required>
              </div>
              <button type="submit" class="btn btn-sm btn-white border shadow-none w-100 fw-bold">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline;vertical-align:middle;margin-right:4px;"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                Reopen for Verification
              </button>
            </form>

          <?php else: ?>
            <!-- Submitted — full verification actions -->
            <?php if ($isPendingReassessment): ?>
              <div class="alert alert-warning border-0 small mb-3">
                <strong>Needs Reassessment.</strong> The reporter selected "Unsure" for most questions.
                This does <strong>not</strong> mean there is no risk — it means the reporter could not assess the severity.
                A personnel must verify the situation and provide actual answers.
              </div>
            <?php endif; ?>

            <div class="d-grid gap-2">
              <form method="post" action="<?= base_url('reports/' . e($incident['code']) . '/verify') ?>">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-success w-100 fw-bold py-2" <?= $isPendingReassessment ? 'disabled' : '' ?>>
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline;vertical-align:middle;margin-right:4px;"><polyline points="20 6 9 17 4 12"/></svg>
                  Verify Report
                </button>
              </form>
              <button type="button" class="btn btn-warning w-100 fw-bold py-2" data-bs-toggle="modal" data-bs-target="#reassessModal">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline;vertical-align:middle;margin-right:4px;"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                <?= $isPendingReassessment ? 'Reassess Now' : 'Situation Verification' ?>
              </button>
              <div class="row g-2">
                <div class="col-6">
                  <button type="button" class="btn btn-white border w-100 fw-bold small py-2" data-bs-toggle="modal" data-bs-target="#rejectModal">Reject</button>
                </div>
                <div class="col-6">
                  <button type="button" class="btn btn-white border w-100 fw-bold small py-2" data-bs-toggle="modal" data-bs-target="#duplicateModal">Duplicate</button>
                </div>
              </div>
              <?php if (!$isAdmin): ?>
                <form method="post" action="<?= base_url('reports/' . e($incident['code']) . '/escalate') ?>">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-ghost-danger w-100 fw-bold py-2" <?= $isEscalated ? 'disabled' : '' ?>>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="display:inline;vertical-align:middle;margin-right:4px;"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/></svg>
                    <?= $isEscalated ? 'Already Escalated' : 'Escalate to Admin' ?>
                  </button>
                </form>
              <?php endif; ?>
            </div>
          <?php endif; ?>

        </div>
      </div>
      <?php endif; ?>

      <!-- Team Assignment -->
      <?php if ($canAssign): ?>
      <div class="card premium-card mb-4 border-0">
        <div class="card-header bg-transparent py-3"><h3 class="card-title fw-bold text-slate">Mission Deployment</h3></div>
        <div class="card-body pt-0">
          <?php if ($activeAssignment): ?>
            <div class="bg-blue-lt p-3 rounded-3 mb-4">
              <div class="info-label text-blue">Deployed Team</div>
              <div class="fw-bold text-blue mb-1"><?= e($activeAssignment['team_name']) ?></div>
              <div class="text-muted x-small">
                Since <?= date('M d, h:i A', strtotime($activeAssignment['assigned_at'])) ?>
                <?php if ($activeAssignment['acknowledged_at']): ?>
                  · Acked <?= date('h:i A', strtotime($activeAssignment['acknowledged_at'])) ?>
                <?php endif; ?>
              </div>
            </div>
          <?php endif; ?>

          <?php if ($isAssignable || $activeAssignment): ?>
            <button type="button" class="btn btn-primary w-100 fw-bold py-2" data-bs-toggle="modal" data-bs-target="#assignModal">
              <?= $activeAssignment ? 'Reassign Team' : 'Deploy Team' ?>
            </button>
          <?php else: ?>
            <div class="text-muted small text-center">Cannot deploy in current status.</div>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ── Complete Transaction Timeline ───────────────────────────────── -->
<?php if (!empty($responseUpdates) || !empty($statusHistory)): ?>
<div class="container-fluid px-lg-4 mb-4">
  <div class="card premium-card border-0">
    <div class="card-header bg-transparent py-3 d-flex align-items-center gap-2">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      <h3 class="card-title fw-bold text-slate mb-0">Complete Transaction Timeline</h3>
    </div>
    <div class="card-body py-0">
      <div class="list-group list-group-flush">
        <?php
        // Merge status history + response updates into one timeline, sorted by timestamp
        $timeline = [];
        foreach ($statusHistory as $h) {
            $timeline[] = [
                'ts'      => strtotime($h['created_at'] ?? ''),
                'type'    => 'status',
                'label'   => ($h['from_status'] ? ucwords(str_replace('_',' ',$h['from_status'])).' → ' : '') . ucwords(str_replace('_',' ',$h['to_status'])),
                'note'    => $h['notes'] ?? null,
                'actor'   => $h['changed_by_name'] ?? null,
                'created' => $h['created_at'],
            ];
        }
        foreach ($responseUpdates as $u) {
            $actionMap = [
                'acknowledged'       => 'Acknowledged mission',
                'en_route'           => 'En route to scene',
                'arrived_on_scene'   => 'Arrived on scene',
                'first_aid'          => 'Administering first aid',
                'extinguishing'      => 'Extinguishing fire',
                'evacuating'         => 'Evacuating residents',
                'resolved'           => 'Mission resolved',
                'support_requested'  => 'Support requested',
                'incident_resolved'  => 'Incident resolved',
            ];
            $label = $actionMap[$u['action_code'] ?? ''] ?? ucwords(str_replace('_',' ',$u['action_code'] ?? $u['update_type'] ?? ''));
            $timeline[] = [
                'ts'       => strtotime($u['created_at'] ?? ''),
                'type'     => $u['support_requested'] ? 'support' : 'update',
                'label'    => $label,
                'note'     => $u['message'] ?? null,
                'actor'    => $u['responder_name'] ?? ($u['team_name'] ?? null),
                'created'  => $u['created_at'],
                'support_type' => $u['support_type'] ?? null,
            ];
        }
        // Sort by timestamp ascending
        usort($timeline, fn($a, $b) => $a['ts'] <=> $b['ts']);

        $typeIcons = [
            'status'  => ['#e0f2fe','#0284c7','<path d="M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/>'],
            'update'  => ['#f0fdf4','#16a34a','<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>'],
            'support' => ['#fef2f2','#dc2626','<path d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>'],
        ];
        ?>
        <?php foreach ($timeline as $entry):
            [$iconBg, $iconColor, $iconPath] = $typeIcons[$entry['type']] ?? $typeIcons['update'];
        ?>
          <div class="list-group-item px-4 py-3">
            <div class="d-flex align-items-start gap-3">
              <div style="width:30px;height:30px;border-radius:8px;background:<?= $iconBg ?>;color:<?= $iconColor ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><?= $iconPath ?></svg>
              </div>
              <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                  <span class="fw-bold small text-slate"><?= e($entry['label']) ?></span>
                  <?php if ($entry['type'] === 'support'): ?>
                    <span class="badge bg-red-lt text-red border-red-subtle fw-bold" style="font-size:.6rem;"><?= e(str_replace('_',' ', $entry['support_type'] ?? 'Support')) ?></span>
                  <?php endif; ?>
                </div>
                <?php if (!empty($entry['note'])): ?>
                  <div class="text-muted small mb-1"><?= e($entry['note']) ?></div>
                <?php endif; ?>
                <div class="d-flex align-items-center gap-2">
                  <?php if (!empty($entry['actor'])): ?>
                    <span style="font-size:.72rem;color:#64748b;"><?= e($entry['actor']) ?></span>
                    <span style="color:#cbd5e1;">·</span>
                  <?php endif; ?>
                  <span style="font-size:.72rem;color:#94a3b8;"><?= $entry['created'] ? date('M j, Y g:i A', $entry['ts']) : '—' ?></span>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
        <?php if (empty($timeline)): ?>
          <div class="list-group-item px-4 py-3 text-muted small text-center">No transaction history yet.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
<!-- (Keeping original modal logic to ensure no functionality is lost) -->

<!-- Reassess Modal (Situation Verification Form) -->
<div class="modal fade" id="reassessModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow-lg">
      <form method="post" action="<?= base_url('reports/' . e($incident['code']) . '/reassess') ?>">
        <?= csrf_field() ?>
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold fs-3">Situation Verification Form</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted small mb-4">Verify the incident situation by answering the questions below. The system will automatically re-calculate the priority level based on your verification.</p>
          
          <div class="row g-4">
            <?php 
            $currentAnswers = [];
            foreach ($answers as $a) {
                $currentAnswers[$a['question_key']] = $a['answer_label'];
            }
            
            $allTriageQuestions = array_merge($hazardQuestions ?? [], [$vulnQuestion]);
            foreach ($allTriageQuestions as $q): 
                $selected = $currentAnswers[$q['key']] ?? '';
            ?>
              <div class="col-md-6">
                <label class="form-label fw-bold small text-muted text-uppercase"><?= e($q['text']) ?></label>
                <select name="answers[<?= e($q['key']) ?>]" class="form-select filter-input shadow-none" required>
                  <option value="">— Select Verification —</option>
                  <?php foreach ($q['options'] as $opt): ?>
                    <option value="<?= e($opt['label']) ?>" <?= $selected === $opt['label'] ? 'selected' : '' ?>>
                      <?= e($opt['label']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="mt-4 pt-3 border-top">
            <label class="form-label fw-bold small text-muted text-uppercase">Verification Notes / Reason</label>
            <textarea name="reason" class="form-control filter-input shadow-none" rows="3" required placeholder="Describe what you observed during verification..."></textarea>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-white border fw-bold" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning fw-bold px-4">Complete Verification</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Assign Team Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow-lg">
      <form method="post" action="<?= base_url('reports/' . e($incident['code']) . '/assign') ?>">
        <?= csrf_field() ?>
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold fs-3"><?= $activeAssignment ? 'Reassign Team' : 'Deploy Response Team' ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <?php if (empty($availableTeams)): ?>
            <div class="alert alert-warning border-0 small">No available teams right now. All teams are on mission or offline.</div>
          <?php else: ?>
            <div class="mb-3">
              <label class="form-label fw-bold small text-muted text-uppercase mb-3">Select Available Unit</label>
              <div class="row g-3">
                <?php foreach ($availableTeams as $t): ?>
                  <?php
                  $unitColors = ['fire'=>'bg-red','medical'=>'bg-green','rescue'=>'bg-blue','security'=>'bg-purple','multi'=>'bg-orange'];
                  $uc = $unitColors[$t['unit_type']] ?? 'bg-secondary';
                  ?>
                  <div class="col-md-6">
                    <label class="form-check card p-3 mb-0 border-0 shadow-sm" style="cursor:pointer; background: #f8fafc;">
                      <input class="form-check-input me-2" type="radio" name="team_id" value="<?= $t['id'] ?>" required>
                      <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                          <span class="fw-bold text-slate"><?= e($t['team_name']) ?></span>
                          <span class="badge <?= $uc ?>-lt text-<?= str_replace('bg-','',$uc) ?> border fw-bold" style="font-size:0.6rem;"><?= e(ucfirst($t['unit_type'])) ?></span>
                        </div>
                        <div class="text-muted x-small">
                          Leader: <?= e($t['leader_name'] ?? '—') ?> ·
                          Load: <?= (int) $t['active_load'] ?> mission(s)
                          <?php if ($t['coverage_area']): ?>
                            · Area: <?= e($t['coverage_area']) ?>
                          <?php endif; ?>
                        </div>
                      </div>
                    </label>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-bold small text-muted text-uppercase">Dispatch Notes</label>
              <input type="text" name="notes" class="form-control filter-input" placeholder="e.g. Bring medical kit, suspect fire in kitchen">
            </div>
          <?php endif; ?>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-white border fw-bold" data-bs-dismiss="modal">Cancel</button>
          <?php if (!empty($availableTeams)): ?>
            <button type="submit" class="btn btn-primary fw-bold px-4">
              <?= $activeAssignment ? 'Confirm Reassign' : 'Deploy Now' ?>
            </button>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow-lg">
      <form method="post" action="<?= base_url('reports/' . e($incident['code']) . '/reject') ?>">
        <?= csrf_field() ?>
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold fs-3">Reject Report</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-bold small text-muted text-uppercase">Rejection Reason</label>
            <textarea name="reason" class="form-control filter-input" rows="4" required placeholder="The resident will be notified with this reason..."></textarea>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-white border fw-bold" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger fw-bold px-4">Reject Report</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Duplicate Modal -->
<div class="modal fade" id="duplicateModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow-lg">
      <form method="post" action="<?= base_url('reports/' . e($incident['code']) . '/duplicate') ?>">
        <?= csrf_field() ?>
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold fs-3">Mark as Duplicate</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-bold small text-muted text-uppercase">Original Report Code</label>
            <input type="text" name="original_code" class="form-control filter-input" placeholder="e.g. RQN-2026-0001" required>
            <div class="form-text x-small mt-2">The resident will be notified that their report duplicates the specified case.</div>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-white border fw-bold" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-outline-secondary fw-bold px-4">Mark Duplicate</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php if ($hasMap): ?>
<?php
$extraScripts = $extraScripts ?? '';
ob_start();
?>
<script>
(function() {
  var lat = <?= (float) $incident['location_lat'] ?>;
  var lng = <?= (float) $incident['location_lng'] ?>;
  var incCode = '<?= e($incident['code']) ?>';
  var map = L.map('detail-map', { 
    zoomControl: false,
    fadeAnimation: true
  }).setView([lat, lng], 16);
  
  L.control.zoom({ position: 'bottomright' }).addTo(map);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap', maxZoom: 19
  }).addTo(map);

  // Fix for map not loading properly in some layouts
  setTimeout(function() {
    map.invalidateSize();
  }, 200);

  // Incident pin (red)
  var incIcon = L.divIcon({
    html: '<div style="width:20px;height:20px;border-radius:50%;background:#dc2626;border:4px solid #fff;box-shadow:0 2px 10px rgba(220,38,38,.4);position:relative;">' +
            '<div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:6px;height:6px;background:#fff;border-radius:50%;"></div>' +
          '</div>',
    className:'', iconSize:[20,20], iconAnchor:[10,10]
  });
  L.marker([lat, lng], { icon: incIcon }).addTo(map).bindPopup('<b>Incident Location</b>').openPopup();

  <?php if (!empty($reporter['home_lat']) && !empty($reporter['home_lng'])): ?>
  var hLat = <?= (float) $reporter['home_lat'] ?>;
  var hLng = <?= (float) $reporter['home_lng'] ?>;
  var homeIcon = L.divIcon({
    html: '<div style="background:#2563eb;width:12px;height:12px;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 4px rgba(0,0,0,.2);"></div>',
    className:'', iconSize:[12,12], iconAnchor:[6,6]
  });
  L.marker([hLat, hLng], { icon: homeIcon }).addTo(map).bindPopup('Resident Home');
  map.fitBounds([[lat, lng], [hLat, hLng]], { padding: [40, 40] });
  <?php endif; ?>

  // ── Live responder tracking ──────────────────────────────────────
  var responderMarker = null;
  var responderCircle = null;
  var responderInfo   = document.getElementById('responder-info-bar');

  function updateResponderPin() {
    fetch('<?= base_url('api/v1/incidents/') ?>' + incCode + '/responder-location', {
      credentials: 'same-origin'
    })
    .then(function(r){ return r.ok ? r.json() : null; })
    .then(function(d){
      if (!d || !d.tracking || !d.lat) {
        if (responderInfo) {
          responderInfo.textContent = d && d.team_name
            ? d.team_name + ' — location not available'
            : '';
        }
        return;
      }

      var rLat = d.lat, rLng = d.lng, acc = d.accuracy || 0;

      // Pulsing responder icon
      var rIcon = L.divIcon({
        html: '<div style="position:relative;">' +
                '<div style="width:18px;height:18px;border-radius:50%;background:#2563eb;border:3px solid #fff;box-shadow:0 2px 8px rgba(37,99,235,.6);position:relative;z-index:2;"></div>' +
                '<div style="position:absolute;top:-6px;left:-6px;width:30px;height:30px;border-radius:50%;background:rgba(37,99,235,.2);animation:resp-pulse 2s infinite;"></div>' +
              '</div>',
        className:'', iconSize:[18,18], iconAnchor:[9,9]
      });

      if (!responderMarker) {
        responderMarker = L.marker([rLat, rLng], { icon: rIcon, zIndexOffset: 1000 })
          .addTo(map)
          .bindPopup('<b>' + (d.team_name || 'Responder') + '</b>');
        if (acc > 0) {
          responderCircle = L.circle([rLat, rLng], {
            radius: acc, color: '#2563eb', fillColor: '#3b82f6',
            fillOpacity: 0.08, weight: 1, dashArray: '4'
          }).addTo(map);
        }
      } else {
        // Smooth move
        responderMarker.setLatLng([rLat, rLng]);
        if (responderCircle) responderCircle.setLatLng([rLat, rLng]);
      }

      // Info bar
      if (responderInfo) {
        var ago = d.last_seen_seconds !== null ? d.last_seen_seconds + 's ago' : '';
        var eta = d.eta_minutes !== null
          ? (d.eta_minutes === 0 ? 'On site' : 'ETA ~' + d.eta_minutes + ' min')
          : '';
        responderInfo.innerHTML =
          '<span style="color:#2563eb;font-weight:700;">' + (d.team_name || 'Responder') + '</span>' +
          (ago ? ' &middot; last seen ' + ago : '') +
          (eta ? ' &middot; ' + eta : '');
      }
    })
    .catch(function(){});
  }

  // Poll every 5 seconds
  updateResponderPin();
  setInterval(updateResponderPin, 5000);
})();
</script>
<style>
@keyframes resp-pulse {
  0%   { transform:scale(1);   opacity:.7; }
  100% { transform:scale(2.2); opacity:0;  }
}
</style>
<?php
$extraScripts .= ob_get_clean();
?>
<?php endif; ?>
