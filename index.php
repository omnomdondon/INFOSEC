<?php
session_start();

// Redirect logged-in users directly to the homepage
if (isset($_SESSION['email'])) {
    // header("Location: view/homepages/homepage1.php");
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

        // ========================== Password Strength Checker =======================
        document.getElementById('registerPassword').addEventListener('input', function () {
            const password = this.value;
            const lengthRequirement = document.getElementById('lengthRequirement');
            const uppercaseRequirement = document.getElementById('uppercaseRequirement');
            const lowercaseRequirement = document.getElementById('lowercaseRequirement');
            const numberRequirement = document.getElementById('numberRequirement');
            const symbolRequirement = document.getElementById('symbolRequirement');
            const signUpButton = document.getElementById('signUpButton');

            let isValid = true;

            // Check password requirements
            lengthRequirement.style.color = password.length >= 12 ? 'green' : 'red';
            uppercaseRequirement.style.color = /[A-Z]/.test(password) ? 'green' : 'red';
            lowercaseRequirement.style.color = /[a-z]/.test(password) ? 'green' : 'red';
            numberRequirement.style.color = /[0-9]/.test(password) ? 'green' : 'red';
            symbolRequirement.style.color = /[!@#$%^&*(),.?":{}|<>]/.test(password) ? 'green' : 'red';

            // Enable the signup button if all conditions are met
            isValid = password.length >= 12 &&
                /[A-Z]/.test(password) &&
                /[a-z]/.test(password) &&
                /[0-9]/.test(password) &&
                /[!@#$%^&*(),.?":{}|<>]/.test(password);
            signUpButton.disabled = !isValid;
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

        // ======================== Trigger password validation when the page loads ====================================
        window.addEventListener('load', function () {
            document.getElementById('registerPassword').dispatchEvent(new Event('input'));
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
                            onclick="toggleVisibility('registerPassword', 'registerIcon')">
                            <i id="registerIcon" class="fa fa-eye"></i>
                        </button>
                    </div>
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