const nav = document.getElementById('nav');
if (nav) {
  window.addEventListener('scroll', () => {
    nav.classList.toggle('solid', window.scrollY > 40);
  });
}

function countUp(el) {
  const end = +el.dataset.count;
  let n = 0;
  const t = setInterval(() => {
    n += Math.max(1, Math.ceil(end / 30));
    if (n >= end) { n = end; clearInterval(t); }
    el.textContent = n;
  }, 40);
}

function observeReveal() {
  const io = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('in');
        if (e.target.dataset.count !== undefined) countUp(e.target);
        io.unobserve(e.target);
      }
    });
  }, { threshold: 0.15 });
  document.querySelectorAll('.reveal,[data-count]').forEach(el => io.observe(el));
}

document.addEventListener('DOMContentLoaded', observeReveal);
