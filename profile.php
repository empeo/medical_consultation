<?php
require_once 'config.php';


if (!isLoggedIn()) {
    redirect('login.php');
}

$lang = isset($_GET['lang']) ? $_GET['lang'] : (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en');

$_SESSION['lang'] = $lang;

$error = '';
$success = '';

// دالة فحص قوة الباسورد
function is_strong_password($password)
{
    return strlen($password) < 6 || !preg_match('/[^A-Za-z0-9]/', $password);
}

$user = null;
$user_id = $_SESSION['user_id'];

// نجيب بيانات المستخدم
$user_query = "SELECT * FROM users WHERE id = ?";
if ($stmt = mysqli_prepare($conn, $user_query)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
    }
    mysqli_stmt_close($stmt);
}

if (!$user) {
    session_destroy();
    redirect('login.php');
}

// نجيب كل التخصصات من جدول specialties
$specialties = [];
$specialty_query = "SELECT id, name_en, name_ar FROM specialties ORDER BY name_en";
if ($spec_stmt = mysqli_prepare($conn, $specialty_query)) {
    mysqli_stmt_execute($spec_stmt);
    $spec_result = mysqli_stmt_get_result($spec_stmt);
    if ($spec_result && mysqli_num_rows($spec_result) > 0) {
        while ($row = mysqli_fetch_assoc($spec_result)) {
            $specialties[] = $row;
        }
    }
    mysqli_stmt_close($spec_stmt);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    // دلوقتي بنستقبل specialty_id مش نص
    $specialty_id = isset($_POST['specialty_id']) ? intval($_POST['specialty_id']) : null;

    if (empty($name) || empty($email)) {
        $error = $_SESSION['lang'] == "ar" ? 'الاسم والايميل مطلوبين' : 'Name and email are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = $_SESSION['lang'] == "ar" ? 'هذا ايميل غير صالح' : 'Invalid email format';
    } else {
        // نتأكد إن الإيميل مش مكرر
        $check_query = "SELECT id FROM users WHERE email = ? AND id != ?";
        if ($check_stmt = mysqli_prepare($conn, $check_query)) {
            mysqli_stmt_bind_param($check_stmt, "si", $email, $user_id);
            mysqli_stmt_execute($check_stmt);
            $check_result = mysqli_stmt_get_result($check_stmt);

            if ($check_result && mysqli_num_rows($check_result) > 0) {
                $error = $_SESSION['lang'] == "ar" ? 'الايميل ده موجود' : 'Email already in use';
                mysqli_stmt_close($check_stmt);
            } else {
                mysqli_stmt_close($check_stmt);

                // لو هو دكتور لازم يكون فيه specialty_id
                if ($_SESSION['role'] === 'doctor' && empty($specialty_id)) {
                    $error = $_SESSION['lang'] == "ar" ? 'التخصص مطلوب للأطباء' : 'Specialty is required for doctors';
                } else {
                    // لو فيه باسورد جديد
                    if (!empty($password)) {
                        if (is_strong_password($password)) {
                            $error = $_SESSION['lang'] == "ar"
                                ? 'كلمة المرور يجب أن تكون 6 أحرف على الأقل، وتحتوي على رمز واحد على الأقل مثل @$!%*#؟&'
                                : 'Password must be at least 6 characters and contain at least one special character';
                        } else {
                            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                            if ($_SESSION['role'] === 'doctor' && !empty($specialty_id)) {
                                $update_query = "UPDATE users SET name = ?, email = ?, password = ?, specialty_id = ? WHERE id = ?";
                                $stmt = mysqli_prepare($conn, $update_query);
                                mysqli_stmt_bind_param($stmt, "sssii", $name, $email, $hashed_password, $specialty_id, $user_id);
                            } else {
                                $update_query = "UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?";
                                $stmt = mysqli_prepare($conn, $update_query);
                                mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $hashed_password, $user_id);
                            }
                        }
                    } else {
                        // مفيش باسورد جديد
                        if ($_SESSION['role'] === 'doctor' && !empty($specialty_id)) {
                            $update_query = "UPDATE users SET name = ?, email = ?, specialty_id = ? WHERE id = ?";
                            $stmt = mysqli_prepare($conn, $update_query);
                            mysqli_stmt_bind_param($stmt, "ssii", $name, $email, $specialty_id, $user_id);
                        } else {
                            $update_query = "UPDATE users SET name = ?, email = ? WHERE id = ?";
                            $stmt = mysqli_prepare($conn, $update_query);
                            mysqli_stmt_bind_param($stmt, "ssi", $name, $email, $user_id);
                        }
                    }

                    if (empty($error)) {
                        if (mysqli_stmt_execute($stmt)) {
                            $_SESSION['name'] = $name;
                            $_SESSION['email'] = $email;

                            if ($_SESSION['role'] === 'doctor' && !empty($specialty_id)) {
                                // نخزن الـ ID بتاع التخصص في السيشن
                                $_SESSION['specialty_id'] = $specialty_id;
                            }

                            mysqli_stmt_close($stmt);
                            header("Location: profile.php?lang=$lang&success=1");
                            exit();
                        } else {
                            $error = $_SESSION['lang'] == "ar"
                                ? 'حاول مره اخري هناك مشكله فى التعديل'
                                : 'Update failed. Please try again.';
                        }
                    }
                }
            }
        } else {
            $error = $_SESSION['lang'] == "ar"
                ? 'حاول مره اخري هناك مشكله فى التعديل'
                : 'Update failed. Please try again.';
        }
    }
}

