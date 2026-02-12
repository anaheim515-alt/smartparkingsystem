<?php
include 'db.php';
$slots = $conn->query("SELECT slot_id FROM parking_slots WHERE status='Available'");
$data = [];
while($row = $slots->fetch_assoc()){
    $data[] = $row;
}
header('Content-Type: application/json');
echo json_encode($data);
