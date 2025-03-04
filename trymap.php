<?php
// Database connection code (adjust with your credentials)
include('config.php');

// SQL query
$sql = "
    SELECT 
        Barangay,
        COUNT(CASE WHEN Sex = 'Female' THEN 1 END) AS Female,
        COUNT(CASE WHEN Sex = 'Male' THEN 1 END) AS Male,
        COUNT(*) AS Registrants,
        COUNT(CASE WHEN Age BETWEEN 60 AND 64 THEN 1 END) AS Age60_64,
        COUNT(CASE WHEN Age BETWEEN 65 AND 69 THEN 1 END) AS Age65_69,
        COUNT(CASE WHEN Age BETWEEN 70 AND 74 THEN 1 END) AS Age70_74,
        COUNT(CASE WHEN Age BETWEEN 75 AND 79 THEN 1 END) AS Age75_79,
        COUNT(CASE WHEN Age >= 80 THEN 1 END) AS Age80Plus
    FROM tblmembers
    WHERE ApplicationStatus = 'Approved'
    GROUP BY Barangay
    ORDER BY Barangay;
";

// Query to fetch approved registrants count grouped by barangay
$sqlBarangayData = "
    SELECT 
        Barangay, 
        COUNT(*) AS Registrants 
    FROM tblmembers 
    WHERE ApplicationStatus = 'Approved' 
    GROUP BY Barangay
";
// Fetch data from database (already done in your code)
$barangayData = [];
$resultBarangayData = $conn->query($sqlBarangayData);

while ($row = $resultBarangayData->fetch_assoc()) {
    $barangayData[$row['Barangay']] = $row['Registrants'];
}

// Encode barangay data as JSON
$barangayDataJson = json_encode($barangayData);


$result = $conn->query($sql);

