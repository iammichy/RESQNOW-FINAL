<?php
$user    = $user    ?? auth();
$errors  = $errors  ?? [];
$success = $success ?? null;
?>

<!-- Flash -->
<?php if ($success): ?>
  <div style="margin:12px 16px 0;padding:11px 14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;font-size:.8rem;color:#166534;font-weight:600;">
    <?= e($success) ?>
  </div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
  <div style="margin:12px 16px 0;padding:11px 14px;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;font-size:.8rem;color:#991b1b;">
    <?php foreach ($errors as $err): ?><div>· <?= e($err) ?></div><?php endforeach; ?>
  </div>
<?php endif; ?>

<!-- Avatar + name -->
<div style="text-align:center;padding:24px 24px 20px;">
  <div style="width:80px;height:80px;border-radius:50%;background:#f1f5f9;border:3px solid #e2e8f0;
              display:flex;align-items:center;justify-content:center;
              font-size:2rem;font-weight:800;color:#475569;margin:0 auto 14px;">
    <?= strtoupper(substr($user['name'] ?? 'R', 0, 1)) ?>
  </div>
  <div style="font-size:1.1rem;font-weight:800;color:#0f172a;margin-bottom:4px;"><?= e($user['name']) ?></div>
  <div style="font-size:.8rem;color:#64748b;"><?= e($user['email'] ?? $user['phone'] ?? '') ?></div>
  <div style="margin-top:8px;">
    <span style="display:inline-block;background:#eff6ff;color:#2563eb;border-radius:6px;padding:3px 12px;font-size:.68rem;font-weight:700;">Resident</span>
  </div>
</div>

<!-- Navigation links (moved from bottom nav) -->
<div style="font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;padding:4px 16px 8px;">Navigation</div>
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;margin:0 16px 14px;overflow:hidden;">

  <a href="<?= base_url('app/resident') ?>" style="display:flex;align-items:center;gap:12px;padding:14px 16px;text-decoration:none;color:inherit;border-bottom:1px solid #f1f5f9;">
    <div style="width:36px;height:36px;border-radius:10px;background:#f8fafc;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.75"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
    </div>
    <div style="flex:1;font-size:.875rem;font-weight:600;color:#0f172a;">Home</div>
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
  </a>

  <a href="<?= base_url('app/resident/report') ?>" style="display:flex;align-items:center;gap:12px;padding:14px 16px;text-decoration:none;color:inherit;border-bottom:1px solid #f1f5f9;">
    <div style="width:36px;height:36px;border-radius:10px;background:#fef2f2;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
    </div>
    <div style="flex:1;font-size:.875rem;font-weight:600;color:#0f172a;">Report Emergency</div>
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
  </a>

  <a href="<?= base_url('app/resident/track') ?>" style="display:flex;align-items:center;gap:12px;padding:14px 16px;text-decoration:none;color:inherit;border-bottom:1px solid #f1f5f9;">
    <div style="width:36px;height:36px;border-radius:10px;background:#f8fafc;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.75"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/></svg>
    </div>
    <div style="flex:1;font-size:.875rem;font-weight:600;color:#0f172a;">Track My Reports</div>
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
  </a>

  <a href="<?= base_url('app/resident/guides') ?>" style="display:flex;align-items:center;gap:12px;padding:14px 16px;text-decoration:none;color:inherit;border-bottom:1px solid #f1f5f9;">
    <div style="width:36px;height:36px;border-radius:10px;background:#f8fafc;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.75"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
    </div>
    <div style="flex:1;font-size:.875rem;font-weight:600;color:#0f172a;">Safety Guides</div>
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
  </a>

  <a href="<?= base_url('app/resident/contacts') ?>" style="display:flex;align-items:center;gap:12px;padding:14px 16px;text-decoration:none;color:inherit;">
    <div style="width:36px;height:36px;border-radius:10px;background:#f8fafc;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.75"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4a2 2 0 0 1 1.99-2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6.13 6.13l1.82-1.82a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
    </div>
    <div style="flex:1;font-size:.875rem;font-weight:600;color:#0f172a;">Emergency Contacts</div>
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
  </a>
