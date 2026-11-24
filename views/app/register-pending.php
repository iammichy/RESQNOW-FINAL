<?php $name = $name ?? 'there'; ?>

<div style="text-align: center; padding: 2rem 0;">
  <div style="width: 64px; height: 64px; background: #f0fdf4; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; color: #16a34a;">
    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
      <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
      <polyline points="22 4 12 14.01 9 11.01"/>
    </svg>
  </div>
  
  <h1 class="form-title">Registration Submitted</h1>
  <p class="form-sub" style="margin-bottom: 2rem;">
    Hi <strong><?= e($name) ?></strong>, your account is now awaiting barangay verification.
  </p>

  <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: var(--radius); padding: 1.25rem; text-align: left; margin-bottom: 2rem;">
    <h4 style="font-size: 0.9rem; font-weight: 700; color: var(--navy); margin-bottom: 0.5rem;">What happens next?</h4>
    <ul style="font-size: 0.85rem; color: var(--sub); padding-left: 1.25rem; line-height: 1.6;">
      <li>Barangay personnel will review your details.</li>
      <li>You will receive a notification once approved.</li>
      <li>Once verified, you can log in to report emergencies.</li>
    </ul>
  </div>

  <a href="<?= base_url('app/login') ?>" class="f-btn" style="text-decoration: none;">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
      <path d="M19 12H5M12 19l-7-7 7-7"/>
    </svg>
    Back to Sign In
  </a>
</div>
