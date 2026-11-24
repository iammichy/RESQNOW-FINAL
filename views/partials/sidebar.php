<?php
$current = current_route();
$isActive = function (string $path) use ($current): bool {
    if ($path === '/' && $current === '/') return true;
    if ($path !== '/' && str_starts_with($current, $path)) return true;
    return false;
};
$authUser = auth();
$userName = $authUser['name'] ?? 'Administrator';
$userInitial = strtoupper(mb_substr($userName, 0, 1));
?>
<aside class="resq-sidebar" id="resq-sidebar">
  
  <div class="navbar-brand-container d-flex align-items-center justify-content-between">
    <a href="<?= base_url('dashboard') ?>" class="d-flex align-items-center text-decoration-none gap-2">
      <div class="bg-primary rounded-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width:32px; height:32px; box-shadow: 0 0 15px rgba(220, 38, 38, 0.3);">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          </svg>
      </div>
      <div class="overflow-hidden">
          <div class="fs-3 fw-bold text-white tracking-tight leading-tight text-truncate">ResQNow</div>
          <div class="text-muted x-small text-uppercase fw-bold opacity-50 tracking-widest text-truncate" style="font-size: 0.6rem;">Barangay Web Admin</div>
      </div>
    </a>
    
    <!-- Close Button (Mobile Only) -->
    <button type="button" class="btn-close btn-close-white d-lg-none shadow-none" onclick="closeSidebar()" aria-label="Close"></button>
  </div>

  <div class="resq-sidebar-content">
    
    <div class="sidebar-section-title">Overview</div>
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link <?= $isActive('/dashboard') ? 'active' : '' ?>" href="<?= base_url('dashboard') ?>">
          <span class="nav-link-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>
          </span>
          <span class="nav-link-title">Dashboard</span>
        </a>
      </li>
    </ul>

    <div class="sidebar-section-title">Operations</div>
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link <?= $isActive('/reports') ? 'active' : '' ?>" href="<?= base_url('reports') ?>">
          <span class="nav-link-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/></svg>
          </span>
          <span class="nav-link-title">All Reports</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $isActive('/verification') ? 'active' : '' ?>" href="<?= base_url('verification') ?>">
          <span class="nav-link-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
          </span>
          <span class="nav-link-title">Verification</span>
          <?php if (!empty($pendingVerificationCount)): ?>
            <span style="display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;background:#dc2626;color:#fff;border-radius:5px;font-size:.65rem;font-weight:700;padding:0 5px;margin-left:auto;line-height:1;"><?= (int) $pendingVerificationCount ?></span>
          <?php endif; ?>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $isActive('/prioritization') ? 'active' : '' ?>" href="<?= base_url('prioritization') ?>">
          <span class="nav-link-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
          </span>
          <span class="nav-link-title">Dispatch</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $isActive('/monitoring') ? 'active' : '' ?>" href="<?= base_url('monitoring') ?>">
          <span class="nav-link-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
          </span>
          <span class="nav-link-title">Monitoring</span>
          <?php if (!empty($pendingSupportCount)): ?>
            <span style="display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;background:#dc2626;color:#fff;border-radius:5px;font-size:.65rem;font-weight:700;padding:0 5px;margin-left:auto;line-height:1;"><?= (int) $pendingSupportCount ?></span>
          <?php endif; ?>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $isActive('/analytics') ? 'active' : '' ?>" href="<?= base_url('analytics') ?>">
          <span class="nav-link-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
          </span>
          <span class="nav-link-title">Analytics</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $isActive('/map') ? 'active' : '' ?>" href="<?= base_url('map') ?>">
          <span class="nav-link-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          </span>
          <span class="nav-link-title">Incident Map</span>
        </a>
      </li>
    </ul>

    <div class="sidebar-section-title">Response</div>
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link <?= $isActive('/teams') ? 'active' : '' ?>" href="<?= base_url('teams') ?>">
          <span class="nav-link-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          </span>
          <span class="nav-link-title">Response Teams</span>
        </a>
      </li>
      <?php /* Responders use the mobile PWA — no admin sidebar link needed */ ?>
    </ul>

    <div class="sidebar-section-title">Management</div>
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link <?= $isActive('/residents') ? 'active' : '' ?>" href="<?= base_url('residents') ?>">
          <span class="nav-link-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          </span>
          <span class="nav-link-title">Residents</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $isActive('/personnel') ? 'active' : '' ?>" href="<?= base_url('personnel') ?>">
          <span class="nav-link-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          </span>
          <span class="nav-link-title">Personnel</span>
        </a>
      </li>
      <li class="nav-item">
          <a class="nav-link <?= $isActive('/announcements') ? 'active' : '' ?>" href="<?= base_url('announcements') ?>">
            <span class="nav-link-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            </span>
            <span class="nav-link-title">Announcements</span>
          </a>
        </li>
    </ul>

    <div class="sidebar-section-title">System</div>
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link <?= $isActive('/notifications') ? 'active' : '' ?>" href="<?= base_url('notifications') ?>">
          <span class="nav-link-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
          </span>
          <span class="nav-link-title">Notifications</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $isActive('/audit-logs') ? 'active' : '' ?>" href="<?= base_url('audit-logs') ?>">
          <span class="nav-link-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          </span>
          <span class="nav-link-title">Audit Logs</span>
        </a>
      </li>
      <li class="nav-item">
          <a class="nav-link <?= $isActive('/settings') ? 'active' : '' ?>" href="<?= base_url('settings') ?>">
            <span class="nav-link-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            </span>
            <span class="nav-link-title">Settings & Roles</span>
          </a>
      </li>
    </ul>

    <div class="mt-auto px-3 py-3">
      <a href="<?= base_url('reports/new') ?>" class="btn btn-manual-report w-100">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="me-2">
          <path d="M12 5v14M5 12h14"/>
        </svg>
        Add Manual Report
      </a>
    </div>

    <div class="sidebar-footer">
      <div class="dropup">
        <a href="#" class="sidebar-profile-card" data-bs-toggle="dropdown" aria-expanded="false">
          <div class="avatar"><?= e($userInitial) ?></div>
          <div class="sidebar-profile-info">
            <div class="sidebar-profile-name"><?= e($userName) ?></div>
            <div class="sidebar-profile-role"><?= e($authUser['role'] ?? 'admin') ?></div>
          </div>
          <div class="ms-auto opacity-50">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
          </div>
        </a>
        <div class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-lg border-0 bg-slate-darker">
          <a class="dropdown-item py-2" href="<?= base_url('profile') ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" /><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" /></svg>
            My Profile
          </a>
          <div class="dropdown-divider opacity-10"></div>
          <a class="dropdown-item py-2 text-danger" href="<?= base_url('logout') ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon dropdown-item-icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" /><path d="M9 12h12l-3 -3" /><path d="M18 15l3 -3" /></svg>
            Sign out
          </a>
        </div>
      </div>
    </div>
  </div>
</aside>