<?php
$pageTitle = $pageTitle ?? 'ResQNow';
$user      = auth();
$role      = $user['role'] ?? '';
$route     = current_route();

$unreadCount = $unreadCount ?? 0;

$tabs = [];
if ($role === 'resident') {
    $tabs = [
        ['href'=>'/app/resident',          'label'=>'Home',     'match'=>'/app/resident$',
         'icon'=>'<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>'],
        ['href'=>'/app/resident/track',    'label'=>'Track',    'match'=>'/app/resident/track',
         'icon'=>'<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/></svg>'],
        ['href'=>'/app/resident/report',   'label'=>'Report',   'match'=>'/app/resident/report', 'report'=>true,
         'icon'=>'<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>'],
        ['href'=>'/app/resident/contacts', 'label'=>'Contacts', 'match'=>'/app/resident/contacts',
         'icon'=>'<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4a2 2 0 0 1 1.99-2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6.13 6.13l1.82-1.82a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>'],
        ['href'=>'/app/resident/profile',  'label'=>'Profile',  'match'=>'/app/resident/profile|/app/resident/guides',
         'icon'=>'<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>'],
    ];
} elseif ($role === 'responder') {
    $tabs = [
        ['href'=>'/app/responder',           'label'=>'Missions', 'match'=>'/app/responder$',
         'icon'=>'<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>'],
        ['href'=>'/app/notifications',       'label'=>'Alerts',   'match'=>'/app/notifications',
         'icon'=>'<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>', 'badge'=>true],
        ['href'=>'/app/responder/contacts',  'label'=>'Contacts', 'match'=>'/app/responder/contacts',
         'icon'=>'<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4a2 2 0 0 1 1.99-2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6.13 6.13l1.82-1.82a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>'],
        ['href'=>'/app/responder/profile',   'label'=>'Profile',  'match'=>'/app/responder/profile',
         'icon'=>'<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>'],
    ];
}

