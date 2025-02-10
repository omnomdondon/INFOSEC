<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Simple Blog Website</title>
    <link href="../../bootstrap/bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .contact-section {
            margin-top: 50px;
            padding: 20px;
        }

        .contact-title {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .contact-form {
            margin-top: 30px;
        }

        .form-control {
            margin-bottom: 10px;
        }

        /* Style to match login form structure */
        .form-group {
            margin-bottom: 15px;
        }

        .contact-social {
            margin-top: 30px;
        }

        .contact-social h3 {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .social-item {
            margin-bottom: 10px;
        }

        .social-item i {
            margin-right: 10px;
        }

        .social-link {
            color: #007bff;
            text-decoration: none;
        }

        .social-link:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body class="bg-light">
    <!-- Sticky Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success sticky-top">
        <div class="container">
            <a class="navbar-brand" href="homepage1.php">Simple Blog Website</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="homepage1.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contact Us Content -->
    <div class="container contact-section">
        <h1 class="contact-title">Contact Us</h1>

        <!-- Inquiry Form -->
        <div class="contact-form">
            <h3>Send Us an Inquiry</h3>
            <form method="post" action="controller/inquiry.php">
                <!-- Name Field -->
                <div class="form-group">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" placeholder="Your Name" required>
                </div>
                <!-- Email Field -->
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" placeholder="Your Email" required>
                </div>
                <!-- Description Field -->
                <div class="form-group">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" rows="4" placeholder="Your Message"
                        required></textarea>
                </div>
                <!-- Submit Button -->
                <div class="d-grid">
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
            </form>
        </div>

        <!-- Social Media Contact -->
        <div class="contact-social mt-5">
            <h3>Social Media</h3>
            <p class="social-item"><i class="fas fa-envelope"></i> Email: <a class="social-link"
                    href="mailto:brandon1203kennethdc@gmail.com">brandon1203kennethdc@gmail.com</a></p>
            <p class="social-item"><i class="fas fa-university"></i> School Email: <a class="social-link"
                    href="mailto:cincobd@students.national-u.edu.ph">cincobd@students.national-u.edu.ph</a></p>
            <p class="social-item"><i class="fas fa-phone-alt"></i> Contact: <a class="social-link"
                    href="tel:+639612284690">+63 961 2284 690</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>