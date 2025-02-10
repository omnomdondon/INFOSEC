<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../bootstrap/bootstrap-5.0.2-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../fontawesome-free-6.7.1-web/css/all.min.css">
    <link rel="stylesheet" href="../view/style.css">
    <title>Reset Password</title>
    <script>
        // Function to handle form submission and email validation
        function validateEmail() {
            const email = document.getElementById("email").value;
            const errorMessage = document.getElementById("error-message");
            const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;

            // Clear previous error messages
            errorMessage.innerHTML = '';

            // Validate email
            if (!emailPattern.test(email)) {
                errorMessage.innerHTML = "Please enter a valid email address.";
                return false;
            }

            return true;
        }
    </script>
</head>

<body class="bg-light">
    <div class="container">
        <div class="row mt-5">
            <!-- Reset Password Form -->
            <div class="col-lg-4 bg-white m-auto rounded-top wrapper">
                <h2 class="text-center pt-3">Reset Password</h2>
                
                <p class="text-center text-muted lead mb-4">Enter your email to receive a password reset link.</p>
                
                <?php
                // Check if there is a success message
                if (isset($_SESSION['success_message'])) {
                    echo "<div class='alert alert-success text-center'>" . $_SESSION['success_message'] . "</div>";
                    unset($_SESSION['success_message']); // Clear the success message
                }

                // Check if there is an error message
                if (isset($_SESSION['error_message'])) {
                    echo "<div class='alert alert-danger text-center'>" . $_SESSION['error_message'] . "</div>";
                    unset($_SESSION['error_message']); // Clear the error message
                }
                ?>

                <form method="post" action="../controller/send_password_reset.php" onsubmit="return validateEmail();">
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                        <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email"
                            required />
                    </div>

                    <p id="error-message" class="text-danger text-center"></p>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success" name="resetPassword">Send Reset Link</button>
                    </div>
                    <div class="text-center mt-3">
                        <p>Remember your password? <a href="../index.php">Login Now</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../bootstrap/bootstrap-5.0.2-dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>

</html>