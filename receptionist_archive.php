<?php
session_start();
require_once 'config.php';

// Fetch only archived reservations
$reservations = $conn->query("SELECT * FROM reservations WHERE archived = 1 ORDER BY reservation_date DESC, reservation_time DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Archived Reservations</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background: linear-gradient(#2e938e, #2e6193);
            color: #ECEFF1;
            display: flex;
            height: 100vh;
        }
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
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
        }
        .sidebar a:hover {
            background: #546E7A;
            transform: scale(1.05);
        }
        .main-content {
            flex: 1;
            padding: 20px;
        }
        .table-container {
            background: #ECEFF1;
            color: #37474F;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            overflow-y: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #B0BEC5;
        }
        th {
            background: #455A64;
            color: #ECEFF1;
        }
        tr:nth-child(even) {
            background: #CFD8DC;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Receptionist</h2>
        <a href="receptionist_dashboard.php">Reservations</a>
        <a href="receptionist_archived.php">Archived</a>
        <a href="logout.php">Logout</a>
    </div>

    <div class="main-content">
        <div class="table-container">
            <h3>Archived Reservations</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Guests</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $reservations->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id']; ?></td>
                            <td><?= $row['name']; ?></td>
                            <td><?= $row['phone_number']; ?></td>
                            <td><?= $row['guests']; ?></td>
                            <td><?= $row['reservation_date']; ?></td>
                            <td><?= $row['reservation_time']; ?></td>
                            <td><?= $row['notes']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
