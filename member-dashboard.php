<?php
session_start();

// Redirect to login if not logged in
//if (!isset($_SESSION['user_id'])) {
  //  header("Location: login.php");
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

// Handle blog submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_blog'])) {
    $title = $_POST['blog_title'];
    $category = $_POST['blog_category'];
    $content = $_POST['blog_content'];
    $user_id = $_SESSION['user_id'];
    
    $sql = "INSERT INTO blogs (title, category, content, user_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $title, $category, $content, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Blog Posted successfully!";
        $_SESSION['message_type'] = "success";
        header("Location: member-dashboard.php?page=blogs");
        exit();
    } else {
        $_SESSION['message'] = "Error: " . $conn->error;
        $_SESSION['message_type'] = "error";
    }
    $stmt->close();
}

// Handle subscription purchase
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['purchase_subscription'])) {
    $customer_name = $_POST['customer_name'];
    $customer_email = $_POST['customer_email'];
    $mobile_number = $_POST['mobile_number'];
    $plan = $_POST['plan'];
    
    $sql = "INSERT INTO subscriptions (customer_name, customer_email, mobile_number, plan) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $customer_name, $customer_email, $mobile_number, $plan);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Thank you for choosing us!";
        $_SESSION['message_type'] = "success";
        header("Location: member-dashboard.php?page=subscriptions");
        exit();
    } else {
        $_SESSION['message'] = "Error: " . $conn->error;
        $_SESSION['message_type'] = "error";
    }
    $stmt->close();
}

// Handle class booking
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['join_session'])) {
    $customer_name = $_POST['customer_name'];
    $customer_email = $_POST['customer_email'];
    $mobile_number = $_POST['mobile_number'];
    $time = $_POST['time'];
    $day = $_POST['day'];
    $trainer = $_POST['trainer'];
    $activity = $_POST['activity'];
    
    $sql = "INSERT INTO bookings (customer_name, customer_email, mobile_number, time, day, trainer, activity) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $customer_name, $customer_email, $mobile_number, $time, $day, $trainer, $activity);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Booking Confirmed!";
        $_SESSION['message_type'] = "success";
        header("Location: member-dashboard.php?page=schedules");
        exit();
    } else {
        $_SESSION['message'] = "Error: " . $conn->error;
        $_SESSION['message_type'] = "error";
    }
    $stmt->close();
}

// Fetch data based on current page
$blogs = [];
$schedules = [];

