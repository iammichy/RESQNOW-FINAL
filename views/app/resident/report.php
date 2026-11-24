<?php
$hazardTypes = $hazardTypes ?? [];

// SVG icon map — no emoji, consistent on all devices
function hazard_icon_php(string $type): string {
    $icons = [
        'fire'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>',
        'flood'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12.5c.5-.5 1.5-.5 2 0s1.5.5 2 0 1.5-.5 2 0 1.5.5 2 0 1.5-.5 2 0 1.5.5 2 0"/><path d="M2 17.5c.5-.5 1.5-.5 2 0s1.5.5 2 0 1.5-.5 2 0 1.5.5 2 0 1.5-.5 2 0 1.5.5 2 0"/><path d="M2 7.5c.5-.5 1.5-.5 2 0s1.5.5 2 0 1.5-.5 2 0 1.5.5 2 0 1.5-.5 2 0 1.5.5 2 0"/></svg>',
        'earthquake' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12h3l2-8 3 16 3-10 2 6 2-4h3"/></svg>',
        'medical'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>',
        'accident'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v9a2 2 0 0 1-2 2h-1"/><circle cx="9" cy="17" r="2"/><circle cx="19" cy="17" r="2"/></svg>',
        'evacuation' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'security'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
        'other'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
    ];
    return $icons[$type] ?? $icons['other'];
}

