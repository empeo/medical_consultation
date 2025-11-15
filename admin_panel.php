<?php
require_once 'config.php';

if (!isLoggedIn() || !hasRole('superadmin')) {
    redirect('index.php');
}

$lang = isset($_GET['lang']) ? $_GET['lang'] : (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en');
$_SESSION['lang'] = $lang;

$error = '';
$success = '';

$specialties = [];
$specialty_query = "SELECT id, name_en, name_ar, code FROM specialties ORDER BY name_en";
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

// -------------------------
// Add Doctor
// -------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_doctor'])) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $specialty_id = isset($_POST['specialty_id']) ? intval($_POST['specialty_id']) : 0;

    if (empty($name) || empty($email) || empty($password) || empty($specialty_id)) {
        $error = $_SESSION['lang'] == 'ar' ? 'كل الخانات مطلوبه' : 'All fields are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = $_SESSION['lang'] == 'ar' ? 'الايميل غير صالح' : 'Invalid email format';
    } elseif (strlen($password) < 6 || !preg_match('/[^A-Za-z0-9]/', $password)) {
        $error = $_SESSION['lang'] == "ar" ? 'كلمة المرور يجب أن تكون 6 أحرف على الأقل، وتحتوي على رمز واحد على الأقل مثل @$!%*#؟&' : 'Password must be at least 6 characters and contain at least one special character';
    } elseif ($password !== $confirm_password) {
        $error = $_SESSION['lang'] == "ar" ? 'هذا ليس مطابق لكلمة الباسورد' : 'Passwords do not match';
    } else {
        $check_query = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $check_result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($check_result) > 0) {
            $error = $_SESSION['lang'] == "ar" ? 'الايميل ده موجود' : 'Email already in use';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_query = "INSERT INTO users (name, email, password, role, specialty_id) VALUES (?, ?, ?, 'doctor', ?)";
            $stmt = mysqli_prepare($conn, $insert_query);
            mysqli_stmt_bind_param($stmt, "sssi", $name, $email, $hashed_password, $specialty_id);

            if (mysqli_stmt_execute($stmt)) {
                $success = $_SESSION['lang'] == 'ar' ? 'البيانات تمت اضافتها بنجاح' : 'Doctor added successfully!';
                header("Location: admin_panel.php?lang=$lang&success=1");
                exit();
            } else {
                $error = $_SESSION['lang'] == "ar" ? 'لم تتم تسجيل البيانات حاول مره اخري' : 'Failed to add doctor. Please try again.';
            }
        }
    }
}

// -------------------------
// Delete User
// -------------------------
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);

    $check_query = "SELECT role FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $check_result = mysqli_stmt_get_result($stmt);
    $user_to_delete = mysqli_fetch_assoc($check_result);

    if ($user_to_delete && $user_to_delete['role'] === 'superadmin') {
        $count_query = "SELECT COUNT(*) as count FROM users WHERE role = 'superadmin'";
        $count_result = mysqli_query($conn, $count_query);
        $count_row = mysqli_fetch_assoc($count_result);

        if ($count_row['count'] <= 1) {
            $_SESSION['error'] = $_SESSION['lang'] == "ar" ? 'لا يمكن حذف اخر ادمن موجود فى السيستيم لازم يبقا على الاقل فيه واحد' : 'Cannot delete the only superadmin account!';
        } else {
            $delete_query = "DELETE FROM users WHERE id = ?";
            $stmt = mysqli_prepare($conn, $delete_query);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
        }
    } else {
        $delete_query = "DELETE FROM users WHERE id = ?";
        $stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
    }

    header("Location: admin_panel.php?lang=$lang");
    exit();
}

