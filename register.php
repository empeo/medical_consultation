<?php
require_once 'config.php';

$lang = isset($_GET['lang']) ? $_GET['lang'] : (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en');
$_SESSION['lang'] = $lang;

$error = '';
$success = '';
$name_value = '';
$email_value = '';

// للتأكد من الباسورد
function is_strong_password($password)
{
    return strlen($password) < 6 || !preg_match('/[^A-Za-z0-9]/', $password);
}

// ارسال بيانات المريض للتسجيل
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $name_value = $name;
    $email_value = $email;

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = $_SESSION['lang'] == "ar" ? 'كل الخانات مطلوبه' : 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = $_SESSION['lang'] == "ar" ? 'هذا ليس ايميل صالح' : 'Invalid email format';
    } elseif (is_strong_password($password)) {
        $error = $_SESSION['lang'] == "ar" ? 'كلمة المرور يجب أن تكون 6 أحرف على الأقل، وتحتوي على رمز واحد على الأقل مثل @$!%*#؟&' : 'Password must be at least 6 characters and contain at least one special character';
    } elseif ($password !== $confirm_password) {
        $error = $_SESSION['lang'] == "ar" ? 'هذا ليس مطابق لكلمة الباسورد' : 'Passwords do not match';
    } else {
        $check_query = "SELECT id FROM users WHERE email = ? LIMIT 1";
        if ($check_stmt = mysqli_prepare($conn, $check_query)) {
            mysqli_stmt_bind_param($check_stmt, "s", $email);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);

            if ($check_result && mysqli_num_rows($check_result) > 0) {
                $error = $_SESSION['lang'] == "ar" ? 'الايميل ده موجود' : 'Email already in use';
                mysqli_stmt_close($check_stmt);
            } else {
                mysqli_stmt_close($check_stmt);

                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $insert_query = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'patient')";
                if ($insert_stmt = mysqli_prepare($conn, $insert_query)) {
                    mysqli_stmt_bind_param($insert_stmt, "sss", $name, $email, $hashed_password);

                    if (mysqli_stmt_execute($insert_stmt)) {
                        mysqli_stmt_close($insert_stmt);
                        $success = $_SESSION['lang'] == "ar" ? 'تم تسيجل البيانات بشكل صحيح اذخب لصفحةالدخول' : 'Registration successful! Redirecting to login...';
                        $name_value = '';
                        $email_value = '';
                        header("refresh:2;url=login.php?lang=$lang");
                    } else {
                        $error = $_SESSION['lang'] == "ar" ? 'لم تتم تسجيل البيانات حاول مره اخري' : 'Registration failed. Please try again.';
                    }
                } else {
                    $error = $_SESSION['lang'] == "ar" ? 'لم تتم تسجيل البيانات حاول مره اخري' : 'Registration failed. Please try again.';
                }
            }
        } else {
            $error = $_SESSION['lang'] == "ar" ? 'لم تتم تسجيل البيانات حاول مره اخري' : 'Registration failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Medical Consultation</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="<?php echo $lang === 'ar' ? 'rtl' : ''; ?>">
    <?php include 'navbar.php'; ?>

    <div class="container">
        <div class="form-container fade-in">
            <div class="form-header">
                <div class="form-icon">📝</div>
                <h2><?php echo $lang === 'en' ? 'Create a new account' : 'إنشاء حساب جديد'; ?></h2>
            </div>

            <?php if ($error): ?>
                <div class="error-message server-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message server-message"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?lang=' . $lang); ?>"
                id="registerForm">
                <div class="form-group">
                    <label for="name"><?php echo $lang === 'en' ? 'Name' : 'الاسم'; ?></label>
                    <input type="text" id="name" name="name" class="form-control" required
                        value="<?php echo htmlspecialchars($name_value); ?>">
                </div>

                <div class="form-group">
                    <label for="email"><?php echo $lang === 'en' ? 'Email' : 'البريد الإلكتروني'; ?></label>
                    <input type="email" id="email" name="email" class="form-control" required
                        value="<?php echo htmlspecialchars($email_value); ?>">
                </div>

                <div class="form-group">
                    <label for="password"><?php echo $lang === 'en' ? 'Password' : 'كلمة المرور'; ?></label>
                    <input type="password" id="password" name="password" class="form-control" required>
                    <small class="question-meta">
                        <?php echo $lang === 'en'
                            ? 'Password must be at least 6 characters and contain at least one special character.'
                            : 'يجب أن تكون كلمة المرور 6 أحرف على الأقل وتحتوي على رمز خاص واحد على الأقل.'; ?>
                    </small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">
                        <?php echo $lang === 'en' ? 'Confirm Password' : 'تأكيد كلمة المرور'; ?>
                    </label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <?php echo $lang === 'en' ? 'Register' : 'تسجيل'; ?>
                </button>
            </form>

            <p class="mt-2 text-center">
                <?php echo $lang === 'en' ? 'Already have an account?' : 'لديك حساب بالفعل؟'; ?>
                <a href="login.php?lang=<?php echo $lang; ?>">
                    <?php echo $lang === 'en' ? 'Login here' : 'سجل دخول هنا'; ?>
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