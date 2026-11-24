<?php
$families      = $families      ?? [];
$counts        = $counts        ?? ['total'=>0,'registered'=>0,'pending'=>0,'not_registered'=>0,'families_total'=>0];
$filters       = $filters       ?? [];
$flash         = $flash         ?? null;
$regRequests   = $regRequests   ?? [];
$totalFamilies = $counts['families_total'];

// Use controller-supplied accurate counts (all families, unfiltered, based on masterlist)
$famNoReg     = $famNoReg     ?? 0;
$famAllReg    = $famAllReg    ?? 0;
$famAtLeastOne = $famAtLeastOne ?? 0;
// famWithReg = partial (at least 1 but not all) — derived for legacy use if needed
$famWithReg   = $famAtLeastOne - $famAllReg;

$activeTab = $_GET['tab'] ?? 'directory';
?>
<style>
.rm-wrap { padding:20px 24px 32px; }
.rm-sum-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:14px; }
@media(max-width:768px){ .rm-sum-grid { grid-template-columns:repeat(2,1fr); } }
.rm-sum-card { background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:14px 16px;cursor:pointer;transition:box-shadow .12s,border-color .12s;text-decoration:none;color:inherit;display:block; }
.rm-sum-card:hover { border-color:#cbd5e1;box-shadow:0 2px 8px rgba(0,0,0,.06); }
.rm-sum-card.active { border-color:#dc2626;box-shadow:0 0 0 2px rgba(220,38,38,.1); }
.rm-sum-label { font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;margin-bottom:6px; }
.rm-sum-val   { font-size:1.5rem;font-weight:800;color:#0f172a;line-height:1; }
.rm-sum-sub   { font-size:.72rem;color:#94a3b8;margin-top:3px; }
/* Tab nav */
.rm-tabs { display:flex;gap:0;border-bottom:2px solid #e2e8f0;margin-bottom:16px; }
.rm-tab  { padding:8px 16px;font-size:.8rem;font-weight:600;color:#94a3b8;border-bottom:2px solid transparent;margin-bottom:-2px;cursor:pointer;text-decoration:none;transition:color .12s; }
.rm-tab:hover { color:#0f172a; }
.rm-tab.active { color:#dc2626;border-bottom-color:#dc2626; }
/* Filter bar */
.rm-filter { background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;margin-bottom:14px;display:flex;flex-wrap:wrap;gap:8px;align-items:center; }
.rm-filter input,.rm-filter select { height:32px;border:1.5px solid #e2e8f0;border-radius:5px;padding:0 9px;font-size:.78rem;color:#0f172a;background:#f8fafc;outline:none;font-family:inherit; }
.rm-filter input:focus,.rm-filter select:focus { border-color:#dc2626;background:#fff; }
.rm-btn-f  { height:32px;padding:0 13px;background:#0f172a;color:#fff;border:none;border-radius:5px;font-size:.78rem;font-weight:600;cursor:pointer; }
.rm-btn-clr{ height:32px;padding:0 10px;background:#fff;color:#64748b;border:1.5px solid #e2e8f0;border-radius:5px;font-size:.78rem;font-weight:600;text-decoration:none;display:inline-flex;align-items:center; }
/* Two-panel */
.rm-panels { display:grid;grid-template-columns:380px 1fr;gap:14px;align-items:start; }
@media(max-width:900px){ .rm-panels { grid-template-columns:1fr; } }
.rm-list-panel { background:#fff;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden; }
.rm-list-scroll { overflow-y:auto;max-height:calc(100vh - 360px); }
.rm-fam-row { padding:10px 14px;border-bottom:1px solid #f8fafc;cursor:pointer;transition:background .1s; }
.rm-fam-row:last-child { border-bottom:none; }
.rm-fam-row:hover { background:#fafbfc; }
.rm-fam-row.selected { background:#eff6ff; }
.rm-purok-sep { padding:5px 14px;font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;background:#f8fafc;border-bottom:1px solid #f1f5f9; }
.rm-reg-bar { height:3px;border-radius:2px;background:#e2e8f0;margin-top:4px;overflow:hidden; }
.rm-reg-fill { height:3px;border-radius:2px;background:#16a34a;transition:width .2s; }
.rm-detail-panel { background:#fff;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden; }
.rm-member-row { display:flex;align-items:center;justify-content:space-between;padding:7px 0;border-bottom:1px solid #f8fafc; }
.rm-member-row:last-child { border-bottom:none; }
/* Family summary chips */
.rm-fam-chips { display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px; }
.rm-fam-chip { display:flex;align-items:center;gap:5px;padding:5px 12px;border-radius:6px;font-size:.72rem;font-weight:700;cursor:pointer;border:1.5px solid transparent;text-decoration:none;color:inherit;transition:border-color .12s; }
.rm-fam-chip:hover { border-color:#cbd5e1; }
.rm-fam-chip.active-chip { border-color:#dc2626; }
/* Request table */
.rm-req-table { width:100%;border-collapse:collapse;table-layout:fixed; }
.rm-req-table thead tr { background:#f8fafc;border-bottom:1px solid #e2e8f0; }
.rm-req-table thead th { padding:9px 14px;font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;text-align:left; }
.rm-req-table tbody tr { border-bottom:1px solid #f8fafc;transition:background .1s; }
.rm-req-table tbody tr:last-child { border-bottom:none; }
.rm-req-table tbody tr:hover { background:#fafbfc; }
.rm-req-table tbody td { padding:10px 14px;vertical-align:middle;font-size:.8rem;color:#374151; }
/* Match status colors */
.ms-matched   { background:#f0fdf4;color:#16a34a; }
.ms-notfound  { background:#fef2f2;color:#dc2626; }
.ms-multiple  { background:#fffbeb;color:#d97706; }
.ms-duplicate { background:#fdf4ff;color:#9333ea; }
</style>

<div class="rm-wrap">

  <?php if ($flash): ?>
    <div style="padding:10px 14px;border-radius:6px;margin-bottom:14px;font-size:.8125rem;<?= $flash['type']==='success'?'background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;':'background:#fef2f2;border:1px solid #fecaca;color:#991b1b;'?>"><?= e($flash['message']) ?></div>
  <?php endif; ?>

  <!-- Header -->
  <div style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:14px;">
    <div>
      <h1 style="font-size:1.25rem;font-weight:800;color:#0f172a;margin:0 0 2px;">Resident Management</h1>
      <p style="font-size:.78rem;color:#64748b;margin:0;">Official resident registry · Barangay Camunatan</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
      <a href="<?= base_url('residents/masterlist/new') ?>" style="display:inline-flex;align-items:center;gap:5px;height:34px;padding:0 13px;background:#dc2626;color:#fff;border-radius:6px;font-size:.78rem;font-weight:700;text-decoration:none;">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Resident
      </a>
      <div style="position:relative;">
        <button onclick="document.getElementById('em').style.display=document.getElementById('em').style.display==='block'?'none':'block'"
          style="display:inline-flex;align-items:center;gap:6px;height:34px;padding:0 13px;background:#fff;border:1.5px solid #e2e8f0;border-radius:6px;font-size:.78rem;font-weight:600;color:#475569;cursor:pointer;">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Export
        </button>
        <div id="em" style="display:none;position:absolute;right:0;top:38px;background:#fff;border:1px solid #e2e8f0;border-radius:7px;box-shadow:0 4px 16px rgba(0,0,0,.1);z-index:99;min-width:200px;">
          <a href="<?= base_url('residents/export') ?>" style="display:flex;align-items:center;gap:8px;padding:9px 14px;font-size:.8rem;color:#374151;text-decoration:none;">Accounts CSV</a>
          <a href="<?= base_url('residents/masterlist/export') ?>" style="display:flex;align-items:center;gap:8px;padding:9px 14px;font-size:.8rem;color:#374151;text-decoration:none;border-top:1px solid #f1f5f9;">Masterlist — All Puroks</a>
          <a href="<?= base_url('residents/masterlist/export?purok=1') ?>" style="padding:7px 14px 7px 28px;font-size:.78rem;color:#64748b;text-decoration:none;display:block;border-top:1px solid #f8fafc;">Purok 1 only</a>
          <a href="<?= base_url('residents/masterlist/export?purok=2') ?>" style="padding:7px 14px 7px 28px;font-size:.78rem;color:#64748b;text-decoration:none;display:block;">Purok 2 only</a>
          <a href="<?= base_url('residents/masterlist/export?purok=3') ?>" style="padding:7px 14px 7px 28px;font-size:.78rem;color:#64748b;text-decoration:none;display:block;">Purok 3 only</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Summary cards -->
  <div class="rm-sum-grid">
    <a href="<?= base_url('residents?tab=directory') ?>" class="rm-sum-card <?= $activeTab==='directory'&&!($filters['registered']??'') ? 'active' : '' ?>">
      <div class="rm-sum-label">Total Residents</div>
      <div class="rm-sum-val"><?= number_format($counts['total']) ?></div>
      <div class="rm-sum-sub"><?= number_format($counts['families_total']) ?> families</div>
    </a>
    <a href="?tab=directory&registered=registered" class="rm-sum-card <?= ($filters['registered']??'')==='registered' ? 'active' : '' ?>">
      <div class="rm-sum-label">Registered</div>
      <div class="rm-sum-val" style="color:#16a34a;"><?= $counts['registered'] ?></div>
      <div class="rm-sum-sub">Approved accounts</div>
    </a>
    <a href="?tab=directory&registered=not_registered" class="rm-sum-card <?= ($filters['registered']??'')==='not_registered' ? 'active' : '' ?>">
      <div class="rm-sum-label">Not Registered</div>
      <div class="rm-sum-val" style="color:#64748b;"><?= number_format($counts['not_registered']) ?></div>
      <div class="rm-sum-sub">No account yet</div>
    </a>
    <a href="?tab=requests&req_status=pending" class="rm-sum-card <?= $activeTab==='requests' ? 'active' : '' ?>" style="<?= $counts['pending'] > 0 ? 'border-color:#fde68a;' : '' ?>">
      <div class="rm-sum-label">Awaiting Approval</div>
      <div class="rm-sum-val" style="color:#ea580c;"><?= $counts['pending'] ?></div>
      <div class="rm-sum-sub"><?= $counts['pending'] > 0 ? 'Needs review — click to view' : 'None pending' ?></div>
    </a>
  </div>

  <!-- Family registration summary chips -->
  <div class="rm-fam-chips">
    <a href="?tab=directory<?= !empty($filters['purok']) ? '&purok='.$filters['purok'] : '' ?>" class="rm-fam-chip <?= $activeTab==='directory'&&!($filters['registered']??'') ? 'active-chip' : '' ?>" style="background:#f8fafc;">
      <span style="font-weight:800;color:#0f172a;"><?= $totalFamilies ?></span> <span style="color:#64748b;">All Families</span>
    </a>
    <a href="?tab=directory&registered=registered<?= !empty($filters['purok']) ? '&purok='.$filters['purok'] : '' ?>" class="rm-fam-chip <?= ($filters['registered']??'')==='registered' ? 'active-chip' : '' ?>" style="background:#f0fdf4;">
      <span style="width:8px;height:8px;border-radius:50%;background:#16a34a;display:inline-block;"></span>
      <span style="font-weight:800;color:#16a34a;"><?= $famAtLeastOne ?></span> <span style="color:#16a34a;">At least 1 registered</span>
    </a>
    <a href="?tab=directory&registered=not_registered<?= !empty($filters['purok']) ? '&purok='.$filters['purok'] : '' ?>" class="rm-fam-chip <?= ($filters['registered']??'')==='not_registered' ? 'active-chip' : '' ?>" style="background:#fef2f2;">
      <span style="width:8px;height:8px;border-radius:50%;background:#dc2626;display:inline-block;"></span>
      <span style="font-weight:800;color:#dc2626;"><?= $famNoReg ?></span> <span style="color:#dc2626;">No member registered</span>
    </a>
    <a href="?tab=directory&registered=full<?= !empty($filters['purok']) ? '&purok='.$filters['purok'] : '' ?>" class="rm-fam-chip <?= ($filters['registered']??'')==='full' ? 'active-chip' : '' ?>" style="background:#eff6ff;">
      <span style="width:8px;height:8px;border-radius:50%;background:#2563eb;display:inline-block;"></span>
      <span style="font-weight:800;color:#2563eb;"><?= $famAllReg ?></span> <span style="color:#2563eb;">All members registered</span>
    </a>
  </div>

  <!-- Tabs -->
  <div class="rm-tabs">
    <a href="?tab=directory<?= !empty($filters['purok'])?'&purok='.$filters['purok']:'' ?>" class="rm-tab <?= $activeTab==='directory' ? 'active' : '' ?>">Resident Directory</a>
    <a href="?tab=requests&req_status=pending" class="rm-tab <?= $activeTab==='requests' ? 'active' : '' ?>">
      Registration Requests
      <?php if ($counts['pending'] > 0): ?>
        <span style="display:inline-flex;align-items:center;justify-content:center;min-width:18px;height:18px;background:#dc2626;color:#fff;border-radius:9px;font-size:.6rem;font-weight:700;padding:0 5px;margin-left:5px;"><?= $counts['pending'] ?></span>
      <?php endif; ?>
    </a>
    <a href="?tab=validation" class="rm-tab <?= $activeTab==='validation' ? 'active' : '' ?>">Masterlist Validation</a>
  </div>

  <!-- ═══ TAB: DIRECTORY ═══════════════════════════════════════════════ -->
  <?php if ($activeTab === 'directory'): ?>

  <!-- Filter bar -->
  <form method="get" class="rm-filter" id="rm-filter-form">
    <input type="hidden" name="tab" value="directory">
    <?php if (!empty($filters['registered'])): ?>
      <input type="hidden" name="registered" value="<?= e($filters['registered']) ?>">
    <?php endif; ?>
    <select name="purok" onchange="this.form.submit()" title="Purok">
      <option value="">All Puroks</option>
      <option value="1" <?= ($filters['purok']??'') === '1' ? 'selected' : '' ?>>Purok 1 (534)</option>
      <option value="2" <?= ($filters['purok']??'') === '2' ? 'selected' : '' ?>>Purok 2 (439)</option>
      <option value="3" <?= ($filters['purok']??'') === '3' ? 'selected' : '' ?>>Purok 3 (438)</option>
    </select>
    <select name="registered" onchange="this.form.submit()" title="Registration Status">
      <option value="" <?= !($filters['registered']??'') ? 'selected' : '' ?>>All Families</option>
      <option value="registered"     <?= ($filters['registered']??'')==='registered'     ? 'selected' : '' ?>>At least 1 registered</option>
      <option value="not_registered" <?= ($filters['registered']??'')==='not_registered' ? 'selected' : '' ?>>No member registered</option>
      <option value="full"           <?= ($filters['registered']??'')==='full'           ? 'selected' : '' ?>>All members registered</option>
    </select>
    <input type="search" name="search" placeholder="Search resident name…" value="<?= e($filters['search'] ?? '') ?>" style="width:200px;" oninput="clearTimeout(window._st);window._st=setTimeout(()=>this.form.submit(),400)">
    <button type="submit" class="rm-btn-f">Search</button>
    <?php if (!empty(array_filter($filters))): ?>
      <a href="?tab=directory" class="rm-btn-clr">Clear</a>
    <?php endif; ?>
    <span style="margin-left:auto;font-size:.72rem;color:#94a3b8;"><?= $totalFamilies ?> famil<?= $totalFamilies!==1?'ies':'y' ?></span>
  </form>

  <!-- Two-panel -->
  <div class="rm-panels">
    <!-- LEFT: family list -->
    <div class="rm-list-panel">
      <div style="padding:8px 14px;border-bottom:1px solid #f1f5f9;font-size:.7rem;color:#94a3b8;">Select a family to view details and manage members</div>
      <div class="rm-list-scroll" id="rm-list">
        <?php if (empty($families)): ?>
          <div style="text-align:center;padding:32px 16px;font-size:.8rem;color:#94a3b8;">No families match your filters.</div>
        <?php else:
          $curPurok = null;
          foreach ($families as $fam):
            if ($curPurok !== $fam['purok']): $curPurok = $fam['purok']; ?>
              <div class="rm-purok-sep">Purok <?= $curPurok ?></div>
            <?php endif; ?>
            <div class="rm-fam-row" data-fam="<?= $fam['purok'] ?>-<?= $fam['family_no'] ?>" onclick="selectFamily(this)">
              <div style="display:flex;align-items:center;justify-content:space-between;gap:6px;">
                <div style="min-width:0;">
                  <div style="font-size:.8rem;font-weight:700;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= e($fam['family_head']) ?></div>
                  <div style="font-size:.7rem;color:#94a3b8;">Purok <?= $fam['purok'] ?> · Family #<?= $fam['family_no'] ?> · <?= $fam['member_count'] ?> member<?= $fam['member_count']!==1?'s':'' ?></div>
                </div>
                <?php $pct = $fam['member_count']>0?round($fam['registered_count']/$fam['member_count']*100):0; ?>
                <span style="font-size:.65rem;font-weight:700;border-radius:4px;padding:2px 7px;flex-shrink:0;background:<?= $pct===100?'#eff6ff':($fam['registered_count']>0?'#f0fdf4':'#f8fafc') ?>;color:<?= $pct===100?'#2563eb':($fam['registered_count']>0?'#16a34a':'#cbd5e1') ?>;">
                  <?= $fam['registered_count'] ?>/<?= $fam['member_count'] ?>
                </span>
              </div>
              <div class="rm-reg-bar">
                <div class="rm-reg-fill" style="width:<?= $pct ?>%;background:<?= $pct===100?'#2563eb':'#16a34a' ?>;"></div>
              </div>
            </div>
          <?php endforeach; endif; ?>
      </div>
    </div>

    <!-- RIGHT: detail panel -->
    <div class="rm-detail-panel" id="rm-detail-panel">
      <div id="rm-detail-empty" style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:52px 20px;text-align:center;">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        <div style="font-size:.875rem;font-weight:700;color:#94a3b8;margin-top:10px;">Select a family</div>
        <div style="font-size:.78rem;color:#cbd5e1;margin-top:4px;">Click any family from the list</div>
      </div>
      <div id="rm-detail-content" style="display:none;"></div>
    </div>
  </div>

  <?php endif; // directory tab ?>

  <!-- ═══ TAB: REGISTRATION REQUESTS ═══════════════════════════════════ -->
  <?php if ($activeTab === 'requests'): ?>
  <?php
  $reqStatus = $_GET['req_status'] ?? 'pending';
  $reqStatusOptions = ['pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected','all'=>'All'];
  $filteredRequests = $reqStatus === 'all' ? $regRequests : array_filter($regRequests, fn($r) => $r['status'] === $reqStatus);
  $matchColors = ['matched'=>['#f0fdf4','#16a34a','Matched'],'not_found'=>['#fef2f2','#dc2626','Not Found'],'unverified'=>['#fffbeb','#d97706','Multiple Matches'],'flagged_duplicate'=>['#fdf4ff','#9333ea','Duplicate Flag']];
  ?>

  <!-- Status sub-tabs -->
  <div style="display:flex;gap:6px;margin-bottom:14px;flex-wrap:wrap;">
    <?php foreach ($reqStatusOptions as $val => $label): ?>
      <?php $cnt = $val==='all' ? count($regRequests) : count(array_filter($regRequests, fn($r) => $r['status']===$val)); ?>
      <a href="?tab=requests&req_status=<?= $val ?>" style="display:inline-flex;align-items:center;gap:5px;height:30px;padding:0 12px;border-radius:6px;font-size:.78rem;font-weight:600;text-decoration:none;
        <?= $reqStatus===$val ? 'background:#0f172a;color:#fff;' : 'background:#fff;color:#64748b;border:1.5px solid #e2e8f0;' ?>">
        <?= $label ?>
        <span style="<?= $reqStatus===$val ? 'background:rgba(255,255,255,.2);color:#fff;' : 'background:#f1f5f9;color:#475569;' ?>;border-radius:4px;padding:0 5px;font-size:.65rem;font-weight:700;"><?= $cnt ?></span>
      </a>
    <?php endforeach; ?>
  </div>

  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">
    <?php if (empty($filteredRequests)): ?>
      <div style="text-align:center;padding:48px 20px;font-size:.875rem;color:#94a3b8;">
        No <?= $reqStatus==='all'?'':$reqStatus ?> registration requests.
      </div>
    <?php else: ?>
      <div style="overflow-x:auto;">
        <table class="rm-req-table">
          <thead>
            <tr>
              <th style="width:180px;">Applicant</th>
              <th style="width:100px;">Purok</th>
              <th style="width:130px;">Masterlist Check</th>
              <th style="width:80px;">Confidence</th>
              <th style="width:110px;">Verification</th>
              <th style="width:120px;">Registered</th>
              <th style="width:120px;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($filteredRequests as $r):
              $ms = $r['match_status'] ?? null;
              [$msBg,$msColor,$msLabel] = $matchColors[$ms] ?? ['#f8fafc','#94a3b8','Not Checked'];
              $mconf = (int)($r['match_confidence'] ?? 0);
              $verifStatus = $r['verif_status'] ?? null;
              $vsBadge = ['pending'=>['#fffbeb','#d97706'],'verified'=>['#f0fdf4','#16a34a'],'rejected'=>['#fef2f2','#dc2626']];
              [$vBg,$vColor] = $vsBadge[$verifStatus] ?? ['#f8fafc','#94a3b8'];
            ?>
              <tr>
                <td>
                  <div style="font-weight:700;color:#0f172a;"><?= e($r['name']) ?></div>
                  <div style="font-size:.7rem;color:#94a3b8;"><?= e($r['phone'] ?? $r['email'] ?? '') ?></div>
                </td>
                <td style="font-size:.78rem;color:#374151;"><?= e($r['purok'] ?? '—') ?></td>
                <td>
                  <span style="display:inline-block;padding:2px 7px;border-radius:4px;font-size:.65rem;font-weight:700;background:<?= $msBg ?>;color:<?= $msColor ?>;">
                    <?= $msLabel ?>
                  </span>
                </td>
                <td style="font-size:.78rem;color:<?= $mconf >= 90 ? '#16a34a' : ($mconf >= 70 ? '#d97706' : '#94a3b8') ?>;">
                  <?= $mconf ? $mconf.'%' : '—' ?>
                </td>
                <td>
                  <?php if ($verifStatus): ?>
                    <span style="display:inline-block;padding:2px 7px;border-radius:4px;font-size:.65rem;font-weight:700;background:<?= $vBg ?>;color:<?= $vColor ?>;">
                      <?= ucfirst($verifStatus) ?>
                    </span>
                    <?php if (!empty($r['verified_by_name'])): ?>
                      <div style="font-size:.65rem;color:#94a3b8;margin-top:2px;">by <?= e($r['verified_by_name']) ?></div>
                    <?php endif; ?>
                  <?php else: ?>
                    <span style="font-size:.72rem;color:#94a3b8;">—</span>
                  <?php endif; ?>
                </td>
                <td style="font-size:.72rem;color:#64748b;"><?= $r['created_at'] ? date('M j, Y', strtotime($r['created_at'])) : '—' ?></td>
                <td>
                  <div style="display:flex;gap:5px;flex-wrap:wrap;">
                    <a href="<?= base_url('residents/' . $r['id']) ?>" style="height:27px;padding:0 10px;background:#fff;border:1.5px solid #e2e8f0;border-radius:5px;font-size:.72rem;font-weight:600;color:#374151;display:inline-flex;align-items:center;text-decoration:none;">
                      Review
                    </a>
                    <?php if ($r['status'] === 'pending' && auth()['role'] === 'admin'): ?>
                      <form method="post" action="<?= base_url('residents/' . $r['id'] . '/approve') ?>">
                        <?= csrf_field() ?>
                        <button type="submit" style="height:27px;padding:0 9px;background:#16a34a;color:#fff;border:none;border-radius:5px;font-size:.72rem;font-weight:600;cursor:pointer;">Approve</button>
                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
  <?php endif; // requests tab ?>

  <!-- ═══ TAB: MASTERLIST VALIDATION ═══════════════════════════════════ -->
  <?php if ($activeTab === 'validation'): ?>
  <?php
  // Find potential duplicate names in masterlist
  $dupQuery = $db->query(
      "SELECT name, COUNT(*) AS cnt, GROUP_CONCAT(CONCAT('P',purok,' F#',family_no) ORDER BY purok,family_no SEPARATOR ', ') AS locations
       FROM resident_masterlist WHERE status='active'
       GROUP BY TRIM(LOWER(REGEXP_REPLACE(name,'[^a-zA-Z ]','')))
       HAVING cnt > 1
       ORDER BY cnt DESC, name
       LIMIT 50"
  )->fetchAll();

  $totalActive = (int)$db->query("SELECT COUNT(*) FROM resident_masterlist WHERE status='active'")->fetchColumn();
  $totalArchived = (int)$db->query("SELECT COUNT(*) FROM resident_masterlist WHERE status!='active'")->fetchColumn();
  $byStatus = $db->query("SELECT status, COUNT(*) AS cnt FROM resident_masterlist GROUP BY status")->fetchAll();
  ?>

  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:16px;">
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:14px 16px;">
      <div style="font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;margin-bottom:6px;">Active Records</div>
      <div style="font-size:1.5rem;font-weight:800;color:#16a34a;line-height:1;"><?= number_format($totalActive) ?></div>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:14px 16px;">
      <div style="font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;margin-bottom:6px;">Archived/Inactive</div>
      <div style="font-size:1.5rem;font-weight:800;color:#94a3b8;line-height:1;"><?= number_format($totalArchived) ?></div>
    </div>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:<?= count($dupQuery)>0?'14px 16px':'14px 16px' ?>;">
      <div style="font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;margin-bottom:6px;">Possible Duplicates</div>
      <div style="font-size:1.5rem;font-weight:800;color:<?= count($dupQuery)>0?'#dc2626':'#16a34a' ?>;line-height:1;"><?= count($dupQuery) ?></div>
    </div>
  </div>

  <!-- Status breakdown -->
  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:14px 18px;margin-bottom:14px;">
    <div style="font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;margin-bottom:10px;">Records by Status</div>
    <div style="display:flex;flex-wrap:wrap;gap:10px;">
      <?php foreach ($byStatus as $bs):
        $bsColors = ['active'=>['#f0fdf4','#16a34a'],'deceased'=>['#fef2f2','#dc2626'],'transferred'=>['#fff7ed','#ea580c'],'archived'=>['#f8fafc','#94a3b8']];
        [$bBg,$bColor] = $bsColors[$bs['status']] ?? ['#f8fafc','#64748b'];
      ?>
        <div style="background:<?= $bBg ?>;color:<?= $bColor ?>;border-radius:6px;padding:6px 12px;font-size:.75rem;font-weight:700;">
          <?= ucfirst($bs['status']) ?>: <?= $bs['cnt'] ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Possible duplicates -->
  <?php if (!empty($dupQuery)): ?>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;margin-bottom:14px;">
      <div style="padding:12px 16px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
        <div style="font-size:.8125rem;font-weight:700;color:#0f172a;">Possible Duplicate Names</div>
        <span style="font-size:.72rem;color:#94a3b8;">Based on normalized name matching — review manually</span>
      </div>
      <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.8rem;">
          <thead>
            <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
              <th style="padding:8px 14px;text-align:left;font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;">Name</th>
              <th style="padding:8px 14px;text-align:left;font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;">Count</th>
              <th style="padding:8px 14px;text-align:left;font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;">Locations</th>
              <th style="padding:8px 14px;text-align:left;font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($dupQuery as $dup): ?>
              <tr style="border-bottom:1px solid #f8fafc;">
                <td style="padding:9px 14px;font-weight:600;color:#0f172a;"><?= e($dup['name']) ?></td>
                <td style="padding:9px 14px;">
                  <span style="background:#fef2f2;color:#dc2626;border-radius:4px;padding:2px 7px;font-size:.7rem;font-weight:700;"><?= $dup['cnt'] ?> records</span>
                </td>
                <td style="padding:9px 14px;font-size:.72rem;color:#64748b;"><?= e($dup['locations']) ?></td>
                <td style="padding:9px 14px;">
                  <a href="?tab=directory&search=<?= urlencode($dup['name']) ?>" style="font-size:.72rem;font-weight:600;color:#2563eb;text-decoration:none;">View in Directory</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php else: ?>
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:14px 18px;text-align:center;font-size:.875rem;font-weight:600;color:#16a34a;">
      No duplicate names detected in the masterlist.
    </div>
  <?php endif; ?>

  <!-- Instructions -->
  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:16px 18px;">
    <div style="font-size:.8125rem;font-weight:700;color:#0f172a;margin-bottom:10px;">How to fix issues</div>
    <ul style="font-size:.78rem;color:#64748b;line-height:1.7;margin:0;padding-left:18px;">
      <li>To <strong>correct a spelling error</strong> — find the resident in the Directory, click their family to select it, then use the Edit button in the detail panel.</li>
      <li>To <strong>archive a record</strong> (deceased, transferred out) — open the resident's profile using "View →" and use the Archive section.</li>
      <li>To <strong>add a missing resident</strong> — click "Add Resident" at the top of this page.</li>
      <li>To <strong>resolve a duplicate</strong> — open both records in the Directory and archive the incorrect one.</li>
    </ul>
  </div>

  <?php endif; // validation tab ?>

</div><!-- /rm-wrap -->

<script>
var FAMILIES = <?= json_encode(array_values($families)) ?>;
var BASE = '<?= base_url('') ?>';
var IS_ADMIN = <?= json_encode(auth()['role'] === 'admin') ?>;
var CSRF = '<?= \App\Core\Csrf::token() ?>';

function selectFamily(el) {
  document.querySelectorAll('.rm-fam-row').forEach(r => r.classList.remove('selected'));
  el.classList.add('selected');
  var parts = el.dataset.fam.split('-');
  var fam = FAMILIES.find(f => f.purok == parts[0] && f.family_no == parts[1]);
  if (!fam) return;

  var totalReg = fam.registered_count;
  var totalMem = fam.member_count;
  var pct = totalMem > 0 ? Math.round(totalReg / totalMem * 100) : 0;
  var pctColor = pct === 100 ? '#2563eb' : pct > 0 ? '#16a34a' : '#94a3b8';
  var html = '<div>';

  // ── Header ──────────────────────────────────────────────────────────
  html += '<div style="padding:14px 18px;border-bottom:1px solid #f1f5f9;display:flex;align-items:flex-start;justify-content:space-between;gap:10px;">';
  html += '<div>';
  html += '<div style="font-size:.875rem;font-weight:800;color:#0f172a;">' + escH(fam.family_head) + '</div>';
  html += '<div style="font-size:.72rem;color:#64748b;margin-top:1px;">Purok ' + fam.purok + ' · Family #' + fam.family_no + ' · ' + fam.member_count + ' member' + (fam.member_count !== 1 ? 's' : '') + '</div>';
  html += '</div>';
  html += '<div style="text-align:right;flex-shrink:0;">';
  html += '<div style="font-size:1.1rem;font-weight:800;color:' + pctColor + ';">' + totalReg + '/' + totalMem + '</div>';
  html += '<div style="font-size:.65rem;color:#94a3b8;">registered</div>';
  html += '</div>';
  html += '</div>';
  html += '<div style="height:6px;background:#e2e8f0;margin:0;overflow:hidden;"><div style="height:6px;background:' + pctColor + ';width:' + pct + '%;transition:width .3s;"></div></div>';

  // ── Members ──────────────────────────────────────────────────────────
  html += '<div style="padding:14px 18px;border-bottom:1px solid #f1f5f9;">';
  html += '<div style="font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;margin-bottom:10px;">Family Members</div>';

  fam.members.forEach(function(m, idx) {
    var isHead = idx === 0;
    var u = m.user;
    var rs = u ? (u.status === 'approved' ? 'registered' : u.status) : 'not_registered';
    var sBg = rs === 'registered' ? '#f0fdf4' : rs === 'pending' ? '#fffbeb' : '#f8fafc';
    var sColor = rs === 'registered' ? '#16a34a' : rs === 'pending' ? '#ea580c' : '#94a3b8';
    var sLabel = rs === 'registered' ? 'Registered' : rs === 'pending' ? 'Pending' : 'Not Registered';
    var sDot = rs === 'registered' ? '#16a34a' : rs === 'pending' ? '#ea580c' : '#cbd5e1';

    html += '<div class="rm-member-row">';
    html += '<div style="display:flex;align-items:center;gap:8px;flex:1;min-width:0;">';
    html += '<div style="width:28px;height:28px;border-radius:50%;background:' + sBg + ';border:1.5px solid ' + (u ? sColor + '33' : '#e2e8f0') + ';display:flex;align-items:center;justify-content:center;font-size:.65rem;font-weight:800;color:' + sColor + ';flex-shrink:0;">' + (m.name.charAt(0) || '?').toUpperCase() + '</div>';
    html += '<div style="min-width:0;">';
    html += '<div style="font-size:.8rem;font-weight:' + (isHead ? '700' : '600') + ';color:#0f172a;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + escH(m.name) + (isHead ? ' <span style="font-size:.6rem;color:#2563eb;background:#eff6ff;padding:1px 5px;border-radius:3px;">Head</span>' : '') + '</div>';
    if (u && u.phone) html += '<div style="font-size:.7rem;color:#94a3b8;">' + escH(u.phone) + '</div>';
    html += '</div></div>';
    html += '<div style="display:flex;align-items:center;gap:5px;flex-shrink:0;">';
    html += '<span style="display:inline-flex;align-items:center;gap:3px;font-size:.65rem;font-weight:700;padding:2px 8px;border-radius:4px;background:' + sBg + ';color:' + sColor + ';">';
    html += '<span style="width:5px;height:5px;border-radius:50%;background:' + sDot + ';"></span>' + sLabel + '</span>';
    if (u && u.status === 'approved') html += '<a href="' + BASE + 'residents/' + u.id + '" style="font-size:.7rem;color:#2563eb;text-decoration:none;">View →</a>';
    // Edit masterlist record link (show for all members)
    html += '<a href="' + BASE + 'residents/masterlist/' + m.id + '/edit" style="font-size:.7rem;color:#475569;text-decoration:none;border:1px solid #e2e8f0;border-radius:4px;padding:1px 6px;" title="Edit this masterlist record">Edit</a>';
    html += '</div>';
    html += '</div>';
  });
  html += '</div>';

  // ── Pending approvals ──────────────────────────────────────────────
  var pendingMembers = fam.members.filter(m => m.user && m.user.status === 'pending');
  if (IS_ADMIN && pendingMembers.length > 0) {
    html += '<div style="padding:14px 18px;border-bottom:1px solid #f1f5f9;">';
    html += '<div style="font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;margin-bottom:8px;">Pending Approval (' + pendingMembers.length + ')</div>';
    pendingMembers.forEach(function(m) {
      html += '<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:6px;padding:10px 12px;margin-bottom:8px;">';
      html += '<div style="font-size:.8rem;font-weight:700;color:#0f172a;margin-bottom:4px;">' + escH(m.name) + '</div>';
      html += '<div style="font-size:.72rem;color:#64748b;margin-bottom:8px;">Match: <strong>' + (m.user.match_status || 'not checked') + '</strong>' + (m.user.match_confidence ? ' · ' + m.user.match_confidence + '% confidence' : '') + '</div>';
      html += '<div style="display:flex;gap:6px;">';
      html += '<form method="post" action="' + BASE + 'residents/' + m.user.id + '/approve" style="flex:1;"><input type="hidden" name="csrf_token" value="' + CSRF + '"><button type="submit" style="width:100%;height:30px;background:#16a34a;color:#fff;border:none;border-radius:5px;font-size:.72rem;font-weight:700;cursor:pointer;">Approve</button></form>';
      html += '<a href="' + BASE + 'residents/' + m.user.id + '" style="flex:1;display:flex;align-items:center;justify-content:center;height:30px;background:#fff;border:1.5px solid #2563eb;border-radius:5px;font-size:.72rem;font-weight:600;color:#2563eb;text-decoration:none;">Full Review</a>';
      html += '</div>';
      html += '</div>';
    });
    html += '</div>';
  }

  // ── Add member + archive buttons ───────────────────────────────────
  html += '<div style="padding:12px 18px;display:flex;gap:8px;flex-wrap:wrap;">';
  html += '<a href="' + BASE + 'residents/masterlist/new?purok=' + fam.purok + '&family_no=' + fam.family_no + '" style="display:inline-flex;align-items:center;gap:5px;height:30px;padding:0 12px;background:#dc2626;color:#fff;border-radius:5px;font-size:.72rem;font-weight:700;text-decoration:none;">';
  html += '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add Member</a>';
  html += '<a href="' + BASE + 'residents/masterlist/new" style="display:inline-flex;align-items:center;gap:5px;height:30px;padding:0 12px;background:#fff;border:1.5px solid #e2e8f0;border-radius:5px;font-size:.72rem;font-weight:600;color:#374151;text-decoration:none;">New Family</a>';
  // Coverage summary
  html += '<span style="margin-left:auto;font-size:.72rem;color:#94a3b8;">' + pct + '% registered</span>';
  html += '</div>';

  html += '</div>';
  document.getElementById('rm-detail-empty').style.display = 'none';
  document.getElementById('rm-detail-content').style.display = '';
  document.getElementById('rm-detail-content').innerHTML = html;
}

function escH(s) {
  return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

(function() {
  // If returning from an add/edit, highlight the specific family; otherwise select the first row
  var params = new URLSearchParams(window.location.search);
  var hlPurok  = params.get('purok');
  var hlFamily = params.get('highlight_family');

  var target = null;
  if (hlPurok && hlFamily) {
    target = document.querySelector('.rm-fam-row[data-fam="' + hlPurok + '-' + hlFamily + '"]');
  }
  if (!target) {
    target = document.querySelector('.rm-fam-row');
  }
  if (target) {
    selectFamily(target);
    // Scroll the family into view in the list panel
    setTimeout(function() {
      target.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }, 80);
  }
  // Close export menu on outside click
  document.addEventListener('click', function(e) {
    var m = document.getElementById('em');
    if (m && !m.parentElement.contains(e.target)) m.style.display = 'none';
  });
})();
</script>