// Color map per hazard type
function hazard_color(string $type): array {
    $colors = [
        'fire'       => ['#fef2f2', '#dc2626'],
        'flood'      => ['#eff6ff', '#2563eb'],
        'earthquake' => ['#fffbeb', '#d97706'],
        'medical'    => ['#fdf4ff', '#9333ea'],
        'accident'   => ['#fff7ed', '#ea580c'],
        'evacuation' => ['#f0fdf4', '#16a34a'],
        'security'   => ['#eef2ff', '#4f46e5'],
        'other'      => ['#f8fafc', '#64748b'],
    ];
    return $colors[$type] ?? $colors['other'];
}
?>
<div class="mob-content" id="report-app">
  
  <div class="px-3 mb-3">
    <div class="step-bar" id="step-indicator">
      <div class="step-dot active"></div>
      <div class="step-dot"></div>
      <div class="step-dot"></div>
      <div class="step-dot"></div>
    </div>
  </div>

  <!-- Step 1: Hazard Type -->
  <div id="step-1" class="report-step">
    <div class="px-3 mb-4">
      <h2 class="h3 fw-bold mb-1">What is the emergency?</h2>
      <p class="text-muted small">Select the type of hazard you are reporting.</p>
    </div>
    <div style="padding:0 16px;">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <?php foreach ($hazardTypes as $type):
          [$bg, $color] = hazard_color($type);
        ?>
          <button class="hazard-btn" data-type="<?= e($type) ?>"
            style="background:#fff;border:1.5px solid #e2e8f0;border-radius:16px;
                   padding:20px 12px;cursor:pointer;text-align:center;
                   transition:border-color .15s,box-shadow .15s;
                   display:flex;flex-direction:column;align-items:center;gap:10px;">
            <div style="width:52px;height:52px;border-radius:14px;background:<?= $bg ?>;
                        display:flex;align-items:center;justify-content:center;flex-shrink:0;">
              <svg width="26" height="26" viewBox="0 0 24 24" fill="none"
                   stroke="<?= $color ?>" stroke-width="1.75"
                   stroke-linecap="round" stroke-linejoin="round">
                <?php
                  $paths = [
                    'fire'       => '<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>',
                    'flood'      => '<path d="M2 12.5c.5-.5 1.5-.5 2 0s1.5.5 2 0 1.5-.5 2 0 1.5.5 2 0 1.5-.5 2 0 1.5.5 2 0"/><path d="M2 17.5c.5-.5 1.5-.5 2 0s1.5.5 2 0 1.5-.5 2 0 1.5.5 2 0 1.5-.5 2 0 1.5.5 2 0"/><path d="M2 7.5c.5-.5 1.5-.5 2 0s1.5.5 2 0 1.5-.5 2 0 1.5.5 2 0 1.5-.5 2 0 1.5.5 2 0"/>',
                    'earthquake' => '<path d="M2 12h3l2-8 3 16 3-10 2 6 2-4h3"/>',
                    'medical'    => '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
                    'accident'   => '<path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v9a2 2 0 0 1-2 2h-1"/><circle cx="9" cy="17" r="2"/><circle cx="19" cy="17" r="2"/>',
                    'evacuation' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
                    'security'   => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
                    'other'      => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
                  ];
                  echo $paths[$type] ?? $paths['other'];
                ?>
              </svg>
            </div>
            <span style="font-size:.85rem;font-weight:700;color:#0f172a;text-transform:capitalize;"><?= e($type) ?></span>
          </button>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Step 2: Dynamic Questions -->
  <div id="step-2" class="report-step d-none">
    <div class="px-3 mb-4">
      <h2 class="h3 fw-bold mb-1">Tell us more</h2>
      <p class="text-muted small">Answer a few questions to help us prioritize.</p>
    </div>
    <div id="questions-container" class="px-3">
      <!-- Injected by JS -->
    </div>
    <div class="px-3 mt-4 d-flex gap-2">
      <button class="mob-btn mob-btn-secondary prev-btn">Back</button>
      <button class="mob-btn next-btn">Next</button>
    </div>
  </div>

  <!-- Step 3: Location -->
  <div id="step-3" class="report-step d-none">
    <div class="px-3 mb-4">
      <h2 class="h3 fw-bold mb-1">Where is it happening?</h2>
      <p class="text-muted small">Pin the location on the map.</p>
    </div>
    <div class="px-3 mb-3">
      <div id="map-picker" style="height: 250px; border-radius: 12px; border: 1.5px solid #e2e8f0;"></div>
      <div class="text-center mt-2">
        <button id="btn-geolocate" class="btn btn-sm btn-outline-primary rounded-pill px-3">
          📍 Use My Current Location
        </button>
      </div>
    </div>
    <div class="px-3">
      <div class="mob-form-group">
        <label>Detailed Address / Landmark</label>
        <textarea id="incident_address" class="mob-input" rows="2" placeholder="e.g., Near the blue gate, Purok 4..."></textarea>
      </div>
      <div class="mob-form-group">
        <label>Additional Description (Optional)</label>
        <textarea id="description" class="mob-input" rows="2" placeholder="Any other details..."></textarea>
      </div>
    </div>
    <div class="px-3 mt-4 d-flex gap-2">
      <button class="mob-btn mob-btn-secondary prev-btn">Back</button>
      <button class="mob-btn next-btn">Review</button>
    </div>
  </div>

  <!-- Step 4: Review -->
  <div id="step-4" class="report-step d-none">
    <div class="px-3 mb-4">
      <h2 class="h3 fw-bold mb-1">Review & Submit</h2>
      <p class="text-muted small">Ensure all details are correct.</p>
    </div>
    <div class="mob-card">
      <div class="mob-card-body">
        <div class="mb-3">
          <div class="text-muted small">Hazard</div>
          <div class="fw-bold h4 mb-0 text-capitalize" id="rev-hazard"></div>
        </div>
        <div class="mb-3">
          <div class="text-muted small">Location</div>
          <div class="fw-bold" id="rev-address"></div>
        </div>
        <div id="rev-answers" class="small">
          <!-- Injected -->
        </div>
      </div>
    </div>
    <div class="px-3 mt-4 d-flex gap-2">
      <button class="mob-btn mob-btn-secondary prev-btn" id="btn-review-back">Back</button>
      <button class="mob-btn mob-btn-danger flex-grow-1" id="btn-submit">SUBMIT REPORT</button>
    </div>
  </div>

  <!-- Success State -->
  <div id="step-success" class="report-step d-none">
    <div class="text-center py-5 px-3">
      <div style="font-size: 80px;" class="mb-4">✅</div>
      <h2 class="h2 fw-bold mb-2">Report Submitted</h2>
      <p class="text-muted mb-4">Your report has been received. Our team is reviewing it now.</p>
      <div class="mob-card text-start mb-4">
        <div class="mob-card-body">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="text-muted">Report Code:</span>
            <span class="fw-bold" id="success-code"></span>
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <span class="text-muted">Triage:</span>
            <span id="success-triage"></span>
          </div>
        </div>
      </div>
      <a href="<?= base_url('app/resident') ?>" class="mob-btn">Back to Home</a>
    </div>
  </div>