if (isset($_GET['page'])) {
    $page = $_GET['page'];
    
    switch($page) {
        case 'blogs':
            $result = $conn->query("SELECT * FROM blogs ORDER BY created_at DESC");
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $blogs[] = $row;
                }
            }
            break;
            
        case 'schedules':
            $result = $conn->query("SELECT * FROM schedule ORDER BY day, time");
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
    <title>FitZone - Member Dashboard</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
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
            background-color:rgb(0, 28, 131);
            color: white;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
        }

        .logout-btn {
            background-color: white;
            color: black;
            border: none;
            padding: 5px 15px;
            border-radius: 2px;
            cursor: button;
            font-weight: 500;
            transition: background-color 0.3s;
        }

        .logout-btn:hover {
            background-color:rgb(244, 72, 72);
            color: white;
        }

        /* Main Header */
        .main-header {
            background-color: #1e5631;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .main-header h1 {
            font-size: 24px;
            font-weight: 600;
        }

        .nav-buttons {
            display: flex;
            gap: 15px;
        }

        .nav-btn {
            background-color: white;
            color: black;
            border: none;
            padding: 8px 20px;
            border-radius: 4px;
            cursor: button;
            font-weight: 500;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .nav-btn:hover {
            background-color:rgb(241, 35, 35);
            color: white;
        }

        /* Main Content */
        .main-content {
            padding: 30px;
            min-height: calc(100vh - 120px);
        }

        /* Dashboard Section */
        .dashboard-section {
            text-align: center;
            margin-top: 50px;
        }

        .dashboard-section h2 {
            color: #1e5631;
            margin-bottom: 20px;
            font-size: 28px;
        }

        .dashboard-section p {
            font-size: 18px;
            color: #555;
            max-width: 800px;
            margin: 0 auto 30px;
        }

        /* Blogs Section */
        .blogs-section {
            max-width: 1200px;
            margin: 0 auto;
        }

        .blogs-section h2 {
            color: #1e5631;
            margin-bottom: 30px;
            text-align: center;
        }

        .add-blog-btn {
            display: block;
            width: 300px;
            margin: 30px auto;
            padding: 15px;
            background-color: #1e5631;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: button;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .add-blog-btn:hover {
            background-color:rgb(0, 0, 0);
        }

        .blogs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .blog-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .blog-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .blog-card-content {
            padding: 20px;
        }

        .blog-card h3 {
            color: #1e5631;
            margin-bottom: 10px;
        }

        .blog-card .category {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            margin-bottom: 15px;
        }

        .blog-card p {
            color: #555;
            line-height: 1.5;
        }

        /* Blog Form */
        .blog-form-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .blog-form-container h2 {
            color: #1e5631;
            margin-bottom: 30px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }

        .form-group textarea {
            min-height: 200px;
            resize: vertical;
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background-color: #1e5631;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 18px;
            cursor: button;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background-color: #164022;
        }

        /* Subscriptions Section */
        .subscriptions-section {
            max-width: 1200px;
            margin: 0 auto;
        }

        .subscriptions-section h2 {
            color: #1e5631;
            margin-bottom: 30px;
            text-align: center;
        }

        .plans-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .plan-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.3s;
        }

        .plan-card:hover {
            transform: translateY(-5px);
        }

        .plan-header {
            background-color: #1e5631;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .plan-header h3 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .plan-header .price {
            font-size: 28px;
            font-weight: 700;
        }

        .plan-features {
            padding: 20px;
        }

        .plan-features ul {
            list-style: none;
            margin-bottom: 20px;
        }

        .plan-features li {
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }

        .plan-features li:before {
            content: "✓";
            color: #1e5631;
            font-weight: bold;
            margin-right: 10px;
        }

        .choose-plan-btn {
            display: block;
            width: 100%;
            padding: 15px;
            background-color:rgb(255, 0, 0);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 18px;
            cursor: button;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .choose-plan-btn:hover {
            background-color: #2980b9;
        }

        /* Payment Form */
        .payment-form-container {
            max-width: 600px;
            margin: 30px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .payment-form-container h2 {
            color: #1e5631;
            margin-bottom: 30px;
            text-align: center;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-row .form-group {
            flex: 1;
            margin-bottom: 0;
        }

        /* Schedules Section */
        .schedules-section {
            max-width: 1200px;
            margin: 0 auto;
        }

        .schedules-section h2 {
            color: #1e5631;
            margin-bottom: 30px;
            text-align: center;
        }

        .schedules-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .schedules-table th,
        .schedules-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .schedules-table th {
            background-color: #1e5631;
            color: white;
            font-weight: 500;
        }

        .schedules-table tr:hover {
            background-color: #f9f9f9;
        }

        .join-btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: button;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        .join-btn:hover {
            background-color: #2980b9;
        }

        /* Booking Form */
        .booking-form-container {
            max-width: 600px;
            margin: 30px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .booking-form-container h2 {
            color: #1e5631;
            margin-bottom: 30px;
            text-align: center;
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

        /* Hidden Class */
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <!-- Top Header -->
    <div class="top-header">
        <div class="contact-info">
            +94 11 234 554 | No.45 Kandy Road, Kurunegala | info@fitzone.com
        </div>
        <form action="logout.php" method="POST">
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>

    <!-- Main Header -->
    <div class="main-header">
        <h1>We Grow Human! - FitZone</h1>
        <div class="nav-buttons">
            <a href="member-dashboard.php?page=blogs" class="nav-btn">Blogs</a>
            <a href="member-dashboard.php?page=subscriptions" class="nav-btn">Subscriptions</a>
            <a href="member-dashboard.php?page=schedules" class="nav-btn">Schedules</a>
        </div>
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

        <?php if (!isset($_GET['page'])): ?>
            <!-- Dashboard Home -->
            <div class="dashboard-section">
                <h2>Welcome to Your FitZone Dashboard</h2>
                <p>Explore our blogs, subscription plans, and class schedules to make the most of your fitness journey with us.</p>
            </div>

        <?php elseif ($_GET['page'] == 'blogs' && !isset($_GET['action'])): ?>
            <!-- Blogs Listing -->
            <div class="blogs-section">
                <h2>FitZone Blogs</h2>
                <a href="member-dashboard.php?page=blogs&action=add" class="add-blog-btn">Add a Blog Post Here</a>
                
                <div class="blogs-grid">
                    <?php foreach ($blogs as $blog): ?>
                    <div class="blog-card">
                        <div class="blog-card-content">
                            <h3><?php echo htmlspecialchars($blog['title']); ?></h3>
                            <span class="category"><?php echo htmlspecialchars($blog['category']); ?></span>
                            <p><?php echo htmlspecialchars(substr($blog['content'], 0, 200)); ?>...</p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php elseif ($_GET['page'] == 'blogs' && isset($_GET['action']) && $_GET['action'] == 'add'): ?>
            <!-- Add Blog Form -->
            <div class="blog-form-container">
                <h2>Add New Blog Post</h2>
                <form action="member-dashboard.php?page=blogs" method="POST">
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
                        <textarea id="blog_content" name="blog_content" required></textarea>
                    </div>
                    <button type="submit" name="submit_blog" class="submit-btn">Submit Blog</button>
                </form>
            </div>

        <?php elseif ($_GET['page'] == 'subscriptions' && !isset($_GET['action'])): ?>
            <!-- Subscriptions Plans -->
            <div class="subscriptions-section">
                <h2>Choose Your Subscription Plan</h2>
                
                <div class="plans-container">
                    <!-- Basic Plan -->
                    <div class="plan-card">
                        <div class="plan-header">
                            <h3>Basic Plan</h3>
                            <div class="price">$29/month</div>
                        </div>
                        <div class="plan-features">
                            <ul>
                                <li>Access to gym facilities</li>
                                <li>Cardio & weight training equipment</li>
                                <li>Locker room & shower access</li>
                                <li>Free Wi-Fi</li>
                            </ul>
                            <a href="member-dashboard.php?page=subscriptions&action=purchase&plan=Basic" class="choose-plan-btn">Choose Plan</a>
                        </div>
                    </div>
                    
                    <!-- Standard Plan -->
                    <div class="plan-card">
                        <div class="plan-header">
                            <h3>Premium Plan</h3>
                            <div class="price">$59/month</div>
                        </div>
                        <div class="plan-features">
                            <ul>
                                <li>Everything in the Basic Plan</li>
                                <li>Access to group fitness classes (Yoga, Zumba, HIIT)</li>
                                <li>Personal trainer consultation (once a month)</li>
                                <li>Sauna & steam room access</li>
                            </ul>
                            <a href="member-dashboard.php?page=subscriptions&action=purchase&plan=Standard" class="choose-plan-btn">Choose Plan</a>
                        </div>
                    </div>
                    
                    <!-- Premium Plan -->
                    <div class="plan-card">
                        <div class="plan-header">
                            <h3>Elite Plan</h3>
                            <div class="price">$99/month</div>
                        </div>
                        <div class="plan-features">
                            <ul>
                                <li>Everything in the Standard Plan</li>
                                <li>Unlimited personal training sessions</li>
                                <li>Nutrition consultation & diet plan</li>
                                <li>Exclusive access to VIP workout areas</li>
                                <li>Free guest pass (bring a friend once a month)</li>
                            </ul>
                            <a href="member-dashboard.php?page=subscriptions&action=purchase&plan=Premium" class="choose-plan-btn">Choose Plan</a>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($_GET['page'] == 'subscriptions' && isset($_GET['action']) && $_GET['action'] == 'purchase'): ?>
            <!-- Payment Form -->
            <div class="payment-form-container">
                <h2>Complete Your Subscription</h2>
                <form action="member-dashboard.php?page=subscriptions" method="POST">
                    <input type="hidden" name="plan" value="<?php echo htmlspecialchars($_GET['plan']); ?>">
                    
                    <div class="form-group">
                        <label for="customer_name">Customer Name</label>
                        <input type="text" id="customer_name" name="customer_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_email">Customer Email</label>
                        <input type="email" id="customer_email" name="customer_email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="mobile_number">Mobile Number</label>
                        <input type="tel" id="mobile_number" name="mobile_number" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="plan_display">Choosed Plan</label>
                        <input type="text" id="plan_display" value="<?php echo htmlspecialchars($_GET['plan']); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="card_number">Card Number</label>
                        <input type="text" id="card_number" name="card_number" pattern="[0-9]{12}" title="12 digit card number" required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="expiry_month">Expiry Month</label>
                            <select id="expiry_month" name="expiry_month" required>
                                <option value="">Month</option>
                                <?php for ($i = 1; $i <= 12; $i++): ?>
                                    <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>">
                                        <?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="expiry_year">Expiry Year</label>
                            <select id="expiry_year" name="expiry_year" required>
                                <option value="">Year</option>
                                <?php for ($i = date('Y'); $i <= date('Y') + 10; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="cvv">CVV</label>
                            <input type="text" id="cvv" name="cvv" pattern="[0-9]{3}" title="3 digit CVV" required>
                        </div>
                    </div>
                    
                    <button type="submit" name="purchase_subscription" class="submit-btn">Purchase</button>
                </form>
            </div>

        <?php elseif ($_GET['page'] == 'schedules' && !isset($_GET['action'])): ?>
            <!-- Schedules Listing -->
            <div class="schedules-section">
                <h2>Class Schedules</h2>
                
                <table class="schedules-table">
                    <thead>
                        <tr>
                            <th>Activity Name</th>
                            <th>Time</th>
                            <th>Day</th>
                            <th>Trainer Name</th>
                            <th>Join</th>
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
                                <a href="member-dashboard.php?page=schedules&action=join&activity=<?php echo urlencode($schedule['activity']); ?>&time=<?php echo urlencode($schedule['time']); ?>&day=<?php echo urlencode($schedule['day']); ?>&trainer=<?php echo urlencode($schedule['trainer']); ?>" class="join-btn">Join Now</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php elseif ($_GET['page'] == 'schedules' && isset($_GET['action']) && $_GET['action'] == 'join'): ?>
            <!-- Booking Form -->
            <div class="booking-form-container">
                <h2>Join Class Session</h2>
                <form action="member-dashboard.php?page=schedules" method="POST">
                    <input type="hidden" name="activity" value="<?php echo htmlspecialchars($_GET['activity']); ?>">
                    <input type="hidden" name="time" value="<?php echo htmlspecialchars($_GET['time']); ?>">
                    <input type="hidden" name="day" value="<?php echo htmlspecialchars($_GET['day']); ?>">
                    <input type="hidden" name="trainer" value="<?php echo htmlspecialchars($_GET['trainer']); ?>">
                    
                    <div class="form-group">
                        <label for="customer_name">Customer Name</label>
                        <input type="text" id="customer_name" name="customer_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="customer_email">Customer Email</label>
                        <input type="email" id="customer_email" name="customer_email" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="mobile_number">Mobile Number</label>
                        <input type="tel" id="mobile_number" name="mobile_number" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="activity_display">Activity</label>
                        <input type="text" id="activity_display" value="<?php echo htmlspecialchars($_GET['activity']); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="time_display">Time</label>
                        <input type="text" id="time_display" value="<?php echo htmlspecialchars($_GET['time']); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="day_display">Day</label>
                        <input type="text" id="day_display" value="<?php echo htmlspecialchars($_GET['day']); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="trainer_display">Trainer Name</label>
                        <input type="text" id="trainer_display" value="<?php echo htmlspecialchars($_GET['trainer']); ?>" readonly>
                    </div>
                    
                    <button type="submit" name="join_session" class="submit-btn">Join Session</button>
                </form>
            </div>
        <?php endif; ?>
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

            // Add animation to blog cards
            const blogCards = document.querySelectorAll('.blog-card');
            blogCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100 * index);
            });
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>