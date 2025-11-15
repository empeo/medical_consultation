document.addEventListener("DOMContentLoaded", function () {
  const navbarToggle = document.querySelector(".navbar-toggle");
  const navbarMenu = document.querySelector(".navbar-menu");

  if (navbarToggle && navbarMenu) {
    navbarToggle.addEventListener("click", function () {
      navbarMenu.classList.toggle("active");
    });

    document.addEventListener("click", function (event) {
      if (!event.target.closest(".navbar-container")) {
        navbarMenu.classList.remove("active");
      }
    });
  }
});

function toggleLanguage() {
  const urlParams = new URLSearchParams(window.location.search);
  const currentLang = urlParams.get("lang") || "en";
  const newLang = currentLang === "en" ? "ar" : "en";

  localStorage.setItem("language", newLang);

  const url = new URL(window.location.href);
  url.searchParams.set("lang", newLang);
  window.location.href = url.toString();
}

window.addEventListener("DOMContentLoaded", function () {
  const urlParams = new URLSearchParams(window.location.search);
  let langParam = urlParams.get("lang");
  const savedLang = localStorage.getItem("language");

  if (!langParam && savedLang) {
    const url = new URL(window.location.href);
    url.searchParams.set("lang", savedLang);
    window.location.replace(url.toString());
    return;
  }

  const currentLang = langParam || savedLang || "en";
  if (currentLang === "ar") {
    document.body.classList.add("rtl");
    document.documentElement.setAttribute("dir", "rtl");
    document.documentElement.setAttribute("lang", "ar");
  } else {
    document.body.classList.remove("rtl");
    document.documentElement.setAttribute("dir", "ltr");
    document.documentElement.setAttribute("lang", "en");
  }
});

function isValidEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

function isStrongPassword(password) {
  const strongPasswordRegex = /^(?=.*[^A-Za-z0-9]).{6,}$/;
  return strongPasswordRegex.test(password);
}

function showError(input, message) {
  if (!input) return;
  input.classList.add("error");

  const old = input.parentElement.querySelector(".error-message.js-error");
  if (old) old.remove();

  const errorDiv = document.createElement("div");
  errorDiv.className = "error-message js-error";
  errorDiv.textContent = message;
  input.parentElement.appendChild(errorDiv);
}

function validateForm(formOrId) {
  const form =
    typeof formOrId === "string" ? document.getElementById(formOrId) : formOrId;

  if (!form) return true;

  let isValid = true;

  form.querySelectorAll(".error-message.js-error").forEach((el) => el.remove());
  form.querySelectorAll(".error").forEach((el) => el.classList.remove("error"));

  const inputs = form.querySelectorAll(
    "input[required], textarea[required], select[required]"
  );

  inputs.forEach((input) => {
    const value = input.value.trim();

    if (!value) {
      showError(input, "This field is required");
      isValid = false;
      return;
    }

    if (input.type === "email" && !isValidEmail(value)) {
      showError(input, "Please enter a valid email");
      isValid = false;
      return;
    }

    if (input.type === "password" && input.name === "password") {
      if (!isStrongPassword(value)) {
        showError(
          input,
          "Password must be at least 6 characters and contain a special character"
        );
        isValid = false;
        return;
      }
    }
  });

  const password = form.querySelector('input[name="password"]');
  const confirmPassword = form.querySelector('input[name="confirm_password"]');

  if (password && confirmPassword && confirmPassword.value.trim() !== "") {
    if (password.value !== confirmPassword.value) {
      showError(confirmPassword, "Passwords do not match");
      isValid = false;
    }
  }

  return isValid;
}

document.addEventListener("DOMContentLoaded", function () {
  const forms = document.querySelectorAll("form");

  forms.forEach((form) => {
    const inputs = form.querySelectorAll("input, textarea, select");

    inputs.forEach((input) => {
      input.addEventListener("blur", function () {
        if (!this.hasAttribute("required")) return;

        const old = this.parentElement.querySelector(".error-message.js-error");
        if (old) old.remove();
        this.classList.remove("error");

        const value = this.value.trim();
        if (!value) {
          showError(this, "This field is required");
        } else if (this.type === "email" && !isValidEmail(value)) {
          showError(this, "Please enter a valid email");
        } else if (
          this.type === "password" &&
          this.name === "password" &&
          !isStrongPassword(value)
        ) {
          showError(
            this,
            "Password must be at least 6 characters and contain a special character"
          );
        }
      });

      input.addEventListener("input", function () {
        if (this.classList.contains("error")) {
          this.classList.remove("error");
        }
        const old = this.parentElement.querySelector(".error-message.js-error");
        if (old) old.remove();
      });
    });
  });
});

function confirmDelete(message) {
  return confirm(message || "Are you sure you want to delete this item?");
}

document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      const targetSelector = this.getAttribute("href");
      if (!targetSelector || targetSelector === "#") return;

      const target = document.querySelector(targetSelector);
      if (!target) return;

      e.preventDefault();
      target.scrollIntoView({
        behavior: "smooth",
        block: "start",
      });
    });
  });
});

function setButtonLoading(button, isLoading) {
  if (!button) return;
  if (isLoading) {
    button.disabled = true;
    button.dataset.originalText = button.textContent;
    button.textContent = "Loading...";
  } else {
    button.disabled = false;
    if (button.dataset.originalText) {
      button.textContent = button.dataset.originalText;
    }
  }
}

document.addEventListener("DOMContentLoaded", function () {
  const forms = document.querySelectorAll("form");

  forms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      const submitBtn = form.querySelector('button[type="submit"]');
      if (submitBtn) {
        setButtonLoading(submitBtn, true);
      }
    });
  });
});
