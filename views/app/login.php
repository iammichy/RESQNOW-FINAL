<?php $loginError = $loginError ?? ''; ?>

<div class="form-header">
  <h1 class="form-title">Welcome back</h1>
  <p class="form-sub">Access your account to report or monitor incidents.</p>
</div>

<?php if ($loginError): ?>
  <div class="f-alert" role="alert">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    <span><?= e($loginError) ?></span>
  </div>
<?php endif; ?>

<form method="post" action="<?= base_url('app/login') ?>" novalidate>
  <?= csrf_field() ?>

  <div class="f-group">
    <label class="f-label" for="identifier">Email Address or Phone</label>
    <div class="f-wrap">
      <span class="f-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
        </svg>
      </span>
      <input class="f-inp" type="text" name="identifier" id="identifier"
        placeholder="e.g., 09123456789 or you@example.com"
        autocomplete="username" autofocus>
    </div>
  </div>

  <div class="f-group">
    <div class="f-row">
      <label class="f-label" for="password">Password</label>
      <a class="f-link" href="<?= base_url('app/forgot-password') ?>">Forgot password?</a>
    </div>
    <div class="f-wrap">
      <span class="f-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
      </span>
      <input class="f-inp" type="password" name="password" id="password"
        placeholder="Enter your password"
        autocomplete="current-password"
        style="padding-right: 44px;">
      <button type="button" class="f-eye" id="pw-toggle" aria-label="Toggle password visibility">
        <svg id="eye-on" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
        </svg>
        <svg id="eye-off" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none">
          <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>
        </svg>
      </button>
    </div>
  </div>

  <label class="f-check">
    <input type="checkbox" name="remember">
    <span>Remember me for 30 days</span>
  </label>

  <button type="submit" class="f-btn">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
      <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M13.8 12H3"/>
    </svg>
    Sign In
  </button>
</form>

<div class="f-foot">
  New to ResQNow? <a href="<?= base_url('app/register') ?>">Create an account</a>
</div>

<script>
(function () {
  var btn = document.getElementById('pw-toggle');
  var inp = document.getElementById('password');
  var on  = document.getElementById('eye-on');
  var off = document.getElementById('eye-off');
  if (!btn) return;
  btn.addEventListener('click', function () {
    var isText = inp.type === 'text';
    inp.type = isText ? 'password' : 'text';
    on.style.display  = isText ? '' : 'none';
    off.style.display = isText ? 'none' : '';
  });
})();
</script>
