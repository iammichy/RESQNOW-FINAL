<?php
$contact = $contact ?? null;
$isEdit  = !empty($contact['id']);
$action  = $isEdit ? base_url('settings/contacts/' . $contact['id'] . '/edit') : base_url('settings/contacts/new');
$unitTypes = [
    'barangay' => 'Barangay Official',
    'rescue'   => 'Rescue / CDRRMO',
    'fire'     => 'Fire Protection (BFP)',
    'police'   => 'Police (PNP)',
    'medical'  => 'Medical / Health',
    'utility'  => 'Utilities',
    'social'   => 'Social Services',
    'other'    => 'Other',
];
?>
<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <div class="page-pretitle"><a href="<?= base_url('settings') ?>" class="text-muted">&larr; Settings</a></div>
        <h2 class="page-title"><?= $isEdit ? 'Edit Contact' : 'Add Emergency Contact' ?></h2>
      </div>
    </div>
  </div>
</div>

<div class="page-body">
  <div class="container-xl">
    <div class="row justify-content-center">
      <div class="col-lg-6">
        <div class="card">
          <div class="card-body">
            <form method="post" action="<?= $action ?>">
              <?= csrf_field() ?>

              <div class="mb-3">
                <label class="form-label fw-bold">Unit / Contact Name <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" required
                  value="<?= e($contact['name'] ?? '') ?>"
                  placeholder="e.g. Barangay Emergency Hotline">
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                <input type="tel" name="phone" class="form-control" required
                  value="<?= e($contact['phone'] ?? '') ?>"
                  placeholder="e.g. 0997 692 0615 or 911"
                  pattern="[0-9\+\-\s\(\)]{3,20}"
                  title="Digits only — spaces, dashes, + and () allowed">
                <div class="form-text text-muted" style="font-size:.75rem;">Numbers only. Short codes like 911 or 143 are allowed.</div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">Description <span class="text-muted small fw-normal">(optional)</span></label>
                <input type="text" name="description" class="form-control"
                  value="<?= e($contact['description'] ?? '') ?>"
                  placeholder="e.g. Main barangay emergency line">
              </div>

              <div class="mb-4">
                <label class="form-label fw-bold">Unit Type</label>
                <select name="unit_type" class="form-select">
                  <?php foreach ($unitTypes as $val => $label): ?>
                    <option value="<?= $val ?>" <?= ($contact['unit_type'] ?? 'other') === $val ? 'selected' : '' ?>>
                      <?= $label ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill fw-bold">
                  <?= $isEdit ? 'Save Changes' : 'Add Contact' ?>
                </button>
                <a href="<?= base_url('settings') ?>" class="btn btn-outline-secondary fw-bold">Cancel</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
