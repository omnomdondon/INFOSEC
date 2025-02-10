<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Simple Blog Website</title>
    <link href="../../bootstrap/bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .about-section {
            margin-top: 50px;
            padding: 20px;
        }

        .about-title {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .about-text {
            font-size: 1.1rem;
            line-height: 1.6;
        }
    </style>
</head>

<body>
    <!-- Sticky Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">Simple Blog Website</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="homepage1.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- About Us Content -->
    <div class="container about-section">
        <h1 class="about-title">About Us</h1>
        <p class="about-text">
            Welcome to <strong>Simple Blog Website</strong>, your go-to platform for sharing and reading interesting
            blog posts! Our mission is to provide a user-friendly space for writers to express their thoughts and ideas,
            while also offering a great place for readers to explore a wide range of topics.
        </p>
        <p class="about-text">
            Whether you're passionate about technology, lifestyle, education, or personal experiences, you'll find
            something that resonates with you here. Our simple and intuitive design makes it easy for anyone to create,
            read, and share blog content.
        </p>
        <p class="about-text">
            Join us in our journey of discovering new stories and perspectives from people around the world. We believe
            in the power of words to connect and inspire, and we’re excited to have you as part of our community.
        </p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>