<?php
// submit.php
header('Content-Type: application/json');

// Ensure this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Load database configuration and connection setup
require_once 'config.php';

// Prepare variables from the POST request
$firstName = $_POST['firstName'] ?? '';
$surname = $_POST['surname'] ?? '';
$department = $_POST['department'] ?? '';
$arrivalTime = $_POST['arrivalTime'] ?? '';
$membership = $_POST['membership'] ?? '';
$invitedBy = $_POST['invitedBy'] ?? null;
$location = $_POST['location'] ?? '';
$dob = $_POST['dob'] ?? '';
$pob = $_POST['pob'] ?? '';
$faceData = $_POST['faceVerificationData'] ?? '';

// Basic validation
if (empty($firstName) || empty($surname) || empty($department) || empty($arrivalTime) || empty($membership) || empty($location) || empty($dob) || empty($pob) || empty($faceData)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields and capture a photo.']);
    exit;
}

// Ensure the arrival time is correctly formatted for MySQL DATETIME
$arrivalTime = str_replace('T', ' ', $arrivalTime);

// Handle the base64 image decoding
// The data URL looks like data:image/png;base64,iVBORw0KGgo...
$image_parts = explode(";base64,", $faceData);
if (count($image_parts) !== 2) {
    echo json_encode(['success' => false, 'message' => 'Invalid image format uploaded.']);
    exit;
}

$image_type_aux = explode("image/", $image_parts[0]);
$image_type = $image_type_aux[1]; // extension like 'png' or 'jpeg'
$image_base64 = base64_decode($image_parts[1]);

// Create uploads directory if it doesn't exist
$upload_dir = __DIR__ . '/uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Generate a unique filename and set the file path
$filename = uniqid('attendee_', true) . '.' . $image_type;
$filepath = $upload_dir . $filename;
$publicUrl = 'uploads/' . $filename;

// Save the file to the disk
if (file_put_contents($filepath, $image_base64) === false) {
    echo json_encode(['success' => false, 'message' => 'Failed to save the captured photo on the server.']);
    exit;
}

try {
    // Insert into DB using Prepared Statements for security
    $sql = "INSERT INTO attendances (first_name, surname, department, arrival_time, membership_status, invited_by, location, date_of_birth, place_of_birth, face_picture_url)
            VALUES (:fname, :sname, :dept, :arrtime, :mem, :invby, :loc, :dob, :pob, :face)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':fname' => $firstName,
        ':sname' => $surname,
        ':dept' => $department,
        ':arrtime' => $arrivalTime,
        ':mem' => $membership,
        ':invby' => $invitedBy,
        ':loc' => $location,
        ':dob' => $dob,
        ':pob' => $pob,
        ':face' => $publicUrl
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Attendance successfully registered!'
    ]);

}
catch (PDOException $e) {
    // If DB insert fails, we might want to delete the uploaded image so we don't end up with orphaned files
    if (file_exists($filepath)) {
        unlink($filepath);
    }

    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
