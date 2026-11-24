<?php
$incidents   = $incidents   ?? [];
$pagination  = $pagination  ?? ['total'=>0,'page'=>1,'per_page'=>20,'total_pages'=>1];
$pendingCount= $pendingCount ?? 0;
$counts      = $counts      ?? [];
$filters     = $filters     ?? [];
$flash       = $flash       ?? null;

$hazardMeta = [
    'fire'       => ['bg'=>'#fef2f2','color'=>'#dc2626','init'=>'F'],
    'flood'      => ['bg'=>'#eff6ff','color'=>'#2563eb','init'=>'W'],
    'earthquake' => ['bg'=>'#fffbeb','color'=>'#d97706','init'=>'E'],
    'medical'    => ['bg'=>'#fdf4ff','color'=>'#9333ea','init'=>'M'],
    'accident'   => ['bg'=>'#fff7ed','color'=>'#ea580c','init'=>'A'],
    'evacuation' => ['bg'=>'#f0fdf4','color'=>'#16a34a','init'=>'V'],
    'security'   => ['bg'=>'#eef2ff','color'=>'#4f46e5','init'=>'S'],
    'other'      => ['bg'=>'#f8fafc','color'=>'#64748b','init'=>'O'],
];
$triageMeta = [
    'critical'             => ['bg'=>'#fef2f2','color'=>'#dc2626','border'=>'#fecaca','bar'=>'#dc2626','label'=>'Critical'],
    'high'                 => ['bg'=>'#fff7ed','color'=>'#ea580c','border'=>'#fed7aa','bar'=>'#ea580c','label'=>'High'],
    'moderate'             => ['bg'=>'#fffbeb','color'=>'#d97706','border'=>'#fde68a','bar'=>'#d97706','label'=>'Moderate'],
    'low'                  => ['bg'=>'#f0fdf4','color'=>'#16a34a','border'=>'#bbf7d0','bar'=>'#16a34a','label'=>'Low'],
    'pending_reassessment' => ['bg'=>'#f1f5f9','color'=>'#475569','border'=>'#cbd5e1','bar'=>'#94a3b8','label'=>'Needs Reassessment'],
];

$total      = (int)($pagination['total']      ?? 0);
$page       = (int)($pagination['page']       ?? 1);
$perPage    = (int)($pagination['per_page']   ?? 20);
$totalPages = (int)($pagination['total_pages']?? 1);
$from       = $total > 0 ? ($page-1)*$perPage+1 : 0;
$to         = min($total, $page*$perPage);

$critical  = $counts['critical']             ?? 0;
$needsRe   = $counts['pending_reassessment'] ?? 0;

