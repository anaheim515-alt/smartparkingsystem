<?php
include 'db.php';

if(isset($_POST['plate'])){
    $plate = strtoupper(trim($_POST['plate']));

    // Validate plate format (AAA 111 or ABC 1234)
    if(!preg_match('/^[A-Z]{3}\s\d{3,4}$/', $plate)){
        exit("Invalid plate format. Use AAA 111 or ABC 1234");
    }

    // Check active transaction
    $stmt = $conn->prepare("SELECT * FROM transactions WHERE plate_number=? AND time_out IS NULL");
    $stmt->bind_param("s", $plate);
    $stmt->execute();
    $res = $stmt->get_result();

    if($res->num_rows > 0){
        $row = $res->fetch_assoc();
        $slot_id = $row['slot_id'];

        // Use timezone-aware calculation to prevent wrong fees
        $timezone = new DateTimeZone('Asia/Manila'); // set to your local timezone
        $time_in = new DateTime($row['time_in'], $timezone);
        $time_out = new DateTime('now', $timezone);

        // Calculate total seconds and convert to hours, round up partial hours
        $seconds = $time_out->getTimestamp() - $time_in->getTimestamp();
        $hours = ceil($seconds / 3600); // 1 hour minimum for short stays

        $rate_per_hour = 30; // PHP 30 per hour
        $fee = $hours * $rate_per_hour;

        // Update transaction
        $stmt_update = $conn->prepare("UPDATE transactions SET time_out=NOW(), fee=? WHERE id=?");
        $stmt_update->bind_param("di", $fee, $row['id']);
        $stmt_update->execute();

        // Free the parking slot
        $stmt_slot = $conn->prepare("UPDATE parking_slots SET status='Available' WHERE slot_id=?");
        $stmt_slot->bind_param("i", $slot_id);
        $stmt_slot->execute();

        // Return message WITHOUT print receipt link
        echo "Car $plate left slot $slot_id. Parking Fee: PHP $fee.";
    } else {
        echo "No active parking record found for plate $plate";
    }
}
?>
