<?php
session_start(); // Start the session to use session variables
$token = $_GET["token"];
$token_hash = hash("sha256", $token);

$mysqli = require __DIR__ . "/../model/connect.php";

// Check database connection
if (!$mysqli) {
    error_log("Database connection failed: " . $mysqli->connect_error);
    die("Database connection failed.");
}

// Check if the token exists in the database
$sql = "SELECT * FROM users WHERE reset_token_hash = ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("s", $token_hash);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user === null) {
    $_SESSION['error_message'] = "Token not found or invalid.";
    header("Location: ../index.php"); // Redirect to the homepage or login page
    exit;
}

$expiry_time = strtotime($user["reset_token_expires_at"]);
$current_time = time();
$remaining_time = $expiry_time - $current_time; // Remaining time in seconds

if ($expiry_time <= time()) {
    // Clear expired token
    $sql = "UPDATE users SET reset_token_hash = NULL, reset_token_expires_at = NULL WHERE reset_token_hash = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("s", $token_hash);
    $stmt->execute();

    // Debugging: Check if the query was successful
    if ($stmt->affected_rows > 0) {
        error_log("Token fields cleared for token hash: $token_hash");
    } else {
        error_log("No rows affected for token hash: $token_hash");
    }

    // Destroy the session to prevent further access to the reset password page
    session_destroy();

    $_SESSION['error_message'] = "Token has expired.";
    echo "<script>
            alert('Token has expired. You will be redirected to the login page.');
            window.location.href = '../index.php';
          </script>";
    exit;
}

// Total token validity period (in seconds)
$total_validity = 60 * 5; // Set this to match the expiry time in send_password_reset.php
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="../bootstrap/bootstrap-5.0.2-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../fontawesome-free-6.7.1-web/css/all.min.css">
    <style>
        /* Style for the countdown tracker */
        #countdown-tracker {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 14px;
            z-index: 1000;
            display: block;
            /* Always visible */
        }

        #progress-bar {
            width: 100%;
            height: 5px;
            background: #ddd;
            margin-top: 5px;
            border-radius: 2.5px;
            overflow: hidden;
        }

        #progress {
            height: 100%;
            width: 100%;
            background: #28a745;
            transition: width 1s linear;
        }

        /* Password Strength Bar and Tooltip Styling */
        .progress-container {
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }

        #password-strength-bar {
            flex-grow: 1;
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

        /* Hide password strength text initially */
        #password-strength-text {
            display: none;
        }
    </style>

