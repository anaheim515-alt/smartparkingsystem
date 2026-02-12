<?php
session_start();
include '../db.php';
if(!isset($_SESSION['admin'])) exit("Access Denied");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sales</title>
<link rel="stylesheet" href="../css/style.css">
<style>
/* Additional styling for Total Sales box */
.total-sales {
    margin-top: 30px;             /* space below table */
    padding: 15px 20px;           /* padding inside the box */
    background-color: #35408e;    /* NU blue */
    color: #ffd41c;               /* NU gold */
    font-size: 18px;
    font-weight: bold;
    border-radius: 10px;
    text-align: right;            /* align right */
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

/* Table container to add spacing */
.table-container {
    width: 100%;
    overflow-x: auto;
    margin-top: 20px;
}
</style>
</head>
<body>
<div class="container dashboard-container">
    <h1>Sales</h1>

    <?php
    // Only show completed transactions (time_out not null)
    $res = $conn->query("SELECT * FROM transactions WHERE time_out IS NOT NULL ORDER BY id DESC");
    $total_sales = 0;
    ?>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Plate</th>
                    <th>Slot</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Fee (PHP)</th>
                    <th>Receipt</th>
                </tr>
            </thead>
            <tbody>
            <?php
            while($r = $res->fetch_assoc()){
                $fee = $r['fee'] ?? 0;
                $total_sales += $fee;
                echo "<tr>
                    <td>{$r['plate_number']}</td>
                    <td>{$r['slot_id']}</td>
                    <td>{$r['time_in']}</td>
                    <td>{$r['time_out']}</td>
                    <td>$fee</td>
                    <td><a href='receipt.php?id={$r['id']}' target='_blank'><button>Print Receipt</button></a></td>
                </tr>";
            }
            ?>
            </tbody>
        </table>

        <!-- Total Sales Box -->
        <h3 class="total-sales">Total Sales: PHP <?php echo $total_sales; ?></h3>
    </div>

    <form action="dashboard.php" method="GET" style="margin-top:20px;">
        <button type="submit">Back to Dashboard</button>
    </form>
</div>
</body>
</html>