$buildQuery = function(array $over) use ($filters): string {
    return http_build_query(array_merge(array_filter($filters), $over));
};
?>
<style>
.vq { padding:20px 24px 32px; }
.vq-head { display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:16px; }
.vq-title { font-size:1.25rem;font-weight:800;color:#0f172a;letter-spacing:-.02em;margin:0 0 2px; }
.vq-sub   { font-size:.78rem;color:#64748b;margin:0; }
.spill { display:inline-flex;align-items:center;gap:5px;padding:4px 11px;border-radius:100px;font-size:.74rem;font-weight:600;white-space:nowrap; }

/* Filter bar */
.vq-bar { display:flex;align-items:center;flex-wrap:wrap;gap:8px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;margin-bottom:14px; }
.vq-bar input,.vq-bar select { height:32px;border:1.5px solid #e2e8f0;border-radius:5px;padding:0 10px;font-size:.78rem;color:#0f172a;background:#f8fafc;outline:none;font-family:inherit;transition:border-color .12s; }
.vq-bar input:focus,.vq-bar select:focus { border-color:#dc2626;background:#fff; }
.vq-bar input[type=search],.vq-bar input[type=text] { width:170px; }
.vq-btn-filter { height:32px;padding:0 14px;background:#0f172a;color:#fff;border:none;border-radius:5px;font-size:.78rem;font-weight:600;cursor:pointer;font-family:inherit; }
.vq-btn-clear  { height:32px;padding:0 11px;background:#fff;color:#64748b;border:1.5px solid #e2e8f0;border-radius:5px;font-size:.78rem;font-weight:600;text-decoration:none;display:inline-flex;align-items:center; }
.vq-rinfo { margin-left:auto;font-size:.72rem;color:#94a3b8;white-space:nowrap; }

/* Table */
.vq-table { width:100%;border-collapse:collapse;table-layout:fixed; }
.vq-table thead tr { background:#f8fafc;border-bottom:1px solid #e2e8f0; }
.vq-table thead th {
  padding:9px 14px;font-size:.62rem;font-weight:700;
  text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;
  text-align:left;
}
.vq-table tbody tr {
  border-bottom:1px solid #f8fafc;
  transition:background .1s;cursor:pointer;
}
.vq-table tbody tr:last-child { border-bottom:none; }
.vq-table tbody tr:hover { background:#fafbfc; }
.vq-table tbody tr.pend { background:#fafafa; }
.vq-table tbody tr.pend:hover { background:#f5f5f5; }
.vq-table tbody td { padding:10px 14px;vertical-align:middle;font-size:.8125rem;color:#374151; }

/* Column widths */
.col-bar   { width:6px;  padding:0 !important; }
.col-code  { width:150px; }
.col-haz   { width:110px; }
.col-triage{ width:180px; }
.col-sub   { width:130px; }
.col-res   { width:160px; }
.col-act   { width:90px;text-align:right !important; }

/* Accent bar cell */
.vq-bar-cell { padding:0 !important;width:4px; }
.vq-accent   { display:block;width:3px;height:100%;min-height:44px;border-radius:0 2px 2px 0; }

/* Hazard avatar */
.hav { display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:5px;font-size:.6rem;font-weight:800;flex-shrink:0;vertical-align:middle; }

/* Triage badge */
.tbadge { display:inline-block;border-radius:4px;padding:2px 7px;font-size:.65rem;font-weight:700;white-space:nowrap;vertical-align:middle; }

/* Action buttons */
.vq-btn {
  display:inline-flex;align-items:center;justify-content:center;
  height:27px;padding:0 11px;border-radius:4px;
  font-size:.71rem;font-weight:700;text-decoration:none;
  border:none;cursor:pointer;white-space:nowrap;
  transition:background .1s,transform .08s;
}
.vq-btn:active { transform:scale(.97); }
.vq-btn-review   { background:#0f172a;color:#fff; }
.vq-btn-review:hover { background:#1e293b;color:#fff; }
.vq-btn-reassess { background:#fff;color:#d97706;border:1.5px solid #fde68a; }
.vq-btn-reassess:hover { background:#fffbeb; }

/* Pagination */
.vq-pager { display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;padding:11px 16px;border-top:1px solid #f1f5f9;background:#fff; }
.vq-pinfo { font-size:.78rem;color:#64748b; }
.pages { display:flex;align-items:center;gap:4px; }
.pg { display:inline-flex;align-items:center;justify-content:center;min-width:30px;height:30px;padding:0 6px;border-radius:5px;font-size:.78rem;font-weight:600;border:1.5px solid #e2e8f0;background:#fff;color:#374151;text-decoration:none;transition:all .1s; }
.pg:hover { background:#f8fafc;color:#0f172a;border-color:#cbd5e1; }
.pg.on  { background:#0f172a;border-color:#0f172a;color:#fff; }
.pg.off { opacity:.35;pointer-events:none; }
.pg-dot { width:26px;text-align:center;font-size:.78rem;color:#94a3b8; }

/* Empty */
.vq-empty { text-align:center;padding:52px 20px; }

@keyframes dp { 0%,100%{opacity:1} 50%{opacity:.4} }
</style>

<div class="vq">

  <?php if ($flash): ?>
    <div style="padding:10px 14px;border-radius:6px;margin-bottom:14px;font-size:.8125rem;<?= $flash['type']==='success'?'background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;':'background:#fef2f2;border:1px solid #fecaca;color:#991b1b;' ?>"><?= e($flash['message']) ?></div>
  <?php endif; ?>

  <!-- Header -->
  <div class="vq-head">
    <div>
      <h1 class="vq-title">Verification Queue</h1>
      <p class="vq-sub">Submitted reports awaiting review &middot; Needs-reassessment items appear first</p>
    </div>
    <div style="display:flex;align-items:center;gap:7px;flex-wrap:wrap;">
      <?php if ($critical > 0): ?>
        <span class="spill" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;">
          <span style="width:6px;height:6px;border-radius:50%;background:#dc2626;flex-shrink:0;"></span>
          <?= $critical ?> Critical
        </span>
      <?php endif; ?>
      <?php if ($needsRe > 0): ?>
        <span class="spill" style="background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;">
          <span style="width:6px;height:6px;border-radius:50%;background:#94a3b8;flex-shrink:0;animation:dp 1.4s infinite;"></span>
          <?= $needsRe ?> Needs Reassessment
        </span>
      <?php endif; ?>
      <span class="spill" style="background:#f0f9ff;color:#0284c7;border:1px solid #bae6fd;">
        <?= $pendingCount ?> total pending
      </span>
    </div>
  </div>

  <!-- Filter bar -->
  <form method="get" action="<?= base_url('verification') ?>" class="vq-bar">
    <input type="text" name="search" placeholder="Search code or name…" value="<?= e($filters['search'] ?? '') ?>">

    <select name="triage_level">
      <option value="">All triage levels</option>
      <?php foreach (['critical','high','moderate','low','pending_reassessment'] as $t): ?>
        <option value="<?= $t ?>" <?= ($filters['triage_level'] ?? '') === $t ? 'selected' : '' ?>><?= ucwords(str_replace('_',' ',$t)) ?></option>
      <?php endforeach; ?>
    </select>

    <select name="hazard_type">
      <option value="">All hazards</option>
      <?php foreach (['fire','flood','earthquake','medical','accident','evacuation','security','other'] as $h): ?>
        <option value="<?= $h ?>" <?= ($filters['hazard_type'] ?? '') === $h ? 'selected' : '' ?>><?= ucfirst($h) ?></option>
      <?php endforeach; ?>
    </select>

    <button type="submit" class="vq-btn-filter">Apply</button>
    <?php if (!empty($filters)): ?>
      <a href="<?= base_url('verification') ?>" class="vq-btn-clear">Clear</a>
    <?php endif; ?>
    <?php if ($total > 0): ?>
      <span class="vq-rinfo">Showing <?= $from ?>–<?= $to ?> of <?= $total ?></span>
    <?php endif; ?>
  </form>

  <!-- Table card -->
  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">

    <?php if (empty($incidents)): ?>
      <div class="vq-empty">
        <div style="width:48px;height:48px;border-radius:50%;background:#f0fdf4;border:1.5px solid #bbf7d0;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div style="font-size:.9375rem;font-weight:700;color:#0f172a;margin-bottom:4px;"><?= !empty($filters) ? 'No results match your filters' : 'Queue is clear' ?></div>
        <div style="font-size:.8125rem;color:#64748b;"><?= !empty($filters) ? 'Try clearing filters.' : 'No submitted reports awaiting verification.' ?></div>
      </div>

    <?php else: ?>

      <table class="vq-table">
        <thead>
          <tr>
            <th class="col-bar"></th>
            <th class="col-code">Report Code</th>
            <th class="col-haz">Hazard</th>
            <th class="col-triage">Triage Level</th>
            <th class="col-sub">Submitted</th>
            <th class="col-res">Resident</th>
            <th class="col-act" style="text-align:right;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($incidents as $r):
            $level    = $r['triage_level'] ?? 'low';
            $isPend   = $level === 'pending_reassessment';
            $tm       = $triageMeta[$level] ?? $triageMeta['low'];
            $hm       = $hazardMeta[$r['hazard_type'] ?? 'other'] ?? $hazardMeta['other'];
            $ts       = strtotime($r['created_at'] ?? '');
            $diff     = time() - $ts;
            if ($diff < 3600)      $ago = round($diff/60) . 'm ago';
            elseif ($diff < 86400) $ago = round($diff/3600) . 'h ago';
            else                   $ago = date('M j, g:i A', $ts);
          ?>
            <tr class="<?= $isPend ? 'pend' : '' ?>"
                onclick="window.location='<?= base_url('reports/' . e($r['code'])) ?>'">

              <!-- Accent bar -->
              <td class="col-bar" style="padding:0;width:4px;">
                <span style="display:block;width:4px;height:100%;min-height:48px;background:<?= $tm['bar'] ?>;"></span>
              </td>

              <!-- Code -->
              <td class="col-code">
                <span style="font-weight:700;color:#0f172a;font-family:monospace;font-size:.8rem;"><?= e($r['code']) ?></span>
              </td>

              <!-- Hazard -->
              <td class="col-haz">
                <span style="display:inline-flex;align-items:center;gap:7px;">
                  <span class="hav" style="background:<?= $hm['bg'] ?>;color:<?= $hm['color'] ?>;"><?= $hm['init'] ?></span>
                  <span style="text-transform:capitalize;font-weight:500;color:#374151;"><?= e($r['hazard_type'] ?? 'other') ?></span>
                </span>
              </td>

              <!-- Triage -->
              <td class="col-triage">
                <span class="tbadge" style="background:<?= $tm['bg'] ?>;color:<?= $tm['color'] ?>;border:1px solid <?= $tm['border'] ?>;"><?= e($tm['label']) ?></span>
                <div style="font-size:.68rem;color:#94a3b8;margin-top:2px;">Score: <?= (int)($r['triage_score'] ?? 0) ?>/16</div>
              </td>

              <!-- Submitted -->
              <td class="col-sub">
                <span style="color:#64748b;font-size:.78rem;"><?= e($ago) ?></span>
              </td>

              <!-- Resident -->
              <td class="col-res">
                <div style="font-weight:600;color:#0f172a;font-size:.8rem;"><?= e($r['reporter_name'] ?? 'Anonymous') ?></div>
                <?php if (!empty($r['reporter_phone'])): ?>
                  <div style="font-size:.7rem;color:#94a3b8;"><?= e($r['reporter_phone']) ?></div>
                <?php endif; ?>
              </td>

              <!-- Action -->
              <td class="col-act" onclick="event.stopPropagation();" style="text-align:right;">
                <a href="<?= base_url('reports/' . e($r['code'])) ?>"
                   class="vq-btn <?= $isPend ? 'vq-btn-reassess' : 'vq-btn-review' ?>">
                  <?= $isPend ? 'Reassess' : 'Review' ?>
                </a>
              </td>

            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
        <div class="vq-pager">
          <div class="vq-pinfo">Showing <strong><?= $from ?>–<?= $to ?></strong> of <strong><?= $total ?></strong> reports</div>
          <div class="pages">

            <!-- Prev -->
            <?php if ($page > 1): ?>
              <a href="?<?= $buildQuery(['page'=>$page-1]) ?>" class="pg">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg>
              </a>
            <?php else: ?>
              <span class="pg off"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 18l-6-6 6-6"/></svg></span>
            <?php endif; ?>

            <?php
            $s = max(1, $page-2); $e = min($totalPages, $page+2);
            if ($s > 1) { echo '<a href="?'.$buildQuery(['page'=>1]).'" class="pg">1</a>'; if($s>2) echo '<span class="pg-dot">…</span>'; }
            for ($i=$s; $i<=$e; $i++):
            ?>
              <?php if ($i === $page): ?>
                <span class="pg on"><?= $i ?></span>
              <?php else: ?>
                <a href="?<?= $buildQuery(['page'=>$i]) ?>" class="pg"><?= $i ?></a>
              <?php endif; ?>
            <?php endfor; ?>
            <?php if ($e < $totalPages) { if($e<$totalPages-1) echo '<span class="pg-dot">…</span>'; echo '<a href="?'.$buildQuery(['page'=>$totalPages]).'" class="pg">'.$totalPages.'</a>'; } ?>

            <!-- Next -->
            <?php if ($page < $totalPages): ?>
              <a href="?<?= $buildQuery(['page'=>$page+1]) ?>" class="pg">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
              </a>
            <?php else: ?>
              <span class="pg off"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg></span>
            <?php endif; ?>

          </div>
        </div>
      <?php endif; ?>

    <?php endif; ?>

  </div><!-- /card -->
</div><!-- /vq -->
