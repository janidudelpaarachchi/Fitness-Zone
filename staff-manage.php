<?php
session_start();

// Redirect to login if not logged in as staff
//if (!isset($_SESSION['staff_id'])) {//
    //header("Location: login.php");
   // exit();
//}


// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fitzone_fitness";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle booking status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_booking'])) {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];
    
    $sql = "UPDATE bookings SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $status, $booking_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Booking status updated successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error updating booking: " . $conn->error;
        $_SESSION['message_type'] = "error";
    }
    $stmt->close();
    header("Location: staff-manage.php");
    exit();
}

// Handle feedback deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_feedback'])) {
    $feedback_id = $_POST['feedback_id'];
    
    $sql = "DELETE FROM feedback WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $feedback_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Feedback deleted successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error deleting feedback: " . $conn->error;
        $_SESSION['message_type'] = "error";
    }
    $stmt->close();
    header("Location: staff-manage.php");
    exit();
}

// Fetch bookings data
$bookings = [];
$result = $conn->query("SELECT * FROM bookings ORDER BY day, time");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
}

// Fetch feedback data
$feedbacks = [];
$result = $conn->query("SELECT * FROM feedback ORDER BY created_at DESC");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $feedbacks[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitZone - Staff Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f5f5f5;
            color: #333;
        }

        /* Top Header */
        .top-header {
            background-color:rgb(23, 14, 152);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-header h1 {
            font-size: 20px;
            font-weight: 600;
        }

        .logout-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .logout-btn:hover {
            background-color: #c0392b;
        }

        /* Main Content */
        .main-content {
            padding: 30px;
        }

        /* Section Headers */
        .section-header {
            color: #1e5631;
            margin: 30px 0 20px;
            font-size: 22px;
            border-bottom: 2px solid #1e5631;
            padding-bottom: 8px;
        }

        /* Tables */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 40px;
        }

        .data-table th,
        .data-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .data-table th {
            background-color: #1e5631;
            color: white;
            font-weight: 500;
        }

        .data-table tr:hover {
            background-color: #f9f9f9;
        }

        /* Action Buttons */
        .confirm-btn {
            background-color: #2ecc71;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
            transition: background-color 0.3s;
        }

        .confirm-btn:hover {
            background-color: #27ae60;
        }

        .cancel-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .cancel-btn:hover {
            background-color: #c0392b;
        }

        .delete-btn {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }

        /* Status Badges */
        .status-pending {
            color: #e67e22;
            font-weight: 500;
        }

        .status-confirmed {
            color: #2ecc71;
            font-weight: 500;
        }

        .status-cancelled {
            color: #e74c3c;
            font-weight: 500;
        }

        /* Alert Messages */
        .alert {
            padding: 15px;
            margin-bottom: 30px;
            border-radius: 4px;
            text-align: center;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <!-- Top Header -->
    <div class="top-header">
        <h1>FitZone - Staff Dashboard</h1>
        <form action="logout.php" method="POST">
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                <?php 
                    echo $_SESSION['message']; 
                    unset($_SESSION['message']); 
                    unset($_SESSION['message_type']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Bookings Table -->
        <h2 class="section-header">Class Bookings</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Customer Email</th>
                    <th>Mobile Number</th>
                    <th>Activity</th>
                    <th>Time</th>
                    <th>Day</th>
                    <th>Trainer</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td><?php echo htmlspecialchars($booking['customer_name']); ?></td>
                    <td><?php echo htmlspecialchars($booking['customer_email']); ?></td>
                    <td><?php echo htmlspecialchars($booking['mobile_number']); ?></td>
                    <td><?php echo htmlspecialchars($booking['activity']); ?></td>
                    <td><?php echo htmlspecialchars($booking['time']); ?></td>
                    <td><?php echo htmlspecialchars($booking['day']); ?></td>
                    <td><?php echo htmlspecialchars($booking['trainer']); ?></td>
                    <td>
                        <?php 
                            $statusClass = 'status-pending';
                            if ($booking['status'] == 'Confirmed') {
                                $statusClass = 'status-confirmed';
                            } elseif ($booking['status'] == 'Cancelled') {
                                $statusClass = 'status-cancelled';
                            }
                        ?>
                        <span class="<?php echo $statusClass; ?>">
                            <?php echo htmlspecialchars($booking['status'] ?? 'Pending'); ?>
                        </span>
                    </td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                            <input type="hidden" name="status" value="Confirmed">
                            <button type="submit" name="update_booking" class="confirm-btn">Confirm</button>
                        </form>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                            <input type="hidden" name="status" value="Cancelled">
                            <button type="submit" name="update_booking" class="cancel-btn">Cancel</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Feedbacks Table -->
        <h2 class="section-header">Customer Feedbacks</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Feedback Message</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($feedbacks as $feedback): ?>
                <tr>
                    <td><?php echo htmlspecialchars($feedback['name']); ?></td>
                    <td><?php echo htmlspecialchars($feedback['email']); ?></td>
                    <td><?php echo htmlspecialchars($feedback['message']); ?></td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="feedback_id" value="<?php echo $feedback['id']; ?>">
                            <button type="submit" name="delete_feedback" class="delete-btn">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide alert message after 5 seconds
            const alertMessage = document.querySelector('.alert');
            if (alertMessage) {
                setTimeout(() => {
                    alertMessage.style.opacity = '0';
                    setTimeout(() => {
                        alertMessage.style.display = 'none';
                    }, 500);
                }, 5000);
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>