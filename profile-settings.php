<?php
require_once 'config.php';
requireLogin();

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings - NPS eLearning</title>
    <link rel="stylesheet" href="[https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">](https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">)
    <link rel="stylesheet" href="[https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">](https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">)
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 80px;
        }
        .profile-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
            transition: transform 0.3s ease;
        }
        .profile-card:hover {
            transform: translateY(-5px);
        }
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }
        .profile-picture-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 20px;
        }
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .profile-picture:hover {
            transform: scale(1.05);
        }
        .upload-btn-wrapper {
            position: absolute;
            right: 0;
            bottom: 0;
        }
        .upload-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #0d6efd;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .upload-btn:hover {
            transform: scale(1.1);
            background: #0b5ed7;
        }
        .upload-btn input[type=file] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        .info-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .info-item {
            margin-bottom: 15px;
        }
        .info-label {
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 1.1em;
        }
        .action-buttons {
            display: grid;
            gap: 10px;
            margin-top: 20px;
        }
        .btn-action {
            padding: 12px 20px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
            animation: slideIn 0.5s ease;
        }
        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
    <!-- Include navbar -->
    <?php include 'navbar-loggedin.html'; ?>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-picture-container">
                            <img src="<?php echo htmlspecialchars($user['profile_picture'] ?? 'IMG/default-profile.jpg'); ?>" 
                                 alt="Profile Picture" class="profile-picture">
                            <div class="upload-btn-wrapper">
                                <form action="upload-profile-picture.php" method="POST" enctype="multipart/form-data" id="profile-pic-form">
                                    <div class="upload-btn">
                                        <i class="bi bi-camera-fill"></i>
                                        <input type="file" name="profile_picture" accept="image/*" onchange="this.form.submit()">
                                    </div>
                                </form>
                            </div>
                        </div>
                        <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                        <p class="text-muted"><?php echo ucfirst($user['user_type']); ?></p>
                    </div>

                    <?php if (isset($_SESSION['message'])): ?>
                        <div class="alert alert-<?php echo strpos($_SESSION['message'], 'Error') !== false ? 'danger' : 'success'; ?> alert-dismissible fade show">
                            <?php 
                                echo $_SESSION['message']; 
                                unset($_SESSION['message']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="info-section">
                        <div class="info-item">
                            <div class="info-label">Email</div>
                            <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Phone</div>
                            <div class="info-value"><?php echo htmlspecialchars($user['phone'] ?? 'Not set'); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Bio</div>
                            <div class="info-value"><?php echo htmlspecialchars($user['bio'] ?? 'No bio added yet'); ?></div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Last Login</div>
                            <div class="info-value"><?php echo date('F j, Y g:i a', strtotime($user['last_login'])); ?></div>
                        </div>
                    </div>

                    <div class="action-buttons">
                        <a href="update-profile.php" class="btn btn-primary btn-action">
                            <i class="bi bi-pencil-square me-2"></i>Edit Profile
                        </a>
                        <a href="change-password.php" class="btn btn-outline-primary btn-action">
                            <i class="bi bi-shield-lock me-2"></i>Change Password
                        </a>
                        <a href="dashboard.php" class="btn btn-outline-secondary btn-action">
                            <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="[https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>](https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>)
</body>
</html>