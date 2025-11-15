<?php
require_once 'config.php';

$lang = isset($_GET['lang']) ? $_GET['lang'] : (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en');
$_SESSION['lang'] = $lang;

$error = '';
$success = '';
$email_value = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];

    $email_value = $email;

    if (empty($email) || empty($password)) {
        $error = $_SESSION['lang'] == "ar" ? 'مطلوب ملء جميع الخانات' : 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = $_SESSION['lang'] == "ar" ? 'الايميل غير صالح' : 'Invalid email format';
    } else {
        $query = "SELECT * FROM users WHERE email = ? LIMIT 1";
        if ($stmt = mysqli_prepare($conn, $query)) {
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($result && mysqli_num_rows($result) > 0) {
                $user = mysqli_fetch_assoc($result);

                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['specialty'] = $user['specialty'];

                    mysqli_stmt_close($stmt);

                    if ($user['role'] === 'doctor') {
                        header("Location: doctor_panel.php?lang=$lang");
                        exit();
                    } elseif ($user['role'] === 'superadmin') {
                        header("Location: admin_panel.php?lang=$lang");
                        exit();
                    } else {
                        header("Location: index.php?lang=$lang");
                        exit();
                    }
                } else {
                    $error = $_SESSION['lang'] == "ar" ? 'تأكد من ادخال الايميل والباسورد بشكل صحيح' : 'Invalid email or password';
                }
            } else {
                $error = $_SESSION['lang'] == "ar" ? 'تأكد من ادخال الايميل والباسورد بشكل صحيح' : 'Invalid email or password';
            }

            mysqli_stmt_close($stmt);
        } else {
            $error = $_SESSION['lang'] == "ar" ? 'هناك مشكله حاول مره اخري' : 'An error occurred. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Medical Consultation</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="<?php echo $lang === 'ar' ? 'rtl' : ''; ?>">
    <?php include 'navbar.php'; ?>

    <div class="container">
        <div class="form-container fade-in">
            <div class="form-header">
                <div class="form-icon">🔐</div>
                <h2><?php echo $lang === 'en' ? 'Login to your account' : 'تسجيل الدخول إلى حسابك'; ?></h2>
            </div>

            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?lang=' . $lang); ?>"
                id="loginForm">
                <div class="form-group">
                    <label for="email"><?php echo $lang === 'en' ? 'Email' : 'البريد الإلكتروني'; ?></label>
                    <input type="email" id="email" name="email" class="form-control" required
                        value="<?php echo htmlspecialchars($email_value); ?>">
                </div>

                <div class="form-group">
                    <label for="password"><?php echo $lang === 'en' ? 'Password' : 'كلمة المرور'; ?></label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <?php echo $lang === 'en' ? 'Login' : 'تسجيل الدخول'; ?>
                </button>
            </form>

            <p class="mt-2 text-center">
                <?php echo $lang === 'en' ? "Don't have an account?" : 'ليس لديك حساب؟'; ?>
                <a href="register.php?lang=<?php echo $lang; ?>">
                    <?php echo $lang === 'en' ? 'Register here' : 'سجل هنا'; ?>
                </a>
            </p>
        </div>
    </div>

    <footer class="footer">
        <p><?php echo $lang === 'en' ? '© 2025 Medical Consultation' : '© 2025 الاستشارات الطبية'; ?></p>
    </footer>

    <script src="script.js"></script>
</body>

</html>