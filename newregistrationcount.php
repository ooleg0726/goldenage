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
            Purok, 
            COUNT(*) AS Registrants 
        FROM tblmembers 
        WHERE ApplicationStatus = 'Approved' 
        GROUP BY Barangay, Purok
";
// Fetch data for Barangay and Purok
    $barangayData = [];
    $resultBarangayData = $conn->query($sqlBarangayData);

    while ($row = $resultBarangayData->fetch_assoc()) {
        if (!isset($barangayData[$row['Barangay']])) {
            $barangayData[$row['Barangay']] = [];
        }
        $barangayData[$row['Barangay']][$row['Purok']] = $row['Registrants'];
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
    <link rel="stylesheet" href="newregistrationcount.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Index</title>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const header = document.querySelector('header');
                const logos = document.querySelectorAll('.logo');
                const registrationSection = document.querySelector('.RegistrationCount');
                const currentDateElement = document.getElementById('currentDate');
                const defaultPosition = [17.0345, 121.6252]; // Latitude, Longitude
                const barangayData = <?php echo $barangayDataJson ?? '{}'; ?>; // Import PHP data or fallback to empty object
                const searchInput = document.getElementById('searchInput');


                function searchBarangay() {
                const searchInput = document.getElementById('searchInput'); // Get the search input field
                const searchTerm = searchInput.value.toLowerCase(); // Get the search term in lowercase
                let found = false; // Flag to check if any matching barangay was found
                let bounds = L.latLngBounds(); // Create an empty bounds object to track the bounds of matched barangays

                // Loop through each layer in the geojsonLayer
                geojsonLayer.eachLayer(function (layer) {
                    const barangayName = layer.feature.properties.Barangay.toLowerCase(); // Access the Barangay name

                    // If the barangay name includes the search term
                    if (barangayName.includes(searchTerm)) {
                        // Highlight the matching barangay by setting a custom style
                        layer.setStyle({
                            weight: 2,
                            opacity: 1,
                            color: 'green', // Highlight color
                            fillOpacity: 0.7
                        });

                        // Extend the bounds to include the matching barangay
                        bounds.extend(layer.getBounds());
                        found = true; // Set flag to true if a match is found
                    } else {
                        // Reset style for non-matching barangay
                        layer.setStyle({
                            weight: 1,
                            opacity: 0.5,
                            color: 'green',
                            fillOpacity: 0.5
                        });
                    }
                });

                // If any matching barangay is found, zoom to fit the bounds of the matching barangays
                if (found) {
                    map.fitBounds(bounds);
                } else {
                    // If no matching barangay is found, you can zoom out or reset the map to a default position
                    alert('Barangay not found');
                    map.setView([defaultLat, defaultLng], defaultZoom); // Adjust to your default lat, lng, and zoom level
                }
            }


                // Attach the search function to the input event of the search bar
                document.getElementById('searchInput').addEventListener('input', searchBarangay);


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

                // Create a legend control
                // Create a legend control
                function createLegend() {
                    const legend = L.control({ position: 'bottomright' });

                    legend.onAdd = function () {
                        const div = L.DomUtil.create('div', 'info legend');
                        const grades = [1, 11, 21, 31, 41]; // Ranges of values
                        const labels = [];

                        // Loop through the grades and generate a label with a colored square
                        for (let i = 0; i < grades.length; i++) {
                            const from = grades[i];
                            const to = grades[i + 1] ? grades[i + 1] - 1 : 100; // Adjust upper limit for last range
                            const color = getColor(from);

                            labels.push(
                                '<i style="background:' + color + '"></i> ' +
                                from + (to ? '&ndash;' + to : '+')
                            );
                        }

                        div.innerHTML = labels.join('<br>');
                        return div;
                    };

                    legend.addTo(map);
                }

                // Add the legend to the map
                createLegend();

                function createInfoPanel(map) {
                infoPanel = L.control({ position: 'topright' }); // Positions the panel in the top-right corner

                infoPanel.onAdd = function () {
                    const div = L.DomUtil.create('div', 'info legend'); // Creates a div with class 'info legend'
                    div.innerHTML = `
                        <h1>Barangay Information</h1>
                        <p>Hover over a barangay to see its details or click to zoom in.</p>
                        <h1 id="barangay-name" style="display: none;">None</h1>
                        <p id="barangay-population" style="display: none;">Population: 0</p>
                    `;
                    return div;
                };

                infoPanel.addTo(map); // Adds the control to the map
            }

            // Initialize the info panel
            createInfoPanel(map);

            // Highlight feature on hover
        // Highlight feature on hover
        function highlightFeature(e) {
                const layer = e.target;
                const barangayName = layer.feature.properties.Barangay;
                const population = barangayData[barangayName] || 0;

                // Change the style to highlight
                layer.setStyle({
                                weight: 2,
                                opacity: 1,
                                color: 'green', // Highlight color
                                fillOpacity: 0.7
                            });

                // Show the info panel and update it
                document.getElementById('barangay-name').style.display = 'block';
                document.getElementById('barangay-population').style.display = 'block';
                document.getElementById('barangay-name').textContent = barangayName;
                document.getElementById('barangay-population').textContent = `Population: ${population}`;
            }
            // Reset feature style when not hovered
            function resetHighlight(e) {
                geojsonLayer.resetStyle(e.target);

                // Reset the info panel to default values
                document.getElementById('barangay-name').style.display = 'none';
                document.getElementById('barangay-population').style.display = 'none';
            }
                // Add events to each feature
                function onEachFeature(feature, layer) {
                    layer.on({
                        mouseover: highlightFeature,
                        mouseout: resetHighlight
                    });
                }


                // Function to style polygons based on barangay data
                function style(feature) {
                    const barangayName = feature.properties.Barangay; // Ensure your GeoJSON has "Barangay" property
                    const value = barangayData[barangayName] || 0; // Get data or default to 0
                    return {
                        fillColor: getColor(value),
                        weight: 2,
                                opacity: 1,
                                color: 'green', // Highlight color
                                fillOpacity: 0.7
                    };
                }
        
                // Load GeoJSON data
                // Store GeoJSON layer globally so we can access it later
                let geojsonLayer = L.geoJSON().addTo(map);

                // Load GeoJSON data
            // Load GeoJSON and add to the map
                fetch('GeoData/mapsanmanuel.geojson')
                    .then(response => response.json())
                    .then(geojsonData => {
                        geojsonLayer = L.geoJSON(geojsonData, {
                            style: style,
                            onEachFeature: onEachFeature
                        }).addTo(map);
                    })
                    .catch(error => console.error('Error loading GeoJSON:', error));

                // Function to zoom to a specific barangay based on its name
                function zoomToBarangay(barangayName) {
                    geojsonLayer.eachLayer(layer => {
                        const feature = layer.feature;
                        if (feature.properties.Barangay === barangayName) {
                            map.fitBounds(layer.getBounds()); // Zoom to the bounds of the selected barangay
                        }
                    });
                }

                // Function to reset the map to the default position
                function resetMapPosition() {
                    map.setView(defaultPosition, 12); // Reset to default center and zoom level
                }

                // Handle checkbox change events
                const checkboxes = document.querySelectorAll('input[name="barangay"]');
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', (event) => {
                    const barangayName = event.target.value;
                    const population = barangayData[barangayName] || 0;

                    if (event.target.checked) {
                        zoomToBarangay(barangayName); // Zoom to the selected barangay
                        // Show and update the info panel when the checkbox is checked
                        document.getElementById('barangay-name').style.display = 'block';
                        document.getElementById('barangay-population').style.display = 'block';
                        document.getElementById('barangay-name').textContent = barangayName;
                        document.getElementById('barangay-population').textContent = `Population: ${population}`;
                    } else {
                        // If unchecked, reset the map position and info panel
                        resetMapPosition();
                        document.getElementById('barangay-name').style.display = 'none';
                        document.getElementById('barangay-population').style.display = 'none';
                    }
                });
            });

            
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
        
                        // Ensure charts are resized or displayed correctly when switching tabs
                        if (target === 'chartView' && barangayChart) {
                            barangayChart.resize(); // Resize chart on tab activation
                        }
                    });
                });
        
                // Prepare data for the chart
                const barangays = Object.keys(barangayData); // Barangay names
                const registrants = Object.values(barangayData); // Registrant counts
        
                let barangayChart; // Chart instance variable
        
                // Initialize the chart on demand
                function initializeChart() {
                    const ctx = document.getElementById('barangayChart').getContext('2d');
                    barangayChart = new Chart(ctx, {
                        type: 'bar', // Bar chart
                        data: {
                            labels: barangays, // X-axis labels (Barangays)
                            datasets: [{
                                label: 'Approved Registrants',
                                data: registrants, // Data points
                                backgroundColor: 'rgba(237, 137, 54, 1)', // Bar color
                                borderColor: 'rgba(75, 192, 192, 1)', // Border color
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true // Y-axis starts at 0
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'top'
                                },
                                tooltip: {
                                    enabled: true
                                }
                            }
                        }
                    });
                }

                
                
                
        
                // Initialize the chart when the tab is first clicked
                document.querySelector('[data-tab="chartView"]').addEventListener('click', function () {
                    if (!barangayChart) {
                        initializeChart();
                    }
                });
        
                // Menu toggle function
                function toggleMenu() {
                    const menuContainer = document.querySelector('.menu-container');
                    if (menuContainer) {
                        menuContainer.classList.toggle('show'); // Toggle visibility on small screens
                    }
                }

                
            });
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

    <div class="registration-title">
        <h1>Senior Citizens Data Collection Distribution</h1>
        <h2>Online Registration Count</h2>
        <p>As of <span id="currentDate"></span></p>
    </div>
    <section class="registration-count-panel">
        <div class="top-panel">
            <div class="top-left-panel">
                <h1>GOLDENAGE</h1>
                <p>Empowering seniors with tools and spatial analysis for better support.</p>

            </div>
            <div class="top-right-panel">
                Top Right Panel Content
            </div>
        </div>
        <div class="content-panel">
            <div class="side-panel">
                <div class="search-bar">
                    <input type="text" id="searchInput" placeholder="Search...">
                </div>

                <div class="barangay-checkboxes">
            <p>Select Barangay:</p>
            <label><input type="checkbox" name="barangay" value="Agliam"> Agliam</label><br>
            <label><input type="checkbox" name="barangay" value="Babanuang"> Babanuang</label><br>
            <label><input type="checkbox" name="barangay" value="Cabaritan"> Cabaritan</label><br>
            <label><input type="checkbox" name="barangay" value="Caraniogan"> Caraniogan</label><br>
            <label><input type="checkbox" name="barangay" value="Eden"> Eden</label><br>
            <label><input type="checkbox" name="barangay" value="Malalinta"> Malalinta</label><br>
            <label><input type="checkbox" name="barangay" value="Mararigue"> Mararigue</label><br>
            <label><input type="checkbox" name="barangay" value="Nueva Era"> Nueva Era</label><br>
            <label><input type="checkbox" name="barangay" value="Pisang"> Pisang</label><br>
            <label><input type="checkbox" name="barangay" value="District 1"> District 1</label><br>
            <label><input type="checkbox" name="barangay" value="District 2"> District 2</label><br>
            <label><input type="checkbox" name="barangay" value="District 3"> District 3</label><br>
            <label><input type="checkbox" name="barangay" value="District 4"> District 4</label><br>
            <label><input type="checkbox" name="barangay" value="San Francisco"> San Francisco</label><br>
            <label><input type="checkbox" name="barangay" value="Sandiat Centro"> Sandiat Centro</label><br>
            <label><input type="checkbox" name="barangay" value="Sandiat East"> Sandiat East</label><br>
            <label><input type="checkbox" name="barangay" value="Sandiat West"> Sandiat West</label><br>
            <label><input type="checkbox" name="barangay" value="Sta Cruz"> Sta Cruz</label><br>
            <label><input type="checkbox" name="barangay" value="Villanueva"> Villanueva</label><br>
        </div>

            </div>

            <div class="main-panel">
                <div class="tabs">
                    <div class="tab active" data-tab="mapView">Map View</div>
                    <div class="tab" data-tab="tableView">Table View</div>
                    <div class="tab" data-tab="chartView">Chart View</div>
                </div>
                <div id="mapView" class="tab-content active">
                    <section class="ChotoplethMap">
                        <h2>Senior Citizens Data Collection Distribution displayed in Choropleth Map</h2>
                        <div id="map"></div>
                    </section>
                </div>
                <div id="chartView" class="tab-content">
                    <section class="ChotoplethMap">
                        <h2>Barangay Registrants Chart</h2>
                        <canvas id="barangayChart" width="400" height="200"></canvas>
                    </section>
                </div>
                <div id="tableView" class="tab-content">
                    <h2>Barangay Registrants Summary</h>
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
            </div>
        </div>
    </section>
</body>

</html>
