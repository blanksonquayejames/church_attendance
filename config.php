<?php
// config.php
// Database configuration settings
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // default xampp/wamp user
define('DB_PASS', '');     // default xampp/wamp password
define('DB_NAME', 'church_attendance');

try {
    // Attempt to connect to the database (if it exists)
    $dsn = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Create the database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    $pdo->exec("USE " . DB_NAME);

    // Create the table if it doesn't exist
    $tableQuery = "
    CREATE TABLE IF NOT EXISTS attendances (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(100) NOT NULL,
        surname VARCHAR(100) NOT NULL,
        department VARCHAR(100) NOT NULL,
        arrival_time DATETIME NOT NULL,
        membership_status VARCHAR(20) NOT NULL,
        invited_by VARCHAR(150),
        location VARCHAR(255) NOT NULL,
        date_of_birth DATE NOT NULL,
        place_of_birth VARCHAR(150) NOT NULL,
        face_picture_url VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    $pdo->exec($tableQuery);

    // Create admins table
    $adminTableQuery = "
    CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $pdo->exec($adminTableQuery);

    // Insert default admin if none exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM admins");
    if ($stmt->fetchColumn() == 0) {
        $defaultUsername = 'admin';
        $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO admins (username, password) VALUES ('$defaultUsername', '$defaultPassword')");
    }

} catch (PDOException $e) {
    die(json_encode([
        'success' => false, 
        'message' => 'Database connection failed: ' . $e->getMessage()
    ]));
}
?>