</div>

<style>
.hazard-btn.selected { border: 2px solid var(--resq-red) !important; background: #fff5f5 !important; }
.ans-btn { display: block; width: 100%; text-align: left; padding: 12px 16px; border: 1.5px solid #e2e8f0; border-radius: 10px; background: #fff; margin-bottom: 8px; font-size: 14px; transition: all .2s; }
.ans-btn.selected { border-color: var(--resq-red); background: #fff5f5; color: var(--resq-red); font-weight: 600; }
.ans-btn:active { background: #f8fafc; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  let currentStep = 1;
  let reportData = {
    hazard_type: '',
    answers: {},
    incident_address: '',
    incident_lat: 14.5995, // Default Manila
    incident_lng: 120.9842,
    description: '',
    is_emergency: 1
  };

  const steps = ['step-1', 'step-2', 'step-3', 'step-4', 'step-success'];
  let map, marker;

  // ── Step Navigation ────────────────────────────────────────────────
  function showStep(n) {
    document.querySelectorAll('.report-step').forEach(el => el.classList.add('d-none'));
    document.getElementById(steps[n-1]).classList.remove('d-none');
    
    // Update dots
    const dots = document.querySelectorAll('.step-dot');
    dots.forEach((dot, idx) => {
      dot.classList.toggle('active', idx === n-1);
      dot.classList.toggle('done', idx < n-1);
    });
    
    currentStep = n;
    window.scrollTo(0,0);
    
    if (n === 3) initMap();
    if (n === 4) populateReview();
  }

  // ── Step 1: Hazard Selection ───────────────────────────────────────
  document.querySelectorAll('.hazard-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      reportData.hazard_type = this.dataset.type;
      document.querySelectorAll('.hazard-btn').forEach(b => b.classList.remove('selected'));
      this.classList.add('selected');
      
      fetch('<?= base_url('api/v1/incidents/questions?hazard_type=') ?>' + reportData.hazard_type)
        .then(r => r.json())
        .then(data => {
          renderQuestions(data.questions);
          showStep(2);
        });
    });
  });

  // ── Step 2: Questions ──────────────────────────────────────────────
  function renderQuestions(questions) {
    const container = document.getElementById('questions-container');
    container.innerHTML = '';
    
    questions.forEach(q => {
      const qDiv = document.createElement('div');
      qDiv.className = 'mb-4';
      qDiv.innerHTML = `<label class="fw-bold mb-2">${q.text}</label>`;
      
      const optsDiv = document.createElement('div');
      optsDiv.dataset.qkey = q.key; // Track question key for validation
      optsDiv.classList.add('question-options');

      q.options.forEach(opt => {
        const btn = document.createElement('button');
        btn.className = 'ans-btn';
        btn.textContent = opt.label;
        btn.onclick = () => {
          reportData.answers[q.key] = opt.label;
          optsDiv.querySelectorAll('.ans-btn').forEach(b => b.classList.remove('selected'));
          btn.classList.add('selected');
        };
        // Restore from draft
        if (reportData.answers[q.key] === opt.label) btn.classList.add('selected');
        optsDiv.appendChild(btn);
      });
      
      qDiv.appendChild(optsDiv);
      container.appendChild(qDiv);
    });
  }

  // ── Step 3: Map & Location ─────────────────────────────────────────
  function initMap() {
    if (map) return;
    map = L.map('map-picker').setView([reportData.incident_lat, reportData.incident_lng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
    
    marker = L.marker([reportData.incident_lat, reportData.incident_lng], { draggable: true }).addTo(map);
    marker.on('dragend', function() {
      const pos = marker.getLatLng();
      reportData.incident_lat = pos.lat;
      reportData.incident_lng = pos.lng;
    });
    
    map.on('click', function(e) {
      marker.setLatLng(e.latlng);
      reportData.incident_lat = e.latlng.lat;
      reportData.incident_lng = e.latlng.lng;
    });
  }

  document.getElementById('btn-geolocate').onclick = function() {
    if (!navigator.geolocation) return alert('Geolocation not supported');
    navigator.geolocation.getCurrentPosition(pos => {
      const { latitude, longitude } = pos.coords;
      reportData.incident_lat = latitude;
      reportData.incident_lng = longitude;
      map.setView([latitude, longitude], 17);
      marker.setLatLng([latitude, longitude]);
    }, err => alert('Could not get location. Please pin manually.'));
  };

  // ── Step 4: Review ─────────────────────────────────────────────────
  function populateReview() {
    document.getElementById('rev-hazard').textContent = reportData.hazard_type;
    document.getElementById('rev-address').textContent = document.getElementById('incident_address').value || 'No address provided';
    
    const ansList = document.getElementById('rev-answers');
    ansList.innerHTML = '<div class="text-muted small mb-2">Answers</div>';
    Object.keys(reportData.answers).forEach(k => {
      const div = document.createElement('div');
      div.className = 'd-flex justify-content-between mb-1 border-bottom pb-1';
      div.innerHTML = `<span class="text-muted">${k}:</span> <span class="fw-bold">${reportData.answers[k]}</span>`;
      ansList.appendChild(div);
    });
  }

  // ── Generic Handlers ───────────────────────────────────────────────
  document.querySelectorAll('.next-btn').forEach(btn => {
    btn.onclick = () => {
      if (currentStep === 2) {
        // Validate all questions are answered
        const questions = document.querySelectorAll('.question-options');
        let allAnswered = true;
        questions.forEach(q => {
          const qkey = q.dataset.qkey;
          if (!reportData.answers[qkey]) {
            allAnswered = false;
            q.closest('.mb-4').style.border = '1px solid #dc2626';
            q.closest('.mb-4').style.padding = '10px';
            q.closest('.mb-4').style.borderRadius = '8px';
          } else {
            q.closest('.mb-4').style.border = 'none';
            q.closest('.mb-4').style.padding = '0';
          }
        });
        if (!allAnswered) {
          alert('Please answer all questions before proceeding.');
          return;
        }
      }
      showStep(currentStep + 1);
    };
  });
  document.querySelectorAll('.prev-btn').forEach(btn => {
    btn.onclick = () => showStep(currentStep - 1);
  });

  // ── Submission ─────────────────────────────────────────────────────
  document.getElementById('btn-submit').onclick = async function() {
    const btn = this;
    btn.disabled = true;
    btn.textContent = 'Submitting...';
    
    reportData.incident_address = document.getElementById('incident_address').value;
    reportData.description = document.getElementById('description').value;
    reportData.csrf_token = '<?= csrf_token() ?>';

    try {
      if (!navigator.onLine) {
        saveToOfflineQueue(reportData);
        alert('You are offline. Report saved and will be submitted automatically when you are online.');
        window.location.href = '<?= base_url('app/resident') ?>';
        return;
      }

      const response = await fetch('<?= base_url('api/v1/incidents') ?>', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-Token': '<?= csrf_token() ?>'
        },
        body: JSON.stringify(reportData)
      });
      
      const result = await response.json();
      if (result.success) {
        document.getElementById('success-code').textContent = result.code;
        const triage = result.triage.level.replace('_', ' ');
        document.getElementById('success-triage').innerHTML = `<span class="mob-badge badge-${result.triage.level}">${triage}</span>`;
        showStep(5);
        localStorage.removeItem('resqnow_draft_report');
      } else {
        alert('Error: ' + (result.error || 'Submission failed'));
        btn.disabled = false;
        btn.textContent = 'SUBMIT REPORT';
      }
    } catch (e) {
      console.error(e);
      alert('Network error. Saving as draft.');
      saveToOfflineQueue(reportData);
      btn.disabled = false;
      btn.textContent = 'SUBMIT REPORT';
    }
  };

  function saveToOfflineQueue(data) {
    const queue = JSON.parse(localStorage.getItem('resqnow_offline_reports') || '[]');
    queue.push(data);
    localStorage.setItem('resqnow_offline_reports', JSON.stringify(queue));
    // Try to register background sync if available
    if ('serviceWorker' in navigator && 'SyncManager' in window) {
      navigator.serviceWorker.ready.then(reg => {
        return reg.sync.register('submit-offline-reports');
      });
    }
  }

  // Auto-save draft
  setInterval(() => {
    if (reportData.hazard_type) {
      reportData.incident_address = document.getElementById('incident_address').value;
      reportData.description = document.getElementById('description').value;
      localStorage.setItem('resqnow_draft_report', JSON.stringify(reportData));
    }
  }, 5000);

  // Load draft
  const draft = localStorage.getItem('resqnow_draft_report');
  if (draft) {
    const parsed = JSON.parse(draft);
    if (confirm('You have an unsaved report draft. Resume?')) {
      reportData = parsed;
      document.getElementById('incident_address').value = reportData.incident_address;
      document.getElementById('description').value = reportData.description;
      // If hazard selected, go to step 2
      if (reportData.hazard_type) {
         fetch('<?= base_url('api/v1/incidents/questions?hazard_type=') ?>' + reportData.hazard_type)
          .then(r => r.json())
          .then(data => {
            renderQuestions(data.questions);
            showStep(2);
          });
      }
    } else {
      localStorage.removeItem('resqnow_draft_report');
    }
  }
});

