<?php
$kpis          = $kpis ?? [];
$dailyIncidents = $dailyIncidents ?? [];
$byHazard      = $byHazard ?? [];
$latestReports = $latestReports ?? [];
$urgentReports = array_filter($latestReports, fn($r) => in_array($r['triage_level'], ['critical', 'high']));

$triageBadge = function (string $level): string {
    $map = [
        'critical'             => ['bg-red-lt text-red border border-red-subtle',       'Critical'],
        'high'                 => ['bg-orange-lt text-orange border border-orange-subtle','High'],
        'moderate'             => ['bg-yellow-lt text-yellow border border-yellow-subtle','Moderate'],
        'low'                  => ['bg-green-lt text-green border border-green-subtle',  'Low'],
        'pending_reassessment' => ['bg-slate-lt text-slate border border-slate-subtle',  'Pending'],
    ];
    [$cls, $label] = $map[$level] ?? ['bg-light text-muted', ucfirst($level)];
    return '<span class="badge ' . $cls . ' fw-bold px-2 py-1" style="font-size:.6rem;border-radius:4px;">' . e($label) . '</span>';
};

$statusBadge = function (string $status): string {
    $map = [
        'submitted'    => ['bg-orange-lt text-orange', 'For Verification'],
        'verified'     => ['bg-blue-lt text-blue',     'Verified'],
        'rejected'     => ['bg-red-lt text-red',       'Rejected'],
        'duplicate'    => ['bg-light text-muted',      'Duplicate'],
        'triaged'      => ['bg-purple-lt text-purple', 'Triaged'],
        'assigned'     => ['bg-indigo-lt text-indigo', 'Assigned'],
        'acknowledged' => ['bg-azure-lt text-azure',   'Acknowledged'],
        'on_scene'     => ['bg-teal-lt text-teal',     'On Scene'],
        'in_progress'  => ['bg-amber-lt text-amber',   'In Progress'],
        'resolved'     => ['bg-green-lt text-green',   'Resolved'],
        'closed'       => ['bg-dark-lt text-dark',     'Closed'],
    ];
    [$cls, $label] = $map[$status] ?? ['bg-light text-muted', ucwords(str_replace('_', ' ', $status))];
    return '<span class="badge ' . $cls . ' fw-bold px-2 py-1" style="font-size:.6rem;border-radius:4px;">' . e($label) . '</span>';
};
?>

