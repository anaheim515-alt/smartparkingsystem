<?php
include 'db.php';
$slots = $conn->query("SELECT * FROM parking_slots");
while($row = $slots->fetch_assoc()){
    $class = strtolower($row['status']);
    echo "<tr><td>{$row['slot_id']}</td><td class='$class'>{$row['status']}</td></tr>";
}
?>
