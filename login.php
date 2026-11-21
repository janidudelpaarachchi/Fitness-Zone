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

// Handle registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Check if email already exists
    $check = $conn->prepare("SELECT email FROM registrations WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();
    
    if ($check->num_rows > 0) {
        $_SESSION['message'] = "Email already registered!";
        $_SESSION['message_type'] = "error";
    } else {
        $sql = "INSERT INTO registrations (name, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $name, $email, $password);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "New User Registered Success!";
            $_SESSION['message_type'] = "success";
            header("Location: login.php");
            exit();
        } else {
            $_SESSION['message'] = "Error: " . $conn->error;
            $_SESSION['message_type'] = "error";
        }
        $stmt->close();
    }
    $check->close();
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Special admin credentials
    if ($email == "admin@gmail.com" && $password == "admin1234") {
        $_SESSION['user'] = "admin";
        $_SESSION['email'] = $email;
        header("Location: admin.php");
        exit();
    }
    // Special staff manager credentials
    elseif ($email == "staffmanage@gmail.com" && $password == "staff@123") {
        $_SESSION['user'] = "staff_manager";
        $_SESSION['email'] = $email;
        header("Location: staff-manage.php");
        exit();
    }
    // Regular user login
    else {
        $sql = "SELECT id, name, email, password FROM registrations WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_id'] = $user['id'];
                header("Location: member-dashboard.php"); // Redirect to member dashboard
                exit();
            } else {
                $_SESSION['message'] = "Invalid password!";
                $_SESSION['message_type'] = "error";
            }
        } else {
            $_SESSION['message'] = "User not found!";
            $_SESSION['message_type'] = "error";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitZone - Login</title>
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
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            width: 100%;
            max-width: 600px;
            padding: 2rem;
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo h1 {
            color: #1e5631;
            font-weight: 700;
            font-size: 2rem;
        }

        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .form-title {
            color: #1e5631;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .btn {
            width: 100%;
            padding: 0.75rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-login {
            background-color: #1e5631;
            color: white;
            margin-bottom: 1rem;
        }

        .btn-login:hover {
            background-color: #164022;
        }

        .register-link {
            text-align: center;
            margin-top: 1rem;
        }

        .register-link a {
            color: #1e5631;
            text-decoration: none;
            font-weight: 500;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .alert {
            padding: 0.75rem;
            margin-bottom: 1rem;
            border-radius: 4px;
            font-size: 0.9rem;
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

        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>FitZone Fitness Center</h1>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                <?php 
                    echo $_SESSION['message']; 
                    unset($_SESSION['message']); 
                    unset($_SESSION['message_type']);
                ?>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <div id="login-form" class="form-container">
            <h2 class="form-title">Login</h2>
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="login-email">Email</label>
                    <input type="email" id="login-email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="login-password">Password</label>
                    <input type="password" id="login-password" name="password" required>
                </div>
                <button type="submit" name="login" class="btn btn-login">Login</button>
            </form>
            <div class="register-link">
                <p>Haven't an account yet? <a href="#" id="show-register">Register Here</a></p>
            </div>
        </div>

        <!-- Registration Form (Initially Hidden) -->
        <div id="register-form" class="form-container hidden">
            <h2 class="form-title">Register</h2>
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="register-name">Name</label>
                    <input type="text" id="register-name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="register-email">Email</label>
                    <input type="email" id="register-email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="register-password">Password</label>
                    <input type="password" id="register-password" name="password" required>
                </div>
                <button type="submit" name="register" class="btn btn-login">Register</button>
            </form>
            <div class="register-link">
                <p>Already have an account? <a href="#" id="show-login">Login Here</a></p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle between login and register forms
            const showRegister = document.getElementById('show-register');
            const showLogin = document.getElementById('show-login');
            const loginForm = document.getElementById('login-form');
            const registerForm = document.getElementById('register-form');
            
            showRegister.addEventListener('click', function(e) {
                e.preventDefault();
                loginForm.classList.add('hidden');
                registerForm.classList.remove('hidden');
            });
            
            showLogin.addEventListener('click', function(e) {
                e.preventDefault();
                registerForm.classList.add('hidden');
                loginForm.classList.remove('hidden');
            });

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