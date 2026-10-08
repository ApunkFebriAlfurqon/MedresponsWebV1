// ============================================================
// RapidAid - Main JavaScript
// ============================================================

// ---- Page Loader ----
window.addEventListener('load', () => {
  setTimeout(() => {
    const loader = document.getElementById('page-loader');
    if (loader) loader.classList.add('hidden');
  }, 1800);
});

// ---- Theme Toggle ----
const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
const savedTheme = localStorage.getItem('rapidaid-theme') || (prefersDark ? 'dark' : 'light');
document.documentElement.setAttribute('data-theme', savedTheme);

function toggleTheme() {
  const current = document.documentElement.getAttribute('data-theme');
  const next = current === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', next);
  localStorage.setItem('rapidaid-theme', next);
  updateThemeIcons();
}

function updateThemeIcons() {
  const theme = document.documentElement.getAttribute('data-theme');
  document.querySelectorAll('.theme-toggle').forEach(btn => {
    btn.innerHTML = theme === 'dark' ? '☀️' : '🌙';
    btn.title = theme === 'dark' ? 'Switch to Light Mode' : 'Switch to Dark Mode';
  });
}

document.addEventListener('DOMContentLoaded', () => {
  updateThemeIcons();

  // ---- Navbar scroll effect ----
  const navbar = document.querySelector('.navbar');
  if (navbar) {
    const onScroll = () => {
      navbar.classList.toggle('scrolled', window.scrollY > 30);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  // ---- Mobile menu ----
  const hamburger = document.querySelector('.hamburger');
  const navLinks = document.querySelector('.nav-links');
  if (hamburger && navLinks) {
    hamburger.addEventListener('click', () => {
      navLinks.classList.toggle('mobile-open');
    });
    document.addEventListener('click', (e) => {
      if (!navbar.contains(e.target)) navLinks.classList.remove('mobile-open');
    });
  }

  // ---- Sidebar toggle (dashboard) ----
  const sidebarToggle = document.getElementById('sidebar-toggle');
  const sidebar = document.querySelector('.sidebar');
  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => {
      sidebar.classList.toggle('open');
    });
    document.addEventListener('click', (e) => {
      if (sidebar && !sidebar.contains(e.target) && e.target !== sidebarToggle) {
        sidebar.classList.remove('open');
      }
    });
  }

  // ---- FAQ Accordion ----
  document.querySelectorAll('.faq-question').forEach(q => {
    q.addEventListener('click', () => {
      const item = q.closest('.faq-item');
      const wasOpen = item.classList.contains('open');
      document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
      if (!wasOpen) item.classList.add('open');
    });
  });

  // ---- Scroll Reveal ----
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('visible');
        observer.unobserve(e.target);
      }
    });
  }, { threshold: 0.1 });
  document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

  // ---- Counter Animation ----
  const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        animateCounter(e.target);
        counterObserver.unobserve(e.target);
      }
    });
  }, { threshold: 0.5 });
  document.querySelectorAll('[data-count]').forEach(el => counterObserver.observe(el));

  // ---- Flash auto-dismiss ----
  const flash = document.querySelector('.flash-message');
  if (flash) {
    setTimeout(() => {
      flash.style.opacity = '0';
      flash.style.transform = 'translateY(-20px)';
      setTimeout(() => flash.remove(), 400);
    }, 4000);
  }

  // ---- Modal triggers ----
  document.querySelectorAll('[data-modal]').forEach(btn => {
    btn.addEventListener('click', () => openModal(btn.dataset.modal));
  });
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) closeModal(overlay.id);
    });
  });
  document.querySelectorAll('.modal-close').forEach(btn => {
    btn.addEventListener('click', () => {
      const modal = btn.closest('.modal-overlay');
      if (modal) closeModal(modal.id);
    });
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      document.querySelectorAll('.modal-overlay.open').forEach(m => closeModal(m.id));
    }
  });

  // ---- Auto-dismiss alerts ----
  document.querySelectorAll('.alert[data-auto-dismiss]').forEach(alert => {
    setTimeout(() => { alert.style.display = 'none'; }, 5000);
  });
});

// ---- Counter Animation ----
function animateCounter(el) {
  const target = parseInt(el.dataset.count);
  const duration = 1500;
  const step = target / (duration / 16);
  let current = 0;
  const timer = setInterval(() => {
    current = Math.min(current + step, target);
    el.textContent = Math.floor(current).toLocaleString();
    if (current >= target) clearInterval(timer);
  }, 16);
}

