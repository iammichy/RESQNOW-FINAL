<?php
$code = $code ?? '';
?>
<div class="mob-content" id="track-detail-app">
  
  <div class="px-3 mb-3">
    <a href="<?= base_url('app/resident/track') ?>" class="text-decoration-none small fw-bold">← Back to List</a>
  </div>

  <div id="loading" class="text-center py-5">
    <div class="spinner-border text-primary" role="status"></div>
    <div class="mt-2 text-muted">Loading report details...</div>
  </div>

  <div id="content" class="d-none">
    <!-- Header Card -->
    <div class="mob-card">
      <div class="mob-card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <div class="text-muted small">Report Code</div>
            <h2 class="h3 fw-bold mb-0" id="det-code"></h2>
          </div>
          <div id="det-triage"></div>
        </div>
        
        <div class="row g-3">
          <div class="col-6">
            <div class="text-muted small">Status</div>
            <div class="fw-bold" id="det-status"></div>
          </div>
          <div class="col-6 text-end">
            <div class="text-muted small">Hazard</div>
            <div class="fw-bold text-capitalize" id="det-hazard"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Map Card -->
    <div class="mob-card">
      <div class="mob-card-body p-0">
        <div id="det-map" style="height: 200px;"></div>
        <!-- Responder live bar -->
        <div id="resp-bar" style="padding:8px 14px;background:#eff6ff;border-top:1px solid #bfdbfe;font-size:.75rem;color:#1e40af;display:none;align-items:center;gap:6px;">
          <span style="width:8px;height:8px;border-radius:50%;background:#2563eb;display:inline-block;animation:resp-pulse-bar 2s infinite;flex-shrink:0;"></span>
          <span id="resp-bar-text">Checking…</span>
        </div>
        <div class="p-3">
          <div class="text-muted small mb-1">Incident Address</div>
          <div class="fw-semibold" id="det-address"></div>
        </div>
      </div>
    </div>
    <style>
    @keyframes resp-pulse-bar {
      0%,100%{opacity:1;transform:scale(1)}50%{opacity:.4;transform:scale(.8)}
    }
    </style>

    <!-- Description -->
    <div class="mob-card">
      <div class="mob-card-body">
        <div class="text-muted small mb-1">Description</div>
        <div id="det-description" class="small"></div>
      </div>
    </div>

    <!-- Rejection Reason -->
    <div id="det-rejection" class="mob-card d-none border-danger">
      <div class="mob-card-body">
        <div class="text-danger fw-bold small mb-1">Reason for Rejection</div>
        <div id="det-rejection-text" class="small"></div>
      </div>
    </div>

    <!-- Timeline -->
    <div class="px-3 mt-4 mb-2">
      <h3 class="h5 fw-bold">Response Timeline</h3>
    </div>
    <div class="mob-card mb-4">
      <div class="mob-card-body" id="det-timeline">
        <!-- Timeline items -->
      </div>
    </div>
  </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const code = '<?= e($code) ?>';
  let map;

  fetch('<?= base_url('api/v1/resident/incidents/') ?>' + code)
    .then(r => r.json())
    .then(data => {
      if (data.error) {
        alert(data.error);
        window.location.href = '<?= base_url('app/resident/track') ?>';
        return;
      }
      
      renderDetail(data);
      document.getElementById('loading').classList.add('d-none');
      document.getElementById('content').classList.remove('d-none');
      
      // Init map after content is visible
      setTimeout(() => initMap(data.location_lat, data.location_lng), 100);

      // Start polling responder location every 5s
      pollResponder();
      setInterval(pollResponder, 5000);
    })
    .catch(err => {
      console.error(err);
      alert('Could not load details. You might be offline.');
    });

  function renderDetail(data) {
    document.getElementById('det-code').textContent = data.report_code;
    document.getElementById('det-status').textContent = data.status.replace('_', ' ').toUpperCase();
    document.getElementById('det-hazard').textContent = data.hazard_type;
    document.getElementById('det-address').textContent = data.address;
    document.getElementById('det-description').textContent = data.description || 'No additional description provided.';
    
    // Triage badge
    const triage = data.triage_level;
    const triageCls = 'badge-' + triage;
    document.getElementById('det-triage').innerHTML = `<span class="mob-badge ${triageCls}">${triage.replace('_', ' ').toUpperCase()}</span>`;
    
    // Rejection
    if (data.rejection_reason) {
      document.getElementById('det-rejection').classList.remove('d-none');
      document.getElementById('det-rejection-text').textContent = data.rejection_reason;
    }
    
    // Timeline
    const timeline = document.getElementById('det-timeline');
    if (data.timeline.length === 0) {
      timeline.innerHTML = '<div class="text-center text-muted py-3 small">No updates yet.</div>';
    } else {
      data.timeline.reverse().forEach(item => {
        const tDiv = document.createElement('div');
        tDiv.className = 'timeline-item';
        tDiv.innerHTML = `
          <div class="timeline-dot">
            <div class="dot"></div>
            <div class="line"></div>
          </div>
          <div class="timeline-content">
            <div class="d-flex justify-content-between">
              <span class="timeline-text">${item.icon || ''} ${item.text}</span>
              <span class="timeline-sub">${formatTime(item.created_at)}</span>
            </div>
            ${item.note ? `<div class="timeline-sub mt-1">${item.note}</div>` : ''}
            <div class="timeline-sub text-primary mt-1">${item.actor}</div>
          </div>
        `;
        timeline.appendChild(tDiv);
      });
    }
  }

  function initMap(lat, lng) {
    if (!lat || !lng) return;
    map = L.map('det-map', { zoomControl: false, attributionControl: false }).setView([lat, lng], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

    // Incident pin
    var incIcon = L.divIcon({
      html: '<div style="width:12px;height:12px;border-radius:50%;background:#dc2626;border:3px solid #fff;box-shadow:0 2px 6px rgba(220,38,38,.5);"></div>',
      className:'', iconSize:[12,12], iconAnchor:[6,6]
    });
    L.marker([lat, lng], { icon: incIcon }).addTo(map).bindPopup('Incident Location');
  }

  // Live responder tracking — poll every 5s
  var respMarker = null;
  var respCircle = null;
  var incCode = '<?= e($code) ?>';

  function pollResponder() {
    fetch('<?= base_url('api/v1/incidents/') ?>' + incCode + '/responder-location', {
      credentials: 'same-origin'
    })
    .then(function(r){ return r.ok ? r.json() : null; })
    .then(function(d){
      var bar = document.getElementById('resp-bar');
      var txt = document.getElementById('resp-bar-text');
      if (!d || !d.tracking || !d.lat) {
        if (bar) bar.style.display = 'none';
        return;
      }
      if (bar) bar.style.display = 'flex';

      // Responder pin (blue pulsing)
      var rIcon = L.divIcon({
        html: '<div style="position:relative;">' +
                '<div style="width:14px;height:14px;border-radius:50%;background:#2563eb;border:3px solid #fff;box-shadow:0 2px 6px rgba(37,99,235,.6);position:relative;z-index:2;"></div>' +
                '<div style="position:absolute;top:-5px;left:-5px;width:24px;height:24px;border-radius:50%;background:rgba(37,99,235,.2);animation:resp-pulse-bar 2s infinite;"></div>' +
              '</div>',
        className:'', iconSize:[14,14], iconAnchor:[7,7]
      });

      if (!respMarker) {
        if (!map) return;
        respMarker = L.marker([d.lat, d.lng], { icon: rIcon, zIndexOffset: 1000 })
          .addTo(map).bindPopup(d.team_name || 'Responder');
        if (d.accuracy) {
          respCircle = L.circle([d.lat, d.lng], {
            radius: d.accuracy, color:'#2563eb', fillColor:'#3b82f6',
            fillOpacity:.08, weight:1, dashArray:'4'
          }).addTo(map);
        }
      } else {
        respMarker.setLatLng([d.lat, d.lng]);
        if (respCircle) respCircle.setLatLng([d.lat, d.lng]);
      }

      // Update bar text
      var ago = d.last_seen_seconds !== null ? d.last_seen_seconds + 's ago' : '';
      var eta = d.eta_minutes !== null
        ? (d.eta_minutes === 0 ? 'On site' : 'ETA ~' + d.eta_minutes + ' min')
        : '';
      if (txt) txt.textContent =
        (d.team_name || 'Responder') +
        (ago ? ' · last seen ' + ago : '') +
        (eta ? ' · ' + eta : '');
    })
    .catch(function(){});
  }

  function formatTime(ds) {
    const d = new Date(ds.replace(' ', 'T'));
    return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  }
});
</script>
