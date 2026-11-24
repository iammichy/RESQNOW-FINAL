<?php $pageTitle = $pageTitle ?? 'ResQNow'; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
  <meta name="theme-color" content="#0f172a">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <link rel="manifest" href="<?= base_url('public/manifest.json') ?>">
  <title><?= e($pageTitle) ?> · ResQNow</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --navy:   #0f172a;
      --navy-light: #1e293b;
      --red:    #dc2626;
      --red-d:  #b91c1c;
      --ink:    #0f172a;
      --sub:    #64748b;
      --border: #e2e8f0;
      --bg:     #f8fafc;
      --radius: 12px;
      --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    html, body {
      height: 100%; min-height: 100dvh;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      -webkit-font-smoothing: antialiased;
      background: #fff;
      color: var(--ink);
      overflow: hidden; /* Prevent body scroll */
    }

    /* ── SHELL ─────────────────────────────────────────── */
    .auth-wrap {
      display: flex;
      height: 100dvh; /* Force height to viewport */
      overflow: hidden;
    }

    /* ── LEFT PANEL (Desktop Only) ─────────────────────── */
    .auth-left {
      width: 420px;
      height: 100%; /* Full height */
      flex-shrink: 0;
      background: var(--navy);
      display: none;
      flex-direction: column;
      justify-content: space-between;
      padding: 3rem;
      position: relative;
      overflow: hidden; /* Left stays fixed */
    }
    @media (min-width: 1024px) {
      .auth-left { display: flex; }
    }

    .auth-left__bg {
      position: absolute; inset: 0;
      background:
        linear-gradient(to bottom, rgba(15, 23, 42, 0.8), rgba(15, 23, 42, 0.95)),
        url('https://images.unsplash.com/photo-1582213782179-e0d53f98f2ca?q=80&w=1200&auto=format&fit=crop') center/cover no-repeat;
      z-index: 0;
    }

    .auth-left__content { position: relative; z-index: 1; height: 100%; display: flex; flex-direction: column; justify-content: space-between; }
    .auth-logo { display: flex; align-items: center; gap: 12px; text-decoration: none; }
    .auth-logo-icon {
      width: 42px; height: 42px; background: var(--red); border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
    }
    .auth-logo-name { font-size: 1.4rem; font-weight: 800; color: #fff; letter-spacing: -0.02em; }

    .auth-left__hero { margin: 2rem 0; }
    .auth-hero-tag {
      display: inline-flex; align-items: center; gap: 6px;
      background: rgba(220, 38, 38, 0.15); border: 1px solid rgba(220, 38, 38, 0.3);
      color: #fca5a5; font-size: 11px; font-weight: 700; text-transform: uppercase;
      padding: 4px 12px; border-radius: 100px; margin-bottom: 1.5rem;
    }
    .auth-hero-tag span { width: 6px; height: 6px; border-radius: 50%; background: #ef4444; display: inline-block; animation: blink 1.5s infinite; }
    @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.4; } }

    .auth-left__headline { font-size: 2.2rem; font-weight: 800; color: #fff; line-height: 1.2; letter-spacing: -0.03em; margin-bottom: 1.2rem; }
    .auth-left__sub { font-size: 0.95rem; color: rgba(255, 255, 255, 0.6); line-height: 1.6; max-width: 320px; }

    .auth-left__stats { display: flex; gap: 2.5rem; border-top: 1px solid rgba(255, 255, 255, 0.1); padding-top: 2rem; }
    .stat-item { display: flex; flex-direction: column; }
    .stat-val { font-size: 1.5rem; font-weight: 800; color: #fff; }
    .stat-lbl { font-size: 0.7rem; color: rgba(255, 255, 255, 0.4); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 2px; }

    /* ── RIGHT PANEL ────────────────────────────────────────── */
    .auth-right {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 3rem 2rem;
      background: #fff;
      overflow-y: auto;
      height: 100%;
    }
    .auth-container { 
      width: 100%; 
      max-width: 420px;
      margin: auto 0; /* Vertically center if content is small, otherwise allow scroll from top */
    }

    /* Mobile Header */
    .mobile-header { display: flex; align-items: center; gap: 10px; margin-bottom: 2rem; width: 100%; }
    @media (min-width: 1024px) { .mobile-header { display: none; } }

    .form-header { margin-bottom: 2.5rem; }
    .form-title { font-size: 1.75rem; font-weight: 800; color: var(--navy); letter-spacing: -0.03em; margin-bottom: 0.5rem; }
    .form-sub { font-size: 0.95rem; color: var(--sub); }

    /* ── FORMS ──────────────────────────────────────────────── */
    .f-group { margin-bottom: 1.25rem; }
    .f-label { display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 0.5rem; }
    .f-wrap { position: relative; }
    .f-inp {
      width: 100%; height: 52px; padding: 0 1rem 0 2.75rem;
      font-size: 0.95rem; font-family: inherit; color: var(--ink);
      background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: var(--radius);
      transition: all 0.2s;
    }
    .f-inp:focus { border-color: var(--red); background: #fff; box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.1); outline: none; }
    .f-inp::placeholder { color: #94a3b8; }
    .f-inp.no-icon { padding-left: 1rem; }

    .f-icon {
      position: absolute; left: 1rem; top: 50%; transform: translateY(-50%);
      color: #94a3b8; display: flex; align-items: center; pointer-events: none;
    }
    .f-eye {
      position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%);
      background: none; border: none; cursor: pointer; color: #94a3b8; padding: 0.5rem;
    }
    .f-eye:hover { color: var(--ink); }

    .f-row { display: flex; justify-content: space-between; align-items: center; }
    .f-link { font-size: 0.85rem; font-weight: 600; color: var(--red); text-decoration: none; }
    .f-link:hover { text-decoration: underline; }

    .f-btn {
      width: 100%; height: 52px; background: var(--red); color: #fff;
      border: none; border-radius: var(--radius); font-size: 1rem; font-weight: 700;
      cursor: pointer; transition: all 0.2s; margin-top: 1rem;
      display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .f-btn:hover { background: var(--red-d); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2); }
    .f-btn:active { transform: translateY(0); }

    .f-foot { margin-top: 2rem; text-align: center; font-size: 0.9rem; color: var(--sub); }
    .f-foot a { color: var(--red); font-weight: 600; text-decoration: none; }

    .f-alert {
      background: #fef2f2; border: 1px solid #fee2e2; border-radius: var(--radius);
      padding: 1rem; margin-bottom: 2rem; display: flex; gap: 12px;
      color: #991b1b; font-size: 0.85rem; line-height: 1.5;
    }

    /* Grid for registration */
    .f-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    @media (max-width: 640px) { .f-grid { grid-template-columns: 1fr; } }

    /* Custom Checkbox */
    .f-check { display: flex; align-items: flex-start; gap: 10px; cursor: pointer; margin-top: 0.5rem; }
    .f-check input { width: 18px; height: 18px; accent-color: var(--red); margin-top: 2px; }
    .f-check span { font-size: 0.85rem; color: var(--sub); line-height: 1.4; }
    /* Privacy Notice Styling */
    .privacy-box {
      background: #f1f5f9; border: 1px solid var(--border); border-radius: var(--radius);
      padding: 1rem; margin-top: 1.5rem; font-size: 0.78rem; color: #475569; line-height: 1.5;
    }
    .privacy-box strong { color: var(--navy); }
    .privacy-box a { color: var(--red); font-weight: 600; text-decoration: none; }
    /* ── MOBILE RESPONSIVE FIXES ───────────────────────────── */
    @media (max-width: 1023px) {
      html, body { overflow: auto; height: auto; }
      .auth-wrap { height: auto; flex-direction: column; overflow: visible; }
      .auth-right { height: auto; overflow: visible; padding: 2rem 1rem 4rem; }
    }
  </style>
</head>
<body>

<div class="auth-wrap">
  <!-- Sidebar -->
  <aside class="auth-left">
    <div class="auth-left__bg"></div>
    <div class="auth-left__content">
      <a href="<?= base_url() ?>" class="auth-logo">
        <div class="auth-logo-icon">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5">
            <path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z" fill="#fff" stroke="none"/>
          </svg>
        </div>
        <span class="auth-logo-name">ResQNow</span>
      </a>

      <div class="auth-left__hero">
        <h1 class="auth-left__headline">
          Emergency response<br>simplified.
        </h1>
        <p class="auth-left__sub">
          A dedicated platform for Barangay Camunatan to coordinate, monitor, and respond to incidents in real-time.
        </p>
      </div>

      <div class="auth-left__stats">
        <div class="stat-item">
          <span class="stat-val">24/7</span>
          <span class="stat-lbl">Monitoring</span>
        </div>
        <div class="stat-item">
          <span class="stat-val">4</span>
          <span class="stat-lbl">Response Teams</span>
        </div>
        <div class="stat-item">
          <span class="stat-val">8</span>
          <span class="stat-lbl">Hazard Types</span>
        </div>
      </div>
    </div>
  </aside>

  <!-- Form Area -->
  <main class="auth-right">
    <div class="auth-container">
      <div class="mobile-header">
        <div class="auth-logo-icon" style="width:36px; height:36px;">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5">
            <path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z" fill="#fff" stroke="none"/>
          </svg>
        </div>
        <span class="auth-logo-name" style="color:var(--navy); font-size:1.2rem;">ResQNow</span>
      </div>

      <?= $content ?>
    </div>
  </main>
</div>

</body>
</html>
