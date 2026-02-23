function confirmAction(message = "هل أنت متأكد؟") {
  return confirm(message);
}
// Confirm لأي رابط عليه data-confirm
document.addEventListener("click", function (e) {
  const a = e.target.closest("a[data-confirm]");
  if (!a) return;
  const msg = a.getAttribute("data-confirm") || "Are you sure?";
  if (!confirm(msg)) e.preventDefault();
});

// تمييز الرابط الحالي في السايدبار
document.addEventListener("DOMContentLoaded", function () {
  const path = window.location.pathname;
  document.querySelectorAll(".nav a").forEach((link) => {
    const href = link.getAttribute("href");
    if (href && path.endsWith(href)) link.classList.add("active");
  });

  // اخفاء التنبيه بعد 3 ثواني (اختياري)
  document.querySelectorAll(".alert").forEach((al) => {
    setTimeout(() => {
      al.style.display = "none";
    }, 3000);
  });
});