// ---- Modal ----
function openModal(id) {
  const overlay = document.getElementById(id);
  if (overlay) overlay.classList.add('open');
}
function closeModal(id) {
  const overlay = document.getElementById(id);
  if (overlay) overlay.classList.remove('open');
}

// ---- Toast ----
function showToast(message, type = 'success') {
  const toast = document.createElement('div');
  toast.style.cssText = `
    position:fixed;top:20px;right:20px;z-index:9999;
    background:var(--bg-card);border:1px solid var(--border);
    padding:14px 20px;border-radius:12px;
    box-shadow:0 8px 30px rgba(0,0,0,0.2);
    display:flex;align-items:center;gap:10px;
    font-size:0.875rem;font-weight:500;color:var(--text);
    animation:slideUp 0.3s ease;max-width:350px;
  `;
  const icons = { success: '✅', danger: '❌', warning: '⚠️', info: 'ℹ️' };
  toast.innerHTML = `<span>${icons[type] || icons.info}</span><span>${message}</span>`;
  document.body.appendChild(toast);
  setTimeout(() => { toast.style.opacity = '0'; toast.style.transform = 'translateY(-20px)'; toast.style.transition = 'all 0.3s'; setTimeout(() => toast.remove(), 300); }, 3500);
}

// ---- AJAX Form Submit ----
function submitForm(formId, url, onSuccess) {
  const form = document.getElementById(formId);
  if (!form) return;
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = form.querySelector('[type=submit]');
    const originalText = btn ? btn.innerHTML : '';
    if (btn) { btn.disabled = true; btn.innerHTML = '<span>Processing...</span>'; }

    try {
      const res = await fetch(url, { method: 'POST', body: new FormData(form) });
      const data = await res.json();
      if (data.success) {
        showToast(data.message || 'Success!', 'success');
        if (onSuccess) onSuccess(data);
      } else {
        showToast(data.message || 'An error occurred.', 'danger');
      }
    } catch (err) {
      showToast('Network error. Please try again.', 'danger');
    } finally {
      if (btn) { btn.disabled = false; btn.innerHTML = originalText; }
    }
  });
}

// ---- Map Simulation ----
let mapSimInterval = null;
function initTrackingMap(lat, lng, ambulanceLat, ambulanceLng) {
  if (!document.getElementById('tracking-map')) return;
  // Use Leaflet if available (loaded separately)
  if (typeof L === 'undefined') return;

  const map = L.map('tracking-map').setView([lat, lng], 14);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap'
  }).addTo(map);

  const userIcon = L.divIcon({ html: '📍', iconSize: [30,30], className: 'map-icon' });
  const ambIcon = L.divIcon({ html: '🚑', iconSize: [30,30], className: 'map-icon' });

  L.marker([lat, lng], { icon: userIcon }).addTo(map).bindPopup('Your Location').openPopup();
  const ambMarker = L.marker([ambulanceLat, ambulanceLng], { icon: ambIcon }).addTo(map).bindPopup('Ambulance');

  // Simulate ambulance movement
  let aLat = ambulanceLat, aLng = ambulanceLng;
  const dLat = (lat - ambulanceLat) / 30;
  const dLng = (lng - ambulanceLng) / 30;
  let steps = 0;

  mapSimInterval = setInterval(() => {
    if (steps >= 30) { clearInterval(mapSimInterval); return; }
    aLat += dLat; aLng += dLng; steps++;
    ambMarker.setLatLng([aLat, aLng]);
  }, 1500);

  return map;
}

// ---- Export table to CSV ----
function exportTableCSV(tableId, filename) {
  const table = document.getElementById(tableId);
  if (!table) return;
  const rows = [...table.querySelectorAll('tr')].map(row =>
    [...row.querySelectorAll('th,td')].map(cell => `"${cell.innerText.replace(/"/g,'""')}"`).join(',')
  );
  const blob = new Blob([rows.join('\n')], { type: 'text/csv' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = filename || 'export.csv';
  a.click();
}

// ---- Confirm delete ----
function confirmDelete(url, message = 'Are you sure you want to delete this?') {
  if (confirm(message)) {
    fetch(url, { method: 'POST' })
      .then(r => r.json())
      .then(data => {
        showToast(data.message || 'Deleted.', data.success ? 'success' : 'danger');
        if (data.success) setTimeout(() => location.reload(), 1000);
      });
  }
}

// ---- Search filter ----
function initTableSearch(inputId, tableId) {
  const input = document.getElementById(inputId);
  const table = document.getElementById(tableId);
  if (!input || !table) return;
  input.addEventListener('input', () => {
    const q = input.value.toLowerCase();
    table.querySelectorAll('tbody tr').forEach(row => {
      row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
  });
}
