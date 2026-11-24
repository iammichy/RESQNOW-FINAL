<?php
$team      = $team ?? null;
$members   = $members ?? [];
$responders= $responders ?? [];
$errors    = $errors ?? [];
$isEdit    = $team && !empty($team['id']);
?>

<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <div class="page-pretitle"><a href="<?= base_url('teams') ?>" class="text-muted">&larr; Response Teams</a></div>
        <h2 class="page-title"><?= $isEdit ? 'Edit ' . e($team['team_name']) : 'New Response Team' ?></h2>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card">
        <div class="card-body">
          <form method="post" action="<?= $isEdit ? base_url('teams/' . $team['id'] . '/edit') : base_url('teams/new') ?>">
            <?= csrf_field() ?>

            <?php if (!empty($errors)): ?>
              <div class="alert alert-danger">
                <ul class="mb-0">
                  <?php foreach ($errors as $e): ?><li><?= e($e) ?></li><?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>

            <div class="mb-3">
              <label class="form-label">Team Name <span class="text-danger">*</span></label>
              <input type="text" name="team_name" class="form-control <?= isset($errors['team_name']) ? 'is-invalid' : '' ?>"
                     value="<?= e($team['team_name'] ?? '') ?>" required>
              <?php if (isset($errors['team_name'])): ?>
                <div class="invalid-feedback"><?= e($errors['team_name']) ?></div>
              <?php endif; ?>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Unit Type <span class="text-danger">*</span></label>
                <select name="unit_type" class="form-select" required>
                  <option value="">— Select —</option>
                  <?php foreach ([
                    'fire'     => 'Fire — Structural/vehicle fires, LPG hazards',
                    'medical'  => 'Medical — First aid, patient care, medical evacuation',
                    'rescue'   => 'Rescue — Flood, earthquake, structural collapse',
                    'security' => 'Security — Law enforcement support, armed threats',
                    'multi'    => 'Multi-hazard — General purpose, any incident type',
                  ] as $val => $lbl): ?>
                    <option value="<?= $val ?>" <?= ($team['unit_type'] ?? '') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                  <?php foreach (['available' => 'Available', 'offline' => 'Offline'] as $val => $label): ?>
                    <option value="<?= $val ?>" <?= ($team['status'] ?? 'available') === $val ? 'selected' : '' ?>><?= $label ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Coverage Area</label>
                <input type="text" name="coverage_area" class="form-control"
                       value="<?= e($team['coverage_area'] ?? '') ?>" placeholder="e.g. Barangay 1 & 2">
              </div>
              <div class="col-md-6 mb-3">
                <label class="form-label">Contact No.</label>
                <input type="tel" name="contact_no" class="form-control"
                       value="<?= e($team['contact_no'] ?? '') ?>"
                       placeholder="e.g. 09200000001"
                       pattern="[0-9\+\-\s]{7,20}"
                       title="Digits, spaces, dashes, or + only (7–20 characters)">
                <div class="form-hint">Philippine mobile number, e.g. 09171234567</div>
                <?php if (isset($errors['contact_no'])): ?>
                  <div class="text-danger small mt-1"><?= e($errors['contact_no']) ?></div>
                <?php endif; ?>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Team Leader (Responder)</label>
              <select name="leader_id" class="form-select" id="leader-select" onchange="syncLeaderMember()">
                <option value="">— None —</option>
                <?php foreach ($responders as $r): ?>
                  <option value="<?= $r['id'] ?>" <?= ($team['leader_id'] ?? null) == $r['id'] ? 'selected' : '' ?>>
                    <?= e($r['name']) ?><?= !empty($r['phone']) ? ' · ' . e($r['phone']) : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <script>
            function syncLeaderMember() {
              var lid = document.getElementById('leader-select').value;
              document.querySelectorAll('[name="members[]"]').forEach(function(cb) {
                var row = cb.closest('.col-md-6');
                if (lid && cb.value === lid) {
                  cb.checked = false;
                  if (row) row.style.opacity = '.4';
                } else {
                  if (row) row.style.opacity = '';
                }
              });
            }
            document.addEventListener('DOMContentLoaded', syncLeaderMember);
            </script>

            <div class="mb-4">
              <label class="form-label">Team Members</label>
              <div class="form-text mb-2">Select responders assigned to this team. The team leader is automatically included — no need to add them separately.</div>
              <?php
              $leaderId = (int)($team['leader_id'] ?? 0);
              $nonLeaders = array_filter($responders, fn($r) => (int)$r['id'] !== $leaderId);
              ?>
              <?php if (empty($nonLeaders)): ?>
                <div class="text-muted small">No other responders found. Add users with role = responder first.</div>
              <?php else: ?>
                <div class="row g-2">
                  <?php foreach ($nonLeaders as $r): ?>
                    <div class="col-md-6">
                      <label class="form-check">
                        <input class="form-check-input" type="checkbox" name="members[]"
                               value="<?= $r['id'] ?>"
                               <?= in_array((int) $r['id'], array_map('intval', $members)) ? 'checked' : '' ?>>
                      <span class="form-check-label">
                          <?= e($r['name']) ?>
                          <?php if (!empty($r['phone'])): ?>
                            <small class="text-muted">· <?= e($r['phone']) ?></small>
                          <?php endif; ?>
                        </span>
                      </label>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            </div>

            <div class="d-flex gap-2">
              <button type="submit" class="btn resq-brand-btn"><?= $isEdit ? 'Save Changes' : 'Create Team' ?></button>
              <a href="<?= base_url('teams') ?>" class="btn btn-outline-secondary">Cancel</a>
            </div>

          </form>
        </div>
      </div>
    </div>
  </div>
</div>
