<?php
$pageTitle = $pageTitle ?? 'ResQNow';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <title><?= e($pageTitle) ?> · ResQNow</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --red:       #dc2626;
      --red-dark:  #b91c1c;
      --red-light: rgba(220,38,38,.12);
      --slate-950: #0a0f1e;
      --slate-900: #0f172a;
      --slate-800: #1e293b;
      --slate-600: #475569;
      --slate-400: #94a3b8;
      --slate-200: #e2e8f0;
      --slate-100: #f1f5f9;
      --white:     #ffffff;
      --radius:    14px;
    }

    html, body {
      height: 100%;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background: var(--slate-950);
      color: #1e293b;
      -webkit-font-smoothing: antialiased;
    }

    .auth-shell {
      display: flex;
      min-height: 100vh;
    }

    /* ── LEFT PHOTO PANEL ─────────────────────────────────────── */
    .auth-photo {
      flex: 1;
      position: relative;
      display: none;
      overflow: hidden;
    }
    @media (min-width: 1024px) {
      .auth-photo { display: flex; flex-direction: column; justify-content: space-between; }
    }

    .auth-photo__bg {
      position: absolute; inset: 0;
      background:
        /* vignette dark overlay */
        linear-gradient(
          to right,
          rgba(10,15,30,.72) 0%,
          rgba(10,15,30,.52) 60%,
          rgba(10,15,30,.18) 100%
        ),
        /* subtle bottom fade for text legibility */
        linear-gradient(
          to top,
          rgba(10,15,30,.85) 0%,
          transparent 50%
        ),
        /* emergency scene photo via Unsplash (no API key needed) */
        url('https://images.unsplash.com/photo-1626958390905-5b87a3f1dd3e?w=1200&q=80&fit=crop')
        center / cover no-repeat;
    }

    /* fallback if image fails to load */
    .auth-photo__bg::after {
      content: '';
      position: absolute; inset: 0;
      background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
      opacity: 0;
      transition: opacity .3s;
    }

    .auth-photo__content {
      position: relative; z-index: 1;
      padding: 2.5rem 3rem;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      height: 100%;
    }

    /* Brand */
    .auth-logo {
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
    }
    .auth-logo__icon {
      width: 42px; height: 42px;
      background: var(--red);
      border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      box-shadow: 0 4px 16px rgba(220,38,38,.45);
      flex-shrink: 0;
    }
    .auth-logo__name {
      font-size: 1.4rem;
      font-weight: 800;
      color: #fff;
      letter-spacing: -.02em;
    }

    /* Hero text */
    .auth-hero {
      margin: 2rem 0;
    }
    .auth-hero__tag {
      display: none; /* Removed as requested */
    }
    .auth-hero__tag span { width: 6px; height: 6px; border-radius: 50%; background: #ef4444; display: inline-block; animation: blink 1.4s infinite; }
    @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }

    .auth-hero__headline {
      font-size: clamp(1.75rem, 2.5vw, 2.4rem);
      font-weight: 800;
      color: #fff;
      line-height: 1.15;
      letter-spacing: -.03em;
      margin-bottom: 1rem;
    }
    .auth-hero__headline em {
      font-style: normal;
      color: #fca5a5;
    }
    .auth-hero__sub {
      color: rgba(255,255,255,.65);
      font-size: .9rem;
      line-height: 1.65;
      max-width: 360px;
      margin-bottom: 2rem;
    }

    /* Stats row */
    .auth-stats {
      display: flex;
      gap: 1.5rem;
      padding-top: 1.5rem;
      border-top: 1px solid rgba(255,255,255,.1);
    }
    .auth-stat__num {
      font-size: 1.5rem;
      font-weight: 800;
      color: #fff;
      line-height: 1;
    }
    .auth-stat__label {
      font-size: .72rem;
      color: rgba(255,255,255,.5);
      letter-spacing: .04em;
      text-transform: uppercase;
      margin-top: 2px;
    }

    /* Footer */
    .auth-photo__footer {
      color: rgba(255,255,255,.35);
      font-size: .75rem;
      padding: 0;
    }

    /* ── RIGHT FORM PANEL ─────────────────────────────────────── */
    .auth-form-panel {
      width: 100%;
      max-width: 520px;
      background: #fff;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 3rem 1.5rem;
      position: relative;
      overflow-y: auto;
      height: 100vh;
    }
    .auth-form-inner {
      width: 100%;
      max-width: 400px;
      margin: auto 0;
    }

    /* Mobile logo (hidden on desktop) */
    .auth-mobile-brand {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 2rem;
      justify-content: center;
    }
    @media (min-width: 1024px) { .auth-mobile-brand { display: none; } }

    /* Access badge */
    .auth-access-badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: 10.5px;
      font-weight: 700;
      letter-spacing: .08em;
      text-transform: uppercase;
      color: #64748b;
      background: #f1f5f9;
      border: 1px solid #e2e8f0;
      padding: 4px 10px 4px 8px;
      border-radius: 100px;
      margin-bottom: 1.25rem;
    }
    .auth-access-badge svg { color: var(--red); }

    .auth-title {
      font-size: 1.75rem;
      font-weight: 800;
      color: #0f172a;
      letter-spacing: -.03em;
      line-height: 1.2;
      margin-bottom: 0.35rem;
    }
    .auth-subtitle {
      font-size: .875rem;
      color: #64748b;
      margin-bottom: 1.75rem;
    }

    /* Form elements */
    .field { margin-bottom: 1.1rem; }
    .field-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
    .field label {
      display: block;
      font-size: .8125rem;
      font-weight: 600;
      color: #374151;
    }
    .field a {
      font-size: .8125rem;
      color: var(--red);
      text-decoration: none;
      font-weight: 500;
    }
    .field a:hover { text-decoration: underline; }

    .input-wrap {
      position: relative;
    }
    .input-wrap .input-icon-left {
      position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
      color: #94a3b8; pointer-events: none;
      display: flex; align-items: center;
    }
    .input-wrap .input-icon-right {
      position: absolute; right: 13px; top: 50%; transform: translateY(-50%);
      color: #94a3b8; cursor: pointer; background: none; border: none; padding: 0;
      display: flex; align-items: center;
      transition: color .15s;
    }
    .input-wrap .input-icon-right:hover { color: #475569; }

    .auth-input {
      width: 100%;
      padding: 11px 42px 11px 42px;
      font-size: .9375rem;
      font-family: inherit;
      color: #0f172a;
      background: #f8fafc;
      border: 1.5px solid #e2e8f0;
      border-radius: 10px;
      outline: none;
      transition: border-color .15s, background .15s, box-shadow .15s;
      -webkit-appearance: none;
    }
    .auth-input::placeholder { color: #cbd5e1; }
    .auth-input:focus {
      border-color: var(--red);
      background: #fff;
      box-shadow: 0 0 0 3px rgba(220,38,38,.08);
    }
    .auth-input.is-error {
      border-color: #dc2626;
      background: #fff5f5;
    }
    .error-msg {
      font-size: .78rem;
      color: #dc2626;
      margin-top: 5px;
      display: flex;
      align-items: center;
      gap: 4px;
    }

    /* Alert */
    .auth-alert {
      display: flex;
      align-items: flex-start;
      gap: 10px;
      background: #fff5f5;
      border: 1.5px solid #fecaca;
      border-radius: 10px;
      padding: 12px 14px;
      margin-bottom: 1.25rem;
      font-size: .875rem;
      color: #991b1b;
    }
    .auth-alert svg { flex-shrink: 0; margin-top: 1px; color: #dc2626; }

    /* Remember */
    .remember-row {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 1.5rem;
    }
    .remember-row input[type=checkbox] {
      width: 17px; height: 17px;
      border: 1.5px solid #d1d5db;
      border-radius: 5px;
      accent-color: var(--red);
      cursor: pointer;
      flex-shrink: 0;
    }
    .remember-row label {
      font-size: .875rem;
      color: #475569;
      cursor: pointer;
      user-select: none;
    }

    /* Submit button */
    .auth-btn {
      width: 100%;
      padding: 13px;
      background: var(--red);
      color: #fff;
      font-size: 1rem;
      font-weight: 700;
      font-family: inherit;
      border: none;
      border-radius: 11px;
      cursor: pointer;
      letter-spacing: .01em;
      box-shadow: 0 4px 20px rgba(220,38,38,.35);
      transition: background .15s, transform .1s, box-shadow .15s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }
    .auth-btn:hover {
      background: var(--red-dark);
      box-shadow: 0 6px 24px rgba(220,38,38,.45);
    }
    .auth-btn:active { transform: translateY(1px); }

    /* Footer note */
    .auth-note {
      margin-top: 1.5rem;
      padding-top: 1.25rem;
      border-top: 1px solid #f1f5f9;
      font-size: .78rem;
      color: #94a3b8;
      text-align: center;
      line-height: 1.6;
    }
    .auth-note strong { color: #64748b; }
  </style>
</head>
<body>

<div class="auth-shell">

  <!-- LEFT: photo + hero text -->
  <div class="auth-photo">
    <div class="auth-photo__bg"></div>
    <div class="auth-photo__content">

      <!-- Logo -->
      <a href="#" class="auth-logo">
        <div class="auth-logo__icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none">
            <path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z" fill="#fff"/>
          </svg>
        </div>
        <span class="auth-logo__name">ResQNow</span>
      </a>

      <!-- Hero -->
      <div class="auth-hero">
        <div class="auth-hero__tag">
          <span></span> Live System
        </div>
        <h1 class="auth-hero__headline">
          Hazard reporting and<br>emergency response,<br><em>in one place.</em>
        </h1>
        <p class="auth-hero__sub">
          Verify reports, prioritize incidents, and coordinate responders for Barangay Camunatan, City of Ilagan.
        </p>

        <div class="auth-stats">
          <div>
            <div class="auth-stat__num">8</div>
            <div class="auth-stat__label">Hazard Types</div>
          </div>
          <div>
            <div class="auth-stat__num">4</div>
            <div class="auth-stat__label">Response Teams</div>
          </div>
          <div>
            <div class="auth-stat__num">24/7</div>
            <div class="auth-stat__label">Monitoring</div>
          </div>
        </div>
      </div>

      <div class="auth-photo__footer">
        Barangay Camunatan, City of Ilagan
      </div>

    </div>
  </div>

  <!-- RIGHT: form -->
  <div class="auth-form-panel">
    <div class="auth-form-inner">

      <!-- Mobile logo -->
      <div class="auth-mobile-brand">
        <div class="auth-logo__icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
            <path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z" fill="#fff"/>
          </svg>
        </div>
        <span style="font-size:1.25rem;font-weight:800;color:#0f172a;letter-spacing:-.02em;">ResQNow</span>
      </div>

      <?= $content ?>

    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.6.1/dist/js/tabler.min.js"></script>
<?= $extraScripts ?? '' ?>
</body>
</html>
