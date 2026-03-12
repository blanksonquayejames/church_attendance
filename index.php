<?php
session_start();
$isAdminLoggedIn = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php if ($isAdminLoggedIn): ?>
    <!-- Tab closure security logic -->
    <script>
        if (!sessionStorage.getItem('admin_tab_active')) {
            window.location.replace('logout.php');
        }
    </script>
    <?php endif; ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Church - Attendance Form</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <div class="background-decor"></div>
    <div class="container">
        <header class="form-header">
            <div class="logo-container">
                <i class="fas fa-church church-icon"></i>
            </div>
            <h1>Welcome to Church</h1>
            <p>Please fill out this form to register your attendance</p>
            <div style="margin-top: 1rem;">
                <?php if ($isAdminLoggedIn): ?>
                    <a href="dashboard.php" style="color: white; text-decoration: none; font-size: 0.9rem; font-weight: 500; display: inline-flex; align-items: center; gap: 0.5rem; background: var(--primary-color); padding: 0.4rem 0.8rem; border-radius: 20px; transition: var(--transition); box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);">
                        <i class="fa-solid fa-gauge-high"></i> Go to Dashboard
                    </a>
                <?php else: ?>
                    <a href="login.php" style="color: var(--primary-color); text-decoration: none; font-size: 0.9rem; font-weight: 500; display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(99, 102, 241, 0.1); padding: 0.4rem 0.8rem; border-radius: 20px; transition: var(--transition);">
                        <i class="fa-solid fa-user-shield"></i> Admin Login
                    </a>
                <?php endif; ?>
            </div>
        </header>

        <form id="attendanceForm" action="#" method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="firstName">First Name</label>
                    <div class="input-wrapper">
                        <i class="far fa-user"></i>
                        <input type="text" id="firstName" name="firstName" placeholder="Enter your first name" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="surname">Surname</label>
                    <div class="input-wrapper">
                        <i class="far fa-user"></i>
                        <input type="text" id="surname" name="surname" placeholder="Enter your surname" required>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="department">Department</label>
                    <div class="input-wrapper">
                        <i class="fas fa-users"></i>
                        <input type="text" id="department" name="department" placeholder="Your department" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="arrivalTime">Time and Date of Arrival</label>
                    <div class="input-wrapper">
                        <i class="far fa-clock"></i>
                        <input type="datetime-local" id="arrivalTime" name="arrivalTime" required>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Membership Status</label>
                    <div class="radio-group">
                        <label class="radio-label">
                            <input type="radio" name="membership" value="new" required>
                            <span class="radio-custom"></span>
                            New Member
                        </label>
                        <label class="radio-label">
                            <input type="radio" name="membership" value="old" required>
                            <span class="radio-custom"></span>
                            Old Member
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="invitedBy">Invited by</label>
                    <div class="input-wrapper">
                        <i class="fas fa-handshake"></i>
                        <input type="text" id="invitedBy" name="invitedBy" placeholder="Who invited you?">
                    </div>
                </div>
            </div>

            <div class="form-group full-width">
                <label for="location">Location</label>
                <div class="input-wrapper">
                    <i class="fas fa-map-marker-alt"></i>
                    <input type="text" id="location" name="location" placeholder="Your current address or location"
                        required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="dob">Date of Birth</label>
                    <div class="input-wrapper">
                        <i class="far fa-calendar-alt"></i>
                        <input type="date" id="dob" name="dob" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="pob">Place of Birth</label>
                    <div class="input-wrapper">
                        <i class="fas fa-city"></i>
                        <input type="text" id="pob" name="pob" placeholder="City or Country of birth" required>
                    </div>
                </div>
            </div>

            <div class="form-group full-width camera-group">
                <label>Face Verification Picture (Live Camera)</label>
                <div class="camera-wrapper" id="cameraContainer">
                    <div class="camera-content" id="cameraStartView">
                        <i class="fas fa-camera"></i>
                        <p>Click below to start camera</p>
                        <button type="button" id="startCameraBtn" class="action-btn">Start Camera</button>
                    </div>

                    <div id="videoContainer" class="video-container hidden">
                        <video id="cameraFeed" autoplay playsinline></video>
                        <canvas id="cameraCanvas" hidden></canvas>
                        <button type="button" id="captureBtn" class="action-btn capture-btn">
                            <i class="fas fa-circle"></i> Capture Photo
                        </button>
                    </div>

                    <div id="imagePreview" class="image-preview hidden">
                        <img id="previewImg" src="" alt="Captured Photo">
                        <input type="hidden" id="faceVerificationData" name="faceVerificationData">
                        <div class="preview-actions">
                            <button type="button" class="action-btn retake-btn" id="retakeBtn">
                                <i class="fas fa-redo"></i> Retake
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="submit-btn">
                    <span>Submit Details</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </form>
    </div>

    <script src="script.js?v=7"></script>
</body>

</html>