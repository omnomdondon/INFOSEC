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

    $_SESSION['error_message'] = "Token has expired.";
    echo "<script>
            alert('Token has expired. You will be redirected to the login page.');
            window.location.href = '../index.php';
          </script>";
    exit;
}

// Total token validity period (in seconds)
$total_validity = 30; // Set this to match the expiry time in send_password_reset.php
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
            display: block; /* Always visible */
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
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const passwordInput = document.getElementById('reset_password');
            const confirmInput = document.getElementById('password_confirmation');
            const submitButton = document.getElementById('submitButton');

            if (passwordInput && confirmInput) {
                passwordInput.addEventListener('input', checkPasswordStrength);
                confirmInput.addEventListener('input', checkPasswordStrength);
            }

            function checkPasswordStrength() {
                const password = passwordInput.value;
                const lengthRequirement = document.getElementById('lengthRequirement');
                const uppercaseRequirement = document.getElementById('uppercaseRequirement');
                const lowercaseRequirement = document.getElementById('lowercaseRequirement');
                const numberRequirement = document.getElementById('numberRequirement');
                const symbolRequirement = document.getElementById('symbolRequirement');

                lengthRequirement.style.color = password.length >= 12 ? 'green' : 'red';
                uppercaseRequirement.style.color = /[A-Z]/.test(password) ? 'green' : 'red';
                lowercaseRequirement.style.color = /[a-z]/.test(password) ? 'green' : 'red';
                numberRequirement.style.color = /[0-9]/.test(password) ? 'green' : 'red';
                symbolRequirement.style.color = /[!@#$%^&*(),.?":{}|<>]/.test(password) ? 'green' : 'red';

                submitButton.disabled = !(password.length >= 12 &&
                    /[A-Z]/.test(password) &&
                    /[a-z]/.test(password) &&
                    /[0-9]/.test(password) &&
                    /[!@#$%^&*(),.?":{}|<>]/.test(password));
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
                    toggleVisibility(this.dataset.input, this.dataset.icon);
                });
            });

            const expiryTime = <?= $expiry_time * 1000 ?>;
            const totalValidity = <?= $total_validity * 1000 ?>;

            function updateCountdown() {
                const now = new Date().getTime();
                const timeLeft = expiryTime - now;

                if (timeLeft <= 0) {
                    clearInterval(countdownInterval);
                    alert('Token has expired. Redirecting to login page.');
                    window.location.href = '../index.php';
                    return;
                }

                document.getElementById('countdown').textContent = `Token expires in ${Math.floor(timeLeft / 1000)}s`;
                document.getElementById('progress').style.width = `${(timeLeft / totalValidity) * 100}%`;
            }

            updateCountdown();
            const countdownInterval = setInterval(updateCountdown, 1000);
        });
    </script>
</head>
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
                            placeholder="New Password" required onpaste="return false;"
                            oninput="checkPasswordStrength()" />
                        <button type="button" class="btn btn-outline-secondary"
                            onclick="toggleVisibility('reset_password', 'passwordIcon')">
                            <i id="passwordIcon" class="fa fa-eye"></i>
                        </button>
                    </div>

                    <!-- Password Strength Indicator -->
                    <div id="passwordStrength" class="mb-3">
                        <ul>
                            <li id="lengthRequirement">At least 8 characters</li>
                            <li id="uppercaseRequirement">At least one uppercase letter</li>
                            <li id="lowercaseRequirement">At least one lowercase letter</li>
                            <li id="numberRequirement">At least one number</li>
                            <li id="symbolRequirement">At least one special character</li>
                        </ul>
                    </div>

                    <!-- Password Confirmation -->
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fa fa-lock"></i></span>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="form-control" placeholder="Repeat Password" required onpaste="return false;" />
                        <button type="button" class="btn btn-outline-secondary"
                            onclick="toggleVisibility('password_confirmation', 'confirmIcon')">
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
    <script src="../view/script.js"></script>
</body>
</html>