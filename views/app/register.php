<?php $errors = $errors ?? []; ?>

<style>
  /* ── Password Strength Meter ──────────────────────────── */
  .pw-meter-wrap { margin: -0.5rem 0 1.25rem; }
  .pw-meter-bar { display: flex; gap: 4px; margin-bottom: 8px; }
  .pw-meter-seg {
    height: 4px; flex: 1; border-radius: 2px;
    background: #e2e8f0; transition: background .2s;
  }
  .pw-meter-seg.active { background: var(--red, #dc2626); }
  .pw-meter-seg.active.s1 { background: #ef4444; }
  .pw-meter-seg.active.s2 { background: #f59e0b; }
  .pw-meter-seg.active.s3 { background: #eab308; }
  .pw-meter-seg.active.s4 { background: #22c55e; }
  .pw-meter-label { font-size: 0.8rem; font-weight: 600; color: #64748b; margin-bottom: 8px; }
  .pw-meter-label.weak   { color: #ef4444; }
  .pw-meter-label.fair   { color: #f59e0b; }
  .pw-meter-label.good    { color: #eab308; }
  .pw-meter-label.strong { color: #22c55e; }
  .pw-reqs { list-style: none; padding: 0; margin: 0; display: flex; flex-wrap: wrap; gap: 6px 16px; }
  .pw-req {
    font-size: 0.75rem; color: #94a3b8;
    display: flex; align-items: center; gap: 4px;
  }
  .pw-req::before {
    content: '○'; font-size: 0.85rem; line-height: 1;
  }
  .pw-req.met { color: #22c55e; }
  .pw-req.met::before { content: '✓'; color: #22c55e; }
</style>

<div class="form-header">
  <h1 class="form-title">Create Account</h1>
  <p class="form-sub">Join ResQNow to start reporting and monitoring incidents.</p>
</div>

<?php if (!empty($errors['csrf'])): ?>
  <div class="f-alert">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <span><?= e($errors['csrf']) ?></span>
  </div>
<?php endif; ?>

<form method="post" action="<?= base_url('app/register') ?>" novalidate>
  <?= csrf_field() ?>

  <!-- PERSONAL INFORMATION -->
  <div class="f-sec">Personal Information</div>

  <div class="f-group">
    <label class="f-label" for="r_name">Full Name <span class="f-req">*</span></label>
    <div class="f-wrap">
      <span class="f-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
      <input class="f-inp <?= isset($errors['name']) ? 'is-err' : '' ?>" type="text" name="name" id="r_name"
        placeholder="Enter your complete name"
        value="<?= e($_POST['name'] ?? '') ?>" autocomplete="name" autofocus required>
    </div>
    <?php if (isset($errors['name'])): ?><div class="f-err"><?= e($errors['name']) ?></div><?php endif; ?>
  </div>

  <div class="f-grid">
    <div class="f-group">
      <label class="f-label" for="r_phone">Contact Number <span class="f-req">*</span></label>
      <div class="f-wrap">
        <span class="f-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4a2 2 0 0 1 1.99-2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6.13 6.13l1.82-1.82a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></span>
        <input class="f-inp <?= isset($errors['phone']) ? 'is-err' : '' ?>" type="tel" name="phone" id="r_phone"
          placeholder="09XX XXX XXXX"
          value="<?= e($_POST['phone'] ?? '') ?>" autocomplete="tel" required>
      </div>
      <?php if (isset($errors['phone'])): ?><div class="f-err"><?= e($errors['phone']) ?></div><?php endif; ?>
    </div>

    <div class="f-group">
      <label class="f-label" for="r_purok">Purok <span class="f-req">*</span></label>
      <div class="f-wrap">
        <select class="f-inp no-icon <?= isset($errors['purok']) ? 'is-err' : '' ?>" name="purok" id="r_purok" required>
          <option value="">Select Purok</option>
          <option value="Purok 1" <?= ($_POST['purok'] ?? '') === 'Purok 1' ? 'selected' : '' ?>>Purok 1 (Zone 1)</option>
          <option value="Purok 2" <?= ($_POST['purok'] ?? '') === 'Purok 2' ? 'selected' : '' ?>>Purok 2 (Zone 1)</option>
          <option value="Purok 3" <?= ($_POST['purok'] ?? '') === 'Purok 3' ? 'selected' : '' ?>>Purok 3 (Zone 2)</option>
          <option value="Purok 4" <?= ($_POST['purok'] ?? '') === 'Purok 4' ? 'selected' : '' ?>>Purok 4 (Zone 2)</option>
          <option value="Purok 5" <?= ($_POST['purok'] ?? '') === 'Purok 5' ? 'selected' : '' ?>>Purok 5 (Zone 3)</option>
          <option value="Purok 6" <?= ($_POST['purok'] ?? '') === 'Purok 6' ? 'selected' : '' ?>>Purok 6 (Zone 3)</option>
          <option value="Purok 7" <?= ($_POST['purok'] ?? '') === 'Purok 7' ? 'selected' : '' ?>>Purok 7 (Zone 4)</option>
        </select>
      </div>
      <?php if (isset($errors['purok'])): ?><div class="f-err"><?= e($errors['purok']) ?></div><?php endif; ?>
    </div>
  </div>

  <div class="f-group">
    <label class="f-label" for="r_address">Complete Address <span class="f-req">*</span></label>
    <div class="f-wrap">
      <span class="f-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></span>
      <input class="f-inp <?= isset($errors['address']) ? 'is-err' : '' ?>" type="text" name="address" id="r_address"
        placeholder="House #, Street Name, Landmark"
        value="<?= e($_POST['address'] ?? '') ?>" autocomplete="street-address" required>
    </div>
    <?php if (isset($errors['address'])): ?><div class="f-err"><?= e($errors['address']) ?></div><?php endif; ?>
  </div>

  <!-- ACCOUNT CREDENTIALS -->
  <div class="f-sec">Account Credentials</div>

  <div class="f-group">
    <label class="f-label" for="r_email">Email Address <span class="f-req">*</span></label>
    <div class="f-wrap">
      <span class="f-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></span>
      <input class="f-inp <?= isset($errors['email']) ? 'is-err' : '' ?>" type="email" name="email" id="r_email"
        placeholder="you@example.com"
        value="<?= e($_POST['email'] ?? '') ?>" autocomplete="email" required>
    </div>
    <?php if (isset($errors['email'])): ?><div class="f-err"><?= e($errors['email']) ?></div><?php endif; ?>
  </div>

  <div class="f-grid">
    <div class="f-group">
      <label class="f-label" for="r_pw">Password <span class="f-req">*</span></label>
      <div class="f-wrap">
        <span class="f-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
        <input class="f-inp <?= isset($errors['password']) ? 'is-err' : '' ?>" type="password" name="password" id="r_pw"
          placeholder="Create password"
          autocomplete="new-password" style="padding-right:44px;" required>
        <button type="button" class="f-eye" id="pw-a-btn">
          <svg id="pw-a-on" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          <svg id="pw-a-off" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
        </button>
      </div>
      <?php if (isset($errors['password'])): ?><div class="f-err"><?= e($errors['password']) ?></div><?php endif; ?>
    </div>

    <div class="f-group">
      <label class="f-label" for="r_pwc">Confirm Password <span class="f-req">*</span></label>
      <div class="f-wrap">
        <span class="f-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
        <input class="f-inp <?= isset($errors['password_confirm']) ? 'is-err' : '' ?>" type="password" name="password_confirm" id="r_pwc"
          placeholder="Confirm password"
          autocomplete="new-password" style="padding-right:44px;" required>
        <button type="button" class="f-eye" id="pw-b-btn">
          <svg id="pw-b-on" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
          <svg id="pw-b-off" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
        </button>
      </div>
      <?php if (isset($errors['password_confirm'])): ?><div class="f-err"><?= e($errors['password_confirm']) ?></div><?php endif; ?>
    </div>
  </div>

  <!-- Password strength meter -->
  <div class="pw-meter-wrap" id="pw-meter-wrap" style="display:none;">
    <div class="pw-meter-bar">
      <div class="pw-meter-seg" id="pw-seg-1"></div>
      <div class="pw-meter-seg" id="pw-seg-2"></div>
      <div class="pw-meter-seg" id="pw-seg-3"></div>
      <div class="pw-meter-seg" id="pw-seg-4"></div>
    </div>
    <div class="pw-meter-label" id="pw-meter-label">Enter a password</div>
    <ul class="pw-reqs">
      <li id="req-len"    class="pw-req">8+ characters</li>
      <li id="req-upper" class="pw-req">Uppercase letter</li>
      <li id="req-lower" class="pw-req">Lowercase letter</li>
      <li id="req-num"   class="pw-req">Number</li>
      <li id="req-spec"  class="pw-req">Special character</li>
    </ul>
  </div>

  <!-- HOUSEHOLD INFORMATION -->
  <div class="f-sec">Household Information</div>

  <div class="f-group">
    <label class="f-label" for="r_hh">Number of Household Members</label>
    <div class="f-wrap">
      <span class="f-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span>
      <input class="f-inp" type="number" name="household_count" id="r_hh"
        placeholder="Total members including yourself"
        min="1" max="30"
        value="<?= e($_POST['household_count'] ?? '') ?>">
    </div>
  </div>

  <div class="f-grid" style="margin-bottom:1.5rem;">
    <label class="f-check">
      <input type="checkbox" name="has_senior" value="1" <?= !empty($_POST['has_senior']) ? 'checked' : '' ?>>
      <span>Senior Citizen in household</span>
    </label>
    <label class="f-check">
      <input type="checkbox" name="has_child" value="1" <?= !empty($_POST['has_child']) ? 'checked' : '' ?>>
      <span>Child/Minor in household</span>
    </label>
    <label class="f-check">
      <input type="checkbox" name="has_pwd" value="1" <?= !empty($_POST['has_pwd']) ? 'checked' : '' ?>>
      <span>PWD in household</span>
    </label>
    <label class="f-check">
      <input type="checkbox" name="has_pregnant" value="1" <?= !empty($_POST['has_pregnant']) ? 'checked' : '' ?>>
      <span>Pregnant woman in household</span>
    </label>
  </div>

  <div class="f-group">
    <label class="f-label">Home Location Pin <span style="font-size:.75rem;color:#94a3b8;font-weight:400;">(optional but recommended)</span></label>
    <div id="reg-map" style="height:220px;border-radius:12px;border:1.5px solid #e2e8f0;margin-bottom:8px;"></div>
    <button type="button" id="btn-locate"
      style="display:flex;align-items:center;gap:7px;height:36px;padding:0 16px;background:#eff6ff;color:#2563eb;border:1.5px solid #bfdbfe;border-radius:8px;font-size:.8rem;font-weight:600;cursor:pointer;font-family:inherit;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="3"/><path d="M12 2v3m0 14v3M2 12h3m14 0h3"/></svg>
      Use My Current Location
    </button>
    <div id="locate-status" style="font-size:.75rem;color:#64748b;margin-top:5px;"></div>
    <input type="hidden" name="home_lat" id="r_lat">
    <input type="hidden" name="home_lng" id="r_lng">
  </div>

  <div style="margin-top: 2rem;">
    <label class="f-check">
      <input type="checkbox" name="confirm_terms" id="r_terms" value="1" <?= !empty($_POST['confirm_terms']) ? 'checked' : '' ?> required>
      <span>I have read and agree to the <strong>Terms of Service</strong> and <strong>Data Privacy Policy</strong>. I understand that my personal data will be processed in accordance with the Data Privacy Act of 2012.</span>
    </label>
    <?php if (isset($errors['confirm_terms'])): ?><div class="f-err" style="margin-top:4px;"><?= e($errors['confirm_terms']) ?></div><?php endif; ?>
  </div>

  <div class="privacy-box">
    <strong>Data Privacy Notice:</strong> ResQNow collects and processes your personal information (Name, Contact, Address, Household details) solely for emergency response and barangay management purposes. We implement strict security measures to protect your data. By creating an account, you consent to this collection and use.
  </div>

  <button type="submit" class="f-btn" style="margin-top:2rem;">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
      <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/>
    </svg>
    Create Account
  </button>

  <div class="f-foot">Already have an account? <a href="<?= base_url('app/login') ?>">Sign In</a></div>
</form>

<script>
(function () {
  /* ── Password visibility toggle ── */
  function eye(btnId, inpId, onId, offId) {
    var b = document.getElementById(btnId),
        i = document.getElementById(inpId),
        s = document.getElementById(onId),
        h = document.getElementById(offId);
    if (!b) return;
    b.addEventListener('click', function () {
      var t = i.type === 'text';
      i.type = t ? 'password' : 'text';
      s.style.display = t ? '' : 'none';
      h.style.display = t ? 'none' : '';
    });
  }
  eye('pw-a-btn', 'r_pw',  'pw-a-on', 'pw-a-off');
  eye('pw-b-btn', 'r_pwc', 'pw-b-on', 'pw-b-off'); */
  var pwInput = document.getElementById('r_pw');
  var meterWrap = document.getElementById('pw-meter-wrap');
  var meterLabel = document.getElementById('pw-meter-label');
  var reqs = {
    len:   document.getElementById('req-len'),
    upper: document.getElementById('req-upper'),
    lower: document.getElementById('req-lower'),
    num:   document.getElementById('req-num'),
    spec:  document.getElementById('req-spec')
  };
  var segs = [
    document.getElementById('pw-seg-1'),
    document.getElementById('pw-seg-2'),
    document.getElementById('pw-seg-3'),
    document.getElementById('pw-seg-4')
  ];

  function checkStrength(pw) {
    var checks = {
      len:   pw.length >= 8,
      upper: /[A-Z]/.test(pw),
      lower: /[a-z]/.test(pw),
      num:   /[0-9]/.test(pw),
      spec:  /[^a-zA-Z0-9]/.test(pw)
    };
    var score = Object.values(checks).filter(Boolean).length;

    /* Update requirement list */
    Object.keys(reqs).forEach(function (k) {
      if (reqs[k]) reqs[k].classList.toggle('met', checks[k]);
    });

    /* Update bar segments */
    var labels = ['Too weak', 'Weak', 'Fair', 'Good', 'Strong'];
    var cls    = ['', 'weak', 'fair', 'good', 'strong'];
    segs.forEach(function (seg, i) {
      seg.classList.remove('active', 's1', 's2', 's3', 's4');
      if (i < score) {
        seg.classList.add('active');
        seg.classList.add('s' + score);
      }
    });

    meterLabel.textContent = pw.length === 0 ? 'Enter a password' : labels[score];
    meterLabel.className = 'pw-meter-label ' + (pw.length === 0 ? '' : cls[score]);
  }

  if (pwInput) {
    pwInput.addEventListener('input', function () {
      meterWrap.style.display = this.value.length > 0 ? '' : 'none';
      checkStrength(this.value);
    });
    /* Also show on focus even if empty */
    pwInput.addEventListener('focus', function () {
      meterWrap.style.display = '';
    });
  }
})();
</script>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function() {
  // Default center: Barangay Camunatan, Ilagan City
  var DEFAULT = [17.1493, 121.8891];
  var map = L.map('reg-map').setView(DEFAULT, 15);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap', maxZoom: 19
  }).addTo(map);

  var marker = null;
  var latInput = document.getElementById('r_lat');
  var lngInput = document.getElementById('r_lng');
  var status   = document.getElementById('locate-status');

  function setPin(lat, lng, msg) {
    if (marker) map.removeLayer(marker);
    marker = L.marker([lat, lng], { draggable: true }).addTo(map);
    marker.bindPopup('Drag to adjust').openPopup();
    marker.on('dragend', function(e) {
      var p = e.target.getLatLng();
      latInput.value = p.lat.toFixed(7);
      lngInput.value = p.lng.toFixed(7);
      status.textContent = 'Pin set: ' + p.lat.toFixed(5) + ', ' + p.lng.toFixed(5);
    });
    latInput.value = lat.toFixed(7);
    lngInput.value = lng.toFixed(7);
    map.setView([lat, lng], 17);
    status.textContent = msg || ('Pin set: ' + lat.toFixed(5) + ', ' + lng.toFixed(5));
    status.style.color = '#16a34a';
  }

  // Click on map to place pin
  map.on('click', function(e) {
    setPin(e.latlng.lat, e.latlng.lng, 'Pin placed — drag to adjust.');
  });

  // GPS locate button
  document.getElementById('btn-locate').addEventListener('click', function() {
    status.textContent = 'Getting your location…';
    status.style.color = '#64748b';
    if (!navigator.geolocation) {
      status.textContent = 'Geolocation not supported by this browser.';
      status.style.color = '#dc2626';
      return;
    }
    navigator.geolocation.getCurrentPosition(
      function(pos) {
        setPin(pos.coords.latitude, pos.coords.longitude, 'Location found — drag pin to adjust if needed.');
      },
      function(err) {
        status.textContent = 'Could not get location. Please pin manually on the map.';
        status.style.color = '#dc2626';
      },
      { enableHighAccuracy: true, timeout: 10000 }
    );
  });

  // Invalidate map size after page fully renders
  setTimeout(function() { map.invalidateSize(); }, 300);
})();
</script>
