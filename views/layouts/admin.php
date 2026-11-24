<?php
$pageTitle = $pageTitle ?? 'ResQNow';
$flash = $this->flash() ?? null;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <meta http-equiv="X-UA-Compatible" content="ie=edge" />
  <title><?= e($pageTitle) ?> · ResQNow</title>

  <!-- Google Fonts: Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- Tabler core (Bootstrap 5 bundled) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.6.1/dist/css/tabler.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.6.1/dist/css/tabler-vendors.min.css" />

  <!-- Leaflet (for incident map pages) -->
  <link rel="stylesheet" href="<?= asset('assets/vendor/leaflet/leaflet.css') ?>" />

  <style>
    :root {
      --tblr-primary: #dc2626;
      --tblr-primary-rgb: 220, 38, 38;
      --tblr-primary-dark: #b91c1c;
      --tblr-primary-light: #fecaca;
      --resq-slate: #1e293b;
      --resq-slate-dark: #0f172a;
      --resq-bg: #f8fafc;
      --resq-card-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
      --resq-card-shadow-hover: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    }

    body { 
      background: var(--resq-bg);
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
      -webkit-font-smoothing: antialiased;
      font-size: 0.8125rem;
      margin: 0;
      padding: 0;
      overflow-x: hidden;
    }

    .page {
      min-height: 100vh;
      display: flex;
      flex-direction: row;
      margin: 0;
      padding: 0;
    }

    @media (max-width: 991.98px) {
      .page {
        flex-direction: column;
        display: block; /* Break flex to avoid top spacing issues in mobile */
      }
    }

    /* Custom Scrollbar */
    ::-webkit-scrollbar { width: 5px; height: 5px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

    /* Custom Grid for 5 columns */
    .stat-grid-container {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      gap: 0.75rem;
      width: 100%;
      margin-bottom: 1.25rem;
    }

    @media (max-width: 1440px) {
      .stat-grid-container {
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      }
    }

    @media (max-width: 767.98px) {
      .stat-grid-container {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.5rem;
      }
      .stat-grid-item:last-child {
        grid-column: span 2;
      }
    }

    @media (max-width: 480px) {
      .stat-grid-container {
        gap: 0.35rem;
      }
      .stat-card-premium {
        padding: 0.5rem 0.625rem;
      }
      .stat-card-premium .stat-value {
        font-size: 1.1rem;
      }
    }

    /* Sidebar Enhancements - Premium Slate Theme */
    .resq-sidebar {
      background: #1e293b;
      color: #f1f5f9;
      border-right: 1px solid rgba(255,255,255,0.05);
      width: 260px;
      box-shadow: 10px 0 30px rgba(0,0,0,.1);
      z-index: 1040;
      position: sticky;
      top: 0;
      height: 100vh;
      display: flex;
      flex-direction: column;
      flex-shrink: 0;
      overflow: hidden;
    }

    .resq-sidebar-content {
      flex: 1;
      overflow-y: auto;
      scrollbar-width: thin;
      scrollbar-color: rgba(255,255,255,0.1) transparent;
    }

    /* Custom Scrollbar for Sidebar Content */
    .resq-sidebar-content::-webkit-scrollbar { width: 5px; }
    .resq-sidebar-content::-webkit-scrollbar-track { background: transparent; }
    .resq-sidebar-content::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
    .resq-sidebar-content::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }

    @media (max-width: 991.98px) {
      .resq-sidebar {
        position: fixed !important;
        left: 0 !important;
        top: 0 !important;
        bottom: 0 !important;
        width: 260px !important;
        height: 100vh !important;
        visibility: hidden !important;
        opacity: 0 !important;
        transform: translateX(-100%) !important;
        transition: transform .3s cubic-bezier(0.4, 0, 0.2, 1), visibility .3s ease, opacity .3s ease !important;
        display: flex !important;
        flex-direction: column !important;
        z-index: 2000 !important;
        background: #0f172a !important;
        border: none !important;
      }
      .resq-sidebar.show {
        visibility: visible !important;
        opacity: 1 !important;
        transform: translateX(0) !important;
        box-shadow: 20px 0 50px rgba(0,0,0,.5) !important;
      }
      /* Ensure children behave in column */
      .resq-sidebar > * {
        width: 100% !important;
      }

      .resq-sidebar .nav-link {
        padding: 0.75rem 1.25rem !important;
        margin: 0.15rem 0.75rem !important;
        font-size: 0.875rem !important;
        color: #94a3b8 !important;
        display: flex !important;
        align-items: center !important;
      }
      .resq-sidebar .nav-link.active {
        color: #fff !important;
        background: rgba(220, 38, 38, 0.1) !important;
      }
      .sidebar-section-title {
        color: #94a3b8 !important;
        opacity: 1 !important;
        margin-left: 1.25rem !important;
      }
    }

    .resq-sidebar .navbar-brand-container {
      padding: 1.25rem 1rem;
      margin-bottom: 0.5rem;
      border-bottom: 1px solid rgba(255,255,255,0.05);
      flex-shrink: 0;
    }

    .resq-sidebar .navbar-nav {
      list-style: none;
      padding: 0;
      margin: 0;
      display: flex;
      flex-direction: column;
    }

    .resq-sidebar .nav-item {
      width: 100%;
    }

    .resq-sidebar .nav-link {
      color: #cbd5e1;
      border-radius: 6px;
      margin: 0.15rem 0.65rem;
      padding: 0.5rem 0.75rem;
      font-weight: 500;
      font-size: 0.8125rem;
      transition: all .2s cubic-bezier(0.4, 0, 0.2, 1);
      display: flex;
      align-items: center;
    }

    .resq-sidebar .nav-link:hover {
      background: rgba(255,255,255,.06);
      color: #fff;
    }

    .resq-sidebar .nav-link.active {
      background: rgba(220, 38, 38, 0.06);
      color: #fff;
      font-weight: 600;
      box-shadow: inset 2px 0 0 0 #dc2626;
    }

    .resq-sidebar .nav-link.active .nav-link-icon {
      color: #dc2626;
      filter: none;
    }

    .resq-sidebar .nav-link-icon { 
      color: #94a3b8; 
      margin-right: 0.75rem; 
      width: 1rem; 
      height: 1rem;
      transition: color .2s;
    }
    
    .resq-sidebar .nav-link:hover .nav-link-icon {
      color: #cbd5e1;
    }

    .sidebar-section-title {
      font-size: 0.625rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: .07em;
      color: #94a3b8;
      margin: 1.25rem 0.85rem 0.35rem;
      opacity: 1;
    }

    /* Sidebar Footer Refined - SaaS Style */
    .sidebar-footer {
      margin-top: auto;
      padding: 0.75rem 0.65rem;
      border-top: 1px solid rgba(255,255,255,.05);
    }
    
    .sidebar-profile-card {
      padding: 0.5rem 0.625rem;
      background: transparent;
      border-radius: 6px;
      display: flex;
      align-items: center;
      gap: 10px;
      text-decoration: none;
      transition: all .15s ease;
      color: #f1f5f9;
      cursor: pointer;
    }
    
    .sidebar-profile-card:hover {
      background: rgba(255,255,255,.05);
      text-decoration: none;
      color: #fff;
    }

    .sidebar-profile-card .avatar {
      width: 24px; height: 24px;
      border-radius: 4px;
      background: #334155;
      color: #fff;
      font-size: 0.7rem;
      font-weight: 700;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
      border: 1px solid rgba(255,255,255,0.05);
    }

    .sidebar-profile-info { line-height: 1.1; overflow: hidden; margin-left: 2px; }
    .sidebar-profile-name { font-size: 0.75rem; font-weight: 600; color: #f1f5f9; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .sidebar-profile-role { font-size: 0.6rem; color: #64748b; font-weight: 500; text-transform: capitalize; margin-top: 1px; }
    
    .bg-slate-darker {
      background-color: #1e293b !important;
      border: 1px solid rgba(255,255,255,0.08) !important;
      padding: 0.35rem;
      min-width: 160px;
    }

    .dropdown-item {
      border-radius: 4px;
      font-size: 0.75rem;
      padding: 0.4rem 0.65rem;
      color: #94a3b8;
      font-weight: 500;
      display: flex;
      align-items: center;
    }

    .dropdown-item:hover {
      background: rgba(255,255,255,0.05);
      color: #fff;
    }

    .dropdown-item.text-danger:hover {
      background: rgba(239, 68, 68, 0.1);
      color: #f87171 !important;
    }

    .dropdown-item-icon {
      width: 14px;
      height: 14px;
      margin-right: 8px;
      opacity: 0.5;
    }

    .dropdown-divider {
      margin: 0.35rem 0.5rem;
      border-top: 1px solid rgba(255,255,255,0.05);
    }

    .btn-manual-report {
      background: #dc2626;
      border: none;
      color: #fff;
      border-radius: 8px;
      font-weight: 700;
      font-size: 0.75rem;
      padding: 0.75rem 1rem;
      box-shadow: 0 4px 12px rgba(220,38,38,.25);
      transition: all .2s cubic-bezier(0.4, 0, 0.2, 1);
      text-transform: uppercase;
      letter-spacing: 0.02em;
    }

    .btn-manual-report:hover {
      background: #b91c1c;
      color: #fff;
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(220,38,38,.35);
    }

    .btn-manual-report:active {
      transform: translateY(0);
    }

    .hover-opacity-100:hover { opacity: 1 !important; }
    .x-small { font-size: 0.65rem; }
    .tracking-tight { letter-spacing: -0.02em; }
    .leading-tight { line-height: 1.25; }

    /* Topbar Enhancements */
    .resq-topbar {
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      background: rgba(255, 255, 255, 0.95) !important;
      border-bottom: 1px solid #e2e8f0 !important;
      padding: 0 1.5rem;
      height: 56px;
      min-height: 56px;
      z-index: 1030;
      position: sticky !important;
      top: 0 !important;
    }

    @media (max-width: 991.98px) {
      .resq-topbar {
        padding: 0 0.75rem;
      }
    }

    @media (max-width: 767.98px) {
      .resq-topbar {
        height: 56px;
        min-height: 56px;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
      }
    }

    .topbar-search .input-icon .form-control {
      background: #f1f5f9;
      border: 1px solid transparent;
      border-radius: 8px;
      padding-left: 2.25rem;
      font-size: 0.8125rem;
      width: 100%;
      max-width: 320px;
      height: 36px;
      transition: all .2s;
    }
    .topbar-search .input-icon .form-control:focus {
      background: #fff;
      border-color: #e2e8f0;
      box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
      max-width: 380px;
    }

    /* Stat Cards Premium Refined */
    .stat-grid-item > a { display: block; height: 100%; color: inherit; }
    .stat-grid-item > a:hover { text-decoration: none; }
    .stat-card-premium {
      background: #fff;
      border-radius: 8px;
      padding: 0.875rem 1rem;
      border: 1px solid #e2e8f0;
      box-shadow: 0 1px 2px rgba(0,0,0,.02);
      display: flex;
      flex-direction: column;
      height: 100%;
      position: relative;
      transition: all .2s ease;
    }
    .stat-card-premium:hover {
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      border-color: #cbd5e1;
      transform: translateY(-2px);
    }
    .stat-card-premium.border-top-wide {
      border-top-width: 3px !important;
    }
    .stat-card-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 0.35rem;
    }
    .stat-card-premium .stat-label {
      font-size: 0.625rem;
      font-weight: 800;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    .stat-card-premium .icon-wrapper {
      width: 28px;
      height: 28px;
      border-radius: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
    .stat-card-premium .stat-value {
      font-size: 1.5rem;
      font-weight: 800;
      color: #1e293b;
      line-height: 1.1;
      margin-bottom: 0.25rem;
      letter-spacing: -0.03em;
    }
    .stat-card-premium .stat-description {
      font-size: 0.725rem;
      font-weight: 500;
      color: #94a3b8;
      margin: 0;
    }

    /* Layout Adjustments */
    .page-wrapper {
      transition: none; /* Removed transition to fix sticky issues */
      flex: 1;
      min-width: 0;
      display: flex;
      flex-direction: column;
      margin-left: 0 !important; /* Force zero margin as we use flex layout */
    }

    @media (max-width: 991.98px) {
      .page-wrapper {
        width: 100%;
        padding-top: 56px; /* Offset for fixed topbar */
      }
    }

    /* Premium Table/Card */
    .card-premium {
      background: #fff;
      border-radius: 10px;
      border: 1px solid #e2e8f0;
      box-shadow: 0 1px 2px rgba(0,0,0,.05);
      overflow: hidden;
    }
    .card-premium .card-header {
      background: transparent;
      padding: 0.875rem 1.25rem;
      border-bottom: 1px solid #f1f5f9;
    }
    .card-premium .card-title {
      font-size: 0.875rem;
      font-weight: 700;
      color: #1e293b;
    }

    /* Footer Premium */
    .resq-footer {
      background: transparent;
      padding: 1.5rem 0;
      border-top: 1px solid #e2e8f0;
      margin-top: 1.5rem;
    }
    .resq-footer .footer-text {
      font-size: 0.75rem;
      color: #64748b;
    }
    .resq-footer .footer-link {
      color: #64748b;
      text-decoration: none;
      font-weight: 500;
      transition: color .2s;
    }
    .resq-footer .footer-link:hover {
      color: var(--tblr-primary);
    }

    /* Layout & Cards */
    .page-wrapper { background: #f8fafc; }
    .card { border: 1px solid #e2e8f0; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,.05); }
    .card-header { padding: 0.75rem 1rem; border-bottom: 1px solid #f1f5f9; }
    .card-title { font-size: 0.875rem; font-weight: 700; color: #1e293b; }

    .stat-card-new {
      padding: 1rem;
      background: #fff;
      border-radius: 10px;
      border: 1px solid #e2e8f0;
    }
    .stat-card-new .icon-box {
      width: 32px; height: 32px;
      border-radius: 6px;
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 0.75rem;
    }
    .stat-card-new .value { font-size: 1.25rem; font-weight: 700; color: #1e293b; line-height: 1; margin-bottom: 0.15rem; }
    .stat-card-new .label { font-size: 0.7rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.02em; }
    
    .urgent-report-item {
      border: 1px solid #f1f5f9;
      background: #fff;
      border-radius: 6px;
      margin-bottom: 0.5rem;
      padding: 0.75rem 1rem;
      transition: all .2s ease;
      display: block;
      text-decoration: none;
      color: inherit;
    }
    .urgent-report-item:hover { border-color: #e2e8f0; background: #f8fafc; }
    .urgent-report-item .triage-tag {
      font-size: 0.625rem; font-weight: 700; text-transform: uppercase;
      padding: 1px 6px; border-radius: 4px;
    }

    .live-indicator {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 3px 8px;
      background: #fff1f2;
      border: 1px solid #ffe4e6;
      color: #e11d48;
      border-radius: 100px;
      font-size: 0.65rem;
      font-weight: 700;
      text-transform: uppercase;
    }
    .live-dot-pulse {
      width: 6px; height: 6px;
      background: #e11d48;
      border-radius: 50%;
      animation: resq-pulse-red 2s infinite;
    }
    @keyframes resq-pulse-red {
      0% { box-shadow: 0 0 0 0 rgba(225, 29, 72, 0.4); }
      70% { box-shadow: 0 0 0 6px rgba(225, 29, 72, 0); }
      100% { box-shadow: 0 0 0 0 rgba(225, 29, 72, 0); }
    }
  </style>
  <?= $extraHead ?? '' ?>
</head>
<body><div class="page">
  <!-- Mobile sidebar overlay -->
  <div id="sidebar-overlay" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:1999;backdrop-filter:blur(2px);opacity:0;transition:opacity .25s ease;" onclick="closeSidebar()"></div>

  <?php require APP_ROOT . '/app/views/partials/sidebar.php'; ?>
  <div class="page-wrapper">
    <?php require APP_ROOT . '/app/views/partials/topbar.php'; ?>

    <div class="page-body">
      <div class="container-fluid px-lg-4">

        <?php require APP_ROOT . '/app/views/partials/alerts.php'; ?>

        <?= $content ?>

      </div>
    </div>

    <footer class="resq-footer d-print-none">
      <div class="container-fluid px-lg-4">
        <div class="row align-items-center justify-content-center justify-content-lg-between flex-column flex-lg-row-reverse text-center text-lg-start">
          <div class="col-lg-auto mb-3 mb-lg-0">
            <ul class="list-inline list-inline-dots mb-0 footer-text">
              <li class="list-inline-item"><a href="#" class="footer-link">Documentation</a></li>
              <li class="list-inline-item"><a href="#" class="footer-link">Privacy Policy</a></li>
              <li class="list-inline-item"><a href="#" class="footer-link">Support</a></li>
            </ul>
          </div>
          <div class="col-lg-auto">
            <div class="footer-text d-flex align-items-center justify-content-center justify-content-lg-start gap-2">
              <span>&copy; <?= date('Y') ?> <span class="fw-bold text-slate">ResQNow</span>.</span>
              <span class="d-none d-md-inline opacity-50">•</span>
              <span class="d-none d-sm-inline-block">Barangay Emergency Response System.</span>
              <span class="badge bg-slate-lt">v1.0.0</span>
            </div>
          </div>
        </div>
      </div>
    </footer>
  </div>
</div>

<!-- Tabler JS (includes Bootstrap 5) -->
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.6.1/dist/js/tabler.min.js"></script>
<!-- ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts@7.6.1/dist/apexcharts.min.js"></script>
<!-- Leaflet -->
<script src="<?= asset('assets/vendor/leaflet/leaflet.js') ?>"></script>

<script>
// ── Mobile sidebar toggle ─────────────────────────────────────────
function openSidebar() {
  var sb  = document.getElementById('resq-sidebar');
  var ov  = document.getElementById('sidebar-overlay');
  if (sb) sb.classList.add('show');
  if (ov) { ov.style.display = 'block'; setTimeout(function(){ ov.style.opacity='1'; },10); }
  document.body.style.overflow = 'hidden';
}
function closeSidebar() {
  var sb  = document.getElementById('resq-sidebar');
  var ov  = document.getElementById('sidebar-overlay');
  if (sb) sb.classList.remove('show');
  if (ov) { ov.style.opacity='0'; setTimeout(function(){ ov.style.display='none'; },250); }
  document.body.style.overflow = '';
}
document.addEventListener('DOMContentLoaded', function(){
  var btn = document.getElementById('sidebar-toggle-btn');
  if (btn) btn.addEventListener('click', function(){
    var sb = document.getElementById('resq-sidebar');
    if (sb && sb.classList.contains('show')) { closeSidebar(); }
    else { openSidebar(); }
  });
  // Close sidebar when a nav link is clicked on mobile
  var links = document.querySelectorAll('.resq-sidebar .nav-link');
  links.forEach(function(l){
    l.addEventListener('click', function(){
      if (window.innerWidth < 992) closeSidebar();
    });
  });
});
// Close on resize to desktop
window.addEventListener('resize', function(){
  if (window.innerWidth >= 992) closeSidebar();
});
</script>

<?= $extraScripts ?? '' ?>
</body>
</html>
