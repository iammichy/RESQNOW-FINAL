<?php
$pageTitle = 'Forgot Password';
$errors    = $errors ?? [];
$sent      = $sent   ?? false;
?>

<a href="<?= base_url('login') ?>" style="display:inline-flex;align-items:center;gap:6px;font-size:.8125rem;font-weight:500;color:#64748b;text-decoration:none;margin-bottom:1.5rem;">
  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
  Back to Sign In
</a>

<div class="auth-access-badge">
  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
  Password Reset
</div>

<h1 class="auth-title">Forgot password?</h1>
<p class="auth-subtitle">Enter your registered email or phone and we'll send a reset link.</p>

<?php if ($sent): ?>
  <div class="auth-alert" style="background:#f0fdf4;border-color:#bbf7d0;color:#166534;" role="alert">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    <span>If an account exists, a reset link has been sent. Check your email or phone.</span>
  </div>
<?php endif; ?>

<?php if (isset($errors['identifier'])): ?>
  <div class="auth-alert" role="alert">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <span><?= e($errors['identifier']) ?></span>
  </div>
<?php endif; ?>

<form action="<?= base_url('forgot-password') ?>" method="post" novalidate>
  <?= csrf_field() ?>

  <div class="field">
    <label for="identifier">Email or Phone Number</label>
    <div class="input-wrap">
      <span class="input-icon-left">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
      </span>
      <input
        type="text"
        name="identifier"
        id="identifier"
        class="auth-input"
        placeholder="you@example.com or 09xxxxxxxxx"
        value="<?= e(old('identifier')) ?>"
        autofocus
        required
      >
    </div>
  </div>

  <button type="submit" class="auth-btn" style="margin-top:.5rem;">
    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
    Send Reset Link
  </button>
</form>

<div class="auth-note">
  Remember your password? <a href="<?= base_url('login') ?>" style="color:var(--red);font-weight:600;text-decoration:none;">Sign In →</a>
</div>