<body class="bg-light">
    <!-- Modal for displaying messages -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="messageModalLabel">Message</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="modalMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Countdown Tracker -->
    <div id="countdown-tracker">
        <div id="countdown">Token will expire in <?= $remaining_time ?>s</div>
        <div id="progress-bar">
            <div id="progress"></div>
        </div>
    </div>

    <div class="container">
        <div class="row mt-5">
            <div class="col-lg-4 bg-white m-auto rounded-top wrapper">
                <h2 class="text-center pt-3">Reset Password</h2>

                <form method="post" action="process_reset_password.php" onsubmit="return validateForm();">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                    <!-- New Password -->
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fa fa-lock"></i></span>
                        <input type="password" name="reset_password" id="reset_password" class="form-control"
                            placeholder="New Password" required
                            oninput="checkPasswordStrength()" />
                        <button type="button" class="btn btn-outline-secondary"
                            data-toggle-visibility="reset_password" data-icon="passwordIcon">
                            <i id="passwordIcon" class="fa fa-eye"></i>
                        </button>
                    </div>

                    <!-- Password Strength Indicator -->
                    <div class="progress-container mb-3">
                        <div id="password-strength-text" class="small"></div>
                        <div id="password-strength-bar" class="progress mt-1">
                            <div id="password-strength-progress" class="progress-bar" role="progressbar"
                                style="width: 0%;"></div>
                        </div>
                        <!-- Tooltip Icon -->
                        <button type="button" class="btn p-0" id="tooltipIcon" data-bs-toggle="tooltip"
                            data-bs-html="true"
                            title="<ul class='mb-0 text-start'><li>At least 12 characters</li><li>Include uppercase letter</li><li>Include lowercase letter</li><li>Include numbers</li><li>Include special character</li></ul>">
                            <i class="fa fa-info-circle fs-6 text-secondary"></i>
                        </button>
                    </div>

                    <!-- Password Confirmation -->
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fa fa-lock"></i></span>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="form-control" placeholder="Repeat Password" required />
                        <button type="button" class="btn btn-outline-secondary"
                            data-toggle-visibility="password_confirmation" data-icon="confirmIcon">
                            <i id="confirmIcon" class="fa fa-eye"></i>
                        </button>
                    </div>

                    <div class="d-grid">
                        <button type="submit" id="submitButton" class="btn btn-success">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (required for tooltips, modals, etc.) -->
    <script src="../bootstrap/bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const passwordInput = document.getElementById('reset_password');
            const confirmInput = document.getElementById('password_confirmation');
            const submitButton = document.getElementById('submitButton');
            const passwordStrengthProgress = document.getElementById('password-strength-progress');
            const passwordStrengthText = document.getElementById('password-strength-text');

            if (passwordInput && confirmInput) {
                passwordInput.addEventListener('input', checkPasswordStrength);
                confirmInput.addEventListener('input', checkPasswordStrength);

                // Anti-pasting logic
                passwordInput.addEventListener('paste', function (e) {
                    e.preventDefault();
                    alert('Pasting is not allowed in this field.');
                });

                confirmInput.addEventListener('paste', function (e) {
                    e.preventDefault();
                    alert('Pasting is not allowed in this field.');
                });
            }

            function checkPasswordStrength() {
                const password = passwordInput.value;
                console.log("Password:", password); // Debugging

                // Calculate password strength
                let strength = 0;
                if (password.length >= 12) strength += 20;
                if (/[A-Z]/.test(password)) strength += 20;
                if (/[a-z]/.test(password)) strength += 20;
                if (/[0-9]/.test(password)) strength += 20;
                if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength += 20;

                console.log("Strength:", strength); // Debugging

                // Update progress bar
                passwordStrengthProgress.style.width = `${strength}%`;

                // Update strength text
                if (password.length > 0) {
                    passwordStrengthText.style.display = "block"; // Show text when typing
                    if (strength < 40) {
                        passwordStrengthProgress.classList.remove("bg-success", "bg-warning");
                        passwordStrengthProgress.classList.add("bg-danger");
                        passwordStrengthText.textContent = "Weak";
                    } else if (strength < 80) {
                        passwordStrengthProgress.classList.remove("bg-danger", "bg-success");
                        passwordStrengthProgress.classList.add("bg-warning");
                        passwordStrengthText.textContent = "Moderate";
                    } else {
                        passwordStrengthProgress.classList.remove("bg-danger", "bg-warning");
                        passwordStrengthProgress.classList.add("bg-success");
                        passwordStrengthText.textContent = "Strong";
                    }
                } else {
                    passwordStrengthText.style.display = "none"; // Hide text when empty
                }

                // Enable/disable submit button
                submitButton.disabled = strength < 100;
            }

            function toggleVisibility(inputId, iconId) {
                const input = document.getElementById(inputId);
                const icon = document.getElementById(iconId);
                if (input && icon) {
                    input.type = input.type === "password" ? "text" : "password";
                    icon.classList.toggle("fa-eye");
                    icon.classList.toggle("fa-eye-slash");
                }
            }

            document.querySelectorAll("[data-toggle-visibility]").forEach(button => {
                button.addEventListener("click", function () {
                    const inputId = this.getAttribute("data-toggle-visibility");
                    const iconId = this.getAttribute("data-icon");
                    toggleVisibility(inputId, iconId);
                });
            });

            const expiryTime = <?= $expiry_time * 1000 ?>;
            const totalValidity = <?= $total_validity * 1000 ?>;

            function formatTime(seconds) {
                if (seconds < 60) {
                    return `${seconds}s`;
                } else if (seconds < 3600) {
                    const minutes = Math.floor(seconds / 60);
                    const remainingSeconds = seconds % 60;
                    return `${minutes}m ${remainingSeconds}s`;
                } else {
                    const hours = Math.floor(seconds / 3600);
                    const minutes = Math.floor((seconds % 3600) / 60);
                    const remainingSeconds = seconds % 60;
                    return `${hours}h ${minutes}m ${remainingSeconds}s`;
                }
            }

            function updateCountdown() {
                const now = new Date().getTime();
                const timeLeft = expiryTime - now;

                if (timeLeft <= 0) {
                    clearInterval(countdownInterval);
                    alert('Token has expired. Redirecting to login page.');
                    window.location.href = '../index.php';
                    return;
                }

                const secondsLeft = Math.floor(timeLeft / 1000);
                document.getElementById('countdown').textContent = `Token expires in ${formatTime(secondsLeft)}`;
                document.getElementById('progress').style.width = `${(timeLeft / totalValidity) * 100}%`;
            }

            updateCountdown();
            const countdownInterval = setInterval(updateCountdown, 1000);

            // Initialize Bootstrap tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl, {
                    html: true,
                    sanitize: false, // Allow HTML in tooltip
                });
            });

            // Form validation
            function validateForm() {
                const password = document.getElementById('reset_password').value;
                const confirmPassword = document.getElementById('password_confirmation').value;

                if (password !== confirmPassword) {
                    alert('Passwords do not match. Please try again.');
                    return false; // Prevent form submission
                }

                return true; // Allow form submission
            }
        });
    </script>
</body>
</html>