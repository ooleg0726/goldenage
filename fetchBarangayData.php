<?php
include 'config.php';

// Decode incoming barangays from POST
$barangays = json_decode($_POST['barangays'], true);

if (!empty($barangays)) {
    // Prepare dynamic placeholders for the IN clause
    $placeholders = implode(',', array_fill(0, count($barangays), '?'));

    // Query to fetch data grouped by Purok within the selected Barangays
    $query = "
        SELECT 
            Purok,
            COUNT(CASE WHEN Sex = 'Female' THEN 1 END) AS Female,
            COUNT(CASE WHEN Sex = 'Male' THEN 1 END) AS Male,
            COUNT(*) AS Registrants,
            COUNT(CASE WHEN Age BETWEEN 60 AND 64 THEN 1 END) AS Age60_64,
            COUNT(CASE WHEN Age BETWEEN 65 AND 69 THEN 1 END) AS Age65_69,
            COUNT(CASE WHEN Age BETWEEN 70 AND 74 THEN 1 END) AS Age70_74,
            COUNT(CASE WHEN Age BETWEEN 75 AND 79 THEN 1 END) AS Age75_79,
            COUNT(CASE WHEN Age >= 80 THEN 1 END) AS Age80Plus
        FROM tblmembers
        WHERE ApplicationStatus = 'Approved' AND Barangay IN ($placeholders)
        GROUP BY Purok
        ORDER BY Purok;
    ";

    // Prepare and bind the query
    $stmt = $conn->prepare($query);
    $stmt->bind_param(str_repeat('s', count($barangays)), ...$barangays);
    $stmt->execute();

    // Fetch results
    $result = $stmt->get_result();

    // Prepare the data array
    $data = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    // Return the data as JSON
    echo json_encode($data);
} else {
    // If no barangays are selected, return an empty dataset
    echo json_encode([]);
}
?>
