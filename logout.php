<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="refresh" content="0;url=login.php">
    <script>
        sessionStorage.removeItem('admin_tab_active');
        window.location.replace('login.php');
    </script>
</head>
<body></body>
</html>