if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success = $_SESSION['lang'] == "ar" ? 'البيانات اتحدثت بنجاح' : 'Profile updated successfully!';

    // Reload user data
    $user_query = "SELECT * FROM users WHERE id = ?";
    if ($stmt = mysqli_prepare($conn, $user_query)) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
        }
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Medical Consultation</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="<?php echo $lang === 'ar' ? 'rtl' : ''; ?>">
    <?php include 'navbar.php'; ?>

    <div class="container">
        <div class="form-container fade-in" style="max-width: 800px;">
            <div class="form-header">
                <div class="form-icon">👤</div>
                <h2><?php echo $lang === 'en' ? 'Profile' : 'الملف الشخصي'; ?></h2>
            </div>

            <?php if ($error): ?>
                <div class="error-message server-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message server-message"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?lang=' . $lang); ?>"
                id="profileForm">
                <div class="form-group">
                    <label for="name"><?php echo $lang === 'en' ? 'Name' : 'الاسم'; ?></label>
                    <input type="text" id="name" name="name" class="form-control" required
                        value="<?php echo htmlspecialchars($user['name']); ?>">
                </div>

                <div class="form-group">
                    <label for="email"><?php echo $lang === 'en' ? 'Email' : 'البريد الإلكتروني'; ?></label>
                    <input type="email" id="email" name="email" class="form-control" required
                        value="<?php echo htmlspecialchars($user['email']); ?>">
                </div>

                <div class="form-group">
                    <label for="password">
                        <?php echo $lang === 'en' ? 'New Password (optional)' : 'كلمة المرور الجديدة (اختياري)'; ?>
                    </label>
                    <input type="password" id="password" name="password" class="form-control">
                    <small class="question-meta">
                        <?php echo $lang === 'en'
                            ? 'Leave blank to keep current password. Must be at least 6 characters and contain a special character.'
                            : 'اتركها فارغة للحفاظ على كلمة المرور الحالية. يجب أن تكون على الأقل 6 أحرف وبها رمز خاص.'; ?>
                    </small>
                </div>

                <?php if ($_SESSION['role'] === 'doctor'): ?>
                    <div class="form-group">
                        <label for="specialty_id">
                            <?php echo $lang === 'en' ? 'Specialty' : 'التخصص'; ?>
                        </label>

                        <select id="specialty_id" name="specialty_id" class="form-control" required>
                            <option value="">
                                <?php echo $lang === 'en' ? 'Select Specialty' : 'اختر التخصص'; ?>
                            </option>

                            <?php foreach ($specialties as $spec): ?>
                                <option value="<?php echo $spec['id']; ?>" <?php echo ($user['specialty_id'] == $spec['id']) ? 'selected' : ''; ?>>
                                    <?php echo $lang === 'ar'
                                        ? htmlspecialchars($spec['name_ar'])
                                        : htmlspecialchars($spec['name_en']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>
                        <?php echo $lang === 'en' ? 'Role' : 'الدور'; ?>:
                    </label>
                    <p class="question-meta">
                        <?php
                        if ($user['role'] === 'patient') {
                            echo $lang === 'en' ? 'Patient' : 'مريض';
                        } elseif ($user['role'] === 'doctor') {
                            echo $lang === 'en' ? 'Doctor' : 'طبيب';
                        } else {
                            echo $lang === 'en' ? 'Super Admin' : 'مدير عام';
                        }
                        ?>
                    </p>
                </div>

                <button type="submit" name="update_profile" class="btn btn-primary" style="width: 100%;">
                    <?php echo $lang === 'en' ? 'Update Profile' : 'تحديث الملف الشخصي'; ?>
                </button>
            </form>
        </div>
    </div>

    <footer class="footer">
        <p><?php echo $lang === 'en' ? '© 2025 Medical Consultation' : '© 2025 الاستشارات الطبية'; ?></p>
    </footer>

    <script src="script.js"></script>
</body>

</html>