<?php
$incidents  = $incidents  ?? [];
$pagination = $pagination ?? ['total'=>0,'page'=>1,'per_page'=>15,'total_pages'=>1];
$filters    = $filters    ?? [];
$flash      = $flash      ?? null;
$summary    = $summary    ?? ['total'=>0,'resolved'=>0,'active'=>0,'pending'=>0,'top_hazard'=>null,'top_area'=>null,'hazard_breakdown'=>[]];

$total      = (int)($pagination['total']       ?? 0);
$page       = (int)($pagination['page']        ?? 1);
$perPage    = (int)($pagination['per_page']    ?? 15);
$totalPages = (int)($pagination['total_pages'] ?? 1);
$from       = $total > 0 ? ($page - 1) * $perPage + 1 : 0;
$to         = min($total, $page * $perPage);

$hazardMeta = [
    'fire'       => ['bg'=>'#fef2f2','color'=>'#dc2626','init'=>'F','label'=>'Fire'],
    'flood'      => ['bg'=>'#eff6ff','color'=>'#2563eb','init'=>'W','label'=>'Flood'],
    'earthquake' => ['bg'=>'#fffbeb','color'=>'#d97706','init'=>'E','label'=>'Earthquake'],
    'medical'    => ['bg'=>'#fdf4ff','color'=>'#9333ea','init'=>'M','label'=>'Medical'],
    'accident'   => ['bg'=>'#fff7ed','color'=>'#ea580c','init'=>'A','label'=>'Accident'],
    'evacuation' => ['bg'=>'#f0fdf4','color'=>'#16a34a','init'=>'V','label'=>'Evacuation'],
    'security'   => ['bg'=>'#eef2ff','color'=>'#4f46e5','init'=>'S','label'=>'Security'],
    'other'      => ['bg'=>'#f8fafc','color'=>'#64748b','init'=>'O','label'=>'Other'],
];
$triageMeta = [
    'critical'             => ['bg'=>'#fef2f2','color'=>'#dc2626','border'=>'#fecaca','bar'=>'#dc2626','label'=>'Critical'],
    'high'                 => ['bg'=>'#fff7ed','color'=>'#ea580c','border'=>'#fed7aa','bar'=>'#ea580c','label'=>'High'],
    'moderate'             => ['bg'=>'#fffbeb','color'=>'#d97706','border'=>'#fde68a','bar'=>'#d97706','label'=>'Moderate'],
    'low'                  => ['bg'=>'#f0fdf4','color'=>'#16a34a','border'=>'#bbf7d0','bar'=>'#16a34a','label'=>'Low'],
    'pending_reassessment' => ['bg'=>'#f1f5f9','color'=>'#475569','border'=>'#cbd5e1','bar'=>'#94a3b8','label'=>'Pending'],
];
$statusMeta = [
    'submitted'    => ['bg'=>'#fff7ed','color'=>'#ea580c','border'=>'#fed7aa','label'=>'For Verification','phase'=>'Verification'],
    'verified'     => ['bg'=>'#eff6ff','color'=>'#2563eb','border'=>'#bfdbfe','label'=>'Verified','phase'=>'Verification'],
    'rejected'     => ['bg'=>'#fef2f2','color'=>'#dc2626','border'=>'#fecaca','label'=>'Rejected','phase'=>'Verification'],
    'duplicate'    => ['bg'=>'#f8fafc','color'=>'#64748b','border'=>'#e2e8f0','label'=>'Duplicate','phase'=>'Verification'],
    'triaged'      => ['bg'=>'#fdf4ff','color'=>'#9333ea','border'=>'#e9d5ff','label'=>'Triaged','phase'=>'Verification'],
    'assigned'     => ['bg'=>'#eef2ff','color'=>'#4f46e5','border'=>'#c7d2fe','label'=>'Assigned','phase'=>'Response'],
    'acknowledged' => ['bg'=>'#ecfeff','color'=>'#0891b2','border'=>'#a5f3fc','label'=>'En Route','phase'=>'Response'],
    'on_scene'     => ['bg'=>'#f0fdfa','color'=>'#0d9488','border'=>'#99f6e4','label'=>'On Scene','phase'=>'Response'],
    'in_progress'  => ['bg'=>'#fffbeb','color'=>'#d97706','border'=>'#fde68a','label'=>'In Progress','phase'=>'Response'],
    'resolved'     => ['bg'=>'#f0fdf4','color'=>'#16a34a','border'=>'#bbf7d0','label'=>'Resolved','phase'=>'Complete'],
    'closed'       => ['bg'=>'#f8fafc','color'=>'#334155','border'=>'#cbd5e1','label'=>'Closed','phase'=>'Complete'],
];

