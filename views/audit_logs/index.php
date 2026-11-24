<?php
$logs       = $logs       ?? [];
$users      = $users      ?? [];
$filters    = $filters    ?? [];
$pagination = $pagination ?? ['total'=>0,'perPage'=>25,'current'=>1,'last'=>1];

// ── Human-readable action labels ───────────────────────────────────
$actionLabels = [
    // Auth
    'auth.login'            => 'Signed in',
    'auth.login_mobile'     => 'Signed in (mobile)',
    'auth.register'         => 'Registered account',
    // Incidents
    'incident.created'      => 'Submitted report',
    'incident.verified'     => 'Verified report',
    'incident.reassessed'   => 'Reassessed triage',
    'incident.rejected'     => 'Rejected report',
    'incident.duplicate'    => 'Marked as duplicate',
    'incident.reopened'     => 'Reopened report',
    'incident.assigned'     => 'Assigned team',
    'incident.acknowledged' => 'Responder acknowledged',
    'incident.on_scene'     => 'Responder on scene',
    'incident.in_progress'  => 'Response in progress',
    'incident.support_requested' => 'Support requested',
    'incident.resolved'     => 'Resolved incident',
    'incident.escalated'    => 'Escalated to admin',
    // Personnel
    'personnel.created'     => 'Created personnel account',
    'personnel.updated'     => 'Updated personnel account',
    'personnel.password_reset'  => 'Reset password',
    'personnel.status_toggle'   => 'Toggled account status',
    // Teams
    'team.created'          => 'Created response team',
    'team.updated'          => 'Updated response team',
    'team.deleted'          => 'Deleted response team',
    // Residents
    'resident.approved'     => 'Approved resident account',
    'resident.rejected'     => 'Rejected resident account',
    // Announcements
    'announcement.created'  => 'Posted announcement',
    'announcement.deleted'  => 'Deleted announcement',
    'broadcast.sent'        => 'Sent broadcast notification',
    // Export / Analytics
    'export_analytics'      => 'Exported analytics CSV',
];

// ── Parse JSON details to readable summary ─────────────────────────
function formatDetails(string $action, ?string $jsonDetails): string
{
    if (!$jsonDetails) return '—';
    $d = json_decode($jsonDetails, true);
    if (!is_array($d)) return e(mb_strimwidth($jsonDetails, 0, 80, '…'));

    $parts = [];

    // Common fields — pick the most meaningful ones per action
    if (!empty($d['code']))          $parts[] = 'Report: ' . $d['code'];
    if (!empty($d['report_code']))   $parts[] = 'Report: ' . $d['report_code'];
    if (!empty($d['team_name']))     $parts[] = 'Team: ' . $d['team_name'];
    if (!empty($d['name']))          $parts[] = $d['name'];
    if (!empty($d['title']))         $parts[] = '"' . mb_strimwidth($d['title'], 0, 40, '…') . '"';
    if (!empty($d['role']))          $parts[] = 'Role: ' . $d['role'];
    if (!empty($d['new_status']))    $parts[] = 'Status → ' . $d['new_status'];
    if (!empty($d['from']))          $parts[] = 'From: ' . $d['from'];
    if (!empty($d['to']) && $action !== 'export_analytics') $parts[] = 'To: ' . $d['to'];
    if (!empty($d['reason']))        $parts[] = 'Reason: ' . mb_strimwidth($d['reason'], 0, 50, '…');
    if (!empty($d['triage_level']))  $parts[] = 'Triage: ' . ucfirst($d['triage_level']);
    if (!empty($d['hazard_type']))   $parts[] = ucfirst($d['hazard_type']);
    if (!empty($d['action']))        $parts[] = ucfirst($d['action']);
    if (!empty($d['target']))        $parts[] = 'Audience: ' . $d['target'];
    if (!empty($d['row_count']))     $parts[] = $d['row_count'] . ' rows';
    if (!empty($d['source']))        $parts[] = 'via ' . str_replace('_', ' ', $d['source']);

    if (empty($parts)) {
        // Fallback: show first 2 key=value pairs
        $i = 0;
        foreach ($d as $k => $v) {
            if (is_scalar($v) && $v !== '' && $v !== null) {
                $parts[] = str_replace('_', ' ', $k) . ': ' . mb_strimwidth((string)$v, 0, 30, '…');
                if (++$i >= 2) break;
            }
        }
    }

    return implode(' · ', array_slice($parts, 0, 4)) ?: '—';
}

// ── Action category colors ─────────────────────────────────────────
function actionColor(string $action): array
{
    $cat = explode('.', $action)[0] ?? 'other';
    return match($cat) {
        'auth'         => ['#eff6ff','#2563eb'],
        'incident'     => ['#fef2f2','#dc2626'],
        'personnel'    => ['#fdf4ff','#9333ea'],
        'team'         => ['#f0fdf4','#16a34a'],
        'resident'     => ['#fff7ed','#ea580c'],
        'announcement', 'broadcast' => ['#fffbeb','#d97706'],
        'export_analytics' => ['#f8fafc','#475569'],
        default        => ['#f8fafc','#64748b'],
    };
}

