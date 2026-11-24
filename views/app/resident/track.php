<?php
$incidents = $incidents ?? [];
?>
<div class="mob-content">
  
  <div class="px-3 mb-3 d-flex justify-content-between align-items-center">
    <h2 class="h3 fw-bold mb-0">My Reports</h2>
    <div class="badge bg-blue-lt px-3 py-2 rounded-pill"><?= count($incidents) ?> Total</div>
  </div>

  <?php if (empty($incidents)): ?>
    <div class="text-center py-5">
      <div style="font-size: 64px;" class="mb-3">📋</div>
      <h3 class="h4">No reports found</h3>
      <p class="text-muted px-4">You haven't submitted any emergency reports yet.</p>
      <a href="<?= base_url('app/resident/report') ?>" class="mob-btn mx-auto mt-3" style="width: auto; display: inline-block; padding-left: 30px; padding-right: 30px;">
        Start a Report
      </a>
    </div>
  <?php else: ?>
    <?php foreach ($incidents as $r): ?>
      <a href="<?= base_url('app/resident/track/' . e($r['code'])) ?>" class="mob-card d-block text-decoration-none text-dark">
        <div class="mob-card-body">
          <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="d-flex align-items-center gap-2">
              <span style="font-size: 20px;"><?= e(hazard_icon($r['hazard_type'])) ?></span>
              <span class="fw-bold"><?= e($r['code']) ?></span>
            </div>
            <?= triage_badge($r['triage_level']) ?>
          </div>

          <div class="mb-2">
            <div class="text-muted small mb-1">Address</div>
            <div class="fw-semibold small"><?= e($r['address']) ?></div>
          </div>

          <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
            <div class="d-flex align-items-center gap-2">
              <div class="status-dot <?= status_color($r['status']) ?>"></div>
              <span class="small fw-bold <?= status_text_color($r['status']) ?>"><?= e(ucwords(str_replace('_', ' ', $r['status']))) ?></span>
            </div>
            <span class="text-muted small"><?= date('M j, Y H:i', strtotime($r['created_at'])) ?></span>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
  <?php endif; ?>

</div>

<?php
function hazard_icon(string $h): string {
    return match($h) {
        'fire' => '🔥', 'flood' => '🌊', 'earthquake' => '🌐',
        'medical' => '⚕️', 'accident' => '🚗', 'evacuation' => '👥',
        'security' => '🛡️', default => '⚠️',
    };
}
function triage_badge(string $l): string {
    $cls = match($l) {
        'critical' => 'badge-critical',
        'high' => 'badge-high',
        'moderate' => 'badge-moderate',
        'low' => 'badge-low',
        'pending_reassessment' => 'badge-pending',
        default => 'bg-secondary',
    };
    $label = ucwords(str_replace('_', ' ', $l));
    return '<span class="mob-badge ' . $cls . '">' . e($label) . '</span>';
}
function status_color(string $s): string {
    return match($s) {
        'submitted' => 'bg-secondary',
        'verified'  => 'bg-blue',
        'assigned'  => 'bg-cyan',
        'on_scene'  => 'bg-info',
        'resolved'  => 'bg-success',
        'closed'    => 'bg-dark',
        'rejected', 'duplicate' => 'bg-danger',
        default => 'bg-secondary',
    };
}
function status_text_color(string $s): string {
    return match($s) {
        'resolved' => 'text-success',
        'rejected', 'duplicate' => 'text-danger',
        'verified', 'assigned', 'on_scene' => 'text-blue',
        default => 'text-muted',
    };
}
?>
