<?php
include '../db.php';

if(!isset($_GET['id'])) exit("Invalid Request");

$id = intval($_GET['id']);
$res = $conn->query("SELECT * FROM transactions WHERE id=$id");
if($res->num_rows == 0) exit("Transaction not found");

$row = $res->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Parking Receipt</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .receipt { width: 400px; margin: auto; border: 1px solid #333; padding: 20px; }
        h2 { text-align: center; }
        table { width: 100%; margin-top: 10px; border-collapse: collapse; }
        td { padding: 6px 0; }
        .total { font-weight: bold; }
        button { margin-top: 20px; padding: 8px 12px; cursor: pointer; }
    </style>
</head>
<body>
<div class="receipt">
    <h2>Parking Receipt</h2>
    <table>
        <tr><td>Plate Number:</td><td><?php echo $row['plate_number']; ?></td></tr>
        <tr><td>Slot:</td><td><?php echo $row['slot_id']; ?></td></tr>
        <tr><td>Time In:</td><td><?php echo $row['time_in']; ?></td></tr>
        <tr><td>Time Out:</td><td><?php echo $row['time_out']; ?></td></tr>
        <tr class="total"><td>Fee:</td><td>PHP <?php echo $row['fee']; ?></td></tr>
    </table>
    <button onclick="window.print()">Print Receipt</button>
</div>
</body>
</html>
