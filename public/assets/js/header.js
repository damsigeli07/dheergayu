document.addEventListener("DOMContentLoaded", () => {
  const userIcon = document.getElementById("user-icon");
  const userDropdown = document.getElementById("user-dropdown");

  if (!userIcon || !userDropdown) return;

  // Toggle dropdown on click
  userIcon.addEventListener("click", (e) => {
    e.stopPropagation();
    userDropdown.style.display = (userDropdown.style.display === "block") ? "none" : "block";
  });

  // Close dropdown if clicked outside
  document.addEventListener("click", (e) => {
    if (!userDropdown.contains(e.target) && !userIcon.contains(e.target)) {
      userDropdown.style.display = "none";
    }
  });

  // Logout confirmation
  document.addEventListener("click", (e) => {
    const logoutBtn = e.target.closest("a.logout-btn");
    if (logoutBtn) {
      e.preventDefault();
      e.stopPropagation();
      if (confirm("Are you sure you want to logout?")) {
        window.location.href = logoutBtn.href;
      }
    }
  }, true);
});
