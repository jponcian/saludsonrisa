document.addEventListener("DOMContentLoaded", function () {
  const video = document.getElementById("heroVideo");
  const btn = document.getElementById("heroPlayBtn");

  function showPlayBtn(show) {
    if (!btn) return;
    btn.hidden = !show;
    btn.setAttribute("aria-hidden", !show);
  }

  function tryPlay() {
    if (!video) return;
    const p = video.play();
    if (p && typeof p.then === "function") {
      p.then(() => showPlayBtn(false)).catch(() => showPlayBtn(true));
    }
  }

  tryPlay();

  if (btn) {
    btn.addEventListener("click", () => {
      if (video.paused) {
        video.muted = false; // opcional: permitir audio si usuario activa
        video.play().catch(() => {});
        btn.innerHTML = '<i class="fas fa-pause"></i>';
      } else {
        video.pause();
        btn.innerHTML = '<i class="fas fa-play"></i>';
      }
    });
  }

  function onFirstInteraction() {
    if (video && video.paused) video.play().catch(() => {});
    document.body.removeEventListener("click", onFirstInteraction);
  }
  document.body.addEventListener("click", onFirstInteraction);
});
