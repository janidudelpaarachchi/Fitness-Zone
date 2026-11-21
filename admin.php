<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fitzone_fitness";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle blog operations
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_blog'])) {
    $title = $_POST['blog_title'];
    $category = $_POST['blog_category'];
    $content = $_POST['blog_content'];
    
    $sql = "INSERT INTO blogs (title, category, content) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $title, $category, $content);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Blog Submitted!";
    } else {
        $_SESSION['message'] = "Error: " . $conn->error;
    }
    
    $stmt->close();
    header("Location: admin.php?page=blogs");
    exit();
}

if (isset($_GET['delete_blog'])) {
    $id = $_GET['delete_blog'];
    $sql = "DELETE FROM blogs WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Blog Deleted!";
    } else {
        $_SESSION['message'] = "Error deleting blog: " . $conn->error;
    }
    
    $stmt->close();
    header("Location: admin.php?page=blogs");
    exit();
}

// Handle subscription operations
if (isset($_GET['delete_subscription'])) {
    $id = $_GET['delete_subscription'];
    $sql = "DELETE FROM subscriptions WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Subscription Deleted!";
    } else {
        $_SESSION['message'] = "Error deleting subscription: " . $conn->error;
    }
    
    $stmt->close();
    header("Location: admin.php?page=subscriptions");
    exit();
}

// Handle registration operations
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_user'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $sql = "INSERT INTO registrations (name, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $email, $password);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "User Registered Success!";
    } else {
        $_SESSION['message'] = "Error: " . $conn->error;
    }
    
    $stmt->close();
    header("Location: admin.php?page=registrations");
    exit();
}

if (isset($_GET['delete_registration'])) {
    $id = $_GET['delete_registration'];
    $sql = "DELETE FROM registrations WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Registered User Deleted!";
    } else {
        $_SESSION['message'] = "Error deleting user: " . $conn->error;
    }
    
    $stmt->close();
    header("Location: admin.php?page=registrations");
    exit();
}

// Handle schedule operations
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_schedule'])) {
    $activity = $_POST['activity'];
    $time = $_POST['time'];
    $day = $_POST['day'];
    $trainer = $_POST['trainer'];
    
    $sql = "INSERT INTO schedule (activity, time, day, trainer) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $activity, $time, $day, $trainer);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Schedule Added!";
    } else {
        $_SESSION['message'] = "Error: " . $conn->error;
    }
    
    $stmt->close();
    header("Location: admin.php?page=schedule");
    exit();
}

if (isset($_GET['delete_schedule'])) {
    $id = $_GET['delete_schedule'];
    $sql = "DELETE FROM schedule WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Schedule Deleted!";
    } else {
        $_SESSION['message'] = "Error deleting schedule: " . $conn->error;
    }
    
    $stmt->close();
    header("Location: admin.php?page=schedule");
    exit();
}

// Fetch data for all sections
$blogs = [];
$subscriptions = [];
$registrations = [];
$schedules = [];

