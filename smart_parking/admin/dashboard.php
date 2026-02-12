<?php
session_start();
include '../db.php';
if(!isset($_SESSION['admin'])) exit("Access Denied");

// Summary calculations
$total_sales_res = $conn->query("SELECT SUM(fee) as total FROM transactions WHERE time_out IS NOT NULL");
$total_sales_row = $total_sales_res->fetch_assoc();
$total_sales = $total_sales_row['total'] ?? 0;

$available_slots_res = $conn->query("SELECT COUNT(*) as count FROM parking_slots WHERE status='Available'");
$available_slots = $available_slots_res->fetch_assoc()['count'] ?? 0;

$occupied_slots_res = $conn->query("SELECT COUNT(*) as count FROM parking_slots WHERE status='Occupied'");
$occupied_slots = $occupied_slots_res->fetch_assoc()['count'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="../css/style.css">
<style>
/* DASHBOARD SUMMARY CARDS */
.dashboard-summary {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 30px;
}

.card {
    flex: 1;
    min-width: 150px;
    background-color: #35408e;   
    color: white;                 
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.card h3 {
    margin-bottom: 10px;
    color: #ffd41c; 
}

/* SEARCH BAR */
#searchInput, #activeSearch, #salesSearch {
    width: 100%;
    max-width: 300px;
    padding: 8px 12px;
    margin-bottom: 15px;
    border-radius: 6px;
    border: 1px solid #ccc;
}

/* TABLE STYLING */
.table-container {
    width: 100%;
    overflow-x: auto;
    margin-bottom: 30px;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 12px 10px;
    border: 1px solid #ddd;
    text-align: center;
}

th {
    background-color: #35408e;
    color: #ffd41c;
}

tr:nth-child(even) {
    background-color: #f8f9fa;
}

/* ACTION BUTTONS */
.action-buttons {
    display: flex;
    justify-content: center;
    gap: 5px;
}

.action-buttons button {
    padding: 6px 10px;
    font-size: 13px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    transition: 0.3s;
}

.delete-btn { background-color: #e74c3c; color: white; }
.delete-btn:hover { background-color: #c0392b; }

.receipt-btn { background-color: #ffd41c; color: #35408e; }
.receipt-btn:hover { background-color: #e6c200; }

/* SECTION HEADINGS */
.section-title {
    margin: 20px 0 10px 0;
    font-size: 20px;
    font-weight: bold;
    color: #35408e;
}

/* RESPONSIVE */
@media (max-width: 800px) {
    .dashboard-summary { flex-direction: column; }
    .action-buttons { flex-direction: column; }
    .action-buttons button { width: 100%; }
}
</style>

<script>
function confirmDelete(id){
    if(confirm("Are you sure you want to delete this transaction?")){
        window.location = "delete_transaction.php?id=" + id;
    }
}

// SEARCH FILTER
function filterTable(tableId, inputId) {
    const input = document.getElementById(inputId).value.toUpperCase();
    const table = document.getElementById(tableId);
    const tr = table.getElementsByTagName("tr");
    for(let i=1; i<tr.length; i++){
        let tdPlate = tr[i].getElementsByTagName("td")[0];
        if(tdPlate){
            tr[i].style.display = tdPlate.innerText.toUpperCase().includes(input) ? "" : "none";
        }
    }
}

// AUTO REFRESH PAGE EVERY 15 SECONDS
setInterval(() => {
    location.reload();
}, 15000);
</script>
</head>
<body>
<div class="container dashboard-container">

    <h1>Admin Dashboard</h1>

    <!-- DASHBOARD SUMMARY CARDS -->
    <div class="dashboard-summary">
        <div class="card">
            <h3>Total Sales</h3>
            <p>PHP <?php echo number_format($total_sales, 2); ?></p>
        </div>
        <div class="card">
            <h3>Available Slots</h3>
            <p><?php echo $available_slots; ?></p>
        </div>
        <div class="card">
            <h3>Occupied Slots</h3>
            <p><?php echo $occupied_slots; ?></p>
        </div>
    </div>

    <!-- ACTIVE CARS TABLE -->
    <h2 class="section-title">Active Cars</h2>
    <input type="text" id="activeSearch" onkeyup="filterTable('activeTable','activeSearch')" placeholder="Search by plate number...">
    <div class="table-container">
        <table id="activeTable">
            <thead>
                <tr>
                    <th>Plate</th>
                    <th>Slot</th>
                    <th>Time In</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $active_res = $conn->query("SELECT * FROM transactions WHERE time_out IS NULL ORDER BY time_in DESC");
            while($row = $active_res->fetch_assoc()){
                echo "<tr>
                    <td>{$row['plate_number']}</td>
                    <td>{$row['slot_id']}</td>
                    <td>{$row['time_in']}</td>
                </tr>";
            }
            ?>
            </tbody>
        </table>
    </div>

    <!-- COMPLETED SALES TABLE -->
    <h2 class="section-title">Completed Sales</h2>
    <input type="text" id="salesSearch" onkeyup="filterTable('salesTable','salesSearch')" placeholder="Search by plate number...">
    <div class="table-container">
        <table id="salesTable">
            <thead>
                <tr>
                    <th>Plate</th>
                    <th>Slot</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Fee (PHP)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $res = $conn->query("SELECT * FROM transactions WHERE time_out IS NOT NULL ORDER BY id DESC");
            while($r = $res->fetch_assoc()){
                $fee = $r['fee'] ?? 0;
                echo "<tr>
                    <td>{$r['plate_number']}</td>
                    <td>{$r['slot_id']}</td>
                    <td>{$r['time_in']}</td>
                    <td>{$r['time_out']}</td>
                    <td>$fee</td>
                    <td class='action-buttons'>
                        <button class='delete-btn' onclick='confirmDelete({$r['id']})'>Delete</button>
                        <a href='receipt.php?id={$r['id']}' target='_blank'><button class='receipt-btn'>Receipt</button></a>
                    </td>
                </tr>";
            }
            ?>
            </tbody>
        </table>
    </div>

</div>
<!-- LOGOUT BUTTON AT THE BOTTOM -->
<div class="logout-wrapper">
    <button onclick="location.href='logout.php'">Logout</button>
</div>

</body>
</html>
