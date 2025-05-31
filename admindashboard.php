<?php
session_start();
require_once 'config.php'; // Include database connection

// Fetch Data from Database
$reservations = $conn->query("SELECT * FROM reservations ORDER BY reservation_date ASC, reservation_time ASC");
$orders = $conn->query("SELECT * FROM menu_items");
$payments = $conn->query("SELECT * FROM payments");
$users = $conn->query("SELECT * FROM users");

// Fetch orders with reservation and customer info
$orders_query = "
    SELECT ci.*, mi.name AS item_name, mi.price AS item_price, r.reservation_date, r.reservation_time, r.name AS customer_name, r.phone_number
    FROM cart_items ci
    JOIN menu_items mi ON ci.item_id = mi.id
    JOIN reservations r ON ci.reservation_id = r.id
    ORDER BY r.reservation_date DESC, r.reservation_time DESC
";

$orders = $conn->query($orders_query);


$today = date('Y-m-d');

// Select reservations with date before today (past)
$past_reservations = $conn->query("SELECT * FROM reservations WHERE reservation_date < '$today'");

if ($past_reservations && $past_reservations->num_rows > 0) {
    $conn->begin_transaction();

    try {
        while ($row = $past_reservations->fetch_assoc()) {
            // Insert into archived_reservations
            $stmt = $conn->prepare("INSERT INTO archived_reservations (id, name, phone_number, guests, reservation_date, reservation_time, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ississs",
                $row['id'],
                $row['name'],
                $row['phone_number'],
                $row['guests'],
                $row['reservation_date'],
                $row['reservation_time'],
                $row['notes']
            );
            $stmt->execute();
            $stmt->close();

            // Delete from reservations
            $delete_stmt = $conn->prepare("DELETE FROM reservations WHERE id = ?");
            $delete_stmt->bind_param("i", $row['id']);
            $delete_stmt->execute();
            $delete_stmt->close();
        }
        $conn->commit();
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error archiving past reservations: " . $e->getMessage());
    }
}
// Fetch Metrics
$total_customers = $users->num_rows;
$total_reservations = $reservations->num_rows;
$total_orders = $orders->num_rows;
$total_payments = $conn->query("SELECT SUM(amount_paid) AS total FROM payments")->fetch_assoc()['total'] ?? 0;
$available_tables = 10; // Set your logic for available tables here, or fetch from DB
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        /* General Styles */
        body {
            font-family: "Poppins", sans-serif;
            background: linear-gradient(#2e938e, #2e6193);
            color: #ECEFF1;
            display: flex;
            height: 100vh;
            margin: 0;
        }
        .action-btn {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            color: white;
            margin-right: 5px;
        }
        /* Sidebar */
        .sidebar {
            width: 260px;
            background: linear-gradient(135deg, #2e6193, #2e8e93);
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            box-shadow: 2px 0px 10px rgba(0, 0, 0, 0.2);
        }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #FFA726;
        }
        .sidebar a {
            display: block;
            color: #ECEFF1;
            text-decoration: none;
            padding: 12px;
            background: #3e7aa2;
            text-align: center;
            border-radius: 5px;
            font-weight: bold;
            transition: 0.3s;
            cursor: pointer;
        }
        .sidebar a:hover {
            background: #546E7A;
            transform: scale(1.05);
        }
        /* Main Content */
        .main-content {
            flex: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            overflow-y: auto;
        }
        /* Metrics Section */
        .metrics {
            display: flex;
            justify-content: space-around;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            text-align: center;
            background: #3e7aa2;
        }
        .metric-box {
            background: #546E7A;
            padding: 15px;
            border-radius: 5px;
            font-weight: bold;
            min-width: 130px;
        }
        /* Table Container */
        .table-container {
            background: #ECEFF1;
            color: #37474F;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            display: none;
            max-height: 500px;
            overflow-y: auto;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table, th, td {
            border: 1px solid #B0BEC5;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background: #455A64;
            color: #ECEFF1;
        }
        tr:nth-child(even) {
            background: #CFD8DC;
        }
        tr:nth-child(odd) {
            background: #ECEFF1;
        }
        /* Buttons */
        .button {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
            color: white;
            font-weight: bold;
            text-align: center;
        }
        .edit-button {
            background-color: #28a745; /* Green */
        }
        .archive-button {
            background-color: #00bcd4; /* Cyan */
        }
        .delete-button {
            background-color: #dc3545; /* Red */
        }
        .button:hover {
            opacity: 0.9;
        }
        .action-column {
            background-color: white;
            min-width: 140px;
        }
        /* Row coloring for reservations */
        .today {
            background-color: #df6262 !important;
        }
        .within-three-days {
            background-color: #dfce62 !important;
        }
        .past {
            background-color: #64df62 !important;
        }
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                text-align: center;
                flex-direction: row;
                flex-wrap: wrap;
                gap: 10px;
                padding: 10px;
            }
            .main-content {
                padding: 10px;
            }
            .metrics {
                flex-direction: column;
                align-items: center;
                gap: 10px;
            }
        }
    </style>
    <script>
        function showTab(tabId) {
            document.querySelectorAll('.content').forEach(section => section.style.display = 'none');
            document.getElementById(tabId).style.display = 'block';
        }
    </script>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h2>SeaSide Admin Dashboard</h2>
       <!-- <a onclick="showTab('customers')">Manage Users</a>-->
        <a onclick="showTab('reservations')">Manage Reservations</a>
        <a onclick="showTab('archived')">Archived Reservations</a>
        <a onclick="showTab('orders')">Manage Orders</a>
        <br><br><br><br><br><br><br><br><br>
        <a href="user_page.php">Logout</a>
    </div>
    <!-- Main Content -->
    <div class="main-content">
        <!-- Metrics Section -->
        <div class="metrics">
            <div class="metric-box">Total Customers: <?php echo $total_customers; ?></div>
            <div class="metric-box">Total Reservations: <?php echo $total_reservations; ?></div>
            <div class="metric-box">Total Orders: <?php echo $total_orders; ?></div>
           <!-- <div class="metric-box">Total Payments: ₱<?php echo number_format($total_payments, 2); ?></div>-->
        </div>

        <!-- Manage Users Table -->
        <div id="customers" class="table-container content">
            <h3>Manage Users</h3>
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Contact</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $users->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['role']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Manage Reservations Table -->
        <div id="reservations" class="table-container content">
            <h3>Manage Reservations</h3>
            <table>
                <thead>
                    <tr>
                        <th>Reservation ID</th>
                        <th>Customer Name</th>
                        <th>Phone Number</th>
                        <th>Guests</th>
                        <th>Reservation Date</th>
                        <th>Reservation Time</th>
                        <th>Request ‎ ‎ ‎ ‎ ‎     </th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Reset result pointer for reservations, if needed
                    if ($reservations) $reservations->data_seek(0);
                    while ($row = $reservations->fetch_assoc()):
                        $reservationDate = new DateTime($row['reservation_date']);
                        $today = new DateTime();
                        $interval = $today->diff($reservationDate)->days;
                        $isToday = $reservationDate->format('Y-m-d') === $today->format('Y-m-d');
                        $isWithinThreeDays = !$isToday && $reservationDate > $today && $interval <= 3;
                        $isPast = $reservationDate < $today;

                        $rowClass = '';
                        if ($isToday) {
                            $rowClass = 'today';
                        } elseif ($isWithinThreeDays) {
                            $rowClass = 'within-three-days';
                        } elseif ($isPast) {
                            $rowClass = 'past';
                        }
                    ?>
                    <tr class="<?php echo $rowClass; ?>">
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['guests']); ?></td>
                        <td><?php echo htmlspecialchars($row['reservation_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['reservation_time']); ?></td>
                        <td><?php echo htmlspecialchars($row['notes']); ?></td>
                        <td class="action-column">
                            </a>
                            <a href="archived_reservation.php?id=<?php echo urlencode($row['id']); ?>" onclick="return confirm('Are you sure you want to archive this reservation?');" class="button archive-button" title="Archive">
                                📦 
                            </a>
                            <a href="delete_reservation.php?id=<?php echo urlencode($row['id']); ?>" onclick="return confirm('Are you sure you want to delete this reservation permanently?');" class="button delete-button" title="Delete">
                                🗑️ 
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Archived Reservations Table -->
        <div id="archived" class="table-container content">
            <h3>Archived Reservations</h3>
            <table>
                <thead>
                    <tr>
                        <th>Reservation ID</th>
                        <th>Name</th>
                        <th>Phone Number</th>
                        <th>Guests</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Request</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $archived = $conn->query("SELECT * FROM archived_reservations ORDER BY reservation_date DESC, reservation_time DESC");
                    while ($row = $archived->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['guests']); ?></td>
                        <td><?php echo htmlspecialchars($row['reservation_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['reservation_time']); ?></td>
                        <td><?php echo htmlspecialchars($row['notes']); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>


        <!-- Orders Table -->
<div id="orders" class="table-container content">
    <h3>Manage Orders</h3>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Reservation Date</th>
                <th>Reservation Time</th>
                <th>Customer Name</th>
                <th>Phone Number</th>
                <th>Item Ordered</th>
                <th>Quantity</th>
                <th>Price Each</th>
                <th>Total Price</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $orders->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['reservation_date']); ?></td>
                <td><?php echo htmlspecialchars($row['reservation_time']); ?></td>
                <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
                <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                <td>₱<?php echo number_format($row['item_price'], 2); ?></td>
                <td>₱<?php echo number_format($row['item_price'] * $row['quantity'], 2); ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>





    </div>


    <script>
        // Show the default tab on page load
        showTab('reservations');
    </script>

</body>
</html>
