<?php
$announcements = $announcements ?? [];
$flash         = $flash         ?? null;
$now           = time();

$severityMeta = [
    'information' => ['#eff6ff','#2563eb','#bfdbfe','Information'],
    'advisory'    => ['#f0fdf4','#16a34a','#bbf7d0','Advisory'],
    'warning'     => ['#fffbeb','#d97706','#fde68a','Warning'],
    'critical'    => ['#fef2f2','#dc2626','#fecaca','Critical'],
];
$audienceMeta = [
    'all'        => 'All Users',
    'residents'  => 'Residents',
    'responders' => 'Responders',
];
?>
<style>
.ann-wrap  { padding:20px 24px 32px; }
.ann-head  { display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:18px; }
.ann-title { font-size:1.25rem;font-weight:800;color:#0f172a;letter-spacing:-.02em;margin:0 0 2px; }
.ann-add   { display:inline-flex;align-items:center;gap:6px;height:36px;padding:0 16px;background:#dc2626;color:#fff;border-radius:6px;font-size:.8rem;font-weight:700;text-decoration:none;box-shadow:0 2px 8px rgba(220,38,38,.3); }
.ann-add:hover { background:#b91c1c;color:#fff; }
.ann-bcast { display:inline-flex;align-items:center;gap:6px;height:36px;padding:0 14px;background:#fff;color:#475569;border:1.5px solid #e2e8f0;border-radius:6px;font-size:.8rem;font-weight:600;cursor:pointer;text-decoration:none; }
.ann-grid  { display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:14px; }
.ann-card  { background:#fff;border-radius:10px;overflow:hidden;border:1px solid #e2e8f0;transition:box-shadow .15s; }
.ann-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.07); }
.ann-card.expired { opacity:.55; }
.ann-card-strip { height:4px; }
.ann-card-body { padding:16px 18px; }
.ann-badge { display:inline-block;padding:3px 9px;border-radius:5px;font-size:.68rem;font-weight:700; }
.ann-expired-tag { display:inline-flex;align-items:center;gap:3px;padding:3px 7px;border-radius:4px;background:#f8fafc;color:#94a3b8;font-size:.65rem;font-weight:700; }
.ann-aud-tag { display:inline-flex;align-items:center;gap:3px;padding:3px 7px;border-radius:4px;background:#f1f5f9;color:#475569;font-size:.65rem;font-weight:600; }
</style>

<div class="ann-wrap">

  <?php if ($flash): ?>
    <div style="padding:10px 14px;border-radius:6px;margin-bottom:14px;font-size:.8125rem;<?= $flash['type']==='success' ? 'background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;' : 'background:#fef2f2;border:1px solid #fecaca;color:#991b1b;' ?>"><?= e($flash['message']) ?></div>
  <?php endif; ?>

  <!-- Header -->
  <div class="ann-head">
    <div>
      <h1 class="ann-title">Announcements</h1>
      <p style="font-size:.78rem;color:#64748b;margin:0;"><?= count($announcements) ?> announcement<?= count($announcements)!==1?'s':'' ?> · Max 3 shown on resident dashboard (active, non-expired)</p>
    </div>
    <div style="display:flex;align-items:center;gap:8px;">
      <button type="button" class="ann-bcast" data-bs-toggle="modal" data-bs-target="#broadcastModal">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4a2 2 0 0 1 1.99-2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6.13 6.13l1.82-1.82a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
        Broadcast
      </button>
      <a href="<?= base_url('announcements/new') ?>" class="ann-add">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Post Announcement
      </a>
    </div>
  </div>

  <!-- Cards -->
  <?php if (empty($announcements)): ?>
    <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;text-align:center;padding:52px 20px;">
      <div style="font-size:.9375rem;font-weight:700;color:#0f172a;margin-bottom:4px;">No announcements yet</div>
      <div style="font-size:.8125rem;color:#64748b;margin-bottom:16px;">Post the first announcement to inform residents.</div>
      <a href="<?= base_url('announcements/new') ?>" class="ann-add" style="display:inline-flex;">Post Announcement</a>
    </div>
  <?php else: ?>
    <div class="ann-grid">
      <?php foreach ($announcements as $a):
        [$bg, $color, $border, $sevLabel] = $severityMeta[$a['severity'] ?? 'information'] ?? $severityMeta['information'];
        $audLabel  = $audienceMeta[$a['audience'] ?? 'all'] ?? 'All Users';
        $isExpired = !empty($a['event_date']) && strtotime($a['event_date']) < $now;
      ?>
        <div class="ann-card <?= $isExpired ? 'expired' : '' ?>">
          <div class="ann-card-strip" style="background:<?= $color ?>;"></div>
          <div class="ann-card-body">

            <!-- Title row -->
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:8px;">
              <h3 style="font-size:.9rem;font-weight:800;color:#0f172a;margin:0;line-height:1.3;"><?= e($a['title']) ?></h3>
              <div style="display:flex;align-items:center;gap:5px;flex-shrink:0;">
                <?php if ($isExpired): ?>
                  <span class="ann-expired-tag">Expired</span>
                <?php endif; ?>
                <span class="ann-badge" style="background:<?= $bg ?>;color:<?= $color ?>;border:1px solid <?= $border ?>;"><?= $sevLabel ?></span>
              </div>
            </div>

            <!-- Body -->
            <p style="font-size:.8125rem;color:#475569;margin:0 0 12px;line-height:1.55;"><?= nl2br(e($a['body'])) ?></p>

            <!-- Meta row -->
            <div style="display:flex;align-items:center;flex-wrap:wrap;gap:7px;margin-bottom:10px;">
              <span class="ann-aud-tag">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <?= $audLabel ?>
              </span>
              <?php if (!empty($a['event_date'])): ?>
                <span class="ann-aud-tag" style="color:<?= $isExpired ? '#dc2626' : '#475569' ?>;">
                  <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                  <?= date('M j, Y g:i A', strtotime($a['event_date'])) ?>
                </span>
              <?php endif; ?>
            </div>

            <!-- Footer -->
            <div style="display:flex;align-items:center;justify-content:space-between;padding-top:10px;border-top:1px solid #f1f5f9;">
              <div style="font-size:.72rem;color:#94a3b8;">
                By <?= e($a['creator_name'] ?? 'System') ?> · <?= date('M j, Y', strtotime($a['created_at'])) ?>
              </div>
              <div style="display:flex;align-items:center;gap:4px;">
                <!-- Edit -->
                <a href="<?= base_url('announcements/' . $a['id'] . '/edit') ?>"
                   style="display:inline-flex;align-items:center;gap:4px;height:28px;padding:0 10px;border:1.5px solid #e2e8f0;border-radius:5px;font-size:.7rem;font-weight:600;color:#475569;text-decoration:none;background:#fff;transition:border-color .1s,color .1s;"
                   title="Edit announcement">
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                  Edit
                </a>
                <!-- Delete -->
                <form method="post" action="<?= base_url('announcements/' . $a['id'] . '/delete') ?>" onsubmit="return confirm('Delete this announcement?')">
                  <?= csrf_field() ?>
                  <button type="submit" style="background:none;border:none;cursor:pointer;color:#fca5a5;padding:4px;display:flex;align-items:center;" title="Delete">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- Broadcast Modal -->
<div class="modal fade" id="broadcastModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow-lg">
      <form method="post" action="<?= base_url('announcements/broadcast') ?>">
        <?= csrf_field() ?>
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title fw-bold fs-3">Broadcast Notification</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-bold small">Title</label>
            <input type="text" name="title" class="form-control" placeholder="Short urgent title" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold small">Message</label>
            <textarea name="message" class="form-control" rows="3" placeholder="Full message content…" required></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold small">Target Audience</label>
            <select name="target" class="form-select">
              <option value="all">All Users (incl. Personnel)</option>
              <option value="residents">Residents Only</option>
            </select>
          </div>
          <div class="alert alert-warning small mb-0">Sends an immediate push notification to selected users' PWA and web portal.</div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-white border fw-bold" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning fw-bold">Send Broadcast</button>
        </div>
      </form>
    </div>
  </div>
</div>
