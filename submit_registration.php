<?php
header('Content-Type: application/json');

// Include the database connection file
include('config.php');

// Error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Generate SCID (Senior Citizen ID)
    $scid = generateSCID();

    // Retrieve form data and sanitize inputs
    $surname = trim($_POST['surname']);
    $firstname = trim($_POST['firstname']);
    $mi = trim($_POST['mi']);
    $suffix = trim($_POST['suffix']);
    $dob = $_POST['dob-year'] . '-' . $_POST['dob-month'] . '-' . $_POST['dob-day'];
    $age = intval($_POST['age']);
    $sex = trim($_POST['sex']);
    $placeOfBirth = trim($_POST['placeOfBirth']);
    $civilStatus = trim($_POST['civilStatus']);
    $municipality = trim($_POST['municipality']);
    $barangay = trim($_POST['barangay']);
    $purok = trim($_POST['purok']);
    $educational = trim($_POST['educational']);
    $occupation = trim($_POST['occupation']);
    $income = floatval($_POST['income']);
    $skills = trim($_POST['skills']);
    $pensioner = trim($_POST['pensioner']);

    // Family composition data
    $family_names = $_POST['family_name'];
    $relationships = $_POST['relationship'];
    $family_ages = $_POST['family_age'];
    $family_civil_statuses = $_POST['family_civil_status'];
    $family_occupations = $_POST['family_occupation'];

    // Designated beneficiary data
    $beneficiary_names = $_POST['beneficiary_name'];
    $beneficiary_relationships = $_POST['beneficiary_relationship'];
    $beneficiary_ages = $_POST['beneficiary_age'];
    $beneficiary_civil_statuses = $_POST['beneficiary_civil_status'];
    $beneficiary_occupations = $_POST['beneficiary_occupation'];

    // Health profile data
    $bloodType = trim($_POST['bloodType']);
    $dentalConcern = isset($_POST['dentalConcern']) ? implode(', ', $_POST['dentalConcern']) : '';
    $dentalOther = trim($_POST['dentalOther']);
    $visualConcerns = isset($_POST['visualConcerns']) ? implode(', ', $_POST['visualConcerns']) : '';
    $visualOther = trim($_POST['visualOther']);
    $physicalDisability = trim($_POST['physicalDisability']);
    $areaOfDifficulty = isset($_POST['areaOfDifficulty']) ? implode(', ', $_POST['areaOfDifficulty']) : '';
    $areaOther = trim($_POST['areaOther']);
    $auralCondition = isset($_POST['auralCondition']) ? implode(', ', $_POST['auralCondition']) : '';
    $auralOther = trim($_POST['auralOther']);
    $healthProblems = isset($_POST['healthProblems']) ? implode(', ', $_POST['healthProblems']) : '';
    $healthOther = trim($_POST['healthOther']);

    // Check for duplicate entry
    $checkDuplicateQuery = "SELECT * FROM tblmembers WHERE Surname = ? AND Firstname = ? AND MI = ? AND DOB = ?";
    $stmt = $conn->prepare($checkDuplicateQuery);
    $stmt->bind_param("ssss", $surname, $firstname, $mi, $dob);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'A record with the same Surname, Firstname, MI, and Date of Birth already exists.'
        ]);
        exit;
    }

    // Initialize blobs for photo and certificate
    $photoBlob = handleFileUpload('captured-photo', 'upload-photo-file');
    $certificateBlob = handleFileUpload('captured-certificate', 'upload-certificate-file');

    // Insert main applicant data
    // Insert main applicant data
    $insertQuery = "INSERT INTO tblmembers 
        (SCID, Surname, Firstname, MI, Suffix, DOB, Age, Sex, PlaceOfBirth, CivilStatus, Municipality, Barangay, Purok, EducationalAttainment, Occupation, AnnualIncome, OtherSkills, PensionerStatus, Photo, BirthCertificate, ApplicationStatus) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("ssssssssssssssssssss", 
        $scid, $surname, $firstname, $mi, $suffix, $dob, $age, $sex, $placeOfBirth, $civilStatus, $municipality, $barangay, $purok, $educational, $occupation, $income, $skills, $pensioner, $photoBlob, $certificateBlob);

    if ($stmt->execute()) {
        saveFamilyComposition($conn, $scid, $family_names, $relationships, $family_ages, $family_civil_statuses, $family_occupations);
        saveBeneficiaryData($conn, $scid, $beneficiary_names, $beneficiary_relationships, $beneficiary_ages, $beneficiary_civil_statuses, $beneficiary_occupations);
        saveHealthProfile($conn, $scid, $bloodType, $dentalConcern, $dentalOther, $visualConcerns, $visualOther, $physicalDisability, $areaOfDifficulty, $areaOther, $auralCondition, $auralOther, $healthProblems, $healthOther);
        
        echo json_encode(['status' => 'success', 'message' => 'Record saved successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error saving record: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();
}

function generateSCID() {
    $prefix = 'SC';
    $random_number = rand(100000, 999999);
    return $prefix . $random_number;
}

function handleFileUpload($base64Key, $fileKey) {
    if (!empty($_POST[$base64Key])) {
        $data = preg_replace('#^data:image/\w+;base64,#i', '', $_POST[$base64Key]);
        return base64_decode($data);
    } elseif (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] == UPLOAD_ERR_OK) {
        return file_get_contents($_FILES[$fileKey]['tmp_name']);
    }
    return null;
}

function saveFamilyComposition($conn, $scid, $names, $relationships, $ages, $civilStatuses, $occupations) {
    $stmt = $conn->prepare("INSERT INTO tblfamilycomposition (SCID, Name, Relationship, Age, CivilStatus, Occupation) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $scid, $name, $relationship, $age, $civilStatus, $occupation);
    
    for ($i = 0; $i < count($names); $i++) {
        $name = $names[$i];
        $relationship = $relationships[$i];
        $age = $ages[$i];
        $civilStatus = $civilStatuses[$i];
        $occupation = $occupations[$i];
        
        // Execute each family member insert
        if (!$stmt->execute()) {
            echo json_encode(['status' => 'error', 'message' => 'Error saving family composition: ' . $stmt->error]);
            return;
        }
    }
    $stmt->close();
}

function saveBeneficiaryData($conn, $scid, $names, $relationships, $ages, $civilStatuses, $occupations) {
    $stmt = $conn->prepare("INSERT INTO tbldesignatedbeneficiary (SCID, Name, Relationship, Age, CivilStatus, Occupation) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $scid, $name, $relationship, $age, $civilStatus, $occupation);
    
    for ($i = 0; $i < count($names); $i++) {
        $name = $names[$i];
        $relationship = $relationships[$i];
        $age = $ages[$i];
        $civilStatus = $civilStatuses[$i];
        $occupation = $occupations[$i];
        
        // Execute each beneficiary insert
        if (!$stmt->execute()) {
            echo json_encode(['status' => 'error', 'message' => 'Error saving beneficiary data: ' . $stmt->error]);
            return;
        }
    }
    $stmt->close();
}

function saveHealthProfile($conn, $scid, $bloodType, $dentalConcern, $dentalOther, $visualConcerns, $visualOther, $physicalDisability, $areaOfDifficulty, $areaOther, $auralCondition, $auralOther, $healthProblems, $healthOther) {
    // Default "N/A" for empty fields
    $dentalOther = !empty($dentalOther) ? $dentalOther : 'None';
    $visualOther = !empty($visualOther) ? $visualOther : 'None';
    $physicalDisability = !empty($physicalDisability) ? $physicalDisability : 'None';
    $areaOther = !empty($areaOther) ? $areaOther : 'None';
    $auralOther = !empty($auralOther) ? $auralOther : 'None';
    $healthOther = !empty($healthOther) ? $healthOther : 'None';
    
    $stmt = $conn->prepare("INSERT INTO tblhealthprofile 
        (SCID, BloodType, DentalConcern, DentalOther, VisualConcerns, VisualOther, PhysicalDisability, AreaOfDifficulty, AreaOther, AuralCondition, AuralOther, HealthProblems, HealthOther) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssssss", $scid, $bloodType, $dentalConcern, $dentalOther, $visualConcerns, $visualOther, $physicalDisability, $areaOfDifficulty, $areaOther, $auralCondition, $auralOther, $healthProblems, $healthOther);
    
    if (!$stmt->execute()) {
        echo json_encode(['status' => 'error', 'message' => 'Error saving health profile: ' . $stmt->error]);
    }
    $stmt->close();
}
?>
