<?php $assignmentId = $assignmentId ?? ''; ?>

<!-- Location permission banner (hidden by default) -->
<div id="loc-denied-banner" style="display:none;background:#fffbeb;border-bottom:1px solid #fde68a;padding:10px 16px;font-size:.78rem;color:#92400e;text-align:center;">
  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:-2px;margin-right:5px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
  Location access denied. Enable GPS to share your position.
</div>

<div style="padding:12px 16px 0;">
  <a href="<?= base_url('app/responder') ?>" style="font-size:.8rem;font-weight:600;color:#64748b;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
    Missions
  </a>
</div>

<div id="loading" style="text-align:center;padding:48px 20px;color:#94a3b8;">
  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" style="animation:spin 1s linear infinite;">
    <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
  </svg>
  <div style="margin-top:8px;font-size:.8rem;">Loading mission…</div>
</div>
<style>@keyframes spin{to{transform:rotate(360deg)}}</style>

<div id="content" style="display:none;">

  <!-- Action bar -->
  <div style="padding:12px 16px 0;" id="action-bar"></div>

  <!-- Info card -->
  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;margin:12px 16px 0;overflow:hidden;">
    <div style="padding:14px 16px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:flex-start;">
      <div>
        <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;margin-bottom:2px;">Report Code</div>
        <div style="font-size:1rem;font-weight:800;color:#0f172a;font-family:monospace;" id="det-code"></div>
      </div>
      <div id="det-triage"></div>
    </div>

    <div style="padding:14px 16px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
      <div>
        <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;margin-bottom:2px;">Reporter</div>
        <div style="font-size:.875rem;font-weight:600;color:#0f172a;" id="det-reporter-name"></div>
        <div style="font-size:.75rem;color:#64748b;" id="det-reporter-phone"></div>
      </div>
      <a href="#" id="btn-call-reporter"
         style="display:inline-flex;align-items:center;gap:6px;height:34px;padding:0 14px;background:#16a34a;color:#fff;border-radius:6px;font-size:.78rem;font-weight:700;text-decoration:none;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.4a2 2 0 0 1 1.99-2.18h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.96a16 16 0 0 0 6.13 6.13l1.82-1.82a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
        Call
      </a>
    </div>

    <div style="padding:14px 16px;">
      <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;margin-bottom:4px;">Description</div>
      <div style="font-size:.8125rem;color:#374151;line-height:1.55;" id="det-description"></div>
    </div>
  </div>

  <!-- Map + Navigate -->
  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;margin:12px 16px 0;overflow:hidden;">
    <div id="det-map" style="height:200px;"></div>
    <div style="padding:12px 16px;border-top:1px solid #f1f5f9;">
      <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;margin-bottom:4px;">Incident Address</div>
      <div style="font-size:.8125rem;font-weight:600;color:#0f172a;margin-bottom:10px;" id="det-address"></div>
      <!-- Navigate buttons -->
      <div style="display:flex;gap:8px;">
        <a href="#" id="btn-google-maps"
           style="flex:1;display:inline-flex;align-items:center;justify-content:center;gap:6px;height:36px;background:#4285F4;color:#fff;border-radius:6px;font-size:.78rem;font-weight:700;text-decoration:none;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="10" r="3"/><path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 14 8 14s8-8.75 8-14a8 8 0 0 0-8-8z"/></svg>
          Google Maps
        </a>
        <a href="#" id="btn-waze"
           style="flex:1;display:inline-flex;align-items:center;justify-content:center;gap:6px;height:36px;background:#33ccff;color:#fff;border-radius:6px;font-size:.78rem;font-weight:700;text-decoration:none;">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          Waze
        </a>
      </div>
    </div>
  </div>

  <!-- Timeline -->
  <div style="padding:16px 16px 8px;font-size:.8125rem;font-weight:700;color:#0f172a;">Mission Timeline</div>
  <div style="background:#fff;border:1px solid #e2e8f0;border-radius:8px;margin:0 16px 80px;" id="det-timeline"></div>

</div>

