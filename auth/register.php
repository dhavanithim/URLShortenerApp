</head>
<body>
    <div class="background-shapes">
        <div></div>
        <div></div>
        <div></div>
        <div></div>
        <div></div>
    </div>

    <div class="container">
        <div class="logo">
            <i class="fas fa-link"></i>
        </div>
        <h1>Create Account</h1>
        
        <!-- Registration form -->
        <form action="register.php" method="POST" class="register-form">
            <div class="form-group">
                <input type="text" name="username" required placeholder="Username" class="form-control" value="<?= isset($username) ? htmlspecialchars($username) : ''; ?>">
                <i class="fas fa-user"></i>
            </div>
            <div class="form-group">
                <input type="email" name="email" required placeholder="Email" class="form-control" value="<?= isset($email) ? htmlspecialchars($email) : ''; ?>">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="form-group">
                <input type="password" name="password" required placeholder="Password" class="form-control">
                <i class="fas fa-lock"></i>
            </div>
            <button type="submit" class="btn">Create Account</button>
        </form>
        
        <!-- Display error message if any -->
        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?= $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="social-login">
            <a href="#" class="social-btn google"><i class="fab fa-google"></i></a>
            <a href="#" class="social-btn facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" class="social-btn twitter"><i class="fab fa-twitter"></i></a>
        </div>
        
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>

    <script>
        // Add animation when form is submitted
        document.querySelector('.register-form').addEventListener('submit', function(e) {
            document.querySelector('.container').style.animation = 'fadeOut 0.5s forwards';
        });

        // Add subtle input animations
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-5px)';
                this.parentElement.style.transition = 'transform 0.3s ease';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html><?php 
session_start(); 

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

include('../config/db.php');  // Include database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Check if the username already exists in the database
    $checkQuery = "SELECT * FROM users WHERE username = :username";
    $stmt = $pdo->prepare($checkQuery);
    $stmt->bindValue(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    
    // If username exists, show error
    if ($stmt->rowCount() > 0) {
        $error = "Username already taken, please choose another one.";
    } else {
        // If username is available, insert the user
        $query = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
        $stmt = $pdo->prepare($query);
        
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':password', $password, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            $_SESSION['user_id'] = $pdo->lastInsertId(); // Get the last inserted ID
            $_SESSION['username'] = $username;
            header("Location: ../index.php");
            exit();
        } else {
            $error = "Registration failed!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | URL Shortener</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --accent-color: #1abc9c;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
            --danger-color: #e74c3c;
            --success-color: #2ecc71;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #3498db, #8e44ad);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .background-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            opacity: 0.4;
        }

        .background-shapes div {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: float 10s infinite ease-in-out;
        }

        .background-shapes div:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .background-shapes div:nth-child(2) {
            width: 120px;
            height: 120px;
            top: 70%;
            left: 80%;
            animation-delay: 1s;
        }

        .background-shapes div:nth-child(3) {
            width: 50px;
            height: 50px;
            top: 30%;
            left: 90%;
            animation-delay: 2s;
        }

        .background-shapes div:nth-child(4) {
            width: 100px;
            height: 100px;
            top: 80%;
            left: 20%;
            animation-delay: 3s;
        }

        .background-shapes div:nth-child(5) {
            width: 70px;
            height: 70px;
            top: 40%;
            left: 40%;
            animation-delay: 4s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(10deg);
            }
        }

        .container {
            background-color: rgba(255, 255, 255, 0.9);
            width: 450px;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            padding: 40px;
            text-align: center;
            backdrop-filter: blur(10px);
            transform: translateY(20px);
            animation: fadeIn 0.8s forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            margin-bottom: 20px;
        }

        .logo i {
            font-size: 3rem;
            color: var(--primary-color);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
            }
        }

        h1 {
            color: var(--dark-color);
            margin-bottom: 30px;
            font-weight: 600;
            position: relative;
        }

        h1::after {
            content: '';
            display: block;
            width: 50px;
            height: 3px;
            background-color: var(--primary-color);
            margin: 10px auto 0;
            border-radius: 2px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-control {
            width: 100%;
            padding: 15px 15px 15px 50px;
            border: 1px solid #ddd;
            border-radius: 30px;
            font-size: 16px;
            transition: all 0.3s ease;
            outline: none;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        .form-group i {
            position: absolute;
            left: 20px;
            top: 16px;
            color: #aaa;
            transition: all 0.3s ease;
        }

        .form-control:focus + i {
            color: var(--primary-color);
        }

        .btn {
            background: linear-gradient(to right, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            width: 100%;
            padding: 15px;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            box-shadow: 0 4px 10px rgba(52, 152, 219, 0.3);
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(52, 152, 219, 0.4);
        }

        .btn:active {
            transform: translateY(0);
        }

        .error-message {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--danger-color);
            padding: 10px;
            border-radius: 5px;
            margin: 20px 0;
            font-size: 14px;
            animation: shake 0.5s;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        p {
            margin-top: 25px;
            color: #777;
            font-size: 14px;
        }

        a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        a:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }

        .social-login {
            margin-top: 25px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .social-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            transition: all 0.3s ease;
        }

        .social-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
        }

        .social-btn i {
            font-size: 18px;
        }

        .social-btn.google i {
            color: #DB4437;
        }

        .social-btn.facebook i {
            color: #4267B2;
        }

        .social-btn.twitter i {
            color: #1DA1F2;
        }
    </style>