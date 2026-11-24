<?php
$isEdit = !empty($user);
?>
<style>
  .pw-meter-bar { display: flex; gap: 4px; margin: 8px 0 6px; }
  .pw-meter-seg { height: 4px; flex: 1; border-radius: 2px; background: #e2e8f0; transition: background .2s; }
  .pw-meter-seg.active.s1 { background: #ef4444; }
  .pw-meter-seg.active.s2 { background: #f59e0b; }
  .pw-meter-seg.active.s3 { background: #eab308; }
  .pw-meter-seg.active.s4 { background: #22c55e; }
  .pw-meter-label { font-size: .8rem; font-weight: 600; color: #64748b; margin-bottom: 8px; }
  .pw-meter-label.weak { color: #ef4444; }
  .pw-meter-label.fair { color: #f59e0b; }
  .pw-meter-label.good { color: #eab308; }
  .pw-meter-label.strong { color: #22c55e; }
  .pw-reqs { list-style: none; padding: 0; margin: 0; display: flex; flex-wrap: wrap; gap: 6px 16px; }
  .pw-req { font-size: .75rem; color: #94a3b8; display: flex; align-items: center; gap: 4px; }
  .pw-req::before { content: '\25CB'; font-size: .85rem; }
  .pw-req.met { color: #22c55e; }
  .pw-req.met::before { content: '\2713'; color: #22c55e; }
  .pw-meter-wrap { margin-top: 8px; }
</style>
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <div class="page-pretitle">
          <a href="<?= base_url('personnel') ?>" class="text-muted text-decoration-none">&larr; Back to List</a>
        </div>
        <h2 class="page-title"><?= $isEdit ? 'Edit Personnel: ' . e($user['name']) : 'Add New Personnel' ?></h2>
      </div>
    </div>
  </div>
</div>

<div class="page-body">
  <div class="container-xl">
    
    <div class="row">
      <div class="col-md-6">
        <div class="card">
          <div class="card-body">
            <form method="post" action="<?= base_url('personnel/' . ($isEdit ? $user['id'] . '/edit' : 'new')) ?>">
              <?= csrf_field() ?>
              
              <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" value="<?= e($user['name'] ?? '') ?>" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" value="<?= e($user['email'] ?? '') ?>" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Phone Number</label>
                <input type="tel" name="phone" class="form-control"
                       value="<?= e($user['phone'] ?? '') ?>"
                       placeholder="e.g. 09171234567"
                       pattern="[0-9\+\-\s\(\)]{7,20}"
                       title="Digits only — spaces, dashes, + and () allowed">
              </div>

              <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select">
                  <option value="personnel" <?= ($user['role'] ?? '') === 'personnel' ? 'selected' : '' ?>>Personnel (Dispatcher)</option>
                  <option value="admin" <?= ($user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Administrator</option>
                  <option value="responder" <?= ($user['role'] ?? '') === 'responder' ? 'selected' : '' ?>>Responder (Team Leader)</option>
                </select>
              </div>

              <?php if (!$isEdit): ?>
                <div class="mb-3">
                  <label class="form-label">Initial Password</label>
                  <input type="password" name="password" id="pw-create" class="form-control" required minlength="8">
                  <div class="form-hint">
                    Must be 8+ characters, with uppercase, lowercase, number, and special character.
                  </div>
                  <div class="pw-meter-wrap" id="pw-create-meter" style="display:none;">
                    <div class="pw-meter-bar">
                      <div class="pw-meter-seg" data-idx="1"></div>
                      <div class="pw-meter-seg" data-idx="2"></div>
                      <div class="pw-meter-seg" data-idx="3"></div>
                      <div class="pw-meter-seg" data-idx="4"></div>
                    </div>
                    <div class="pw-meter-label">Enter a password</div>
                    <ul class="pw-reqs">
                      <li class="pw-req" data-req="len">8+ characters</li>
                      <li class="pw-req" data-req="upper">Uppercase letter</li>
                      <li class="pw-req" data-req="lower">Lowercase letter</li>
                      <li class="pw-req" data-req="num">Number</li>
                      <li class="pw-req" data-req="spec">Special character</li>
                    </ul>
                  </div>
                </div>
              <?php else: ?>
                <div class="mb-3">
                  <label class="form-label">Status</label>
                  <select name="status" class="form-select">
                    <option value="approved" <?= ($user['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Active</option>
                    <option value="suspended" <?= ($user['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                  </select>
                </div>
              <?php endif; ?>

              <div class="mt-4">
                <button type="submit" class="btn btn-primary w-100">
                  <?= $isEdit ? 'Save Changes' : 'Create Account' ?>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <?php if ($isEdit): ?>
        <div class="col-md-6">
          <div class="card border-danger">
            <div class="card-header"><h3 class="card-title text-danger">Security: Reset Password</h3></div>
            <div class="card-body">
              <form method="post" action="<?= base_url('personnel/' . $user['id'] . '/reset-password') ?>">
                <?= csrf_field() ?>
                <div class="mb-3">
                  <label class="form-label">New Password</label>
                  <input type="password" name="password" id="pw-reset" class="form-control" required minlength="8">
                  <div class="form-hint">
                    Must be 8+ characters, with uppercase, lowercase, number, and special character.
                  </div>
                  <div class="pw-meter-wrap" id="pw-reset-meter" style="display:none;">
                    <div class="pw-meter-bar">
                      <div class="pw-meter-seg" data-idx="1"></div>
                      <div class="pw-meter-seg" data-idx="2"></div>
                      <div class="pw-meter-seg" data-idx="3"></div>
                      <div class="pw-meter-seg" data-idx="4"></div>
                    </div>
                    <div class="pw-meter-label">Enter a password</div>
                    <ul class="pw-reqs">
                      <li class="pw-req" data-req="len">8+ characters</li>
                      <li class="pw-req" data-req="upper">Uppercase letter</li>
                      <li class="pw-req" data-req="lower">Lowercase letter</li>
                      <li class="pw-req" data-req="num">Number</li>
                      <li class="pw-req" data-req="spec">Special character</li>
                    </ul>
                  </div>
                </div>
                <button type="submit" class="btn btn-danger w-100">Force Password Reset</button>
              </form>
            </div>
          </div>
        </div>
      <?php endif; ?>
    </div>

  </div>
</div>

<script>
(function () {
  function attachMeter(inputId, meterId) {
    var input = document.getElementById(inputId);
    var meter = document.getElementById(meterId);
    if (!input || !meter) return;

    var segs = meter.querySelectorAll('.pw-meter-seg');
    var label = meter.querySelector('.pw-meter-label');
    var reqs = meter.querySelectorAll('.pw-req');

    function update() {
      var pw = input.value;
      var checks = {
        len: pw.length >= 8,
        upper: /[A-Z]/.test(pw),
        lower: /[a-z]/.test(pw),
        num: /[0-9]/.test(pw),
        spec: /[^a-zA-Z0-9]/.test(pw)
      };
      var score = Object.values(checks).filter(Boolean).length;

      reqs.forEach(function (li) {
        var key = li.getAttribute('data-req');
        li.classList.toggle('met', checks[key]);
      });

      var labels = ['Too weak', 'Weak', 'Fair', 'Good', 'Strong'];
      var cls = ['', 'weak', 'fair', 'good', 'strong'];
      segs.forEach(function (seg, i) {
        seg.classList.remove('active', 's1', 's2', 's3', 's4');
        if (i < score) { seg.classList.add('active', 's' + score); }
      });

      label.textContent = pw.length === 0 ? 'Enter a password' : labels[score];
      label.className = 'pw-meter-label ' + (pw.length === 0 ? '' : cls[score]);
    }

    input.addEventListener('input', function () {
      meter.style.display = this.value.length > 0 ? '' : 'none';
      update();
    });
    input.addEventListener('focus', function () { meter.style.display = ''; update(); });
  }

  attachMeter('pw-create', 'pw-create-meter');
  attachMeter('pw-reset', 'pw-reset-meter');
})();
</script>