<!-- Support Modal -->
<div id="supportModal" style="display:none;position:fixed;inset:0;z-index:1050;background:rgba(0,0,0,.45);">
  <div style="position:absolute;bottom:0;left:0;right:0;background:#fff;border-radius:16px 16px 0 0;padding:20px;">
    <div style="font-size:1rem;font-weight:800;color:#0f172a;margin-bottom:16px;">Request Support</div>
    <label style="font-size:.75rem;font-weight:600;color:#64748b;display:block;margin-bottom:5px;">Support Type</label>
    <select id="support_type" style="width:100%;height:44px;border:1.5px solid #e2e8f0;border-radius:6px;padding:0 12px;font-size:.875rem;margin-bottom:12px;">
      <option value="backup">Backup Personnel</option>
      <option value="ambulance">Ambulance</option>
      <option value="fire_truck">Fire Truck</option>
      <option value="additional_personnel">Additional Personnel</option>
    </select>
    <div style="display:flex;gap:8px;margin-top:8px;">
      <button onclick="document.getElementById('supportModal').style.display='none'"
        style="flex:1;height:44px;background:#f1f5f9;border:none;border-radius:6px;font-size:.875rem;font-weight:600;color:#475569;cursor:pointer;">Cancel</button>
      <button id="btn-confirm-support"
        style="flex:2;height:44px;background:#dc2626;border:none;border-radius:6px;font-size:.875rem;font-weight:700;color:#fff;cursor:pointer;">Send Request</button>
    </div>
  </div>
</div>

<!-- Resolve Modal -->
<div id="resolveModal" style="display:none;position:fixed;inset:0;z-index:1050;background:rgba(0,0,0,.45);">
  <div style="position:absolute;bottom:0;left:0;right:0;background:#fff;border-radius:16px 16px 0 0;padding:20px;">
    <div style="font-size:1rem;font-weight:800;color:#0f172a;margin-bottom:16px;">Mark Resolved</div>
    <label style="font-size:.75rem;font-weight:600;color:#64748b;display:block;margin-bottom:5px;">Resolution Summary <span style="color:#dc2626;">*</span></label>
    <textarea id="resolution_summary"
      style="width:100%;height:80px;border:1.5px solid #e2e8f0;border-radius:6px;padding:10px 12px;font-size:.875rem;resize:none;margin-bottom:12px;"
      placeholder="Describe how the incident was resolved…"></textarea>
    <div style="display:flex;gap:8px;">
      <button onclick="document.getElementById('resolveModal').style.display='none'"
        style="flex:1;height:44px;background:#f1f5f9;border:none;border-radius:6px;font-size:.875rem;font-weight:600;color:#475569;cursor:pointer;">Cancel</button>
      <button id="btn-confirm-resolve"
        style="flex:2;height:44px;background:#16a34a;border:none;border-radius:6px;font-size:.875rem;font-weight:700;color:#fff;cursor:pointer;">Confirm Resolved</button>
    </div>
  </div>
</div>

