<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Simple Blog Website</title>
    <link href="../../bootstrap/bootstrap-5.0.2-dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* General Styling */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
        }

        /* About Section Styling */
        .about-section {
            margin-top: 50px;
            padding: 40px 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .about-title {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 20px;
            color: #198754;
        }

        .about-text {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #555;
        }

        /* Developers Section Styling */
        .developers-section {
            margin-top: 50px;
            padding: 40px 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .developers-title {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 30px;
            color: #198754;
            text-align: center;
        }

        .developer-card {
            text-align: center;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            background-color: #f8f9fa;
        }

        .developer-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 4px solid #198754;
        }

        .developer-name {
            font-size: 1.25rem;
            font-weight: bold;
            margin-bottom: 5px;
            color: #333;
        }

        .developer-details {
            font-size: 0.95rem;
            color: #666;
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

    <!-- Developers Section -->
    <div class="container developers-section">
        <h2 class="developers-title">Developers</h2>
        <div class="row">
            <!-- Developer 1 -->
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="developer-card">
                    <img src="../../assets/images/developers/cinco.jpg" alt="Developer 1" class="developer-image">
                    <div class="developer-name">Brandon Kenneth Cinco</div>
                    <div class="developer-details">INF225<br>BSIT - Web and Mobile Applications</div>
                </div>
            </div>
            <!-- Developer 2 -->
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="developer-card">
                    <img src="../../assets/images/developers/david.jpg" alt="Developer 2" class="developer-image">
                    <div class="developer-name">Eiron Clark David</div>
                    <div class="developer-details">INF225<br>BSIT - Web and Mobile Applications</div>
                </div>
            </div>
            <!-- Developer 3 -->
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="developer-card">
                    <img src="developer3.jpg" alt="Developer 3" class="developer-image">
                    <div class="developer-name">Axcel Bryan Garcia</div>
                    <div class="developer-details">INF225<br>BSIT - Web and Mobile Applications</div>
                </div>
            </div>
            <!-- Developer 4 -->
            <div class="col-md-3 col-sm-6 mb-4">
                <div class="developer-card">
                    <img src="../../assets/images/developers/sunga.jpg" alt="Developer 4" class="developer-image">
                    <div class="developer-name">Carl Stuart Sunga</div>
                    <div class="developer-details">INF225<br>BSIT - Web and Mobile Applications</div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>