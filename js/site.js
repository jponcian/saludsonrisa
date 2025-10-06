// Small helper interactions: smooth scroll and simple animations
document.addEventListener("DOMContentLoaded", function () {
  // Smooth scroll for anchor links
  document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
    anchor.addEventListener("click", function (e) {
      const href = this.getAttribute("href");
      if (href.length > 1) {
        e.preventDefault();
        const target = document.querySelector(href);
        if (target) {
          window.scrollTo({ top: target.offsetTop - 70, behavior: "smooth" });
        }
      }
    });
  });

  // Simple fade-in on scroll for service cards
  const toObserve = document.querySelectorAll(
    ".service-card, .testimonial-card"
  );
  if ("IntersectionObserver" in window) {
    const obs = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add("inview");
            obs.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.15 }
    );
    toObserve.forEach(function (el) {
      obs.observe(el);
    });
  }
});
