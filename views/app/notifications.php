<?php
$notifications = $notifications ?? [];
$unread = count(array_filter($notifications, fn($n) => !(int)$n['is_read']));

function notifIconColor(string $link): array {
    if (str_contains($link, 'reports'))      return ['#fef2f2','#dc2626'];
    if (str_contains($link, 'monitoring'))   return ['#fff7ed','#ea580c'];
    if (str_contains($link, 'verification')) return ['#eff6ff','#2563eb'];
    if (str_contains($link, 'residents'))    return ['#f0fdf4','#16a34a'];
    if (str_contains($link, 'responder'))    return ['#eff6ff','#2563eb'];
    return ['#f8fafc','#64748b'];
}
?>
<style>
.mn-wrap { padding-top: 8px; }
.mn-row {
  display: flex; align-items: flex-start; gap: 12px;
  padding: 14px 16px; border-bottom: 1px solid #f8fafc;
  text-decoration: none; color: inherit;
  transition: background .1s;
}
.mn-row:last-child { border-bottom: none; }
.mn-row:active { background: #f8fafc; }
.mn-row.unread { background: #fffbeb; }
.mn-icon { width: 38px; height: 38px; border-radius: 11px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.mn-unread-dot { width: 8px; height: 8px; border-radius: 50%; background: #dc2626; flex-shrink: 0; margin-top: 6px; }
.mn-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 24px; text-align: center; }
</style>

<div class="mn-wrap">

  <!-- Header row -->
  <div style="display:flex;align-items:center;justify-content:space-between;padding:8px 16px 14px;">
    <div>
      <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;">
        <?= $unread > 0 ? $unread . ' unread' : 'All read' ?>
      </div>
    </div>
    <?php if ($unread > 0): ?>
      <form method="post" action="<?= base_url('app/notifications/read-all') ?>">
        <?= csrf_field() ?>
        <button type="submit" style="height:30px;padding:0 12px;background:#fff;border:1.5px solid #e2e8f0;border-radius:7px;font-size:.72rem;font-weight:600;color:#475569;cursor:pointer;">
          Mark all read
        </button>
      </form>
    <?php endif; ?>
  </div>

  <?php if (empty($notifications)): ?>
    <div class="mn-empty">
      <div style="width:64px;height:64px;border-radius:18px;background:#f8fafc;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
      </div>
      <div style="font-size:.9375rem;font-weight:700;color:#0f172a;margin-bottom:5px;">No notifications</div>
      <div style="font-size:.8rem;color:#94a3b8;">You're all caught up.</div>
    </div>
  <?php else: ?>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:14px;margin:0 16px;overflow:hidden;">
      <?php foreach ($notifications as $n):
        $isUnread = !(int)$n['is_read'];
        [$iconBg, $iconColor] = notifIconColor($n['link'] ?? '');
        $ts = strtotime($n['created_at'] ?? '');
        $diff = time() - $ts;
        if     ($diff < 3600)  $ago = round($diff/60).'m ago';
        elseif ($diff < 86400) $ago = round($diff/3600).'h ago';
        else                   $ago = date('M j', $ts);
        $href = !empty($n['link']) ? base_url(ltrim($n['link'],'/')) : '#';
      ?>
        <a href="<?= $href ?>" class="mn-row <?= $isUnread ? 'unread' : '' ?>">
          <?php if ($isUnread): ?>
            <div class="mn-unread-dot"></div>
          <?php else: ?>
            <div style="width:8px;flex-shrink:0;"></div>
          <?php endif; ?>
          <div class="mn-icon" style="background:<?= $iconBg ?>;">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="<?= $iconColor ?>" stroke-width="2">
              <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
              <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
            </svg>
          </div>
          <div style="flex:1;min-width:0;">
            <div style="font-size:.8125rem;font-weight:<?= $isUnread ? '700' : '600' ?>;color:#0f172a;line-height:1.35;margin-bottom:3px;"><?= e($n['title']) ?></div>
            <?php if (!empty($n['message'])): ?>
              <div style="font-size:.75rem;color:#64748b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($n['message']) ?></div>
            <?php endif; ?>
            <div style="font-size:.7rem;color:#94a3b8;margin-top:4px;"><?= e($ago) ?></div>
          </div>
          <?php if (!empty($n['link'])): ?>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="2.5" style="flex-shrink:0;"><path d="M9 18l6-6-6-6"/></svg>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>
    <div style="height:16px;"></div>
  <?php endif; ?>

</div>
