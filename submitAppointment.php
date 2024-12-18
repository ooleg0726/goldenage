<?php
// submitAppointment.php
include('config.php');

$response = ['status' => 'error', 'message' => 'There was an error scheduling your appointment.'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $firstname = $_POST['firstname'];
    $middleInitial = $_POST['middleInitial'];
    $lastname = $_POST['lastname']; // changed from 'surname' to 'lastname'
    $suffix = $_POST['suffix'];
    $birthYear = $_POST['birthYear'];
    $birthMonth = $_POST['birthMonth'];
    $birthDay = $_POST['birthDay'];

    $appointmentDate = $_POST['appointment-date'];
    $appointmentTime = $_POST['appointment-time'];
    $appointmentContent = $_POST['appointment-content'];

    // Validate form data
    if (empty($firstname) || empty($middleInitial) || empty($lastname) || empty($birthYear) || 
        empty($birthMonth) || empty($birthDay) || empty($appointmentDate) || 
        empty($appointmentTime) || empty($appointmentContent)) {
        $response = ['status' => 'error', 'message' => 'All fields are required!'];
        echo json_encode($response);
        exit();
    }

    // Construct Fullname
    $fullName = trim("$firstname $middleInitial $lastname $suffix");

    // Construct DOB
    $dob = $birthYear . '-' . str_pad($birthMonth, 2, '0', STR_PAD_LEFT) . '-' . str_pad($birthDay, 2, '0', STR_PAD_LEFT);

    // Find SCID based on Fullname and DOB
    $stmt = $conn->prepare("SELECT SCID FROM tblmembers WHERE Firstname = ? AND MI = ? AND Surname = ? AND Suffix = ? AND DOB = ?");
    $stmt->bind_param("sssss", $firstname, $middleInitial, $lastname, $suffix, $dob); // Use 'lastname' instead of 'surname'
    $stmt->execute();
    $result = $stmt->get_result();

    $SCID = null;
    if ($row = $result->fetch_assoc()) {
        $SCID = $row['SCID']; // SCID found
    }

    // Debug output for SCID
    if ($SCID === null) {
        // Log this
        error_log("No SCID found for Fullname: $fullName and DOB: $dob. Appointment will be saved without SCID.");
    }

    // Close the first statement
    $stmt->close();

    // Insert the appointment data into the database
    $stmt = $conn->prepare("INSERT INTO tblappointments (SCID, Fullname, DOB, appointment_date, appointment_time, appointment_content) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $SCID, $fullName, $dob, $appointmentDate, $appointmentTime, $appointmentContent);

    if ($stmt->execute()) {
        $response = ['status' => 'success', 'message' => 'Your appointment has been scheduled successfully.'];
    }

    // Close the second statement and connection
    $stmt->close();
    $conn->close();

    echo json_encode($response);
}
?>
