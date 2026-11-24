<?php
$announcement  = $announcement ?? null;
$isEdit        = !empty($announcement['id']);
$severityMeta = [
    'information' => ['#eff6ff','#2563eb','Information','Routine updates, FYI'],
    'advisory'    => ['#f0fdf4','#16a34a','Advisory',   'Recommended action, no immediate danger'],
    'warning'     => ['#fffbeb','#d97706','Warning',    'Potential danger, take precautions'],
    'critical'    => ['#fef2f2','#dc2626','Critical',   'Immediate life-safety threat'],
];
$audienceMeta = [
    'all'        => 'General (All Users)',
    'residents'  => 'Residents Only',
    'responders' => 'Responders Only',
];
?>
<style>
.af-wrap  { padding:20px 24px 32px;max-width:700px; }
.af-head  { margin-bottom:20px; }
.af-title { font-size:1.15rem;font-weight:800;color:#0f172a;margin:0 0 2px; }
.af-card  { background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:24px; }
.af-field { margin-bottom:18px; }
.af-label { display:block;font-size:.8125rem;font-weight:700;color:#374151;margin-bottom:7px; }
.af-req   { color:#dc2626;margin-left:2px; }
.af-input { width:100%;height:44px;padding:0 14px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.875rem;color:#0f172a;background:#f8fafc;outline:none;font-family:inherit;transition:border-color .12s; }
.af-input:focus { border-color:#dc2626;background:#fff; }
.af-textarea { width:100%;padding:12px 14px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.875rem;color:#0f172a;background:#f8fafc;outline:none;font-family:inherit;resize:vertical;min-height:110px;transition:border-color .12s; }
.af-textarea:focus { border-color:#dc2626;background:#fff; }
.af-hint  { font-size:.72rem;color:#94a3b8;margin-top:5px; }
.af-row2  { display:grid;grid-template-columns:1fr 1fr;gap:14px; }
@media(max-width:600px){ .af-row2 { grid-template-columns:1fr; } }
.sev-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:8px; }
@media(max-width:600px){ .sev-grid { grid-template-columns:repeat(2,1fr); } }
.sev-opt { position:relative;cursor:pointer; }
.sev-opt input { position:absolute;opacity:0;pointer-events:none; }
.sev-chip {
  display:flex;flex-direction:column;align-items:center;justify-content:center;
  gap:3px;padding:10px 6px;border-radius:8px;border:2px solid #e2e8f0;
  text-align:center;transition:all .12s;background:#f8fafc;
}
.sev-opt:hover .sev-chip { border-color:#cbd5e1; }
.sev-opt input:checked + .sev-chip { border-color:var(--sc);background:var(--sb); }
.sev-name { font-size:.75rem;font-weight:800;color:var(--sc); }
.sev-desc { font-size:.62rem;color:#94a3b8;line-height:1.3; }
.af-btn-row { display:flex;align-items:center;gap:10px;padding-top:20px;border-top:1px solid #f1f5f9;margin-top:20px; }
.af-submit { flex:1;height:42px;background:#dc2626;color:#fff;border:none;border-radius:8px;font-size:.9rem;font-weight:700;cursor:pointer;box-shadow:0 2px 8px rgba(220,38,38,.3); }
.af-submit:hover { background:#b91c1c; }
.af-cancel { height:42px;padding:0 18px;background:#fff;color:#475569;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.875rem;font-weight:600;text-decoration:none;display:inline-flex;align-items:center; }
</style>

<div class="af-wrap">
  <div class="af-head">
    <a href="<?= base_url('announcements') ?>" style="font-size:.78rem;color:#94a3b8;text-decoration:none;display:inline-flex;align-items:center;gap:4px;margin-bottom:8px;">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
      Back to Announcements
    </a>
    <h1 class="af-title"><?= $isEdit ? 'Edit Announcement' : 'Post New Announcement' ?></h1>
    <p style="font-size:.78rem;color:#64748b;margin:0;">Announcements appear on the Resident PWA home screen and notification history.</p>
  </div>

  <div class="af-card">
    <form method="post" action="<?= base_url($isEdit ? 'announcements/' . $announcement['id'] . '/edit' : 'announcements/new') ?>">
      <?= csrf_field() ?>

      <div class="af-field">
        <label class="af-label">Title <span class="af-req">*</span></label>
        <input type="text" name="title" class="af-input"
               value="<?= e($announcement['title'] ?? '') ?>"
               placeholder="e.g. Typhoon Warning: Egay Approaching" required maxlength="200">
        <div class="af-hint">Keep short — this is the push notification headline.</div>
      </div>

      <div class="af-field">
        <label class="af-label">Body Content <span class="af-req">*</span></label>
        <textarea name="body" class="af-textarea" placeholder="Details, safety tips, instructions, or contact info…" required><?= e($announcement['body'] ?? '') ?></textarea>
      </div>

      <!-- Severity -->
      <div class="af-field">
        <label class="af-label">Severity Level <span class="af-req">*</span></label>
        <div class="sev-grid">
          <?php foreach ($severityMeta as $val => [$bg, $color, $name, $desc]): ?>
            <label class="sev-opt" style="--sc:<?= $color ?>;--sb:<?= $bg ?>;">
              <input type="radio" name="severity" value="<?= $val ?>"
                     <?= ($announcement['severity'] ?? 'information') === $val ? 'checked' : '' ?> required>
              <div class="sev-chip">
                <span class="sev-name"><?= $name ?></span>
                <span class="sev-desc"><?= $desc ?></span>
              </div>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="af-row2">
        <!-- Audience -->
        <div class="af-field">
          <label class="af-label">Audience</label>
          <select name="audience" class="af-input">
            <?php foreach ($audienceMeta as $val => $label): ?>
              <option value="<?= $val ?>" <?= ($announcement['audience'] ?? 'all') === $val ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Event date -->
        <div class="af-field">
          <label class="af-label">Event / Effective Date <span style="font-size:.7rem;color:#94a3b8;font-weight:400;">(optional)</span></label>
          <input type="datetime-local" name="event_date" class="af-input"
                 value="<?= !empty($announcement['event_date']) ? date('Y-m-d\TH:i', strtotime($announcement['event_date'])) : '' ?>">
          <div class="af-hint">Announcement auto-hides from dashboard after this date/time passes.</div>
        </div>
      </div>

      <div class="af-btn-row">
        <a href="<?= base_url('announcements') ?>" class="af-cancel">Cancel</a>
        <button type="submit" class="af-submit"><?= $isEdit ? 'Save Changes' : 'Post Announcement' ?></button>
      </div>
    </form>
  </div>
</div>
