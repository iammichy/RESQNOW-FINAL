<div class="mob-content">
  
  <div class="px-3 mb-4">
    <h2 class="h3 fw-bold mb-1">Emergency & City Services</h2>
    <p class="text-muted small">Tap to call local authorities and emergency units.</p>
  </div>

  <?php 
  $typeLabels = [
    'barangay' => 'Barangay Officials',
    'rescue'   => 'Disaster & Rescue',
    'police'   => 'Police & Security',
    'fire'     => 'Fire Protection',
    'medical'  => 'Health & Medical',
    'utility'  => 'Utilities & General Services',
    'social'   => 'Social & Environment',
    'other'    => 'Other Contacts'
  ];

  $typeIcons = [
    'barangay' => '👤',
    'rescue'   => '🚁',
    'police'   => '🚔',
    'fire'     => '🚒',
    'medical'  => '🏥',
    'utility'  => '🛠️',
    'social'   => '🤝',
    'other'    => '📞'
  ];
  ?>

  <?php foreach ($typeLabels as $type => $label): ?>
    <?php if (!empty($contacts[$type])): ?>
      <div class="section-label"><?= e($label) ?></div>
      <div class="mob-card p-0">
        <?php foreach ($contacts[$type] as $c): ?>
          <a href="tel:<?= e(str_replace(' ', '', $c['phone'])) ?>" class="list-item">
            <span style="font-size: 20px;"><?= $typeIcons[$type] ?? '📞' ?></span>
            <div class="flex-grow-1">
              <div class="fw-bold" style="font-size: 14px;"><?= e($c['name']) ?></div>
              <div class="text-muted small"><?= e($c['description']) ?></div>
              <div class="text-primary fw-semibold" style="font-size: 12px; margin-top: 2px;"><?= e($c['phone']) ?></div>
            </div>
            <div class="bg-primary-lt rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-primary">
                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4a2 2 0 0 1 1.99-2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6.13 6.13l1.82-1.82a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
              </svg>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  <?php endforeach; ?>

  <div class="px-3 mt-4 mb-5">
    <div class="mob-card bg-primary-lt border-0">
      <div class="mob-card-body">
        <div class="d-flex align-items-center gap-3">
          <span style="font-size: 24px;">💡</span>
          <div class="small text-muted">
            For life-threatening emergencies, always prioritize calling the National Emergency hotline (911) or your local CDRRMO.
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<style>
  .bg-primary-lt { background-color: rgba(220, 38, 38, 0.08) !important; }
  .text-primary { color: #dc2626 !important; }
</style>
