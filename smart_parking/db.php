<?php
$conn = new mysqli("localhost", "root", "", "smart_parking");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
