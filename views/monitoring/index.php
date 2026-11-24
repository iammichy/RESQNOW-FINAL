<?php
$activeBoard       = $activeBoard ?? [];
$resolvedToday     = $resolvedToday ?? [];
$supportQueue      = $supportQueue ?? [];
$activeMissions    = $activeMissions ?? 0;
$pendingSupport    = $pendingSupport ?? 0;
$avgAckMinutes     = $avgAckMinutes ?? null;
$avgResolveMinutes = $avgResolveMinutes ?? null;
$flash             = $flash ?? null;

// ── Shared badge helpers (premium *-lt pattern, no emoji) ─────────────
$triageBadge = function(string $l): string {
    $map = [
        'critical'             => ['bg-red-lt text-red border border-red-subtle',    'Critical'],
        'high'                 => ['bg-orange-lt text-orange border border-orange-subtle', 'High'],
        'moderate'             => ['bg-yellow-lt text-yellow border border-yellow-subtle', 'Moderate'],
        'low'                  => ['bg-green-lt text-green border border-green-subtle',    'Low'],
        'pending_reassessment' => ['bg-slate-lt text-slate border border-slate-subtle',    'Reassess'],
    ];
    [$cls, $label] = $map[$l] ?? ['bg-light text-muted', ucfirst($l)];
    return '<span class="badge ' . $cls . ' fw-bold px-2 py-1" style="font-size:.6rem;border-radius:4px;">' . e($label) . '</span>';
};

// Hazard: coloured letter avatar — no emoji
$hazardAvatar = function(string $h): string {
    $map = [
        'fire'       => ['bg-red-lt',     'text-red',     'F'],
        'flood'      => ['bg-azure-lt',   'text-azure',   'W'],
        'earthquake' => ['bg-yellow-lt',  'text-yellow',  'E'],
        'medical'    => ['bg-purple-lt',  'text-purple',  'M'],
        'accident'   => ['bg-orange-lt',  'text-orange',  'A'],
        'evacuation' => ['bg-green-lt',   'text-green',   'V'],
        'security'   => ['bg-indigo-lt',  'text-indigo',  'S'],
        'other'      => ['bg-muted-lt',   'text-muted',   'O'],
    ];
    [$bg, $tc, $init] = $map[$h] ?? ['bg-muted-lt', 'text-muted', 'O'];
    return '<span class="avatar avatar-xs rounded ' . $bg . ' fw-bold ' . $tc . '" style="font-size:.6rem;">' . $init . '</span>';
};

$fmtMins = function(?int $m): string {
    if ($m === null) return '—';
    if ($m < 60) return $m . 'm';
    return floor($m / 60) . 'h ' . ($m % 60) . 'm';
};

// Kanban grouping
$kanban = ['assigned' => [], 'acknowledged' => [], 'on_scene' => [], 'in_progress' => []];
foreach ($activeBoard as $card) {
    $col = $card['incident_status'] ?? 'assigned';
    if (!array_key_exists($col, $kanban)) $col = 'assigned';
    $kanban[$col][] = $card;
}

$kanbanCols = [
    'assigned'     => ['label' => 'Assigned',    'hex' => '#3b82f6'],
    'acknowledged' => ['label' => 'En Route',    'hex' => '#06b6d4'],
    'on_scene'     => ['label' => 'On Scene',    'hex' => '#14b8a6'],
    'in_progress'  => ['label' => 'In Progress', 'hex' => '#f59e0b'],
];
?>

