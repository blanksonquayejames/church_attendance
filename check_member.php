<?php
// check_member.php
header('Content-Type: application/json');
require_once 'config.php';

$firstName = trim($_GET['firstName'] ?? '');
$surname = trim($_GET['surname'] ?? '');

$response = [
    'is_old_member' => false,
    'department' => '',
    'location' => '',
    'dob' => '',
    'pob' => ''
];

if (empty($firstName) || empty($surname)) {
    echo json_encode($response);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT department, location, date_of_birth, place_of_birth FROM attendances WHERE first_name = :fname AND surname = :sname ORDER BY arrival_time DESC LIMIT 1");
    $stmt->execute([
        ':fname' => $firstName, 
        ':sname' => $surname
    ]);
    
    $member = $stmt->fetch();
    
    if ($member) {
        $response['is_old_member'] = true;
        // Sending back old data so we can be helpful and auto-fill it!
        $response['department'] = $member['department'];
        $response['location'] = $member['location'];
        $response['dob'] = $member['date_of_birth'];
        $response['pob'] = $member['place_of_birth'];
    }
    
    echo json_encode($response);
} catch (PDOException $e) {
    // Failing silently on backend so it doesn't alert the user
    echo json_encode($response);
}
?>
