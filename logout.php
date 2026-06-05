<?php
// logout.php - Logs the user out
// Destroys the session and sends user to homepage

require_once 'includes/auth.php';

// Clear all session data
session_unset();
session_destroy();

// Send user back to homepage
header("Location: /index.php");
exit();
?>