// confirms for links that have data-confirm (optional helper)
document.addEventListener("click", function (e) {
  const a = e.target.closest("a[data-confirm]");
  if (!a) return;
  const msg = a.getAttribute("data-confirm") || "Are you sure?";
  if (!confirm(msg)) e.preventDefault();
});
