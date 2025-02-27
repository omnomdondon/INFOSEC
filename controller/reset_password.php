<?php
session_start(); // Start the session to use session variables
$token = $_GET["token"];
$token_hash = hash("sha256", $token);

$mysqli = require __DIR__ . "/../model/connect.php";

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

if (strtotime($user["reset_token_expires_at"]) <= time()) {
    // Token has expired
    $_SESSION['error_message'] = "Token has expired.";
    echo "<script>
            alert('Token has expired. You will be redirected to the login page.');
            window.location.href = '../index.php';
          </script>";
    exit;
}

// Calculate the remaining time for the token
$expiry_time = strtotime($user["reset_token_expires_at"]);
$current_time = time();
$remaining_time = $expiry_time - $current_time; // Remaining time in seconds
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
    <script src="../view/script.js"></script>
    <script>
        // Password strength validation function
        function checkPasswordStrength() {
            const password = document.getElementById('reset_password').value;
            const lengthRequirement = document.getElementById('lengthRequirement');
            const uppercaseRequirement = document.getElementById('uppercaseRequirement');
            const lowercaseRequirement = document.getElementById('lowercaseRequirement');
            const numberRequirement = document.getElementById('numberRequirement');
            const symbolRequirement = document.getElementById('symbolRequirement');
            const submitButton = document.getElementById('submitButton');

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
            submitButton.disabled = !isValid;
        }

        document.getElementById('reset_password').addEventListener('paste', function (e) {
            e.preventDefault();  // Prevent paste
            alert('Pasting is disabled in this field!');
        });

        document.getElementById('password_confirmation').addEventListener('paste', function (e) {
            e.preventDefault();  // Prevent paste
            alert('Pasting is disabled in this field!');
        });

        window.addEventListener('load', function () {
            document.getElementById('reset_password').dispatchEvent(new Event('input'));
        });

        // Toggle password visibility
        function toggleVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }

        // Show modal with messages
        document.addEventListener('DOMContentLoaded', function () {
            const errorMessage = "<?php echo $_SESSION['error_message'] ?? ''; ?>";
            const successMessage = "<?php echo $_SESSION['success_message'] ?? ''; ?>";

            if (errorMessage) {
                document.getElementById('modalMessage').textContent = errorMessage;
                document.getElementById('messageModal').classList.add('show');
                document.getElementById('messageModal').style.display = 'block';
            }

            if (successMessage) {
                document.getElementById('modalMessage').textContent = successMessage;
                document.getElementById('messageModal').classList.add('show');
                document.getElementById('messageModal').style.display = 'block';
            }
        });

        // Countdown timer for token expiry
        let remainingTime = <?= $remaining_time ?>; // Remaining time in seconds
        const expiryTime = new Date().getTime() + remainingTime * 1000; // Expiry time in milliseconds

        function updateCountdown() {
            const now = new Date().getTime();
            const timeLeft = expiryTime - now;

            if (timeLeft <= 0) {
                clearInterval(countdownInterval);
                alert('Token has expired. You will be redirected to the login page.');
                window.location.href = '../index.php';
                return;
            }

            const seconds = Math.floor(timeLeft / 1000);
            document.getElementById('countdown').textContent = `Token will expire in ${seconds}s`;

            // Update progress bar based on the full remaining time
            const progress = (timeLeft / (remainingTime * 1000)) * 100;
            document.getElementById('progress').style.width = `${progress}%`;
        }

        const countdownInterval = setInterval(updateCountdown, 1000);
        updateCountdown(); // Initial call to avoid delay
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