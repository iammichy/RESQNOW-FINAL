<?php
$pageTitle  = 'Sign In';
$errors     = $errors     ?? [];
$loginError = $loginError ?? '';
?>

<!-- Access badge -->
<div class="auth-access-badge">
  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
  Authorized Personnel Only
</div>

<h1 class="auth-title">Welcome back</h1>
<p class="auth-subtitle">Access the ResQNow operations portal to manage incidents and response teams.</p>

<?php if ($loginError): ?>
  <div class="auth-alert" role="alert">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    <span><?= e($loginError) ?></span>
  </div>
<?php endif; ?>

<form action="<?= base_url('login') ?>" method="post" autocomplete="on" novalidate>
  <?= csrf_field() ?>

  <!-- Email or Phone -->
  <div class="field">
    <label for="identifier">Email Address or Phone</label>
    <div class="input-wrap">
      <span class="input-icon-left">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
        </svg>
      </span>
      <input
        type="text"
        name="identifier"
        id="identifier"
        class="auth-input <?= isset($errors['identifier']) ? 'is-error' : '' ?>"
        placeholder="e.g., you@example.com or 09123456789"
        value="<?= e(old('identifier')) ?>"
        autocomplete="username"
        autofocus
      >
    </div>
    <?php if (isset($errors['identifier'])): ?>
      <div class="error-msg">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <?= e($errors['identifier']) ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Password -->
  <div class="field">
    <div class="field-row">
      <label for="password">Password</label>
      <a href="<?= base_url('forgot-password') ?>">Forgot password?</a>
    </div>
    <div class="input-wrap">
      <span class="input-icon-left">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
        </svg>
      </span>
      <input
        type="password"
        name="password"
        id="password"
        class="auth-input <?= isset($errors['password']) ? 'is-error' : '' ?>"
        placeholder="Enter your password"
        autocomplete="current-password"
        style="padding-right:44px;"
      >
      <button type="button" class="input-icon-right" id="toggle-pw" aria-label="Toggle password visibility">
        <svg id="eye-show" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
        </svg>
        <svg id="eye-hide" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;">
          <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>
        </svg>
      </button>
    </div>
    <?php if (isset($errors['password'])): ?>
      <div class="error-msg">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <?= e($errors['password']) ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Remember me -->
  <div class="remember-row">
    <input type="checkbox" name="remember" id="remember" <?= old('remember') ? 'checked' : '' ?>>
    <label for="remember">Remember me for 30 days</label>
  </div>

  <!-- Submit -->
  <button type="submit" class="auth-btn">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
      <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M13.8 12H3"/>
    </svg>
    Sign In
  </button>

</form>

<!-- Footer note -->
<div class="auth-note">
  <strong>Restricted access.</strong> This portal is for authorized barangay administrators and emergency response personnel. Activity is logged.
</div>

<?php
$extraScripts = $extraScripts ?? '';
ob_start();
?>
<script>
// Password show/hide toggle
(function () {
  var btn   = document.getElementById('toggle-pw');
  var input = document.getElementById('password');
  var show  = document.getElementById('eye-show');
  var hide  = document.getElementById('eye-hide');
  if (!btn) return;
  btn.addEventListener('click', function () {
    var isText = input.type === 'text';
    input.type = isText ? 'password' : 'text';
    show.style.display = isText ? 'block' : 'none';
    hide.style.display = isText ? 'none'  : 'block';
  });
})();
</script>
<?php
$extraScripts .= ob_get_clean();
?>
