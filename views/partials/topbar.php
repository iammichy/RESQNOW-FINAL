<?php
$user = auth();
$userName = $user['name'] ?? 'User';
$userInitial = strtoupper(mb_substr($userName, 0, 1));
?>
<header class="navbar navbar-expand-lg d-print-none sticky-top resq-topbar shadow-none">
  <div class="container-fluid">

    <!-- Sidebar Toggler (Mobile) -->
    <button class="border-0 shadow-none bg-transparent p-2 d-lg-none" id="sidebar-toggle-btn" type="button" aria-label="Toggle sidebar" style="cursor:pointer;color:#1e293b;">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>

    <!-- Mobile Branding (Hidden on Desktop) -->
    <a href="<?= base_url('dashboard') ?>" class="navbar-brand d-lg-none me-auto ms-1">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-primary rounded-2 d-flex align-items-center justify-content-center" style="width:28px; height:28px; box-shadow: 0 0 10px rgba(220, 38, 38, 0.2);">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
            </div>
            <span class="fs-4 fw-bold text-slate tracking-tight">ResQNow</span>
        </div>
    </a>

    <!-- Desktop Search -->
    <div class="d-none d-lg-flex align-items-center gap-3">
        <div class="d-none d-md-flex align-items-center">
            <form class="topbar-search" action="<?= base_url('reports') ?>" method="get">
                <div class="input-icon">
                    <span class="input-icon-addon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    </span>
                    <input type="text" name="q" class="form-control border-0" placeholder="Search reports, residents..." aria-label="Search">
                </div>
            </form>
        </div>
    </div>

    <!-- Actions Area (Always visible on mobile) -->
    <div class="d-flex align-items-center ms-auto gap-1 gap-md-2">

        <!-- Notification Center -->
        <div class="nav-item dropdown">
          <a href="#" class="nav-link px-0" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-label="Notifications" tabindex="-1">
            <div class="p-2 text-slate opacity-75 hover-opacity-100 transition-all" style="position:relative;">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
              <span id="notif-badge" class="badge badge-notification bg-red" style="display:none;"></span>
            </div>
          </a>
          <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow dropdown-menu-card border-0 shadow-lg mt-2" style="width:20rem;">
            <div class="card border-0">
              <div class="card-header bg-white py-2 px-3 border-bottom d-flex align-items-center justify-content-between">
                <h3 class="card-title x-small fw-bold text-uppercase tracking-wider mb-0">Notifications</h3>
                <span id="notif-unread-count" class="badge bg-red-lt text-red fw-bold" style="font-size:.62rem;display:none;"></span>
              </div>
              <div class="list-group list-group-flush" id="notif-list" style="max-height:320px;overflow-y:auto;">
                <div class="list-group-item text-center text-muted small py-4">Loading…</div>
              </div>
              <div class="card-footer bg-light-lt py-2 text-center border-top">
                <a href="<?= base_url('notifications') ?>" class="x-small fw-bold text-primary text-decoration-none">View all notifications →</a>
              </div>
            </div>
          </div>
        </div>

        <!-- User Quick Actions -->
        <div class="nav-item dropdown ms-1">
          <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
            <div class="d-flex align-items-center gap-2 ps-2">
                <div class="avatar avatar-sm rounded-circle border shadow-none fw-bold" style="background:#f1f5f9; color:#1e293b;">
                    <?= e($userInitial) ?>
                </div>
            </div>
          </a>
          <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow shadow-lg border-0 mt-2" style="min-width: 180px;">
            <div class="dropdown-header text-muted x-small fw-bold text-uppercase tracking-wider mb-1">Account</div>
            <a href="<?= base_url('profile') ?>" class="dropdown-item py-1 px-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2 text-muted"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                My Profile
            </a>
            <div class="dropdown-divider"></div>
            <a href="<?= base_url('logout') ?>" class="dropdown-item py-1 px-3 text-danger">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Logout
            </a>
          </div>
        </div>

    </div>
  </div>
</header>

<script>
(function() {
  var BASE = '<?= base_url('') ?>';

  function loadNotifications() {
    fetch(BASE + 'api/v1/notifications?limit=6', { credentials: 'same-origin' })
      .then(function(r) { return r.ok ? r.json() : null; })
      .then(function(d) {
        if (!d || !d.notifications) return;
        var list = document.getElementById('notif-list');
        var badge = document.getElementById('notif-badge');
        var countEl = document.getElementById('notif-unread-count');
        if (!list) return;

        var items = d.notifications.slice(0, 6);
        var unread = items.filter(function(n) { return !n.is_read; }).length;

        if (unread > 0) {
          badge.style.display = '';
          countEl.textContent = unread + ' new';
          countEl.style.display = '';
        } else {
          badge.style.display = 'none';
          countEl.style.display = 'none';
        }

        if (items.length === 0) {
          list.innerHTML = '<div class="list-group-item text-center text-muted small py-4">No notifications</div>';
          return;
        }

        list.innerHTML = items.map(function(n) {
          var rawLink = (n.link || '').replace(/^\//,'');
          // Guard: if the link targets the mobile PWA but we're on the admin portal,
          // redirect to the notifications page instead to avoid 403.
          var isAppLink = rawLink.indexOf('app/') === 0;
          var href = rawLink ? (isAppLink ? BASE + 'notifications' : BASE + rawLink) : '#';
          var unreadStyle = n.is_read ? '' : 'background:#fffbeb;';
          var dot = n.is_read ? '' : '<span style="width:7px;height:7px;border-radius:50%;background:#dc2626;flex-shrink:0;margin-top:4px;"></span>';
          return '<a href="' + href + '" class="list-group-item list-group-item-action py-2 px-3" style="' + unreadStyle + '">' +
            '<div style="display:flex;align-items:flex-start;gap:8px;">' +
              dot +
              '<div style="flex:1;min-width:0;">' +
                '<div class="fw-bold text-slate small text-truncate">' + (n.title || '') + '</div>' +
                '<div class="text-muted x-small text-truncate">' + (n.message || '') + '</div>' +
              '</div>' +
            '</div>' +
          '</a>';
        }).join('');
      })
      .catch(function() {});
  }

  // Load on dropdown open
  var dropdownEl = document.querySelector('[data-bs-toggle="dropdown"][aria-label="Notifications"]');
  if (dropdownEl) {
    dropdownEl.addEventListener('click', loadNotifications);
  }

  // Poll unread count every 30s
  function pollCount() {
    fetch(BASE + 'api/v1/notifications/unread-count', { credentials: 'same-origin' })
      .then(function(r) { return r.ok ? r.json() : null; })
      .then(function(d) {
        if (!d) return;
        var badge = document.getElementById('notif-badge');
        var countEl = document.getElementById('notif-unread-count');
        if (!badge) return;
        var n = parseInt(d.unread_count || 0);
        badge.style.display = n > 0 ? '' : 'none';
        if (countEl) { countEl.textContent = n + ' new'; countEl.style.display = n > 0 ? '' : 'none'; }
      })
      .catch(function() {});
  }
  pollCount();
  setInterval(pollCount, 30000);
})();
</script>