document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.getElementById("login-form");
  const registerForm = document.getElementById("register-form");

  // --- Helper Functions ---
  const showError = (inputElement, message) => {
    const errorElement =
      inputElement.parentElement.querySelector(".validation-error");
    if (errorElement) {
      errorElement.textContent = message;
      inputElement.style.borderColor = "#ffaaaa"; // Highlight border
    }
  };

  const clearError = (inputElement) => {
    const errorElement =
      inputElement.parentElement.querySelector(".validation-error");
    if (errorElement) {
      errorElement.textContent = "";
      inputElement.style.borderColor = "#ffd700"; // Reset border
    }
  };

  const isValidEmail = (email) => {
    // Simple regex for basic email format check
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
  };

  // --- Login Form Validation ---
  if (loginForm) {
    loginForm.addEventListener("submit", (event) => {
      let isValid = true;
      const usernameInput = document.getElementById("username");
      const passwordInput = document.getElementById("password");

      // Clear previous errors
      clearError(usernameInput);
      clearError(passwordInput);

      // Validate Username
      if (usernameInput.value.trim() === "") {
        showError(usernameInput, "Username is required.");
        isValid = false;
      }

      // Validate Password
      if (passwordInput.value === "") {
        // No trim for password
        showError(passwordInput, "Password is required.");
        isValid = false;
      }

      if (!isValid) {
        event.preventDefault(); // Stop form submission if validation fails
      }
    });

    // Optional: Clear errors on input
    ["username", "password"].forEach((id) => {
      const input = document.getElementById(id);
      if (input) {
        input.addEventListener("input", () => clearError(input));
      }
    });
  }

  // --- Registration Form Validation ---
  if (registerForm) {
    registerForm.addEventListener("submit", (event) => {
      let isValid = true;
      const usernameInput = document.getElementById("reg-username");
      const emailInput = document.getElementById("reg-email");
      const passwordInput = document.getElementById("reg-password");
      const confirmPasswordInput = document.getElementById("confirm-password");

      // Clear previous errors
      clearError(usernameInput);
      clearError(emailInput);
      clearError(passwordInput);
      clearError(confirmPasswordInput);

      // Validate Username
      if (usernameInput.value.trim() === "") {
        showError(usernameInput, "Username is required.");
        isValid = false;
      }

      // Validate Email
      if (emailInput.value.trim() === "") {
        showError(emailInput, "Email is required.");
        isValid = false;
      } else if (!isValidEmail(emailInput.value.trim())) {
        showError(emailInput, "Please enter a valid email address.");
        isValid = false;
      }

      // Validate Password
      if (passwordInput.value === "") {
        showError(passwordInput, "Password is required.");
        isValid = false;
      } else if (passwordInput.value.length < 6) {
        showError(passwordInput, "Password must be at least 6 characters.");
        isValid = false;
      }

      // Validate Confirm Password
      if (confirmPasswordInput.value === "") {
        showError(confirmPasswordInput, "Please confirm your password.");
        isValid = false;
      } else if (passwordInput.value !== confirmPasswordInput.value) {
        showError(confirmPasswordInput, "Passwords do not match.");
        isValid = false;
        // Optionally show error on the first password field too
        // showError(passwordInput, 'Passwords do not match.');
      }

      if (!isValid) {
        event.preventDefault(); // Stop form submission if validation fails
      }
    });

    // Optional: Clear errors on input
    ["reg-username", "reg-email", "reg-password", "confirm-password"].forEach(
      (id) => {
        const input = document.getElementById(id);
        if (input) {
          input.addEventListener("input", () => clearError(input));
        }
      }
    );
  }
});
