<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <div class="page-pretitle">
          <a href="<?= base_url('residents') ?>" class="text-muted text-decoration-none">&larr; Back to List</a>
        </div>
        <h2 class="page-title">Resident: <?= e($resident['name']) ?></h2>
      </div>
    </div>
  </div>
</div>

<div class="page-body">
  <div class="container-xl">
    
    <div class="row g-3">
      <div class="col-md-4">
        <div class="card">
          <div class="card-body text-center">
            <span class="avatar avatar-xl rounded-circle mb-3"><?= substr(e($resident['name']), 0, 1) ?></span>
            <h3 class="mb-1"><?= e($resident['name']) ?></h3>
            <div class="text-muted mb-3"><?= e($resident['email'] ?: $resident['phone']) ?></div>
            
            <div class="list-group list-group-flush text-start">
              <div class="list-group-item d-flex justify-content-between">
                <span class="text-muted small">Status</span>
                <span class="badge bg-<?= $resident['status'] === 'approved' ? 'success' : ($resident['status'] === 'pending' ? 'warning' : 'danger') ?>">
                  <?= ucfirst($resident['status']) ?>
                </span>
              </div>
              <div class="list-group-item d-flex justify-content-between">
                <span class="text-muted small">Phone</span>
                <span class="fw-bold"><?= e($resident['phone'] ?: '—') ?></span>
              </div>
              <div class="list-group-item d-flex justify-content-between">
                <span class="text-muted small">Registered</span>
                <span><?= date('M j, Y', strtotime($resident['created_at'])) ?></span>
              </div>
            </div>

            <?php if ($resident['status'] === 'pending' && auth()['role'] === 'admin'): ?>
              <div class="mt-4 d-grid gap-2">
                <form method="post" action="<?= base_url('residents/' . $resident['id'] . '/approve') ?>">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-success w-100">Approve Account</button>
                </form>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                  Reject Account
                </button>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="card mt-3">
          <div class="card-header"><h3 class="card-title">Masterlist & Residency Verification</h3></div>
          <div class="card-body">
            <?php
            $ms = $resident['match_status'] ?? null;
            $mconf = (int)($resident['match_confidence'] ?? 0);
            $msColors = [
                'matched'           => ['bg-success-lt text-success',   'Masterlist Match Found'],
                'not_found'         => ['bg-danger-lt text-danger',     'Not Found in Masterlist'],
                'unverified'        => ['bg-warning-lt text-warning',   'Multiple Possible Matches'],
                'flagged_duplicate' => ['bg-danger-lt text-danger',     'Possible Duplicate Registration'],
            ];
            [$msBadge, $msLabel] = $msColors[$ms] ?? ['bg-secondary-lt text-muted', 'Not Checked'];
            ?>

            <div class="mb-3">
              <div class="text-muted small fw-bold mb-1">Masterlist Check</div>
              <span class="badge <?= $msBadge ?> fw-bold me-2"><?= $msLabel ?></span>
              <?php if ($mconf): ?>
                <span class="text-muted small">Confidence: <?= $mconf ?>%</span>
              <?php endif; ?>
            </div>

            <?php if (!empty($masterlistRecord)): ?>
              <div class="bg-success-lt rounded-3 p-3 mb-3 small">
                <div class="fw-bold text-success mb-1">Matched Masterlist Record</div>
                <div>Name: <strong><?= e($masterlistRecord['name']) ?></strong></div>
                <div>Purok: <?= $masterlistRecord['purok'] ?> · Family #<?= $masterlistRecord['family_no'] ?></div>
                <a href="<?= base_url('residents/masterlist/' . $masterlistRecord['id'] . '/edit') ?>" class="text-success small fw-bold">Edit Record →</a>
              </div>
            <?php endif; ?>

            <?php if (!empty($verifRecord)): ?>
              <div class="mb-3">
                <div class="text-muted small fw-bold mb-1">Residency Verification</div>
                <?php
                $vsBadge = ['pending'=>'bg-warning-lt text-warning','verified'=>'bg-success-lt text-success','rejected'=>'bg-danger-lt text-danger'];
                $vb = $vsBadge[$verifRecord['status']] ?? 'bg-secondary-lt text-muted';
                ?>
                <span class="badge <?= $vb ?> fw-bold"><?= ucfirst($verifRecord['status']) ?></span>
                <?php if ($verifRecord['verified_by_name']): ?>
                  <div class="text-muted small mt-1">By <?= e($verifRecord['verified_by_name']) ?> · <?= $verifRecord['verified_at'] ? date('M j, Y', strtotime($verifRecord['verified_at'])) : '' ?></div>
                <?php endif; ?>
                <?php if ($verifRecord['notes']): ?>
                  <div class="text-muted small mt-1"><?= e($verifRecord['notes']) ?></div>
                <?php endif; ?>
              </div>
            <?php endif; ?>

            <?php if ($resident['status'] === 'pending' && auth()['role'] === 'admin'): ?>
              <?php if (!$verifRecord || $verifRecord['status'] === 'pending'): ?>
                <!-- Verification form for admin -->
                <div class="border rounded-3 p-3 mb-3 small">
                  <div class="fw-bold mb-2 text-muted">Record Residency Verification</div>
                  <form method="post" action="<?= base_url('residents/verification/' . $resident['id'] . '/verify') ?>">
                    <?= csrf_field() ?>
                    <?php if ($resident['match_status'] !== 'matched' && !empty($resident['masterlist_id']) === false): ?>
                      <div class="mb-2">
                        <label class="form-label small">Link to Masterlist Record (optional)</label>
                        <input type="number" name="masterlist_id" class="form-control form-control-sm" placeholder="Masterlist ID (leave blank if not found)" value="<?= $resident['masterlist_id'] ?? '' ?>">
                      </div>
                    <?php else: ?>
                      <input type="hidden" name="masterlist_id" value="<?= $resident['masterlist_id'] ?? '' ?>">
                    <?php endif; ?>
                    <div class="mb-2">
                      <label class="form-label small">Verification Notes</label>
                      <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="e.g. Verified via house visit on Oct 25, 2026. Found in Purok 1."></textarea>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm w-100 fw-bold">Confirm Residency Verified</button>
                  </form>
                  <form method="post" action="<?= base_url('residents/verification/' . $resident['id'] . '/reject') ?>" class="mt-2">
                    <?= csrf_field() ?>
                    <textarea name="notes" class="form-control form-control-sm mb-1" rows="1" placeholder="Reason for rejection…"></textarea>
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">Reject — Not a Resident</button>
                  </form>
                </div>
              <?php elseif ($verifRecord['status'] === 'verified'): ?>
                <div class="d-grid gap-2 mt-2">
                  <form method="post" action="<?= base_url('residents/' . $resident['id'] . '/approve') ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-success w-100 fw-bold">Approve Account</button>
                  </form>
                  <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">Reject Account</button>
                </div>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>

        <div class="card mt-3">
          <div class="card-header"><h3 class="card-title">Address Details</h3></div>
          <div class="card-body">
            <div class="mb-3">
              <div class="text-muted small">Home Address</div>
              <div><?= e($resident['home_address'] ?: '—') ?></div>
            </div>
            <?php if ($resident['home_lat']): ?>
              <div id="home-map" style="height: 200px; border-radius: 4px; border: 1px solid #e2e8f0;"></div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="col-md-8">
        <div class="card">
          <div class="card-header"><h3 class="card-title">Report History</h3></div>
          <div class="table-responsive">
            <table class="table table-vcenter card-table">
              <thead>
                <tr>
                  <th>Code</th>
                  <th>Hazard</th>
                  <th>Triage</th>
                  <th>Status</th>
                  <th>Date</th>
                  <th class="w-1"></th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($reports)): ?>
                  <tr><td colspan="6" class="text-center text-muted py-4">No reports submitted by this resident.</td></tr>
                <?php else: ?>
                  <?php foreach ($reports as $r): ?>
                    <tr>
                      <td class="fw-bold"><?= e($r['code']) ?></td>
                      <td class="text-capitalize"><?= e($r['hazard_type']) ?></td>
                      <td>
                        <?php
                        $tcls = match($r['triage_level']) {
                            'critical' => 'bg-danger', 'high' => 'bg-orange', 'moderate' => 'bg-amber', 'low' => 'bg-success', default => 'bg-secondary'
                        };
                        ?>
                        <span class="badge <?= $tcls ?>"><?= ucfirst($r['triage_level']) ?></span>
                      </td>
                      <td><span class="badge bg-blue-lt"><?= ucfirst($r['status']) ?></span></td>
                      <td class="small text-muted"><?= date('M j, Y', strtotime($r['created_at'])) ?></td>
                      <td><a href="<?= base_url('reports/' . $r['code']) ?>" class="btn btn-white btn-sm">View</a></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<?php if ($resident['status'] === 'pending'): ?>
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" action="<?= base_url('residents/' . $resident['id'] . '/reject') ?>">
        <?= csrf_field() ?>
        <div class="modal-header">
          <h5 class="modal-title">Reject Account</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Reason for Rejection</label>
            <textarea name="reason" class="form-control" rows="3" required placeholder="Explain why the account is being rejected..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">Confirm Rejection</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if ($resident['home_lat']): ?>
<?php
$extraScripts = $extraScripts ?? '';
ob_start();
?>
<script>
(function() {
  var map = L.map('home-map').setView([<?= (float)$resident['home_lat'] ?>, <?= (float)$resident['home_lng'] ?>], 17);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap', maxZoom: 19
  }).addTo(map);
  L.marker([<?= (float)$resident['home_lat'] ?>, <?= (float)$resident['home_lng'] ?>])
    .addTo(map).bindPopup('Home Location');
})();
</script>
<?php $extraScripts .= ob_get_clean(); ?>
<?php endif; ?>
