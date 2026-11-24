<?php
$record       = $record        ?? null;
$families     = $families      ?? [];
$isEdit       = !empty($record['id']);
$presetPurok  = $preset_purok  ?? 0;
$presetFamily = $preset_family ?? 0;
$action       = $isEdit
    ? base_url('residents/masterlist/' . $record['id'] . '/edit')
    : base_url('residents/masterlist/new');

$byPurok = [];
foreach ($families as $f) { $byPurok[$f['purok']][] = $f; }

$statusMeta = [
    'active'      => ['#f0fdf4','#16a34a','#bbf7d0','Active'],
    'deceased'    => ['#fef2f2','#dc2626','#fecaca','Deceased'],
    'transferred' => ['#fff7ed','#ea580c','#fed7aa','Transferred Out'],
    'archived'    => ['#f8fafc','#64748b','#e2e8f0','Archived'],
];
[$curBg,$curColor,$curBorder,$curLabel] = $statusMeta[$record['status'] ?? 'active'] ?? $statusMeta['active'];
?>
<style>
/* ── Page shell ──────────────────────────────── */
.mf-page { padding: 28px 32px 48px; }

/* Breadcrumb */
.mf-breadcrumb {
  display: flex; align-items: center; gap: 8px;
  margin-bottom: 28px;
}
.mf-breadcrumb a {
  font-size: .78rem; font-weight: 600; color: #94a3b8;
  text-decoration: none; display: flex; align-items: center; gap: 5px;
}
.mf-breadcrumb a:hover { color: #0f172a; }
.mf-breadcrumb-sep { color: #cbd5e1; font-size: .9rem; }
.mf-breadcrumb-cur { font-size: .78rem; font-weight: 700; color: #0f172a; }

/* Two-column grid */
.mf-grid {
  display: grid;
  grid-template-columns: 1fr 300px;
  gap: 24px;
  align-items: start;
}
@media(max-width:960px) { .mf-grid { grid-template-columns: 1fr; } }

/* ── Form card ───────────────────────────────── */
.mf-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  overflow: hidden;
  box-shadow: 0 1px 4px rgba(0,0,0,.03);
}
.mf-card-head {
  padding: 22px 28px 20px;
  border-bottom: 1px solid #f1f5f9;
  background: #fafbfc;
}
.mf-card-title {
  font-size: 1rem; font-weight: 800; color: #0f172a;
  letter-spacing: -.02em; margin: 0 0 4px;
}
.mf-card-sub { font-size: .78rem; color: #64748b; margin: 0; }
.mf-card-body { padding: 28px; }

/* Fields */
.mf-field { margin-bottom: 22px; }
.mf-field:last-of-type { margin-bottom: 0; }
.mf-label {
  display: block; font-size: .8rem; font-weight: 700;
  color: #374151; margin-bottom: 8px; letter-spacing: .01em;
}
.mf-req { color: #dc2626; margin-left: 2px; }
.mf-opt { font-size: .72rem; color: #94a3b8; font-weight: 400; margin-left: 4px; }

.mf-input {
  width: 100%; height: 46px; padding: 0 16px;
  border: 1.5px solid #e2e8f0; border-radius: 10px;
  font-size: .9375rem; color: #0f172a;
  background: #f8fafc; outline: none;
  font-family: inherit;
  transition: border-color .15s, background .15s, box-shadow .15s;
}
.mf-input::placeholder { color: #cbd5e1; }
.mf-input:focus {
  border-color: #dc2626; background: #fff;
  box-shadow: 0 0 0 4px rgba(220,38,38,.08);
}
.mf-hint { font-size: .72rem; color: #94a3b8; margin-top: 6px; line-height: 1.4; }

.mf-row2 { display: grid; grid-template-columns: 1fr 1.5fr; gap: 14px; }
@media(max-width:600px) { .mf-row2 { grid-template-columns: 1fr; } }

/* Action bar */
.mf-actions {
  display: flex; align-items: center; gap: 10px;
  padding-top: 24px; border-top: 1px solid #f1f5f9; margin-top: 24px;
}
.mf-btn-save {
  height: 46px; padding: 0 28px;
  background: #dc2626; color: #fff; border: none;
  border-radius: 10px; font-size: .9375rem; font-weight: 700;
  cursor: pointer; font-family: inherit;
  box-shadow: 0 2px 8px rgba(220,38,38,.25);
  transition: background .12s, box-shadow .12s;
}
.mf-btn-save:hover {
  background: #b91c1c;
  box-shadow: 0 4px 14px rgba(220,38,38,.35);
}
.mf-btn-cancel {
  height: 46px; padding: 0 20px;
  background: #fff; color: #475569;
  border: 1.5px solid #e2e8f0; border-radius: 10px;
  font-size: .875rem; font-weight: 600; cursor: pointer;
  font-family: inherit; text-decoration: none;
  display: inline-flex; align-items: center;
  transition: border-color .12s, color .12s;
}
.mf-btn-cancel:hover { border-color: #cbd5e1; color: #0f172a; }
.mf-id-badge {
  margin-left: auto; font-size: .72rem; color: #94a3b8;
  font-family: monospace; background: #f8fafc;
  border: 1px solid #e2e8f0; border-radius: 5px;
  padding: 4px 9px;
}

/* ── Right sidebar ───────────────────────────── */
.mf-side-card {
  background: #fff; border: 1px solid #e2e8f0;
  border-radius: 14px; overflow: hidden; margin-bottom: 16px;
  box-shadow: 0 1px 4px rgba(0,0,0,.03);
}
.mf-side-head {
  padding: 14px 18px; border-bottom: 1px solid #f1f5f9;
  background: #fafbfc; display: flex; align-items: center; gap: 7px;
  font-size: .8rem; font-weight: 700; color: #0f172a;
}
.mf-info-row {
  display: flex; align-items: center;
  justify-content: space-between;
  padding: 10px 18px; border-bottom: 1px solid #f8fafc;
  font-size: .8rem;
}
.mf-info-row:last-child { border-bottom: none; }
.mf-info-k { color: #64748b; font-weight: 600; }
.mf-info-v { color: #0f172a; font-weight: 700; }

/* Archive card */
.mf-archive-card {
  background: #fff; border: 1.5px solid #fecaca;
  border-radius: 14px; overflow: hidden;
  box-shadow: 0 1px 4px rgba(220,38,38,.06);
}
.mf-archive-head {
  padding: 13px 18px; border-bottom: 1px solid #fecaca;
  background: #fff5f5; display: flex; align-items: center; gap: 8px;
  font-size: .8rem; font-weight: 700; color: #dc2626;
}
.mf-archive-body { padding: 18px; }
.mf-archive-body p {
  font-size: .75rem; color: #64748b;
  margin: 0 0 14px; line-height: 1.55;
}
.mf-archive-body label {
  display: block; font-size: .75rem; font-weight: 700;
  color: #374151; margin-bottom: 5px;
}
.mf-select-sm {
  width: 100%; height: 40px; padding: 0 12px;
  border: 1.5px solid #e2e8f0; border-radius: 8px;
  font-size: .875rem; background: #f8fafc; outline: none;
  font-family: inherit; margin-bottom: 12px;
  transition: border-color .12s;
}
.mf-select-sm:focus { border-color: #dc2626; background: #fff; }
.mf-textarea-sm {
  width: 100%; height: 68px; padding: 10px 12px;
  border: 1.5px solid #e2e8f0; border-radius: 8px;
  font-size: .875rem; background: #f8fafc; outline: none;
  font-family: inherit; resize: none; margin-bottom: 12px;
  transition: border-color .12s;
}
.mf-textarea-sm:focus { border-color: #dc2626; background: #fff; }
.mf-btn-archive {
  width: 100%; height: 42px;
  background: #dc2626; color: #fff; border: none;
  border-radius: 8px; font-size: .875rem; font-weight: 700;
  cursor: pointer; font-family: inherit;
  transition: background .12s;
}
.mf-btn-archive:hover { background: #b91c1c; }

/* Guidelines list */
.mf-guidelines {
  font-size: .78rem; color: #475569; line-height: 1.8;
  padding: 0 18px 18px; margin: 0;
}
.mf-guidelines li { padding: 1px 0; }

/* Purok summary */
.mf-purok-row {
  display: flex; align-items: center;
  justify-content: space-between;
  padding: 9px 18px; border-bottom: 1px solid #f8fafc;
  font-size: .78rem;
}
.mf-purok-row:last-child { border-bottom: none; background: #f8fafc; }
</style>

<div class="mf-page">

  <!-- Breadcrumb -->
  <div class="mf-breadcrumb">
    <a href="<?= base_url('residents') ?>">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
      Resident Management
    </a>
    <span class="mf-breadcrumb-sep">›</span>
    <span class="mf-breadcrumb-cur"><?= $isEdit ? 'Edit Resident' : 'Add Resident' ?></span>
  </div>

  <div class="mf-grid">

    <!-- ═══ MAIN FORM ════════════════════════════════════════════ -->
    <div class="mf-card">
      <div class="mf-card-head">
        <div class="mf-card-title">
          <?= $isEdit ? 'Edit Resident Information' : 'Add New Resident to Masterlist' ?>
        </div>
        <div class="mf-card-sub">
          <?= $isEdit
            ? 'Correct spelling errors, update purok/family assignment, or archive this record.'
            : 'Only add residents who have been physically verified as residents of Barangay Camunatan.' ?>
        </div>
      </div>

      <div class="mf-card-body">
        <form method="post" action="<?= $action ?>">
          <?= csrf_field() ?>

          <!-- Full Name -->
          <div class="mf-field">
            <label class="mf-label">Full Name <span class="mf-req">*</span></label>
            <input type="text" name="name" class="mf-input" required
              value="<?= e($record['name'] ?? '') ?>"
              placeholder="e.g. Dela Cruz, Juan Reyes">
            <div class="mf-hint">Use format: <strong>Surname, Firstname Middlename</strong> — consistent with the official barangay masterlist</div>
          </div>

          <!-- Purok + Family -->
          <div class="mf-row2">
            <div class="mf-field">
              <label class="mf-label">Purok <span class="mf-req">*</span></label>
              <select name="purok" class="mf-input" id="ml-purok" required onchange="updateFamilies()">
                <option value="">— Select —</option>
                <option value="1" <?= ($record['purok'] ?? $presetPurok) == 1 ? 'selected' : '' ?>>Purok 1</option>
                <option value="2" <?= ($record['purok'] ?? $presetPurok) == 2 ? 'selected' : '' ?>>Purok 2</option>
                <option value="3" <?= ($record['purok'] ?? $presetPurok) == 3 ? 'selected' : '' ?>>Purok 3</option>
              </select>
            </div>
            <div class="mf-field">
              <label class="mf-label">Family Group</label>
              <select name="family_no" class="mf-input" id="ml-family">
                <option value="">— Create New Family —</option>
              </select>
              <div class="mf-hint">Leave blank to start a new family record</div>
            </div>
          </div>

          <!-- Notes -->
          <div class="mf-field">
            <label class="mf-label">Notes <span class="mf-opt">(optional)</span></label>
            <input type="text" name="notes" class="mf-input"
              value="<?= e($record['notes'] ?? '') ?>"
              placeholder="e.g. Newly transferred, Added per Punong Barangay verification">
          </div>

          <!-- Buttons -->
          <div class="mf-actions">
            <button type="submit" class="mf-btn-save">
              <?= $isEdit ? 'Save Changes' : 'Add to Masterlist' ?>
            </button>
            <a href="<?= base_url('residents') ?>" class="mf-btn-cancel">Cancel</a>
            <?php if ($isEdit): ?>
              <span class="mf-id-badge">ID #<?= (int)$record['id'] ?></span>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </div>

    <!-- ═══ SIDEBAR ══════════════════════════════════════════════ -->
    <div>

      <?php if ($isEdit): ?>

        <!-- Current Record Info -->
        <div class="mf-side-card" style="margin-bottom:16px;">
          <div class="mf-side-head">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            Current Record
          </div>
          <div class="mf-info-row">
            <span class="mf-info-k">Status</span>
            <span style="display:inline-block;padding:3px 10px;border-radius:6px;font-size:.68rem;font-weight:700;background:<?= $curBg ?>;color:<?= $curColor ?>;border:1px solid <?= $curBorder ?>;">
              <?= $curLabel ?>
            </span>
          </div>
          <div class="mf-info-row">
            <span class="mf-info-k">Location</span>
            <span class="mf-info-v">Purok <?= $record['purok'] ?> · Family #<?= $record['family_no'] ?></span>
          </div>
          <div class="mf-info-row">
            <span class="mf-info-k">Resident #</span>
            <span class="mf-info-v"><?= $record['resident_no'] ?></span>
          </div>
          <?php if (!empty($record['notes'])): ?>
            <div style="padding:10px 18px;font-size:.75rem;color:#64748b;border-top:1px solid #f8fafc;line-height:1.5;">
              <span style="font-weight:700;color:#374151;">Note: </span><?= e($record['notes']) ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Archive / Reactivate -->
        <?php if ($record['status'] === 'active'): ?>
          <div class="mf-archive-card">
            <div class="mf-archive-head">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
              Archive This Resident
            </div>
            <div class="mf-archive-body">
              <p>Removes from active count while preserving the historical record.</p>
              <form method="post" action="<?= base_url('residents/masterlist/' . $record['id'] . '/archive') ?>"
                    onsubmit="return confirm('Archive this resident? This removes them from the active masterlist.')">
                <?= csrf_field() ?>
                <label>Reason <span style="color:#dc2626;">*</span></label>
                <select name="archive_status" class="mf-select-sm">
                  <option value="deceased">Deceased</option>
                  <option value="transferred">Transferred Out of Barangay</option>
                  <option value="archived">Inactive / Archived</option>
                </select>
                <label>Notes <span style="font-weight:400;color:#94a3b8;font-size:.7rem;">(optional)</span></label>
                <textarea name="notes" class="mf-textarea-sm"
                  placeholder="e.g. Passed away Oct 2026, confirmed by family"></textarea>
                <button type="submit" class="mf-btn-archive">Archive Resident</button>
              </form>
            </div>
          </div>
        <?php else: ?>
          <div class="mf-side-card">
            <div class="mf-side-head">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-.18-4.96"/></svg>
              Reactivate Record
            </div>
            <div style="padding:16px 18px;">
              <p style="font-size:.75rem;color:#64748b;margin:0 0 12px;line-height:1.5;">
                This record is archived. Reactivate if it was archived by mistake.
              </p>
              <form method="post" action="<?= base_url('residents/masterlist/' . $record['id'] . '/archive') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="archive_status" value="active">
                <button type="submit" style="width:100%;height:40px;background:#16a34a;color:#fff;border:none;border-radius:8px;font-size:.875rem;font-weight:700;cursor:pointer;">
                  Reactivate Resident
                </button>
              </form>
            </div>
          </div>
        <?php endif; ?>

      <?php else: ?>

        <!-- Add mode: guidelines -->
        <div class="mf-side-card">
          <div class="mf-side-head">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Guidelines
          </div>
          <ul class="mf-guidelines">
            <li>Only add residents <strong>physically verified</strong> to live in Barangay Camunatan.</li>
            <li>Use <strong>Surname, Firstname</strong> format.</li>
            <li>Select an existing family if the person belongs to a known household.</li>
            <li>Adding to the masterlist does <strong>not</strong> create a ResQNow account.</li>
          </ul>
        </div>

        <!-- Purok counts -->
        <div class="mf-side-card">
          <div class="mf-side-head">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Masterlist Summary
          </div>
          <div class="mf-purok-row"><span style="color:#64748b;font-weight:600;">Purok 1</span><span style="color:#0f172a;font-weight:700;">534 residents</span></div>
          <div class="mf-purok-row"><span style="color:#64748b;font-weight:600;">Purok 2</span><span style="color:#0f172a;font-weight:700;">439 residents</span></div>
          <div class="mf-purok-row"><span style="color:#64748b;font-weight:600;">Purok 3</span><span style="color:#0f172a;font-weight:700;">438 residents</span></div>
          <div class="mf-purok-row"><span style="font-weight:800;color:#0f172a;">Total</span><span style="font-weight:800;color:#dc2626;">1,411 residents</span></div>
        </div>

      <?php endif; ?>

    </div>
  </div>
</div>

<script>
var FAMILIES    = <?= json_encode($byPurok) ?>;
var PRESET_FAM  = <?= (int)($presetFamily ?? 0) ?>;
var IS_EDIT     = <?= $isEdit ? 'true' : 'false' ?>;
var EDIT_FAM    = <?= $isEdit ? (int)($record['family_no'] ?? 0) : 0 ?>;

function updateFamilies() {
  var purok = document.getElementById('ml-purok').value;
  var sel   = document.getElementById('ml-family');
  sel.innerHTML = '<option value="">— Create New Family —</option>';
  if (!purok || !FAMILIES[purok]) return;
  FAMILIES[purok].forEach(function(f) {
    var opt = document.createElement('option');
    opt.value = f.family_no;
    opt.textContent = 'Family #' + f.family_no + ' — ' + f.family_head;
    var pre = IS_EDIT ? (f.family_no == EDIT_FAM) : (PRESET_FAM && f.family_no == PRESET_FAM);
    if (pre) opt.selected = true;
    sel.appendChild(opt);
  });
}
document.addEventListener('DOMContentLoaded', updateFamilies);
</script>
