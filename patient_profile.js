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
            passwordPanel.querySelector("form").reset();
        });
    }
});