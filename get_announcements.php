<?php
include('db_connection.php');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get the latest 4 announcements
$sql = "SELECT AnnouncementDate, Title, Content, Image FROM tblannouncements ORDER BY AnnouncementDate DESC LIMIT 4";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output the announcements
    while ($row = $result->fetch_assoc()) {
        // Convert the image blob to a base64 string for display
        $imageData = base64_encode($row['Image']);
        $imageSrc = 'data:image/jpeg;base64,' . $imageData;

        echo '<div class="announcement-item">';
        echo '  <div class="announcement-thumbnail">';
        echo "      <img src='{$imageSrc}' alt='{$row['Title']}'>";
        echo '  </div>';
        echo '  <div class="announcement-details">';
        echo "      <p class='announcement-date'>" . date('M d, Y', strtotime($row['AnnouncementDate'])) . "</p>";
        echo "      <h3 class='announcement-title'>{$row['Title']}</h3>";
        echo "      <p class='announcement-description'>{$row['Content']}</p>";
        echo '  </div>';
        echo '</div>';
    }
} else {
    echo '<p>No announcements found.</p>';
}

$conn->close();
?>
