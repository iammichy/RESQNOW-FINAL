<?php $pageTitle = $pageTitle ?? 'ResQNow'; ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
  <meta name="theme-color" content="#1d4ed8">
  <link rel="manifest" href="<?= base_url('public/manifest.json') ?>">
  <link rel="apple-touch-icon" href="<?= base_url('public/assets/icons/icon-192x192.png') ?>">
  <title><?= e($pageTitle) ?> · ResQNow</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --blue: #1d4ed8; --blue-d: #1e40af;
      --ink: #0f172a; --sub: #64748b;
      --border: #e2e8f0; --surface: #f8fafc;
      --red: #dc2626;
      --r: 6px;
    }
    html, body {
      height: 100%; min-height: 100dvh;
      font-family: 'Inter', -apple-system, sans-serif;
      -webkit-font-smoothing: antialiased;
    }

    /* Full-page blurred photo background */
    .page-bg {
      position: fixed; inset: 0; z-index: 0;
      background: url('https://images.unsplash.com/photo-1626958390905-5b87a3f1dd3e?w=1600&q=80&fit=crop&auto=format')
                  center / cover no-repeat;
      filter: blur(12px) brightness(.55);
      transform: scale(1.05); /* avoid blur edges */
    }

    /* Centered container */
    .page-wrap {
      position: relative; z-index: 1;
      min-height: 100dvh;
      display: flex; align-items: flex-start; justify-content: center;
      padding: 32px 16px 48px;
    }

    /* The card panel */
    .reg-card {
      width: 100%; max-width: 820px;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 24px 80px rgba(0,0,0,.45);
      display: flex;
      overflow: hidden;
    }

    /* LEFT: brand side-panel */
    .reg-side {
      width: 210px;
      flex-shrink: 0;
      background: #fff;
      border-right: 1px solid var(--border);
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 40px 24px;
      text-align: center;
    }
    .reg-side__icon {
      width: 60px; height: 60px;
      background: var(--blue);
      border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      margin-bottom: 14px;
      box-shadow: 0 6px 20px rgba(29,78,216,.3);
    }
    .reg-side__name {
      font-size: 1.25rem; font-weight: 800;
      color: var(--ink); letter-spacing: -.02em;
      margin-bottom: 6px;
    }
    .reg-side__loc {
      font-size: .8rem; color: var(--sub); line-height: 1.5;
    }
    .reg-side__badge {
      margin-top: 28px;
      display: inline-flex; align-items: center; gap: 6px;
      font-size: .7rem; font-weight: 700; letter-spacing: .05em;
      text-transform: uppercase; color: #16a34a;
      background: #f0fdf4; border: 1.5px solid #bbf7d0;
      padding: 5px 10px; border-radius: 100px;
    }
    .reg-side__badge span {
      width: 7px; height: 7px; border-radius: 50%;
      background: #16a34a; flex-shrink: 0;
    }

    /* RIGHT: form panel */
    .reg-form {
      flex: 1;
      overflow-y: auto;
      padding: 36px 36px 40px;
    }
    .reg-form__title {
      font-size: 1.5rem; font-weight: 800;
      color: var(--ink); letter-spacing: -.025em;
      margin-bottom: 3px;
    }
    .reg-form__sub {
      font-size: .8125rem; color: var(--sub);
      margin-bottom: 28px;
    }

    /* Section labels */
    .section-label {
      font-size: .6875rem; font-weight: 700;
      letter-spacing: .07em; text-transform: uppercase;
      color: #9ca3af;
      margin: 22px 0 14px;
    }
    .section-label:first-of-type { margin-top: 0; }

    /* Grid layouts */
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .grid-2-3 { display: grid; grid-template-columns: 2fr 1fr; gap: 12px; }
    .col-full { grid-column: 1 / -1; }

    /* Field */
    .field { display: flex; flex-direction: column; }
    .field label {
      font-size: .78rem; font-weight: 600; color: #374151;
      margin-bottom: 6px;
    }
    .field label .req { color: var(--red); margin-left: 2px; }
    .field-err {
      font-size: .74rem; color: var(--red); margin-top: 4px;
      display: flex; align-items: center; gap: 3px;
    }

    /* Input */
    .inp-wrap { position: relative; }
    .inp-ico {
      position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
      color: #9ca3af; pointer-events: none; display: flex;
    }
    .inp-eye-btn {
      position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
      background: none; border: none; padding: 4px; cursor: pointer;
      color: #9ca3af; display: flex; border-radius: 6px;
    }
    .inp-eye-btn:hover { color: #374151; }

    input.f, select.f {
      width: 100%; height: 44px;
      padding: 0 38px 0 38px;
      font-size: 14px; font-family: inherit;
      color: var(--ink);
      background: #fff;
      border: 1.5px solid var(--border);
      border-radius: var(--r);
      outline: none;
      -webkit-appearance: none; appearance: none;
      transition: border-color .15s, box-shadow .15s;
    }
    select.f { padding-right: 14px; cursor: pointer; }
    input.f::placeholder, select.f option[value=""] { color: #c9d1db; }
    input.f:focus, select.f:focus {
      border-color: var(--blue);
      box-shadow: 0 0 0 3px rgba(29,78,216,.08);
    }
    input.f.err, select.f.err { border-color: var(--red); }
    input.f.no-ico, select.f.no-ico { padding-left: 14px; }

    /* Map placeholder */
    .map-placeholder {
      border: 1.5px dashed var(--border);
      border-radius: var(--r);
      height: 80px;
      display: flex; flex-direction: column;
      align-items: center; justify-content: center; gap: 4px;
      color: #9ca3af; font-size: .8rem;
      background: var(--surface);
      cursor: default;
    }

    /* Vulnerability checkboxes */
    .vuln-grid {
      display: grid; grid-template-columns: 1fr 1fr; gap: 8px;
      margin-top: 0;
    }
    .vuln-item {
      display: flex; align-items: center; gap: 10px;
      border: 1.5px solid var(--border);
      border-radius: var(--r); padding: 9px 12px;
      cursor: pointer; transition: border-color .15s, background .15s;
      font-size: .8125rem; color: var(--ink); font-weight: 500;
      user-select: none;
    }
    .vuln-item:hover { border-color: #93c5fd; background: #eff6ff; }
    .vuln-item input[type=checkbox] {
      width: 15px; height: 15px;
      accent-color: var(--blue); flex-shrink: 0;
    }
    .vuln-icon { color: #9ca3af; transition: color .15s; }

    /* Alert */
    .alert {
      display: flex; gap: 10px; align-items: flex-start;
      background: #fef2f2; border: 1.5px solid #fecaca;
      border-radius: 10px; padding: 10px 12px;
      margin-bottom: 18px; font-size: .8125rem; color: #991b1b;
    }
    .alert svg { flex-shrink: 0; margin-top: 1px; }

    /* Confirm checkbox */
    .confirm-row {
      display: flex; align-items: flex-start; gap: 10px;
      margin: 20px 0 6px;
    }
    .confirm-row input[type=checkbox] {
      width: 16px; height: 16px; margin-top: 2px;
      accent-color: var(--blue); flex-shrink: 0;
    }
    .confirm-row label {
      font-size: .8rem; color: var(--sub); line-height: 1.5; cursor: pointer;
    }
    .confirm-err { font-size: .74rem; color: var(--red); margin-top: 3px; }

    /* Submit button */
    .btn-submit {
      width: 100%; height: 48px;
      background: var(--blue); color: #fff;
      font-family: inherit; font-size: .9375rem; font-weight: 700;
      border: none; border-radius: var(--r); cursor: pointer;
      display: flex; align-items: center; justify-content: center; gap: 8px;
      box-shadow: 0 2px 12px rgba(29,78,216,.3);
      letter-spacing: .01em;
      transition: background .12s, transform .08s;
      margin-top: 20px;
    }
    .btn-submit:hover { background: var(--blue-d); }
    .btn-submit:active { transform: scale(.99); }

    .sign-in-link {
      text-align: center; margin-top: 14px;
      font-size: .875rem; color: var(--sub);
    }
    .sign-in-link a { color: var(--blue); font-weight: 600; text-decoration: none; }

    /* Mobile: stack vertically */
    @media (max-width: 640px) {
      .page-wrap { padding: 0; align-items: stretch; }
      .reg-card { flex-direction: column; border-radius: 0; box-shadow: none; min-height: 100dvh; }
      .reg-side { width: 100%; border-right: none; border-bottom: 1px solid var(--border); flex-direction: row; justify-content: flex-start; padding: 18px 20px; text-align: left; }
      .reg-side__icon { width: 44px; height: 44px; border-radius: 12px; margin-bottom: 0; margin-right: 12px; }
      .reg-side__badge { display: none; }
      .reg-form { padding: 20px 20px 36px; }
      .grid-2, .grid-2-3 { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>
  <div class="page-bg"></div>
  <div class="page-wrap">
    <div class="reg-card">

      <!-- Left panel -->
      <div class="reg-side">
        <div class="reg-side__icon">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" fill="#fff"/>
        </svg>
      </div>
        <div>
          <div class="reg-side__name">ResQNow</div>
          <div class="reg-side__loc">Barangay Camunatan<br>City of Ilagan</div>
          <div class="reg-side__badge">
            <span></span> Secure Registration
          </div>
        </div>
      </div>

      <!-- Right form panel -->
      <div class="reg-form">
        <?= $content ?>
      </div>

    </div>
  </div>
</body>
</html>