function hazard_icon(h) {
  const colors = {
    fire:'#dc2626', flood:'#2563eb', earthquake:'#d97706',
    medical:'#9333ea', accident:'#ea580c', evacuation:'#16a34a',
    security:'#4f46e5', other:'#64748b'
  };
  const bgs = {
    fire:'#fef2f2', flood:'#eff6ff', earthquake:'#fffbeb',
    medical:'#fdf4ff', accident:'#fff7ed', evacuation:'#f0fdf4',
    security:'#eef2ff', other:'#f8fafc'
  };
  const paths = {
    fire:       '<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>',
    flood:      '<path d="M2 12.5c.5-.5 1.5-.5 2 0s1.5.5 2 0 1.5-.5 2 0 1.5.5 2 0 1.5-.5 2 0 1.5.5 2 0"/><path d="M2 17.5c.5-.5 1.5-.5 2 0s1.5.5 2 0 1.5-.5 2 0 1.5.5 2 0 1.5-.5 2 0 1.5.5 2 0"/><path d="M2 7.5c.5-.5 1.5-.5 2 0s1.5.5 2 0 1.5-.5 2 0 1.5.5 2 0 1.5-.5 2 0 1.5.5 2 0"/>',
    earthquake: '<path d="M2 12h3l2-8 3 16 3-10 2 6 2-4h3"/>',
    medical:    '<path d="M22 12h-4l-3 9L9 3l-3 9H2"/>',
    accident:   '<path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v9a2 2 0 0 1-2 2h-1"/><circle cx="9" cy="17" r="2"/><circle cx="19" cy="17" r="2"/>',
    evacuation: '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
    security:   '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
    other:      '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>',
  };
  const c = colors[h] || '#64748b';
  const bg = bgs[h] || '#f8fafc';
  const p = paths[h] || paths.other;
  return `<div style="width:48px;height:48px;border-radius:12px;background:${bg};display:flex;align-items:center;justify-content:center;margin:0 auto;">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="${c}" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">${p}</svg>
  </div>`;
}
</script>