<div class="page-header d-print-none mb-2">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title fs-2 fw-bold tracking-tight text-slate mb-0" id="dash-greeting" data-name="<?= e(explode(' ', auth()['name'])[0]) ?>">Good <?= date('H') < 12 ? 'morning' : (date('H') < 18 ? 'afternoon' : 'evening') ?>, <?= e(explode(' ', auth()['name'])[0]) ?>!</h2>
        <div class="text-muted xx-small fw-bold opacity-50 d-none d-sm-block">Here's the current situation in your barangay.</div>
      </div>
      <div class="col-auto">
        <div class="bg-white border px-2 py-1 rounded-2 d-flex align-items-center gap-2 shadow-sm" style="min-height: 38px;">
            <div class="text-blue opacity-50 d-none d-md-block">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div class="lh-1">
                <div class="fw-bold text-slate x-small" id="dash-date"><?= date('F j, Y') ?></div>
                <div class="text-muted xx-small" id="dash-time" style="font-size: 0.6rem;"><?= date('l, g:i A') ?></div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container-xl">

  <!-- Compact Stat Cards Row (5 columns) -->
  <div class="stat-grid-container mb-3">
    <div class="stat-grid-item">
      <a href="<?= base_url('reports') ?>" class="text-decoration-none">
      <div class="stat-card-premium border-top-wide border-blue">
        <div class="stat-card-header">
            <div class="stat-label">Total Reports</div>
            <div class="icon-wrapper bg-blue-lt text-blue">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
        </div>
        <div class="stat-value"><?= (int) ($kpis['total_reports'] ?? 0) ?></div>
        <div class="stat-description">All reports recorded</div>
      </div>
      </a>
    </div>

    <div class="stat-grid-item">
      <a href="<?= base_url('reports?is_emergency=1') ?>" class="text-decoration-none">
      <div class="stat-card-premium border-top-wide border-red">
        <div class="stat-card-header">
            <div class="stat-label">Emergency</div>
            <div class="icon-wrapper bg-red-lt text-red">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
        </div>
        <div class="stat-value"><?= (int) ($kpis['emergency_reports'] ?? 0) ?></div>
        <div class="stat-description">Flagged as emergency</div>
      </div>
      </a>
    </div>

    <div class="stat-grid-item">
      <a href="<?= base_url('reports?is_emergency=0') ?>" class="text-decoration-none">
      <div class="stat-card-premium border-top-wide border-azure">
        <div class="stat-card-header">
            <div class="stat-label">Non-Emergency</div>
            <div class="icon-wrapper bg-azure-lt text-azure">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
        </div>
        <div class="stat-value"><?= (int) ($kpis['non_emergency_reports'] ?? 0) ?></div>
        <div class="stat-description">Standard reports</div>
      </div>
      </a>
    </div>

    <div class="stat-grid-item">
      <a href="<?= base_url('verification') ?>" class="text-decoration-none">
      <div class="stat-card-premium border-top-wide border-orange">
        <div class="stat-card-header">
            <div class="stat-label">Pending</div>
            <div class="icon-wrapper bg-orange-lt text-orange">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
            </div>
        </div>
        <div class="stat-value"><?= (int) ($kpis['pending_verification'] ?? 0) ?></div>
        <div class="stat-description">Awaiting verification</div>
      </div>
      </a>
    </div>

    <div class="stat-grid-item">
      <a href="<?= base_url('reports?status=resolved') ?>" class="text-decoration-none">
      <div class="stat-card-premium border-top-wide border-green">
        <div class="stat-card-header">
            <div class="stat-label">Resolved</div>
            <div class="icon-wrapper bg-green-lt text-green">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <div class="stat-value"><?= (int) ($kpis['resolved_reports'] ?? 0) ?></div>
        <div class="stat-description">Successfully resolved</div>
      </div>
      </a>
    </div>
  </div>

  <div class="row row-deck row-cards">
    <!-- Urgent Reports Section -->
    <div class="col-12">
      <div class="card card-premium shadow-none border">
        <div class="card-header d-flex align-items-center justify-content-between py-2">
          <div class="d-flex align-items-center gap-2">
            <div class="text-red">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            </div>
            <h3 class="card-title">Urgent Emergency Reports</h3>
          </div>
          <?php if (!empty($urgentReports)): ?>
            <span class="badge bg-red-lt text-red rounded-pill"><?= count($urgentReports) ?></span>
          <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <?php if (empty($urgentReports)): ?>
                <div class="text-center py-4 text-muted small">No urgent reports at this moment.</div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                <?php foreach ($urgentReports as $r): ?>
                    <div class="list-group-item py-2 px-3">
                        <div class="row align-items-center g-3">
                            <div class="col-auto">
                                <span class="badge bg-red-lt text-red text-uppercase x-small fw-bold">High</span>
                            </div>
                            <div class="col">
                                <div class="fw-bold small text-slate"><?= e($r['description'] ?: 'No description provided.') ?></div>
                                <div class="text-muted x-small d-flex align-items-center mt-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                    <?= e($r['address'] ?: ($r['location_address'] ?? 'Location not specified')) ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <a href="<?= base_url('reports/' . e($r['code'])) ?>" class="btn btn-sm btn-primary px-3">View</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Active Incidents & Live Updates -->
    <div class="col-md-7">
        <div class="card card-premium shadow-none border">
            <div class="card-header d-flex align-items-center justify-content-between py-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="text-red">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
                    </div>
                    <h3 class="card-title">Active Incidents</h3>
                    <?php if (($activeCount ?? 0) > 0): ?>
                        <span class="badge bg-red-lt text-red rounded-pill ms-1"><?= (int)($activeCount ?? 0) ?> in progress</span>
                    <?php endif; ?>
                </div>
                <a href="<?= base_url('monitoring') ?>" class="x-small text-primary text-decoration-none fw-bold">Monitoring board →</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($activeIncidents ?? [])): ?>
                    <div class="text-center py-4">
                        <div class="text-muted mb-1">
                            <span class="status-dot status-dot-animated bg-green me-2"></span>
                            No active incidents
                        </div>
                        <div class="text-muted small">All clear — command center is standing by.</div>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                    <?php foreach (($activeIncidents ?? []) as $ai):
                        $aiTriage = $triageBadge($ai['triage_level'] ?? 'low');
                        $aiStatus = $statusBadge($ai['status'] ?? '');
                        $aiTs = strtotime($ai['created_at'] ?? '');
                        $aiDiff = time() - $aiTs;
                        if     ($aiDiff < 3600)  $aiAgo = round($aiDiff/60).'m ago';
                        elseif ($aiDiff < 86400) $aiAgo = round($aiDiff/3600).'h ago';
                        else                     $aiAgo = date('M j', $aiTs);
                    ?>
                        <a href="<?= base_url('reports/' . e($ai['code'])) ?>" class="list-group-item list-group-item-action py-2 px-3 text-decoration-none">
                            <div class="row align-items-center g-2">
                                <div class="col-auto">
                                    <?= $aiTriage ?>
                                </div>
                                <div class="col">
                                    <div class="fw-bold small text-slate"><?= e($ai['code']) ?> — <?= e($ai['hazard_type'] ?? 'Unknown') ?></div>
                                    <div class="text-muted x-small text-truncate" style="max-width:280px;"><?= e($ai['description'] ?: 'No description') ?></div>
                                </div>
                                <div class="col-auto d-flex align-items-center gap-2">
                                    <?= $aiStatus ?>
                                    <?php if (!empty($ai['team_name'])): ?>
                                        <span class="text-muted xx-small text-nowrap"><?= e($ai['team_name']) ?></span>
                                    <?php endif; ?>
                                    <span class="text-muted xx-small"><?= e($aiAgo) ?></span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card card-premium shadow-none border">
            <div class="card-header d-flex align-items-center justify-content-between py-2">
                <h3 class="card-title">Live Updates</h3>
                <a href="<?= base_url('monitoring') ?>" class="x-small text-primary text-decoration-none fw-bold">View all updates →</a>
            </div>
            <div class="card-body p-0" style="max-height: 240px; overflow-y: auto;">
                <?php if (empty($liveUpdates ?? [])): ?>
                    <div class="text-center py-4 text-muted small">No recent updates.</div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                    <?php foreach (($liveUpdates ?? []) as $u):
                        $ts   = strtotime($u['created_at'] ?? '');
                        $diff = time() - $ts;
                        if     ($diff < 3600)      $ago = round($diff/60).'m ago';
                        elseif ($diff < 86400)     $ago = round($diff/3600).'h ago';
                        else                       $ago = date('M j, g:i A', $ts);
                        $statusColors = [
                            'submitted'=>'bg-orange-lt text-orange','verified'=>'bg-blue-lt text-blue',
                            'resolved'=>'bg-green-lt text-green','assigned'=>'bg-indigo-lt text-indigo',
                            'acknowledged'=>'bg-azure-lt text-azure','on_scene'=>'bg-teal-lt text-teal',
                            'in_progress'=>'bg-amber-lt text-amber','closed'=>'bg-dark-lt text-dark',
                        ];
                        $sc = $statusColors[$u['incident_status'] ?? ''] ?? 'bg-light text-muted';
                        $slabel = ucwords(str_replace('_',' ',$u['incident_status'] ?? ''));
                    ?>
                        <div class="list-group-item py-2 px-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <span class="text-muted x-small"><?= e($ago) ?></span>
                                <span class="badge <?= $sc ?> x-small fw-bold" style="font-size:.6rem;"><?= e($slabel) ?></span>
                            </div>
                            <div class="fw-bold small text-slate"><?= e($u['incident_code'] ?? '') ?></div>
                            <div class="text-muted x-small text-truncate" style="max-width:200px;"><?= e($u['message'] ?? '') ?></div>
                            <?php if (!empty($u['team_name'])): ?>
                                <div class="text-muted xx-small mt-1"><?= e($u['team_name']) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
  </div>

