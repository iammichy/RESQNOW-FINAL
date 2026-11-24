<?php
// Determine on-duty responders via active assignments
// (passed from controller, or derived here via a query if not available)
$onDutyIds = $onDutyIds ?? [];

$roleMeta = [
    'admin'     => ['#eef2ff','#4f46e5','Admin'],
    'personnel' => ['#eff6ff','#2563eb','Personnel'],
    'responder' => ['#f0fdf4','#16a34a','Responder'],
];
?>
<style>
.pm-wrap  { padding:20px 24px 32px; }
.pm-head  { display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:18px; }
.pm-title { font-size:1.25rem;font-weight:800;color:#0f172a;letter-spacing:-.02em;margin:0 0 2px; }
.pm-add   { display:inline-flex;align-items:center;gap:6px;height:36px;padding:0 16px;background:#dc2626;color:#fff;border-radius:6px;font-size:.8rem;font-weight:700;text-decoration:none;box-shadow:0 2px 8px rgba(220,38,38,.3); }
.pm-add:hover { background:#b91c1c;color:#fff; }
.pm-table { width:100%;border-collapse:collapse;table-layout:auto; }
.pm-table thead tr { background:#f8fafc;border-bottom:1px solid #e2e8f0; }
.pm-table thead th { padding:9px 16px;font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;text-align:left;white-space:nowrap; }
.pm-table tbody tr { border-bottom:1px solid #f8fafc;transition:background .1s; }
.pm-table tbody tr:last-child { border-bottom:none; }
.pm-table tbody tr:hover { background:#fafbfc; }
.pm-table tbody td { padding:12px 16px;vertical-align:middle;font-size:.8125rem;color:#374151; }
.pm-av { width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:800;flex-shrink:0; }
.pm-role-badge { display:inline-block;border-radius:5px;padding:3px 9px;font-size:.68rem;font-weight:700; }
.pm-status-badge {
  display:inline-flex;align-items:center;gap:5px;
  border-radius:5px;padding:4px 10px;font-size:.72rem;font-weight:700;
}
.pm-status-dot { width:7px;height:7px;border-radius:50%;flex-shrink:0; }
</style>

<div class="pm-wrap">

  <?php if ($flash): ?>
    <div style="padding:10px 14px;border-radius:6px;margin-bottom:14px;font-size:.8125rem;
      <?= $flash['type']==='success' ? 'background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;' : 'background:#fef2f2;border:1px solid #fecaca;color:#991b1b;' ?>">
      <?= e($flash['message']) ?>
    </div>
  <?php endif; ?>

  <div class="pm-head">
    <div>
      <h1 class="pm-title">Personnel Management</h1>
      <p style="font-size:.78rem;color:#64748b;margin:0;"><?= count($users) ?> staff member<?= count($users)!==1?'s':'' ?> registered</p>
    </div>
    <a href="<?= base_url('personnel/new') ?>" class="pm-add">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Add Personnel
    </a>
  </div>

  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">
    <?php if (empty($users)): ?>
      <div style="text-align:center;padding:48px 20px;font-size:.875rem;color:#94a3b8;">No personnel found.</div>
    <?php else: ?>
      <div style="overflow-x:auto;">
        <table class="pm-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Role</th>
              <th>Contact</th>
              <th>Status</th>
              <th style="text-align:right;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $u):
              [$roleBg, $roleColor, $roleLabel] = $roleMeta[$u['role']] ?? ['#f8fafc','#64748b',ucfirst($u['role'])];
              $isActive    = $u['status'] === 'approved';
              $isSuspended = $u['status'] === 'suspended';
              $isRespOnDuty = $u['role'] === 'responder' && in_array((int)$u['id'], $onDutyIds);
              $isSelf      = (int)$u['id'] === (int)auth()['id'];

              // Status display
              if ($u['role'] === 'responder') {
                  if (!$isActive) {
                      $stBg='#fef2f2'; $stColor='#dc2626'; $stDot='#dc2626'; $stLabel='Suspended';
                  } elseif ($isRespOnDuty) {
                      $stBg='#fff7ed'; $stColor='#ea580c'; $stDot='#ea580c'; $stLabel='On Duty';
                  } else {
                      $stBg='#f0fdf4'; $stColor='#16a34a'; $stDot='#16a34a'; $stLabel='Off Duty';
                  }
              } else {
                  if ($isActive) {
                      $stBg='#f0fdf4'; $stColor='#16a34a'; $stDot='#16a34a'; $stLabel='Active';
                  } else {
                      $stBg='#fef2f2'; $stColor='#dc2626'; $stDot='#dc2626'; $stLabel='Suspended';
                  }
              }
            ?>
              <tr>
                <!-- Name -->
                <td>
                  <div style="display:flex;align-items:center;gap:10px;">
                    <div class="pm-av" style="background:<?= $roleBg ?>;color:<?= $roleColor ?>;">
                      <?= strtoupper(substr($u['name'],0,1)) ?>
                    </div>
                    <div>
                      <div style="font-weight:700;color:#0f172a;"><?= e($u['name']) ?></div>
                      <?php if ($isSelf): ?>
                        <div style="font-size:.65rem;color:#94a3b8;">(you)</div>
                      <?php endif; ?>
                    </div>
                  </div>
                </td>

                <!-- Role -->
                <td>
                  <span class="pm-role-badge" style="background:<?= $roleBg ?>;color:<?= $roleColor ?>;"><?= $roleLabel ?></span>
                </td>

                <!-- Contact -->
                <td>
                  <div style="font-size:.8rem;color:#0f172a;"><?= e($u['email'] ?? '—') ?></div>
                  <div style="font-size:.72rem;color:#94a3b8;"><?= e($u['phone'] ?? '—') ?></div>
                </td>

                <!-- Status -->
                <td>
                  <span class="pm-status-badge" style="background:<?= $stBg ?>;color:<?= $stColor ?>;">
                    <span class="pm-status-dot" style="background:<?= $stDot ?>;<?= ($isRespOnDuty) ? 'animation:pend-pulse 1.4s infinite;' : '' ?>"></span>
                    <?= $stLabel ?>
                  </span>
                </td>

                <!-- Actions -->
                <td style="text-align:right;">
                  <div style="display:flex;align-items:center;gap:6px;justify-content:flex-end;">
                    <a href="<?= base_url('personnel/' . $u['id'] . '/edit') ?>"
                       style="height:30px;padding:0 12px;background:#fff;border:1.5px solid #e2e8f0;border-radius:5px;font-size:.75rem;font-weight:600;color:#374151;display:inline-flex;align-items:center;text-decoration:none;">
                      Edit
                    </a>
                    <?php if (!$isSelf): ?>
                      <form method="post" action="<?= base_url('personnel/' . $u['id'] . '/toggle-status') ?>">
                        <?= csrf_field() ?>
                        <button type="submit"
                          style="height:30px;padding:0 11px;background:#fff;border-radius:5px;font-size:.75rem;font-weight:600;cursor:pointer;
                            <?= $isActive ? 'border:1.5px solid #fecaca;color:#dc2626;' : 'border:1.5px solid #bbf7d0;color:#16a34a;' ?>">
                          <?= $isActive ? 'Suspend' : 'Activate' ?>
                        </button>
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

</div>
