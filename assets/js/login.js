class Login {
    static initLogin() {
        const MAIN_CONTENT = document.getElementById("ims__main-login");

        const form = ElementFactory.createForm();
        form.classList.add("login__form", "p-4", "rounded", "bg-white");

        const loginFormTitleRow = ElementFactory.createRow();
        const loginFormTitleCol = ElementFactory.createCol();
        const formTitle = ElementFactory.createParagraph("Staff Login");
        formTitle.classList.add("login__form-title", "fs-5");
        loginFormTitleRow.appendChild(loginFormTitleCol);
        loginFormTitleCol.appendChild(formTitle);

        const loginFormUsernameRow = ElementFactory.createRow();
        const loginFormUsernameCol = ElementFactory.createCol();
        const usernameinput = this.createInputLabel(
            "username",
            "Username",
            "text"
        );
        loginFormUsernameRow.appendChild(loginFormUsernameCol);
        loginFormUsernameCol.appendChild(usernameinput);

        const loginFormPasswordRow = ElementFactory.createRow();
        const loginFormPasswordCol = ElementFactory.createCol();
        const passwordInput = this.createInputLabel(
            "password",
            "Password",
            "password"
        );
        loginFormPasswordRow.appendChild(loginFormPasswordCol);
        loginFormPasswordCol.appendChild(passwordInput);

        const loginFormSubmitRow = ElementFactory.createRow();
        const loginFormSubmitCol = ElementFactory.createCol();
        const submitButton = ElementFactory.createButton("submit", "Submit");
        submitButton.classList.add(
            "login__form-submit-btn",
            "w-100",
            "border",
            "border-none",
            "py-2",
            "px-3",
            "text-white",
            "rounded"
        );
        loginFormSubmitRow.appendChild(loginFormSubmitCol);
        loginFormSubmitCol.appendChild(submitButton);

        const loginFormForgotPwRow = ElementFactory.createRow();
        const loginFormForgotPwCol = ElementFactory.createCol();
        const forgotPasswordWrapper = ElementFactory.createParagraph("");
        forgotPasswordWrapper.classList.add(
            "login__forgot-pw-btn",
            "text-center",
            "m-0"
        );
        const forgotPasswordLink =
            ElementFactory.createLink("Forgot password?");
        forgotPasswordLink.href = "#";
        loginFormForgotPwRow.appendChild(loginFormForgotPwCol);
        loginFormForgotPwCol.appendChild(forgotPasswordWrapper);
        forgotPasswordWrapper.appendChild(forgotPasswordLink);

        forgotPasswordLink.addEventListener("click", () => {
            alert("Please contact the admin to reset your password.");
        });

        form.appendChild(loginFormTitleRow);
        form.appendChild(loginFormUsernameRow);
        form.appendChild(loginFormPasswordRow);
        form.appendChild(loginFormSubmitRow);
        form.appendChild(loginFormForgotPwRow);
        MAIN_CONTENT.appendChild(form);

        form.addEventListener("submit", (e) => {
            e.preventDefault();

            this.handleLoginFormSubmit(form);
        });
    }

    static createInputLabel = (name, labelText, type) => {
        var label = document.createElement("label");
        label.classList.add("mb-3");

        var input = document.createElement("input");
        input.required = true;
        input.placeholder = "";
        input.type = type;
        input.name = name;
        input.classList.add("w-100", "rounded");

        var span = ElementFactory.createSpan(labelText);

        label.appendChild(input);
        label.appendChild(span);
        return label;
    };

    static handleLoginFormSubmit = (form) => {
        const formData = new FormData(form);

        fetch("db/login_db.php", {
            method: "POST",
            body: formData,
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    console.log(data);
                    window.location.href = "dashboard.php";
                } else {
                    console.error("Error login to the system:", data.error_msg);
                    alert(data.error_msg);
                }
            })
            .catch((error) => {
                console.error("Error login to the system:", error);
            });
    };
}
