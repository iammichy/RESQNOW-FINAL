<?php
$incidents = $incidents ?? [];
$counts    = $counts    ?? [];
$flash     = $flash     ?? null;

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

$critical  = $counts['critical']             ?? 0;
$highCount = $counts['high']                 ?? 0;
$needsRe   = $counts['pending_reassessment'] ?? 0;
$total     = array_sum($counts);
?>

<style>
.pq { padding:20px 24px 32px; }
.pq-head { display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:16px; }
.pq-title { font-size:1.25rem;font-weight:800;color:#0f172a;letter-spacing:-.02em;margin:0 0 2px; }
.pq-sub   { font-size:.78rem;color:#64748b;margin:0; }
.spill { display:inline-flex;align-items:center;gap:5px;padding:4px 11px;border-radius:100px;font-size:.74rem;font-weight:600;white-space:nowrap; }

/* Table */
.pq-table { width:100%;border-collapse:collapse;table-layout:fixed; }
.pq-table thead tr { background:#f8fafc;border-bottom:1px solid #e2e8f0; }
.pq-table thead th { padding:9px 14px;font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;text-align:left; }
.pq-table tbody tr { border-bottom:1px solid #f8fafc;transition:background .1s;cursor:pointer; }
.pq-table tbody tr:last-child { border-bottom:none; }
.pq-table tbody tr:hover { background:#fafbfc; }
.pq-table tbody tr.pend { background:#fafafa; }
.pq-table tbody tr.pend:hover { background:#f5f5f5; }
.pq-table tbody td { padding:10px 14px;vertical-align:middle;font-size:.8125rem;color:#374151; }

/* Column widths */
.c-bar { width:4px;padding:0!important; }
.c-code{ width:150px; }
.c-haz { width:115px; }
.c-tr  { width:170px; }
.c-dt  { width:140px; }
.c-rep { width:160px; }
.c-act { width:100px; }

/* Badges */
.tbadge { display:inline-block;border-radius:4px;padding:2px 7px;font-size:.65rem;font-weight:700;white-space:nowrap;vertical-align:middle; }
.hav    { display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:5px;font-size:.6rem;font-weight:800;flex-shrink:0;vertical-align:middle; }

/* Action buttons */
.pq-btn { display:inline-flex;align-items:center;justify-content:center;height:27px;padding:0 12px;border-radius:4px;font-size:.72rem;font-weight:700;text-decoration:none;border:none;cursor:pointer;white-space:nowrap;transition:background .1s,transform .08s; }
.pq-btn:active { transform:scale(.97); }
.pq-btn-critical { background:#dc2626;color:#fff; }
.pq-btn-critical:hover { background:#b91c1c;color:#fff; }
.pq-btn-high     { background:#ea580c;color:#fff; }
.pq-btn-high:hover { background:#c2410c;color:#fff; }
.pq-btn-other    { background:#fff;color:#475569;border:1.5px solid #e2e8f0; }
.pq-btn-other:hover { background:#f8fafc; }
.pq-btn-reassess { background:#fff;color:#d97706;border:1.5px solid #fde68a; }
.pq-btn-reassess:hover { background:#fffbeb; }

@keyframes dp { 0%,100%{opacity:1} 50%{opacity:.4} }
</style>

<div class="pq">

  <?php if ($flash): ?>
    <div style="padding:10px 14px;border-radius:6px;margin-bottom:14px;font-size:.8125rem;<?= $flash['type']==='success'?'background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;':'background:#fef2f2;border:1px solid #fecaca;color:#991b1b;' ?>"><?= e($flash['message']) ?></div>
  <?php endif; ?>

  <!-- Header -->
  <div class="pq-head">
    <div>
      <h1 class="pq-title">Dispatch Queue</h1>
      <p class="pq-sub">Verified incidents ordered by priority — select a report to assign a response team</p>
    </div>
    <div style="display:flex;align-items:center;gap:7px;flex-wrap:wrap;">
      <?php if ($critical > 0): ?>
        <span class="spill" style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;">
          <span style="width:6px;height:6px;border-radius:50%;background:#dc2626;flex-shrink:0;"></span>
          <?= $critical ?> Critical
        </span>
      <?php endif; ?>
      <?php if ($highCount > 0): ?>
        <span class="spill" style="background:#fff7ed;color:#ea580c;border:1px solid #fed7aa;">
          <span style="width:6px;height:6px;border-radius:50%;background:#ea580c;flex-shrink:0;"></span>
          <?= $highCount ?> High
        </span>
      <?php endif; ?>
      <?php if ($needsRe > 0): ?>
        <span class="spill" style="background:#f1f5f9;color:#475569;border:1px solid #cbd5e1;">
          <span style="width:6px;height:6px;border-radius:50%;background:#94a3b8;flex-shrink:0;animation:dp 1.4s infinite;"></span>
          <?= $needsRe ?> Needs Reassessment
        </span>
      <?php endif; ?>
      <span class="spill" style="background:#f0f9ff;color:#0284c7;border:1px solid #bae6fd;">
        <?= $total ?> total pending
      </span>
    </div>
  </div>

  <!-- Table card -->
  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">

    <?php if (empty($incidents)): ?>
      <div style="text-align:center;padding:52px 20px;">
        <div style="width:48px;height:48px;border-radius:50%;background:#f0fdf4;border:1.5px solid #bbf7d0;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div style="font-size:.9375rem;font-weight:700;color:#0f172a;margin-bottom:4px;">Queue is clear</div>
        <div style="font-size:.8125rem;color:#64748b;">No verified incidents awaiting dispatch.</div>
      </div>

    <?php else: ?>

      <table class="pq-table">
        <thead>
          <tr>
            <th class="c-bar"></th>
            <th class="c-code">Report Code</th>
            <th class="c-haz">Hazard</th>
            <th class="c-tr">Triage</th>
            <th class="c-dt">Submitted</th>
            <th class="c-rep">Reporter</th>
            <th class="c-act" style="text-align:right;">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($incidents as $r):
            $level   = $r['triage_level'] ?? 'low';
            $isPend  = $level === 'pending_reassessment';
            $isCrit  = $level === 'critical';
            $isHigh  = $level === 'high';
            $tm      = $triageMeta[$level] ?? $triageMeta['low'];
            $hm      = $hazardMeta[$r['hazard_type'] ?? 'other'] ?? $hazardMeta['other'];
            $ts      = strtotime($r['created_at'] ?? '');
            $dateStr = $ts ? date('M j, Y g:i A', $ts) : '—';
            // Button class
            if ($isPend)     $btnCls = 'pq-btn-reassess';
            elseif ($isCrit) $btnCls = 'pq-btn-critical';
            elseif ($isHigh) $btnCls = 'pq-btn-high';
            else             $btnCls = 'pq-btn-other';
            $btnLabel = $isPend ? 'Reassess' : 'Assign';
          ?>
            <tr class="<?= $isPend ? 'pend' : '' ?>"
                onclick="window.location='<?= base_url('reports/' . e($r['code'])) ?>'">

              <!-- Accent bar -->
              <td class="c-bar" style="padding:0!important;">
                <span style="display:block;width:4px;min-height:48px;background:<?= $tm['bar'] ?>;"></span>
              </td>

              <!-- Code -->
              <td class="c-code">
                <span style="font-weight:700;color:#0f172a;font-family:monospace;font-size:.8rem;"><?= e($r['code']) ?></span>
              </td>

              <!-- Hazard -->
              <td class="c-haz">
                <span style="display:inline-flex;align-items:center;gap:7px;">
                  <span class="hav" style="background:<?= $hm['bg'] ?>;color:<?= $hm['color'] ?>;"><?= $hm['init'] ?></span>
                  <span style="text-transform:capitalize;font-weight:500;font-size:.78rem;"><?= e($r['hazard_type'] ?? 'other') ?></span>
                </span>
              </td>

              <!-- Triage -->
              <td class="c-tr">
                <span class="tbadge" style="background:<?= $tm['bg'] ?>;color:<?= $tm['color'] ?>;border:1px solid <?= $tm['border'] ?>;"><?= e($tm['label']) ?></span>
                <div style="font-size:.68rem;color:#94a3b8;margin-top:2px;">Score: <?= (int)($r['triage_score'] ?? 0) ?>/16</div>
              </td>

              <!-- Submitted -->
              <td class="c-dt" style="font-size:.75rem;color:#64748b;white-space:nowrap;"><?= e($dateStr) ?></td>

              <!-- Reporter -->
              <td class="c-rep">
                <div style="font-weight:600;color:#0f172a;font-size:.8rem;"><?= e($r['reporter_name'] ?? 'Anonymous') ?></div>
                <?php if (!empty($r['reporter_phone'])): ?>
                  <div style="font-size:.7rem;color:#94a3b8;"><?= e($r['reporter_phone']) ?></div>
                <?php endif; ?>
              </td>

              <!-- Action -->
              <td class="c-act" onclick="event.stopPropagation();">
                <div style="display:flex;justify-content:flex-end;">
                  <a href="<?= base_url('reports/' . e($r['code'])) ?>"
                     class="pq-btn <?= $btnCls ?>">
                    <?= $btnLabel ?>
                  </a>
                </div>
              </td>

            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

    <?php endif; ?>

  </div>

  <div style="margin:10px 0 0;font-size:.72rem;color:#94a3b8;line-height:1.5;">
    Incidents are ordered by triage priority (Critical first). Admin selects which team to deploy — the triage score determines urgency, not automatic dispatch.
  </div>

</div>