$isActive = fn(string $pattern): bool => (bool) preg_match('#' . $pattern . '#', $route);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#dc2626">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="ResQNow">
  <link rel="apple-touch-icon" href="<?= base_url('public/assets/icons/icon-192x192.png') ?>">
  <link rel="manifest" href="<?= base_url('public/manifest.json') ?>">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="ResQNow">
  <meta name="msapplication-TileColor" content="#dc2626">
  <title><?= e($pageTitle) ?> · ResQNow</title>

  <!-- Google Fonts: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Bootstrap 5 core only (no Tabler admin styles) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= base_url('public/assets/vendor/leaflet/leaflet.css') ?>">

  <style>
    :root {
      --resq-red: #dc2626;
      --resq-red-dark: #b91c1c;
      --resq-slate: #1e293b;
      --resq-bg: #f8fafc;
      --tab-height: 68px;
      --topbar-height: 60px;
      --resq-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    }

    * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }

    html, body {
      height: 100%; margin: 0; padding: 0;
      background: var(--resq-bg);
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
      -webkit-font-smoothing: antialiased;
      overscroll-behavior: none;
      color: #1e293b;
    }

    /* Offline banner */
    #offline-banner {
      display: none; position: fixed; top: 0; left: 0; right: 0; z-index: 9999;
      background: #1e293b; color: #fff; text-align: center;
      padding: 10px 16px; font-size: 13px; font-weight: 600;
      backdrop-filter: blur(8px); background: rgba(30, 41, 59, 0.9);
    }
    #offline-banner.show { display: block; }

    /* Top app bar premium */
    .mob-topbar {
      position: fixed !important;
      top: 0; left: 0; right: 0;
      z-index: 1050 !important; /* above Tabler's z-index scale */
      height: var(--topbar-height);
      background: rgba(220, 38, 38, 0.97);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      color: #fff;
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 20px;
      box-shadow: 0 2px 12px rgba(220, 38, 38, 0.25);
    }
    .mob-topbar h1 { font-size: 18px; font-weight: 800; margin: 0; color: #fff; letter-spacing: -0.02em; }
    .mob-topbar .notif-btn {
      position: relative; background: rgba(255, 255, 255, 0.15); border: none; color: #fff;
      padding: 8px; cursor: pointer; width: 40px; height: 40px;
      display: flex; align-items: center; justify-content: center; border-radius: 12px;
      transition: all .2s;
    }
    .mob-topbar .notif-btn:active { transform: scale(0.9); background: rgba(255, 255, 255, 0.25); }
    .notif-badge {
      position: absolute; top: -4px; right: -4px;
      background: #fff; color: var(--resq-red);
      font-size: 10px; font-weight: 800; border-radius: 8px;
      min-width: 18px; height: 18px; line-height: 18px;
      text-align: center; padding: 0 4px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    /* Page content area */
    .mob-content {
      padding-top: calc(var(--topbar-height) + 12px);
      padding-bottom: calc(var(--tab-height) + env(safe-area-inset-bottom) + 20px);
      min-height: 100vh;
      overflow-y: auto;
    }

    /* Bottom tab bar */
    .mob-tabbar {
      position: fixed; bottom: 0; left: 0; right: 0; z-index: 100;
      height: calc(var(--tab-height) + env(safe-area-inset-bottom));
      padding-bottom: env(safe-area-inset-bottom);
      background: #fff;
      border-top: 1px solid #f1f5f9;
      display: flex; align-items: stretch;
      box-shadow: 0 -4px 20px rgba(0,0,0,0.06);
    }
    .mob-tab {
      flex: 1; display: flex; flex-direction: column;
      align-items: center; justify-content: center;
      text-decoration: none; color: #94a3b8;
      font-size: 10px; font-weight: 600; gap: 3px;
      padding: 6px 2px;
      transition: color .15s;
      -webkit-tap-highlight-color: transparent;
    }
    .mob-tab .tab-icon { display:flex;align-items:center;justify-content:center; }
    .mob-tab.active { color: var(--resq-red); }

    /* Report tab — prominent red button, same height as bar, no floating */
    .mob-tab.report-tab {
      flex: 1.1;
    }
    .mob-tab.report-tab .tab-icon {
      width: 46px; height: 32px;
      background: var(--resq-red);
      border-radius: 10px;
      color: #fff;
      box-shadow: 0 3px 10px rgba(220,38,38,.35);
    }
    .mob-tab.report-tab .tab-icon svg { stroke: #fff; }
    .mob-tab.report-tab span { color: var(--resq-red); font-weight: 700; }
    .mob-tab.report-tab:active .tab-icon { opacity: .85; transform: scale(.95); }

    /* Remove all old FAB styles */
    .mob-tab.fab-tab .fab-circle { display: none; }

    /* Cards premium */
    .mob-card {
      background: #fff; border-radius: 20px;
      box-shadow: var(--resq-shadow);
      margin: 0 16px 16px; overflow: hidden;
      border: 1px solid rgba(226, 232, 240, 0.5);
    }
    .mob-card-header { padding: 20px 20px 0; }
    .mob-card-header h2 { font-size: 17px; font-weight: 800; margin: 0 0 4px; color: #1e293b; letter-spacing: -0.01em; }
    .mob-card-body { padding: 16px 20px; }

    /* Forms premium */
    .mob-form-group { margin-bottom: 20px; }
    .mob-form-group label { display: block; font-size: 13px; font-weight: 700; color: #64748b; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.05em; }
    .mob-input {
      width: 100%; padding: 14px 16px; border: 2px solid #f1f5f9;
      border-radius: 14px; font-size: 16px;
      background: #f8fafc; transition: all .2s; outline: none;
      color: #1e293b; font-weight: 500;
    }
    .mob-input:focus { border-color: var(--resq-red); background: #fff; box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.1); }

    /* Install banner */
    #install-banner {
      display: none; position: fixed; bottom: calc(var(--tab-height) + env(safe-area-inset-bottom) + 8px);
      left: 12px; right: 12px; z-index: 200;
      background: #1e293b; color: #fff; border-radius: 12px;
      padding: 14px 16px; box-shadow: 0 8px 24px rgba(0,0,0,.3);
    }
    #install-banner.show { display: flex; align-items: center; gap: 12px; }
    #install-banner .install-icon { font-size: 28px; flex-shrink: 0; }
    #install-banner .install-text { flex: 1; font-size: 13px; }
    #install-banner .install-text strong { display: block; margin-bottom: 2px; }
    #btn-install { background: var(--resq-red); color: #fff; border: none; border-radius: 8px; padding: 8px 16px; font-weight: 600; font-size: 13px; white-space: nowrap; }
    #btn-dismiss-install { background: none; border: none; color: #94a3b8; font-size: 20px; padding: 4px; cursor: pointer; }

    /* Triage badges */
    .badge-critical { background: #dc2626; color: #fff; }
    .badge-high     { background: #ea580c; color: #fff; }
    .badge-moderate { background: #d97706; color: #fff; }
    .badge-low      { background: #16a34a; color: #fff; }
    .badge-pending  { background: #334155; color: #fff; }
    .mob-badge { display: inline-block; border-radius: 6px; padding: 2px 8px; font-size: 11px; font-weight: 600; }

    /* Form styles */
    .mob-form-group { margin-bottom: 16px; }
    .mob-form-group label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 6px; }
    .mob-input {
      width: 100%; padding: 12px 14px; border: 1.5px solid #e2e8f0;
      border-radius: 10px; font-size: 16px; /* 16px prevents iOS zoom */
      background: #fff; transition: border-color .15s; outline: none;
    }
    .mob-input:focus { border-color: var(--resq-red); }
    .mob-input.error { border-color: #dc2626; }

    .mob-btn {
      display: block; width: 100%; padding: 14px;
      background: var(--resq-red); color: #fff;
      border: none; border-radius: 12px; font-size: 16px; font-weight: 700;
      cursor: pointer; text-align: center; text-decoration: none;
    }
    .mob-btn:active { background: var(--resq-red-dark); }
    .mob-btn-outline {
      background: none; border: 2px solid var(--resq-red); color: var(--resq-red);
    }
    .mob-btn-secondary {
      background: #f1f5f9; color: #334155; border: none;
    }
    .mob-btn-success { background: #16a34a; }
    .mob-btn-danger  { background: #dc2626; }
    .mob-btn-warning { background: #d97706; }

    .error-text { color: #dc2626; font-size: 12px; margin-top: 4px; }
    .section-label { font-size: 11px; font-weight: 700; letter-spacing: .05em; color: #94a3b8; text-transform: uppercase; padding: 0 16px; margin: 16px 0 8px; }
    .list-item { display: flex; align-items: center; gap: 12px; padding: 14px 16px; background: #fff; border-bottom: 1px solid #f1f5f9; text-decoration: none; color: inherit; }
    .list-item:last-child { border-bottom: none; }
    .list-item:active { background: #f8fafc; }
    .timeline-item { display: flex; gap: 12px; padding: 10px 0; }
    .timeline-dot { width: 32px; flex-shrink: 0; display: flex; flex-direction: column; align-items: center; }
    .timeline-dot .dot { width: 10px; height: 10px; border-radius: 50%; background: #dc2626; margin-top: 4px; }
    .timeline-dot .line { width: 2px; flex: 1; background: #e2e8f0; margin-top: 4px; }
    .timeline-content { flex: 1; padding-bottom: 8px; }
    .timeline-text { font-size: 13px; font-weight: 600; color: #1e293b; }
    .timeline-sub  { font-size: 11px; color: #64748b; margin-top: 2px; }

    /* Step indicator */
    .step-bar { display: flex; gap: 4px; padding: 0 12px 12px; }
    .step-dot { flex: 1; height: 4px; border-radius: 2px; background: #e2e8f0; }
    .step-dot.active { background: var(--resq-red); }
    .step-dot.done   { background: #16a34a; }
  </style>
  <?= $extraHead ?? '' ?>
</head>
<body>

<!-- Offline banner -->
<div id="offline-banner">
  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" style="margin-right:6px;vertical-align:-2px;"><line x1="1" y1="1" x2="23" y2="23"/><path d="M16.72 11.06A10.94 10.94 0 0 1 19 12.55"/><path d="M5 12.55a10.94 10.94 0 0 1 5.17-2.39"/><path d="M10.71 5.05A16 16 0 0 1 22.56 9"/><path d="M1.42 9a15.91 15.91 0 0 1 4.7-2.88"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
  You are offline — some features may be unavailable.
</div>

<!-- Top app bar -->
<div class="mob-topbar">
  <div style="display:flex;align-items:center;gap:10px;">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z" fill="#fff"/></svg>
    <h1><?= e($pageTitle) ?></h1>
  </div>
  <?php if ($user): ?>
    <button class="notif-btn" onclick="location.href='<?= base_url('app/notifications') ?>'" aria-label="Notifications">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
        <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
      </svg>
      <?php if ($unreadCount > 0): ?>
        <span class="notif-badge" id="notif-badge"><?= min(99, $unreadCount) ?></span>
      <?php else: ?>
        <span class="notif-badge" id="notif-badge" style="display:none;">0</span>
      <?php endif; ?>
    </button>
  <?php endif; ?>
</div>

<!-- Page content -->
<div class="mob-content">
  <?= $content ?>
</div>

<!-- Bottom tab bar -->
<?php if (!empty($tabs)): ?>
<nav class="mob-tabbar" role="navigation" aria-label="Main navigation">
  <?php foreach ($tabs as $tab): ?>
    <?php $active = $isActive($tab['match']); ?>
    <a href="<?= base_url(ltrim($tab['href'], '/')) ?>"
       class="mob-tab <?= $active ? 'active' : '' ?> <?= !empty($tab['report']) ? 'report-tab' : '' ?>"
       aria-label="<?= e($tab['label']) ?>">
      <span class="tab-icon" style="position:relative;">
        <?= $tab['icon'] ?>
        <?php if (!empty($tab['badge']) && $unreadCount > 0): ?>
          <span style="position:absolute;top:-4px;right:-5px;background:#dc2626;color:#fff;border-radius:9px;font-size:9px;font-weight:800;padding:1px 4px;min-width:15px;height:15px;display:flex;align-items:center;justify-content:center;line-height:1;"><?= min(99,$unreadCount) ?></span>
        <?php endif; ?>
      </span>
      <span><?= e($tab['label']) ?></span>
    </a>
  <?php endforeach; ?>
</nav>
<?php endif; ?>

<!-- Install banner -->
<div id="install-banner" role="complementary" aria-label="Install app">
  <div class="install-icon" style="width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
  </div>
  <div class="install-text">
    <strong>Install ResQNow</strong>
    Add to your home screen for quick access.
  </div>
  <button id="btn-install">Install</button>
  <button id="btn-dismiss-install" aria-label="Dismiss" style="color:#94a3b8;background:none;border:none;font-size:20px;padding:4px;cursor:pointer;">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
  </button>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('public/assets/vendor/leaflet/leaflet.js') ?>"></script>

<script>
// ── Service Worker registration ───────────────────────────────────────
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('<?= base_url('public/sw.js') ?>', { scope: '/public/' })
      .then(reg => {
        reg.addEventListener('updatefound', () => {
          const nw = reg.installing;
          nw.addEventListener('statechange', () => {
            if (nw.state === 'installed' && navigator.serviceWorker.controller) {
              if (confirm('A new version of ResQNow is available. Reload now?')) {
                nw.postMessage({ type: 'SKIP_WAITING' });
                location.reload();
              }
            }
          });
        });
      })
      .catch(err => console.warn('SW registration failed:', err));
  });
}

// ── Offline/online banner ─────────────────────────────────────────────
function updateOnlineStatus() {
  const banner = document.getElementById('offline-banner');
  if (!navigator.onLine) {
    banner.classList.add('show');
  } else {
    banner.classList.remove('show');
  }
}
window.addEventListener('online',  updateOnlineStatus);
window.addEventListener('offline', updateOnlineStatus);
updateOnlineStatus();

// ── Install prompt ────────────────────────────────────────────────────
let deferredPrompt = null;
window.addEventListener('beforeinstallprompt', e => {
  e.preventDefault();
  deferredPrompt = e;
  if (!localStorage.getItem('resqnow_install_dismissed')) {
    document.getElementById('install-banner').classList.add('show');
  }
});
document.getElementById('btn-install')?.addEventListener('click', () => {
  if (!deferredPrompt) return;
  deferredPrompt.prompt();
  deferredPrompt.userChoice.then(choice => {
    if (choice.outcome === 'accepted') {
      document.getElementById('install-banner').classList.remove('show');
    }
    deferredPrompt = null;
  });
});
document.getElementById('btn-dismiss-install')?.addEventListener('click', () => {
  document.getElementById('install-banner').classList.remove('show');
  localStorage.setItem('resqnow_install_dismissed', '1');
});
window.addEventListener('appinstalled', () => {
  document.getElementById('install-banner').classList.remove('show');
  deferredPrompt = null;
});

// ── Notification badge polling (every 30s) ───────────────────────────
<?php if ($user): ?>
(function pollNotifications() {
  function update() {
    fetch('<?= base_url('api/v1/notifications/unread-count') ?>', { credentials: 'same-origin' })
      .then(r => r.ok ? r.json() : null)
      .then(data => {
        if (!data) return;
        const badge = document.getElementById('notif-badge');
        if (!badge) return;
        const count = data.unread_count || 0;
        badge.textContent = Math.min(99, count);
        badge.style.display = count > 0 ? 'block' : 'none';
      })
      .catch(() => {});
  }
  setInterval(update, 30000);
})();
<?php endif; ?>

// ── CSRF token helper for fetch() calls ──────────────────────────────
window.CSRF_TOKEN = '<?= csrf_token() ?>';
window.apiPost = async function(url, body) {
  return fetch(url, {
    method: 'POST',
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': window.CSRF_TOKEN,
    },
    body: JSON.stringify(body),
  });
};
</script>

<?= $extraScripts ?? '' ?>
</body>
</html>
