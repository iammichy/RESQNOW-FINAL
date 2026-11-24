<?php
$flash ??= null;
$errors ??= [];
$validationErrors = $errors ?? [];

if (!empty($validationErrors)): ?>
  <div class="alert alert-danger alert-dismissible" role="alert">
    <div class="d-flex">
      <div class="me-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      </div>
      <div>
        <h4 class="alert-title mb-1">Please fix the following:</h4>
        <ul class="mb-0 ps-3">
          <?php foreach ($validationErrors as $field => $msg): ?>
            <li><?= e($msg) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
    <a href="#" class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
  </div>
<?php endif; ?>

<?php if ($flash): ?>
  <div class="alert alert-<?= e($flash['type'] === 'error' ? 'danger' : $flash['type']) ?> alert-dismissible" role="alert">
    <div class="d-flex">
      <div class="me-2">
        <?php if ($flash['type'] === 'success'): ?>
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <?php else: ?>
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <?php endif; ?>
      </div>
      <div class="align-self-center"><?= e($flash['message']) ?></div>
    </div>
    <a href="#" class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
  </div>
<?php endif; ?>
