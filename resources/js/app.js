"use strict";

/* ── HELPERS ─────────────────────────────── */
const $  = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => [...r.querySelectorAll(s)];
const reduced = window.matchMedia("(prefers-reduced-motion:reduce)").matches;

/* ── MOBILE MENU ─────────────────────────── */
const menuBtn  = $("#mobile-menu");
const navLinks = $(".nav-links");
const overlay  = $("[data-nav-overlay]");

function setMenu(open) {
  navLinks?.classList.toggle("active", open);
  overlay?.classList.toggle("active", open);
  menuBtn?.classList.toggle("active", open);
  menuBtn?.setAttribute("aria-expanded", String(open));
  menuBtn?.setAttribute("aria-label", open ? "إغلاق القائمة" : "فتح القائمة");
  document.body.classList.toggle("nav-drawer-open", open);
  document.body.style.overflow = open ? "hidden" : "";
  if (overlay) {
    overlay.setAttribute("aria-hidden", open ? "false" : "true");
  }
}

/* ── قائمة الصفحات الثابتة (منسدلة) — تُغلق عند النقر خارجها أو Escape ── */
const navDropdownItems = $$(".nav-item--dropdown");

function closeAllNavDropdowns() {
  navDropdownItems.forEach(li => {
    li.classList.remove("is-open");
    const t = li.querySelector(".nav-dropdown-toggle");
    t?.setAttribute("aria-expanded", "false");
  });
}

menuBtn?.addEventListener("click", () => {
  const willOpen = !navLinks?.classList.contains("active");
  setMenu(willOpen);
  if (willOpen) {
    closeAllNavDropdowns();
  }
});
overlay?.addEventListener("click", () => setMenu(false));

$$(".nav-dropdown-toggle").forEach(btn => {
  btn.addEventListener("click", e => {
    e.stopPropagation();
    const li = btn.closest(".nav-item--dropdown");
    const willOpen = !li?.classList.contains("is-open");
    closeAllNavDropdowns();
    if (willOpen) {
      li?.classList.add("is-open");
      btn.setAttribute("aria-expanded", "true");
    }
  });
});

document.addEventListener("click", () => closeAllNavDropdowns());

document.addEventListener("keydown", e => {
  if (e.key !== "Escape") {
    return;
  }
  setMenu(false);
  closeAllNavDropdowns();
});

$$(".nav-links a").forEach(a => a.addEventListener("click", () => {
  setMenu(false);
  closeAllNavDropdowns();
}));
/* يجب أن يطابق CSS نقطة تحول القائمة (641px) وليس 769px */
window.matchMedia("(min-width:641px)").addEventListener("change", e => {
  if (e.matches) {
    setMenu(false);
    closeAllNavDropdowns();
  }
});

/* ── HEADER SCROLL EFFECT (فقط الشكل وليس الروابط) ─ */
const header = $(".site-header");

function onScroll() {
  const y = window.scrollY;
  header?.classList.toggle("scrolled", y > 30);
}

window.addEventListener("scroll", onScroll, { passive: true });
onScroll();

/* ── SCROLL REVEAL ───────────────────────── */
(function() {
  const els = $$(".reveal, .reveal-up");
  if (reduced) { els.forEach(el => el.classList.add("visible")); return; }

  const io = new IntersectionObserver((entries, obs) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add("visible");
        obs.unobserve(e.target);
      }
    });
  }, { threshold: 0.1, rootMargin: "0px 0px -8% 0px" });

  els.forEach(el => io.observe(el));
})();

/* ── COUNTERS ────────────────────────────── */
(function() {
  const counters  = $$(".counter");
  const statsRoot = $(".about-stats");
  if (!statsRoot || !counters.length) return;

  function run(el) {
    const target = parseInt(el.dataset.target || "0", 10);
    if (!target) return;

    const dur = reduced ? 0 : 1100;
    const t0  = performance.now();

    (function tick(now) {
      const p = dur ? Math.min(1, (now - t0) / dur) : 1;
      const v = Math.round((1 - Math.pow(1 - p, 3)) * target);
      el.textContent = v;
      p < 1 ? requestAnimationFrame(tick) : (el.textContent = target + "+");
    })(t0);
  }

  let fired = false;

  new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting && !fired) {
        fired = true;
        counters.forEach(run);
      }
    });
  }, { threshold: 0.35 }).observe(statsRoot);
})();

/* ── CONTACT FORM ────────────────────────── */
(function() {
  const form = $("#contactForm");
  if (!form) return;

  const fields = $$("input, textarea", form);

  function validate(f) {
    const ok = f.checkValidity();
    f.style.borderColor  = ok ? "" : "rgba(220,50,50,.6)";
    f.style.boxShadow    = ok ? "" : "0 0 0 3px rgba(220,50,50,.12)";
  }

  fields.forEach(f => {
    f.addEventListener("blur",  () => { f._t = true; validate(f); });
    f.addEventListener("input", () => { if (f._t) validate(f); });
  });

  form.addEventListener("submit", e => {
    e.preventDefault();

    let ok = true;
    fields.forEach(f => {
      f._t = true;
      validate(f);
      if (!f.checkValidity()) ok = false;
    });

    if (!ok) {
      fields.find(f => !f.checkValidity())?.focus();
      return;
    }

    const name = $("#fullName")?.value?.trim() || "عزيزي العميل";
    const btn  = $(".btn-submit", form);
    const orig = btn.innerHTML;

    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>جارٍ الإرسال...</span>';
    btn.disabled  = true;

    setTimeout(() => {
      btn.innerHTML = '<i class="fas fa-check"></i><span>تم الإرسال!</span>';
      btn.style.background = "linear-gradient(135deg,#22c55e,#16a34a)";

      setTimeout(() => {
        form.reset();
        fields.forEach(f => {
          f.style.borderColor = "";
          f.style.boxShadow   = "";
          f._t = false;
        });

        btn.innerHTML = orig;
        btn.disabled  = false;
        btn.style.background = "";

        alert("شكراً " + name + "! تم استلام رسالتك. سنتواصل معك قريباً.");
      }, 2000);
    }, 1200);
  });
})();

/* ── CARD TILT ──────────────────────────── */
if (!reduced && matchMedia("(pointer:fine) and (min-width:1024px)").matches) {
  $$(".svc-card:not(.svc-cta)").forEach(card => {
    card.addEventListener("mousemove", e => {
      const r  = card.getBoundingClientRect();
      const dx = (e.clientX - (r.left + r.width  / 2)) / (r.width  / 2);
      const dy = (e.clientY - (r.top  + r.height / 2)) / (r.height / 2);

      card.style.transform = `translateY(-8px) rotateY(${dx * 4}deg) rotateX(${-dy * 4}deg)`;
    });

    card.addEventListener("mouseleave", () => card.style.transform = "");
  });
}