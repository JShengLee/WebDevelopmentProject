document.addEventListener("DOMContentLoaded", function () {
    const editButton = document.getElementById("editButton");
    const cancelButton = document.getElementById("cancelButton");
    const formActions = document.getElementById("formActions");

    const editableFields = ["fullName", "email", "phone", "specialty"];
    let originalValues = {};

    // Enable editing fields
    if (editButton) {
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
            document.getElementById("fullName").focus();
        });
    }

    // Cancel editing and revert values
    if (cancelButton) {
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

    // Password Panel Toggle
    const passwordButton = document.getElementById("passwordButton");
    const passwordPanel = document.getElementById("passwordPanel");
    const cancelPasswordButton = document.getElementById("cancelPasswordButton");

    if (passwordButton && passwordPanel) {
        passwordButton.addEventListener("click", function () {
            passwordPanel.hidden = !passwordPanel.hidden;
            if (!passwordPanel.hidden) {
                document.getElementById("currentPassword").focus();
            }
        });
    }

    if (cancelPasswordButton && passwordPanel) {
        cancelPasswordButton.addEventListener("click", function () {
            passwordPanel.hidden = true;
        });
    }
});