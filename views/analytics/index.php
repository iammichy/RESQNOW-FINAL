<?php
$filters       = $filters       ?? [];
$kpis          = $kpis          ?? [];
$chartDaily    = $chartDaily    ?? ['dates' => [], 'counts' => []];
$chartHazard   = $chartHazard   ?? ['labels' => [], 'counts' => []];
$chartTriage   = $chartTriage   ?? ['labels' => [], 'counts' => []];
$chartStatus   = $chartStatus   ?? ['labels' => [], 'counts' => []];
$topAreas      = $topAreas      ?? [];
$dailyTable    = $dailyTable    ?? [];
$hazardSummary = $hazardSummary ?? [];
$barangays     = $barangays     ?? [];
$overallSummary= $overallSummary ?? [];
$flash         = $flash         ?? null;

$fmtMins = function (?int $m): string {
    if ($m === null) return '—';
    if ($m < 60) return $m . 'm';
    return floor($m / 60) . 'h ' . ($m % 60) . 'm';
};

$exportQuery = http_build_query(array_filter($filters));
$exportUrl   = base_url('analytics/export') . ($exportQuery ? '?' . $exportQuery : '');
$hasAnyData  = ($kpis['total_reports'] ?? 0) > 0;
?>
<style>
/* ── Analytics page ─────────────────────────────────────── */
.an-wrap    { padding: 20px 24px 32px; }
.an-head    { display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px; }
.an-title   { font-size:1.25rem;font-weight:800;color:#0f172a;letter-spacing:-.02em;margin:0 0 2px; }
.an-sub     { font-size:.78rem;color:#64748b;margin:0; }
.an-export  { display:inline-flex;align-items:center;gap:6px;height:36px;padding:0 16px;background:#dc2626;color:#fff;border-radius:6px;font-size:.8rem;font-weight:700;text-decoration:none;box-shadow:0 2px 8px rgba(220,38,38,.3);white-space:nowrap; }
.an-export:hover { background:#b91c1c;color:#fff; }

/* Filter bar */
.an-filter  { background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 16px;margin-bottom:20px; }
.an-filter input[type=date],
.an-filter select {
  height:32px;border:1.5px solid #e2e8f0;border-radius:5px;
  padding:0 8px;font-size:.78rem;color:#0f172a;background:#f8fafc;
  outline:none;transition:border-color .12s;font-family:inherit;
}
.an-filter input[type=date]:focus,
.an-filter select:focus { border-color:#dc2626;background:#fff; }
.an-btn-apply  { height:32px;padding:0 14px;background:#0f172a;color:#fff;border:none;border-radius:5px;font-size:.78rem;font-weight:700;cursor:pointer;white-space:nowrap; }
.an-btn-reset  { height:32px;padding:0 11px;background:#fff;color:#475569;border:1.5px solid #e2e8f0;border-radius:5px;font-size:.78rem;font-weight:600;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center; }

/* KPI cards */
.an-kpi-grid { display:grid;grid-template-columns:repeat(6,1fr);gap:12px;margin-bottom:20px; }
@media(max-width:1100px){ .an-kpi-grid { grid-template-columns:repeat(3,1fr); } }
@media(max-width:640px) { .an-kpi-grid { grid-template-columns:repeat(2,1fr); } }
.an-kpi { background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:14px 16px; }
.an-kpi-label { font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;margin-bottom:8px; }
.an-kpi-value { font-size:1.5rem;font-weight:800;color:#0f172a;line-height:1;letter-spacing:-.02em; }

/* Chart + table cards */
.an-card { background:#fff;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden; }
.an-card-hd { padding:13px 18px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between; }
.an-card-title { font-size:.78rem;font-weight:700;color:#0f172a; }
.an-card-sub   { font-size:.68rem;color:#94a3b8;margin-left:6px; }
.an-card-body  { padding:16px; }
.an-empty { text-align:center;padding:40px 20px;font-size:.8rem;color:#94a3b8; }

/* Grid rows */
.an-row    { display:grid;gap:16px;margin-bottom:16px; }
.an-row-2  { grid-template-columns:2fr 1fr; }
.an-row-2b { grid-template-columns:1fr 1fr; }
.an-row-tb { grid-template-columns:5fr 7fr; }
@media(max-width:900px){ .an-row-2,.an-row-2b,.an-row-tb { grid-template-columns:1fr; } }

/* Tables */
.an-table { width:100%;border-collapse:collapse;font-size:.8rem; }
.an-table th { padding:8px 14px;font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#94a3b8;border-bottom:1px solid #f1f5f9; }
.an-table td { padding:9px 14px;border-bottom:1px solid #f8fafc; }
.an-table tr:last-child td { border-bottom:none; }
.an-progress { display:flex;align-items:center;gap:8px; }
.an-progress-bar { flex:1;height:4px;background:#f1f5f9;border-radius:2px; }
.an-progress-fill { height:4px;background:#dc2626;border-radius:2px; }
.an-scroll { max-height:360px;overflow-y:auto; }
</style>

<div class="an-wrap">

  <?php if ($flash): ?>
    <div style="padding:10px 14px;border-radius:6px;margin-bottom:16px;font-size:.8125rem;<?= $flash['type']==='success' ? 'background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;' : 'background:#fef2f2;border:1px solid #fecaca;color:#991b1b;' ?>">
      <?= e($flash['message']) ?>
    </div>
  <?php endif; ?>

  <!-- Header -->
  <div class="an-head">
    <div>
      <h1 class="an-title">Analytics</h1>
      <p class="an-sub">
        <?php if (!empty($filters['date_from']) && !empty($filters['date_to'])): ?>
          <?= date('M j, Y', strtotime($filters['date_from'])) ?> — <?= date('M j, Y', strtotime($filters['date_to'])) ?>
        <?php else: ?>
          All time
        <?php endif; ?>
      </p>
    </div>
    <button type="button" id="btn-export-csv" class="an-export">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
      Export to CSV
    </button>
  </div>

  <!-- Filter bar — compact single row -->
  <div class="an-filter">
    <form method="get" action="<?= base_url('analytics') ?>">
      <div style="display:flex;align-items:center;flex-wrap:wrap;gap:8px;">

        <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          <input type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>" style="width:130px;" title="From">
          <span style="color:#cbd5e1;font-size:.8rem;">—</span>
          <input type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>" style="width:130px;" title="To">
        </div>

        <div style="width:1px;height:20px;background:#e2e8f0;flex-shrink:0;"></div>

        <select name="hazard_type" title="Hazard Type" style="width:130px;">
          <option value="">All hazards</option>
          <?php foreach (['fire','flood','earthquake','medical','accident','evacuation','security','other'] as $h): ?>
            <option value="<?= $h ?>" <?= ($filters['hazard_type'] ?? '') === $h ? 'selected' : '' ?>><?= ucfirst($h) ?></option>
          <?php endforeach; ?>
        </select>

        <select name="status" title="Status" style="width:140px;">
          <option value="">All statuses</option>
          <?php foreach (['submitted','verified','rejected','duplicate','triaged','assigned','acknowledged','on_scene','in_progress','resolved','closed'] as $s): ?>
            <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option>
          <?php endforeach; ?>
        </select>

        <select name="triage_level" title="Triage Level" style="width:130px;">
          <option value="">All levels</option>
          <?php foreach (['critical','high','moderate','low','pending_reassessment'] as $t): ?>
            <option value="<?= $t ?>" <?= ($filters['triage_level'] ?? '') === $t ? 'selected' : '' ?>><?= ucwords(str_replace('_',' ',$t)) ?></option>
          <?php endforeach; ?>
        </select>

        <select name="barangay_id" title="Barangay" style="width:130px;">
          <option value="">All barangays</option>
          <?php foreach ($barangays as $b): ?>
            <option value="<?= $b['id'] ?>" <?= ((int)($filters['barangay_id'] ?? 0)) === (int)$b['id'] ? 'selected' : '' ?>><?= e($b['name']) ?></option>
          <?php endforeach; ?>
        </select>

        <div style="display:flex;gap:6px;margin-left:auto;flex-shrink:0;">
          <button type="submit" class="an-btn-apply">Apply</button>
          <a href="<?= base_url('analytics') ?>" class="an-btn-reset">Reset</a>
        </div>

      </div>
    </form>
  </div>

  <?php if (!$hasAnyData): ?>
    <!-- Empty state -->
    <div class="an-card">
      <div style="text-align:center;padding:56px 20px;">
        <div style="width:48px;height:48px;border-radius:50%;background:#f8fafc;border:1.5px solid #e2e8f0;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        </div>
        <div style="font-size:.9375rem;font-weight:700;color:#0f172a;margin-bottom:4px;">No data for selected filters</div>
        <div style="font-size:.8125rem;color:#64748b;margin-bottom:16px;">Try adjusting the date range or removing filters.</div>
        <a href="<?= base_url('analytics') ?>" style="display:inline-flex;height:34px;padding:0 16px;align-items:center;background:#0f172a;color:#fff;border-radius:6px;font-size:.8rem;font-weight:600;text-decoration:none;">Reset filters</a>
      </div>
    </div>

  <?php else: ?>

  <!-- Overall Summary banner -->
  <?php
  $atTotal    = $overallSummary['total']          ?? 0;
  $atResolved = $overallSummary['resolved']        ?? 0;
  $atActive   = $overallSummary['active']          ?? 0;
  $atPending  = $overallSummary['pending']         ?? 0;
  $atCritical = $overallSummary['critical']        ?? 0;
  $atEmerg    = $overallSummary['emergency']       ?? 0;
  $topHazard  = $overallSummary['top_hazard']      ?? null;
  $topArea    = $overallSummary['top_area']        ?? null;
  $resolveRate= $atTotal > 0 ? round($atResolved/$atTotal*100) : 0;
  $emergRate  = $atTotal > 0 ? round($atEmerg/$atTotal*100) : 0;
  ?>
  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:16px 20px;margin-bottom:16px;">
    <div style="font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;margin-bottom:12px;">Overall Summary (All Time)</div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:16px;">
      <div>
        <div style="font-size:1.5rem;font-weight:800;color:#0f172a;line-height:1;"><?= (int)$atTotal ?></div>
        <div style="font-size:.72rem;color:#94a3b8;margin-top:3px;">Total Reports</div>
      </div>
      <div>
        <div style="font-size:1.5rem;font-weight:800;color:#16a34a;line-height:1;"><?= (int)$atResolved ?></div>
        <div style="font-size:.72rem;color:#94a3b8;margin-top:3px;">Resolved <span style="color:#16a34a;">(<?= $resolveRate ?>%)</span></div>
      </div>
      <div>
        <div style="font-size:1.5rem;font-weight:800;color:#dc2626;line-height:1;"><?= (int)$atCritical ?></div>
        <div style="font-size:.72rem;color:#94a3b8;margin-top:3px;">Critical Priority</div>
      </div>
      <div>
        <div style="font-size:1.5rem;font-weight:800;color:#ea580c;line-height:1;"><?= (int)$atActive ?></div>
        <div style="font-size:.72rem;color:#94a3b8;margin-top:3px;">Active Missions</div>
      </div>
      <div>
        <div style="font-size:1.5rem;font-weight:800;color:#d97706;line-height:1;"><?= (int)$atPending ?></div>
        <div style="font-size:.72rem;color:#94a3b8;margin-top:3px;">Awaiting Verification</div>
      </div>
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:20px;margin-top:12px;padding-top:12px;border-top:1px solid #f1f5f9;">
      <?php if (!empty($topHazard)): ?>
        <div style="display:flex;align-items:center;gap:6px;">
          <span style="font-size:.68rem;color:#94a3b8;font-weight:700;text-transform:uppercase;">Top Hazard:</span>
          <span style="font-size:.78rem;font-weight:700;color:#0f172a;text-transform:capitalize;"><?= e($topHazard['hazard_type']) ?></span>
          <span style="font-size:.68rem;color:#94a3b8;"><?= (int)$topHazard['cnt'] ?> reports</span>
        </div>
      <?php endif; ?>
      <?php if (!empty($topArea) && !empty($topArea['area'])): ?>
        <div style="display:flex;align-items:center;gap:6px;">
          <span style="font-size:.68rem;color:#94a3b8;font-weight:700;text-transform:uppercase;">Most Affected Area:</span>
          <span style="font-size:.78rem;font-weight:700;color:#0f172a;"><?= e($topArea['area']) ?></span>
          <span style="font-size:.68rem;color:#94a3b8;"><?= (int)$topArea['cnt'] ?> reports</span>
        </div>
      <?php endif; ?>
      <div style="display:flex;align-items:center;gap:6px;">
        <span style="font-size:.68rem;color:#94a3b8;font-weight:700;text-transform:uppercase;">Emergency Rate:</span>
        <span style="font-size:.78rem;font-weight:700;color:#dc2626;"><?= $emergRate ?>%</span>
        <span style="font-size:.68rem;color:#94a3b8;">(<?= (int)$atEmerg ?> of <?= (int)$atTotal ?>)</span>
      </div>
    </div>
  </div>

  <!-- KPI Row -->
  <div class="an-kpi-grid">    <?php
    $kpiCards = [
      ['label'=>'Total Reports',        'value'=>(int)($kpis['total_reports']??0)],
      ['label'=>'Pending Verification', 'value'=>(int)($kpis['pending_verification']??0)],
      ['label'=>'Active Missions',      'value'=>(int)($kpis['active_missions']??0)],
      ['label'=>'Resolved',             'value'=>(int)($kpis['resolved_total']??0)],
      ['label'=>'Avg Ack Time',         'value'=>$fmtMins($kpis['avg_acknowledge_minutes']??null)],
      ['label'=>'Avg Resolve Time',     'value'=>$fmtMins($kpis['avg_resolve_minutes']??null)],
    ];
    foreach ($kpiCards as $kc): ?>
      <div class="an-kpi">
        <div class="an-kpi-label"><?= $kc['label'] ?></div>
        <div class="an-kpi-value"><?= $kc['value'] ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Charts row 1: daily + hazard -->
  <div class="an-row an-row-2">
    <div class="an-card">
      <div class="an-card-hd">
        <span class="an-card-title">Daily Incidents</span>
      </div>
      <div class="an-card-body">
        <?php if (empty($chartDaily['dates']) || max($chartDaily['counts'] ?? [0]) === 0 && count($chartDaily['dates']) === 0): ?>
          <div class="an-empty">No data for selected filters.</div>
        <?php else: ?>
          <div id="chart-daily" style="height:260px;"></div>
        <?php endif; ?>
      </div>
    </div>
    <div class="an-card">
      <div class="an-card-hd">
        <span class="an-card-title">By Hazard Type</span>
      </div>
      <div class="an-card-body">
        <?php if (empty($chartHazard['labels'])): ?>
          <div class="an-empty">No data.</div>
        <?php else: ?>
          <div id="chart-hazard" style="height:260px;"></div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Charts row 2: triage donut + status bar -->
  <div class="an-row an-row-2b">
    <div class="an-card">
      <div class="an-card-hd">
        <span class="an-card-title">By Triage Level</span>
      </div>
      <div class="an-card-body" style="display:flex;align-items:center;justify-content:center;">
        <?php if (empty($chartTriage['labels'])): ?>
          <div class="an-empty">No data.</div>
        <?php else: ?>
          <div id="chart-triage" style="height:260px;width:100%;"></div>
        <?php endif; ?>
      </div>
    </div>
    <div class="an-card">
      <div class="an-card-hd">
        <span class="an-card-title">By Status</span>
      </div>
      <div class="an-card-body">
        <?php if (empty($chartStatus['labels'])): ?>
          <div class="an-empty">No data.</div>
        <?php else: ?>
          <div id="chart-status" style="height:260px;"></div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Tables row: top areas + daily counts -->
  <div class="an-row an-row-tb" style="margin-bottom:16px;">

    <div class="an-card">
      <div class="an-card-hd"><span class="an-card-title">Top Areas</span></div>
      <?php
      $areaRows = $topAreas['by_barangay'] ?? [];
      $aTotal   = array_sum(array_column($areaRows, 'count'));
      ?>
      <?php if (empty($areaRows)): ?>
        <div class="an-empty">No data.</div>
      <?php else: ?>
        <table class="an-table">
          <thead><tr><th>#</th><th>Area</th><th>Count</th><th>Share</th></tr></thead>
          <tbody>
            <?php foreach ($areaRows as $i => $row): ?>
              <tr>
                <td style="color:#94a3b8;font-weight:600;"><?= $i+1 ?></td>
                <td style="color:#0f172a;font-weight:500;"><?= e($row['area']) ?></td>
                <td style="font-weight:700;color:#0f172a;"><?= (int)$row['count'] ?></td>
                <td>
                  <div class="an-progress">
                    <span style="font-size:.72rem;color:#475569;min-width:30px;"><?= $aTotal>0 ? round((int)$row['count']/$aTotal*100,1) : 0 ?>%</span>
                    <div class="an-progress-bar"><div class="an-progress-fill" style="width:<?= $aTotal>0 ? round((int)$row['count']/$aTotal*100) : 0 ?>%;"></div></div>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

    <div class="an-card">
      <div class="an-card-hd"><span class="an-card-title">Daily Case Counts</span></div>
      <div class="an-scroll">
        <?php if (empty($dailyTable)): ?>
          <div class="an-empty">No data.</div>
        <?php else: ?>
          <table class="an-table">
            <thead style="position:sticky;top:0;background:#fff;z-index:1;">
              <tr><th>Date</th><th>Submitted</th><th>Resolved</th><th>Active</th></tr>
            </thead>
            <tbody>
              <?php foreach ($dailyTable as $row): ?>
                <tr>
                  <td style="color:#374151;"><?= e($row['date']) ?></td>
                  <td style="font-weight:600;color:#0f172a;"><?= (int)$row['submitted'] ?></td>
                  <td style="color:#16a34a;font-weight:600;"><?= (int)$row['resolved'] ?></td>
                  <td style="color:#0284c7;font-weight:600;"><?= (int)$row['active'] ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>

  </div>

  <!-- Hazard summary table -->
  <div class="an-card" style="margin-bottom:8px;">
    <div class="an-card-hd">
      <span class="an-card-title">Incident Summary by Hazard</span>
    </div>
    <?php if (empty($hazardSummary)): ?>
      <div class="an-empty">No data for selected filters.</div>
    <?php else: ?>
      <div style="overflow-x:auto;">
        <table class="an-table">
          <thead>
            <tr>
              <th>Hazard</th><th>Total</th>
              <th style="color:#dc2626;">Critical</th>
              <th style="color:#ea580c;">High</th>
              <th style="color:#d97706;">Moderate</th>
              <th style="color:#16a34a;">Low</th>
              <th>Resolved</th><th>Avg Resolve</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($hazardSummary as $row): ?>
              <tr>
                <td style="font-weight:600;color:#0f172a;text-transform:capitalize;"><?= e(str_replace('_',' ',$row['hazard_type'])) ?></td>
                <td style="font-weight:800;color:#0f172a;font-size:.875rem;"><?= (int)$row['total'] ?></td>
                <td style="color:#dc2626;font-weight:600;"><?= (int)$row['critical_count'] ?></td>
                <td style="color:#ea580c;font-weight:600;"><?= (int)$row['high_count'] ?></td>
                <td style="color:#d97706;font-weight:600;"><?= (int)$row['moderate_count'] ?></td>
                <td style="color:#16a34a;font-weight:600;"><?= (int)$row['low_count'] ?></td>
                <td style="color:#475569;"><?= (int)$row['resolved_count'] ?></td>
                <td style="color:#64748b;"><?= $fmtMins(isset($row['avg_resolve_minutes']) && $row['avg_resolve_minutes'] !== null ? (int)$row['avg_resolve_minutes'] : null) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

  <?php endif; /* hasAnyData */ ?>

</div><!-- /an-wrap -->

<?php
$extraScripts = $extraScripts ?? '';
ob_start();
?>
<script>
(function () {
  var daily  = <?= json_encode($chartDaily) ?>;
  var hazard = <?= json_encode($chartHazard) ?>;
  var triage = <?= json_encode($chartTriage) ?>;
  var status = <?= json_encode($chartStatus) ?>;

  var TC = { critical:'#dc2626', high:'#ea580c', moderate:'#d97706', low:'#16a34a', pending_reassessment:'#334155' };
  var grid = { borderColor:'#eef2f6', strokeDashArray:4 };

  if (daily.dates && daily.dates.length > 0) {
    // Show at most 15 tick labels regardless of range length
    var tickAmt = Math.min(daily.dates.length, 15);
    new ApexCharts(document.querySelector('#chart-daily'), {
      series: [{ name:'Incidents', data: daily.counts }],
      chart: { type:'area', height:260, toolbar:{show:false}, zoom:{enabled:false} },
      dataLabels: { enabled:false },
      stroke: { curve:'smooth', width:2 },
      fill: { type:'gradient', gradient:{ shadeIntensity:1, opacityFrom:.3, opacityTo:.03 } },
      colors: ['#dc2626'],
      xaxis: {
        categories: daily.dates,
        tickAmount: tickAmt,
        axisBorder:{show:false}, axisTicks:{show:false},
        labels:{ rotate:-30, style:{fontSize:'10px'}, formatter:function(v){
          if(!v)return''; var d=new Date(v+'T00:00:00'); return (d.getMonth()+1)+'/'+d.getDate();
        }}
      },
      yaxis: { labels:{ formatter: v => Math.round(v) }, min:0 },
      grid: grid,
      tooltip: {
        x: { formatter: function(val, opts){
          var dt = daily.dates[opts.dataPointIndex];
          if(!dt) return val;
          var d = new Date(dt+'T00:00:00');
          return d.toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'});
        }},
        y: { formatter: v => v+' incident(s)' }
      }
    }).render();
  }

  if (hazard.labels && hazard.labels.length > 0) {
    new ApexCharts(document.querySelector('#chart-hazard'), {
      series: [{ name:'Reports', data: hazard.counts }],
      chart: { type:'bar', height:260, toolbar:{show:false} },
      plotOptions: { bar:{ borderRadius:3, columnWidth:'55%' } },
      dataLabels: { enabled:false },
      colors: ['#dc2626'],
      xaxis: { categories: hazard.labels, axisBorder:{show:false}, axisTicks:{show:false} },
      yaxis: { labels:{ formatter: v => Math.round(v) } },
      grid: grid
    }).render();
  }

  if (triage.labels && triage.labels.length > 0) {
    new ApexCharts(document.querySelector('#chart-triage'), {
      series: triage.counts,
      labels: triage.labels.map(l => l.replace(/_/g,' ')),
      chart: { type:'donut', height:260 },
      colors: triage.labels.map(l => TC[l] || '#6b7280'),
      legend: { position:'bottom', fontSize:'11px' },
      dataLabels: { enabled:true, formatter: val => Math.round(val)+'%' },
      plotOptions: { pie:{ donut:{ size:'58%' } } }
    }).render();
  }

  if (status.labels && status.labels.length > 0) {
    new ApexCharts(document.querySelector('#chart-status'), {
      series: [{ name:'Count', data: status.counts }],
      chart: { type:'bar', height:260, toolbar:{show:false} },
      plotOptions: { bar:{ borderRadius:3, horizontal:true } },
      dataLabels: { enabled:true, style:{fontSize:'11px'} },
      colors: ['#3b82f6'],
      xaxis: { categories: status.labels.map(l => l.replace(/_/g,' ')), axisBorder:{show:false} },
      grid: grid
    }).render();
  }
})();

// Export button — reads live filter form values so the CSV always matches what's on screen
document.getElementById('btn-export-csv').addEventListener('click', function() {
  var form = document.querySelector('form[action]');
  if (!form) { window.location = '<?= base_url('analytics/export') ?>'; return; }
  var params = new URLSearchParams();
  ['date_from','date_to','hazard_type','status','triage_level','barangay_id'].forEach(function(name) {
    var el = form.querySelector('[name="' + name + '"]');
    if (el && el.value) params.set(name, el.value);
  });
  var qs = params.toString();
  window.location = '<?= base_url('analytics/export') ?>' + (qs ? '?' + qs : '');
});
</script>
<?php $extraScripts .= ob_get_clean(); ?>
