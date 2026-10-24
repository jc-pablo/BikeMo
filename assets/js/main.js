/* BikeMo: small shared behaviors. Database work is handled by PHP, not localStorage. */

function startRenting() {
    window.location.href = window.bikemoRentUrl || 'login.php?role=user';
}

// An SVG bike keeps the hero looking finished while you choose real photos.
function heroBikeSvg() {
    return `<svg viewBox="0 0 580 355" role="img" aria-label="Illustration of a mountain bike" xmlns="http://www.w3.org/2000/svg">
      <ellipse cx="290" cy="307" rx="252" ry="19" fill="rgba(0,0,0,.23)" />
      <g fill="none" stroke="#101d2d" stroke-width="12">
        <circle cx="127" cy="233" r="91" fill="rgba(255,255,255,.07)" />
        <circle cx="455" cy="233" r="91" fill="rgba(255,255,255,.07)" />
      </g>

      <g class="hero-wheel"
   fill="none"
   stroke="rgba(255,255,255,.72)"
   stroke-width="2">
    <path d="M127 142V324
             M36 233H218
             M62 168L192 298
             M192 168L62 298" />
</g>

<!-- Right wheel spokes -->
<g class="hero-wheel"
   fill="none"
   stroke="rgba(255,255,255,.72)"
   stroke-width="2">
    <path d="M455 142V324
             M364 233H546
             M390 168L520 298
             M520 168L390 298" />
</g>
      <g fill="none" stroke="#ff6a1a" stroke-width="12" stroke-linejoin="round" stroke-linecap="round">
        <path d="M127 233 L238 95 L302 233 Z M238 95 L373 102 L302 233 L127 233 M373 102 L455 233" />
      </g>
      <g fill="none" stroke="#0b1d2e" stroke-width="12" stroke-linecap="round">
        <path d="M226 88 L220 61 M195 61 H248 M369 101 L386 62 L422 62 M374 62 L412 62" />
        <circle cx="302" cy="233" r="20" fill="#c6ff3d" stroke-width="8" />
        <path d="M302 233L320 250" stroke="#c6ff3d" stroke-width="9" />
      </g>
      <g fill="none" stroke="#c6ff3d" stroke-width="6">
        <circle cx="127" cy="233" r="9" /><circle cx="455" cy="233" r="9" />
      </g>
    </svg>`;
}

function animateCount(element) {
    const target = Number(element.dataset.count || element.dataset.bmCount || 0);
    if (!Number.isFinite(target) || target < 0) return;

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches || target === 0) {
        element.textContent = Math.round(target).toLocaleString();
        return;
    }

    const start = performance.now();
    function frame(now) {
        const progress = Math.min((now - start) / 850, 1);
        element.textContent = Math.round(target * (1 - (1 - progress) ** 3)).toLocaleString();
        if (progress < 1) requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
}

document.addEventListener('DOMContentLoaded', () => {
    const hero = document.getElementById('heroBike');
    if (hero && !hero.querySelector('img, svg')) hero.innerHTML = heroBikeSvg();

    document.querySelectorAll('[data-count], [data-bm-count]').forEach(animateCount);

    const greeting = document.getElementById('bm-greeting');
    if (greeting) {
        const hour = new Date().getHours();
        greeting.textContent = hour < 12 ? 'Good morning' : hour < 18 ? 'Good afternoon' : 'Good evening';
    }

    const tips = [
        'Check your brakes and tire pressure before every ride.',
        'Wear a helmet and use lights when riding at night.',
        'Adjust your saddle so you can pedal comfortably.',
        'Stay hydrated during long rides.'
    ];
    const tip = document.getElementById('bm-tip-text');
    if (tip) {
        let index = 0;
        setInterval(() => {
            index = (index + 1) % tips.length;
            tip.textContent = tips[index];
        }, 6500);
    }

    const search = document.getElementById('bikeSearch');
    const type = document.getElementById('bikeType');
    if (search && type) {
        function filterBikes() {
            document.querySelectorAll('.customer-bike').forEach(card => {
                card.hidden = !card.dataset.name.includes(search.value.toLowerCase()) ||
                    (type.value !== '' && card.dataset.type !== type.value);
            });
        }
        search.addEventListener('input', filterBikes);
        type.addEventListener('change', filterBikes);
    }

    const rentalFilter = document.getElementById('rentalFilter');
    if (rentalFilter) {
        rentalFilter.addEventListener('change', () => {
            document.querySelectorAll('#rentalRows tr').forEach(row => {
                row.hidden = !!rentalFilter.value && row.dataset.status !== rentalFilter.value;
            });
        });
    }
});

function updateNavbar() {
    const navbar = document.getElementById('nav');

    if (!navbar) return;

    if (window.scrollY > 40) {
        navbar.classList.add('solid');
    } else {
        navbar.classList.remove('solid');
    }
}

// Change navbar background when scrolling
window.addEventListener('scroll', updateNavbar);

// Check navbar position when page loads
window.addEventListener('DOMContentLoaded', updateNavbar);
