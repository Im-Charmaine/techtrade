<?php
// auth.php - Authentication and user session functions
// This file handles who is logged in and what they can do

// Start the session so we remember the logged in user
session_start();

// Check if someone is logged in
function is_logged_in() {
    if (isset($_SESSION['user_id'])) {
        return true;
    }
    return false;
}

// Get the role of the logged in user
function get_role() {
    if (isset($_SESSION['role'])) {
        return $_SESSION['role'];
    }
    return "";
}

// Check if user is a seller
function is_seller() {
    return get_role() == "seller";
}

// Check if user is a buyer
function is_buyer() {
    return get_role() == "buyer";
}

// Check if user is an admin
function is_admin() {
    return get_role() == "admin";
}

// Send user to login page if not logged in
function require_login() {
    if (!is_logged_in()) {
        header("Location: /login.php");
        exit();
    }
}

// Send user away if they are not a seller
function require_seller() {
    require_login();
    if (!is_seller()) {
        header("Location: /index.php");
        exit();
    }
}

// Send user away if they are not an admin
function require_admin() {
    require_login();
    if (!is_admin()) {
        header("Location: /index.php");
        exit();
    }
}

// Clean user input to stop hackers
function clean($input) {
    $input = trim($input);
    $input = strip_tags($input);
    $input = htmlspecialchars($input);
    return $input;
}

// Log what admins do for security
function log_admin_action($conn, $action, $target_type = null, $target_id = null) {
    $admin_id = $_SESSION['user_id'];
    $sql = "INSERT INTO admin_logs (admin_id, action, target_type, target_id)
            VALUES ('$admin_id', '$action', '$target_type', '$target_id')";
    mysqli_query($conn, $sql);
}
?>