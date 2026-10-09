/* =========================================================
   FILE: checkData.js
   PURPOSE:
   Adds small client-side interactions and validation support
   for the registration form.
   ========================================================= */

document.addEventListener("DOMContentLoaded", function () {
    /* -----------------------------------------------------
       SECTION 1: Get Page Elements
       ----------------------------------------------------- */
    const todayEl = document.getElementById("TodaysDate");
    const greetEl = document.getElementById("greet");
    const nameInput = document.getElementById("fname");
    const namePreview = document.getElementById("nm");
    const dobInput = document.getElementById("dob");
    const birthdayMessage = document.getElementById("birthdayMessage");
    const emailInput = document.getElementById("emadd");
    const registrationForm = document.getElementById("registrationForm");

    const now = new Date();
    const hours = now.getHours();

    /* -----------------------------------------------------
       SECTION 2: Display Today’s Date
       ----------------------------------------------------- */
    if (todayEl) {
        todayEl.textContent = `Today is ${now.toDateString()}`;
    }

    /* -----------------------------------------------------
       SECTION 3: Dynamic Greeting
       ----------------------------------------------------- */
    if (greetEl) {
        if (hours < 12) {
            greetEl.textContent = "Good Morning";
        } else if (hours < 18) {
            greetEl.textContent = "Good Afternoon";
        } else {
            greetEl.textContent = "Good Evening";
        }
    }

    /* -----------------------------------------------------
       SECTION 4: Limit Date of Birth Input
       ----------------------------------------------------- */
    if (dobInput) {
        const maxDate = new Date();
        maxDate.setDate(maxDate.getDate() - 1);
        dobInput.max = maxDate.toISOString().split("T")[0];
    }

    /* -----------------------------------------------------
       SECTION 5: Preview First Name Greeting
       ----------------------------------------------------- */
    if (nameInput && namePreview) {
        nameInput.addEventListener("blur", function () {
            const firstName = nameInput.value.trim();
            namePreview.textContent = firstName ? `Hello, ${firstName}!` : "";
        });
    }

    /* -----------------------------------------------------
       SECTION 6: Birthday Month Message
       ----------------------------------------------------- */
    if (dobInput && birthdayMessage) {
        dobInput.addEventListener("change", function () {
            if (!dobInput.value) {
                birthdayMessage.textContent = "";
                return;
            }

            const dob = new Date(dobInput.value);

            if (dob.getMonth() === now.getMonth()) {
                birthdayMessage.textContent = "Your birthday is in this month.";
            } else {
                birthdayMessage.textContent = "";
            }
        });
    }

    /* -----------------------------------------------------
       SECTION 7: Email Format Check
       ----------------------------------------------------- */
    if (emailInput) {
        emailInput.addEventListener("change", function () {
            const email = emailInput.value.trim();

            if (email !== "" && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                alert("Please enter a valid email address.");
                emailInput.focus();
            }
        });
    }

    /* -----------------------------------------------------
       SECTION 8: Final Form Validation
       ----------------------------------------------------- */
    if (registrationForm) {
        registrationForm.addEventListener("submit", function (event) {
            const passwordField = document.getElementById("pword");
            const levelField = document.getElementById("level");

            if (nameInput && nameInput.value.trim().length < 2) {
                alert("First name must be at least 2 characters.");
                nameInput.focus();
                event.preventDefault();
                return;
            }

            if (levelField && levelField.value === "") {
                alert("Please select your level.");
                levelField.focus();
                event.preventDefault();
                return;
            }

            if (passwordField && passwordField.value.length < 6) {
                alert("Password must be at least 6 characters.");
                passwordField.focus();
                event.preventDefault();
            }
        });
    }
});