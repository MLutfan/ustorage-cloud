<?php
// process/logger.php

function catatLog($conn, $username, $role, $action, $detail, $status) {
    // Mencegah SQL Injection
    $username = $conn->real_escape_string($username);
    $role = $conn->real_escape_string($role);
    $action = $conn->real_escape_string($action);
    $detail = $conn->real_escape_string($detail);
    $status = $conn->real_escape_string($status);

    // Query untuk menyimpan ke tabel logs
    $query = "INSERT INTO logs (username, role, action, detail, status) 
              VALUES ('$username', '$role', '$action', '$detail', '$status')";
    
    $conn->query($query);
}
?>