<?php

// This file connects TechTrade to the MySQL database

$host     = "localhost";    // XAMPP runs MySQL on localhost
$user     = "root";         // Default XAMPP username
$password = "";             // Default XAMPP has no password
$dbname   = "techtrade";   // Our database name

// Create the connection
$conn = mysqli_connect($host, $user, $password, $dbname);

// If connection fails, stop and show error
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Set character encoding so special characters work
mysqli_set_charset($conn, "utf8mb4");
?>