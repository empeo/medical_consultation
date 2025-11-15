<?php
require_once 'config.php';

if (!isLoggedIn() || !hasRole('patient')) {
    redirect('index.php');
}

$lang = isset($_GET['lang']) ? $_GET['lang'] : (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en');
$_SESSION['lang'] = $lang;

$error = '';
$success = '';

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = sanitize($_POST['title']);
    $description = sanitize($_POST['description']);
    $specialty_id = isset($_POST['specialty_id']) ? intval($_POST['specialty_id']) : 0;

    if (empty($title) || empty($description) || empty($specialty_id)) {
        $error = $_SESSION['lang'] == "ar" ? 'تأكد من ملء الخانات' : 'All fields are required';
    } else {
        $insert_query = "INSERT INTO questions (user_id, title, description, specialty_id) VALUES (?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($conn, $insert_query)) {
            $user_id = $_SESSION['user_id'];
            mysqli_stmt_bind_param($stmt, "issi", $user_id, $title, $description, $specialty_id);

            if (mysqli_stmt_execute($stmt)) {
                $success = $_SESSION['lang'] == "ar" ? 'السؤال أُضيف بنجاح' : 'Question added successfully!';
                mysqli_stmt_close($stmt);
                header("Location: my_questions.php?lang=$lang&success=1");
                exit();
            } else {
                $error = $_SESSION['lang'] == "ar" ? 'حاول مره اخري هناك مشكله' : 'Failed to add question. Please try again.';
                mysqli_stmt_close($stmt);
            }
        } else {
            $error = $_SESSION['lang'] == "ar" ? 'حاول مره اخري هناك مشكله' : 'Failed to add question. Please try again.';
        }
    }
}

