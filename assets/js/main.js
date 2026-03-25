// ============================================================
//  Sen Location — main.js
//  Gère : Navbar, Modal Auth, Connexion, Inscription, Filtres
// ============================================================

// ── Utilitaires ───────────────────────────────────────────

function showAlert(msg, type = 'error', container = null) {
  // Supprimer les anciens
  document.querySelectorAll('.alert-dynamic').forEach(a => a.remove());

  const div = document.createElement('div');
  div.className = `alert alert-${type} alert-dynamic`;
  div.textContent = msg;

  const target = container
    || document.querySelector('.modal.active')
    || document.querySelector('.modal-overlay.open .modal')
    || document.body;

  target.prepend(div);
  setTimeout(() => div.remove(), 4500);
}

function setLoading(btn, loading = true) {
  if (loading) {
    btn.dataset.orig = btn.textContent;
    btn.innerHTML    = '<span class="spinner"></span>';
    btn.disabled     = true;
  } else {
    btn.textContent = btn.dataset.orig || 'Envoyer';
    btn.disabled    = false;
  }
}

// ── Navbar scroll ─────────────────────────────────────────

window.addEventListener('scroll', () => {
  const nav = document.getElementById('navbar');
  if (nav) nav.classList.toggle('scrolled', window.scrollY > 50);
});

// ── Modal Auth ────────────────────────────────────────────

window.openModal = function (tab = 'login') {
  const overlay = document.getElementById('authModal');
  if (!overlay) return;
  overlay.classList.add('open');
  switchTab(tab);
  document.body.style.overflow = 'hidden';
};

window.closeModal = function () {
  const overlay = document.getElementById('authModal');
  if (overlay) overlay.classList.remove('open');
  document.body.style.overflow = '';
};

window.switchTab = function (tab) {
  const isLogin = tab === 'login';
  const fl = document.getElementById('formLogin');
  const fr = document.getElementById('formRegister');
  const tl = document.getElementById('tabLogin');
  const tr = document.getElementById('tabRegister');

  if (fl) fl.style.display = isLogin ? 'block' : 'none';
  if (fr) fr.style.display = isLogin ? 'none'  : 'block';
  if (tl) tl.classList.toggle('active',  isLogin);
  if (tr) tr.classList.toggle('active', !isLogin);
};

// Fermer en cliquant en dehors
document.addEventListener('click', e => {
  if (e.target.id === 'authModal') closeModal();
});

// Fermer avec Escape
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closeModal();
});

// ── Connexion ─────────────────────────────────────────────

window.handleLogin = async function (formEl) {
  const email = formEl.querySelector('[name="email"]')?.value.trim();
  const mdp   = formEl.querySelector('[name="mot_de_passe"]')?.value;
  const btn   = formEl.querySelector('[type="submit"]');

  if (!email || !mdp) {
    showAlert('Remplissez tous les champs.');
    return;
  }

  setLoading(btn, true);

  const fd = new FormData();
  fd.append('action',       'login');
  fd.append('email',        email);
  fd.append('mot_de_passe', mdp);

  try {
    const res  = await fetch('/senlocation/api/auth.php', { method: 'POST', body: fd });
    const data = await res.json();

    if (data.success) {
      showAlert('Connexion réussie ! Redirection…', 'success');
      setTimeout(() => {
        window.location.href = data.redirect || '/senlocation/index.php';
      }, 800);
    } else {
      showAlert(data.message || 'Email ou mot de passe incorrect.');
      setLoading(btn, false);
    }
  } catch (err) {
    showAlert('Erreur réseau. Réessayez.');
    setLoading(btn, false);
  }
};

// ── Inscription ───────────────────────────────────────────

window.handleRegister = async function (formEl) {
  const nom   = formEl.querySelector('[name="nom"]')?.value.trim();
  const email = formEl.querySelector('[name="email"]')?.value.trim();
  const tel   = formEl.querySelector('[name="telephone"]')?.value.trim();
  const mdp   = formEl.querySelector('[name="mot_de_passe"]')?.value;
  const mdp2  = formEl.querySelector('[name="confirmer_mdp"]')?.value;
  const btn   = formEl.querySelector('[type="submit"]');

  if (!nom || !email || !mdp || !mdp2) {
    showAlert('Remplissez tous les champs obligatoires (*).');
    return;
  }
  if (mdp !== mdp2) {
    showAlert('Les mots de passe ne correspondent pas.');
    return;
  }
  if (mdp.length < 6) {
    showAlert('Le mot de passe doit faire au moins 6 caractères.');
    return;
  }

  setLoading(btn, true);

  const fd = new FormData();
  fd.append('action',       'register');
  fd.append('nom',          nom);
  fd.append('email',        email);
  fd.append('telephone',    tel);
  fd.append('mot_de_passe', mdp);
  fd.append('confirmer_mdp', mdp2);

  try {
    const res  = await fetch('/senlocation/api/auth.php', { method: 'POST', body: fd });
    const data = await res.json();

    if (data.success) {
      showAlert('Compte créé ! Redirection…', 'success');
      setTimeout(() => {
        window.location.href = data.redirect || '/senlocation/index.php';
      }, 900);
    } else {
      showAlert(data.message || 'Erreur lors de l\'inscription.');
      setLoading(btn, false);
    }
  } catch (err) {
    showAlert('Erreur réseau. Réessayez.');
    setLoading(btn, false);
  }
};

// ── Déconnexion ───────────────────────────────────────────

window.logout = async function () {
  const fd = new FormData();
  fd.append('action', 'logout');
  await fetch('/senlocation/api/auth.php', { method: 'POST', body: fd });
  window.location.href = '/senlocation/index.php';
};

// ── Wishlist (favori) ─────────────────────────────────────

document.addEventListener('click', e => {
  const btn = e.target.closest('.card-wishlist');
  if (!btn) return;
  e.stopPropagation();
  btn.textContent = btn.textContent.trim() === '♡' ? '❤️' : '♡';
});

// ── Filtres logements ─────────────────────────────────────

document.addEventListener('click', e => {
  const btn = e.target.closest('.filter-btn');
  if (!btn) return;

  document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');

  const filter = btn.dataset.filter || '';
  filterCards(filter);
});

function filterCards(filter) {
  document.querySelectorAll('.property-card').forEach(card => {
    const type   = card.dataset.type   || '';
    const statut = card.dataset.statut || '';
    const match  = !filter
      || type   === filter
      || statut === filter;
    card.style.display = match ? '' : 'none';
  });
}

// ── Scroll reveal ─────────────────────────────────────────

const revealObserver = new IntersectionObserver(entries => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('fade-up');
      revealObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.1 });

// Observer les cartes après chargement DOM
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.property-card, .feature-card').forEach(el => {
    revealObserver.observe(el);
  });

  // Attacher les formulaires de la modal
  const loginForm    = document.getElementById('loginForm');
  const registerForm = document.getElementById('registerForm');

  if (loginForm) {
    loginForm.addEventListener('submit', e => {
      e.preventDefault();
      handleLogin(loginForm);
    });
  }
  if (registerForm) {
    registerForm.addEventListener('submit', e => {
      e.preventDefault();
      handleRegister(registerForm);
    });
  }

  // Ouvrir la modal si l'URL contient ?modal=login ou ?modal=register
  const params = new URLSearchParams(location.search);
  const modal  = params.get('modal');
  if (modal === 'login' || modal === 'register') openModal(modal);
});
