document.addEventListener("DOMContentLoaded", function () {
    const editButton = document.getElementById("editButton");
    const cancelButton = document.getElementById("cancelButton");
    const formActions = document.getElementById("formActions");
    const editableFields = ["email", "phone", "gender"];
    let originalValues = {};

    if (editButton && formActions) {
        editButton.addEventListener("click", function () {
            originalValues = {};
            editableFields.forEach(function (id) {
                const field = document.getElementById(id);
                if (field) {
                    originalValues[id] = field.value;
                    field.disabled = false;
                }
            });

            editButton.hidden = true;
            formActions.hidden = false;
            document.getElementById("email").focus();
        });
    }

    if (cancelButton && formActions) {
        cancelButton.addEventListener("click", function () {
            editableFields.forEach(function (id) {
                const field = document.getElementById(id);
                if (field) {
                    field.value = originalValues[id];
                    field.disabled = true;
                }
            });

            formActions.hidden = true;
            editButton.hidden = false;
        });
    }

    // Toggle password visibility (blue eye icon)
    function resetPasswordVisibility() {
        document.querySelectorAll("[data-toggle-password]").forEach(function (button) {
            const targetInput = document.getElementById(button.dataset.togglePassword);
            if (targetInput) {
                targetInput.type = "password";
            }
            button.setAttribute("aria-label", "Show password");
            button.innerHTML = '<i class="fa-regular fa-eye"></i>';
        });
    }

    document.querySelectorAll("[data-toggle-password]").forEach(function (button) {
        button.addEventListener("click", function () {
            const targetInput = document.getElementById(button.dataset.togglePassword);
            if (!targetInput) return;

            const isHidden = targetInput.type === "password";
            targetInput.type = isHidden ? "text" : "password";
            button.setAttribute("aria-label", isHidden ? "Hide password" : "Show password");
            button.innerHTML = isHidden
                ? '<i class="fa-regular fa-eye-slash"></i>'
                : '<i class="fa-regular fa-eye"></i>';
        });
    });

    const passwordButton = document.getElementById("passwordButton");
    const passwordPanel = document.getElementById("passwordPanel");
    const cancelPasswordButton = document.getElementById("cancelPasswordButton");

    if (passwordButton && passwordPanel) {
        passwordButton.addEventListener("click", function () {
            passwordPanel.hidden = !passwordPanel.hidden;
            if (!passwordPanel.hidden) {
                document.getElementById("currentPassword").focus();
            } else {
                resetPasswordVisibility();
            }
        });
    }

    if (cancelPasswordButton && passwordPanel) {
        cancelPasswordButton.addEventListener("click", function () {
            passwordPanel.hidden = true;
            passwordPanel.querySelector("form").reset();
            resetPasswordVisibility();
        });
    }
});