<?php
$missions = $missions ?? [];
$teams    = $teams    ?? [];

$hazardColors = [
    'fire'       => ['#fef2f2','#dc2626','F'],
    'flood'      => ['#eff6ff','#2563eb','W'],
    'earthquake' => ['#fffbeb','#d97706','E'],
    'medical'    => ['#fdf4ff','#9333ea','M'],
    'accident'   => ['#fff7ed','#ea580c','A'],
    'evacuation' => ['#f0fdf4','#16a34a','V'],
    'security'   => ['#eef2ff','#4f46e5','S'],
    'other'      => ['#f8fafc','#64748b','O'],
];
$triageColors = [
    'critical'             => ['#fef2f2','#dc2626','Critical'],
    'high'                 => ['#fff7ed','#ea580c','High'],
    'moderate'             => ['#fffbeb','#d97706','Moderate'],
    'low'                  => ['#f0fdf4','#16a34a','Low'],
    'pending_reassessment' => ['#f1f5f9','#475569','Pending'],
];
$statusLabels = [
    'assigned'     => ['#eff6ff','#2563eb','Assigned'],
    'acknowledged' => ['#ecfeff','#0891b2','Acknowledged'],
    'on_scene'     => ['#f0fdfa','#0d9488','On Scene'],
    'in_progress'  => ['#fffbeb','#d97706','In Progress'],
    'resolved'     => ['#f0fdf4','#16a34a','Resolved'],
];

$unitTypeLabels = [
    'fire'     => 'Fire Unit',
    'medical'  => 'Medical Unit',
    'rescue'   => 'Rescue Unit',
    'security' => 'Security Unit',
    'multi'    => 'Multi-Hazard Unit',
];
?>

