<?php
// db.php - Database connection
// This file connects TechTrade to the MySQL database

$host     = "sql207.infinityfree.com";    
$user     = "if0_42100238";         
$password = "VBXU1X8lRZTCpjV";             
$dbname   = "if0_42100238_techtrade";   

// Create the connection
$conn = mysqli_connect($host, $user, $password, $dbname);

// If connection fails, stop and show error
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Set character encoding so special characters work
mysqli_set_charset($conn, "utf8mb4");

//helper function for secure database queries that uses prepared statements to stop sql injections
function secure_query($conn, $sql, $params = []) {
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt === false) {
        return false;
    }
    if (!empty($params)) {
        $types = $params[0]; // e.g "ssi" for string, string, integer
        $values = array_slice($params, 1); // actual values
        mysqli_stmt_bind_param($stmt, $types, ...$values);
    }

    mysqli_stmt_execute($stmt);
    return $stmt;
}
?>