<style>
.mon-kpi-icon { width:40px;height:40px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.kanban-col   { min-height:180px; }
.mission-card { cursor:pointer;transition:box-shadow .15s,transform .1s; border:1px solid #e2e8f0; border-radius:8px; margin-bottom:8px; background:#fff; }
.mission-card:hover { box-shadow:0 4px 12px rgba(15,23,42,.1); transform:translateY(-1px); }
.support-card { border:1.5px solid #dc2626 !important; animation:support-pulse 1.6s infinite; }
@keyframes support-pulse { 0%,100%{border-color:#dc2626;} 50%{border-color:#fca5a5;} }
.elapsed-clock { font-size:.7rem;color:#94a3b8;font-variant-numeric:tabular-nums; }
.kanban-col-header { display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border-bottom:1px solid #f1f5f9; }
.support-banner { background:#fef2f2;border-left:3px solid #dc2626;padding:5px 8px;border-radius:0 4px 4px 0;font-size:.7rem;font-weight:700;color:#dc2626;margin-bottom:8px; }
@keyframes spin { from { transform:rotate(0deg); } to { transform:rotate(360deg); } }
.spinning { animation:spin .65s linear infinite; display:inline-flex !important; transform-origin:center center; }
</style>

<!-- Page header -->
<div style="padding:20px 24px 0;">
  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:20px;">
    <div>
      <h1 style="font-size:1.25rem;font-weight:800;color:#0f172a;letter-spacing:-.02em;margin:0 0 2px;">Monitoring Board</h1>
      <p style="font-size:.78rem;color:#64748b;margin:0;display:flex;align-items:center;gap:6px;" id="last-refresh">
        <span id="refresh-dot" style="width:6px;height:6px;border-radius:50%;background:#16a34a;display:inline-block;flex-shrink:0;"></span>
        <span id="refresh-text">Auto-refreshes every 30s</span>
      </p>
    </div>
    <button id="btn-refresh" onclick="refreshBoard(true)" style="display:inline-flex;align-items:center;gap:6px;height:34px;padding:0 14px;background:#fff;border:1.5px solid #e2e8f0;border-radius:6px;font-size:.8rem;font-weight:600;color:#475569;cursor:pointer;transition:border-color .12s,color .12s;">
      <span id="refresh-icon-wrap" style="display:inline-flex;align-items:center;justify-content:center;width:14px;height:14px;">
        <svg id="refresh-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-.18-4.96"/></svg>
      </span>
      <span id="refresh-label">Refresh</span>
    </button>
  </div>

  <?php if ($flash): ?>
    <div style="padding:10px 14px;border-radius:6px;margin-bottom:16px;font-size:.8125rem;
      <?= $flash['type']==='success' ? 'background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;' : 'background:#fef2f2;border:1px solid #fecaca;color:#991b1b;' ?>">
      <?= e($flash['message']) ?>
    </div>
  <?php endif; ?>
</div>

<div style="padding:0 24px 24px;">

  <!-- KPI row -->
  <div class="row g-3 mb-4">
    <?php $kpis = [
      ['id'=>'kpi-active',  'label'=>'Active Missions',          'value'=>$activeMissions, 'bg'=>'#eff6ff', 'icon'=>'<path d="M13 2L3 14h7l-1 8 10-12h-7l1-8z" fill="#3b82f6"/>'],
      ['id'=>'kpi-support', 'label'=>'Pending Support',           'value'=>$pendingSupport, 'bg'=>'#fef2f2', 'icon'=>'<circle cx="12" cy="12" r="10" fill="#dc2626"/><line x1="12" y1="8" x2="12" y2="12" stroke="#fff" stroke-width="2"/><line x1="12" y1="16" x2="12.01" y2="16" stroke="#fff" stroke-width="2"/>'],
      ['id'=>'kpi-ack',     'label'=>'Avg Acknowledge Time (7d)', 'value'=>$fmtMins($avgAckMinutes),    'bg'=>'#f0fdf4', 'icon'=>'<circle cx="12" cy="12" r="10" fill="#16a34a"/><polyline points="12,6 12,12 16,14" stroke="#fff" stroke-width="2" stroke-linecap="round"/>'],
      ['id'=>'kpi-res',     'label'=>'Avg Resolve Time (7d)',     'value'=>$fmtMins($avgResolveMinutes),'bg'=>'#fffbeb', 'icon'=>'<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" fill="#d97706"/><polyline points="22 4 12 14.01 9 11.01" stroke="#fff" stroke-width="2"/>'],
    ]; ?>
    <?php foreach ($kpis as $k): ?>
      <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm" style="border-radius:8px;">
          <div class="card-body d-flex align-items-center gap-3 py-3">
            <div class="mon-kpi-icon" style="background:<?= $k['bg'] ?>;">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="none"><?= $k['icon'] ?></svg>
            </div>
            <div>
              <div class="fw-bold" style="font-size:1.35rem;line-height:1;color:#0f172a;" id="<?= $k['id'] ?>"><?= $k['value'] ?></div>
              <div style="font-size:.72rem;color:#64748b;margin-top:2px;"><?= $k['label'] ?></div>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Support request queue -->
  <?php if (!empty($supportQueue)): ?>
    <div style="background:#fff;border:1.5px solid #fecaca;border-radius:8px;overflow:hidden;margin-bottom:24px;">
      <div style="padding:12px 16px;background:#fef2f2;border-bottom:1px solid #fecaca;display:flex;align-items:center;gap:8px;">
        <div style="width:8px;height:8px;border-radius:50%;background:#dc2626;animation:support-pulse 1.6s infinite;"></div>
        <span style="font-size:.8rem;font-weight:700;color:#dc2626;text-transform:uppercase;letter-spacing:.04em;">Pending Support Requests</span>
        <span style="margin-left:auto;background:#dc2626;color:#fff;font-size:.65rem;font-weight:700;border-radius:100px;padding:2px 8px;"><?= count($supportQueue) ?></span>
      </div>
      <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.8125rem;">
          <thead>
            <tr style="border-bottom:1px solid #f1f5f9;">
              <?php foreach (['Incident','Team','Support Type','Remarks','Requested By','Time',''] as $h): ?>
                <th style="padding:8px 16px;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#94a3b8;"><?= $h ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody id="support-queue-body">
            <?php foreach ($supportQueue as $sr): ?>
              <tr style="border-bottom:1px solid #f8fafc;">
                <td style="padding:10px 16px;">
                  <a href="<?= base_url('reports/' . e($sr['incident_code'])) ?>" style="font-weight:700;color:#0f172a;font-family:monospace;font-size:.8rem;"><?= e($sr['incident_code']) ?></a>
                  <div style="margin-top:3px;"><?= $triageBadge($sr['triage_level']) ?></div>
                </td>
                <td style="padding:10px 16px;color:#374151;font-size:.8rem;"><?= e($sr['team_name'] ?? '—') ?></td>
                <td style="padding:10px 16px;">
                  <span style="display:inline-block;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;border-radius:4px;padding:2px 8px;font-size:.7rem;font-weight:700;text-transform:uppercase;">
                    <?= e(str_replace('_',' ', $sr['support_type'] ?? '—')) ?>
                  </span>
                </td>
                <td style="padding:10px 16px;color:#475569;font-size:.78rem;max-width:220px;"><?= e($sr['message'] ?? '—') ?></td>
                <td style="padding:10px 16px;color:#374151;font-size:.8rem;"><?= e($sr['responder_name'] ?? '—') ?></td>
                <td style="padding:10px 16px;color:#94a3b8;font-size:.75rem;"><?= e($sr['created_at']) ?></td>
                <td style="padding:10px 16px;">
                  <form method="post" action="<?= base_url('monitoring/support/' . $sr['id'] . '/ack') ?>">
                    <?= csrf_field() ?>
                    <button type="submit" style="height:30px;padding:0 12px;background:#16a34a;color:#fff;border:none;border-radius:5px;font-size:.75rem;font-weight:600;cursor:pointer;">Acknowledge</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>

  <!-- Mission flow legend -->
  <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;flex-wrap:wrap;">
    <span style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#94a3b8;">Mission Flow:</span>
    <?php
    $flowSteps = [
      ['Verified',    '#e2e8f0','#64748b'],
      ['Assigned',    '#dbeafe','#2563eb'],
      ['En Route',    '#cffafe','#0891b2'],
      ['On Scene',    '#ccfbf1','#0d9488'],
      ['In Progress', '#fef9c3','#d97706'],
      ['Resolved',    '#dcfce7','#16a34a'],
    ];
    foreach ($flowSteps as $i => [$label, $bg, $color]):
    ?>
      <div style="display:flex;align-items:center;gap:4px;">
        <?php if ($i > 0): ?>
          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="3"><path d="M9 18l6-6-6-6"/></svg>
        <?php endif; ?>
        <span style="padding:3px 9px;border-radius:100px;font-size:.65rem;font-weight:700;background:<?= $bg ?>;color:<?= $color ?>;"><?= $label ?></span>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Kanban board -->
  <div id="kanban-board">
    <div class="row g-3">
      <?php foreach ($kanbanCols as $status => $col): ?>
        <div class="col-xl-3 col-md-6">
          <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;height:100%;">
            <div class="kanban-col-header" style="border-left:3px solid <?= $col['hex'] ?>;">
              <span style="font-size:.8125rem;font-weight:700;color:#0f172a;"><?= $col['label'] ?></span>
              <span style="background:<?= $col['hex'] ?>20;color:<?= $col['hex'] ?>;border-radius:100px;padding:1px 8px;font-size:.7rem;font-weight:700;"><?= count($kanban[$status]) ?></span>
            </div>
            <div class="kanban-col p-2" id="col-<?= $status ?>">
              <?php foreach ($kanban[$status] as $card):
                $hasSupport = (int)($card['open_support_count'] ?? 0) > 0;
                // Check if mission is stale (> 24h)
                $assignedTs = strtotime($card['assigned_at'] ?? '');
                $hoursOld = $assignedTs ? floor((time() - $assignedTs) / 3600) : 0;
                $isStale = $hoursOld >= 24;
                // Last action label
                $actionLabels = [
                    'acknowledged' => 'Acknowledged',
                    'en_route' => 'En Route',
                    'arrived_on_scene' => 'Arrived',
                    'first_aid' => 'First Aid',
                    'extinguishing' => 'Extinguishing',
                    'evacuating' => 'Evacuating',
                    'resolved' => 'Resolved',
                    'support_requested' => 'Support Requested',
                ];
                $lastAction = $actionLabels[$card['last_update_action'] ?? ''] ?? null;
              ?>
                <div class="mission-card <?= $hasSupport ? 'support-card' : '' ?>"
                     onclick="window.location='<?= base_url('reports/' . e($card['code'])) ?>'">
                  <div style="padding:10px 12px;">

                    <?php if ($hasSupport): ?>
                      <div class="support-banner">Support requested</div>
                    <?php endif; ?>

                    <?php if ($isStale): ?>
                      <div style="background:#fffbeb;border-left:3px solid #f59e0b;padding:3px 8px;border-radius:0 4px 4px 0;font-size:.68rem;font-weight:600;color:#d97706;margin-bottom:6px;">
                        Stale — <?= $hoursOld ?>h since assignment
                      </div>
                    <?php endif; ?>

                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px;">
                      <div style="display:flex;align-items:center;gap:6px;">
                        <?= $hazardAvatar($card['hazard_type']) ?>
                        <span style="font-size:.8rem;font-weight:700;color:#0f172a;font-family:monospace;"><?= e($card['code']) ?></span>
                      </div>
                      <?= $triageBadge($card['triage_level']) ?>
                    </div>

                    <div style="font-size:.75rem;color:#64748b;margin-bottom:4px;"><?= e($card['team_name']) ?></div>

                    <?php if (!empty($card['address'])): ?>
                      <div style="font-size:.72rem;color:#94a3b8;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:4px;">
                        <?= e($card['address']) ?>
                      </div>
                    <?php endif; ?>

                    <?php if ($lastAction): ?>
                      <div style="display:flex;align-items:center;gap:4px;margin-bottom:4px;">
                        <span style="font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.03em;color:#94a3b8;">Last:</span>
                        <span style="font-size:.65rem;font-weight:600;color:#475569;"><?= e($lastAction) ?></span>
                      </div>
                    <?php endif; ?>

                    <?php if (!empty($card['last_update_message'])): ?>
                      <div style="font-size:.72rem;color:#64748b;border-top:1px solid #f1f5f9;padding-top:5px;margin-top:5px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        <?= e($card['last_update_message']) ?>
                      </div>
                    <?php endif; ?>

                    <div style="display:flex;justify-content:space-between;margin-top:6px;">
                      <span class="elapsed-clock elapsed-timer" data-assigned="<?= e($card['assigned_at']) ?>">—</span>
                    </div>

                  </div>
                </div>
              <?php endforeach; ?>
              <?php if (empty($kanban[$status])): ?>
                <div style="text-align:center;padding:28px 12px;font-size:.78rem;color:#cbd5e1;">No missions</div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Resolved today -->
  <?php if (!empty($resolvedToday)): ?>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;margin-top:24px;">
      <div style="padding:12px 16px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:.8125rem;font-weight:700;color:#0f172a;">Resolved Today</span>
        <span style="font-size:.72rem;color:#94a3b8;"><?= count($resolvedToday) ?> incidents</span>
      </div>
      <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.8rem;" id="resolved-today-body">
          <thead>
            <tr style="border-bottom:1px solid #f1f5f9;">
              <?php foreach (['Code','Hazard','Triage','Team','Resolved At'] as $h): ?>
                <th style="padding:8px 16px;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#94a3b8;"><?= $h ?></th>
              <?php endforeach; ?>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($resolvedToday as $r): ?>
              <tr style="border-bottom:1px solid #f8fafc;" onclick="window.location='<?= base_url('reports/'.e($r['code'])) ?>'" style="cursor:pointer;">
                <td style="padding:10px 16px;font-family:monospace;font-weight:700;color:#0f172a;font-size:.8rem;">
                  <a href="<?= base_url('reports/' . e($r['code'])) ?>" style="color:inherit;"><?= e($r['code']) ?></a>
                </td>
                <td style="padding:10px 16px;">
                  <div style="display:flex;align-items:center;gap:6px;">
                    <?= $hazardAvatar($r['hazard_type']) ?>
                    <span style="font-size:.78rem;color:#374151;text-transform:capitalize;"><?= e($r['hazard_type']) ?></span>
                  </div>
                </td>
                <td style="padding:10px 16px;"><?= $triageBadge($r['triage_level']) ?></td>
                <td style="padding:10px 16px;color:#374151;font-size:.78rem;"><?= e($r['team_name']) ?></td>
                <td style="padding:10px 16px;color:#94a3b8;font-size:.75rem;"><?= e($r['resolved_at']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>

</div>

<?php
$extraScripts = $extraScripts ?? '';
ob_start();
?>
<script>
(function(){
  // Elapsed timers
  function updateTimers() {
    document.querySelectorAll('.elapsed-timer[data-assigned]').forEach(function(el) {
      var d = new Date(el.dataset.assigned.replace(' ','T'));
      var diff = Math.floor((Date.now() - d) / 1000);
      var h = Math.floor(diff/3600), m = Math.floor((diff%3600)/60), s = diff%60;
      el.textContent = (h>0?h+'h ':'')+m+'m '+s+'s';
    });
  }
  setInterval(updateTimers,1000); updateTimers();

  // ── Refresh with full UX feedback ────────────────────────
  var nextRefresh = 30;
  var isRefreshing = false;

  function showToast(msg, color) {
    var existing = document.getElementById('mon-toast');
    if (existing) existing.remove();
    var t = document.createElement('div');
    t.id = 'mon-toast';
    t.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;' +
      'padding:10px 18px;border-radius:8px;font-size:.82rem;font-weight:600;' +
      'color:#fff;background:' + color + ';box-shadow:0 4px 16px rgba(0,0,0,.18);' +
      'transition:opacity .3s;display:flex;align-items:center;gap:8px;';
    t.innerHTML = msg;
    document.body.appendChild(t);
    setTimeout(function() { t.style.opacity = '0'; setTimeout(function() { t.remove(); }, 320); }, 2200);
  }

  window.refreshBoard = function(manual) {
    if (isRefreshing) return;
    isRefreshing = true;

    var btn   = document.getElementById('btn-refresh');
    var icon  = document.getElementById('refresh-icon-wrap');
    var label = document.getElementById('refresh-label');
    var dot   = document.getElementById('refresh-dot');
    var text  = document.getElementById('refresh-text');

    // Start — spin icon, change text, amber dot
    if (icon)  icon.classList.add('spinning');
    if (label) label.textContent = 'Refreshing…';
    if (btn)   { btn.disabled = true; btn.style.borderColor = '#fde68a'; btn.style.color = '#d97706'; }
    if (dot)   { dot.style.background = '#f59e0b'; }
    if (text)  text.textContent = 'Refreshing…';

    fetch('<?= base_url('monitoring/board') ?>')
      .then(function(r) { return r.json(); })
      .then(function(d) {
        var ae = document.getElementById('kpi-active');
        var se = document.getElementById('kpi-support');
        if (ae) ae.textContent = d.active.length;
        if (se) { se.textContent = d.pending_support; se.style.color = d.pending_support > 0 ? '#dc2626' : ''; }

        var now = new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit', second:'2-digit'});

        // Done — restore button, green dot, show time
        if (icon)  icon.classList.remove('spinning');
        if (label) label.textContent = 'Refresh';
        if (dot)   dot.style.background = '#16a34a';
        if (text)  text.textContent = 'Last refreshed: ' + now;

        if (btn) {
          btn.disabled = false;
          if (manual) {
            // Green flash to confirm manual refresh worked
            btn.style.background    = '#f0fdf4';
            btn.style.borderColor   = '#bbf7d0';
            btn.style.color         = '#16a34a';
            showToast(
              '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Board refreshed at ' + now,
              '#16a34a'
            );
            setTimeout(function() {
              btn.style.background  = '';
              btn.style.borderColor = '';
              btn.style.color       = '';
            }, 1400);
          } else {
            btn.style.borderColor = '';
            btn.style.color       = '';
          }
        }

        nextRefresh = 30;
        isRefreshing = false;
      })
      .catch(function() {
        if (icon)  icon.classList.remove('spinning');
        if (label) label.textContent = 'Refresh';
        if (btn)   { btn.disabled = false; btn.style.borderColor = '#fecaca'; btn.style.color = '#dc2626'; }
        if (dot)   dot.style.background = '#dc2626';
        if (text)  text.textContent = 'Refresh failed — click to retry';
        showToast('<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg> Refresh failed', '#dc2626');
        isRefreshing = false;
      });
  };

  // Countdown ticker: counts down to next auto-refresh
  setInterval(function() {
    if (isRefreshing) return;
    nextRefresh--;
    if (nextRefresh <= 0) {
      nextRefresh = 30;
      window.refreshBoard(false);
    } else {
      var text = document.getElementById('refresh-text');
      // Only show countdown if we're not showing "Last refreshed" or error
      if (text && text.textContent.indexOf('Refreshing') === -1 && text.textContent.indexOf('failed') === -1) {
        if (text.textContent.indexOf('Last refreshed') !== -1) {
          // Already shows last refreshed — append countdown
          var base = text.textContent.replace(/\s*·.*$/, '');
          text.textContent = base + ' · next in ' + nextRefresh + 's';
        } else {
          text.textContent = 'Auto-refreshes in ' + nextRefresh + 's';
        }
      }
    }
  }, 1000);
})();
</script>
<?php $extraScripts .= ob_get_clean(); ?>
