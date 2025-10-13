document.addEventListener("DOMContentLoaded", () => {
  const userIcon = document.getElementById("user-icon");
  const userDropdown = document.getElementById("user-dropdown");

  if (!userIcon || !userDropdown) return; // Exit if elements are not present

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
});
