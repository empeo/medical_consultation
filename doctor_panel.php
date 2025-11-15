<?php
require_once 'config.php';

if (!isLoggedIn() || !hasRole('doctor')) {
    redirect('index.php');
}

$lang = isset($_GET['lang']) ? $_GET['lang'] : (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en');
$_SESSION['lang'] = $lang;

$error = '';
$success = '';

$doctor = null;
$doctor_id = $_SESSION['user_id'];

$doctor_query = "SELECT u.*, s.name_en AS specialty_name_en, s.name_ar AS specialty_name_ar
                 FROM users u
                 LEFT JOIN specialties s ON u.specialty_id = s.id
                 WHERE u.id = ?";

if ($stmt = mysqli_prepare($conn, $doctor_query)) {
    mysqli_stmt_bind_param($stmt, "i", $doctor_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result && mysqli_num_rows($result) > 0) {
        $doctor = mysqli_fetch_assoc($result);
    }
    mysqli_stmt_close($stmt);
}

if (!$doctor) {
    session_destroy();
    redirect('login.php');
}

$doctor_specialty_id = $doctor['specialty_id'] ?? null;
$doctorSpecialtyLabel = '-';

if ($doctor_specialty_id) {
    $doctorSpecialtyLabel = $lang === 'ar'
        ? ($doctor['specialty_name_ar'] ?? '-')
        : ($doctor['specialty_name_en'] ?? '-');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_id = intval($_POST['question_id']);
    $description = sanitize($_POST['description']);

    if (empty($description)) {
        $error = $_SESSION['lang'] == "ar" ? 'الوصف مطلوب' : 'Answer description is required';
    } else {
        $insert_query = "INSERT INTO answers (question_id, doctor_id, description) VALUES (?, ?, ?)";

        if ($stmt = mysqli_prepare($conn, $insert_query)) {
            $doctor_id = $_SESSION['user_id'];
            mysqli_stmt_bind_param($stmt, "iis", $question_id, $doctor_id, $description);

            if (mysqli_stmt_execute($stmt)) {
                $success = $_SESSION['lang'] == "ar" ? 'تم إضافة الإجابة بنجاح!' : 'Answer added successfully!';
                mysqli_stmt_close($stmt);
                header("Location: doctor_panel.php?lang=$lang&success=1");
                exit();
            } else {
                $error = $_SESSION['lang'] == "ar" ? 'حاول مره اخري' : 'Failed to add answer. Please try again.';
            }
        } else {
            $error = $_SESSION['lang'] == "ar" ? 'حاول مره اخري' : 'Failed to add answer. Please try again.';
        }
    }
}

if (isset($_GET['delete_answer'])) {
    $answer_id = intval($_GET['delete_answer']);
    $delete_query = "DELETE FROM answers WHERE id = ? AND doctor_id = ?";

    if ($stmt = mysqli_prepare($conn, $delete_query)) {
        $doctor_id = $_SESSION['user_id'];
        mysqli_stmt_bind_param($stmt, "ii", $answer_id, $doctor_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header("Location: doctor_panel.php?lang=$lang");
    exit();
}

if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success = $_SESSION['lang'] == "ar" ? 'الإجابة أضيفت بنجاح' : 'Answer added successfully!';
}

$questions_result = null;

if ($doctor_specialty_id) {
    $questions_query = "SELECT q.*, u.name as patient_name,
                               s.name_en, s.name_ar
                        FROM questions q
                        JOIN users u ON q.user_id = u.id
                        JOIN specialties s ON q.specialty_id = s.id
                        WHERE q.specialty_id = ?
                        ORDER BY q.created_at DESC";

    if ($stmt = mysqli_prepare($conn, $questions_query)) {
        mysqli_stmt_bind_param($stmt, "i", $doctor_specialty_id);
        mysqli_stmt_execute($stmt);
        $questions_result = mysqli_stmt_get_result($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Panel - Medical Consultation</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="<?php echo $lang === 'ar' ? 'rtl' : ''; ?>">
    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <div class="container">
        <div class="text-center mb-3">
            <div class="form-icon" style="margin: 0 auto;">🩺</div>
            <h2 class="section-title"><?php echo $lang === 'en' ? 'Doctor Panel' : 'لوحة الطبيب'; ?></h2>
            <p class="section-subtitle">
                <?php echo $lang === 'en' ? 'Specialty' : 'التخصص'; ?>:
                <span class="badge badge-primary" style="font-size: 1rem; padding: 0.5rem 1rem;">
                    <?php echo htmlspecialchars($doctorSpecialtyLabel); ?>
                </span>
            </p>
        </div>

        <?php if ($error): ?>
            <div class="error-message" style="max-width: 800px; margin: 0 auto 2rem;"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message" style="max-width: 800px; margin: 0 auto 2rem;"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Questions List -->
        <div style="max-width: 1000px; margin: 0 auto;">
            <?php if (!$doctor_specialty_id): ?>
                <div class="card text-center">
                    <p style="color: #6b7280; font-size: 1.125rem; margin: 2rem 0;">
                        <?php echo $lang === 'en'
                            ? 'No specialty set for your account. Please contact admin.'
                            : 'لا يوجد تخصص محدد لحسابك. من فضلك تواصل مع المسؤول.'; ?>
                    </p>
                </div>
            <?php elseif ($questions_result && mysqli_num_rows($questions_result) > 0): ?>
                <?php while ($question = mysqli_fetch_assoc($questions_result)): ?>
                    <?php
                    $specLabel = ($lang === 'ar')
                        ? $question['name_ar']
                        : $question['name_en'];
                    ?>
                    <div class="question-card slide-in">
                        <div class="question-header">
                            <div>
                                <h4 class="question-title"><?php echo htmlspecialchars($question['title']); ?></h4>
                                <div class="question-meta">
                                    <span style="color: #6b7280;">
                                        <?php echo $lang === 'en' ? 'By' : 'بواسطة'; ?>:
                                        <?php echo htmlspecialchars($question['patient_name']); ?>
                                    </span>
                                    <span style="margin-left: 1rem; color: #6b7280;">
                                        <?php echo date('M d, Y', strtotime($question['created_at'])); ?>
                                    </span>
                                </div>
                            </div>
                            <span class="badge badge-primary"><?php echo htmlspecialchars($specLabel); ?></span>
                        </div>

                        <p class="question-description"><?php echo nl2br(htmlspecialchars($question['description'])); ?></p>

                        <!-- Get answers for this question -->
                        <?php
                        $answers_query = "SELECT a.*, u.name as doctor_name
                                         FROM answers a 
                                         JOIN users u ON a.doctor_id = u.id 
                                         WHERE a.question_id = ? 
                                         ORDER BY a.created_at ASC";

                        $answers_result = null;
                        if ($a_stmt = mysqli_prepare($conn, $answers_query)) {
                            mysqli_stmt_bind_param($a_stmt, "i", $question['id']);
                            mysqli_stmt_execute($a_stmt);
                            $answers_result = mysqli_stmt_get_result($a_stmt);
                        }
                        ?>

                        <?php if ($answers_result && mysqli_num_rows($answers_result) > 0): ?>
                            <div class="answer-section">
                                <h5 style="margin-bottom: 1rem; color: var(--primary-color);">
                                    <?php echo $lang === 'en' ? 'Previous Answers' : 'الإجابات السابقة'; ?>
                                </h5>
                                <?php while ($answer = mysqli_fetch_assoc($answers_result)): ?>
                                    <div class="answer-item">
                                        <div style="display: flex; justify-content: space-between; align-items: start;">
                                            <div>
                                                <strong style="color: var(--primary-color);">
                                                    Dr. <?php echo htmlspecialchars($answer['doctor_name']); ?>
                                                </strong>
                                                <p style="margin: 0.5rem 0;">
                                                    <?php echo nl2br(htmlspecialchars($answer['description'])); ?>
                                                </p>
                                                <small style="color: #6b7280;">
                                                    <?php echo date('M d, Y H:i', strtotime($answer['created_at'])); ?>
                                                </small>
                                            </div>

                                            <?php if ($answer['doctor_id'] == $_SESSION['user_id']): ?>
                                                <a href="doctor_panel.php?delete_answer=<?php echo $answer['id']; ?>&lang=<?php echo $lang; ?>"
                                                    class="btn btn-danger" style="padding: 0.5rem 1rem;"
                                                    onclick="return confirmDelete('<?php echo $lang === 'en' ? 'Delete this answer?' : 'حذف هذه الإجابة؟'; ?>');">
                                                    🗑️
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php endif;
                        if (isset($a_stmt)) {
                            mysqli_stmt_close($a_stmt);
                        }
                        ?>

                        <!-- Add Answer Form -->
                        <div style="border-top: 2px solid var(--light-color); padding-top: 1.5rem; margin-top: 1.5rem;">
                            <h5 style="margin-bottom: 1rem; color: var(--dark-color);">
                                <?php echo $lang === 'en' ? 'Add Your Answer' : 'أضف إجابتك'; ?>
                            </h5>
                            <form method="POST" action="doctor_panel.php?lang=<?php echo $lang; ?>">
                                <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                                <div class="form-group">
                                    <textarea name="description" class="form-control" required rows="4"
                                        placeholder="<?php echo $lang === 'en' ? 'Write your answer here...' : 'اكتب إجابتك هنا...'; ?>"></textarea>
                                </div>
                                <button type="submit" name="add_answer" class="btn btn-primary">
                                    <?php echo $lang === 'en' ? 'Submit Answer' : 'إرسال الإجابة'; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="card text-center">
                    <p style="color: #6b7280; font-size: 1.125rem; margin: 2rem 0;">
                        <?php echo $lang === 'en'
                            ? 'No questions available for your specialty at the moment.'
                            : 'لا توجد أسئلة متاحة لتخصصك حالياً.'; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p><?php echo $lang === 'en' ? '© 2025 Medical Consultation' : '© 2025 الاستشارات الطبية'; ?></p>
    </footer>

    <script src="script.js"></script>
</body>

</html>