</div>
<?php
$extraScripts = $extraScripts ?? '';
ob_start();
?>
<script>
// ── Live clock using browser time (matches user's device) ──────────
(function() {
  var days   = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
  var months = ['January','February','March','April','May','June','July','August','September','October','November','December'];

  function tick() {
    var now  = new Date();
    var h    = now.getHours();
    var m    = now.getMinutes();
    var s    = now.getSeconds();
    var ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12 || 12;
    var timeStr = h + ':' + (m<10?'0':'')+m + ':' + (s<10?'0':'')+s + ' ' + ampm;
    var dateStr = months[now.getMonth()] + ' ' + now.getDate() + ', ' + now.getFullYear();
    var dayStr  = days[now.getDay()] + ', ' + timeStr;

    var de = document.getElementById('dash-date');
    var te = document.getElementById('dash-time');
    if (de) de.textContent = dateStr;
    if (te) te.textContent = dayStr;

    // Update greeting based on current hour
    var greet = document.getElementById('dash-greeting');
    if (greet) {
      var hr = now.getHours();
      var g  = hr < 12 ? 'Good morning' : (hr < 18 ? 'Good afternoon' : 'Good evening');
      var name = greet.dataset.name || '';
      greet.textContent = g + ', ' + name + '!';
    }
  }

  tick();
  setInterval(tick, 1000);
})();
</script>
<?php $extraScripts .= ob_get_clean(); ?>