</div>

<!-- Account info -->
<div style="font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;padding:4px 16px 8px;">Account Information</div>
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;margin:0 16px 14px;overflow:hidden;">
  <div style="display:flex;align-items:center;justify-content:space-between;padding:13px 16px;border-bottom:1px solid #f8fafc;">
    <div>
      <div style="font-size:.72rem;font-weight:600;color:#94a3b8;margin-bottom:2px;">Phone</div>
      <div style="font-size:.875rem;font-weight:700;color:#0f172a;"><?= e($user['phone'] ?? '—') ?></div>
    </div>
  </div>
  <div style="display:flex;align-items:center;justify-content:space-between;padding:13px 16px;">
    <div>
      <div style="font-size:.72rem;font-weight:600;color:#94a3b8;margin-bottom:2px;">Email</div>
      <div style="font-size:.875rem;font-weight:700;color:#0f172a;"><?= e($user['email'] ?: '—') ?></div>
    </div>
  </div>
</div>

<!-- Settings -->
<div style="font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;padding:4px 16px 8px;">Account Settings</div>
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;margin:0 16px 14px;overflow:hidden;">

  <button onclick="openModal('modal-profile')"
    style="width:100%;display:flex;align-items:center;gap:12px;padding:14px 16px;background:none;border:none;text-align:left;cursor:pointer;border-bottom:1px solid #f1f5f9;">
    <div style="width:36px;height:36px;border-radius:10px;background:#eff6ff;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    </div>
    <div style="flex:1;font-size:.875rem;font-weight:600;color:#0f172a;">Edit Profile</div>
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
  </button>

  <button onclick="openModal('modal-password')"
    style="width:100%;display:flex;align-items:center;gap:12px;padding:14px 16px;background:none;border:none;text-align:left;cursor:pointer;border-bottom:1px solid #f1f5f9;">
    <div style="width:36px;height:36px;border-radius:10px;background:#fef2f2;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
    </div>
    <div style="flex:1;font-size:.875rem;font-weight:600;color:#0f172a;">Change Password</div>
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
  </button>

  <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;">
    <div style="width:36px;height:36px;border-radius:10px;background:#f0fdf4;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
    </div>
    <div style="flex:1;">
      <div style="font-size:.875rem;font-weight:600;color:#0f172a;">Push Notifications</div>
      <div style="font-size:.72rem;color:#64748b;">Emergency alerts enabled</div>
    </div>
    <div style="width:42px;height:24px;background:#16a34a;border-radius:12px;position:relative;flex-shrink:0;">
      <div style="position:absolute;top:2px;right:2px;width:20px;height:20px;background:#fff;border-radius:50%;box-shadow:0 1px 3px rgba(0,0,0,.2);"></div>
    </div>
  </div>
</div>

<!-- App info -->
<div style="font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;padding:4px 16px 8px;">Support & Info</div>
<div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;margin:0 16px 14px;overflow:hidden;">
  <div style="display:flex;align-items:center;justify-content:space-between;padding:13px 16px;border-bottom:1px solid #f1f5f9;">
    <span style="font-size:.875rem;font-weight:600;color:#0f172a;">App Version</span>
    <span style="font-size:.8rem;color:#94a3b8;font-weight:600;">1.0.0-pwa</span>
  </div>
  <div style="display:flex;align-items:center;justify-content:space-between;padding:13px 16px;">
    <span style="font-size:.875rem;font-weight:600;color:#0f172a;">Account Status</span>
    <span style="background:#f0fdf4;color:#16a34a;border-radius:5px;padding:3px 10px;font-size:.68rem;font-weight:700;"><?= ucfirst($user['status'] ?? 'active') ?></span>
  </div>
</div>

