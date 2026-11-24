<?php
$profile     = $profile     ?? [];
$reportCount = $reportCount ?? 0;
$flash       = $flash       ?? null;

$roleMeta = [
    'admin'     => ['#eef2ff','#4f46e5','Administrator'],
    'personnel' => ['#eff6ff','#2563eb','Personnel / Dispatcher'],
    'responder' => ['#f0fdf4','#16a34a','Responder'],
    'resident'  => ['#fff7ed','#ea580c','Resident'],
];
[$roleBg, $roleColor, $roleLabel] = $roleMeta[$profile['role'] ?? ''] ?? ['#f8fafc','#64748b',ucfirst($profile['role'] ?? '')];
?>
<style>
.pf-wrap { padding:20px 24px 32px;max-width:820px; }
.pf-card { background:#fff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;margin-bottom:16px; }
.pf-card-hd { padding:14px 20px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:8px; }
.pf-card-title { font-size:.875rem;font-weight:700;color:#0f172a;margin:0; }
.pf-card-body { padding:20px; }
.pf-label { display:block;font-size:.8rem;font-weight:700;color:#374151;margin-bottom:6px; }
.pf-input { width:100%;height:44px;padding:0 14px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.875rem;color:#0f172a;background:#f8fafc;outline:none;font-family:inherit;transition:border-color .12s; }
.pf-input:focus { border-color:#dc2626;background:#fff; }
.pf-input[readonly] { color:#94a3b8;cursor:not-allowed; }
.pf-hint { font-size:.72rem;color:#94a3b8;margin-top:5px; }
.pf-grid2 { display:grid;grid-template-columns:1fr 1fr;gap:14px; }
@media(max-width:600px){ .pf-grid2 { grid-template-columns:1fr; } }
.pf-btn { height:40px;padding:0 20px;background:#dc2626;color:#fff;border:none;border-radius:7px;font-size:.875rem;font-weight:700;cursor:pointer;box-shadow:0 2px 6px rgba(220,38,38,.25); }
.pf-btn:hover { background:#b91c1c; }
.pf-btn-sec { height:40px;padding:0 16px;background:#fff;color:#475569;border:1.5px solid #e2e8f0;border-radius:7px;font-size:.875rem;font-weight:600;cursor:pointer; }
</style>

<div class="pf-wrap">

  <?php if ($flash): ?>
    <div style="padding:10px 14px;border-radius:6px;margin-bottom:14px;font-size:.8125rem;
      <?= $flash['type']==='success' ? 'background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;' : 'background:#fef2f2;border:1px solid #fecaca;color:#991b1b;' ?>">
      <?= e($flash['message']) ?>
    </div>
  <?php endif; ?>

  <!-- Header -->
  <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;">
    <div style="width:56px;height:56px;border-radius:50%;background:<?= $roleBg ?>;color:<?= $roleColor ?>;display:flex;align-items:center;justify-content:center;font-size:1.25rem;font-weight:800;flex-shrink:0;">
      <?= strtoupper(substr($profile['name'] ?? 'U', 0, 1)) ?>
    </div>
    <div>
      <h1 style="font-size:1.25rem;font-weight:800;color:#0f172a;margin:0 0 2px;"><?= e($profile['name'] ?? '') ?></h1>
      <div style="display:flex;align-items:center;gap:8px;">
        <span style="display:inline-block;padding:3px 10px;border-radius:5px;font-size:.7rem;font-weight:700;background:<?= $roleBg ?>;color:<?= $roleColor ?>;"><?= $roleLabel ?></span>
        <span style="font-size:.75rem;color:#94a3b8;">Joined <?= !empty($profile['created_at']) ? date('M j, Y', strtotime($profile['created_at'])) : '—' ?></span>
      </div>
    </div>
    <?php if ($reportCount > 0): ?>
      <div style="margin-left:auto;text-align:right;">
        <div style="font-size:1.25rem;font-weight:800;color:#0f172a;line-height:1;"><?= $reportCount ?></div>
        <div style="font-size:.72rem;color:#94a3b8;">reports submitted</div>
      </div>
    <?php endif; ?>
  </div>

  <!-- Profile form -->
  <div class="pf-card">
    <div class="pf-card-hd">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      <h3 class="pf-card-title">Personal Information</h3>
    </div>
    <div class="pf-card-body">
      <form method="post" action="<?= base_url('profile') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="profile">

        <div class="pf-grid2" style="margin-bottom:14px;">
          <div>
            <label class="pf-label">Full Name <span style="color:#dc2626;">*</span></label>
            <input class="pf-input" type="text" name="name" value="<?= e($profile['name'] ?? '') ?>" required>
          </div>
          <div>
            <label class="pf-label">Role</label>
            <input class="pf-input" type="text" value="<?= e($roleLabel) ?>" readonly>
            <div class="pf-hint">Role is managed by your administrator.</div>
          </div>
        </div>

        <div class="pf-grid2" style="margin-bottom:14px;">
          <div>
            <label class="pf-label">Phone Number <span style="color:#dc2626;">*</span></label>
            <input class="pf-input" type="tel" name="phone" value="<?= e($profile['phone'] ?? '') ?>" required placeholder="09XXXXXXXXX" pattern="[0-9\+\-\s\(\)]{7,20}" title="Digits only — spaces, dashes, + and () allowed">
          </div>
          <div>
            <label class="pf-label">Email Address</label>
            <input class="pf-input" type="email" name="email" value="<?= e($profile['email'] ?? '') ?>" placeholder="you@example.com">
          </div>
        </div>

        <div style="display:flex;gap:8px;padding-top:14px;border-top:1px solid #f1f5f9;">
          <button type="submit" class="pf-btn">Save Changes</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Password change -->
  <div class="pf-card">
    <div class="pf-card-hd">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      <h3 class="pf-card-title">Change Password</h3>
    </div>
    <div class="pf-card-body">
      <form method="post" action="<?= base_url('profile') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="password">

        <div style="margin-bottom:14px;">
          <label class="pf-label">Current Password <span style="color:#dc2626;">*</span></label>
          <input class="pf-input" type="password" name="current_password" autocomplete="current-password" required>
        </div>

        <div class="pf-grid2" style="margin-bottom:14px;">
          <div>
            <label class="pf-label">New Password <span style="color:#dc2626;">*</span></label>
            <input class="pf-input" type="password" name="new_password" id="new-pw" autocomplete="new-password" required>
          </div>
          <div>
            <label class="pf-label">Confirm New Password <span style="color:#dc2626;">*</span></label>
            <input class="pf-input" type="password" name="confirm_password" autocomplete="new-password" required>
          </div>
        </div>

        <!-- Password meter -->
        <div id="pw-meter-wrap" style="margin-bottom:14px;display:none;">
          <div style="display:flex;gap:4px;margin-bottom:6px;">
            <?php for ($i=1;$i<=4;$i++): ?>
              <div id="pw-seg-<?= $i ?>" style="height:4px;flex:1;border-radius:2px;background:#e2e8f0;transition:background .2s;"></div>
            <?php endfor; ?>
          </div>
          <div id="pw-meter-label" style="font-size:.75rem;font-weight:600;color:#64748b;margin-bottom:6px;">Enter a new password</div>
          <div style="display:flex;flex-wrap:wrap;gap:6px 14px;">
            <?php foreach (['len'=>'8+ characters','upper'=>'Uppercase','lower'=>'Lowercase','num'=>'Number','spec'=>'Special character'] as $k=>$t): ?>
              <span id="req-<?= $k ?>" style="font-size:.7rem;color:#94a3b8;">○ <?= $t ?></span>
            <?php endforeach; ?>
          </div>
        </div>

        <div style="display:flex;gap:8px;padding-top:14px;border-top:1px solid #f1f5f9;">
          <button type="submit" class="pf-btn" style="background:#1e293b;box-shadow:none;">Change Password</button>
        </div>
      </form>
    </div>
  </div>

</div>

<script>
(function() {
  var pw = document.getElementById('new-pw');
  var wrap = document.getElementById('pw-meter-wrap');
  var label = document.getElementById('pw-meter-label');
  var segs = [document.getElementById('pw-seg-1'),document.getElementById('pw-seg-2'),document.getElementById('pw-seg-3'),document.getElementById('pw-seg-4')];
  var reqEls = { len:document.getElementById('req-len'), upper:document.getElementById('req-upper'), lower:document.getElementById('req-lower'), num:document.getElementById('req-num'), spec:document.getElementById('req-spec') };
  var colors = ['','#ef4444','#f59e0b','#eab308','#22c55e'];
  var labels = ['','Weak','Fair','Good','Strong'];

  function check() {
    var v = pw.value;
    var checks = { len:v.length>=8, upper:/[A-Z]/.test(v), lower:/[a-z]/.test(v), num:/[0-9]/.test(v), spec:/[^a-zA-Z0-9]/.test(v) };
    var score = Object.values(checks).filter(Boolean).length;
    segs.forEach(function(s,i){ s.style.background = i<score ? colors[score] : '#e2e8f0'; });
    label.textContent = v.length===0 ? 'Enter a new password' : labels[score];
    label.style.color = score>0 ? colors[score] : '#64748b';
    Object.keys(checks).forEach(function(k){
      var el = reqEls[k];
      if (!el) return;
      el.textContent = (checks[k]?'✓ ':'○ ') + el.textContent.slice(2);
      el.style.color = checks[k] ? '#22c55e' : '#94a3b8';
    });
  }

  if (pw) {
    pw.addEventListener('focus', function(){ wrap.style.display=''; });
    pw.addEventListener('input', function(){ wrap.style.display=''; check(); });
  }
})();
</script>
