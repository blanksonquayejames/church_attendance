<?php
// dashboard.php
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$showSuccessToast = false;
if (isset($_SESSION['login_success']) && $_SESSION['login_success'] === true) {
    $showSuccessToast = true;
    unset($_SESSION['login_success']); // Clear to only show once
}

require_once 'config.php';

try {
    $stmt = $pdo->query("SELECT * FROM attendances ORDER BY arrival_time DESC");
    $attendances = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Tab closure security logic -->
    <script>
        <?php if ($showSuccessToast): ?>
        sessionStorage.setItem('admin_tab_active', 'true');
        <?php endif; ?>
        
        // If tab doesn't have the active session flag, log them out
        if (!sessionStorage.getItem('admin_tab_active')) {
            window.location.replace('logout.php');
        }
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Church Attendance Dashboard</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css?v=5">
</head>
<body>
    <?php if ($showSuccessToast): ?>
    <div id="loginToast" style="position: fixed; top: 20px; right: 20px; background: #10b981; color: white; padding: 1rem 1.5rem; border-radius: var(--border-radius-md); box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4); display: flex; align-items: center; gap: 0.75rem; z-index: 1000; font-weight: 500; transform: translateX(150%); transition: transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);">
        <i class="fa-solid fa-circle-check" style="font-size: 1.2rem;"></i>
        <span>Login successfully!</span>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toast = document.getElementById('loginToast');
            if (toast) {
                // Slide in
                setTimeout(() => {
                    toast.style.transform = 'translateX(0)';
                }, 100);
                
                // Slide out
                setTimeout(() => {
                    toast.style.transform = 'translateX(150%)';
                }, 3500);
            }
        });
    </script>
    <?php endif; ?>

    <div class="background-decor"></div>
    <div class="container dashboard-container">
        
        <div class="dashboard-header">
            <div>
                <div class="logo-container" style="width: 50px; height: 50px; font-size: 1.5rem; margin: 0 0 0.5rem 0; border-radius: 12px;">
                    <i class="fa-solid fa-church"></i>
                </div>
                <h1>Attendance Records</h1>
                <p>Overview of all submitted church attendance data.</p>
                <div style="display: inline-flex; align-items: center; gap: 0.5rem; margin-top: 0.75rem; background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.2); padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.85rem; color: var(--primary-color);">
                    <i class="fa-solid fa-circle-user"></i>
                    <span>Currently in session as: <strong><?= htmlspecialchars($_SESSION['admin_username'] ?? 'Admin') ?></strong></span>
                </div>
            </div>
            <div style="display: flex; gap: 1rem;">
                <button onclick="downloadPDF()" class="btn-back" style="background-color: var(--primary-color); border-color: var(--primary-color); color: white; cursor: pointer;">
                    <i class="fa-solid fa-download"></i> Download PDF
                </button>
                <a href="index.php" class="btn-back">
                    <i class="fa-solid fa-arrow-left"></i> Back to Form
                </a>
                <button onclick="showLogoutModal()" class="btn-back" style="color: var(--secondary-color); border-color: rgba(244, 63, 94, 0.3); background: transparent; cursor: pointer;">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> Logout
                </button>
            </div>
        </div>

        <div class="table-container" id="attendanceTable">
            <table>
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Arrival Time</th>
                        <th>Status</th>
                        <th>Invited By</th>
                        <th>Location</th>
                        <th>Date of Birth</th>
                        <th>Place of Birth</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($attendances) > 0): ?>
                        <?php foreach ($attendances as $row): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($row['face_picture_url'])): ?>
                                        <img src="<?= htmlspecialchars($row['face_picture_url']) ?>" alt="Face" class="user-img">
                                    <?php else: ?>
                                        <div class="user-img" style="display:flex; align-items:center; justify-content:center; background:#e2e8f0; color:#64748b;">
                                            <i class="fa-solid fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($row['first_name'] . ' ' . $row['surname']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($row['department']) ?></td>
                                <td><?= htmlspecialchars(date('M d, Y h:i A', strtotime($row['arrival_time']))) ?></td>
                                <td>
                                    <?php
                                        $statusClass = strtolower($row['membership_status']) == 'new' ? 'status-new' : 'status-old';
                                    ?>
                                    <span class="status-badge <?= $statusClass ?>">
                                        <?= htmlspecialchars(ucfirst($row['membership_status'])) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($row['invited_by'] ?: '-') ?></td>
                                <td><?= htmlspecialchars($row['location']) ?></td>
                                <td><?= htmlspecialchars(date('M d, Y', strtotime($row['date_of_birth']))) ?></td>
                                <td><?= htmlspecialchars($row['place_of_birth']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align: center; padding: 3rem;">
                                <i class="fa-solid fa-inbox" style="font-size: 2.5rem; color: #cbd5e1; margin-bottom: 1rem; display: block;"></i>
                                <span style="color: var(--text-muted); font-size: 1.1rem;">No attendance records found yet.</span>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- html2pdf Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function downloadPDF() {
            const element = document.getElementById('attendanceTable');
            const opt = {
                margin:       0.5,
                filename:     'church_attendance_records.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
            };

            // Temporarily hide scrolling so the whole table gets captured
            element.style.overflow = 'visible';
            
            html2pdf().set(opt).from(element).save().then(() => {
                // Restore overflow
                element.style.overflow = 'auto';
            });
        }
    </script>
    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="logout-modal-overlay">
        <div class="logout-modal-content">
            <div class="logout-icon-wrapper">
                <i class="fa-solid fa-right-from-bracket"></i>
            </div>
            <h2>Confirm Logout</h2>
            <p>Are you sure you want to log out of the admin dashboard?</p>
            <div class="logout-actions">
                <button onclick="hideLogoutModal()" class="cancel-logout-btn">Cancel</button>
                <a href="logout.php" class="confirm-logout-btn">Yes, Logout</a>
            </div>
        </div>
    </div>
    
    <style>
        .logout-modal-overlay {
            display: none; 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            background: rgba(0,0,0,0.4); 
            backdrop-filter: blur(8px); 
            z-index: 2000; 
            justify-content: center; 
            align-items: center;
        }
        
        .logout-modal-content {
            background: white; 
            padding: 2.5rem 2rem; 
            border-radius: var(--border-radius-lg); 
            width: 90%;
            max-width: 380px; 
            text-align: center; 
            box-shadow: 0 25px 50px rgba(0,0,0,0.15); 
            transform: scale(0.9); 
            opacity: 0; 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            animation: modalIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }

        .logout-icon-wrapper {
            width: 70px; 
            height: 70px; 
            background: rgba(244, 63, 94, 0.1); 
            color: var(--secondary-color); 
            border-radius: 50%; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            font-size: 2rem; 
            margin: 0 auto 1.5rem;
        }
        
        .logout-modal-content h2 {
            font-size: 1.5rem; 
            margin-bottom: 0.5rem;
            color: var(--text-main);
        }
        
        .logout-modal-content p {
            color: var(--text-muted); 
            margin-bottom: 2rem;
            line-height: 1.5;
        }
        
        .logout-actions {
            display: flex; 
            gap: 1rem; 
            justify-content: center;
        }

        .cancel-logout-btn {
            flex: 1;
            padding: 0.8rem 1.5rem; 
            border-radius: var(--border-radius-md); 
            border: 1px solid var(--border-color); 
            background: white; 
            color: var(--text-main); 
            font-weight: 600; 
            cursor: pointer; 
            transition: all 0.3s;
            font-family: inherit;
        }
        
        .cancel-logout-btn:hover {
            background: var(--input-bg);
            border-color: #cbd5e1;
        }

        .confirm-logout-btn {
            flex: 1;
            display: inline-block;
            padding: 0.8rem 1.5rem; 
            border-radius: var(--border-radius-md); 
            border: none; 
            background: var(--secondary-color); 
            color: white; 
            text-decoration: none; 
            font-weight: 600; 
            transition: all 0.3s; 
            box-shadow: 0 4px 15px rgba(244, 63, 94, 0.3);
        }
        
        .confirm-logout-btn:hover {
            background: #e11d48;
            box-shadow: 0 6px 20px rgba(244, 63, 94, 0.4);
            transform: translateY(-2px);
        }

        @keyframes modalIn {
            to { transform: scale(1); opacity: 1; }
        }
    </style>

    <script>
        function showLogoutModal() {
            document.getElementById('logoutModal').style.display = 'flex';
        }
        function hideLogoutModal() {
            document.getElementById('logoutModal').style.display = 'none';
        }
    </script>
</body>
</html>