// -------------------------
// Edit Doctor
// -------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_doctor'])) {
    $user_id = intval($_POST['user_id']);
    $name = sanitize($_POST['edit_name']);
    $email = sanitize($_POST['edit_email']);
    $specialty_id = isset($_POST['edit_specialty_id']) ? intval($_POST['edit_specialty_id']) : 0;
    $password = $_POST['edit_password'];

    if (empty($name) || empty($email) || empty($specialty_id)) {
        $error = $_SESSION['lang'] == "ar" ? 'مطلوب ملء الخانات' : 'Name, email, and specialty are required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = $_SESSION['lang'] == 'ar' ? 'الايميل غير صالح' : 'Invalid email format';
    } else {
        $check_query = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, "si", $email, $user_id);
        mysqli_stmt_execute($stmt);
        $check_result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($check_result) > 0) {
            $error = $_SESSION['lang'] == "ar" ? 'الايميل ده موجود' : 'Email already in use';
        } else {
            if (!empty($password)) {
                if (strlen($password) < 6 || !preg_match('/[^A-Za-z0-9]/', $password)) {
                    $error = $_SESSION['lang'] == "ar" ? 'كلمة المرور يجب أن تكون 6 أحرف على الأقل، وتحتوي على رمز واحد على الأقل مثل @$!%*#؟&' : 'Password must be at least 6 characters and contain at least one special character';
                } else {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $update_query = "UPDATE users SET name = ?, email = ?, specialty_id = ?, password = ? WHERE id = ?";
                    $stmt = mysqli_prepare($conn, $update_query);
                    mysqli_stmt_bind_param($stmt, "ssisi", $name, $email, $specialty_id, $hashed_password, $user_id);
                }
            } else {
                $update_query = "UPDATE users SET name = ?, email = ?, specialty_id = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $update_query);
                mysqli_stmt_bind_param($stmt, "ssii", $name, $email, $specialty_id, $user_id);
            }

            if (empty($error)) {
                if (mysqli_stmt_execute($stmt)) {
                    $success = $_SESSION['lang'] == 'ar' ? 'البيانات تمت تعديلها' : 'Doctor updated successfully!';
                    header("Location: admin_panel.php?lang=$lang&success=2");
                    exit();
                } else {
                    $error = $_SESSION['lang'] == 'ar' ? 'البيانات لم تتم تحديثها حاول مره اخري' : 'Update failed. Please try again.';
                }
            }
        }
    }
}

// -------------------------
// Add Specialty
// -------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_specialty'])) {
    $name_en = sanitize($_POST['name_en'] ?? '');
    $name_ar = sanitize($_POST['name_ar'] ?? '');
    $code = sanitize($_POST['code'] ?? '');

    if (empty($name_en) || empty($name_ar) || empty($code)) {
        $error = $_SESSION['lang'] == 'ar' ? 'يجب ملء جميع الخانات' : 'English, Arabic names and code are required for specialty';
    } else {
        $check_spec_query = "SELECT id FROM specialties WHERE name_en = ? OR name_ar = ? OR code = ?";
        if ($check_stmt = mysqli_prepare($conn, $check_spec_query)) {
            mysqli_stmt_bind_param($check_stmt, "sss", $name_en, $name_ar, $code);
            mysqli_stmt_execute($check_stmt);
            $check_spec_result = mysqli_stmt_get_result($check_stmt);

            if ($check_spec_result && mysqli_num_rows($check_spec_result) > 0) {
                $error = $_SESSION['lang'] == 'ar' ? 'خانت الاسم او الكود موجود تأكد انه ليس موجود' : 'This specialty (name or code) already exists';
            } else {
                $insert_spec_query = "INSERT INTO specialties (name_en, name_ar, code) VALUES (?, ?, ?)";
                if ($ins_stmt = mysqli_prepare($conn, $insert_spec_query)) {
                    mysqli_stmt_bind_param($ins_stmt, "sss", $name_en, $name_ar, $code);
                    if (mysqli_stmt_execute($ins_stmt)) {
                        mysqli_stmt_close($ins_stmt);
                        header("Location: admin_panel.php?lang=$lang&success=3");
                        exit();
                    } else {
                        $error = $_SESSION['lang'] == 'ar' ? 'فشل عملية الاضافه حاول مره اخري' : 'Failed to add specialty. Please try again.';
                    }
                } else {
                    $error = $_SESSION['lang'] == 'ar' ? 'فشل عملية الاضافه حاول مره اخري' : 'Failed to add specialty. Please try again.';
                }
            }
            mysqli_stmt_close($check_stmt);
        } else {
            $error = $_SESSION['lang'] == 'ar' ? 'فشل عملية الاضافه حاول مره اخري' : 'Failed to add specialty. Please try again.';
        }
    }
}


