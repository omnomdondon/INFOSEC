<?php

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
    die("Token not found or invalid.");
}

if (strtotime($user["reset_token_expires_at"]) <= time()) {
    die("Token has expired.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="../bootstrap/bootstrap-5.0.2-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../fontawesome-free-6.7.1-web/css/all.min.css">
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
    </script>
</head>

<body class="bg-light">

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