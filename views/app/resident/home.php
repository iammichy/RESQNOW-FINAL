<?php
$announcements = $announcements ?? [];
$activeReports = $activeReports ?? [];
$firstName = e(explode(',', auth()['name'])[0] ?? explode(' ', auth()['name'])[0]);
?>

<!-- Welcome Section -->
<div style="padding:20px 16px 8px;display:flex;align-items:center;justify-content:space-between;">
  <div>
    <div style="font-size:1.15rem;font-weight:800;color:#0f172a;letter-spacing:-.02em;">Hello, <?= $firstName ?>!</div>
    <div style="font-size:.8rem;color:#64748b;margin-top:2px;">Stay safe and informed today.</div>
  </div>
</div>

<!-- Emergency Report Button -->
<div style="padding:12px 16px 16px;">
  <a href="<?= base_url('app/resident/report') ?>"
     style="display:flex;align-items:center;justify-content:center;gap:10px;
            height:52px;background:#dc2626;color:#fff;border-radius:14px;
            font-size:.95rem;font-weight:700;text-decoration:none;
            box-shadow:0 4px 16px rgba(220,38,38,.35);letter-spacing:.01em;">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
      <polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/>
      <line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    Report Emergency
  </a>
</div>

<!-- Active Reports -->
<?php if (!empty($activeReports)): ?>
<div style="font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;padding:4px 16px 8px;">My Active Reports</div>
<?php foreach ($activeReports as $r):
  $statusColors = ['submitted'=>'#ea580c','verified'=>'#2563eb','triaged'=>'#9333ea','assigned'=>'#4f46e5','on_scene'=>'#0d9488','in_progress'=>'#d97706','resolved'=>'#16a34a'];
  $sc = $statusColors[$r['status']] ?? '#64748b';
?>
  <a href="<?= base_url('app/resident/track/' . e($r['code'])) ?>"
     style="display:flex;align-items:center;justify-content:space-between;
            background:#fff;border:1px solid #e2e8f0;border-radius:14px;
            margin:0 16px 10px;padding:14px 16px;text-decoration:none;color:inherit;">
    <div>
      <div style="font-size:.875rem;font-weight:700;color:#0f172a;"><?= e($r['code']) ?></div>
      <div style="font-size:.75rem;color:#64748b;margin-top:2px;text-transform:capitalize;"><?= e(str_replace('_',' ',$r['hazard_type'])) ?></div>
    </div>
    <span style="background:<?= $sc ?>;color:#fff;border-radius:6px;padding:3px 10px;font-size:.7rem;font-weight:700;">
      <?= e(ucwords(str_replace('_',' ',$r['status']))) ?>
    </span>
  </a>
<?php endforeach; ?>
<?php endif; ?>

<!-- Announcements -->
<div style="font-size:.65rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;padding:4px 16px 8px;">Barangay Advisories</div>
<?php if (empty($announcements)): ?>
  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;margin:0 16px 10px;padding:20px 16px;text-align:center;font-size:.8rem;color:#94a3b8;">
    No active advisories at this time.
  </div>
<?php else: ?>
  <?php
  $sevColors = ['critical'=>'#dc2626','warning'=>'#d97706','advisory'=>'#2563eb','information'=>'#64748b'];
  foreach ($announcements as $a):
    $sc2 = $sevColors[$a['severity'] ?? 'information'] ?? '#64748b';
  ?>
    <div style="background:#fff;border:1px solid #e2e8f0;border-left:4px solid <?= $sc2 ?>;border-radius:14px;margin:0 16px 10px;padding:14px 16px;">
      <div style="font-size:.875rem;font-weight:800;color:#0f172a;margin-bottom:5px;"><?= e($a['title']) ?></div>
      <div style="font-size:.8rem;color:#475569;line-height:1.55;"><?= e($a['body']) ?></div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