$buildQuery = fn(array $over): string => http_build_query(array_merge(array_filter($filters), $over));
?>
<style>
.al-wrap  { padding:20px 24px 32px; }
.al-head  { display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:14px; }
.al-filter { background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;margin-bottom:14px;display:flex;align-items:center;flex-wrap:wrap;gap:8px; }
.al-filter select,.al-filter input { height:32px;border:1.5px solid #e2e8f0;border-radius:5px;padding:0 9px;font-size:.78rem;color:#0f172a;background:#f8fafc;outline:none;font-family:inherit;transition:border-color .12s; }
.al-filter select:focus,.al-filter input:focus { border-color:#dc2626;background:#fff; }
.al-btn-f { height:32px;padding:0 13px;background:#0f172a;color:#fff;border:none;border-radius:5px;font-size:.78rem;font-weight:600;cursor:pointer; }
.al-btn-clr { height:32px;padding:0 10px;background:#fff;color:#64748b;border:1.5px solid #e2e8f0;border-radius:5px;font-size:.78rem;font-weight:600;text-decoration:none;display:inline-flex;align-items:center; }

.al-table { width:100%;border-collapse:collapse;table-layout:fixed; }
.al-table thead tr { background:#f8fafc;border-bottom:1px solid #e2e8f0; }
.al-table thead th { padding:9px 14px;font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;text-align:left; }
.al-table tbody tr { border-bottom:1px solid #f8fafc;transition:background .1s; }
.al-table tbody tr:last-child { border-bottom:none; }
.al-table tbody tr:hover { background:#fafbfc; }
.al-table tbody td { padding:10px 14px;vertical-align:middle;font-size:.8rem;color:#374151; }
.c-ts  { width:140px; }
.c-usr { width:160px; }
.c-act { width:200px; }
.c-dtl { }
.c-ip  { width:120px; }

.al-action-badge { display:inline-block;padding:3px 9px;border-radius:5px;font-size:.68rem;font-weight:700;white-space:nowrap; }
.al-pager { display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;padding:11px 16px;border-top:1px solid #f1f5f9;background:#fff; }
.pages { display:flex;align-items:center;gap:4px; }
.pg { display:inline-flex;align-items:center;justify-content:center;min-width:30px;height:28px;padding:0 6px;border-radius:5px;font-size:.75rem;font-weight:600;border:1.5px solid #e2e8f0;background:#fff;color:#374151;text-decoration:none;transition:all .1s; }
.pg:hover { background:#f8fafc;color:#0f172a;border-color:#cbd5e1; }
.pg.on  { background:#0f172a;border-color:#0f172a;color:#fff; }
.pg.off { opacity:.35;pointer-events:none; }
.pg-dot { width:24px;text-align:center;font-size:.78rem;color:#94a3b8; }
</style>

<div class="al-wrap">

  <!-- Header -->
  <div class="al-head">
    <div>
      <h1 style="font-size:1.25rem;font-weight:800;color:#0f172a;letter-spacing:-.02em;margin:0 0 2px;">Audit Logs</h1>
      <p style="font-size:.78rem;color:#64748b;margin:0;"><?= $pagination['total'] ?> entries</p>
    </div>
    <a href="<?= base_url('audit-logs/export') . (($q = http_build_query(array_filter($filters))) ? '?' . $q : '') ?>" style="display:inline-flex;align-items:center;gap:6px;height:34px;padding:0 14px;background:#fff;border:1.5px solid #e2e8f0;border-radius:6px;font-size:.78rem;font-weight:600;color:#475569;text-decoration:none;">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
      Export CSV <?php if (array_filter($filters)): ?><span style="font-size:.65rem;color:#94a3b8;">(filtered)</span><?php endif; ?>
    </a>
  </div>

  <!-- Filter bar -->
  <form method="get" class="al-filter">
    <select name="user_id" title="User">
      <option value="">All Users</option>
      <?php foreach ($users as $u): ?>
        <option value="<?= $u['id'] ?>" <?= $filters['user_id'] == $u['id'] ? 'selected' : '' ?>><?= e($u['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <input type="text" name="action" placeholder="Action (e.g. incident)" value="<?= e($filters['action'] ?? '') ?>" style="width:150px;" title="Action">
    <input type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>" title="From">
    <span style="color:#e2e8f0;font-size:.8rem;">—</span>
    <input type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>" title="To">
    <input type="text" name="search" placeholder="Search details…" value="<?= e($filters['search'] ?? '') ?>" style="width:160px;" title="Search details">
    <button type="submit" class="al-btn-f">Filter</button>
    <?php if (!empty(array_filter($filters))): ?>
      <a href="<?= base_url('audit-logs') ?>" class="al-btn-clr">Clear</a>
    <?php endif; ?>
    <span style="margin-left:auto;font-size:.72rem;color:#94a3b8;"><?= $pagination['total'] ?> result<?= $pagination['total']!==1?'s':'' ?></span>
  </form>

  <!-- Table -->
  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">
    <?php if (empty($logs)): ?>
      <div style="text-align:center;padding:48px 20px;font-size:.875rem;color:#94a3b8;">No audit logs found for the selected filters.</div>
    <?php else: ?>
      <div style="overflow-x:auto;">
        <table class="al-table">
          <thead>
            <tr>
              <th class="c-ts">Timestamp</th>
              <th class="c-usr">User</th>
              <th class="c-act">Action</th>
              <th class="c-dtl">Details</th>
              <th class="c-ip">IP Address</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($logs as $log):
              [$actBg, $actColor] = actionColor($log['action']);
              $actionLabel = $actionLabels[$log['action']] ?? ucwords(str_replace(['.','_'], [' → ', ' '], $log['action']));
              $detailText  = formatDetails($log['action'], $log['details'] ?? null);
            ?>
              <tr>
                <!-- Timestamp -->
                <td class="c-ts">
                  <div style="font-size:.78rem;color:#0f172a;font-weight:600;"><?= date('M j, Y', strtotime($log['created_at'])) ?></div>
                  <div style="font-size:.7rem;color:#94a3b8;"><?= date('g:i:s A', strtotime($log['created_at'])) ?></div>
                </td>

                <!-- User -->
                <td class="c-usr">
                  <div style="font-weight:700;color:#0f172a;font-size:.8rem;"><?= e($log['user_name'] ?: 'System') ?></div>
                  <div style="font-size:.7rem;color:#94a3b8;text-transform:capitalize;"><?= e($log['user_role'] ?: 'guest') ?></div>
                </td>

                <!-- Action -->
                <td class="c-act">
                  <span class="al-action-badge" style="background:<?= $actBg ?>;color:<?= $actColor ?>;"><?= e($actionLabel) ?></span>
                  <div style="font-size:.65rem;color:#cbd5e1;margin-top:2px;font-family:monospace;"><?= e($log['action']) ?></div>
                </td>

                <!-- Details (human-readable) -->
                <td class="c-dtl">
                  <div style="font-size:.8rem;color:#374151;"><?= $detailText ?></div>
                  <?php if (!empty($log['entity']) && !empty($log['entity_id'])): ?>
                    <div style="font-size:.68rem;color:#94a3b8;margin-top:1px;text-transform:capitalize;"><?= e($log['entity']) ?> #<?= e($log['entity_id']) ?></div>
                  <?php endif; ?>
                </td>

                <!-- IP -->
                <td class="c-ip" style="font-size:.75rem;color:#94a3b8;font-family:monospace;"><?= e($log['ip_address'] ?? '—') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <?php if ($pagination['last'] > 1): ?>
        <div class="al-pager">
          <div style="font-size:.78rem;color:#64748b;">
            Showing <strong><?= ($pagination['current']-1)*$pagination['perPage']+1 ?>–<?= min($pagination['total'], $pagination['current']*$pagination['perPage']) ?></strong>
            of <strong><?= $pagination['total'] ?></strong>
          </div>
          <div class="pages">
            <?php if ($pagination['current'] > 1): ?>
              <a href="?<?= $buildQuery(['page' => $pagination['current']-1]) ?>" class="pg">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
              </a>
            <?php else: ?>
              <span class="pg off"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg></span>
            <?php endif; ?>

            <?php
            $s = max(1, $pagination['current']-2);
            $e = min($pagination['last'], $pagination['current']+2);
            if ($s > 1) { echo '<a href="?'.$buildQuery(['page'=>1]).'" class="pg">1</a>'; if($s>2) echo '<span class="pg-dot">…</span>'; }
            for ($i=$s; $i<=$e; $i++):
            ?>
              <?php if ($i === $pagination['current']): ?>
                <span class="pg on"><?= $i ?></span>
              <?php else: ?>
                <a href="?<?= $buildQuery(['page'=>$i]) ?>" class="pg"><?= $i ?></a>
              <?php endif; ?>
            <?php endfor; ?>
            <?php if ($e < $pagination['last']) { if($e<$pagination['last']-1) echo '<span class="pg-dot">…</span>'; echo '<a href="?'.$buildQuery(['page'=>$pagination['last']]).'" class="pg">'.$pagination['last'].'</a>'; } ?>

            <?php if ($pagination['current'] < $pagination['last']): ?>
              <a href="?<?= $buildQuery(['page' => $pagination['current']+1]) ?>" class="pg">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
              </a>
            <?php else: ?>
              <span class="pg off"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg></span>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>

</div>
