<?php
$notifications = $notifications ?? [];
$announcements = $announcements ?? [];
$now = time();

$severityMeta = [
    'information' => ['#eff6ff','#2563eb','Information'],
    'advisory'    => ['#f0fdf4','#16a34a','Advisory'],
    'warning'     => ['#fffbeb','#d97706','Warning'],
    'critical'    => ['#fef2f2','#dc2626','Critical'],
];

// Map notification link prefix to icon color
function notifColor(string $link): string
{
    if (str_contains($link, 'reports'))       return '#dc2626';
    if (str_contains($link, 'monitoring'))    return '#f59e0b';
    if (str_contains($link, 'verification'))  return '#2563eb';
    if (str_contains($link, 'residents'))     return '#ea580c';
    return '#64748b';
}
?>
<style>
.nf-wrap   { padding:20px 24px 32px; }
.nf-head   { display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:18px; }
.nf-tabs   { display:flex;gap:0;border-bottom:1px solid #e2e8f0;margin-bottom:16px; }
.nf-tab    { padding:8px 18px;font-size:.8125rem;font-weight:600;color:#94a3b8;border-bottom:2px solid transparent;cursor:pointer;text-decoration:none;transition:color .12s; }
.nf-tab:hover { color:#0f172a; }
.nf-tab.active { color:#0f172a;border-bottom-color:#dc2626; }
.nf-panel  { display:none; }
.nf-panel.active { display:block; }
.nf-list   { background:#fff;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden; }
.nf-row    { display:flex;align-items:flex-start;gap:12px;padding:13px 16px;border-bottom:1px solid #f8fafc;text-decoration:none;color:inherit;transition:background .1s; }
.nf-row:last-child { border-bottom:none; }
.nf-row:hover { background:#fafbfc; }
.nf-row.unread { background:#fffbeb; }
.nf-row.unread:hover { background:#fef9ec; }
.nf-dot { width:8px;height:8px;border-radius:50%;background:#dc2626;flex-shrink:0;margin-top:5px; }
.nf-dot.read { background:transparent; }
.nf-icon { width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.ann-card { background:#fff;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;margin-bottom:10px; }
.ann-strip { height:3px; }
</style>

<div class="nf-wrap">
  <div class="nf-head">
    <div>
      <h1 style="font-size:1.25rem;font-weight:800;color:#0f172a;letter-spacing:-.02em;margin:0 0 2px;">Notifications</h1>
      <p style="font-size:.78rem;color:#64748b;margin:0;">
        <?= $unread = count(array_filter($notifications, fn($n) => !$n['is_read'])) ?> unread ·
        <?= count($notifications) ?> system notifications ·
        <?= count($announcements) ?> posted announcements
      </p>
    </div>
    <form method="post" action="<?= base_url('notifications/read-all') ?>" id="mark-read-form">
      <?= csrf_field() ?>
      <input type="hidden" name="return_tab" id="return-tab-input" value="notif">
      <button type="submit" id="mark-read-btn" style="height:32px;padding:0 13px;background:#fff;border:1.5px solid #e2e8f0;border-radius:6px;font-size:.78rem;font-weight:600;color:#475569;cursor:pointer;">
        Mark all as read
      </button>
    </form>
  </div>

  <!-- Tabs -->
  <div class="nf-tabs">
    <a class="nf-tab active" id="tab-notif-btn" href="#" onclick="switchTab('tab-notif',this);return false;">
      System Notifications
      <?php $unread = count(array_filter($notifications, fn($n) => !$n['is_read'])); ?>
      <?php if ($unread > 0): ?>
        <span style="display:inline-block;background:#dc2626;color:#fff;border-radius:4px;padding:1px 6px;font-size:.62rem;font-weight:700;margin-left:4px;"><?= $unread ?> unread</span>
      <?php endif; ?>
    </a>
    <a class="nf-tab" id="tab-ann-btn" href="#" onclick="switchTab('tab-ann',this);return false;">
      Announcements
      <?php if (!empty($announcements)): ?>
        <span style="display:inline-block;background:#f1f5f9;color:#475569;border-radius:4px;padding:1px 6px;font-size:.62rem;font-weight:700;margin-left:4px;"><?= count($announcements) ?></span>
      <?php endif; ?>
    </a>
  </div>

  <!-- System Notifications Panel -->
  <div class="nf-panel active" id="tab-notif">
    <?php if (empty($notifications)): ?>
      <div class="nf-list" style="text-align:center;padding:48px 20px;">
        <div style="font-size:.875rem;font-weight:700;color:#94a3b8;margin-bottom:4px;">No notifications</div>
        <div style="font-size:.78rem;color:#cbd5e1;">You're all caught up.</div>
      </div>
    <?php else: ?>
      <div class="nf-list">
        <?php foreach ($notifications as $n):
          $isUnread = !(int)$n['is_read'];
          $color = notifColor($n['link'] ?? '');
          $ts = strtotime($n['created_at'] ?? '');
          $diff = time() - $ts;
          if ($diff < 3600)     $ago = round($diff/60).'m ago';
          elseif ($diff < 86400) $ago = round($diff/3600).'h ago';
          else                  $ago = date('M j, Y', $ts);
        ?>
          <a class="nf-row <?= $isUnread ? 'unread' : '' ?>"
             href="<?= !empty($n['link']) ? base_url(ltrim($n['link'],'/')) : '#' ?>">
            <div class="nf-dot <?= $isUnread ? '' : 'read' ?>"></div>
            <div class="nf-icon" style="background:<?= $color ?>1a;">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="<?= $color ?>" stroke-width="2.5">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
              </svg>
            </div>
            <div style="flex:1;min-width:0;">
              <div style="font-size:.8125rem;font-weight:<?= $isUnread ? '700' : '600' ?>;color:#0f172a;line-height:1.3;"><?= e($n['title']) ?></div>
              <?php if (!empty($n['message'])): ?>
                <div style="font-size:.75rem;color:#64748b;margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($n['message']) ?></div>
              <?php endif; ?>
              <div style="font-size:.68rem;color:#94a3b8;margin-top:3px;"><?= e($ago) ?></div>
            </div>
            <?php if (!empty($n['link'])): ?>
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2.5" style="flex-shrink:0;margin-top:3px;"><path d="M9 18l6-6-6-6"/></svg>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Announcements Panel -->
  <div class="nf-panel" id="tab-ann">
    <?php if (empty($announcements)): ?>
      <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;text-align:center;padding:48px 20px;">
        <div style="font-size:.875rem;font-weight:700;color:#94a3b8;margin-bottom:4px;">No announcements</div>
        <div style="font-size:.78rem;color:#cbd5e1;">No announcements have been posted yet.</div>
      </div>
    <?php else: ?>
      <?php foreach ($announcements as $a):
        [$abg, $acolor, $alabel] = $severityMeta[$a['severity'] ?? 'information'] ?? $severityMeta['information'];
        $isExpired = (int)$a['is_expired'];
        $ats = strtotime($a['created_at'] ?? '');
      ?>
        <div class="ann-card" style="<?= $isExpired ? 'opacity:.6;' : '' ?>">
          <div class="ann-strip" style="background:<?= $acolor ?>;"></div>
          <div style="padding:13px 16px;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:6px;">
              <div style="font-size:.875rem;font-weight:700;color:#0f172a;"><?= e($a['title']) ?></div>
              <div style="display:flex;align-items:center;gap:5px;flex-shrink:0;">
                <?php if ($isExpired): ?>
                  <span style="font-size:.62rem;font-weight:700;padding:2px 7px;border-radius:4px;background:#f8fafc;color:#94a3b8;">Expired</span>
                <?php endif; ?>
                <span style="font-size:.65rem;font-weight:700;padding:3px 8px;border-radius:5px;background:<?= $abg ?>;color:<?= $acolor ?>;"><?= $alabel ?></span>
              </div>
            </div>
            <p style="font-size:.8rem;color:#475569;margin:0 0 8px;line-height:1.55;"><?= nl2br(e($a['body'])) ?></p>
            <div style="font-size:.7rem;color:#94a3b8;"><?= date('M j, Y g:i A', $ats) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

</div>

<script>
function switchTab(id, el) {
  document.querySelectorAll('.nf-panel').forEach(function(p) { p.classList.remove('active'); });
  document.querySelectorAll('.nf-tab').forEach(function(t) { t.classList.remove('active'); });
  document.getElementById(id).classList.add('active');
  el.classList.add('active');
  // Track active tab for "Mark all as read" redirect
  var tabKey = id === 'tab-notif' ? 'notif' : 'ann';
  var input = document.getElementById('return-tab-input');
  if (input) input.value = tabKey;
  // Update URL hash without reload
  history.replaceState(null, '', '#' + tabKey);
  // Hide/show mark-all button — only relevant on system notifications tab
  var btn = document.getElementById('mark-read-btn');
  if (btn) btn.style.display = tabKey === 'notif' ? '' : 'none';
}

// Restore tab from URL hash on page load
(function() {
  var hash = window.location.hash;
  if (hash === '#ann') {
    var annBtn = document.getElementById('tab-ann-btn');
    if (annBtn) switchTab('tab-ann', annBtn);
  }
  // Also hide mark-all if starting on announcements tab
  var btn = document.getElementById('mark-read-btn');
  var isAnn = hash === '#ann';
  if (btn) btn.style.display = isAnn ? 'none' : '';
})();
</script>
