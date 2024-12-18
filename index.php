<?php
// Database connection details
include('config.php');

// SQL query to count ApprovedStatus
$sql = "SELECT COUNT(*) AS approved_count FROM tblmembers WHERE ApplicationStatus = 'Approved'";
$result = $conn->query($sql);

// Initialize count
$approvedCount = 0;

if ($result->num_rows > 0) {
    // Fetch the result
    $row = $result->fetch_assoc();
    $approvedCount = $row['approved_count'];
}


$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="responsiveindex.css">
    <title>Index</title>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const header = document.querySelector('header');
            const logos = document.querySelectorAll('.logo');

            window.addEventListener('scroll', function () {
                if (window.scrollY > 50) {
                    header.classList.add('header-scrolled');
                    logos.forEach(logo => logo.classList.add('logo-scrolled'));
                } else {
                    header.classList.remove('header-scrolled');
                    logos.forEach(logo => logo.classList.remove('logo-scrolled'));
                }
            });
        });

        function toggleMenu() {
            const menuContainer = document.querySelector('.menu-container');
            menuContainer.classList.toggle('show'); // Toggle visibility on small screens
        }
    </script>
</head>

<body>
    <header>
        <div class="container">
            <div class="logo-section left-logos">
                <img src="Images/images.png" alt="MSWD Logo" class="logo">
                <img src="Images/Artboard 1.png" alt="Goldenage Logo" class="logo">
                <span class="logo-label">Municipal Social Welfare and Development<br>San Manuel, Isabela</span>
            </div>

            <button class="menu-toggle" onclick="toggleMenu()">☰</button>

            <div class="menu-container" id="menu">
                <nav class="nav-links">
                    <a href="index.php">Home</a>
                    <a href="#about">About</a>
                    <a href="#services">Services</a>
                </nav>

                <div class="logo-section right-logos">
                    <img src="Images/olplogo.png" alt="OLPCSMI Logo" class="logo">
                    <img src="Images/bsitlogo.png" alt="BSIT Logo" class="logo">
                </div>
            </div>
        </div>
    </header>



    <section class="registration">
        <div class="content">
            <div class="text-content">
                <h1>REGISTER<br>BE COUNTED</h1>
                <p>Let us build a reliable database of all Senior Citizens of the Municipality of San Manuel. Join the community, register, and be counted today!</p>
            </div>
            <div class="image-content">
                <img src="Images\senior using phone.jpg" alt="People using a mobile phone">
            </div>
        </div>
    </section>

    <section class="stats-section">
        <div class="stats-container">
            <div class="registrants">
                <h2><?php echo $approvedCount; ?></h2>
                <h1>REGISTRANTS AS OF TODAY</h1>
                <p class="info"><i>We’re currently receiving a high volume of submission requests. Our online registration service might experience transmission difficulty issues. Please bear with us.</i></p>
                
                <div class="action-buttons">
                    <a href="registrationindex.html" class="register-button">REGISTER NOW!</a>
                    <a href="verifyform.html" class="verify-button">VERIFY HERE!</a>
                </div>
            </div>

            <div class="links">
                <div class="register-info">
                    <img src="Images/how.png" alt="Register Icon" class="icon">
                    <div class="text">
                        <h3>How to register?</h3>
                        <a href="#">Online Registration User Guide</a>
                    </div>
                </div>
                <div class="registration-count">
                    <img src="Images/analytics.png" alt="Count Icon" class="icon">
                    <div class="text">
                        <h3>View Registration Count</h3>
                        <a href="newRegistrationCount.php">Registration Distribution by Barangay</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="services" class="service-section">
        <div class="content">
            <!-- Service Subsection Title with Centering Class -->
            <h2 class="section-ourservices center-heading">Our Services</h2>

            <!-- Main Section Title with Centering Class -->
            <h1 class="section-heading center-heading">What Do You Need?</h1>

            <div class="service-list">
                <div class="service-item">
                    <div class="service-details">
                        <h3 class="service-title">Request Reprint ID</h3>
                        <p class="service-description">If you need a reprint of your Senior Citizen ID, click the button below to make a request.</p>
                        <!-- Link the button to RequestRePrintID.html -->
                        <a href="RequestRePrintID.html" class="service-button request-button">Request Reprint ID</a>
                    </div>
                </div>

                <div class="service-item">
                    <div class="service-details">
                        <h3 class="service-title">Schedule Appointment</h3>
                        <p class="service-description">Schedule an appointment with the National Commission of Senior Citizens for various services.</p>
                        <a href="ScheduleApppointment.html" class="service-button request-button">Schedule Appointment</a>
                    </div>
                </div>
            </div>
        </div>
    </section>




    <section class="announcement-section">
        <div class="content">
            <h2 class="section-title">Latest Announcements</h2>
            <div class="announcement-list">
                <!-- PHP script to load announcements -->
                 <?php include 'get_announcements.php'; ?>
            </div>
        </div>
    </section>

    <section id="about" class="about-section">
        <div class="content">
            <h2 class="section-title">About Us</h2>
            <p>GOLDENAGE is a cutting-edge Senior Citizen Information System created with the goal of making life better for older adults. By integrating spatial data and Geographic Information System (GIS) technologies, we offer a user-friendly platform that helps manage and access important information for seniors. With GOLDENAGE, we use advanced mapping and analysis to ensure resources are allocated effectively and services are delivered efficiently, all tailored to the needs of the senior community.</p>
            <p>Our mission is to empower seniors by providing a simple, accessible platform that connects them to vital services and resources. We’re dedicated to creating a supportive and efficient environment that enhances their well-being and independence through the smart use of spatial data and GIS technology.</p>
            <p>We envision a future where every senior citizen has easy access to the resources and support they need to live a fulfilling and dignified life. By harnessing the power of GIS and spatial data, GOLDENAGE aspires to be at the forefront of technology-driven solutions, fostering a connected and responsive community that prioritizes the needs of its elderly members.</p>
            <p>Our organization is committed to enriching the lives of seniors through various thoughtful programs and services, making sure their needs are met with compassion and efficiency.</p>
        </div>
    </section>
    <footer>
        <div class="footer-content">
            <div class="footer-logos">
                <img src="Images/bsitlogo.png" alt="Logo 1">
                <img src="Images/Artboard 1.png" alt="Logo 2">
                <img src="Images/olplogo.png" alt="Logo 3">
            </div>
            <p>&copy; 2024 Municipal Social Welfare and Development - San Manuel, Isabela. All Rights Reserved.</p>
            <p>Contact us at: <a href="mailto:support@example.com">Goldenage@gnail.com</a></p>
        </div>
    </footer>



</body>

</html>
