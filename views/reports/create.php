<?php
$step         = $step         ?? 1;
$form         = $form         ?? [];
$hazardTypes  = $hazardTypes  ?? [];
$questions    = $questions    ?? [];
$vulnQuestion = $vulnQuestion ?? [];
$user         = $user         ?? null;
$errors       = $errors       ?? [];

// Hazard meta — SVG icons, no emoji
$hazardMeta = [
    'fire'       => ['label'=>'Fire',       'bg'=>'#fef2f2','color'=>'#dc2626',
        'svg'=>'<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>'],
    'flood'      => ['label'=>'Flood',      'bg'=>'#eff6ff','color'=>'#2563eb',
        'svg'=>'<path d="M2 12.5c.5-.5 1.5-.5 2 0s1.5.5 2 0 1.5-.5 2 0 1.5.5 2 0 1.5-.5 2 0 1.5.5 2 0"/><path d="M2 17.5c.5-.5 1.5-.5 2 0s1.5.5 2 0 1.5-.5 2 0 1.5.5 2 0 1.5-.5 2 0 1.5.5 2 0"/><path d="M2 7.5c.5-.5 1.5-.5 2 0s1.5.5 2 0 1.5-.5 2 0 1.5.5 2 0 1.5-.5 2 0 1.5.5 2 0"/>'],
    'earthquake' => ['label'=>'Earthquake', 'bg'=>'#fffbeb','color'=>'#d97706',
        'svg'=>'<path d="M2 12h3l2-8 3 16 3-10 2 6 2-4h3"/>'],
    'medical'    => ['label'=>'Medical',    'bg'=>'#fdf4ff','color'=>'#9333ea',
        'svg'=>'<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>'],
    'accident'   => ['label'=>'Accident',   'bg'=>'#fff7ed','color'=>'#ea580c',
        'svg'=>'<path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v9a2 2 0 0 1-2 2h-1"/><circle cx="9" cy="17" r="2"/><circle cx="19" cy="17" r="2"/>'],
    'evacuation' => ['label'=>'Evacuation', 'bg'=>'#f0fdf4','color'=>'#16a34a',
        'svg'=>'<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'],
    'security'   => ['label'=>'Security',   'bg'=>'#eef2ff','color'=>'#4f46e5',
        'svg'=>'<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>'],
    'other'      => ['label'=>'Other',      'bg'=>'#f8fafc','color'=>'#64748b',
        'svg'=>'<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>'],
];

$selectedHazard = $form['hazard_type']     ?? '';
$isEmergency    = $form['is_emergency']    ?? 1;
$answers        = $form['answers']         ?? [];
$address        = $form['incident_address']?? '';
$lat            = $form['incident_lat']    ?? '';
$lng            = $form['incident_lng']    ?? '';
$description    = $form['description']     ?? '';
$homeAddress    = $user['home_address']    ?? '';
?>

<style>
/* ── Report wizard ─────────────────────────────────────────── */
.rw-wrap { padding: 24px 16px 40px; max-width: 820px; margin: 0 auto; }

