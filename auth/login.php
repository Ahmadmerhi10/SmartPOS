<?php
session_start();

require "../config/db.php";

$error = "";

// جلب الإيميل المحفوظ من الـ Cookie إن وجد
$savedEmail = $_COOKIE["remember_email"] ?? "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["signin"])) {

    $email = trim($_POST["email"] ?? '');
    $password = $_POST["password"] ?? '';
    $remember = isset($_POST["rememberMe"]);

    if ($email === '' || $password === '') {
        $error = "You should type the email and password";
    } else {
        // البحث الديناميكي عن أي مستخدم بواسطة البريد الإلكتروني
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        // التحقق الديناميكي من كلمة السر لأي مستخدم
        if ($data && password_verify($password, $data["password_hash"])) {
            session_regenerate_id(true);

            $_SESSION["user_id"] = $data["id"];
            $_SESSION["name"]    = $data["name"];
            $_SESSION["email"]   = $data["email"];
            $_SESSION["role"]    = $data["role"];

            // حفظ / مسح الكوكي بحسب الاختيار
            if ($remember) {
                setcookie("remember_email", $email, time() + (30 * 24 * 60 * 60), "/");
            } else {
                setcookie("remember_email", "", time() - 3600, "/");
            }

            header("Location: ../index.php");
            exit;
        } else {
            $error = "Invalid email or password";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartPOS - Login</title>

    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        #sidebar-wrapper,
        nav.navbar {
            display: none !important;
        }

        body {
            background-color: #f8f9fa !important;
        }
    </style>
</head>

<body class="bg-light d-flex align-items-center justify-content-center min-vh-100 m-0 p-0">

    <div class="card shadow-lg border-0 rounded-4 p-4" style="width: 100%; max-width: 420px;">
        <div class="card-body">

            <!-- Logo / Header -->
            <div class="text-center mb-4">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <i class="fa-solid fa-cash-register fa-2x"></i>
                </div>
                <h3 class="fw-bold text-dark">SmartPOS</h3>
                <p class="text-muted fs-6">Sign in to continue</p>
            </div>

            <!-- Login Form -->
            <form action="login.php" method="POST">

                <!-- Email Input -->
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted">
                            <i class="fa-solid fa-envelope"></i>
                        </span>
                        <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required autocomplete="email" value="<?= htmlspecialchars($_POST['email'] ?? $savedEmail) ?>">
                    </div>
                </div>

                <!-- Password Input -->
                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="rememberMe" name="rememberMe" <?= (isset($_POST['rememberMe']) || (empty($_POST) && isset($_COOKIE["remember_email"]))) ? "checked" : "" ?>>
                    <label class="form-check-label text-muted" for="rememberMe">
                        Remember Me
                    </label>
                </div>

                <!-- Error Alert -->
                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger py-2 fs-7">
                        <i class="fa-solid fa-circle-exclamation me-1"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold rounded-3" name="signin">
                    <i class="fa-solid fa-right-to-bracket me-2"></i>
                    Login
                </button>

            </form>

        </div>

        <!-- Card Footer -->
        <div class="card-footer bg-transparent border-0 text-center text-muted mt-2 fs-7">
            &copy; 2026 SmartPOS. All rights reserved.
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>