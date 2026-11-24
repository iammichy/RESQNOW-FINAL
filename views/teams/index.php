<?php
$teams = $teams ?? [];
$flash = $flash ?? null;

$unitMeta = [
    'fire'     => ['#fef2f2','#dc2626','F','Fire',           'Responds to structural/vehicle fires and LPG hazards'],
    'medical'  => ['#fdf4ff','#9333ea','M','Medical',        'First aid, patient stabilization, medical evacuation'],
    'rescue'   => ['#eff6ff','#2563eb','R','Rescue',         'Search & rescue, flood response, structural collapse'],
    'security' => ['#eef2ff','#4f46e5','S','Security',       'Law enforcement support, crowd control, armed threats'],
    'multi'    => ['#fff7ed','#ea580c','X','Multi-hazard',   'General-purpose, deployable to any hazard type'],
];

$statusInfo = [
    'available'  => ['#f0fdf4','#16a34a','Available'],
    'on_mission' => ['#fff7ed','#ea580c','On Mission'],
    'offline'    => ['#f8fafc','#94a3b8','Offline'],
];
?>
<style>
.tm-wrap  { padding:20px 24px 32px; }
.tm-head  { display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:16px; }
.tm-title { font-size:1.25rem;font-weight:800;color:#0f172a;letter-spacing:-.02em;margin:0 0 2px; }
.tm-sub   { font-size:.78rem;color:#64748b;margin:0; }
.tm-add   { display:inline-flex;align-items:center;gap:6px;height:36px;padding:0 16px;background:#dc2626;color:#fff;border-radius:6px;font-size:.8rem;font-weight:700;text-decoration:none;box-shadow:0 2px 8px rgba(220,38,38,.3); }
.tm-add:hover { background:#b91c1c;color:#fff; }

/* Unit legend strip */
.tm-legend { display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px; }
.tm-legend-chip { display:flex;align-items:center;gap:5px;padding:4px 10px;border-radius:6px;font-size:.72rem;font-weight:600; }

/* Card grid */
.tm-grid { display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:14px; }

/* Team card */
.tm-card { background:#fff;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;transition:box-shadow .15s,transform .1s; }
.tm-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.07);transform:translateY(-1px); }
.tm-card-head { padding:14px 16px 10px; border-bottom:1px solid #f1f5f9; }
.tm-card-body { padding:12px 16px 14px; }
.tm-card-foot { padding:10px 16px;border-top:1px solid #f1f5f9;display:flex;align-items:center;gap:8px;background:#fafbfc; }

/* Unit avatar */
.tm-unit-av { width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.8rem;font-weight:800;flex-shrink:0; }

/* Status badge */
.tm-status { display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:100px;font-size:.7rem;font-weight:700; }
.tm-status-dot { width:6px;height:6px;border-radius:50%; }

/* Member row */
.tm-member { display:flex;align-items:center;justify-content:space-between;padding:4px 0; }
.tm-member:not(:last-child) { border-bottom:1px solid #f8fafc; }
.tm-duty { display:inline-flex;align-items:center;gap:3px;font-size:.63rem;font-weight:700;border-radius:3px;padding:1px 6px; }
</style>

<div class="tm-wrap">

  <?php if ($flash): ?>
    <div style="padding:10px 14px;border-radius:6px;margin-bottom:14px;font-size:.8125rem;<?= $flash['type']==='success' ? 'background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;' : 'background:#fef2f2;border:1px solid #fecaca;color:#991b1b;' ?>"><?= e($flash['message']) ?></div>
  <?php endif; ?>

  <!-- Header -->
  <div class="tm-head">
    <div>
      <h1 class="tm-title">Response Teams</h1>
      <p class="tm-sub"><?= count($teams) ?> team<?= count($teams)!==1?'s':'' ?> registered — organized by unit type</p>
    </div>
    <a href="<?= base_url('teams/new') ?>" class="tm-add">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      New Team
    </a>
  </div>

  <!-- Unit type legend -->
  <div class="tm-legend">
    <?php foreach ($unitMeta as $type => [$bg, $color, $init, $label, $desc]): ?>
      <div class="tm-legend-chip" style="background:<?= $bg ?>;color:<?= $color ?>;" title="<?= e($desc) ?>">
        <span style="font-weight:900;"><?= $init ?></span>
        <span><?= $label ?></span>
      </div>
    <?php endforeach; ?>
    <div class="tm-legend-chip" style="background:#f0fdf4;color:#16a34a;" title="Responder is on an active mission">
      <span style="width:6px;height:6px;border-radius:50%;background:#16a34a;display:inline-block;"></span>
      On Duty
    </div>
    <div class="tm-legend-chip" style="background:#f8fafc;color:#94a3b8;" title="Responder not currently on a mission">
      <span style="width:6px;height:6px;border-radius:50%;background:#cbd5e1;display:inline-block;"></span>
      Off Duty
    </div>
  </div>

  <?php if (empty($teams)): ?>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;text-align:center;padding:52px 20px;">
      <div style="font-size:.9375rem;font-weight:700;color:#0f172a;margin-bottom:4px;">No teams yet</div>
      <div style="font-size:.8125rem;color:#64748b;margin-bottom:16px;">Create your first response team to start assigning missions.</div>
      <a href="<?= base_url('teams/new') ?>" class="tm-add" style="display:inline-flex;">Create Team</a>
    </div>
  <?php else: ?>

  <!-- Card grid -->
  <div class="tm-grid">
    <?php foreach ($teams as $t):
      [$bg, $color, $init, $label, $desc] = $unitMeta[$t['unit_type']] ?? ['#f8fafc','#64748b','O','Other',''];
      [$stBg, $stColor, $stLabel] = $statusInfo[$t['status']] ?? ['#f8fafc','#94a3b8',ucfirst($t['status'])];
      $isOffline = $t['status'] === 'offline';
      $isOnMission = $t['status'] === 'on_mission';

      // Parse members_packed: "Name|||duty;;Name2|||duty2"
      $memberRows = [];
      if (!empty($t['members_packed'])) {
          foreach (explode(';;', $t['members_packed']) as $chunk) {
              $parts = explode('|||', $chunk, 2);
              if (count($parts) === 2) {
                  $memberRows[] = ['name' => $parts[0], 'duty' => $parts[1]];
              }
          }
      }
      $onDutyCount = count(array_filter($memberRows, fn($m) => $m['duty'] === 'on_duty'));
    ?>
      <div class="tm-card" style="<?= $isOffline ? 'opacity:.6;' : '' ?>border-left:3px solid <?= $color ?>;">

        <!-- Card header -->
        <div class="tm-card-head">
          <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;">
            <div style="display:flex;align-items:center;gap:10px;">
              <div class="tm-unit-av" style="background:<?= $bg ?>;color:<?= $color ?>;"
                   title="<?= e($desc) ?>">
                <?= $init ?>
              </div>
              <div>
                <div style="font-size:.875rem;font-weight:800;color:#0f172a;line-height:1.2;"><?= e($t['team_name']) ?></div>
                <div style="font-size:.72rem;color:<?= $color ?>;font-weight:600;margin-top:1px;"><?= e($label) ?></div>
              </div>
            </div>
            <div>
              <span class="tm-status" style="background:<?= $stBg ?>;color:<?= $stColor ?>;">
                <span class="tm-status-dot" style="background:<?= $stColor ?>;<?= $isOnMission ? 'animation:pend-pulse 1.4s infinite;' : '' ?>"></span>
                <?= e($stLabel) ?>
              </span>
            </div>
          </div>

          <!-- Quick info row -->
          <div style="display:flex;flex-wrap:wrap;gap:14px;margin-top:10px;">
            <?php if (!empty($t['coverage_area'])): ?>
              <div style="display:flex;align-items:center;gap:4px;font-size:.72rem;color:#64748b;">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <?= e($t['coverage_area']) ?>
              </div>
            <?php endif; ?>
            <?php if (!empty($t['contact_no'])): ?>
              <div style="display:flex;align-items:center;gap:4px;font-size:.72rem;color:#64748b;">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4a2 2 0 0 1 1.99-2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6.13 6.13l1.82-1.82a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                <?= e($t['contact_no']) ?>
              </div>
            <?php endif; ?>
            <?php if ((int)$t['active_missions'] > 0): ?>
              <div style="display:flex;align-items:center;gap:4px;font-size:.72rem;color:#ea580c;font-weight:700;">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                <?= (int)$t['active_missions'] ?> active mission<?= (int)$t['active_missions']!==1?'s':'' ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Card body: leader + members -->
        <div class="tm-card-body">
          <!-- Team leader -->
          <div style="margin-bottom:8px;">
            <div style="font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;margin-bottom:5px;">Team Leader</div>
            <?php if (!empty($t['leader_name'])): ?>
              <div style="display:flex;align-items:center;gap:6px;">
                <div style="width:22px;height:22px;border-radius:50%;background:<?= $bg ?>;color:<?= $color ?>;display:flex;align-items:center;justify-content:center;font-size:.6rem;font-weight:800;flex-shrink:0;">
                  <?= strtoupper(substr($t['leader_name'],0,1)) ?>
                </div>
                <span style="font-size:.8rem;font-weight:700;color:#0f172a;"><?= e($t['leader_name']) ?></span>
                <span style="font-size:.65rem;font-weight:600;background:#eff6ff;color:#2563eb;border-radius:3px;padding:1px 5px;">Leader</span>
              </div>
            <?php else: ?>
              <span style="font-size:.78rem;color:#94a3b8;">No leader assigned</span>
            <?php endif; ?>
          </div>

          <!-- Members -->
          <div>
            <div style="font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;margin-bottom:6px;">
              Members
              <?php if (!empty($memberRows)): ?>
                <span style="margin-left:4px;background:#f1f5f9;color:#475569;border-radius:3px;padding:1px 5px;font-size:.65rem;"><?= count($memberRows) ?></span>
                <?php if ($onDutyCount > 0): ?>
                  <span style="margin-left:3px;background:#f0fdf4;color:#16a34a;border-radius:3px;padding:1px 5px;font-size:.65rem;"><?= $onDutyCount ?> on duty</span>
                <?php endif; ?>
              <?php endif; ?>
            </div>
            <?php if (empty($memberRows)): ?>
              <span style="font-size:.78rem;color:#94a3b8;">No members assigned</span>
            <?php else: ?>
              <?php foreach ($memberRows as $m):
                $isOnDuty = $m['duty'] === 'on_duty';
              ?>
                <div class="tm-member">
                  <div style="display:flex;align-items:center;gap:6px;">
                    <div style="width:20px;height:20px;border-radius:50%;background:<?= $isOnDuty ? '#f0fdf4' : '#f8fafc' ?>;border:1.5px solid <?= $isOnDuty ? '#bbf7d0' : '#e2e8f0' ?>;display:flex;align-items:center;justify-content:center;font-size:.55rem;font-weight:800;color:<?= $isOnDuty ? '#16a34a' : '#94a3b8' ?>;flex-shrink:0;">
                      <?= strtoupper(substr($m['name'],0,1)) ?>
                    </div>
                    <span style="font-size:.78rem;color:#374151;font-weight:500;"><?= e($m['name']) ?></span>
                  </div>
                  <span class="tm-duty" style="background:<?= $isOnDuty ? '#f0fdf4' : '#f8fafc' ?>;color:<?= $isOnDuty ? '#16a34a' : '#94a3b8' ?>;">
                    <span style="width:5px;height:5px;border-radius:50%;background:<?= $isOnDuty ? '#16a34a' : '#cbd5e1' ?>;flex-shrink:0;"></span>
                    <?= $isOnDuty ? 'On Duty' : 'Off Duty' ?>
                  </span>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <!-- Card footer: actions -->
        <div class="tm-card-foot">
          <a href="<?= base_url('teams/' . $t['id'] . '/edit') ?>"
             style="flex:1;height:30px;display:inline-flex;align-items:center;justify-content:center;background:#fff;border:1.5px solid #e2e8f0;border-radius:5px;font-size:.75rem;font-weight:600;color:#374151;text-decoration:none;">
            Edit Team
          </a>
          <?php if ($t['status'] !== 'on_mission'): ?>
            <form method="post" action="<?= base_url('teams/' . $t['id'] . '/delete') ?>"
                  onsubmit="return confirm('Delete <?= e(addslashes($t['team_name'])) ?>?')">
              <?= csrf_field() ?>
              <button type="submit"
                style="height:30px;padding:0 12px;background:#fff;border:1.5px solid #fecaca;border-radius:5px;font-size:.75rem;font-weight:600;color:#dc2626;cursor:pointer;">
                Delete
              </button>
            </form>
          <?php else: ?>
            <span style="font-size:.72rem;color:#94a3b8;font-style:italic;">On mission — cannot delete</span>
          <?php endif; ?>
        </div>

      </div>
    <?php endforeach; ?>
  </div>

  <?php endif; ?>
</div>
