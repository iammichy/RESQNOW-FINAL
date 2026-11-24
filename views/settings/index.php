<?php
$activeTab = $_GET['tab'] ?? 'profile';

$typeIcons = [
    'barangay' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
    'rescue'   => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
    'police'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
    'fire'     => '<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>',
    'medical'  => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
    'utility'  => '<circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>',
    'social'   => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>',
    'other'    => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4a2 2 0 0 1 1.99-2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6.13 6.13l1.82-1.82a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>',
];
$typeLabels = ['barangay'=>'Barangay','rescue'=>'Rescue','police'=>'Police','fire'=>'Fire','medical'=>'Medical','utility'=>'Utility','social'=>'Social','other'=>'Other'];
$typeColors = ['barangay'=>['#eff6ff','#2563eb'],'rescue'=>['#fff7ed','#ea580c'],'police'=>['#eef2ff','#4f46e5'],'fire'=>['#fef2f2','#dc2626'],'medical'=>['#f0fdf4','#16a34a'],'utility'=>['#ecfeff','#0891b2'],'social'=>['#fdf4ff','#9333ea'],'other'=>['#f8fafc','#64748b']];
?>
<style>
/* ── Settings Page ──────────────────────────────── */
.st-wrap { padding: 24px 28px 40px; max-width: 900px; }