if (isset($_GET['page'])) {
    $page = $_GET['page'];
    
    switch($page) {
        case 'blogs':
            $result = $conn->query("SELECT * FROM blogs");
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $blogs[] = $row;
                }
            }
            break;
            
        case 'subscriptions':
            $result = $conn->query("SELECT * FROM subscriptions");
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $subscriptions[] = $row;
                }
            }
            break;
            
        case 'registrations':
            $result = $conn->query("SELECT * FROM registrations");
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $registrations[] = $row;
                }
            }
            break;
            
        case 'schedule':
            $result = $conn->query("SELECT * FROM schedule");
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $schedules[] = $row;
                }
            }
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitZone Fitness - Admin Panel</title>

    <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap');
    </style>

    <style>
        /* Reset and Base Styles */
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

        /* Admin Container */
        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background-color: #1e5631;
            color: white;
            height: 100vh;
            position: fixed;
            padding: 20px 0;
        }

        .logo {
            text-align: center;
            padding: 20px 0;
            font-size: 24px;
            font-weight: bold;
            color: white;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 30px;
        }

        .nav-links {
            list-style: none;
        }

        .nav-links li {
            padding: 15px 20px;
            transition: all 0.3s ease;
        }

        .nav-links li a {
            text-decoration: none;
            color: black;
            display: flex;
            align-items: center;
        }

        .nav-links li a span {
            display: inline-block;
            background-color: white;
            padding: 10px 15px;
            border-radius: 5px;
            width: 100%;
            transition: all 0.3s ease;
        }

        .nav-links li a:hover span {
            background-color: #f0f0f0;
        }

        .nav-links li a.active span {
            font-weight: bold;
            background-color: #f8f8f8;
        }

        .nav-links li .logout span {
            background-color: #ffcc00;
        }

        /* Main Content Styles */
        .main-content {
            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 30px;
        }

        /* Common Section Styles */
        .section-title {
            margin-bottom: 30px;
            color: #1e5631;
        }

        .form-container {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 150px;
        }

        .submit-btn {
            background-color: #1e5631;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background-color: #164022;
        }

        /* Table Styles */
        .data-table {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        table th {
            background-color: #f8f8f8;
            font-weight: 600;
            color: #555;
        }

        table tr:hover {
            background-color: #f9f9f9;
        }

        .action-btn {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s;
            display: inline-block;
            text-align: center;
        }

        .delete-btn {
            background-color: #e74c3c;
            color: white;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }

        .remove-btn {
            background-color: #f39c12;
            color: white;
        }

        .remove-btn:hover {
            background-color: #d35400;
        }

        /* Alert Message */
        .alert-message {
            padding: 15px;
            margin-bottom: 20px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            transition: all 0.5s ease;
        }

        /* Dashboard Section */
        .dashboard-section {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .dashboard-section h1 {
            color: #1e5631;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">FitZone</div>
            <ul class="nav-links">
                <li><a href="admin.php" class="<?php echo (!isset($_GET['page'])) ? 'active' : ''; ?>"><span>Dashboard</span></a></li>
                <li><a href="admin.php?page=blogs" class="<?php echo (isset($_GET['page']) && $_GET['page'] == 'blogs') ? 'active' : ''; ?>"><span>Blogs</span></a></li>
                <li><a href="admin.php?page=subscriptions" class="<?php echo (isset($_GET['page']) && $_GET['page'] == 'subscriptions') ? 'active' : ''; ?>"><span>Subscriptions</span></a></li>
                <li><a href="admin.php?page=registrations" class="<?php echo (isset($_GET['page']) && $_GET['page'] == 'registrations') ? 'active' : ''; ?>"><span>Registrations</span></a></li>
                <li><a href="admin.php?page=schedule" class="<?php echo (isset($_GET['page']) && $_GET['page'] == 'schedule') ? 'active' : ''; ?>"><span>Schedule</span></a></li>
                <li><a href="logout.php" class="logout"><span>Logout</span></a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert-message">
                    <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                </div>
            <?php endif; ?>

            <?php if (!isset($_GET['page'])): ?>
                <!-- Dashboard Content -->
                <div class="dashboard-section">
                    <h1>Welcome to FitZone Admin Panel</h1>
                    <p>Select an option from the sidebar to manage different sections.</p>
                </div>

            <?php elseif ($_GET['page'] == 'blogs'): ?>
                <!-- Blogs Management -->
                <div class="blogs-section">
                    <h1 class="section-title">FitZone Fitness - Blogs</h1>
                    
                    <form action="admin.php?page=blogs" method="POST" class="form-container">
                        <div class="form-group">
                            <label for="blog_title">Blog Title</label>
                            <input type="text" id="blog_title" name="blog_title" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="blog_category">Blog Category</label>
                            <input type="text" id="blog_category" name="blog_category" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="blog_content">Blog Content</label>
                            <textarea id="blog_content" name="blog_content" rows="5" required></textarea>
                        </div>
                        
                        <button type="submit" name="submit_blog" class="submit-btn">Submit Blog</button>
                    </form>
                    
                    <div class="data-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Blog Title</th>
                                    <th>Blog Category</th>
                                    <th>Blog Content</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($blogs as $blog): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($blog['title']); ?></td>
                                    <td><?php echo htmlspecialchars($blog['category']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($blog['content'], 0, 100)); ?>...</td>
                                    <td>
                                        <a href="admin.php?page=blogs&delete_blog=<?php echo $blog['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this blog?')">Delete</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php elseif ($_GET['page'] == 'subscriptions'): ?>
                <!-- Subscriptions Management -->
                <div class="subscriptions-section">
                    <h1 class="section-title">FitZone - Subscriptions</h1>
                    
                    <div class="data-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Customer Name</th>
                                    <th>Customer Email</th>
                                    <th>Mobile Number</th>
                                    <th>Choosed Plan</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($subscriptions as $subscription): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($subscription['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($subscription['customer_email']); ?></td>
                                    <td><?php echo htmlspecialchars($subscription['mobile_number']); ?></td>
                                    <td><?php echo htmlspecialchars($subscription['plan']); ?></td>
                                    <td>
                                        <a href="admin.php?page=subscriptions&delete_subscription=<?php echo $subscription['id']; ?>" class="action-btn remove-btn" onclick="return confirm('Are you sure you want to remove this subscription?')">Remove</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php elseif ($_GET['page'] == 'registrations'): ?>
                <!-- Registrations Management -->
                <div class="registrations-section">
                    <h1 class="section-title">FitZone - Registrations</h1>
                    
                    <div class="data-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($registrations as $registration): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($registration['name']); ?></td>
                                    <td><?php echo htmlspecialchars($registration['email']); ?></td>
                                    <td>
                                        <a href="admin.php?page=registrations&delete_registration=<?php echo $registration['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <form action="admin.php?page=registrations" method="POST" class="form-container">
                        <h2>Register New User</h2>
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        
                        <button type="submit" name="register_user" class="submit-btn">Register User</button>
                    </form>
                </div>

            <?php elseif ($_GET['page'] == 'schedule'): ?>
                <!-- Schedule Management -->
                <div class="schedule-section">
                    <h1 class="section-title">FitZone - Schedule Management</h1>
                    
                    <form action="admin.php?page=schedule" method="POST" class="form-container">
                        <div class="form-group">
                            <label for="activity">Activity Name</label>
                            <input type="text" id="activity" name="activity" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="time">Time</label>
                            <input type="time" id="time" name="time" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="day">Day</label>
                            <select id="day" name="day" required>
                                <option value="">Select Day</option>
                                <option value="Sunday">Sunday</option>
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednesday">Wednesday</option>
                                <option value="Thursday">Thursday</option>
                                <option value="Friday">Friday</option>
                                <option value="Saturday">Saturday</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="trainer">Trainer Name</label>
                            <input type="text" id="trainer" name="trainer" required>
                        </div>
                        
                        <button type="submit" name="add_schedule" class="submit-btn">Add Schedule</button>
                    </form>
                    
                    <div class="data-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Activity Name</th>
                                    <th>Time</th>
                                    <th>Day</th>
                                    <th>Trainer Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schedules as $schedule): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($schedule['activity']); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['time']); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['day']); ?></td>
                                    <td><?php echo htmlspecialchars($schedule['trainer']); ?></td>
                                    <td>
                                        <a href="admin.php?page=schedule&delete_schedule=<?php echo $schedule['id']; ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this schedule?')">Delete</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide alert message after 5 seconds
            const alertMessage = document.querySelector('.alert-message');
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