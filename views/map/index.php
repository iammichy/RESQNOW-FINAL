<?php
// Triage colour map used for both PHP legend and JS inline
$triageColors = [
    'critical'             => '#dc2626',
    'high'                 => '#ea580c',
    'moderate'             => '#d97706',
    'low'                  => '#16a34a',
    'pending_reassessment' => '#475569',
];
?>

<style>
/* ── Map page layout ──────────────────────────────────────── */
.map-page { display:flex; flex-direction:column; height:calc(100vh - 56px); overflow:hidden; }

/* Filter bar */
.map-filterbar {
  flex-shrink:0;
  background:#fff;
  border-bottom:1px solid #e2e8f0;
  padding:8px 16px;
  display:flex; align-items:center; flex-wrap:wrap; gap:8px;
}
.map-filterbar select,
.map-filterbar input[type=date] {
  height:32px; border:1.5px solid #e2e8f0; border-radius:5px;
  padding:0 8px; font-size:.78rem; color:#0f172a; background:#f8fafc;
  outline:none; transition:border-color .12s;
}
.map-filterbar select:focus,
.map-filterbar input[type=date]:focus { border-color:#dc2626; background:#fff; }

.map-filter-btn {
  height:32px; padding:0 14px;
  background:#0f172a; color:#fff;
  border:none; border-radius:5px;
  font-size:.78rem; font-weight:700; cursor:pointer;
  transition:background .12s;
}
.map-filter-btn:hover { background:#1e293b; }

.map-toggle {
  display:flex; align-items:center; gap:7px;
  font-size:.78rem; font-weight:600; color:#374151;
  cursor:pointer; user-select:none;
}
.map-toggle input[type=checkbox] { width:16px; height:16px; accent-color:#2563eb; cursor:pointer; }

.map-sep { width:1px; height:28px; background:#e2e8f0; flex-shrink:0; }

/* Map container — fills remaining height */
.map-wrap {
  flex:1;
  position:relative;
  overflow:hidden;
}
#map { height:100%; width:100%; }

/* Stat bar at bottom of map */
.map-statbar {
  position:absolute; bottom:0; left:0; right:0; z-index:800;
  background:rgba(15,23,42,.85);
  backdrop-filter:blur(6px);
  display:flex; align-items:center; gap:0;
  height:38px;
  border-top:1px solid rgba(255,255,255,.06);
}
.map-stat {
  flex:1; display:flex; align-items:center; justify-content:center; gap:7px;
  font-size:.72rem; color:rgba(255,255,255,.75); border-right:1px solid rgba(255,255,255,.06);
  padding:0 8px;
}
.map-stat:last-child { border-right:none; }
.map-stat strong { color:#fff; font-size:.85rem; }
.map-stat-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }

/* Legend tooltip */
.map-legend {
  position:absolute; top:12px; left:12px; z-index:800;
  background:#fff; border:1px solid #e2e8f0; border-radius:8px;
  padding:10px 14px; box-shadow:0 4px 16px rgba(0,0,0,.12);
  min-width:160px;
}
.map-legend-title {
  font-size:.65rem; font-weight:700; text-transform:uppercase;
  letter-spacing:.05em; color:#94a3b8; margin-bottom:8px;
}
.map-legend-row {
  display:flex; align-items:center; gap:7px;
  font-size:.76rem; color:#374151; margin-bottom:5px;
}
.map-legend-row:last-child { margin-bottom:0; }
.map-legend-dot { width:10px; height:10px; border-radius:50%; flex-shrink:0; }

/* Leaflet popup premium */
.resq-popup { font-family:'Inter',sans-serif; min-width:200px; }
.resq-popup-code { font-weight:800; font-size:.875rem; color:#0f172a; margin-bottom:4px; font-family:monospace; }
.resq-popup-row { font-size:.75rem; color:#475569; margin-bottom:3px; display:flex; align-items:center; gap:5px; }
.resq-popup-triage { display:inline-block; border-radius:3px; padding:1px 7px; font-size:.65rem; font-weight:700; color:#fff; }
.resq-popup-link {
  display:block; margin-top:8px; text-align:center; height:30px; line-height:30px;
  background:#0f172a; color:#fff; border-radius:5px; font-size:.75rem;
  font-weight:700; text-decoration:none;
}
.resq-popup-link:hover { background:#1e293b; color:#fff; }

/* Responder popup */
.resp-popup { font-family:'Inter',sans-serif; min-width:170px; }
.resp-popup-name { font-weight:800; font-size:.875rem; color:#1d4ed8; margin-bottom:4px; }
.resp-popup-row  { font-size:.75rem; color:#475569; margin-bottom:3px; }

@keyframes resp-map-pulse {
  0%   { transform:scale(1);   opacity:.7; }
  100% { transform:scale(2.4); opacity:0; }
}
@keyframes pend-pulse {
  0%,100%{ opacity:1; } 50%{ opacity:.4; }
}
</style>

<div class="map-page">

  <!-- Filter bar -->
  <div class="map-filterbar">

    <select id="f-status" title="Status">
      <option value="active">Active Only</option>
      <option value="all">All Statuses</option>
      <option value="submitted">Submitted</option>
      <option value="verified">Verified</option>
      <option value="assigned">Assigned</option>
      <option value="on_scene">On Scene</option>
      <option value="in_progress">In Progress</option>
      <option value="resolved">Resolved</option>
    </select>

    <select id="f-hazard" title="Hazard Type">
      <option value="">All Hazards</option>
      <option value="fire">Fire</option>
      <option value="flood">Flood</option>
      <option value="earthquake">Earthquake</option>
      <option value="medical">Medical</option>
      <option value="accident">Accident</option>
      <option value="evacuation">Evacuation</option>
      <option value="security">Security</option>
      <option value="other">Other</option>
    </select>

    <select id="f-triage" title="Triage Level">
      <option value="">All Levels</option>
      <option value="critical">Critical</option>
      <option value="high">High</option>
      <option value="moderate">Moderate</option>
      <option value="low">Low</option>
      <option value="pending_reassessment">Needs Reassessment</option>
    </select>

    <div style="display:flex;align-items:center;gap:5px;flex-shrink:0;">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      <input type="date" id="f-from" title="From">
      <span style="color:#cbd5e1;font-size:.8rem;">—</span>
      <input type="date" id="f-to" title="To">
    </div>

    <button class="map-filter-btn" id="btn-filter">Apply</button>

    <div class="map-sep"></div>

    <label class="map-toggle">
      <input type="checkbox" id="show-responders" checked>
      <span style="width:8px;height:8px;border-radius:50%;background:#2563eb;display:inline-block;"></span>
      Responders
    </label>

    <div style="margin-left:auto;font-size:.72rem;color:#94a3b8;" id="map-last-updated"></div>

  </div>

  <!-- Map -->
  <div class="map-wrap">
    <div id="map"></div>

    <!-- Legend — top left, clear of zoom controls -->
    <div class="map-legend" style="top:12px;left:12px;right:auto;">
      <div class="map-legend-title">Triage Level</div>
      <?php foreach ($triageColors as $level => $color): ?>
        <div class="map-legend-row">
          <div class="map-legend-dot" style="background:<?= $color ?>;"></div>
          <?= ucwords(str_replace('_',' ',$level)) ?>
        </div>
      <?php endforeach; ?>
      <div style="margin-top:8px;padding-top:8px;border-top:1px solid #f1f5f9;">
        <div class="map-legend-row">
          <div class="map-legend-dot" style="background:#2563eb;"></div>
          Responder
        </div>
      </div>
    </div>

    <!-- Recenter + no-GPS notice — bottom left, above contacts -->
    <div style="position:absolute;bottom:170px;left:12px;z-index:800;display:flex;flex-direction:column;gap:6px;min-width:200px;">
      <button id="btn-recenter" style="height:30px;background:#fff;border:1px solid #e2e8f0;border-radius:6px;font-size:.72rem;font-weight:600;color:#475569;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,.08);">
        ⊕ Recenter on Camunatan
      </button>
      <div id="map-no-gps" style="display:none;padding:6px 10px;background:#fffbeb;border:1px solid #fde68a;border-radius:6px;font-size:.67rem;color:#92400e;line-height:1.4;">
        <strong><span id="map-no-gps-count">0</span></strong> incident(s) have no GPS — not shown.
      </div>
    </div>

    <!-- Emergency contact reference -->
    <div style="position:absolute;bottom:50px;left:12px;z-index:800;background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;box-shadow:0 4px 16px rgba(0,0,0,.1);min-width:200px;max-width:220px;">
      <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;margin-bottom:8px;">Emergency Contacts</div>
      <?php foreach ([
        ['Barangay Hotline', '0997 692 0615'],
        ['CDRRMO Rescue',    '0915 234 1124'],
        ['BFP Fire Station', '0953 056 3939'],
        ['Red Cross',        '143'],
        ['PNP Emergency',    '117'],
      ] as [$label, $num]): ?>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;font-size:.73rem;">
          <span style="color:#475569;"><?= $label ?></span>
          <strong style="color:#0f172a;font-family:monospace;margin-left:12px;"><?= $num ?></strong>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- Stats bar -->
    <div class="map-statbar">
      <div class="map-stat">
        <div class="map-stat-dot" style="background:#dc2626;"></div>
        <span>Critical: <strong id="stat-critical">0</strong></span>
      </div>
      <div class="map-stat">
        <div class="map-stat-dot" style="background:#ea580c;"></div>
        <span>High: <strong id="stat-high">0</strong></span>
      </div>
      <div class="map-stat">
        <div class="map-stat-dot" style="background:#d97706;"></div>
        <span>Moderate: <strong id="stat-moderate">0</strong></span>
      </div>
      <div class="map-stat">
        <div class="map-stat-dot" style="background:#16a34a;"></div>
        <span>Low: <strong id="stat-low">0</strong></span>
      </div>
      <div class="map-stat">
        <div class="map-stat-dot" style="background:#2563eb;animation:pend-pulse 1.4s infinite;"></div>
        <span>Responders: <strong id="stat-resp">0</strong></span>
      </div>
      <div class="map-stat">
        Total: <strong id="stat-total">0</strong>
      </div>
    </div>

  </div>

</div>

<!-- MarkerCluster — local CDN fallback -->
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
<link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">

<?php
$extraHead = ($extraHead ?? '') .
  '<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>';
?>

<?php
$extraScripts = $extraScripts ?? '';
ob_start();
?>
<script>
(function() {
  // ── Map init ───────────────────────────────────────────────────
  // Center on Barangay Camunatan, City of Ilagan, Isabela
  var CAMUNATAN = [17.1493, 121.8891];
  var map = L.map('map', { zoomControl: false }).setView(CAMUNATAN, 15);
  window._resqMap = map; // expose for recenter button
  L.control.zoom({ position: 'bottomright' }).addTo(map);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a>',
    maxZoom: 19
  }).addTo(map);

  var BASE = '<?= base_url('') ?>';

  // Cluster group for incidents
  var clusterGroup = L.markerClusterGroup({ maxClusterRadius: 40, disableClusteringAtZoom: 15 });
  map.addLayer(clusterGroup);

  // Responder layer (not clustered)
  var respLayer = L.layerGroup().addTo(map);
  var respPins  = {};

  // ── Triage colours ─────────────────────────────────────────────
  var TC = <?= json_encode($triageColors) ?>;

  function triageColor(level) { return TC[level] || '#475569'; }

  // ── Incident marker ────────────────────────────────────────────
  function makeIncidentMarker(inc) {
    var c = triageColor(inc.triage_level);
    var isPending = inc.triage_level === 'pending_reassessment';

    var icon = L.divIcon({
      html: '<div style="position:relative;">' +
        '<div style="width:14px;height:14px;border-radius:50%;background:' + c + ';' +
        'border:2.5px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,.25);position:relative;z-index:2;' +
        (isPending ? 'animation:pend-pulse 1.4s infinite;' : '') + '"></div>' +
        '</div>',
      className: '', iconSize: [14,14], iconAnchor: [7,7]
    });

    var statusLabel = inc.status.replace(/_/g,' ');
    var popup = '<div class="resq-popup">' +
      '<div class="resq-popup-code">' + inc.code + '</div>' +
      '<div class="resq-popup-row">' +
        '<span class="resq-popup-triage" style="background:' + c + ';">' + inc.triage_level.replace(/_/g,' ') + '</span>' +
        '<span style="text-transform:capitalize;">' + inc.hazard_type + '</span>' +
      '</div>' +
      '<div class="resq-popup-row">' +
        '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>' +
        (inc.address || 'Location pinned') +
      '</div>' +
      '<div class="resq-popup-row">Status: <strong>' + statusLabel + '</strong></div>' +
      (inc.assigned_team ? '<div class="resq-popup-row">Team: ' + inc.assigned_team + '</div>' : '') +
      '<a href="' + BASE + 'reports/' + inc.code + '" class="resq-popup-link">View Report →</a>' +
      '</div>';

    var m = L.marker([parseFloat(inc.location_lat), parseFloat(inc.location_lng)], { icon: icon });
    m.bindPopup(popup, { maxWidth: 240 });
    return m;
  }

  // ── Responder marker ────────────────────────────────────────────
  function respIcon() {
    return L.divIcon({
      html: '<div style="position:relative;">' +
        '<div style="width:16px;height:16px;border-radius:50%;background:#2563eb;border:3px solid #fff;' +
        'box-shadow:0 2px 8px rgba(37,99,235,.55);position:relative;z-index:2;"></div>' +
        '<div style="position:absolute;top:-5px;left:-5px;width:26px;height:26px;border-radius:50%;' +
        'background:rgba(37,99,235,.18);animation:resp-map-pulse 2s infinite;"></div>' +
        '</div>',
      className: '', iconSize: [16,16], iconAnchor: [8,8]
    });
  }

  // ── Load incidents ──────────────────────────────────────────────
  function loadIncidents() {
    var params = new URLSearchParams({
      status:       document.getElementById('f-status').value,
      hazard_type:  document.getElementById('f-hazard').value,
      triage_level: document.getElementById('f-triage').value,
      date_from:    document.getElementById('f-from').value,
      date_to:      document.getElementById('f-to').value,
    });

    fetch(BASE + 'api/v1/map/data?' + params.toString(), { credentials: 'same-origin' })
      .then(function(r){ return r.ok ? r.json() : Promise.reject(r.status); })
      .then(function(data) {
        clusterGroup.clearLayers();

        var counts = { critical:0, high:0, moderate:0, low:0 };
        var noGps = 0;
        (data.incidents || []).forEach(function(inc) {
          if (!inc.location_lat || !inc.location_lng) { noGps++; return; }
          clusterGroup.addLayer(makeIncidentMarker(inc));
          if (counts[inc.triage_level] !== undefined) counts[inc.triage_level]++;
        });

        document.getElementById('stat-critical').textContent = counts.critical;
        document.getElementById('stat-high').textContent     = counts.high;
        document.getElementById('stat-moderate').textContent = counts.moderate;
        document.getElementById('stat-low').textContent      = counts.low;
        document.getElementById('stat-total').textContent    = clusterGroup.getLayers().length;

        // No-GPS notice
        var noGpsEl = document.getElementById('map-no-gps');
        var noGpsCnt = document.getElementById('map-no-gps-count');
        if (noGps > 0) {
          noGpsEl.style.display = '';
          noGpsCnt.textContent = noGps;
        } else {
          noGpsEl.style.display = 'none';
        }

        if (clusterGroup.getLayers().length > 0) {
          try { map.fitBounds(clusterGroup.getBounds(), { padding:[60,60], maxZoom:15 }); } catch(e){}
        } else {
          // No GPS pins — recenter on Camunatan
          map.setView(CAMUNATAN, 15);
        }

        var now = new Date();
        document.getElementById('map-last-updated').textContent =
          'Updated ' + now.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit',second:'2-digit'});
      })
      .catch(function(e){ console.error('Map data error:', e); });
  }

  // ── Load responders ─────────────────────────────────────────────
  function loadResponders() {
    if (!document.getElementById('show-responders').checked) {
      respLayer.clearLayers(); respPins = {};
      document.getElementById('stat-resp').textContent = '0';
      return;
    }
    fetch(BASE + 'api/v1/monitoring/locations', { credentials: 'same-origin' })
      .then(function(r){ return r.ok ? r.json() : null; })
      .then(function(d) {
        if (!d || !d.responders) return;
        var seen = {};
        d.responders.forEach(function(r) {
          seen[r.team_id] = true;
          var ago = r.last_seen_seconds !== null ? r.last_seen_seconds + 's ago' : 'unknown';
          var popupHtml =
            '<div class="resp-popup">' +
            '<div class="resp-popup-name">' + r.team_name + '</div>' +
            '<div class="resp-popup-row">Last seen: ' + ago + '</div>' +
            (r.active_incident
              ? '<div class="resp-popup-row" style="color:#dc2626;">Mission: ' + r.active_incident + '</div>' +
                '<a href="' + BASE + 'reports/' + r.active_incident + '" style="display:block;margin-top:6px;font-size:.72rem;font-weight:600;color:#2563eb;text-decoration:none;">View Incident →</a>'
              : '') +
            '</div>';
          if (respPins[r.team_id]) {
            respPins[r.team_id].setLatLng([r.lat, r.lng]);
            respPins[r.team_id].setPopupContent(popupHtml);
          } else {
            respPins[r.team_id] = L.marker([r.lat, r.lng], { icon: respIcon(), zIndexOffset: 2000 })
              .bindPopup(popupHtml, { maxWidth: 220 })
              .addTo(respLayer);
          }
        });
        Object.keys(respPins).forEach(function(tid) {
          if (!seen[tid]) { respLayer.removeLayer(respPins[tid]); delete respPins[tid]; }
        });
        document.getElementById('stat-resp').textContent = Object.keys(respPins).length;
      })
      .catch(function(){});
  }

  // ── Event bindings ──────────────────────────────────────────────
  document.getElementById('btn-filter').addEventListener('click', loadIncidents);
  document.getElementById('show-responders').addEventListener('change', loadResponders);
  document.getElementById('btn-recenter').addEventListener('click', function() {
    map.setView(CAMUNATAN, 15);
  });

  // ── Initial load ────────────────────────────────────────────────
  loadIncidents();
  loadResponders();

  // ── Auto-refresh every 30s ──────────────────────────────────────
  setInterval(function() { loadIncidents(); loadResponders(); }, 30000);
  // Responder positions faster: every 5s
  setInterval(loadResponders, 5000);
})();
</script>
<?php $extraScripts .= ob_get_clean(); ?>
