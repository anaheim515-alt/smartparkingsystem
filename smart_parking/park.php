<?php
include 'db.php';

if(isset($_POST['plate'], $_POST['slot_id'])){
    $plate = strtoupper(trim($_POST['plate']));
    $slot_id = intval($_POST['slot_id']);

    // Validate plate format: AAA 111 or ABC 1234
    if(!preg_match('/^[A-Z]{3}\s\d{3,4}$/', $plate)){
        exit("Invalid plate format. Use AAA 111 or ABC 1234");
    }

    // 1️⃣ Check if this car is already parked
    $stmt_check = $conn->prepare("SELECT * FROM transactions WHERE plate_number=? AND time_out IS NULL");
    $stmt_check->bind_param("s", $plate);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();
    if($res_check->num_rows > 0){
        exit("Car $plate is already parked and cannot be parked again!");
    }

    // 2️⃣ Check if slot is still available
    $stmt_slot = $conn->prepare("SELECT * FROM parking_slots WHERE slot_id=? AND status='Available'");
    $stmt_slot->bind_param("i", $slot_id);
    $stmt_slot->execute();
    $res_slot = $stmt_slot->get_result();
    if($res_slot->num_rows === 0) exit("Slot $slot_id is no longer available!");

    // 3️⃣ Occupy the slot
    $stmt_update = $conn->prepare("UPDATE parking_slots SET status='Occupied' WHERE slot_id=?");
    $stmt_update->bind_param("i", $slot_id);
    $stmt_update->execute();

    // 4️⃣ Insert transaction
    $stmt_insert = $conn->prepare("INSERT INTO transactions (plate_number, slot_id, time_in) VALUES (?, ?, NOW())");
    $stmt_insert->bind_param("si", $plate, $slot_id);
    $stmt_insert->execute();

    exit("Car $plate parked in slot $slot_id");
}
?>
