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
        document.addEventListener("DOMContentLoaded", function () {
            // Toggle between login and signup forms
            window.toggleForms = function (showLogin) {
                document.querySelector('#loginForm')?.style.setProperty('display', showLogin ? 'block' : 'none');
                document.querySelector('#signupForm')?.style.setProperty('display', showLogin ? 'none' : 'block');
            };

            // Form validation on submit
            window.validateForm = function () {
                const password = document.querySelector("#registerPassword")?.value;
                const confirmPassword = document.querySelector("#confirmPassword")?.value;
                const errorMessage = document.querySelector("#error-message");

                if (!password || !confirmPassword || !errorMessage) return false;

                errorMessage.textContent = '';

                if (password !== confirmPassword) {
                    errorMessage.textContent = "Passwords do not match!";
                    return false;
                }

                if (!/^(?=.{12,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[!@#$%^&*(),.?":{}|<>])/.test(password)) {
                    errorMessage.textContent = "Password must have at least 12 characters, uppercase/lowercase letters, numbers, and symbols.";
                    return false;
                }

                return true;
            };

            // Auto-close Bootstrap alert after 2 seconds
            const successMessage = "<?php echo addslashes($successMessage); ?>";
            if (successMessage) {
                const alertElement = document.createElement('div');
                alertElement.classList.add('alert', 'alert-success', 'text-center');
                alertElement.textContent = successMessage;
                document.querySelector('.container')?.prepend(alertElement);

                setTimeout(() => {
                    alertElement.classList.add('fade');
                    setTimeout(() => alertElement.remove(), 500);
                }, 2000);
            }

            // Restrict paste action on password fields
            const disablePasteFields = ["#password", "#registerPassword", "#confirmPassword"];
            disablePasteFields.forEach(selector => {
                const field = document.querySelector(selector);
                if (field) {
                    field.addEventListener('paste', e => {
                        e.preventDefault();
                        alert('Pasting is disabled in this field!');
                    });
                }
            });

            // Toggle Password Visibility
            window.toggleVisibility = function (inputId, iconId) {
                const input = document.querySelector(`#${inputId}`);
                const icon = document.querySelector(`#${iconId}`);

                if (input && icon) {
                    input.type = input.type === 'password' ? 'text' : 'password';
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                }
            };

            // Password Strength Checker
            const passwordInput = document.querySelector("#registerPassword");
            const passwordStrengthProgress = document.querySelector("#password-strength-progress");
            const passwordStrengthText = document.querySelector("#password-strength-text");

            if (passwordInput && passwordStrengthProgress && passwordStrengthText) {
                passwordInput.addEventListener("input", function () {
                    const password = passwordInput.value;
                    const minLength = 12;
                    const hasUppercase = /[A-Z]/.test(password);
                    const hasLowercase = /[a-z]/.test(password);
                    const hasNumber = /[0-9]/.test(password);
                    const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);

                    let strength = 0;
                    if (password.length >= minLength) strength += 20;
                    if (hasUppercase) strength += 20;
                    if (hasLowercase) strength += 20;
                    if (hasNumber) strength += 20;
                    if (hasSpecial) strength += 20;

                    passwordStrengthProgress.style.width = `${strength}%`;

                    const strengthClasses = ["bg-danger", "bg-warning", "bg-success"];
                    passwordStrengthProgress.classList.remove(...strengthClasses);

                    if (strength < 40) {
                        passwordStrengthProgress.classList.add("bg-danger");
                        passwordStrengthText.textContent = "Weak";
                    } else if (strength < 80) {
                        passwordStrengthProgress.classList.add("bg-warning");
                        passwordStrengthText.textContent = "Moderate";
                    } else {
                        passwordStrengthProgress.classList.add("bg-success");
                        passwordStrengthText.textContent = "Strong";
                    }

                    // Enable/Disable signup button
                    const signUpButton = document.querySelector("#signUpButton");
                    if (signUpButton) signUpButton.disabled = strength < 100;
                });
            }

            // Initialize Bootstrap Tooltips
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(tooltipTriggerEl => {
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
                    <div class="progress-container mt-3 mb-3"> <!-- Added Bootstrap margin classes -->
                        <div id="password-strength-text" class="small"></div> <!-- Strength text -->
                        <div id="password-strength-bar" class="progress">
                            <div id="password-strength-progress" class="progress-bar" role="progressbar"
                                style="width: 0%;"></div>
                        </div>
                        <!-- Tooltip Icon -->
                        <i id="tooltipIcon" class="fa fa-info-circle" data-bs-toggle="tooltip" data-bs-html="true"
                            title="<ul class='mb-0 text-start'><li>At least 12 characters</li><li>Include uppercase letter</li><li>Include lowercase letter</li><li>Include numbers</li><li>Include special character</li></ul>">
                        </i>
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