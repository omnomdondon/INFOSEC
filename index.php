<?php
session_start();

// Redirect logged-in users directly to the homepage
if (isset($_SESSION['email'])) {
    header("Location: view/homepages/homepage1.php");
    exit();
}

// Check for success message
$successMessage = $_SESSION['success_message'] ?? null;
unset($_SESSION['success_message']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="bootstrap/bootstrap-5.0.2-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="fontawesome-free-6.7.1-web/css/all.min.css">
    <link rel="stylesheet" href="view/style.css">
    <title>Login/Register Page</title>
    <style>
        /* Password Strength Bar and Tooltip Styling */
        .progress-container {
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
            /* Space between the progress bar and tooltip icon */
            margin-top: 10px;
        }

        #password-strength-bar {
            flex-grow: 1;
            height: 8px;
            /* Add height */
            background-color: #e9ecef;
            border-radius: 4px;
        }

        #password-strength-progress {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        #tooltipIcon {
            cursor: pointer;
            color: #6c757d;
        }

        #tooltipIcon:hover {
            color: #0d6efd;
        }
    </style>
    <script>
        // Toggle between login and signup forms
        function toggleForms(showLogin) {
            document.getElementById('loginForm').style.display = showLogin ? 'block' : 'none';
            document.getElementById('signupForm').style.display = showLogin ? 'none' : 'block';
        }

        // Form validation on submit
        function validateForm() {
            const password = document.getElementById("registerPassword").value;
            const confirmPassword = document.getElementById("confirmPassword").value;
            const errorMessage = document.getElementById("error-message");

            errorMessage.innerHTML = '';

            if (password !== confirmPassword) {
                errorMessage.innerHTML = "Passwords do not match!";
                return false;
            }

            if (!/^(?=.{12,})/.test(password) || !/[A-Z]/.test(password) || !/[a-z]/.test(password) || !/[0-9]/.test(password) || !/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
                errorMessage.innerHTML = "Password does not meet strength requirements. Must have at least 12 characters, uppercase/lowercase letters, numbers, and symbols.";
                return false;
            }

            return true;
        }

        // Auto-close Bootstrap alert after 2 seconds
        document.addEventListener('DOMContentLoaded', function () {
            const successMessage = "<?php echo addslashes($successMessage); ?>";
            if (successMessage) {
                const alertElement = document.createElement('div');
                alertElement.classList.add('alert', 'alert-success', 'text-center');
                alertElement.textContent = successMessage;
                document.querySelector('.container').prepend(alertElement);

                setTimeout(() => {
                    alertElement.classList.add('fade');
                    setTimeout(() => {
                        alertElement.remove();
                    }, 500); // Wait for fade-out
                }, 2000); // Show alert for 2 seconds
            }
        });

        // ======================== Restrict paste action on login form password field =================================
        document.getElementById('password').addEventListener('paste', function (e) {
            e.preventDefault();  // Prevent paste
            alert('Pasting is disabled in this field!');
        });

        // ======================== Restrict paste action on register form password fields =============================
        document.getElementById('registerPassword').addEventListener('paste', function (e) {
            e.preventDefault();  // Prevent paste
            alert('Pasting is disabled in this field!');
        });

        document.getElementById('confirmPassword').addEventListener('paste', function (e) {
            e.preventDefault();  // Prevent paste
            alert('Pasting is disabled in this field!');
        });

        // ============== Toggle Password Visibility ===================== 
        function toggleVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // ========================== Password Strength Checker =======================
        document.addEventListener("DOMContentLoaded", function () {
            const passwordInput = document.getElementById("registerPassword");
            const passwordStrengthProgress = document.getElementById("password-strength-progress");
            const passwordStrengthText = document.getElementById("password-strength-text");
            const tooltipIcon = document.getElementById("tooltipIcon");

            // Initialize progress bar width
            passwordStrengthProgress.style.width = "0%";

            // Password Strength Checker
            passwordInput.addEventListener("input", function () {
                const password = passwordInput.value;

                // Define password requirements
                const minLength = 12;
                const hasUppercase = /[A-Z]/.test(password);
                const hasLowercase = /[a-z]/.test(password);
                const hasNumber = /[0-9]/.test(password);
                const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);

                // Calculate password strength
                let strength = 0;
                if (password.length >= minLength) strength += 20;
                if (hasUppercase) strength += 20;
                if (hasLowercase) strength += 20;
                if (hasNumber) strength += 20;
                if (hasSpecial) strength += 20;

                // Update progress bar
                passwordStrengthProgress.style.width = `${strength}%`;
                if (strength < 40) {
                    passwordStrengthProgress.classList.remove("bg-success", "bg-warning");
                    passwordStrengthProgress.classList.add("bg-danger");
                    passwordStrengthText.textContent = "Weak"; // Update strength text
                } else if (strength < 80) {
                    passwordStrengthProgress.classList.remove("bg-danger", "bg-success");
                    passwordStrengthProgress.classList.add("bg-warning");
                    passwordStrengthText.textContent = "Moderate"; // Update strength text
                } else {
                    passwordStrengthProgress.classList.remove("bg-danger", "bg-warning");
                    passwordStrengthProgress.classList.add("bg-success");
                    passwordStrengthText.textContent = "Strong"; // Update strength text
                }

                // Update password requirements list
                document.getElementById("lengthRequirement").style.color = password.length >= minLength ? "green" : "red";
                document.getElementById("uppercaseRequirement").style.color = hasUppercase ? "green" : "red";
                document.getElementById("lowercaseRequirement").style.color = hasLowercase ? "green" : "red";
                document.getElementById("numberRequirement").style.color = hasNumber ? "green" : "red";
                document.getElementById("symbolRequirement").style.color = hasSpecial ? "green" : "red";

                // Enable/disable signup button
                const signUpButton = document.getElementById("signUpButton");
                signUpButton.disabled = strength < 100;
            });

            // Initialize Bootstrap Tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.forEach(function (tooltipTriggerEl) {
                new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
</head>

<body class="bg-light">
    <div class="container">
        <div class="row mt-5">

            <!-- ======================================== LOGIN FORM ============================================== -->
            <div id="loginForm" class="col-lg-4 bg-white m-auto rounded-top wrapper">
                <h2 class="text-center pt-3">Login</h2>
                <form method="post" action="controller/login.php" class="py-3">
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <p id="error-message" class="text-danger text-center">
                            <?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                        <?php unset($_SESSION['error_message']); ?>
                    <?php endif; ?>
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                        <input type="text" name="email" id="email" class="form-control" placeholder="Username or Email"
                            required />
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fa fa-lock"></i></span>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Password"
                            required />
                        <button type="button" class="btn btn-outline-secondary"
                            onclick="toggleVisibility('password', 'loginIcon')">
                            <i id="loginIcon" class="fa fa-eye"></i>
                        </button>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="rememberMe" name="rememberMe">
                            <label class="form-check-label" for="rememberMe">Remember Me</label>
                        </div>
                        <a href="view/password_reset_page.php" class="text-decoration-none">Forgot Password?</a>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success" name="signIn">Login</button>
                        <p class="text-center mt-3">
                            Don't have an account? <a href="#" onclick="toggleForms(false); return false;">Register
                                Now</a>
                        </p>
                    </div>
                </form>
            </div>

            <!-- ======================================== SIGNUP FORM ============================================== -->
            <div id="signupForm" class="col-lg-4 bg-white m-auto rounded-top wrapper" style="display: none;">
                <h2 class="text-center pt-3">Signup</h2>
                <p class="text-center text-muted lead mb-4">It's Free and Takes a Minute</p>

                <form method="post" action="controller/register.php" onsubmit="return validateForm();">
                    <!-- Username Input -->
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fa fa-user-circle"></i></span>
                        <input type="text" name="username" id="username" class="form-control" placeholder="Username"
                            required />
                    </div>
                    <!-- Input Fields -->
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fa fa-user"></i></span>
                        <input type="text" name="fName" id="fName" class="form-control" placeholder="First Name"
                            required />
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fa fa-user"></i></span>
                        <input type="text" name="lName" id="lName" class="form-control" placeholder="Last Name"
                            required />
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                        <input type="email" name="email" id="email" class="form-control" placeholder="Email" required />
                    </div>
                    <!-- Password Fields -->
                    <div class="input-group mb-2">
                        <span class="input-group-text"><i class="fa fa-lock"></i></span>
                        <input type="password" name="password" id="registerPassword" class="form-control"
                            placeholder="Password" required />
                        <button type="button" class="btn btn-outline-secondary"
                            onclick="toggleVisibility('registerPassword', 'passwordIcon')">
                            <i id="passwordIcon" class="fa fa-eye"></i>
                        </button>
                    </div>

                    <!-- Password Strength Bar and Tooltip -->
                    <div class="progress-container">
                        <div id="password-strength-text" class="small"></div> <!-- Strength text -->
                        <div id="password-strength-bar" class="progress">
                            <div id="password-strength-progress" class="progress-bar" role="progressbar"
                                style="width: 0%;"></div>
                        </div>
                        <!-- Tooltip Icon -->
                        <button type="button" class="btn p-0" id="tooltipIcon" data-bs-toggle="tooltip"
                            data-bs-html="true"
                            title="<ul class='mb-0 text-start'><li>At least 12 characters</li><li>Include uppercase letter</li><li>Include lowercase letter</li><li>Include numbers</li><li>Include special character</li></ul>">
                            <i class="fa fa-info-circle fs-6"></i>
                        </button>
                    </div>

                    <!-- Password Requirements -->
                    <div id="passwordRequirements" class="text-muted mb-3">
                        <ul>
                            <li id="lengthRequirement">At least 12 characters long</li>
                            <li id="uppercaseRequirement">Includes uppercase letters</li>
                            <li id="lowercaseRequirement">Includes lowercase letters</li>
                            <li id="numberRequirement">Includes numbers</li>
                            <li id="symbolRequirement">Includes special characters</li>
                        </ul>
                    </div>

                    <div class="input-group mb-2">
                        <span class="input-group-text"><i class="fa fa-lock"></i></span>
                        <input type="password" name="confirmPassword" id="confirmPassword" class="form-control"
                            placeholder="Confirm Password" required />
                        <button type="button" class="btn btn-outline-secondary"
                            onclick="toggleVisibility('confirmPassword', 'confirmIcon')">
                            <i id="confirmIcon" class="fa fa-eye"></i>
                        </button>
                    </div>
                    <p id="error-message" class="text-danger"></p>
                    <div class="d-grid">
                        <button type="submit" id="signUpButton" class="btn btn-success" name="signUp" disabled>Sign
                            Up</button>
                    </div>
                    <p class="text-center mt-3">Already have an account? <a href="#"
                            onclick="toggleForms(true); return false;">Login Now</a></p>
                </form>
            </div>
        </div>
    </div>

    <script src="bootstrap/bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
    <script src="view/script.js"></script>
</body>

</html>