// -------------------------
// Delete Specialty + reassign doctors
// -------------------------
if (isset($_GET['delete_specialty'])) {
    $spec_id = intval($_GET['delete_specialty']);

    $check_spec = "SELECT id FROM specialties WHERE id = ?";
    if ($cs_stmt = mysqli_prepare($conn, $check_spec)) {
        mysqli_stmt_bind_param($cs_stmt, "i", $spec_id);
        mysqli_stmt_execute($cs_stmt);
        $cs_result = mysqli_stmt_get_result($cs_stmt);
        $exists = ($cs_result && mysqli_num_rows($cs_result) > 0);
        mysqli_stmt_close($cs_stmt);

        if ($exists) {
            $fallback_q = "SELECT id FROM specialties WHERE id != ? ORDER BY id LIMIT 1";
            if ($fb_stmt = mysqli_prepare($conn, $fallback_q)) {
                mysqli_stmt_bind_param($fb_stmt, "i", $spec_id);
                mysqli_stmt_execute($fb_stmt);
                $fb_result = mysqli_stmt_get_result($fb_stmt);
                $fb_row = $fb_result ? mysqli_fetch_assoc($fb_result) : null;
                mysqli_stmt_close($fb_stmt);

                if (!$fb_row) {
                    $_SESSION['error'] = ($_SESSION['lang'] == 'ar') ? 'لا يمكن حذف التخصص الوحيد. يرجى إضافة تخصص آخر أولاً.' : 'Cannot delete the only specialty. Please add another specialty first.';
                    header("Location: admin_panel.php?lang=$lang");
                    exit();
                }

                $fallback_id = intval($fb_row['id']);

                $update_docs = "UPDATE users SET specialty_id = ? WHERE specialty_id = ? AND role = 'doctor'";
                if ($ud_stmt = mysqli_prepare($conn, $update_docs)) {
                    mysqli_stmt_bind_param($ud_stmt, "ii", $fallback_id, $spec_id);
                    mysqli_stmt_execute($ud_stmt);
                    mysqli_stmt_close($ud_stmt);
                }

                $del_spec = "DELETE FROM specialties WHERE id = ?";
                if ($ds_stmt = mysqli_prepare($conn, $del_spec)) {
                    mysqli_stmt_bind_param($ds_stmt, "i", $spec_id);
                    mysqli_stmt_execute($ds_stmt);
                    mysqli_stmt_close($ds_stmt);

                    header("Location: admin_panel.php?lang=$lang&success=4");
                    exit();
                } else {
                    $_SESSION['error'] = $_SESSION['lang'] == 'ar' ? 'لم تتم عملية الحذف حاول مره اخري' : 'Failed to delete specialty.';
                    header("Location: admin_panel.php?lang=$lang");
                    exit();
                }
            } else {
                $_SESSION['error'] = $_SESSION['lang'] == 'ar' ? 'لا يوجد بينات بهذا الاسم' : 'Failed to find fallback specialty.';
                header("Location: admin_panel.php?lang=$lang");
                exit();
            }
        } else {
            $_SESSION['error'] = $_SESSION['lang'] == 'ar' ? 'لا يوجد بينات بهذا الاسم' : 'Specialty not found.';
            header("Location: admin_panel.php?lang=$lang");
            exit();
        }
    } else {
        $_SESSION['error'] = $_SESSION['lang'] == 'ar' ? 'لا يوجد بينات بهذا الاسم' : 'Failed to check specialty.';
        header("Location: admin_panel.php?lang=$lang");
        exit();
    }
}

// -------------------------
// Success messages
// -------------------------
if (isset($_GET['success'])) {
    if ($_GET['success'] == 1) {
        $success = $_SESSION['lang'] == 'ar' ? 'البيانات تمت اضافتها' : 'Doctor added successfully!';
    } elseif ($_GET['success'] == 2) {
        $success = $_SESSION['lang'] == 'ar' ? 'البيانات تمت تعديلها' : 'Doctor updated successfully!';
    } elseif ($_GET['success'] == 3) {
        $success = $_SESSION['lang'] == 'ar' ? 'البيانات تمت اضافتها' : 'Specialty added successfully!';
    } elseif ($_GET['success'] == 4) {
        $success = $_SESSION['lang'] == 'ar' ? 'البيانات تمت حذفها' : 'Specialty deleted and doctors reassigned successfully!';
    }
}

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