<style>
/* ── Responder Home ── */
.rsp-section-label {
  font-size: .6rem; font-weight: 800; text-transform: uppercase;
  letter-spacing: .07em; color: #94a3b8;
  padding: 4px 16px 8px; display: block;
}
.rsp-team-chip {
  display: flex; align-items: center; gap: 10px;
  margin: 0 16px 12px;
  background: #fff; border: 1px solid #e2e8f0;
  border-radius: 12px; padding: 12px 14px;
  box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.rsp-mission-card {
  background: #fff; border-radius: 16px;
  border: 1px solid #e2e8f0; margin: 0 16px 12px;
  overflow: hidden;
  box-shadow: 0 1px 4px rgba(0,0,0,.04);
  transition: box-shadow .15s, transform .1s;
}
.rsp-mission-card:active { transform: scale(.99); box-shadow: 0 0 0 2px rgba(220,38,38,.15); }
.rsp-mission-card.support-alert { border-color: #fca5a5; }
.rsp-mission-card a { text-decoration: none; color: inherit; display: block; }
.rsp-support-banner {
  background: #fef2f2; border-bottom: 1px solid #fecaca;
  padding: 7px 14px; display: flex; align-items: center; gap: 7px;
  font-size: .72rem; font-weight: 700; color: #dc2626;
}
.rsp-mission-body { padding: 14px; }
.rsp-hazard-av {
  width: 32px; height: 32px; border-radius: 8px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  font-size: .65rem; font-weight: 800;
}
.rsp-triage { display: inline-block; border-radius: 4px; padding: 2px 8px; font-size: .65rem; font-weight: 700; }
.rsp-status-chip { display: inline-flex; align-items: center; gap: 4px; border-radius: 5px; padding: 3px 9px; font-size: .67rem; font-weight: 700; }
.rsp-elapsed { font-size: .7rem; color: #94a3b8; font-variant-numeric: tabular-nums; }

/* Empty state */
.rsp-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 52px 24px; text-align: center; }
.rsp-empty-icon { width: 72px; height: 72px; border-radius: 20px; background: #f8fafc; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; }

/* Quick stats */
.rsp-stats { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 0 16px 16px; }
.rsp-stat-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; }
.rsp-stat-val { font-size: 1.5rem; font-weight: 800; color: #0f172a; line-height: 1; }
.rsp-stat-lbl { font-size: .67rem; color: #94a3b8; margin-top: 3px; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
</style>

<div style="padding-top: 4px;">

  <!-- Team chips -->
  <?php if (!empty($teams)):
    $user = auth();
  ?>
    <span class="rsp-section-label" style="padding-top: 12px;">My Team<?= count($teams) > 1 ? 's' : '' ?></span>
    <?php foreach ($teams as $t):
      $ut = $unitTypeLabels[$t['unit_type'] ?? ''] ?? ucfirst($t['unit_type'] ?? 'Team');
      $isOnMission = ($t['status'] ?? '') === 'on_mission';
    ?>
      <div class="rsp-team-chip">
        <div style="width:36px;height:36px;border-radius:10px;background:<?= $isOnMission ? '#fff7ed' : '#f0fdf4' ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="<?= $isOnMission ? '#ea580c' : '#16a34a' ?>" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
        </div>
        <div style="flex:1;min-width:0;">
          <div style="font-size:.8125rem;font-weight:700;color:#0f172a;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($t['team_name']) ?></div>
          <div style="font-size:.7rem;color:#64748b;"><?= e($ut) ?><?= !empty($t['contact_no']) ? ' · ' . e($t['contact_no']) : '' ?></div>
        </div>
        <span style="font-size:.65rem;font-weight:700;padding:3px 9px;border-radius:5px;background:<?= $isOnMission ? '#fff7ed' : '#f0fdf4' ?>;color:<?= $isOnMission ? '#ea580c' : '#16a34a' ?>;">
          <?= $isOnMission ? 'On Mission' : 'Available' ?>
        </span>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <!-- Stats -->
  <?php
  $activeMissions = count(array_filter($missions, fn($m) => !in_array($m['incident_status']??'', ['resolved','closed'])));
  $criticalCount  = count(array_filter($missions, fn($m) => ($m['triage_level']??'') === 'critical'));
  ?>
  <div class="rsp-stats">
    <div class="rsp-stat-card">
      <div class="rsp-stat-val"><?= $activeMissions ?></div>
      <div class="rsp-stat-lbl">Active Missions</div>
    </div>
    <div class="rsp-stat-card">
      <div class="rsp-stat-val" style="color:<?= $criticalCount > 0 ? '#dc2626' : '#0f172a' ?>;"><?= $criticalCount ?></div>
      <div class="rsp-stat-lbl">Critical</div>
    </div>
  </div>

  <!-- Mission list -->
  <span class="rsp-section-label">Assigned Missions</span>

  <?php if (empty($missions)): ?>
    <div class="rsp-empty">
      <div class="rsp-empty-icon">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
      </div>
      <div style="font-size:.9375rem;font-weight:700;color:#0f172a;margin-bottom:6px;">No missions assigned</div>
      <div style="font-size:.8rem;color:#94a3b8;line-height:1.5;">Your team is available for dispatch.<br>Missions will appear here when assigned.</div>
    </div>
  <?php else: ?>
    <?php foreach ($missions as $m):
      $haz   = $m['hazard_type']     ?? 'other';
      $tl    = $m['triage_level']    ?? 'low';
      $istat = $m['incident_status'] ?? 'assigned';
      [$hBg,$hColor,$hInit] = $hazardColors[$haz] ?? $hazardColors['other'];
      [$tBg,$tColor,$tLabel] = $triageColors[$tl] ?? $triageColors['low'];
      [$sBg,$sColor,$sLabel] = $statusLabels[$istat] ?? ['#f8fafc','#64748b',ucwords(str_replace('_',' ',$istat))];
      $hasSupport = (int)($m['open_support'] ?? 0) > 0;
    ?>
      <div class="rsp-mission-card <?= $hasSupport ? 'support-alert' : '' ?>">
        <a href="<?= base_url('app/responder/missions/' . e($m['assignment_id'] ?? $m['id'] ?? '')) ?>">
          <?php if ($hasSupport): ?>
            <div class="rsp-support-banner">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
              Support requested from dispatch
            </div>
          <?php endif; ?>
          <div class="rsp-mission-body">
            <!-- Top row: hazard + code + triage -->
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
              <div class="rsp-hazard-av" style="background:<?= $hBg ?>;color:<?= $hColor ?>;"><?= $hInit ?></div>
              <div style="flex:1;min-width:0;">
                <div style="font-size:.8rem;font-weight:800;color:#0f172a;font-family:monospace;"><?= e($m['code'] ?? '') ?></div>
                <div style="font-size:.7rem;color:#64748b;text-transform:capitalize;"><?= e($haz) ?> · <?= e($m['team_name'] ?? '') ?></div>
              </div>
              <span class="rsp-triage" style="background:<?= $tBg ?>;color:<?= $tColor ?>;"><?= $tLabel ?></span>
            </div>

            <!-- Address -->
            <?php if (!empty($m['address'])): ?>
              <div style="display:flex;align-items:flex-start;gap:6px;margin-bottom:10px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" style="margin-top:1px;flex-shrink:0;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <div style="font-size:.78rem;color:#475569;line-height:1.4;"><?= e($m['address']) ?></div>
              </div>
            <?php endif; ?>

            <!-- Status + elapsed -->
            <div style="display:flex;align-items:center;justify-content:space-between;">
              <span class="rsp-status-chip" style="background:<?= $sBg ?>;color:<?= $sColor ?>;">
                <span style="width:5px;height:5px;border-radius:50%;background:<?= $sColor ?>;"></span>
                <?= $sLabel ?>
              </span>
              <span class="rsp-elapsed elapsed-timer" data-assigned="<?= e($m['assigned_at'] ?? '') ?>">--:--</span>
            </div>
          </div>
        </a>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <div style="height: 16px;"></div>
</div>

<script>
function updateTimers() {
  document.querySelectorAll('.elapsed-timer[data-assigned]').forEach(function(el) {
    var raw = el.dataset.assigned;
    if (!raw) return;
    var d = new Date(raw.replace(' ','T'));
    if (isNaN(d)) return;
    var diff = Math.max(0, Math.floor((Date.now() - d) / 1000));
    var h = Math.floor(diff/3600), m = Math.floor((diff%3600)/60), s = diff%60;
    el.textContent = (h>0?h+'h ':'')+m+'m '+s+'s';
  });
}
setInterval(updateTimers, 1000);
updateTimers();

// Auto-refresh missions every 30s
setInterval(function() { window.location.reload(); }, 30000);
</script>
