<?php
$typeLabels = [
    'barangay' => 'Barangay Officials',
    'rescue'   => 'Dispatch & Command',
    'police'   => 'Police & Security',
    'fire'     => 'Fire Protection',
    'medical'  => 'Medical Support',
    'utility'  => 'Utilities',
    'social'   => 'Social Services',
    'other'    => 'Other Contacts',
];
$typeColors = [
    'barangay' => ['#eff6ff','#2563eb'],
    'rescue'   => ['#fff7ed','#ea580c'],
    'police'   => ['#eef2ff','#4f46e5'],
    'fire'     => ['#fef2f2','#dc2626'],
    'medical'  => ['#f0fdf4','#16a34a'],
    'utility'  => ['#ecfeff','#0891b2'],
    'social'   => ['#fdf4ff','#9333ea'],
    'other'    => ['#f8fafc','#64748b'],
];
$typeIcons = [
    'barangay' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
    'rescue'   => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
    'police'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
    'fire'     => '<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>',
    'medical'  => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
    'utility'  => '<circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>',
    'social'   => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>',
    'other'    => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4a2 2 0 0 1 1.99-2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6.13 6.13l1.82-1.82a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>',
];
?>
<style>
.rsp-contact-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; margin: 0 16px 10px; overflow: hidden; }
.rsp-contact-row { display: flex; align-items: center; gap: 12px; padding: 13px 14px; border-bottom: 1px solid #f8fafc; text-decoration: none; color: inherit; }
.rsp-contact-row:last-child { border-bottom: none; }
.rsp-contact-row:active { background: #f8fafc; }
.rsp-contact-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.rsp-call-btn { display: inline-flex; align-items: center; gap: 5px; height: 30px; padding: 0 12px; background: #16a34a; color: #fff; border-radius: 7px; font-size: .72rem; font-weight: 700; text-decoration: none; flex-shrink: 0; }
</style>

<div style="padding-top: 12px;">

  <?php foreach ($typeLabels as $type => $label): ?>
    <?php if (!empty($contacts[$type])): ?>
      <?php
      [$iconBg, $iconColor] = $typeColors[$type] ?? ['#f8fafc','#64748b'];
      $iconPath = $typeIcons[$type] ?? $typeIcons['other'];
      ?>
      <div style="font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;padding:4px 16px 8px;"><?= e($label) ?></div>
      <div class="rsp-contact-card">
        <?php foreach ($contacts[$type] as $c): ?>
          <div class="rsp-contact-row">
            <div class="rsp-contact-icon" style="background:<?= $iconBg ?>;">
              <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="<?= $iconColor ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?= $iconPath ?></svg>
            </div>
            <div style="flex:1;min-width:0;">
              <div style="font-size:.8125rem;font-weight:700;color:#0f172a;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($c['name']) ?></div>
              <div style="font-size:.72rem;color:#64748b;font-family:monospace;"><?= e($c['phone']) ?></div>
            </div>
            <a href="tel:<?= e(preg_replace('/\s+/', '', $c['phone'])) ?>" class="rsp-call-btn">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4a2 2 0 0 1 1.99-2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6.13 6.13l1.82-1.82a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
              Call
            </a>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  <?php endforeach; ?>

  <?php if (empty(array_filter($contacts))): ?>
    <div style="text-align:center;padding:48px 24px;">
      <div style="width:60px;height:60px;border-radius:16px;background:#f8fafc;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4a2 2 0 0 1 1.99-2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6.13 6.13l1.82-1.82a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
      </div>
      <div style="font-size:.875rem;font-weight:700;color:#94a3b8;">No contacts configured</div>
      <div style="font-size:.78rem;color:#cbd5e1;margin-top:4px;">Contact your administrator to add emergency contacts.</div>
    </div>
  <?php endif; ?>

  <!-- Protocol note -->
  <div style="margin: 8px 16px 20px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 12px; padding: 13px 14px; display: flex; align-items: flex-start; gap: 10px;">
    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <div style="font-size:.78rem;color:#1d4ed8;line-height:1.5;font-weight:500;">Use radio for primary communication. These phone lines are for backup and coordination only.</div>
  </div>

</div>
