// ── Global Search shortcut Ctrl+K ──
document.addEventListener("keydown", function (e) {
  if ((e.ctrlKey || e.metaKey) && e.key === "k") {
    e.preventDefault();
    const searchInput = document.getElementById("globalSearch");
    if (searchInput) searchInput.focus();
  }
});

// ── Tự động ẩn flash message sau 4 giây ──
document.querySelectorAll(".alert").forEach(function (alert) {
  setTimeout(function () {
    alert.classList.remove("show");
    alert.classList.add("fade");
  }, 4000);
});

// ── Xác nhận trước khi thực hiện action nguy hiểm ──
document.querySelectorAll("[data-confirm]").forEach(function (el) {
  el.addEventListener("click", function (e) {
    if (!confirm(el.getAttribute("data-confirm"))) {
      e.preventDefault();
    }
  });
});
