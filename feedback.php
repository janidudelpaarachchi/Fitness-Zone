<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "fitzone_fitness";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $message = $_POST['message'];
    
    // Insert feedback into database
    $stmt = $conn->prepare("INSERT INTO feedback (name, email, message) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $message);
    
    if ($stmt->execute()) {
        $success_message = "Thank You For Your Feedback!";
    } else {
        $error_message = "Error submitting feedback: " . $conn->error;
    }
    $stmt->close();
}

// Fetch existing feedbacks
$feedbacks = [];
$result = $conn->query("SELECT * FROM feedback ORDER BY created_at DESC");
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $feedbacks[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FitZone - Feedback</title>
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
            background-color:rgb(49, 14, 138);
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .top-header h1 {
            font-size: 24px;
            font-weight: 600;
            margin-left: 20px;
        }

        /* Feedback Form */
        .feedback-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .feedback-form h2 {
            color: #1e5631;
            margin-bottom: 20px;
            text-align: center;
            font-size: 28px;
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
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
            transition: border 0.3s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #3498db;
            outline: none;
        }

        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }

        .submit-btn {
            background-color: #3498db;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s;
            display: block;
            margin: 0 auto;
            width: 200px;
        }

        .submit-btn:hover {
            background-color: #2980b9;
        }

        /* Feedback Display */
        .feedbacks-section {
            max-width: 1200px;
            margin: 60px auto;
            padding: 0 20px;
        }

        .feedbacks-section h2 {
            color: #1e5631;
            margin-bottom: 30px;
            text-align: center;
            font-size: 28px;
        }

        .feedbacks-container {
            position: relative;
            height: 350px;
            overflow: hidden;
            margin: 0 auto;
            width: 100%;
        }

        .feedbacks-slider {
            display: flex;
            transition: transform 0.8s ease-in-out;
            height: 100%;
            width: 100%;
        }

        .feedback-card {
            min-width: 300px;
            padding: 25px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin: 0 15px;
            height: 300px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .feedback-author {
            font-weight: 600;
            color: #3498db;
            margin-bottom: 10px;
            font-size: 18px;
        }

        .feedback-email {
            color: #777;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .feedback-message {
            line-height: 1.6;
            color: #555;
            flex-grow: 1;
            overflow-y: auto;
            padding-right: 10px;
        }

        .feedback-date {
            color: #999;
            font-size: 12px;
            margin-top: 15px;
            text-align: right;
        }

        .slider-controls {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .slider-dots {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #ccc;
            margin: 0 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .dot.active {
            background-color: #3498db;
        }

        /* Success Message */
        .success-message {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #2ecc71;
            color: white;
            padding: 15px 30px;
            border-radius: 5px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            display: none;
            animation: fadeIn 0.5s;
        }

        @keyframes fadeIn {
            from { opacity: 0; top: 0; }
            to { opacity: 1; top: 20px; }
        }

        @keyframes fadeOut {
            from { opacity: 1; top: 20px; }
            to { opacity: 0; top: 0; }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .feedback-card {
                min-width: 250px;
                margin: 0 10px;
            }
            
            .feedbacks-container {
                height: 400px;
            }
        }
    </style>
</head>
<body>
    <!-- Top Header -->
    <div class="top-header">
        <h1>FitZone - Feedback</h1>
    </div>

    <!-- Feedback Form -->
    <div class="feedback-container">
        <form class="feedback-form" method="POST" action="feedback.php">
            <h2>Share Your Feedback</h2>
            
            <div class="form-group">
                <label for="name">Your Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="email">Your Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="message">Your Feedback</label>
                <textarea id="message" name="message" required></textarea>
            </div>
            
            <button type="submit" class="submit-btn">Submit Feedback</button>
        </form>
    </div>

    <!-- Feedback Display -->
    <div class="feedbacks-section">
        <h2>What Our Members Say</h2>
        <div class="feedbacks-container">
            <div class="feedbacks-slider" id="feedbacksSlider">
                <?php if (!empty($feedbacks)): ?>
                    <?php foreach ($feedbacks as $feedback): ?>
                        <div class="feedback-card">
                            <div>
                                <div class="feedback-author"><?php echo htmlspecialchars($feedback['name']); ?></div>
                                <div class="feedback-email"><?php echo htmlspecialchars($feedback['email']); ?></div>
                                <div class="feedback-message"><?php echo htmlspecialchars($feedback['message']); ?></div>
                            </div>
                            <?php if (!empty($feedback['created_at'])): ?>
                                <div class="feedback-date">
                                    <?php echo date('M j, Y', strtotime($feedback['created_at'])); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="feedback-card" style="min-width: 100%;">
                        <div class="feedback-message">No feedbacks yet. Be the first to share!</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="slider-dots" id="sliderDots"></div>
    </div>

    <!-- Success Message -->
    <?php if (isset($success_message)): ?>
        <div class="success-message" id="successMessage">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Show success message
            const successMessage = document.getElementById('successMessage');
            if (successMessage) {
                successMessage.style.display = 'block';
                
                setTimeout(() => {
                    successMessage.style.animation = 'fadeOut 0.5s';
                    setTimeout(() => {
                        successMessage.style.display = 'none';
                    }, 500);
                }, 3000);
            }

            // Enhanced Slideshow Functionality
            const slider = document.getElementById('feedbacksSlider');
            const cards = document.querySelectorAll('.feedback-card');
            const dotsContainer = document.getElementById('sliderDots');
            
            if (cards.length > 0) {
                let currentIndex = 0;
                const cardWidth = cards[0].offsetWidth + 30; // width + margin
                
                // Create dots for navigation
                cards.forEach((_, index) => {
                    const dot = document.createElement('div');
                    dot.classList.add('dot');
                    if (index === 0) dot.classList.add('active');
                    dot.addEventListener('click', () => {
                        goToSlide(index);
                    });
                    dotsContainer.appendChild(dot);
                });
                
                // Auto-rotate slides
                let slideInterval = setInterval(nextSlide, 5000);
                
                function updateSlider() {
                    slider.style.transform = `translateX(-${currentIndex * cardWidth}px)`;
                    
                    // Update active dot
                    document.querySelectorAll('.dot').forEach((dot, index) => {
                        dot.classList.toggle('active', index === currentIndex);
                    });
                    
                    // Reset timer when manually navigating
                    clearInterval(slideInterval);
                    slideInterval = setInterval(nextSlide, 5000);
                }
                
                function nextSlide() {
                    currentIndex = (currentIndex + 1) % cards.length;
                    updateSlider();
                }
                
                function goToSlide(index) {
                    currentIndex = index;
                    updateSlider();
                }
                
                // Pause on hover
                slider.parentElement.addEventListener('mouseenter', () => {
                    clearInterval(slideInterval);
                });
                
                slider.parentElement.addEventListener('mouseleave', () => {
                    clearInterval(slideInterval);
                    slideInterval = setInterval(nextSlide, 5000);
                });
                
                // Handle touch events for mobile swipe
                let touchStartX = 0;
                let touchEndX = 0;
                
                slider.addEventListener('touchstart', (e) => {
                    touchStartX = e.changedTouches[0].screenX;
                    clearInterval(slideInterval);
                }, {passive: true});
                
                slider.addEventListener('touchend', (e) => {
                    touchEndX = e.changedTouches[0].screenX;
                    handleSwipe();
                    slideInterval = setInterval(nextSlide, 5000);
                }, {passive: true});
                
                function handleSwipe() {
                    const difference = touchStartX - touchEndX;
                    if (difference > 50) { // Swipe left
                        nextSlide();
                    } else if (difference < -50) { // Swipe right
                        currentIndex = (currentIndex - 1 + cards.length) % cards.length;
                        updateSlider();
                    }
                }
            }
        });
    </script>
</body>
</html>