// Initialize totals
$totalRegistrants = 0;
$totalFemale = 0;
$totalMale = 0;
$totalAge60_64 = 0;
$totalAge65_69 = 0;
$totalAge70_74 = 0;
$totalAge75_79 = 0;
$totalAge80Plus = 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="registrationcount.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <title>Registration Count</title>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const header = document.querySelector('header');
        const logos = document.querySelectorAll('.logo');
        const registrationSection = document.querySelector('.RegistrationCount');
        const currentDateElement = document.getElementById('currentDate');
        const defaultPosition = [17.0345, 121.6252]; // Latitude, Longitude
        const barangayData = <?php echo $barangayDataJson ?? '{}'; ?>; // Import PHP data or fallback to empty object

        // Initialize the map
        const map = L.map('map', {
            center: defaultPosition,
            zoom: 12,
            minZoom: 12
        });

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Set the current date
        if (currentDateElement) {
            const currentDate = new Date().toLocaleDateString(undefined, {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
            });
            currentDateElement.textContent = currentDate;
        }

        // Add scroll effect for header and logos
        window.addEventListener('scroll', function () {
            if (window.scrollY > 50) {
                header.classList.add('header-scrolled');
                logos.forEach(logo => logo.classList.add('logo-scrolled'));
            } else {
                header.classList.remove('header-scrolled');
                logos.forEach(logo => logo.classList.remove('logo-scrolled'));
            }
        });

        // Adjust margin for the registration section
        function adjustMargin() {
            const headerHeight = header.offsetHeight;
            if (registrationSection) {
                registrationSection.style.marginTop = headerHeight + 'px';
            }
        }

        adjustMargin(); // Set margin initially
        window.addEventListener('resize', adjustMargin); // Adjust on window resize

        // Function to determine color based on registrant count
        function getColor(value) {
            if (value >= 1 && value <= 10) {
                return '#fffcad'; // Light Yellow
            } else if (value >= 11 && value <= 20) {
                return '#ffe577'; // Yellow
            } else if (value >= 21 && value <= 30) {
                return '#ffcf86'; // Light Orange
            } else if (value >= 31 && value <= 40) {
                return '#fda63a'; // Orange
            } else if (value >= 41) {
                return '#ff5a00'; // Dark Orange
            } else {
                return '#ffffff'; // Default (white)
            }
        }

        // Function to style polygons based on barangay data
        function style(feature) {
            const barangayName = feature.properties.Barangay; // Ensure your GeoJSON has "Barangay" property
            const value = barangayData[barangayName] || 0; // Get data or default to 0
            return {
                fillColor: getColor(value),
                weight: 2,
                opacity: 1,
                color: 'white',
                dashArray: '3',
                fillOpacity: 0.7
            };
        }

        // Load GeoJSON data
        fetch('GeoData/mapsanmanuel.geojson')
            .then(response => response.json())
            .then(geojsonData => {
                // Add GeoJSON to the map with dynamic styles
                L.geoJSON(geojsonData, {
                    style: style, // Apply style function
                    onEachFeature: (feature, layer) => {
                        const barangayName = feature.properties.Barangay;
                        const value = barangayData[barangayName] || 0;
                        layer.bindPopup(
                            `<strong>Barangay:</strong> ${barangayName}<br>
                             <strong>Approved Registrants:</strong> ${value}`
                        );
                    }
                }).addTo(map);
            })
            .catch(error => console.error('Error loading GeoJSON:', error));

        // Tabs functionality
        const tabs = document.querySelectorAll('.tab');
        const tabContents = document.querySelectorAll('.tab-content');

        tabs.forEach(tab => {
            tab.addEventListener('click', function () {
                // Remove active class from all tabs and contents
                tabs.forEach(t => t.classList.remove('active'));
                tabContents.forEach(tc => tc.classList.remove('active'));

                // Add active class to the clicked tab and its corresponding content
                const target = this.getAttribute('data-tab');
                this.classList.add('active');
                document.getElementById(target).classList.add('active');
            });
        });
    });

    // Menu toggle function
    function toggleMenu() {
        const menuContainer = document.querySelector('.menu-container');
        if (menuContainer) {
            menuContainer.classList.toggle('show'); // Toggle visibility on small screens
        }
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

    <section class="RegistrationCount">
        <h1>Senior Citizens Data Collection Distribution</h1>
        <h2>Online Registration Count</h2>
        <p>As of <span id="currentDate"></span></p>

        <div class="tabs">
            <div class="tab active" data-tab="mapView">Map View</div>
            <div class="tab " data-tab="tableView">Table View</div>
            <div class="tab" data-tab="chartview">Chart View</div>
        </div>


        <div id="mapView" class="tab-content active">
            <section class="ChotoplethMap">
                <h1>Senior Citizens Data Collection Distribution displayed in Choropleth Map</h1>
                <div id="map"></div>
            </section>
        </div>

         <div id="tableView" class="tab-content">
            <table class="registration-table">
                <thead>
                    <tr>
                        <th>Barangay</th>
                        <th>Registrants</th>
                        <th>Female</th>
                        <th>Male</th>
                        <th>Age 60-64</th>
                        <th>Age 65-69</th>
                        <th>Age 70-74</th>
                        <th>Age 75-79</th>
                        <th>Age 80+</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>{$row['Barangay']}</td>
                                    <td>{$row['Registrants']}</td>
                                    <td>{$row['Female']}</td>
                                    <td>{$row['Male']}</td>
                                    <td>{$row['Age60_64']}</td>
                                    <td>{$row['Age65_69']}</td>
                                    <td>{$row['Age70_74']}</td>
                                    <td>{$row['Age75_79']}</td>
                                    <td>{$row['Age80Plus']}</td>
                                </tr>";

                            // Update totals
                            $totalRegistrants += $row['Registrants'];
                            $totalFemale += $row['Female'];
                            $totalMale += $row['Male'];
                            $totalAge60_64 += $row['Age60_64'];
                            $totalAge65_69 += $row['Age65_69'];
                            $totalAge70_74 += $row['Age70_74'];
                            $totalAge75_79 += $row['Age75_79'];
                            $totalAge80Plus += $row['Age80Plus'];
                        }
                    } else {
                        echo "<tr><td colspan='9'>No data available</td></tr>";
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total</td>
                        <td><?php echo $totalRegistrants; ?></td>
                        <td><?php echo $totalFemale; ?></td>
                        <td><?php echo $totalMale; ?></td>
                        <td><?php echo $totalAge60_64; ?></td>
                        <td><?php echo $totalAge65_69; ?></td>
                        <td><?php echo $totalAge70_74; ?></td>
                        <td><?php echo $totalAge75_79; ?></td>
                        <td><?php echo $totalAge80Plus; ?></td>
                    </tr>
                </tfoot>
            </table>
        
    </section>
   

    

</body>

</html>