<!-- Logout -->
<div style="padding:0 16px 16px;">
  <a href="<?= base_url('app/logout') ?>"
     style="display:flex;align-items:center;justify-content:center;gap:8px;height:46px;background:#fff;border:1.5px solid #fecaca;border-radius:12px;font-size:.875rem;font-weight:700;color:#dc2626;text-decoration:none;">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
    Sign Out
  </a>
</div>

<div style="text-align:center;padding:0 16px 24px;">
  <p style="font-size:.7rem;color:#cbd5e1;margin:0;">ResQNow · Resident Portal</p>
</div>

<!-- Modal backdrop -->
<div id="modal-backdrop" onclick="closeAllModals()"
  style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;backdrop-filter:blur(2px);"></div>

<!-- Edit Profile Modal -->
<div id="modal-profile"
  style="display:none;position:fixed;bottom:0;left:0;right:0;z-index:1001;
         background:#fff;border-radius:20px 20px 0 0;padding:0 0 env(safe-area-inset-bottom);
         box-shadow:0 -8px 32px rgba(0,0,0,.15);max-height:90vh;overflow-y:auto;
         transform:translateY(100%);transition:transform .3s cubic-bezier(.32,1,.5,1);">
  <div style="text-align:center;padding:14px 0 4px;">
    <div style="width:36px;height:4px;background:#e2e8f0;border-radius:2px;display:inline-block;"></div>
  </div>
  <div style="padding:0 20px 24px;">
    <div style="font-size:1rem;font-weight:800;color:#0f172a;margin-bottom:18px;">Edit Profile</div>
    <form method="post" action="<?= base_url('app/resident/profile') ?>">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="update_info">
      <div style="margin-bottom:14px;">
        <label style="display:block;font-size:.72rem;font-weight:700;color:#64748b;margin-bottom:6px;">Full Name</label>
        <input type="text" name="name" value="<?= e($user['name'] ?? '') ?>" required class="mob-input">
      </div>
      <div style="margin-bottom:14px;">
        <label style="display:block;font-size:.72rem;font-weight:700;color:#64748b;margin-bottom:6px;">Phone Number</label>
        <input type="tel" name="phone" value="<?= e($user['phone'] ?? '') ?>" required
          pattern="[0-9\+\-\s\(\)]{7,20}" class="mob-input">
      </div>
      <div style="margin-bottom:20px;">
        <label style="display:block;font-size:.72rem;font-weight:700;color:#64748b;margin-bottom:6px;">Email Address</label>
        <input type="email" name="email" value="<?= e($user['email'] ?? '') ?>" class="mob-input">
      </div>
      <button type="submit" class="mob-btn">Save Changes</button>
      <button type="button" onclick="closeAllModals()" class="mob-btn mob-btn-secondary" style="margin-top:10px;">Cancel</button>
    </form>
  </div>
</div>

<!-- Change Password Modal -->
<div id="modal-password"
  style="display:none;position:fixed;bottom:0;left:0;right:0;z-index:1001;
         background:#fff;border-radius:20px 20px 0 0;padding:0 0 env(safe-area-inset-bottom);
         box-shadow:0 -8px 32px rgba(0,0,0,.15);max-height:90vh;overflow-y:auto;
         transform:translateY(100%);transition:transform .3s cubic-bezier(.32,1,.5,1);">
  <div style="text-align:center;padding:14px 0 4px;">
    <div style="width:36px;height:4px;background:#e2e8f0;border-radius:2px;display:inline-block;"></div>
  </div>
  <div style="padding:0 20px 24px;">
    <div style="font-size:1rem;font-weight:800;color:#0f172a;margin-bottom:18px;">Change Password</div>
    <form method="post" action="<?= base_url('app/resident/profile') ?>">
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
  requestAnimationFrame(function() {
    requestAnimationFrame(function() { m.style.transform = 'translateY(0)'; });
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
<?php if (!empty($errors)): ?>
window.addEventListener('DOMContentLoaded', function() {
  var action = '<?= e($_POST['action'] ?? '') ?>';
  if (action === 'change_password') openModal('modal-password');
  else openModal('modal-profile');
});
<?php endif; ?>
</script>
