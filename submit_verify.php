<?php
// verify.php
include('config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get verification method from the form
    $verificationMethod = $_POST['verificationMethod'];

    if ($verificationMethod == 'RRN') {
        // Verify using SCID
        $scid = $_POST['scidInput'];

        // Check if SCID is valid
        $stmt = $conn->prepare("SELECT * FROM tblcitizen WHERE SCID = ?");
        $stmt->bind_param("s", $scid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // SCID exists, verification successful
            echo "SCID verification successful. Record found!";
        } else {
            // SCID not found
            echo "SCID verification failed. No record found.";
        }

    } elseif ($verificationMethod == 'NameDob') {
        // Verify using Name and Birthday
        $firstname = $_POST['firstname'];
        $middlename = $_POST['middleInitial'];
        $lastname = $_POST['lastname'];
        $suffix = $_POST['suffix']; // optional
        $birthYear = $_POST['birthYear'];
        $birthMonth = $_POST['birthMonth'];
        $birthDay = $_POST['birthDay'];

        // Format birthdate
        $birthdate = "$birthYear-$birthMonth-$birthDay";

        // Check if name and birthdate match a record
        $stmt = $conn->prepare("SELECT * FROM tblcitizen WHERE firstname = ? AND middlename = ? AND lastname = ? AND suffix = ? AND birthdate = ?");
        $stmt->bind_param("sssss", $firstname, $middlename, $lastname, $suffix, $birthdate);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Name and birthday match, verification successful
            echo "Name and Birthday verification successful. Record found!";
        } else {
            // No match found
            echo "Name and Birthday verification failed. No record found.";
        }
    } else {
        echo "Invalid verification method selected.";
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>