// -------------------------
// Load users with specialties
// -------------------------
$users_query = "SELECT u.*, s.name_en, s.name_ar
                FROM users u
                LEFT JOIN specialties s ON u.specialty_id = s.id
                ORDER BY 
                CASE u.role 
                    WHEN 'superadmin' THEN 1 
                    WHEN 'doctor' THEN 2 
                    WHEN 'patient' THEN 3 
                END, u.name";
$users_result = mysqli_query($conn, $users_query);
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Medical Consultation</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            box-shadow: var(--shadow-lg);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close:hover {
            color: #000;
        }
    </style>
</head>

<body class="<?php echo $lang === 'ar' ? 'rtl' : ''; ?>">
    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <div class="container">
        <div class="text-center mb-3">
            <div class="form-icon" style="margin: 0 auto;">🛡️</div>
            <h2 class="section-title"><?php echo $lang === 'en' ? 'Admin Panel' : 'لوحة الإدارة'; ?></h2>
        </div>

        <?php if ($error): ?>
            <div class="error-message" style="max-width: 800px; margin: 0 auto 2rem;"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message" style="max-width: 800px; margin: 0 auto 2rem;"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Add Doctor Button -->
        <button onclick="document.getElementById('addDoctorModal').style.display='block'" class="btn btn-primary"
            style="margin-bottom: 2rem;">
            ➕ <?php echo $lang === 'en' ? 'Add Doctor' : 'إضافة طبيب'; ?>
        </button>

        <!-- Users Table -->
        <div class="table-container">
            <div class="table-header">
                <h3><?php echo $lang === 'en' ? 'Manage Users' : 'إدارة المستخدمين'; ?></h3>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th><?php echo $lang === 'en' ? 'Name' : 'الاسم'; ?></th>
                        <th><?php echo $lang === 'en' ? 'Email' : 'البريد الإلكتروني'; ?></th>
                        <th><?php echo $lang === 'en' ? 'Role' : 'الدور'; ?></th>
                        <th><?php echo $lang === 'en' ? 'Specialty' : 'التخصص'; ?></th>
                        <th><?php echo $lang === 'en' ? 'Actions' : 'الإجراءات'; ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                        <tr>
                            <td data-label="<?php echo $lang === 'en' ? 'Name' : 'الاسم'; ?>">
                                <?php echo htmlspecialchars($user['name']); ?>
                            </td>

                            <td data-label="<?php echo $lang === 'en' ? 'Email' : 'البريد الإلكتروني'; ?>">
                                <?php echo htmlspecialchars($user['email']); ?>
                            </td>

                            <td data-label="<?php echo $lang === 'en' ? 'Role' : 'الدور'; ?>">
                                <span class="badge <?php
                                echo $user['role'] === 'superadmin' ? 'badge-danger' :
                                    ($user['role'] === 'doctor' ? 'badge-primary' : 'badge-success');
                                ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>

                            <td data-label="<?php echo $lang === 'en' ? 'Specialty' : 'التخصص'; ?>">
                                <?php
                                if ($user['role'] === 'doctor' && $user['specialty_id']) {
                                    echo $lang === 'ar'
                                        ? htmlspecialchars($user['name_ar'])
                                        : htmlspecialchars($user['name_en']);
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>

                            <td data-label="<?php echo $lang === 'en' ? 'Actions' : 'الإجراءات'; ?>">
                                <?php if ($user['role'] === 'doctor'): ?>
                                    <button onclick='openEditModal(<?php echo json_encode([
                                        "id" => $user["id"],
                                        "name" => $user["name"],
                                        "email" => $user["email"],
                                        "specialty_id" => $user["specialty_id"],
                                    ]); ?>)' class="btn btn-outline"
                                        style="padding: 0.5rem 1rem; margin-right: 0.5rem;">
                                        ✏️ <?php echo $lang === 'en' ? 'Edit' : 'تعديل'; ?>
                                    </button>
                                <?php endif; ?>

                                <a href="admin_panel.php?delete=<?php echo $user['id']; ?>&lang=<?php echo $lang; ?>"
                                    class="btn btn-danger" style="padding: 0.5rem 1rem;"
                                    onclick="return confirmDelete('<?php echo $lang === 'en' ? 'Delete this user?' : 'حذف هذا المستخدم؟'; ?>');">
                                    🗑️ <?php echo $lang === 'en' ? 'Delete' : 'حذف'; ?>
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>

            </table>
        </div>

        <!-- Manage Specialties Section -->
        <div style="margin-top: 3rem;">
            <div class="form-container" style="max-width: 700px; margin: 0 auto 2rem;">
                <div class="form-header">
                    <div class="form-icon">📋</div>
                    <h3><?php echo $lang === 'en' ? 'Add New Specialty' : 'إضافة تخصص جديد'; ?></h3>
                </div>

                <form method="POST" action="admin_panel.php?lang=<?php echo $lang; ?>">
                    <input type="hidden" name="add_specialty">

                    <div class="form-group">
                        <label for="name_en">
                            <?php echo $lang === 'en' ? 'Specialty (English)' : 'اسم التخصص بالإنجليزية'; ?>
                        </label>
                        <input type="text" id="name_en" name="name_en" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="name_ar">
                            <?php echo $lang === 'en' ? 'Specialty (Arabic)' : 'اسم التخصص بالعربية'; ?>
                        </label>
                        <input type="text" id="name_ar" name="name_ar" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="code">
                            <?php echo $lang === 'en' ? 'Specialty Code' : 'كود التخصص'; ?>
                        </label>
                        <input type="text" id="code" name="code" class="form-control" required
                            placeholder="<?php echo $lang === 'en' ? 'e.g. CARD, PED, DERMA' : 'مثال: CARD, PED, DERMA'; ?>">
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <?php echo $lang === 'en' ? 'Add Specialty' : 'إضافة تخصص'; ?>
                    </button>
                </form>

            </div>

            <div class="table-container" style="max-width: 700px; margin: 0 auto;">
                <div class="table-header">
                    <h3><?php echo $lang === 'en' ? 'Specialties List' : 'قائمة التخصصات'; ?></h3>
                </div>
                <table class="table">
                    <thead>
                        <tr>
                            <th><?php echo $lang === 'en' ? 'Code' : 'الكود'; ?></th>
                            <th><?php echo $lang === 'en' ? 'English Name' : 'الاسم بالإنجليزية'; ?></th>
                            <th><?php echo $lang === 'en' ? 'Arabic Name' : 'الاسم بالعربية'; ?></th>
                            <th><?php echo $lang === 'en' ? 'Actions' : 'الإجراءات'; ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($specialties)): ?>
                            <?php foreach ($specialties as $spec): ?>
                                <tr>
                                    <td data-label="<?php echo $lang === 'en' ? 'Code' : 'الكود'; ?>">
                                        <?php echo htmlspecialchars($spec['code']); ?>
                                    </td>

                                    <td data-label="<?php echo $lang === 'en' ? 'English Name' : 'الاسم بالإنجليزية'; ?>">
                                        <?php echo htmlspecialchars($spec['name_en']); ?>
                                    </td>

                                    <td data-label="<?php echo $lang === 'en' ? 'Arabic Name' : 'الاسم بالعربية'; ?>">
                                        <?php echo htmlspecialchars($spec['name_ar']); ?>
                                    </td>

                                    <td data-label="<?php echo $lang === 'en' ? 'Actions' : 'الإجراءات'; ?>">
                                        <a href="admin_panel.php?delete_specialty=<?php echo $spec['id']; ?>&lang=<?php echo $lang; ?>"
                                            class="btn btn-danger" onclick="return confirmDelete('<?php echo $lang === 'en'
                                                ? 'Delete this specialty? All doctors with this specialty will be reassigned automatically.'
                                                : 'حذف هذا التخصص؟ سيتم تحويل جميع الأطباء لهذا التخصص إلى تخصص آخر تلقائياً.'; ?>');">
                                            🗑️ <?php echo $lang === 'en' ? 'Delete' : 'حذف'; ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align:center; color:#6b7280;">
                                    <?php echo $lang === 'en' ? 'No specialties found.' : 'لا توجد تخصصات بعد.'; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>

                </table>
            </div>
        </div>

    </div>

    <!-- Add Doctor Modal -->
    <div id="addDoctorModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('addDoctorModal').style.display='none'">&times;</span>
            <h3 style="margin-bottom: 1.5rem;"><?php echo $lang === 'en' ? 'Add New Doctor' : 'إضافة طبيب جديد'; ?></h3>

            <form method="POST" action="admin_panel.php?lang=<?php echo $lang; ?>">
                <input type="hidden" name="add_doctor">
                <div class="form-group">
                    <label for="name"><?php echo $lang === 'en' ? 'Name' : 'الاسم'; ?></label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="email"><?php echo $lang === 'en' ? 'Email' : 'البريد الإلكتروني'; ?></label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="specialty_id"><?php echo $lang === 'en' ? 'Specialty' : 'التخصص'; ?></label>
                    <select id="specialty_id" name="specialty_id" class="form-control" required>
                        <option value=""><?php echo $lang === 'en' ? 'Select Specialty' : 'اختر التخصص'; ?></option>
                        <?php foreach ($specialties as $spec): ?>
                            <option value="<?php echo $spec['id']; ?>">
                                <?php echo $lang === 'ar'
                                    ? htmlspecialchars($spec['name_ar'])
                                    : htmlspecialchars($spec['name_en']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="password"><?php echo $lang === 'en' ? 'Password' : 'كلمة المرور'; ?></label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <div class="form-group">
                    <label
                        for="confirm_password"><?php echo $lang === 'en' ? 'Confirm Password' : 'تأكيد كلمة المرور'; ?></label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <?php echo $lang === 'en' ? 'Add Doctor' : 'إضافة طبيب'; ?>
                </button>
            </form>
        </div>
    </div>

    <!-- Edit Doctor Modal -->
    <div id="editDoctorModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('editDoctorModal').style.display='none'">&times;</span>
            <h3 style="margin-bottom: 1.5rem;"><?php echo $lang === 'en' ? 'Edit Doctor' : 'تعديل الطبيب'; ?></h3>

            <form method="POST" action="admin_panel.php?lang=<?php echo $lang; ?>">
                <input type="hidden" id="edit_user_id" name="user_id">
                <input type="hidden" name="edit_doctor">

                <div class="form-group">
                    <label for="edit_name"><?php echo $lang === 'en' ? 'Name' : 'الاسم'; ?></label>
                    <input type="text" id="edit_name" name="edit_name" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="edit_email"><?php echo $lang === 'en' ? 'Email' : 'البريد الإلكتروني'; ?></label>
                    <input type="email" id="edit_email" name="edit_email" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="edit_specialty_id"><?php echo $lang === 'en' ? 'Specialty' : 'التخصص'; ?></label>
                    <select id="edit_specialty_id" name="edit_specialty_id" class="form-control" required>
                        <option value=""><?php echo $lang === 'en' ? 'Select Specialty' : 'اختر التخصص'; ?></option>
                        <?php foreach ($specialties as $spec): ?>
                            <option value="<?php echo $spec['id']; ?>">
                                <?php echo $lang === 'ar'
                                    ? htmlspecialchars($spec['name_ar'])
                                    : htmlspecialchars($spec['name_en']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label
                        for="edit_password"><?php echo $lang === 'en' ? 'New Password (leave blank to keep current)' : 'كلمة مرور جديدة (اتركها فارغة للاحتفاظ بالحالية)'; ?></label>
                    <input type="password" id="edit_password" name="edit_password" class="form-control">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <?php echo $lang === 'en' ? 'Update Doctor' : 'تحديث الطبيب'; ?>
                </button>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p><?php echo $lang === 'en' ? '© 2025 Medical Consultation' : '© 2025 الاستشارات الطبية'; ?></p>
    </footer>

    <script src="script.js"></script>
    <script>
        function openEditModal(user) {
            document.getElementById('edit_user_id').value = user.id;
            document.getElementById('edit_name').value = user.name;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_specialty_id').value = user.specialty_id ? user.specialty_id : "";
            document.getElementById('editDoctorModal').style.display = 'block';
        }

        window.onclick = function (event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>

</html>