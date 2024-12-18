<?php
// submitReprintRequest.php
include('config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $lastname = trim($_POST['lastname']);
    $firstname = trim($_POST['firstname']);
    $middleInitial = trim($_POST['middleInitial']);
    $suffix = trim($_POST['suffix']);
    $reason = trim($_POST['reason']);
    $uploadReasonFile = $_FILES['upload-reason-file'];
    $capturedReasonData = trim($_POST['captured-reason-data']);

    // Combine name fields to create Fullname for tblreprint_requests
    $fullname = trim("$firstname $middleInitial $lastname $suffix");

    // Format DOB from form input
    $birthYear = $_POST['birthYear'];
    $birthMonth = str_pad($_POST['birthMonth'], 2, "0", STR_PAD_LEFT);
    $birthDay = str_pad($_POST['birthDay'], 2, "0", STR_PAD_LEFT);
    $dob = "$birthYear-$birthMonth-$birthDay";

    // Automatically set the request date (since the input is disabled)
    $requestDate = date('Y-m-d');

    // Validate required fields
    if (empty($lastname) || empty($firstname) || empty($middleInitial) || empty($dob) || empty($reason)) {
        echo "All required fields must be filled out!";
        exit();
    }

    // Look up the SCID in tblmembers using individual name fields and DOB
    $scid = null;
    $stmt = $conn->prepare("SELECT SCID FROM tblmembers WHERE Firstname = ? AND MI = ? AND Surname = ? AND Suffix = ? AND DOB = ?");
    $stmt->bind_param("sssss", $firstname, $middleInitial, $lastname, $suffix, $dob);
    $stmt->execute();
    $stmt->bind_result($scid);
    $stmt->fetch();
    $stmt->close();

    // Handle image upload and captured data as binary data (LONG BLOB)
    $reasonImageBlob = null;

    // Check if a file was uploaded
    if ($uploadReasonFile && $uploadReasonFile['tmp_name']) {
        if ($uploadReasonFile['size'] > 10 * 1024 * 1024) { // Max size 10MB
            echo "File is too large! Maximum size is 10MB.";
            exit();
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array(mime_content_type($uploadReasonFile['tmp_name']), $allowedMimeTypes)) {
            echo "Invalid file type! Only JPG, PNG, and GIF images are allowed.";
            exit();
        }

        $reasonImageBlob = file_get_contents($uploadReasonFile['tmp_name']);
    }
    // Check if the captured data is provided
    elseif (!empty($capturedReasonData)) {
        $reasonImageBlob = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $capturedReasonData));
    }

    // Ensure we have an image to save
    if (is_null($reasonImageBlob)) {
        echo "No valid image was provided. Please upload or capture a reason photo.";
        exit();
    }

    // Insert the reprint request into tblreprint_requests with or without SCID
    $stmt = $conn->prepare("INSERT INTO tblreprint_requests (SCID, Fullname, DOB, requestDate, reason, reason_image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $scid, $fullname, $dob, $requestDate, $reason, $reasonImageBlob);

    try {
        if ($stmt->execute()) {
            echo "Your request has been submitted successfully.";
        } else {
            echo "There was an error submitting your request: " . $stmt->error;
        }
    } catch (mysqli_sql_exception $e) {
        echo "Error: " . $e->getMessage();
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>
