<?php
require_once 'config.php';

// Create tables if they don't exist
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        profile_picture VARCHAR(255) DEFAULT 'uploads/profile_pictures/default.jpg',
        user_type ENUM('student', 'teacher', 'admin') DEFAULT 'student',
        phone VARCHAR(20),
        bio TEXT,
        last_login TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS user_progress (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        course_id INT,
        progress_percentage INT DEFAULT 0,
        last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )",
    "CREATE TABLE IF NOT EXISTS achievements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        title VARCHAR(100) NOT NULL,
        description TEXT,
        date_earned TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )"
];

foreach ($tables as $sql) {
    if (!$conn->query($sql)) {
        die("Error creating table: " . $conn->error);
    }
}

requireLogin();

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Get user progress
$stmt = $conn->prepare("SELECT * FROM user_progress WHERE user_id = ?");
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$progress = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get achievements
$stmt = $conn->prepare("SELECT * FROM achievements WHERE user_id = ? ORDER BY date_earned DESC LIMIT 3");
if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$achievements = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - NPS eLearning</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="Dashboard.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand" href="Home.html">
                <img src="IMG/logo1.jpg" alt="eLearning Logo" height="40"> NPS eLearning
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="Home.html">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="Courses.html">Courses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="Dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="AboutUs.html">About Us</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <a href="profile-settings.php" class="btn btn-light me-2">
                        <i class="bi bi-gear-fill"></i> Settings
                    </a>
                    <a href="logout.php" class="btn btn-outline-light">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5 pt-5">
        <!-- User Profile Overview -->
        <div class="user-profile mb-5">
            <div class="row">
                <div class="col-lg-4 text-center text-lg-start">
                    <?php if (!empty($user['profile_picture'])): ?>
                        <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Picture" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                    <?php else: ?>
                        <div class="text-center mb-3">
                            <i class="bi bi-person-circle" style="font-size: 150px; color: #6c757d;"></i>
                            <p class="text-muted mt-2">
                                <a href="profile-settings.php" class="text-decoration-none">Please set up your profile picture</a>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-lg-8">
                    <h1 class="mb-2">Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h1>
                    <p class="text-muted"><?php echo htmlspecialchars($user['email']); ?></p>
                    <p class="fs-4 mb-2">Level <?php echo calculateLevel($user['id']); ?></p>
                </div>
            </div>
        </div>

        <!-- Progress Section -->
        <div class="row mb-4">
            <?php foreach ($progress as $course): ?>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($course['course_name']); ?></h5>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: <?php echo $course['progress']; ?>%"
                                 aria-valuenow="<?php echo $course['progress']; ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <?php echo $course['progress']; ?>%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Achievements Section -->
        <h3 class="mb-4">Recent Achievements</h3>
        <div class="row mb-4">
            <?php foreach ($achievements as $achievement): ?>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($achievement['achievement_name']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($achievement['achievement_description']); ?></p>
                        <small class="text-muted">Earned on <?php echo date('M d, Y', strtotime($achievement['date_earned'])); ?></small>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>