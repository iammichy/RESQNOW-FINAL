<?php $errors = $errors ?? []; $sent = $sent ?? false; ?>

<div class="form-header">
  <h1 class="form-title">Forgot password?</h1>
  <p class="form-sub">Enter your registered phone or email to receive a reset link.</p>
</div>

<?php if ($sent): ?>
  <div class="f-alert" style="background:#f0fdf4; border-color:#bbf7d0; color:#166534;">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    <span>If an account exists, a reset link has been sent.</span>
  </div>
<?php endif; ?>

<?php if (isset($errors['identifier'])): ?>
  <div class="f-alert">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <span><?= e($errors['identifier']) ?></span>
  </div>
<?php endif; ?>

<form method="post" action="<?= base_url('app/forgot-password') ?>" novalidate>
  <?= csrf_field() ?>
  <div class="f-group">
    <label class="f-label" for="fi">Phone or Email Address</label>
    <div class="f-wrap">
      <span class="f-icon">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4a2 2 0 0 1 1.99-2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6.13 6.13l1.82-1.82a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
        </svg>
      </span>
      <input class="f-inp" type="text" name="identifier" id="fi"
        placeholder="e.g., 09123456789 or you@example.com" autofocus required>
    </div>
  </div>
  
  <button type="submit" class="f-btn">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
      <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>
    </svg>
    Send Reset Link
  </button>
</form>

<div class="f-foot">
  Remembered your password? <a href="<?= base_url('app/login') ?>">Back to Sign In</a>
</div>
