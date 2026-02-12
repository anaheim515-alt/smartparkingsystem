<?php
session_start();
include 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Car Parking</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: #f8f9fa;
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
        }

        .container {
            background: #ffffff;
            max-width: 1000px;
            width: 100%;
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h1 {
            color: #35408e;
            margin-bottom: 30px;
            font-size: 28px;
            letter-spacing: 1px;
        }

        /* LOGOUT BUTTON */
        .logout-btn {
            background: #e74c3c;
            color: #ffffff;
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            float: right;
            margin-bottom: 10px;
        }

        .logout-btn:hover {
            background: #c0392b;
        }

        .forms-wrapper {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }

        form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            width: 280px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        form input {
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
            color: #35408e;
            text-transform: uppercase;
            background-color: #ffffff;
        }

        form button {
            padding: 12px;
            border: none;
            border-radius: 6px;
            background: #35408e;
            color: #ffd41c;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        form button:hover {
            background: #ffd41c;
        }

        #message {
            margin-bottom: 20px;
            min-height: 28px;
        }

        .popup-msg {
            display: inline-block;
            padding: 10px 15px;
            border-radius: 8px;
            font-weight: bold;
            color: #ffffff;
        }

        .success-msg {
            background-color: #28a745;
        }

        .error-msg {
            background-color: #e74c3c;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 12px 10px;
            text-align: center;
        }

        th {
            background-color: #35408e;
            color: #ffd41c;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f1f3f5;
        }

        tr:nth-child(odd) {
            background-color: #ffffff;
        }

        .available,
        .occupied {
            display: inline-block;
            font-weight: bold;
            padding: 6px 14px;
            border-radius: 20px;
            color: #ffffff;
        }

        .available {
            background-color: #28a745;
        }

        .occupied {
            background-color: #e74c3c;
        }

        .slot-popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 380px;
            max-width: 90%;
            background-color: #35408e;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
            z-index: 1000;
            color: #ffffff;
            text-align: center;
        }

        .slot-popup h3 {
            margin-bottom: 15px;
            font-size: 20px;
        }

        .slot-button {
            margin: 5px;
            padding: 10px 15px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            color: #35408e;
            background-color: #f8f9fa;
            transition: 0.3s;
        }

        .slot-button.slot-available:hover {
            background-color: #ffd41c;
            color: #35408e;
        }

        .slot-button.slot-occupied {
            background-color: #e74c3c;
            color: #ffffff;
            cursor: not-allowed;
        }

        .popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 900;
        }

        @media (max-width: 800px) {
            .forms-wrapper {
                flex-direction: column;
                align-items: center;
            }

            form {
                width: 90%;
            }
        }
    </style>
</head>
<body>

    <div class="container">

        <h1>Smart Parking System</h1>

        <div id="message"></div>

        <div class="forms-wrapper">
            <form id="parkForm">
                <input type="text" id="plate" name="plate" placeholder="AAA 111 / ABC 1234" required>
                <button type="submit">PARK</button>
            </form>

            <form id="leaveForm">
                <input type="text" id="leavePlate" name="plate" placeholder="AAA 111 / ABC 1234" required>
                <button type="submit">LEAVE</button>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Slot</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="slotTable">
                <?php
                $slots = $conn->query("SELECT * FROM parking_slots");
                while ($row = $slots->fetch_assoc()) {
                    $class = strtolower($row['status']);
                    echo "<tr id='slot-{$row['slot_id']}'><td>{$row['slot_id']}</td><td class='$class'>{$row['status']}</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div id="popupOverlay" class="popup-overlay"></div>

    <div id="slotPopup" class="slot-popup">
        <h3>Select an Available Slot</h3>
        <div id="slotContainer"></div>
        <button onclick="closePopup()">Cancel</button>
    </div>

    <script>
        function openPopup() {
            document.getElementById('slotPopup').style.display = 'block';
            document.getElementById('popupOverlay').style.display = 'block';
        }

        function closePopup() {
            document.getElementById('slotPopup').style.display = 'none';
            document.getElementById('popupOverlay').style.display = 'none';
        }

        function isValidPlate(plate) {
            return /^[A-Z]{3} \d{3,4}$/.test(plate.toUpperCase());
        }

        document.getElementById('parkForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const plate = document.getElementById('plate').value.trim().toUpperCase();

            if (!isValidPlate(plate)) {
                alert('Invalid plate format!');
                return;
            }

            fetch('get_slots.php')
                .then(res => res.json())
                .then(slots => {
                    const container = document.getElementById('slotContainer');
                    container.innerHTML = '';

                    if (slots.length === 0) {
                        alert('No available slots!');
                        return;
                    }

                    slots.forEach(slot => {
                        const btn = document.createElement('button');
                        btn.innerText = slot.slot_id;
                        btn.className = 'slot-button slot-available';
                        btn.onclick = () => parkCar(plate, slot.slot_id);
                        container.appendChild(btn);
                    });

                    openPopup();
                });
        });

        function parkCar(plate, slot_id) {
            fetch('park.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `plate=${plate}&slot_id=${slot_id}`
            })
            .then(res => res.text())
            .then(msg => {
                showMessage(msg, true);
                closePopup();
                updateSlots();
                document.getElementById('parkForm').reset();
            });
        }

        document.getElementById('leaveForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const plate = document.getElementById('leavePlate').value.trim().toUpperCase();

            if (!isValidPlate(plate)) {
                alert('Invalid plate format!');
                return;
            }

            fetch('leave.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `plate=${plate}`
            })
            .then(res => res.text())
            .then(msg => {
                showMessage(msg, !msg.includes('No active'));
                updateSlots();
                document.getElementById('leaveForm').reset();
            });
        });

        function showMessage(msg, success = true) {
            const messageDiv = document.getElementById('message');
            messageDiv.innerHTML = `<div class="popup-msg ${success ? 'success-msg' : 'error-msg'}">${msg}</div>`;
            setTimeout(() => { messageDiv.innerHTML = ''; }, 4000);
        }

        function updateSlots() {
            fetch('slots_table.php')
                .then(res => res.text())
                .then(data => { document.getElementById('slotTable').innerHTML = data; });
        }

        setInterval(updateSlots, 10000);
    </script>
    <!-- LOGOUT BUTTON AT THE BOTTOM -->
<div class="logout-wrapper">
    <button onclick="location.href='logout.php'">Logout</button>
</div>

</body>
</html>
