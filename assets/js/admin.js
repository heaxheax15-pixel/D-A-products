(function () {
  var btn = document.getElementById("adminMenuBtn");
  var sidebar = document.getElementById("adminSidebar");
  if (btn && sidebar) {
    btn.addEventListener("click", function () {
      sidebar.classList.toggle("open");
    });
  }
})();