$buildQuery = function(array $over) use ($filters, $page): string {
    $base = array_filter($filters, fn($v) => $v !== '' && $v !== null);
    return http_build_query(array_merge($base, $over));
};
$hasFilters  = !empty(array_filter($filters, fn($v) => $v !== '' && $v !== null));
$resolveRate = $summary['total'] > 0 ? round($summary['resolved'] / $summary['total'] * 100) : 0;
?>
<style>
/* ═══ All Reports page ═══════════════════════════════════════════════════ */
*,*::before,*::after { box-sizing:border-box; }
.rp { padding:24px 28px 40px;overflow-x:hidden;min-width:0; }
@media(max-width:720px){ .rp { padding:14px 12px 32px; } }

/* Page header ─────────────────────────────────────────────────────────── */
.rp-head {
  display:flex;align-items:center;justify-content:space-between;
  flex-wrap:wrap;gap:12px;margin-bottom:22px;
}
.rp-title { font-size:1.35rem;font-weight:800;color:#0f172a;letter-spacing:-.03em;margin:0 0 2px; }
.rp-sub   { font-size:.78rem;color:#64748b;margin:0; }
.rp-add-btn {
  display:inline-flex;align-items:center;gap:7px;
  height:38px;padding:0 20px;border-radius:8px;
  background:#dc2626;color:#fff;font-size:.825rem;font-weight:700;
  text-decoration:none;white-space:nowrap;
  box-shadow:0 2px 8px rgba(220,38,38,.25);
  transition:background .12s,box-shadow .12s;
}
.rp-add-btn:hover { background:#b91c1c;box-shadow:0 4px 14px rgba(220,38,38,.35);color:#fff; }
@media(max-width:480px){
  .rp-add-btn { height:36px;padding:0 14px;font-size:.78rem; }
}

/* KPI cards ───────────────────────────────────────────────────────────── */
.rp-kpi {
  display:grid;
  grid-template-columns:repeat(4,1fr);
  gap:12px;
  margin-bottom:14px;
  width:100%;
}
@media(max-width:860px){ .rp-kpi { grid-template-columns:repeat(2,1fr); } }
@media(max-width:420px){ .rp-kpi { grid-template-columns:1fr 1fr;gap:8px; } }

.rp-kpi-card {
  display:block;text-decoration:none;
  background:#fff;border:1px solid #e2e8f0;border-radius:10px;
  padding:14px 16px 12px;
  border-top:3px solid var(--kc);
  transition:box-shadow .12s,transform .1s;
}
.rp-kpi-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.07);transform:translateY(-1px); }
.rp-kpi-lbl  { font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:6px; }
.rp-kpi-val  { font-size:1.75rem;font-weight:800;color:var(--kc);letter-spacing:-.04em;line-height:1;margin-bottom:4px; }
.rp-kpi-sub  { font-size:.7rem;color:#94a3b8;line-height:1.3; }

/* Insights bar ────────────────────────────────────────────────────────── */
.rp-insights {
  background:#fff;border:1px solid #e2e8f0;border-radius:10px;
  padding:12px 14px;margin-bottom:12px;
  overflow:hidden;
}
.rp-insight-row {
  display:flex;align-items:center;gap:12px;flex-wrap:wrap;
}
.rp-insight-sep { width:1px;height:18px;background:#e2e8f0;flex-shrink:0; }
.rp-insight-pill {
  display:flex;align-items:center;gap:6px;
  font-size:.78rem;flex-wrap:wrap;
}
.rp-insight-lbl {
  font-size:.62rem;font-weight:700;text-transform:uppercase;
  letter-spacing:.06em;color:#94a3b8;white-space:nowrap;
}
.rp-av {
  display:inline-flex;align-items:center;justify-content:center;
  width:22px;height:22px;border-radius:5px;
  font-size:.62rem;font-weight:800;flex-shrink:0;
}

/* Hazard chips ────────────────────────────────────────────────────────── */
.rp-haz-row {
  display:flex;align-items:center;gap:5px;flex-wrap:wrap;
  padding-top:9px;margin-top:9px;
  border-top:1px solid #f1f5f9;
}
.rp-haz-chip {
  display:inline-flex;align-items:center;gap:3px;
  padding:2px 8px;border-radius:5px;
  font-size:.68rem;font-weight:700;
  text-decoration:none;transition:opacity .1s;
  white-space:nowrap;
}
.rp-haz-chip:hover { opacity:.75; }
.rp-haz-lbl {
  font-size:.62rem;font-weight:700;text-transform:uppercase;
  letter-spacing:.06em;color:#94a3b8;white-space:nowrap;
  margin-right:2px;flex-shrink:0;
}

/* Filter bar ──────────────────────────────────────────────────────────── */
.rp-filter-bar {
  background:#fff;border:1px solid #e2e8f0;border-radius:10px;
  padding:10px 12px;margin-bottom:12px;
  display:flex;align-items:center;flex-wrap:wrap;gap:6px;
}
.rp-filter-bar input,
.rp-filter-bar select {
  height:32px;border:1.5px solid #e2e8f0;border-radius:7px;
  padding:0 9px;font-size:.78rem;color:#0f172a;background:#f8fafc;
  outline:none;font-family:inherit;transition:border-color .12s;
  flex-shrink:1;min-width:0;
}
.rp-filter-bar input:focus,
.rp-filter-bar select:focus { border-color:#dc2626;background:#fff; }
.rp-filter-bar input[type=search] { width:200px;max-width:100%; }
.rp-filter-bar select             { min-width:115px;flex-shrink:1; }
.rp-filter-bar input[type=date]   { width:118px;max-width:45%; }
.rp-filter-sep { color:#cbd5e1;font-size:.8rem;flex-shrink:0; }

@media(max-width:480px){
  .rp-filter-bar { gap:5px; }
  .rp-filter-bar input[type=search] { width:100%;max-width:100%; }
  .rp-filter-bar select             { min-width:48%;flex:1; }
  .rp-filter-bar input[type=date]   { width:45%; }
}
.rp-btn-filter {
  height:32px;padding:0 14px;
  background:#0f172a;color:#fff;border:none;border-radius:7px;
  font-size:.78rem;font-weight:600;cursor:pointer;font-family:inherit;
  transition:background .12s;white-space:nowrap;
}
.rp-btn-filter:hover { background:#1e293b; }
.rp-btn-clear {
  height:32px;padding:0 12px;
  background:#fff;color:#64748b;
  border:1.5px solid #e2e8f0;border-radius:7px;
  font-size:.78rem;font-weight:600;text-decoration:none;
  display:inline-flex;align-items:center;white-space:nowrap;
  transition:border-color .12s,color .12s;
}
.rp-btn-clear:hover { border-color:#94a3b8;color:#0f172a; }
.rp-filter-count { margin-left:auto;font-size:.73rem;color:#94a3b8;white-space:nowrap; }

/* Table card ──────────────────────────────────────────────────────────── */
.rp-card { background:#fff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden; }

.rp-table { width:100%;border-collapse:collapse;table-layout:fixed; }
.rp-table thead tr { background:#f8fafc;border-bottom:2px solid #e2e8f0; }
.rp-table thead th {
  padding:9px 13px;
  font-size:.6rem;font-weight:700;text-transform:uppercase;
  letter-spacing:.07em;color:#94a3b8;text-align:left;white-space:nowrap;
}
.rp-table tbody tr {
  border-bottom:1px solid #f1f5f9;cursor:pointer;
  transition:background .07s;
}
.rp-table tbody tr:last-child { border-bottom:none; }
.rp-table tbody tr:hover { background:#f8fafc; }
.rp-table tbody td { padding:11px 13px;vertical-align:middle;font-size:.8rem;color:#374151; }

.rp-col-bar { width:4px;padding:0!important; }
.rp-col-rep { width:170px; }
.rp-col-haz { width:116px; }
.rp-col-pri { width:118px; }
.rp-col-sta { width:138px; }
.rp-col-dt  { width:148px; }
.rp-col-who { width:140px; }

/* Badges ──────────────────────────────────────────────────────────────── */
.rp-badge {
  display:inline-block;padding:3px 8px;border-radius:5px;
  font-size:.67rem;font-weight:700;white-space:nowrap;
}

/* Pagination ──────────────────────────────────────────────────────────── */
.rp-pager {
  display:flex;align-items:center;justify-content:space-between;
  flex-wrap:wrap;gap:10px;padding:11px 16px;
  border-top:1px solid #f1f5f9;
}
.rp-pinfo { font-size:.77rem;color:#64748b; }
.rp-pages { display:flex;align-items:center;gap:4px; }
.rp-pg {
  display:inline-flex;align-items:center;justify-content:center;
  min-width:30px;height:30px;padding:0 6px;border-radius:6px;
  font-size:.77rem;font-weight:600;
  border:1.5px solid #e2e8f0;background:#fff;color:#374151;
  text-decoration:none;transition:all .1s;
}
.rp-pg:hover           { background:#f8fafc;border-color:#94a3b8;color:#0f172a; }
.rp-pg.rp-pg-active    { background:#0f172a;border-color:#0f172a;color:#fff; }
.rp-pg.rp-pg-disabled  { opacity:.3;pointer-events:none; }
.rp-pg-ellipsis        { width:28px;text-align:center;font-size:.8rem;color:#94a3b8; }

/* Empty state ─────────────────────────────────────────────────────────── */
.rp-empty {
  text-align:center;padding:56px 20px;
}

/* Mobile cards ────────────────────────────────────────────────────────── */
.rp-mob-card {
  background:#fff;border:1px solid #e2e8f0;border-radius:10px;
  margin-bottom:9px;overflow:hidden;cursor:pointer;
  transition:box-shadow .15s,transform .1s;
}
.rp-mob-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.08);transform:translateY(-1px); }
.rp-mob-body { padding:12px 13px; }

/* Show/hide table vs cards */
@media(max-width:720px) {
  .rp-desktop-only { display:none!important; }
  .rp-mobile-only  { display:block!important; }
}
@media(min-width:721px) {
  .rp-desktop-only { display:block!important; }
  .rp-mobile-only  { display:none!important; }
}
.rp-mobile-only { display:none; }

/* Flash */
.rp-flash {
  display:flex;align-items:center;gap:8px;
  padding:10px 14px;border-radius:8px;margin-bottom:16px;font-size:.8rem;
}
</style>

<div class="rp">

  <?php if ($flash): ?>
    <?php $isOk = $flash['type'] === 'success'; ?>
    <div class="rp-flash" style="<?= $isOk ? 'background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;' : 'background:#fef2f2;border:1px solid #fecaca;color:#991b1b;' ?>">
      <?php if ($isOk): ?>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
      <?php else: ?>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <?php endif; ?>
      <?= e($flash['message']) ?>
    </div>
  <?php endif; ?>

  <!-- ── Header ─────────────────────────────────────────────────────── -->
  <div class="rp-head">
    <div>
      <h1 class="rp-title">All Reports</h1>
      <p class="rp-sub">Monitor and manage all submitted barangay incident reports</p>
    </div>
    <a href="<?= base_url('reports/new') ?>" class="rp-add-btn">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Add Manual Report
    </a>
  </div>

  <!-- ── KPI cards ──────────────────────────────────────────────────── -->
  <div class="rp-kpi">
    <a class="rp-kpi-card" style="--kc:#2563eb;" href="<?= base_url('reports') ?>">
      <div class="rp-kpi-lbl">Total Reports</div>
      <div class="rp-kpi-val"><?= $summary['total'] ?></div>
      <div class="rp-kpi-sub">All time</div>
    </a>
    <a class="rp-kpi-card" style="--kc:#16a34a;" href="<?= base_url('reports?status=resolved') ?>">
      <div class="rp-kpi-lbl">Resolved</div>
      <div class="rp-kpi-val"><?= $summary['resolved'] ?></div>
      <div class="rp-kpi-sub"><?= $resolveRate ?>% resolution rate</div>
    </a>
    <a class="rp-kpi-card" style="--kc:#ea580c;" href="<?= base_url('reports?status=in_progress') ?>">
      <div class="rp-kpi-lbl">Active</div>
      <div class="rp-kpi-val"><?= $summary['active'] ?></div>
      <div class="rp-kpi-sub">Being responded to</div>
    </a>
    <a class="rp-kpi-card" style="--kc:#dc2626;" href="<?= base_url('verification') ?>">
      <div class="rp-kpi-lbl">Needs Verification</div>
      <div class="rp-kpi-val"><?= $summary['pending'] ?></div>
      <div class="rp-kpi-sub">Awaiting review</div>
    </a>
  </div>

  <!-- ── Insights bar ───────────────────────────────────────────────── -->
  <?php if (!empty($summary['top_hazard']) || !empty($summary['hazard_breakdown'])): ?>
  <div class="rp-insights">
    <div class="rp-insight-row">
      <?php if (!empty($summary['top_hazard'])): ?>
        <?php $thm = $hazardMeta[$summary['top_hazard']['hazard_type']] ?? $hazardMeta['other']; ?>
        <div class="rp-insight-pill">
          <span class="rp-insight-lbl">Top Hazard</span>
          <span class="rp-av" style="background:<?= $thm['bg'] ?>;color:<?= $thm['color'] ?>;"><?= $thm['init'] ?></span>
          <span style="font-weight:700;color:#0f172a;text-transform:capitalize;"><?= e($summary['top_hazard']['hazard_type']) ?></span>
          <span style="color:#94a3b8;font-size:.7rem;"><?= (int)$summary['top_hazard']['cnt'] ?> reports</span>
        </div>
      <?php endif; ?>
      <?php if (!empty($summary['top_area']) && !empty($summary['top_area']['area'])): ?>
        <div class="rp-insight-sep"></div>
        <div class="rp-insight-pill">
          <span class="rp-insight-lbl">Most Affected</span>
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          <span style="font-weight:700;color:#0f172a;"><?= e($summary['top_area']['area']) ?></span>
          <span style="color:#94a3b8;font-size:.7rem;"><?= (int)$summary['top_area']['cnt'] ?> reports</span>
        </div>
      <?php endif; ?>
    </div>
    <?php if (!empty($summary['hazard_breakdown'])): ?>
    <div class="rp-haz-row">
      <span class="rp-haz-lbl">By Hazard</span>
      <?php foreach ($summary['hazard_breakdown'] as $hb):
        $hm = $hazardMeta[$hb['hazard_type']] ?? $hazardMeta['other'];
      ?>
        <a class="rp-haz-chip"
           style="background:<?= $hm['bg'] ?>;color:<?= $hm['color'] ?>;"
           href="<?= base_url('reports?hazard_type=' . urlencode($hb['hazard_type'])) ?>">
          <strong><?= $hm['init'] ?></strong><?= ucfirst($hb['hazard_type']) ?> · <?= (int)$hb['cnt'] ?>
        </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- ── Filter bar ─────────────────────────────────────────────────── -->
  <form method="get" action="<?= base_url('reports') ?>" class="rp-filter-bar">
    <input type="search" name="search"
      placeholder="Search code, address, reporter…"
      value="<?= e($filters['search'] ?? '') ?>">

    <select name="status">
      <option value="">All Statuses</option>
      <?php foreach ($statusMeta as $k => $v): ?>
        <option value="<?= $k ?>" <?= ($filters['status'] ?? '') === $k ? 'selected' : '' ?>><?= $v['label'] ?></option>
      <?php endforeach; ?>
    </select>

    <select name="triage_level">
      <option value="">All Priorities</option>
      <?php foreach ($triageMeta as $k => $v): ?>
        <option value="<?= $k ?>" <?= ($filters['triage_level'] ?? '') === $k ? 'selected' : '' ?>><?= $v['label'] ?></option>
      <?php endforeach; ?>
    </select>

    <select name="is_emergency">
      <option value="">All Types</option>
      <option value="1" <?= ($filters['is_emergency'] ?? '') == '1' ? 'selected' : '' ?>>Emergency</option>
      <option value="0" <?= ($filters['is_emergency'] ?? '') == '0' ? 'selected' : '' ?>>Non-Emergency</option>
    </select>

    <div style="display:flex;align-items:center;gap:5px;flex-shrink:0;flex-wrap:wrap;">
      <input type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>" title="From" style="width:130px;max-width:calc(50% - 10px);">
      <span class="rp-filter-sep" style="flex-shrink:0;">–</span>
      <input type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>" title="To" style="width:130px;max-width:calc(50% - 10px);">
    </div>

    <button type="submit" class="rp-btn-filter">Filter</button>

    <?php if ($hasFilters): ?>
      <a href="<?= base_url('reports') ?>" class="rp-btn-clear">Clear</a>
    <?php endif; ?>

    <?php if ($total > 0): ?>
      <span class="rp-filter-count">Showing <?= $from ?>–<?= $to ?> of <?= $total ?></span>
    <?php endif; ?>
  </form>

  <!-- ── Main card ──────────────────────────────────────────────────── -->
  <div class="rp-card">

    <?php if (empty($incidents)): ?>
      <div class="rp-empty">
        <div style="width:52px;height:52px;border-radius:12px;background:#f8fafc;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
        <div style="font-size:.9375rem;font-weight:700;color:#0f172a;margin-bottom:5px;">
          <?= $hasFilters ? 'No reports match your filters' : 'No reports yet' ?>
        </div>
        <div style="font-size:.8rem;color:#64748b;margin-bottom:16px;">
          <?= $hasFilters ? 'Try adjusting or clearing your filters.' : 'Create the first incident report to get started.' ?>
        </div>
        <?php if ($hasFilters): ?>
          <a href="<?= base_url('reports') ?>" style="display:inline-flex;height:32px;padding:0 14px;align-items:center;background:#0f172a;color:#fff;border-radius:7px;font-size:.78rem;font-weight:600;text-decoration:none;">Clear filters</a>
        <?php endif; ?>
      </div>

    <?php else: ?>

      <!-- ── Desktop table ─────────────────────────────────────────── -->
      <div class="rp-desktop-only">
        <table class="rp-table">
          <thead>
            <tr>
              <th class="rp-col-bar"></th>
              <th class="rp-col-rep">Report</th>
              <th class="rp-col-haz">Hazard</th>
              <th class="rp-col-pri">Priority</th>
              <th class="rp-col-sta">Status</th>
              <th class="rp-col-dt">
                Submitted&nbsp;<svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:-1px;"><path d="M12 20V4M5 13l7 7 7-7"/></svg>
              </th>
              <th class="rp-col-who">Reporter</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($incidents as $r):
              $level  = $r['triage_level'] ?? 'low';
              $status = $r['status']       ?? 'submitted';
              $tm = $triageMeta[$level]  ?? $triageMeta['low'];
              $sm = $statusMeta[$status] ?? ['bg'=>'#f8fafc','color'=>'#64748b','border'=>'#e2e8f0','label'=>ucwords(str_replace('_',' ',$status)),'phase'=>''];
              $hm = $hazardMeta[$r['hazard_type'] ?? 'other'] ?? $hazardMeta['other'];
              $ts = strtotime($r['created_at'] ?? '');
            ?>
            <tr onclick="location.href='<?= base_url('reports/' . e($r['code'])) ?>'">

              <!-- Triage color strip -->
              <td class="rp-col-bar" style="padding:0!important;">
                <div style="width:4px;min-height:50px;background:<?= $tm['bar'] ?>;"></div>
              </td>

              <!-- Report code + address -->
              <td class="rp-col-rep">
                <div style="font-weight:800;font-family:monospace;font-size:.82rem;color:#0f172a;letter-spacing:-.01em;"><?= e($r['code']) ?></div>
                <div style="font-size:.7rem;color:#94a3b8;margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:154px;" title="<?= e($r['address'] ?? '') ?>">
                  <?= e($r['address'] ?: '—') ?>
                </div>
              </td>

              <!-- Hazard -->
              <td class="rp-col-haz">
                <div style="display:flex;align-items:center;gap:7px;">
                  <span class="rp-av" style="background:<?= $hm['bg'] ?>;color:<?= $hm['color'] ?>;"><?= $hm['init'] ?></span>
                  <span style="font-size:.78rem;font-weight:600;color:#374151;text-transform:capitalize;"><?= e($r['hazard_type'] ?? 'other') ?></span>
                </div>
              </td>

              <!-- Priority -->
              <td class="rp-col-pri">
                <span class="rp-badge" style="background:<?= $tm['bg'] ?>;color:<?= $tm['color'] ?>;border:1px solid <?= $tm['border'] ?>;"><?= $tm['label'] ?></span>
                <div style="font-size:.65rem;color:#94a3b8;margin-top:3px;"><?= (int)($r['triage_score'] ?? 0) ?>/16 pts</div>
              </td>

              <!-- Status -->
              <td class="rp-col-sta">
                <span class="rp-badge" style="background:<?= $sm['bg'] ?>;color:<?= $sm['color'] ?>;border:1px solid <?= $sm['border'] ?>;"><?= $sm['label'] ?></span>
                <?php if (!empty($sm['phase'])): ?>
                  <div style="font-size:.59rem;color:#94a3b8;font-weight:600;margin-top:3px;text-transform:uppercase;letter-spacing:.05em;"><?= $sm['phase'] ?></div>
                <?php endif; ?>
              </td>

              <!-- Date/Time -->
              <td class="rp-col-dt">
                <div style="font-size:.78rem;font-weight:600;color:#0f172a;"><?= $ts ? date('M j, Y', $ts) : '—' ?></div>
                <div style="font-size:.7rem;color:#94a3b8;margin-top:1px;"><?= $ts ? date('g:i A', $ts) : '' ?></div>
              </td>

              <!-- Reporter -->
              <td class="rp-col-who">
                <div style="display:flex;align-items:center;gap:7px;">
                  <div style="width:24px;height:24px;border-radius:50%;background:#f1f5f9;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:800;color:#475569;flex-shrink:0;">
                    <?= strtoupper(substr($r['reporter_name'] ?? 'A', 0, 1)) ?>
                  </div>
                  <span style="font-size:.78rem;font-weight:600;color:#0f172a;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:100px;"><?= e($r['reporter_name'] ?? 'Anonymous') ?></span>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div><!-- /desktop -->

      <!-- ── Mobile cards ──────────────────────────────────────────── -->
      <div class="rp-mobile-only" style="padding:10px;">
        <?php foreach ($incidents as $r):
          $level  = $r['triage_level'] ?? 'low';
          $status = $r['status']       ?? 'submitted';
          $tm = $triageMeta[$level]  ?? $triageMeta['low'];
          $sm = $statusMeta[$status] ?? ['bg'=>'#f8fafc','color'=>'#64748b','border'=>'#e2e8f0','label'=>ucwords(str_replace('_',' ',$status)),'phase'=>''];
          $hm = $hazardMeta[$r['hazard_type'] ?? 'other'] ?? $hazardMeta['other'];
          $ts = strtotime($r['created_at'] ?? '');
        ?>
        <div class="rp-mob-card" onclick="location.href='<?= base_url('reports/' . e($r['code'])) ?>'">
          <!-- Triage strip -->
          <div style="height:3px;background:<?= $tm['bar'] ?>;"></div>

          <div class="rp-mob-body">
            <!-- Row 1: hazard icon + code + status badge -->
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:8px;">
              <div style="display:flex;align-items:center;gap:8px;">
                <span class="rp-av" style="background:<?= $hm['bg'] ?>;color:<?= $hm['color'] ?>;width:30px;height:30px;border-radius:7px;font-size:.7rem;"><?= $hm['init'] ?></span>
                <div>
                  <div style="font-weight:800;font-family:monospace;font-size:.88rem;color:#0f172a;line-height:1.2;"><?= e($r['code']) ?></div>
                  <div style="font-size:.7rem;color:#94a3b8;text-transform:capitalize;"><?= e($r['hazard_type'] ?? '') ?></div>
                </div>
              </div>
              <span class="rp-badge" style="background:<?= $sm['bg'] ?>;color:<?= $sm['color'] ?>;border:1px solid <?= $sm['border'] ?>;flex-shrink:0;margin-top:1px;"><?= $sm['label'] ?></span>
            </div>

            <!-- Address -->
            <?php if (!empty($r['address'])): ?>
            <div style="display:flex;align-items:center;gap:5px;font-size:.72rem;color:#64748b;margin-bottom:9px;">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
              <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($r['address']) ?></span>
            </div>
            <?php endif; ?>

            <!-- Footer: priority + reporter + date -->
            <div style="display:flex;align-items:center;justify-content:space-between;padding-top:8px;border-top:1px solid #f1f5f9;gap:6px;flex-wrap:wrap;">
              <span class="rp-badge" style="background:<?= $tm['bg'] ?>;color:<?= $tm['color'] ?>;border:1px solid <?= $tm['border'] ?>;"><?= $tm['label'] ?></span>
              <div style="text-align:right;min-width:0;">
                <div style="font-size:.75rem;font-weight:600;color:#0f172a;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= e($r['reporter_name'] ?? 'Anonymous') ?></div>
                <div style="font-size:.68rem;color:#94a3b8;"><?= $ts ? date('M j, Y · g:i A', $ts) : '—' ?></div>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div><!-- /mobile -->

      <!-- ── Pagination ─────────────────────────────────────────────── -->
      <?php if ($totalPages > 1): ?>
      <div class="rp-pager">
        <div class="rp-pinfo">
          Showing <strong><?= $from ?>–<?= $to ?></strong> of <strong><?= $total ?></strong> reports
        </div>
        <div class="rp-pages">
          <!-- Prev -->
          <?php if ($page > 1): ?>
            <a href="?<?= $buildQuery(['page'=>$page-1]) ?>" class="rp-pg">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
            </a>
          <?php else: ?>
            <span class="rp-pg rp-pg-disabled"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg></span>
          <?php endif; ?>

          <?php
          $s = max(1, $page - 2);
          $e = min($totalPages, $page + 2);
          if ($s > 1): ?>
            <a href="?<?= $buildQuery(['page'=>1]) ?>" class="rp-pg">1</a>
            <?php if ($s > 2): ?><span class="rp-pg-ellipsis">…</span><?php endif; ?>
          <?php endif; ?>

          <?php for ($i = $s; $i <= $e; $i++): ?>
            <?php if ($i === $page): ?>
              <span class="rp-pg rp-pg-active"><?= $i ?></span>
            <?php else: ?>
              <a href="?<?= $buildQuery(['page'=>$i]) ?>" class="rp-pg"><?= $i ?></a>
            <?php endif; ?>
          <?php endfor; ?>

          <?php if ($e < $totalPages): ?>
            <?php if ($e < $totalPages - 1): ?><span class="rp-pg-ellipsis">…</span><?php endif; ?>
            <a href="?<?= $buildQuery(['page'=>$totalPages]) ?>" class="rp-pg"><?= $totalPages ?></a>
          <?php endif; ?>

          <!-- Next -->
          <?php if ($page < $totalPages): ?>
            <a href="?<?= $buildQuery(['page'=>$page+1]) ?>" class="rp-pg">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
            </a>
          <?php else: ?>
            <span class="rp-pg rp-pg-disabled"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg></span>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

    <?php endif; ?>

  </div><!-- /rp-card -->
</div><!-- /rp -->