/* Page header */
.st-page-title { font-size: 1.25rem; font-weight: 800; color: #0f172a; letter-spacing: -.02em; margin: 0 0 2px; }
.st-page-sub   { font-size: .78rem; color: #64748b; margin: 0 0 20px; }

/* Tabs */
.st-tabs { display: flex; gap: 0; border-bottom: 2px solid #e2e8f0; margin-bottom: 24px; }
.st-tab  {
  display: flex; align-items: center; gap: 7px;
  padding: 10px 18px; font-size: .82rem; font-weight: 600;
  color: #94a3b8; border-bottom: 2px solid transparent;
  margin-bottom: -2px; cursor: pointer;
  text-decoration: none; transition: color .12s;
  white-space: nowrap;
}
.st-tab:hover { color: #0f172a; }
.st-tab.active { color: #dc2626; border-bottom-color: #dc2626; }
.st-tab svg { flex-shrink: 0; }

/* Section card */
.st-card {
  background: #fff; border: 1px solid #e2e8f0;
  border-radius: 12px; overflow: hidden; margin-bottom: 20px;
  box-shadow: 0 1px 3px rgba(0,0,0,.03);
}
.st-card-head {
  padding: 16px 22px; border-bottom: 1px solid #f1f5f9;
  background: #fafbfc; display: flex; align-items: center;
  justify-content: space-between;
}
.st-card-title { font-size: .875rem; font-weight: 700; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 8px; }
.st-card-body  { padding: 22px; }

/* Form fields */
.st-field { margin-bottom: 18px; }
.st-label { display: block; font-size: .8rem; font-weight: 700; color: #374151; margin-bottom: 7px; }
.st-input {
  width: 100%; height: 44px; padding: 0 14px;
  border: 1.5px solid #e2e8f0; border-radius: 9px;
  font-size: .875rem; color: #0f172a; background: #f8fafc;
  outline: none; font-family: inherit; transition: all .15s;
}
.st-input:focus { border-color: #dc2626; background: #fff; box-shadow: 0 0 0 3px rgba(220,38,38,.08); }
.st-row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
@media(max-width:600px){ .st-row2 { grid-template-columns: 1fr; } }

/* Save button */
.st-btn-save {
  height: 42px; padding: 0 24px;
  background: #dc2626; color: #fff; border: none;
  border-radius: 8px; font-size: .875rem; font-weight: 700;
  cursor: pointer; font-family: inherit;
  box-shadow: 0 2px 6px rgba(220,38,38,.25);
  transition: background .12s;
}
.st-btn-save:hover { background: #b91c1c; }

/* Toggle switches */
.st-toggle-row {
  display: flex; align-items: center; justify-content: space-between;
  padding: 16px 20px; border: 1.5px solid #e2e8f0;
  border-radius: 10px; margin-bottom: 12px;
  transition: border-color .12s;
}
.st-toggle-row:last-child { margin-bottom: 0; }
.st-toggle-row:hover { border-color: #cbd5e1; }
.st-toggle-title { font-size: .875rem; font-weight: 700; color: #0f172a; margin-bottom: 2px; }
.st-toggle-sub   { font-size: .75rem; color: #64748b; }

/* Contacts table */
.st-contacts-table { width: 100%; border-collapse: collapse; }
.st-contacts-table th { padding: 9px 14px; font-size: .62rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #94a3b8; border-bottom: 1px solid #e2e8f0; background: #f8fafc; text-align: left; }
.st-contacts-table td { padding: 11px 14px; border-bottom: 1px solid #f8fafc; vertical-align: middle; font-size: .8rem; }
.st-contacts-table tr:last-child td { border-bottom: none; }
.st-contacts-table tr:hover td { background: #fafbfc; }
.st-contacts-table tr.inactive td { opacity: .55; }
.st-unit-av { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.st-act-btn { height: 27px; padding: 0 10px; background: #fff; border-radius: 5px; font-size: .72rem; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; text-decoration: none; white-space: nowrap; transition: background .1s; }

/* Logo upload */
.st-logo-zone {
  border: 2px dashed #e2e8f0; border-radius: 12px;
  padding: 28px 20px; text-align: center; background: #f8fafc;
  cursor: pointer; transition: border-color .15s, background .15s;
}
.st-logo-zone:hover { border-color: #dc2626; background: #fff; }
.st-logo-preview { width: 96px; height: 96px; object-fit: contain; border-radius: 10px; border: 1px solid #e2e8f0; background: #fff; }
.st-logo-placeholder {
  width: 96px; height: 96px; border-radius: 10px;
  background: #f1f5f9; display: flex; align-items: center;
  justify-content: center; margin: 0 auto 10px; color: #94a3b8;
}

/* Triage table */
.st-triage-row { display: flex; align-items: center; padding: 13px 20px; border-bottom: 1px solid #f8fafc; gap: 16px; }
.st-triage-row:last-child { border-bottom: none; }
.st-triage-badge { display: inline-block; padding: 4px 12px; border-radius: 6px; font-size: .72rem; font-weight: 700; min-width: 80px; text-align: center; }
.st-triage-score { font-size: .9rem; font-weight: 800; color: #0f172a; min-width: 80px; }
.st-triage-action { font-size: .75rem; color: #94a3b8; margin-left: auto; }
</style>

<div class="st-wrap">

  <?php if ($flash): ?>
    <div style="padding:10px 14px;border-radius:8px;margin-bottom:16px;font-size:.8125rem;<?= $flash['type']==='success'?'background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;':'background:#fef2f2;border:1px solid #fecaca;color:#991b1b;' ?>;display:flex;align-items:center;gap:8px;">
      <?php if ($flash['type']==='success'): ?>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
      <?php else: ?>
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?php endif; ?>
      <?= e($flash['message']) ?>
    </div>
  <?php endif; ?>

  <!-- Page heading -->
  <div>
    <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:4px;">Configuration</div>
    <h1 class="st-page-title">System Settings</h1>
    <p class="st-page-sub">Manage barangay profile, system behaviour, and emergency contacts.</p>
  </div>

  <!-- Tabs -->
  <div class="st-tabs">
    <a href="?tab=profile" class="st-tab <?= $activeTab==='profile'?'active':'' ?>">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
      Barangay Profile
    </a>
    <a href="?tab=system" class="st-tab <?= $activeTab==='system'?'active':'' ?>">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
      System
    </a>
    <a href="?tab=contacts" class="st-tab <?= $activeTab==='contacts'?'active':'' ?>">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5-2.5 5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/></svg>
      Emergency Contacts
      <?php if (!empty($contacts)): ?>
        <span style="background:#f1f5f9;color:#475569;border-radius:4px;padding:1px 6px;font-size:.62rem;font-weight:700;"><?= count($contacts) ?></span>
      <?php endif; ?>
    </a>
    <a href="?tab=triage" class="st-tab <?= $activeTab==='triage'?'active':'' ?>">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4m0 4h.01"/><path d="M5 19h14a2 2 0 0 0 1.84-2.75L13.84 3.25a2 2 0 0 0-3.5 0L3.16 16.25A2 2 0 0 0 5 19z"/></svg>
      Triage Reference
    </a>
  </div>

  <!-- ═══ TAB: BARANGAY PROFILE ════════════════════════════════ -->
  <?php if ($activeTab === 'profile'): ?>
  <div class="st-card">
    <div class="st-card-head">
      <span class="st-card-title">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        Barangay Profile
      </span>
    </div>
    <div class="st-card-body">
      <form method="post" action="<?= base_url('settings/profile') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <!-- Logo upload -->
        <div class="st-field">
          <label class="st-label">Official Logo</label>
          <div class="st-logo-zone" id="logo-drop-zone">
            <div id="logo-preview-container" style="margin-bottom:10px;">
              <?php if (!empty($settings['barangay_logo'])): ?>
                <img src="<?= base_url('storage/logos/' . $settings['barangay_logo']) ?>" alt="Logo" class="st-logo-preview">
              <?php else: ?>
                <div class="st-logo-placeholder">
                  <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                </div>
              <?php endif; ?>
            </div>
            <div style="font-size:.8rem;color:#64748b;">
              <span style="color:#dc2626;font-weight:700;">Click to upload</span> or drag and drop
              <div style="font-size:.72rem;color:#94a3b8;margin-top:3px;">PNG, JPG or JPEG · max 2MB</div>
            </div>
            <input type="file" name="barangay_logo" id="logo-file-input" style="display:none;" accept="image/png,image/jpeg" onchange="previewLogo(this)">
          </div>
        </div>

        <!-- Barangay Name -->
        <div class="st-field">
          <label class="st-label">Barangay Name <span style="color:#dc2626;">*</span></label>
          <input type="text" name="barangay_name" class="st-input" required
            value="<?= e($settings['barangay_name'] ?? '') ?>"
            placeholder="e.g. Barangay Camunatan">
        </div>

        <!-- City + Contact -->
        <div class="st-row2">
          <div class="st-field">
            <label class="st-label">City / Municipality <span style="color:#dc2626;">*</span></label>
            <input type="text" name="barangay_city" class="st-input" required
              value="<?= e($settings['barangay_city'] ?? '') ?>"
              placeholder="City of Ilagan">
          </div>
          <div class="st-field">
            <label class="st-label">Primary Contact # <span style="color:#dc2626;">*</span></label>
            <input type="text" name="barangay_contact" class="st-input" required
              value="<?= e($settings['barangay_contact'] ?? '') ?>"
              placeholder="0997 692 0615">
          </div>
        </div>

        <div style="padding-top:16px;border-top:1px solid #f1f5f9;margin-top:4px;">
          <button type="submit" class="st-btn-save">Save Profile</button>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <!-- ═══ TAB: SYSTEM CONFIGURATION ══════════════════════════ -->
  <?php if ($activeTab === 'system'): ?>
  <div class="st-card">
    <div class="st-card-head">
      <span class="st-card-title">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06A1.65 1.65 0 0 0 15 19.4a1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        System Configuration
      </span>
    </div>
    <div class="st-card-body">
      <form method="post" action="<?= base_url('settings/system') ?>">
        <?= csrf_field() ?>

        <div class="st-toggle-row">
          <div>
            <div class="st-toggle-title">Resident Registration</div>
            <div class="st-toggle-sub">Allow new residents to register accounts via the mobile app.</div>
          </div>
          <label class="form-check form-switch m-0" style="cursor:pointer;">
            <input class="form-check-input shadow-none" type="checkbox" name="registration_enabled"
              style="width:40px;height:22px;cursor:pointer;"
              <?= ($settings['registration_enabled'] ?? '1') == '1' ? 'checked' : '' ?>>
          </label>
        </div>

        <div class="st-toggle-row" style="border-color:#fecaca;">
          <div>
            <div class="st-toggle-title" style="color:#dc2626;">Maintenance Mode</div>
            <div class="st-toggle-sub">Restrict access to all non-admin users. Use during updates.</div>
          </div>
          <label class="form-check form-switch m-0" style="cursor:pointer;">
            <input class="form-check-input shadow-none" type="checkbox" name="maintenance_mode"
              style="width:40px;height:22px;cursor:pointer;--tblr-form-check-input-checked-bg-color:#dc2626;"
              <?= ($settings['maintenance_mode'] ?? '0') == '1' ? 'checked' : '' ?>>
          </label>
        </div>

        <div style="padding-top:16px;border-top:1px solid #f1f5f9;margin-top:16px;">
          <button type="submit" class="st-btn-save">Apply Changes</button>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <!-- ═══ TAB: EMERGENCY CONTACTS ═════════════════════════════ -->
  <?php if ($activeTab === 'contacts'): ?>
  <div class="st-card">
    <div class="st-card-head">
      <span class="st-card-title">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5-2.5 5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/></svg>
        Emergency Contacts
        <span style="background:#f1f5f9;color:#475569;border-radius:4px;padding:1px 7px;font-size:.65rem;font-weight:700;margin-left:4px;"><?= count($contacts) ?></span>
      </span>
      <a href="<?= base_url('settings/contacts/new') ?>" style="display:inline-flex;align-items:center;gap:5px;height:32px;padding:0 12px;background:#dc2626;color:#fff;border-radius:6px;font-size:.75rem;font-weight:700;text-decoration:none;box-shadow:0 2px 5px rgba(220,38,38,.25);">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Contact
      </a>
    </div>
    <?php if (empty($contacts)): ?>
      <div style="text-align:center;padding:48px 20px;">
        <div style="width:52px;height:52px;border-radius:14px;background:#f8fafc;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><path d="M5 4h4l2 5l-2.5 1.5a11 11 0 0 0 5 5l1.5-2.5 5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/></svg>
        </div>
        <div style="font-size:.875rem;font-weight:700;color:#94a3b8;margin-bottom:4px;">No contacts added yet</div>
        <div style="font-size:.78rem;color:#cbd5e1;">Add emergency contact numbers for the barangay and response units.</div>
      </div>
    <?php else: ?>
      <div style="overflow-x:auto;">
        <table class="st-contacts-table">
          <thead>
            <tr>
              <th style="width:280px;">Unit / Contact Name</th>
              <th style="width:150px;">Phone Number</th>
              <th style="width:110px;">Category</th>
              <th style="width:90px;">Status</th>
              <th style="text-align:right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($contacts as $c):
              $isActive = (int)($c['is_active'] ?? 1);
              $type = $c['unit_type'] ?? 'other';
              [$iconBg,$iconColor] = $typeColors[$type] ?? ['#f8fafc','#64748b'];
              $iconPath = $typeIcons[$type] ?? $typeIcons['other'];
              $typeLabel = $typeLabels[$type] ?? ucfirst($type);
            ?>
              <tr class="<?= !$isActive ? 'inactive' : '' ?>">
                <td>
                  <div style="display:flex;align-items:center;gap:10px;">
                    <div class="st-unit-av" style="background:<?= $iconBg ?>;">
                      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="<?= $iconColor ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?= $iconPath ?></svg>
                    </div>
                    <div>
                      <div style="font-weight:700;color:#0f172a;font-size:.8rem;"><?= e($c['name']) ?></div>
                      <?php if (!empty($c['description'])): ?>
                        <div style="font-size:.7rem;color:#94a3b8;"><?= e($c['description']) ?></div>
                      <?php endif; ?>
                    </div>
                  </div>
                </td>
                <td>
                  <span style="font-family:monospace;font-size:.8rem;font-weight:600;color:#0f172a;"><?= e($c['phone']) ?></span>
                </td>
                <td>
                  <span style="display:inline-block;padding:2px 8px;border-radius:5px;font-size:.65rem;font-weight:700;background:<?= $iconBg ?>;color:<?= $iconColor ?>;">
                    <?= $typeLabel ?>
                  </span>
                </td>
                <td>
                  <?php if ($isActive): ?>
                    <span style="display:inline-flex;align-items:center;gap:4px;font-size:.7rem;font-weight:700;color:#16a34a;">
                      <span style="width:6px;height:6px;border-radius:50%;background:#16a34a;"></span>Active
                    </span>
                  <?php else: ?>
                    <span style="display:inline-flex;align-items:center;gap:4px;font-size:.7rem;font-weight:700;color:#94a3b8;">
                      <span style="width:6px;height:6px;border-radius:50%;background:#cbd5e1;"></span>Inactive
                    </span>
                  <?php endif; ?>
                </td>
                <td>
                  <div style="display:flex;align-items:center;gap:5px;justify-content:flex-end;">
                    <a href="<?= base_url('settings/contacts/' . $c['id'] . '/edit') ?>"
                       class="st-act-btn" style="border:1.5px solid #e2e8f0;color:#374151;">Edit</a>
                    <form method="post" action="<?= base_url('settings/contacts/' . $c['id'] . '/toggle') ?>">
                      <?= csrf_field() ?>
                      <button type="submit" class="st-act-btn"
                        style="<?= $isActive ? 'border:1.5px solid #fde68a;color:#d97706;' : 'border:1.5px solid #bbf7d0;color:#16a34a;' ?>">
                        <?= $isActive ? 'Deactivate' : 'Activate' ?>
                      </button>
                    </form>
                    <form method="post" action="<?= base_url('settings/contacts/' . $c['id'] . '/delete') ?>"
                          onsubmit="return confirm('Delete this contact permanently?')">
                      <?= csrf_field() ?>
                      <button type="submit" class="st-act-btn" style="border:1.5px solid #fecaca;color:#dc2626;">Delete</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- ═══ TAB: TRIAGE REFERENCE ════════════════════════════════ -->
  <?php if ($activeTab === 'triage'): ?>
  <div class="st-card">
    <div class="st-card-head">
      <span class="st-card-title">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><path d="M12 9v4m0 4h.01"/><path d="M5 19h14a2 2 0 0 0 1.84-2.75L13.84 3.25a2 2 0 0 0-3.5 0L3.16 16.25A2 2 0 0 0 5 19z"/></svg>
        Triage Priority Thresholds
      </span>
    </div>
    <!-- Description -->
    <div style="padding:14px 22px;border-bottom:1px solid #f1f5f9;background:#fafbfc;font-size:.78rem;color:#64748b;line-height:1.6;">
      These are the fixed thresholds used by the triage engine to assign priority levels. The score is computed as: <strong>Σ(answer points × question weight) + vulnerability bonus</strong>. Max score: 16. If ≥50% of answers are "Unsure", the level becomes <strong>Needs Reassessment</strong> regardless of score.
    </div>
    <?php foreach ([
      ['critical','#fef2f2','#dc2626','#fecaca','Critical','13 – 16','Immediate response required'],
      ['high','#fff7ed','#ea580c','#fed7aa','High','9 – 12','High priority dispatch'],
      ['moderate','#fffbeb','#d97706','#fde68a','Moderate','5 – 8','Standard response'],
      ['low','#f0fdf4','#16a34a','#bbf7d0','Low','1 – 4','Non-urgent / monitor'],
      ['pending_reassessment','#f1f5f9','#475569','#cbd5e1','Needs Reassessment','≥50% Unsure','Human assessment required'],
    ] as [$key,$bg,$color,$border,$label,$range,$action]): ?>
      <div class="st-triage-row">
        <span class="st-triage-badge" style="background:<?= $bg ?>;color:<?= $color ?>;border:1px solid <?= $border ?>;"><?= $label ?></span>
        <span class="st-triage-score"><?= $range ?></span>
        <span class="st-triage-action"><?= $action ?></span>
      </div>
    <?php endforeach; ?>
    <!-- Formula note -->
    <div style="padding:14px 22px;border-top:1px solid #f1f5f9;font-size:.75rem;color:#94a3b8;">
      <strong style="color:#0f172a;">Maximum score:</strong> 16 · <strong style="color:#0f172a;">Vulnerability bonus:</strong> +2 if senior/infant/PWD/pregnant/needs evacuation · <strong style="color:#0f172a;">Unsure threshold:</strong> ≥50% → Needs Reassessment
    </div>
  </div>
  <?php endif; ?>

</div>

<script>
// Logo upload
(function() {
  var zone  = document.getElementById('logo-drop-zone');
  var input = document.getElementById('logo-file-input');
  if (!zone) return;
  zone.addEventListener('click', function() { input.click(); });
  zone.addEventListener('dragover', function(e) { e.preventDefault(); zone.style.borderColor='#dc2626'; zone.style.background='#fff'; });
  zone.addEventListener('dragleave', function() { zone.style.borderColor=''; zone.style.background=''; });
  zone.addEventListener('drop', function(e) {
    e.preventDefault(); zone.style.borderColor=''; zone.style.background='';
    if (e.dataTransfer.files.length) { input.files = e.dataTransfer.files; previewLogo(input); }
  });
})();

function previewLogo(input) {
  if (!input.files || !input.files[0]) return;
  var reader = new FileReader();
  reader.onload = function(e) {
    var c = document.getElementById('logo-preview-container');
    c.innerHTML = '<img src="' + e.target.result + '" class="st-logo-preview" style="display:block;margin:0 auto 10px;">';
  };
  reader.readAsDataURL(input.files[0]);
}
</script>