if (isset($_GET['delete'])) {
    $question_id = intval($_GET['delete']);

    $delete_query = "DELETE FROM questions WHERE id = ? AND user_id = ?";
    if ($stmt = mysqli_prepare($conn, $delete_query)) {
        $user_id = $_SESSION['user_id'];
        mysqli_stmt_bind_param($stmt, "ii", $question_id, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    header("Location: my_questions.php?lang=$lang");
    exit();
}

if (isset($_GET['success']) && $_GET['success'] == 1) {
    $success = $_SESSION['lang'] == "ar" ? 'السؤال أُضيف بنجاح' : 'Question added successfully!';
}

$questions_query = "SELECT q.*, 
                           u.name as patient_name,
                           s.name_en,
                           s.name_ar
                    FROM questions q
                    JOIN users u ON q.user_id = u.id
                    JOIN specialties s ON q.specialty_id = s.id
                    WHERE q.user_id = ?
                    ORDER BY q.created_at DESC";

$questions_result = null;
if ($stmt = mysqli_prepare($conn, $questions_query)) {
    $user_id = $_SESSION['user_id'];
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $questions_result = mysqli_stmt_get_result($stmt);
} else {
    $questions_result = false;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Questions - Medical Consultation</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="<?php echo $lang === 'ar' ? 'rtl' : ''; ?>">
    <?php include 'navbar.php'; ?>

    <div class="container">
        <!-- Add Question Form -->
        <div class="form-container fade-in" style="max-width: 800px;">
            <div class="form-header">
                <div class="form-icon">💬</div>
                <h2><?php echo $lang === 'en' ? 'Add New Question' : 'إضافة سؤال جديد'; ?></h2>
            </div>

            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="success-message"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?lang=' . $lang); ?>"
                id="questionForm">
                <div class="form-group">
                    <label for="title"><?php echo $lang === 'en' ? 'Title' : 'عنوان السؤال'; ?></label>
                    <input type="text" id="title" name="title" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="description"><?php echo $lang === 'en' ? 'Description' : 'وصف المشكلة'; ?></label>
                    <textarea id="description" name="description" class="form-control" required rows="5"></textarea>
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

                <button type="submit" name="add_question" class="btn btn-primary" style="width: 100%;">
                    <?php echo $lang === 'en' ? 'Submit Question' : 'إرسال السؤال'; ?>
                </button>
            </form>
        </div>

        <!-- Questions List -->
        <div class="questions-list">
            <h2 style="margin-bottom: 1rem;">
                <?php echo $lang === 'en' ? 'My Questions' : 'أسئلتي'; ?>
            </h2>

            <?php if ($questions_result && mysqli_num_rows($questions_result) > 0): ?>
                <?php while ($question = mysqli_fetch_assoc($questions_result)): ?>
                    <?php
                    $date = date('Y-m-d H:i', strtotime($question['created_at']));
                    $specLabel = ($lang === 'ar')
                        ? $question['name_ar']
                        : $question['name_en'];
                    ?>
                    <div class="question-card fade-in">
                        <div class="question-header">
                            <div>
                                <h3><?php echo htmlspecialchars($question['title']); ?></h3>
                                <div class="question-meta">
                                    <?php
                                    if ($lang === 'en') {
                                        echo "Asked on $date in " . htmlspecialchars($specLabel);
                                    } else {
                                        echo 'تم السؤال في ' . $date . ' في تخصص ' . htmlspecialchars($specLabel);
                                    }
                                    ?>
                                </div>
                            </div>

                            <div>
                                <a href="my_questions.php?delete=<?php echo $question['id']; ?>&lang=<?php echo $lang; ?>"
                                    class="btn btn-danger btn-sm" onclick="return confirmDelete('<?php echo $lang === 'en'
                                        ? 'Are you sure you want to delete this question?'
                                        : 'هل أنت متأكد أنك تريد حذف هذا السؤال؟'; ?>');">
                                    <?php echo $lang === 'en' ? 'Delete' : 'حذف'; ?>
                                </a>
                            </div>
                        </div>

                        <p class="question-description">
                            <?php echo nl2br(htmlspecialchars($question['description'])); ?>
                        </p>

                        <div class="answer-section">
                            <h4><?php echo $lang === 'en' ? 'Answers' : 'الإجابات'; ?></h4>

                            <?php
                            $answers_query = "SELECT a.*, u.name as doctor_name 
                                          FROM answers a 
                                          JOIN users u ON a.doctor_id = u.id 
                                          WHERE a.question_id = ? 
                                          ORDER BY a.created_at ASC";

                            if ($a_stmt = mysqli_prepare($conn, $answers_query)) {
                                mysqli_stmt_bind_param($a_stmt, "i", $question['id']);
                                mysqli_stmt_execute($a_stmt);
                                $answers_result = mysqli_stmt_get_result($a_stmt);

                                if ($answers_result && mysqli_num_rows($answers_result) > 0): ?>
                                    <?php while ($answer = mysqli_fetch_assoc($answers_result)): ?>
                                        <div class="answer-item">
                                            <div class="question-meta">
                                                <?php
                                                $a_date = date('Y-m-d H:i', strtotime($answer['created_at']));
                                                if ($lang === 'en') {
                                                    echo 'Answered by Dr. ' . htmlspecialchars($answer['doctor_name']) .
                                                        ' on ' . $a_date;
                                                } else {
                                                    echo 'أجاب د. ' . htmlspecialchars($answer['doctor_name']) .
                                                        ' في ' . $a_date;
                                                }
                                                ?>
                                            </div>
                                            <p class="question-description">
                                                <?php echo nl2br(htmlspecialchars($answer['description'])); ?>
                                            </p>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="question-meta">
                                        <?php echo $lang === 'en'
                                            ? 'No answers yet. A doctor will answer you soon.'
                                            : 'لا توجد إجابات بعد. سيتم الرد عليك من طبيب قريباً.'; ?>
                                    </p>
                                <?php endif;

                                mysqli_stmt_close($a_stmt);
                            }
                            ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="question-meta">
                    <?php echo $lang === 'en'
                        ? 'You have not asked any questions yet.'
                        : 'لم تقم بطرح أي أسئلة بعد.'; ?>
                </p>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <p><?php echo $lang === 'en' ? '© 2025 Medical Consultation' : '© 2025 الاستشارات الطبية'; ?></p>
    </footer>

    <script src="script.js"></script>
</body>

</html>