/* Page header */
.rw-head { display:flex;align-items:center;justify-content:space-between;margin-bottom:18px; }
.rw-title { font-size:1.15rem;font-weight:800;color:#1e293b;letter-spacing:-.02em;margin:0 0 1px; }
.rw-sub   { font-size:.72rem;color:#94a3b8;margin:0;font-weight:600; }
.rw-cancel { display:inline-flex;align-items:center;height:30px;padding:0 12px;background:#fff;border:1px solid #e2e8f0;border-radius:6px;font-size:.75rem;font-weight:700;color:#64748b;text-decoration:none;transition:all .12s; }
.rw-cancel:hover { background:#f8fafc;color:#1e293b;border-color:#cbd5e1; }

/* Stepper */
.rw-stepper { display:flex;align-items:center;background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:14px 20px;margin-bottom:20px;gap:0;box-shadow:0 1px 2px rgba(0,0,0,0.01); }
.rw-step { display:flex;align-items:center;flex:1;position:relative; }
.rw-step-circle {
  width:26px;height:26px;border-radius:8px;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;
  font-size:.725rem;font-weight:800;
  transition: all 0.2s ease;
}
.rw-step-circle.done   { background:#16a34a;color:#fff; }
.rw-step-circle.active { background:#dc2626;color:#fff;box-shadow:0 3px 8px rgba(220,38,38,.2); }
.rw-step-circle.future { background:#f8fafc;color:#94a3b8;border:1px solid #e2e8f0; }
.rw-step-label { font-size:.7rem;font-weight:700;color:#94a3b8;margin-left:10px;white-space:nowrap;transition: color 0.2s; }
.rw-step-label.active { color:#dc2626; }
.rw-step-label.done   { color:#1e293b; }
.rw-step-line { flex:1;height:2px;background:#f1f5f9;margin:0 14px;border-radius:10px; }
.rw-step-line.done { background:#16a34a;opacity:0.4; }

/* Card */
.rw-card { background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;box-shadow: 0 2px 10px -4px rgba(0,0,0,0.04); }
.rw-step-title { font-size:1rem;font-weight:800;color:#1e293b;letter-spacing:-.01em;margin:0 0 4px; }
.rw-step-desc  { font-size:.78rem;color:#64748b;margin:0 0 20px;line-height:1.5; }

/* Hazard grid */
.rw-hazard-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px; }
@media(max-width:640px){ .rw-hazard-grid { grid-template-columns:repeat(2,1fr); } }
.rw-hazard-card {
  border:1px solid #e2e8f0;border-radius:10px;padding:16px 12px;
  text-align:center;cursor:pointer;
  transition:all .15s ease;
  display:block; position:relative; background:#fff;
}
.rw-hazard-card:hover {
  border-color:#fca5a5;
  transform:translateY(-2px);
  box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
}
.rw-hazard-card.selected {
  border-color:#dc2626;background:#fff;
  box-shadow:0 8px 16px -6px rgba(220,38,38,0.15);
  border-width: 1.5px;
}
.rw-hazard-icon { width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 10px;transition:all .15s; }
.rw-hazard-icon svg { width:18px; height:18px; }
.rw-hazard-card.selected .rw-hazard-icon { transform:scale(1.05); }
.rw-hazard-label { font-size:.78rem;font-weight:700;color:#1e293b; }
.rw-hazard-radio { position:absolute;opacity:0;pointer-events:none; }
/* Checkmark badge on selected card */
.rw-hcheck {
  position:absolute;top:8px;right:8px;
  width:18px;height:18px;border-radius:50%;
  background:#dc2626;display:none;
  align-items:center;justify-content:center;
  color:#fff;box-shadow:0 2px 4px rgba(0,0,0,0.1);
}
.rw-hazard-card.selected .rw-hcheck { display:flex; }

/* Emergency toggle */
.rw-emergency {
  display:flex;align-items:center;gap:12px;
  padding:14px 16px;
  background:#fff1f2;border:1px solid #ffe4e6;border-radius:10px;
  margin-bottom:20px;cursor:pointer;
  transition:all .15s;
}
.rw-emergency:hover { border-color:#fecaca; }
.rw-emergency input[type=checkbox] { width:16px;height:16px;accent-color:#dc2626;flex-shrink:0;cursor:pointer; }
.rw-emergency-label { font-size:.825rem;font-weight:800;color:#991b1b; }
.rw-emergency-sub   { font-size:.72rem;color:#b91c1c;margin-top:1px;opacity:0.8;font-weight:600; }

/* Triage question */
.rw-question { background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:20px 24px;margin-bottom:16px;transition:all .2s; }
.rw-question:focus-within { border-color:#cbd5e1; background:#fff; box-shadow:0 4px 6px -1px rgba(0,0,0,0.02); }
.rw-question-text { font-size:.9rem;font-weight:700;color:#1e293b;margin-bottom:14px;line-height:1.5; }
.rw-options { display:flex;flex-wrap:wrap;gap:10px;margin-bottom:12px; }
.rw-option { display:flex;align-items:center;gap:8px;padding:8px 16px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;font-size:.875rem;color:#475569;cursor:pointer;font-weight:600;transition:all .15s;user-select:none; }
.rw-option:hover { border-color:#cbd5e1; background:#f8fafc; }
.rw-option:has(input:checked) { border-color:#dc2626;background:#fff1f2;color:#dc2626;box-shadow:0 0 0 1px #dc2626; }
.rw-option input { width:16px;height:16px;accent-color:#dc2626;cursor:pointer; }
.rw-unsure { display:flex;align-items:center;gap:8px;font-size:.8rem;color:#94a3b8;cursor:pointer;font-weight:600;margin-top:4px; }
.rw-unsure:hover { color:#64748b; }
.rw-unsure:has(input:checked) { color:#475569; }
.rw-unsure input { width:16px;height:16px;accent-color:#94a3b8;cursor:pointer; }
.rw-vuln { background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:20px 24px;margin-bottom:16px; }

/* Location */
.rw-field { margin-bottom: 20px; }
.rw-field label { display:block;font-size:.8125rem;font-weight:700;color:#1e293b;margin-bottom:8px;letter-spacing:.01em; }
.rw-media-upload { border: 2px dashed #e2e8f0; border-radius: 12px; padding: 20px; text-align: center; cursor: pointer; transition: all .2s; background: #f8fafc; }
.rw-media-upload:hover { border-color: #dc2626; background: #fff; }
.rw-media-placeholder { color: #94a3b8; padding: 20px 0; }
.rw-media-previews { display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 10px; }
.rw-media-preview-item { position: relative; aspect-ratio: 1; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; }
.rw-media-preview-item img, .rw-media-preview-item video { width: 100%; height: 100%; object-fit: cover; }
.rw-media-preview-item .remove-btn { position: absolute; top: 4px; right: 4px; background: rgba(0,0,0,0.5); color: #fff; border: none; border-radius: 50%; width: 18px; height: 18px; font-size: 10px; display: flex; align-items: center; justify-content: center; cursor: pointer; }
.rw-input {
  width:100%;height:46px;padding:0 16px;
  border:1.5px solid #e2e8f0;border-radius:10px;
  font-size:.875rem;color:#1e293b;background:#f8fafc;
  outline:none;transition:all .2s;
  font-family:inherit;
}
.rw-input:focus { border-color:#dc2626;background:#fff;box-shadow:0 0 0 4px rgba(220,38,38,.08); }
.rw-textarea { height:auto;padding:14px 16px;resize:vertical;min-height:100px;line-height:1.6; }
.rw-error { font-size:.78rem;color:#dc2626;margin-top:6px;font-weight:500; }

/* Review section */
.rw-review-section { margin-bottom:24px;padding:20px;background:#f8fafc;border-radius:12px;border:1px solid #f1f5f9; }
.rw-review-section:last-of-type { margin-bottom:0; }
.rw-review-label { font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#64748b;margin-bottom:16px; }
.rw-review-row { display:flex;justify-content:space-between;gap:20px;margin-bottom:10px;font-size:.875rem; }
.rw-review-row:last-child { margin-bottom:0; }
.rw-review-key { color:#64748b;font-weight:600; }
.rw-review-val { color:#1e293b;font-weight:700;text-align:right; }

/* Buttons */
.rw-btn-row { display:flex;justify-content:space-between;align-items:center;margin-top:28px;padding-top:24px;border-top:1px solid #f1f5f9; }
.rw-btn-next { display:inline-flex;align-items:center;gap:8px;height:44px;padding:0 24px;background:#dc2626;color:#fff;border:none;border-radius:8px;font-size:.9rem;font-weight:700;cursor:pointer;box-shadow:0 4px 12px rgba(220,38,38,.2);transition:all .2s; }
.rw-btn-next:hover { background:#b91c1c; transform:translateY(-1px); box-shadow:0 6px 16px rgba(220,38,38,.25); }
.rw-btn-back { display:inline-flex;align-items:center;gap:6px;height:42px;padding:0 20px;background:#fff;color:#475569;border:1.5px solid #e2e8f0;border-radius:8px;font-size:.875rem;font-weight:700;cursor:pointer;transition:all .2s; }
.rw-btn-back:hover { border-color:#cbd5e1; background:#f8fafc; color:#1e293b; }
</style>

<div class="rw-wrap">

  <!-- Header -->
  <div class="rw-head">
    <div>
      <h1 class="rw-title">New Incident Report</h1>
      <p class="rw-sub">Step <?= $step ?> of 4</p>
    </div>
    <a href="<?= base_url('reports') ?>" class="rw-cancel">Cancel</a>
  </div>

  <!-- Stepper -->
  <div class="rw-stepper">
    <?php
    $stepLabels = [1=>'Hazard Type', 2=>'Triage Questions', 3=>'Location', 4=>'Review & Submit'];
    foreach ($stepLabels as $n => $lbl):
      $done   = $n < $step;
      $active = $n === $step;
      $cls    = $done ? 'done' : ($active ? 'active' : 'future');
    ?>
      <div class="rw-step">
        <div class="rw-step-circle <?= $cls ?>">
          <?php if ($done): ?>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
          <?php else: ?>
            <?= $n ?>
          <?php endif; ?>
        </div>
        <span class="rw-step-label <?= $active ? 'active' : '' ?>"><?= e($lbl) ?></span>
      </div>
      <?php if ($n < 4): ?>
        <div class="rw-step-line <?= $done ? 'done' : '' ?>"></div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>

  <!-- Main card -->
  <div class="rw-card">

    <?php if (!empty($errors['csrf'])): ?>
      <div style="padding:10px 14px;border-radius:6px;margin-bottom:16px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;font-size:.8rem;"><?= e($errors['csrf']) ?></div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('reports/new') ?>" id="report-form" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <input type="hidden" name="step" value="<?= $step ?>">

      <!-- ── STEP 1: Hazard Type ─────────────────────────────── -->
      <?php if ($step === 1): ?>
        <div class="rw-step-title">Hazard Type</div>
        <p class="rw-step-desc">Select the type of emergency or hazard you are reporting.</p>

        <?php if (!empty($errors['hazard_type'])): ?>
          <div style="padding:8px 12px;border-radius:5px;margin-bottom:14px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;font-size:.8rem;"><?= e($errors['hazard_type']) ?></div>
        <?php endif; ?>

        <div class="rw-hazard-grid">
          <?php foreach ($hazardTypes as $ht):
            $m = $hazardMeta[$ht] ?? ['label'=>ucfirst($ht),'bg'=>'#f8fafc','color'=>'#64748b','svg'=>'<circle cx="12" cy="12" r="10"/>'];
            $sel = $selectedHazard === $ht;
          ?>
            <label class="rw-hazard-card <?= $sel ? 'selected' : '' ?>" id="hcard-<?= $ht ?>">
              <div class="rw-hcheck">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4"><polyline points="20 6 9 17 4 12"/></svg>
              </div>
              <div class="rw-hazard-icon" style="background:<?= $m['bg'] ?>;color:<?= $m['color'] ?>;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                  <?= $m['svg'] ?>
                </svg>
              </div>
              <div class="rw-hazard-label"><?= e($m['label']) ?></div>
              <input class="rw-hazard-radio" type="radio" name="hazard_type" value="<?= e($ht) ?>" <?= $sel ? 'checked' : '' ?>>
            </label>
          <?php endforeach; ?>
        </div>

        <label class="rw-emergency">
          <input type="checkbox" name="is_emergency" value="1" <?= $isEmergency ? 'checked' : '' ?> id="rw-emergency-cb">
          <div>
            <div class="rw-emergency-label">Emergency — life-threatening, immediate response needed</div>
            <div class="rw-emergency-sub">The hazard type tells <strong>what</strong> happened. This checkbox tells <strong>how urgent</strong> it is. Check if lives or property are at immediate risk. Uncheck for minor or non-urgent incidents — a photo will be required as documentation.</div>
          </div>
        </label>

        <div class="rw-btn-row">
          <span></span>
          <button type="submit" class="rw-btn-next">
            Next
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </button>
        </div>
      <?php endif; ?>

      <!-- ── STEP 2: Triage Questions ─────────────────────────── -->
      <?php if ($step === 2): ?>
        <div class="rw-step-title">Triage Questions</div>
        <p class="rw-step-desc">Answer each question honestly. Select "Unsure" if you don't know — the report will be flagged for reassessment.</p>

        <?php if (!empty($errors['answers'])): ?>
          <div style="display:flex;align-items:center;gap:9px;padding:11px 14px;border-radius:8px;margin-bottom:16px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;font-size:.8rem;font-weight:600;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?= e($errors['answers']) ?>
          </div>
        <?php endif; ?>

        <?php foreach ($questions as $q):
          $qKey = $q['key'];
          $currentAns = $answers[$qKey] ?? '';
          $unanswered = !empty($errors['answers']) && $currentAns === '';
        ?>
          <div class="rw-question" style="<?= $unanswered ? 'border-color:#fca5a5;background:#fff8f8;' : '' ?>">
            <div class="rw-question-text">
              <?= e($q['text']) ?>
              <?php if ($unanswered): ?>
                <span style="font-size:.7rem;color:#dc2626;font-weight:700;margin-left:6px;">— Required</span>
              <?php endif; ?>
            </div>
            <div class="rw-options">
              <?php foreach ($q['options'] as $opt):
                $optVal = $opt['label'];
                $checked = $currentAns === $optVal;
              ?>
                <label class="rw-option">
                  <input type="radio" name="answers[<?= e($qKey) ?>]" value="<?= e($optVal) ?>" <?= $checked ? 'checked' : '' ?> required>
                  <?= e($optVal) ?>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>

        <!-- Vulnerability question -->
        <?php $vulnUnanswered = !empty($errors['answers']) && empty($answers['vulnerability']); ?>
        <div class="rw-vuln" style="<?= $vulnUnanswered ? 'border-color:#fca5a5;background:#fff8f8;' : '' ?>">
          <div class="rw-question-text">
            <?= e($vulnQuestion['text']) ?>
            <?php if ($vulnUnanswered): ?>
              <span style="font-size:.7rem;color:#dc2626;font-weight:700;margin-left:6px;">— Required</span>
            <?php endif; ?>
          </div>
          <div class="rw-options">
            <?php foreach ($vulnQuestion['options'] as $opt):
              $optVal = $opt['label'];
              $checked = ($answers['vulnerability'] ?? '') === $optVal;
            ?>
              <label class="rw-option">
                <input type="radio" name="answers[vulnerability]" value="<?= e($optVal) ?>" <?= $checked ? 'checked' : '' ?>>
                <?= e($optVal) ?>
              </label>
            <?php endforeach; ?>
          </div>
          <div style="font-size:.72rem;color:#92400e;margin-top:6px;">If "Yes", a +2 priority bonus is added to the triage score.</div>
        </div>

        <div class="rw-btn-row">
          <button type="submit" name="step" value="1" class="rw-btn-back">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
            Back
          </button>
          <button type="submit" class="rw-btn-next">
            Next
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </button>
        </div>
      <?php endif; ?>

      <!-- ── STEP 3: Location ─────────────────────────────────── -->
      <?php if ($step === 4): ?>
<script>
(function(){
  var lat = <?= (float)($lat ?: 17.149) ?>;
  var lng = <?= (float)($lng ?: 121.889) ?>;
  var map = L.map('review-map', {
    zoomControl: false,
    dragging: false,
    scrollWheelZoom: false,
    doubleClickZoom: false,
    boxZoom: false
  }).setView([lat, lng], 16);
  
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap'
  }).addTo(map);

  var icon = L.divIcon({
    html: '<div style="width:14px;height:14px;border-radius:50%;background:#dc2626;border:3px solid #fff;box-shadow:0 2px 8px rgba(220,38,38,.6);"></div>',
    className:'', iconSize:[14,14], iconAnchor:[7,7]
  });
  L.marker([lat, lng], { icon: icon }).addTo(map);

  setTimeout(function() { map.invalidateSize(); }, 300);
})();
</script>
<?php endif; ?>

<?php if ($step === 3): ?>
<script>
function previewMedia(input) {
  var container = document.getElementById('media-preview-container');
  var files = input.files;
  if (files.length > 0) {
    container.innerHTML = ''; // Clear placeholder
    for (var i = 0; i < files.length; i++) {
      (function(file) {
        var reader = new FileReader();
        reader.onload = function(e) {
          var item = document.createElement('div');
          item.className = 'rw-media-preview-item';
          if (file.type.startsWith('image/')) {
            item.innerHTML = '<img src="' + e.target.result + '">';
          } else {
            item.innerHTML = '<video src="' + e.target.result + '"></video>';
          }
          container.appendChild(item);
        };
        reader.readAsDataURL(file);
      })(files[i]);
    }
  }
}
</script>
        <div class="rw-step-title">Location</div>
        <p class="rw-step-desc">Pin the incident location on the map or enter the address manually.</p>

        <div class="rw-field">
          <label>Incident Address <span style="color:#dc2626;">*</span></label>
          <input class="rw-input <?= !empty($errors['incident_address']) ? 'is-err' : '' ?>" type="text"
            name="incident_address" placeholder="Street, barangay, city"
            value="<?= e($address) ?>" required>
          <?php if (!empty($errors['incident_address'])): ?><div class="rw-error"><?= e($errors['incident_address']) ?></div><?php endif; ?>
        </div>

        <div class="rw-field">
          <label>Photos / Videos <span id="media-req-label" style="font-size:.7rem;color:#94a3b8;font-weight:400;">(optional)</span></label>
          <?php if (!empty($errors['media'])): ?><div class="rw-error" style="margin-bottom:8px;"><?= e($errors['media']) ?></div><?php endif; ?>
          <div class="rw-media-upload" onclick="document.getElementById('media-input').click()">
            <input type="file" id="media-input" name="media[]" multiple accept="image/*,video/*" style="display:none" onchange="previewMedia(this)">
            <div id="media-preview-container" class="rw-media-previews">
              <div class="rw-media-placeholder">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                <div style="margin-top:8px;font-weight:600;">Click to upload photos or videos</div>
                <div style="font-size:.65rem;color:#94a3b8;margin-top:2px;" id="media-hint">PNG, JPG, MP4 up to 10MB each</div>
              </div>
            </div>
          </div>
        </div>

        <?php if ($homeAddress): ?>
          <div class="rw-field">
            <label>Home Address <span style="font-size:.7rem;color:#94a3b8;font-weight:400;">(for reference)</span></label>
            <input class="rw-input" type="text" value="<?= e($homeAddress) ?>" readonly style="color:#94a3b8;background:#f1f5f9;">
          </div>
        <?php endif; ?>

        <div class="rw-field">
          <label>Pin on Map <span style="font-size:.7rem;color:#94a3b8;font-weight:400;">Drag pin or click to set location</span></label>
          <div id="incident-map" style="height:340px;border:1.5px solid #e2e8f0;border-radius:6px;"></div>
          <input type="hidden" name="incident_lat" id="incident_lat" value="<?= e($lat) ?>">
          <input type="hidden" name="incident_lng" id="incident_lng" value="<?= e($lng) ?>">
          <?php if (!empty($errors['incident_lat'])): ?><div class="rw-error"><?= e($errors['incident_lat']) ?></div><?php endif; ?>
        </div>

        <div class="rw-field">
          <label>Description <span style="font-size:.7rem;color:#94a3b8;font-weight:400;">(optional)</span></label>
          <textarea class="rw-input rw-textarea" name="description" placeholder="Additional details about the incident..."><?= e($description) ?></textarea>
        </div>

        <div class="rw-btn-row">
          <button type="submit" name="step" value="2" class="rw-btn-back">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
            Back
          </button>
          <button type="submit" class="rw-btn-next">
            Next
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
          </button>
        </div>
      <?php endif; ?>

      <!-- ── STEP 4: Review & Submit ───────────────────────────── -->
      <?php if ($step === 4): ?>
        <div class="rw-step-title">Review & Submit</div>
        <p class="rw-step-desc">Please confirm all details before submitting your report.</p>

        <?php
        $hazardLabel = $hazardMeta[$selectedHazard]['label'] ?? ucfirst($selectedHazard);
        $hm = $hazardMeta[$selectedHazard] ?? ['bg'=>'#f8fafc','color'=>'#64748b','svg'=>'<circle cx="12" cy="12" r="10"/>'];
        ?>

        <div class="rw-review-section">
          <div class="rw-review-label">Hazard Type</div>
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
            <div style="width:36px;height:36px;border-radius:8px;background:<?= $hm['bg'] ?>;color:<?= $hm['color'] ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><?= $hm['svg'] ?></svg>
            </div>
            <div>
              <div style="font-size:.875rem;font-weight:700;color:#0f172a;"><?= e($hazardLabel) ?></div>
              <div style="font-size:.75rem;color:<?= $isEmergency ? '#dc2626' : '#16a34a' ?>;font-weight:600;">
                <?= $isEmergency ? 'Emergency' : 'Non-Emergency' ?>
              </div>
            </div>
          </div>
        </div>

        <div class="rw-review-section">
          <div class="rw-review-label">Triage Answers</div>
          <?php foreach ($questions as $q):
            $qKey = $q['key'];
            $ans  = $answers[$qKey] ?? 'Unsure';
          ?>
            <div class="rw-review-row">
              <span class="rw-review-key"><?= e($q['text']) ?></span>
              <span class="rw-review-val <?= $ans === 'Unsure' ? '' : '' ?>"><?= e($ans) ?></span>
            </div>
          <?php endforeach; ?>
          <div class="rw-review-row">
            <span class="rw-review-key"><?= e($vulnQuestion['text'] ?? 'Vulnerability') ?></span>
            <span class="rw-review-val"><?= e($answers['vulnerability'] ?? 'No') ?></span>
          </div>
        </div>

        <?php if (!empty($form['media_temp'])): ?>
        <div class="rw-review-section">
          <div class="rw-review-label">Attached Media</div>
          <div class="rw-media-previews">
            <?php foreach ($form['media_temp'] as $m):
              $mediaUrl = base_url('uploads/temp/' . e($m['temp_name']));
            ?>
              <div class="rw-media-preview-item">
                <?php if (str_starts_with($m['type'], 'image/')): ?>
                  <img src="<?= $mediaUrl ?>" alt="<?= e($m['name']) ?>">
                <?php else: ?>
                  <video src="<?= $mediaUrl ?>" controls style="width:100%;height:100%;object-fit:cover;"></video>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <div class="rw-review-section">
          <div class="rw-review-label">Location</div>
          <div id="review-map" style="height:180px;border-radius:10px;margin-bottom:16px;border:1px solid #e2e8f0;"></div>
          <div class="rw-review-row"><span class="rw-review-key">Address</span><span class="rw-review-val"><?= e($address ?: '—') ?></span></div>
          <?php if ($lat !== '' && $lng !== ''): ?>
            <div class="rw-review-row"><span class="rw-review-key">Coordinates</span><span class="rw-review-val"><?= e($lat) ?>, <?= e($lng) ?></span></div>
          <?php endif; ?>
          <?php if ($description): ?>
            <div class="rw-review-row"><span class="rw-review-key">Description</span><span class="rw-review-val"><?= e($description) ?></span></div>
          <?php endif; ?>
        </div>

        <?php if (($user['role'] ?? '') === 'admin'): ?>
          <div style="padding:10px 14px;border-radius:6px;margin-bottom:14px;background:#eff6ff;border:1px solid #bfdbfe;color:#1e40af;font-size:.78rem;font-weight:600;display:flex;align-items:center;gap:8px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg>
            As admin, this report will be automatically verified and skip the verification queue.
          </div>
        <?php endif; ?>

        <div class="rw-btn-row">
          <button type="submit" name="step" value="3" class="rw-btn-back">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
            Back
          </button>
          <button type="submit" class="rw-btn-next" style="background:#16a34a;box-shadow:0 2px 8px rgba(22,163,74,.3);">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Submit Report
          </button>
        </div>
      <?php endif; ?>

    </form>
  </div>
</div>

<?php if ($step === 4): ?>
<?php
$extraScripts = $extraScripts ?? '';
ob_start();
?>
<script>
(function(){
  var lat = <?= (float)($lat ?: 17.149) ?>;
  var lng = <?= (float)($lng ?: 121.889) ?>;
  var map = L.map('review-map', {
    zoomControl: false,
    dragging: false,
    scrollWheelZoom: false,
    doubleClickZoom: false,
    boxZoom: false,
    fadeAnimation: true
  }).setView([lat, lng], 16);
  
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap', maxZoom: 19
  }).addTo(map);

  var icon = L.divIcon({
    html: '<div style="width:18px;height:18px;border-radius:50%;background:#dc2626;border:3px solid #fff;box-shadow:0 2px 10px rgba(220,38,38,.4);position:relative;">' +
            '<div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:6px;height:6px;background:#fff;border-radius:50%;"></div>' +
          '</div>',
    className:'', iconSize:[18,18], iconAnchor:[9,9]
  });
  L.marker([lat, lng], { icon: icon }).addTo(map);

  setTimeout(function() { 
    map.invalidateSize(); 
  }, 400);
})();
</script>
<?php $extraScripts .= ob_get_clean(); ?>
<?php endif; ?>

<?php if ($step === 3): ?>
<?php
$extraScripts = $extraScripts ?? '';
ob_start();
?>
<script>
function previewMedia(input) {
  var container = document.getElementById('media-preview-container');
  var files = input.files;
  if (files.length > 0) {
    container.innerHTML = '';
    for (var i = 0; i < files.length; i++) {
      (function(file) {
        var reader = new FileReader();
        reader.onload = function(e) {
          var item = document.createElement('div');
          item.className = 'rw-media-preview-item';
          if (file.type.startsWith('image/')) {
            item.innerHTML = '<img src="' + e.target.result + '">';
          } else {
            item.innerHTML = '<video src="' + e.target.result + '"></video>';
          }
          container.appendChild(item);
        };
        reader.readAsDataURL(file);
      })(files[i]);
    }
  }
}

// Update media label based on emergency status
(function() {
  var isEmerg = <?= (int)($isEmergency ?? 1) ?>;
  var label = document.getElementById('media-req-label');
  var hint = document.getElementById('media-hint');
  if (label) {
    if (isEmerg === 0) {
      label.innerHTML = '<span style="color:#dc2626;font-weight:600;">(required for non-emergency)</span>';
      if (hint) hint.innerHTML = 'At least one photo is required for non-emergency reports. PNG, JPG, MP4 up to 10MB each.';
    } else {
      label.innerHTML = '(optional)';
    }
  }
})();

(function () {
  var initLat = <?= $lat !== '' ? (float)$lat : 17.149 ?>;
  var initLng = <?= $lng !== '' ? (float)$lng : 121.889 ?>;

  var map = L.map('incident-map', {
    fadeAnimation: true
  }).setView([initLat, initLng], 15);
  
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap', maxZoom: 19
  }).addTo(map);

  setTimeout(function() {
    map.invalidateSize();
  }, 400);

  var marker = L.marker([initLat, initLng], { 
    draggable: true,
    icon: L.icon({
      iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
      iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
      shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
      iconSize: [25, 41],
      iconAnchor: [12, 41],
      popupAnchor: [1, -34],
      shadowSize: [41, 41]
    })
  }).addTo(map);

  function reverseGeocode(lat, lng) {
    var addrInput = document.querySelector('input[name="incident_address"]');
    if (!addrInput) return;
    addrInput.placeholder = 'Locating address...';
    fetch('https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=' + lat + '&lon=' + lng)
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (data && data.display_name) {
          addrInput.value = data.display_name;
        }
        addrInput.placeholder = 'Street, barangay, city';
      })
      .catch(function() {
        addrInput.placeholder = 'Street, barangay, city';
      });
  }

  function updateInputs(lat, lng, doGeocode) {
    document.getElementById('incident_lat').value = lat.toFixed(7);
    document.getElementById('incident_lng').value = lng.toFixed(7);
    if (doGeocode) {
      reverseGeocode(lat, lng);
    }
  }
  
  if (initLat && initLng && !document.querySelector('input[name="incident_address"]').value) {
    updateInputs(initLat, initLng, true);
  } else {
    updateInputs(initLat, initLng, false);
  }

  marker.on('dragend', function () {
    var p = marker.getLatLng();
    updateInputs(p.lat, p.lng, true);
  });
  map.on('click', function (e) {
    marker.setLatLng(e.latlng);
    updateInputs(e.latlng.lat, e.latlng.lng, true);
  });

  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function (pos) {
      var lat = pos.coords.latitude, lng = pos.coords.longitude;
      map.setView([lat, lng], 16);
      marker.setLatLng([lat, lng]);
      updateInputs(lat, lng);
    }, function () { });
  }
})();
</script>
<?php $extraScripts .= ob_get_clean(); ?>
<?php endif; ?>

<?php if ($step === 1): ?>
<script>
(function(){
  // Highlight selected hazard card on click
  document.querySelectorAll('.rw-hazard-card').forEach(function(card){
    card.addEventListener('click', function(){
      document.querySelectorAll('.rw-hazard-card').forEach(function(c){ c.classList.remove('selected'); });
      card.classList.add('selected');
      card.querySelector('input[type=radio]').checked = true;
    });
  });
})();
</script>
<?php endif; ?>

<?php if ($step === 2): ?>
<script>
(function(){
  // Radio button validation visual feedback if needed
  document.querySelectorAll('.rw-option input[type=radio]').forEach(function(r){
    r.addEventListener('change', function(){
      // Option selection logic
    });
  });
})();
</script>
<?php endif; ?>
