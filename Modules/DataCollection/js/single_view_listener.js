$(document).ready(function () {

  function hideToolbar(toolbar) {
    toolbar.style.display = "none";
  }

  function showToolbar(toolbar) {
    toolbar.style.display = "block";
  }

  addEventListener("edit_paragraph_open", () => {
    for (let toolbar of document.querySelectorAll(".ilToolbar")) {
      hideToolbar(toolbar);
    }
  });

  addEventListener("edit_paragraph_save_return", () => {
    for (let toolbar of document.querySelectorAll(".ilToolbar")) {
      showToolbar(toolbar);
    }
  });

  addEventListener("edit_paragraph_cancel", () => {
    for (let toolbar of document.querySelectorAll(".ilToolbar")) {
      showToolbar(toolbar);
    }
  });
});