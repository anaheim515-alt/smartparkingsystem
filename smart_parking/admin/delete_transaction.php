<?php
include '../db.php';

if(isset($_GET['id'])){
    $id = intval($_GET['id']); // secure integer conversion
    $conn->query("DELETE FROM transactions WHERE id=$id");
}

header("Location: dashboard.php");
exit;
?>