<script>
(function() {
  var AID   = '<?= e($assignmentId) ?>';
  var CSRF  = '<?= csrf_token() ?>';
  var BASE  = '<?= base_url('') ?>';
  var missionData = null;
  var map = null;
  var incidentMarker = null;
  var watchId = null;
  var lastLocationPost = 0;
  var THROTTLE_MS = 8000; // post at most every 8s

  // ── Load mission ────────────────────────────────────────────────────
  function loadMission() {
    fetch(BASE + 'api/v1/responder/missions/' + AID, { credentials: 'same-origin' })
      .then(function(r){ return r.json(); })
      .then(function(data){
        if (data.error) { alert(data.error); window.location.href = BASE + 'app/responder'; return; }
        missionData = data;
        render(data);
        document.getElementById('loading').style.display  = 'none';
        document.getElementById('content').style.display  = '';
        setTimeout(function(){ initMap(data.incident.location_lat, data.incident.location_lng); }, 100);
      })
      .catch(function(){ document.getElementById('loading').innerHTML = '<p style="color:#dc2626;">Failed to load. <a href="javascript:loadMission()">Retry</a></p>'; });
  }

  // ── Render ──────────────────────────────────────────────────────────
  function render(data) {
    var inc = data.incident;
    document.getElementById('det-code').textContent        = inc.code;
    document.getElementById('det-reporter-name').textContent = inc.reporter_name || 'Anonymous';
    document.getElementById('det-reporter-phone').textContent = inc.reporter_phone || '';
    document.getElementById('det-description').textContent = inc.description || 'No description.';
    document.getElementById('det-address').textContent     = inc.address || '—';
    document.getElementById('btn-call-reporter').href      = inc.reporter_phone ? 'tel:' + inc.reporter_phone : '#';

    // Triage badge
    var triageColors = { critical:'#dc2626', high:'#ea580c', moderate:'#d97706', low:'#16a34a', pending_reassessment:'#475569' };
    var tc = triageColors[inc.triage_level] || '#475569';
    document.getElementById('det-triage').innerHTML =
      '<span style="background:' + tc + '20;color:' + tc + ';border:1px solid ' + tc + '40;border-radius:4px;padding:3px 10px;font-size:.65rem;font-weight:700;text-transform:uppercase;">' +
      inc.triage_level.replace('_',' ') + '</span>';

    // Navigate buttons
    if (inc.location_lat && inc.location_lng) {
      var ll = inc.location_lat + ',' + inc.location_lng;
      document.getElementById('btn-google-maps').href = 'https://www.google.com/maps/dir/?api=1&destination=' + ll + '&travelmode=driving';
      document.getElementById('btn-waze').href        = 'https://waze.com/ul?ll=' + ll + '&navigate=yes';
    }

    renderActions(data);
    renderTimeline(data.timeline || []);
  }

  // ── Action bar ──────────────────────────────────────────────────────
  function renderActions(data) {
    var bar = document.getElementById('action-bar');
    var inc = data.incident;
    var aStatus = data.assignment_status;

    if (inc.status === 'resolved' || inc.status === 'closed') {
      bar.innerHTML = '<div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;text-align:center;font-weight:700;color:#16a34a;font-size:.875rem;">Mission Completed</div>';
      stopTracking();
      return;
    }
    startTracking();

    var btns = [];
    if (aStatus === 'active' && inc.status === 'assigned') {
      btns.push(btn('Acknowledge Mission', '#0f172a', "doAct('acknowledge')"));
    }
    if (['acknowledged','assigned'].includes(inc.status)) {
      btns.push(btn('En Route', '#3b82f6', "doAct('en_route')"));
    }
    if (['acknowledged','assigned','en_route'].includes(inc.status)) {
      btns.push(btn('On Scene', '#14b8a6', "doAct('on_scene')"));
    }
    if (['on_scene','in_progress','acknowledged','assigned'].includes(inc.status)) {
      btns.push(btn('In Progress', '#d97706', "doAct('in_progress')"));
      btns.push(btnOutline('Request Support', '#dc2626', "document.getElementById('supportModal').style.display=''"));
      btns.push(btn('Mark Resolved', '#16a34a', "document.getElementById('resolveModal').style.display=''"));
    }

    bar.innerHTML = '<div style="display:flex;flex-direction:column;gap:8px;">' + btns.join('') + '</div>';
  }

  function btn(label, bg, onclick) {
    return '<button onclick="' + onclick + '" style="width:100%;height:46px;background:' + bg + ';color:#fff;border:none;border-radius:7px;font-size:.875rem;font-weight:700;cursor:pointer;">' + label + '</button>';
  }
  function btnOutline(label, color, onclick) {
    return '<button onclick="' + onclick + '" style="width:100%;height:46px;background:#fff;color:' + color + ';border:1.5px solid ' + color + ';border-radius:7px;font-size:.875rem;font-weight:700;cursor:pointer;">' + label + '</button>';
  }

  // ── Actions ─────────────────────────────────────────────────────────
  window.doAct = function(action) {
    var url, body = { csrf_token: CSRF };
    if (action === 'acknowledge') {
      url = BASE + 'api/v1/missions/' + AID + '/acknowledge';
    } else {
      url = BASE + 'api/v1/missions/' + AID + '/updates';
      body.action_code = action;
      body.message = 'Status updated: ' + action.replace(/_/g,' ');
      body.update_type = 'status_change';
    }
    apiPost(url, body, function(){ loadMission(); });
  };

  document.getElementById('btn-confirm-support').onclick = function() {
    var type = document.getElementById('support_type').value;
    apiPost(BASE + 'api/v1/missions/' + AID + '/support-request',
      { csrf_token: CSRF, support_type: type },
      function(){
        document.getElementById('supportModal').style.display = 'none';
        loadMission();
      });
  };

  document.getElementById('btn-confirm-resolve').onclick = function() {
    var summary = document.getElementById('resolution_summary').value.trim();
    if (!summary) { alert('Resolution summary is required.'); return; }
    apiPost(BASE + 'api/v1/missions/' + AID + '/resolve',
      { csrf_token: CSRF, resolution_summary: summary },
      function(){
        document.getElementById('resolveModal').style.display = 'none';
        stopTracking();
        postLocationStop();
        loadMission();
      });
  };

  function apiPost(url, body, onSuccess) {
    fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
      body: JSON.stringify(body)
    }).then(function(r){ return r.json(); })
      .then(function(d){ if (d.error) { alert(d.error); } else { onSuccess(d); } })
      .catch(function(e){ alert('Network error: ' + e.message); });
  }

  // ── Timeline ────────────────────────────────────────────────────────
  function renderTimeline(items) {
    var el = document.getElementById('det-timeline');
    if (!items.length) {
      el.innerHTML = '<div style="padding:20px;text-align:center;font-size:.8rem;color:#94a3b8;">No updates yet.</div>';
      return;
    }
    el.innerHTML = items.slice().reverse().map(function(item) {
      return '<div style="display:flex;gap:12px;padding:12px 16px;border-bottom:1px solid #f8fafc;">' +
        '<div style="flex-shrink:0;width:8px;height:8px;border-radius:50%;background:#dc2626;margin-top:6px;"></div>' +
        '<div style="flex:1;">' +
          '<div style="font-size:.8125rem;font-weight:600;color:#0f172a;">' + esc(item.text) + '</div>' +
          '<div style="font-size:.72rem;color:#94a3b8;margin-top:2px;">' + esc(item.actor || '') + ' · ' + fmtTime(item.created_at) + '</div>' +
        '</div></div>';
    }).join('');
  }

  // ── Map ─────────────────────────────────────────────────────────────
  function initMap(lat, lng) {
    if (map || !lat || !lng) return;
    map = L.map('det-map', { zoomControl: false, attributionControl: false }).setView([lat, lng], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

    // Incident pin (red)
    var incIcon = L.divIcon({
      html: '<div style="width:14px;height:14px;border-radius:50%;background:#dc2626;border:3px solid #fff;box-shadow:0 2px 6px rgba(220,38,38,.5);"></div>',
      className: '', iconSize: [14,14], iconAnchor: [7,7]
    });
    incidentMarker = L.marker([lat, lng], { icon: incIcon }).addTo(map)
      .bindPopup('Incident Location');
  }

  // ── GPS Tracking ─────────────────────────────────────────────────────
  function startTracking() {
    if (watchId !== null || !navigator.geolocation) return;
    navigator.geolocation.getCurrentPosition(
      function(){ /* granted — start watch */ },
      function(err){ if (err.code === 1) showLocationDenied(); },
      { timeout: 5000 }
    );
    watchId = navigator.geolocation.watchPosition(
      function(pos) {
        var lat = pos.coords.latitude;
        var lng = pos.coords.longitude;
        var acc = pos.coords.accuracy;
        var now = Date.now();
        if (now - lastLocationPost >= THROTTLE_MS) {
          lastLocationPost = now;
          postLocation(lat, lng, acc);
        }
      },
      function(err) {
        if (err.code === 1) { showLocationDenied(); stopTracking(); }
      },
      { enableHighAccuracy: true, maximumAge: 5000, timeout: 15000 }
    );
    // Audit: tracking started
    fetch(BASE + 'api/v1/responder/location', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
      body: JSON.stringify({ lat: 0, lng: 0, accuracy: null, _event: 'start' })
    }).catch(function(){});
  }

  function stopTracking() {
    if (watchId !== null) {
      navigator.geolocation.clearWatch(watchId);
      watchId = null;
    }
  }

  function postLocation(lat, lng, acc) {
    fetch(BASE + 'api/v1/responder/location', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
      body: JSON.stringify({ lat: lat, lng: lng, accuracy: acc })
    }).catch(function(){});
  }

  function postLocationStop() {
    fetch(BASE + 'api/v1/responder/location/stop', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF },
      body: JSON.stringify({})
    }).catch(function(){});
  }

  function showLocationDenied() {
    document.getElementById('loc-denied-banner').style.display = '';
  }

  // Stop tracking when page unloads
  window.addEventListener('beforeunload', function() { stopTracking(); postLocationStop(); });

  // ── Helpers ─────────────────────────────────────────────────────────
  function esc(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
  function fmtTime(ds) {
    var d = new Date((ds || '').replace(' ','T'));
    return isNaN(d) ? '' : d.toLocaleTimeString([], { hour:'2-digit', minute:'2-digit' });
  }

  loadMission();
})();
</script>
