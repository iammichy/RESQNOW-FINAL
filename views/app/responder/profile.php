<?php
$user    = $user    ?? auth();
$teams   = $teams   ?? [];
$errors  = $errors  ?? [];
$success = $success ?? null;
$unitLabels = ['fire'=>'Fire','medical'=>'Medical','rescue'=>'Rescue','security'=>'Security','multi'=>'Multi-Hazard'];
$statusColors = ['available'=>['#f0fdf4','#16a34a','Available'],'on_mission'=>['#fff7ed','#ea580c','On Mission'],'offline'=>['#f8fafc','#94a3b8','Offline']];
?>
<style>
.rsp-prof-av {
  width: 80px; height: 80px; border-radius: 24px;
  background: linear-gradient(135deg, #dc2626, #b91c1c);
  display: flex; align-items: center; justify-content: center;
  font-size: 2rem; font-weight: 800; color: #fff;
  box-shadow: 0 8px 20px rgba(220,38,38,.3);
  margin: 0 auto 14px;
}
.rsp-info-row { display: flex; align-items: center; justify-content: space-between; padding: 13px 16px; border-bottom: 1px solid #f8fafc; }
.rsp-info-row:last-child { border-bottom: none; }
.rsp-info-label { font-size: .72rem; font-weight: 600; color: #94a3b8; margin-bottom: 2px; }
.rsp-info-val   { font-size: .875rem; font-weight: 700; color: #0f172a; }
.rsp-action-row { display: flex; align-items: center; gap: 12px; padding: 13px 16px; border-bottom: 1px solid #f8fafc; text-decoration: none; color: inherit; transition: background .1s; }
.rsp-action-row:last-child { border-bottom: none; }
.rsp-action-row:active { background: #f8fafc; }
.rsp-action-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
</style>

<div style="padding-top: 12px;">

  <!-- Avatar + name -->
  <div style="text-align: center; padding: 16px 24px 20px;">
    <div class="rsp-prof-av"><?= strtoupper(substr($user['name'] ?? 'R', 0, 1)) ?></div>
    <div style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin-bottom: 4px;"><?= e($user['name']) ?></div>
    <div style="font-size: .8rem; color: #64748b;"><?= e($user['phone'] ?? '') ?><?= !empty($user['email']) ? ' · ' . e($user['email']) : '' ?></div>
    <div style="margin-top: 8px; display: flex; justify-content: center; gap: 8px; flex-wrap: wrap;">
      <span style="display: inline-block; background: #eff6ff; color: #2563eb; border-radius: 6px; padding: 3px 10px; font-size: .68rem; font-weight: 700;">Responder</span>
      <span style="display: inline-block; background: #f0fdf4; color: #16a34a; border-radius: 6px; padding: 3px 10px; font-size: .68rem; font-weight: 700;">
        <?= ucfirst($user['status'] ?? 'Active') ?>
      </span>
    </div>
  </div>

  <!-- Account info -->
  <div style="font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;padding:4px 16px 8px;">Account Information</div>
  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;margin:0 16px 14px;overflow:hidden;">
    <div class="rsp-info-row">
      <div><div class="rsp-info-label">Full Name</div><div class="rsp-info-val"><?= e($user['name']) ?></div></div>
    </div>
    <div class="rsp-info-row">
      <div><div class="rsp-info-label">Phone</div><div class="rsp-info-val"><?= e($user['phone'] ?? '—') ?></div></div>
    </div>
    <div class="rsp-info-row">
      <div><div class="rsp-info-label">Email</div><div class="rsp-info-val"><?= e($user['email'] ?: '—') ?></div></div>
    </div>
  </div>

  <!-- Team information -->
  <?php if (!empty($teams)): ?>
    <div style="font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;padding:4px 16px 8px;">Team Assignment</div>
    <?php foreach ($teams as $t):
      $ut = $unitLabels[$t['unit_type'] ?? ''] ?? ucfirst($t['unit_type'] ?? '—');
      [$stBg,$stColor,$stLabel] = $statusColors[$t['status'] ?? 'available'] ?? ['#f8fafc','#64748b',ucfirst($t['status']??'')];
    ?>
      <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;margin:0 16px 12px;overflow:hidden;">
        <div style="padding:14px 16px;border-bottom:1px solid #f8fafc;display:flex;align-items:center;justify-content:space-between;">
          <div>
            <div style="font-size:.8125rem;font-weight:800;color:#0f172a;"><?= e($t['team_name']) ?></div>
            <div style="font-size:.72rem;color:#64748b;margin-top:2px;"><?= e($ut) ?></div>
          </div>
          <span style="background:<?= $stBg ?>;color:<?= $stColor ?>;border-radius:5px;padding:3px 9px;font-size:.65rem;font-weight:700;"><?= $stLabel ?></span>
        </div>
        <?php if (!empty($t['coverage_area'])): ?>
          <div class="rsp-info-row">
            <div><div class="rsp-info-label">Coverage Area</div><div class="rsp-info-val"><?= e($t['coverage_area']) ?></div></div>
          </div>
        <?php endif; ?>
        <?php if (!empty($t['contact_no'])): ?>
          <div class="rsp-info-row">
            <div><div class="rsp-info-label">Team Contact</div><div class="rsp-info-val"><?= e($t['contact_no']) ?></div></div>
          </div>
        <?php endif; ?>
        <?php if (!empty($t['leader_name'])): ?>
          <div class="rsp-info-row">
            <div><div class="rsp-info-label">Team Leader</div><div class="rsp-info-val"><?= e($t['leader_name']) ?></div></div>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;margin:0 16px 14px;padding:16px;text-align:center;font-size:.8rem;color:#94a3b8;">
      No team assigned yet. Contact your administrator.
    </div>
  <?php endif; ?>

  <!-- Success flash (shown on page when no modal needed) -->
  <?php if ($success): ?>
    <div style="margin:0 16px 14px;padding:12px 14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;font-size:.8rem;color:#166534;font-weight:600;">
      <?= e($success) ?>
    </div>
  <?php endif; ?>

  <!-- Settings -->
  <div style="font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;padding:4px 16px 8px;">Navigation</div>
  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;margin:0 16px 14px;overflow:hidden;">
    <a href="<?= base_url('app/responder') ?>" style="display:flex;align-items:center;gap:12px;padding:14px 16px;text-decoration:none;color:inherit;border-bottom:1px solid #f1f5f9;">
      <div style="width:36px;height:36px;border-radius:10px;background:#f8fafc;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.75"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
      </div>
      <div style="flex:1;font-size:.875rem;font-weight:600;color:#0f172a;">Missions</div>
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
    </a>
    <a href="<?= base_url('app/responder/contacts') ?>" style="display:flex;align-items:center;gap:12px;padding:14px 16px;text-decoration:none;color:inherit;border-bottom:1px solid #f1f5f9;">
      <div style="width:36px;height:36px;border-radius:10px;background:#f8fafc;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.75"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4a2 2 0 0 1 1.99-2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6.13 6.13l1.82-1.82a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
      </div>
      <div style="flex:1;font-size:.875rem;font-weight:600;color:#0f172a;">Emergency Contacts</div>
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
    </a>
    <a href="<?= base_url('app/notifications') ?>" style="display:flex;align-items:center;gap:12px;padding:14px 16px;text-decoration:none;color:inherit;">
      <div style="width:36px;height:36px;border-radius:10px;background:#f8fafc;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.75"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
      </div>
      <div style="flex:1;font-size:.875rem;font-weight:600;color:#0f172a;">Notifications</div>
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
    </a>
  </div>

  <!-- Settings -->
  <div style="font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;padding:4px 16px 8px;">Settings</div>
  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;margin:0 16px 14px;overflow:hidden;">

    <!-- Edit Profile trigger -->
    <button onclick="openModal('modal-profile')" class="rsp-action-row" style="width:100%;background:none;border:none;text-align:left;cursor:pointer;">
      <div class="rsp-action-icon" style="background:#eff6ff;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </div>
      <div style="flex:1;font-size:.875rem;font-weight:600;color:#0f172a;">Edit Profile</div>
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
    </button>

    <!-- Change Password trigger -->
    <button onclick="openModal('modal-password')" class="rsp-action-row" style="width:100%;background:none;border:none;text-align:left;cursor:pointer;border-top:1px solid #f1f5f9;">
      <div class="rsp-action-icon" style="background:#fef2f2;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
      </div>
      <div style="flex:1;font-size:.875rem;font-weight:600;color:#0f172a;">Change Password</div>
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
    </button>

    <!-- Notifications -->
    <div class="rsp-action-row" style="border-top:1px solid #f1f5f9;cursor:default;">
      <div class="rsp-action-icon" style="background:#f0fdf4;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
      </div>
      <div style="flex:1;">
        <div style="font-size:.875rem;font-weight:600;color:#0f172a;">Duty Notifications</div>
        <div style="font-size:.72rem;color:#64748b;">Mission alerts enabled</div>
      </div>
      <div style="width:42px;height:24px;background:#16a34a;border-radius:12px;position:relative;">
        <div style="position:absolute;top:2px;right:2px;width:20px;height:20px;background:#fff;border-radius:50%;box-shadow:0 1px 3px rgba(0,0,0,.2);"></div>
      </div>
    </div>
  </div>

</div><!-- /padding-top -->

<!-- ── Modal backdrop ─────────────────────────────────────────── -->
<div id="modal-backdrop" onclick="closeAllModals()"
  style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;backdrop-filter:blur(2px);">
</div>

<!-- ── Edit Profile Modal ─────────────────────────────────────── -->
<div id="modal-profile"
  style="display:none;position:fixed;bottom:0;left:0;right:0;z-index:1001;
         background:#fff;border-radius:20px 20px 0 0;padding:0 0 env(safe-area-inset-bottom);
         box-shadow:0 -8px 32px rgba(0,0,0,.15);max-height:90vh;overflow-y:auto;
         transform:translateY(100%);transition:transform .3s cubic-bezier(.32,1,.5,1);">
  <!-- Handle -->
  <div style="text-align:center;padding:14px 0 4px;">
    <div style="width:36px;height:4px;background:#e2e8f0;border-radius:2px;display:inline-block;"></div>
  </div>
  <div style="padding:0 20px 24px;">
    <div style="font-size:1rem;font-weight:800;color:#0f172a;margin-bottom:<?= (!empty($errors) && ($_POST['action']??'') === 'update_info') ? '12px' : '18px' ?>;">Edit Profile</div>

    <?php if (!empty($errors) && ($_POST['action'] ?? '') === 'update_info'): ?>
      <div style="padding:10px 12px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;font-size:.8rem;color:#991b1b;margin-bottom:14px;">
        <?php foreach ($errors as $err): ?><div>· <?= e($err) ?></div><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('app/responder/profile') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="update_info">
      <div style="margin-bottom:14px;">
        <label style="display:block;font-size:.72rem;font-weight:700;color:#64748b;margin-bottom:6px;">Full Name</label>
        <input type="text" name="name"
          value="<?= e(($_POST['action']??'') === 'update_info' ? ($_POST['name'] ?? $user['name'] ?? '') : ($user['name'] ?? '')) ?>"
          required class="mob-input">
      </div>
      <div style="margin-bottom:14px;">
        <label style="display:block;font-size:.72rem;font-weight:700;color:#64748b;margin-bottom:6px;">Phone Number</label>
        <input type="tel" name="phone"
          value="<?= e(($_POST['action']??'') === 'update_info' ? ($_POST['phone'] ?? $user['phone'] ?? '') : ($user['phone'] ?? '')) ?>"
          required pattern="[0-9\+\-\s\(\)]{7,20}" class="mob-input">
      </div>
      <div style="margin-bottom:20px;">
        <label style="display:block;font-size:.72rem;font-weight:700;color:#64748b;margin-bottom:6px;">Email Address</label>
        <input type="email" name="email"
          value="<?= e(($_POST['action']??'') === 'update_info' ? ($_POST['email'] ?? $user['email'] ?? '') : ($user['email'] ?? '')) ?>"
          class="mob-input">
      </div>
      <button type="submit" class="mob-btn">Save Changes</button>
      <button type="button" onclick="closeAllModals()" class="mob-btn mob-btn-secondary" style="margin-top:10px;">Cancel</button>
    </form>
  </div>
</div>

<!-- ── Change Password Modal ──────────────────────────────────── -->
<div id="modal-password"
  style="display:none;position:fixed;bottom:0;left:0;right:0;z-index:1001;
         background:#fff;border-radius:20px 20px 0 0;padding:0 0 env(safe-area-inset-bottom);
         box-shadow:0 -8px 32px rgba(0,0,0,.15);max-height:90vh;overflow-y:auto;
         transform:translateY(100%);transition:transform .3s cubic-bezier(.32,1,.5,1);">
  <div style="text-align:center;padding:14px 0 4px;">
    <div style="width:36px;height:4px;background:#e2e8f0;border-radius:2px;display:inline-block;"></div>
  </div>
  <div style="padding:0 20px 24px;">
    <div style="font-size:1rem;font-weight:800;color:#0f172a;margin-bottom:<?= (!empty($errors) && ($_POST['action']??'') === 'change_password') ? '12px' : '18px' ?>;">Change Password</div>

    <?php if (!empty($errors) && ($_POST['action'] ?? '') === 'change_password'): ?>
      <div style="padding:10px 12px;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;font-size:.8rem;color:#991b1b;margin-bottom:14px;">
        <?php foreach ($errors as $err): ?><div>· <?= e($err) ?></div><?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('app/responder/profile') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="change_password">
      <div style="margin-bottom:14px;">
        <label style="display:block;font-size:.72rem;font-weight:700;color:#64748b;margin-bottom:6px;">Current Password</label>
        <input type="password" name="current_password" required class="mob-input" autocomplete="current-password">
      </div>
      <div style="margin-bottom:14px;">
        <label style="display:block;font-size:.72rem;font-weight:700;color:#64748b;margin-bottom:6px;">New Password</label>
        <input type="password" name="new_password" required minlength="8" class="mob-input" autocomplete="new-password">
        <div style="font-size:.7rem;color:#94a3b8;margin-top:4px;">8+ chars · uppercase · lowercase · number · special character</div>
      </div>
      <div style="margin-bottom:20px;">
        <label style="display:block;font-size:.72rem;font-weight:700;color:#64748b;margin-bottom:6px;">Confirm New Password</label>
        <input type="password" name="confirm_password" required minlength="8" class="mob-input" autocomplete="new-password">
      </div>
      <button type="submit" class="mob-btn" style="background:#0f172a;">Update Password</button>
      <button type="button" onclick="closeAllModals()" class="mob-btn mob-btn-secondary" style="margin-top:10px;">Cancel</button>
    </form>
  </div>
</div>

<script>
function openModal(id) {
  document.getElementById('modal-backdrop').style.display = 'block';
  var m = document.getElementById(id);
  m.style.display = 'block';
  // Trigger transition
  requestAnimationFrame(function() {
    requestAnimationFrame(function() {
      m.style.transform = 'translateY(0)';
    });
  });
  document.body.style.overflow = 'hidden';
}
function closeAllModals() {
  ['modal-profile','modal-password'].forEach(function(id) {
    var m = document.getElementById(id);
    m.style.transform = 'translateY(100%)';
    setTimeout(function() { m.style.display = 'none'; }, 300);
  });
  document.getElementById('modal-backdrop').style.display = 'none';
  document.body.style.overflow = '';
}
// Auto-open modal if there were errors from a POST
<?php if (!empty($errors)): ?>
  window.addEventListener('DOMContentLoaded', function() {
    var action = '<?= e($_POST['action'] ?? '') ?>';
    if (action === 'change_password') openModal('modal-password');
    else openModal('modal-profile');
  });
<?php endif; ?>
</script>

  <!-- Logout -->
  <div style="padding: 0 16px 16px;">
    <a href="<?= base_url('app/logout') ?>"
       style="display:flex;align-items:center;justify-content:center;gap:8px;height:46px;background:#fff;border:1.5px solid #fecaca;border-radius:12px;font-size:.875rem;font-weight:700;color:#dc2626;text-decoration:none;">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Sign Out
    </a>
  </div>

  <div style="text-align:center;padding:0 16px 24px;">
    <p style="font-size:.7rem;color:#cbd5e1;margin:0;">ResQNow · Responder Portal</p>
  </div>

</div>
