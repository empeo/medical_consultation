<?php
require_once 'config.php';

$lang = isset($_GET['lang']) ? $_GET['lang'] : (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en');
$_SESSION['lang'] = $lang;

$text = [
    'en' => [
        'home' => 'Home',
        'login' => 'Login',
        'register' => 'Register',
        'profile' => 'Profile',
        'myQuestion' => 'My Questions',
        'logout' => 'Logout',
        'doctorPanel' => 'Doctor Panel',
        'adminPanel' => 'Admin Panel',
        'heroTitle' => 'Your Health, Our Priority',
        'heroSubtitle' => 'Connect with expert medical professionals for personalized consultations',
        'aboutTitle' => 'Professional Medical Consultation',
        'aboutText' => 'Our platform connects patients with qualified doctors across various specialties. Get expert medical advice, ask questions, and receive personalized care from the comfort of your home.',
        'servicesTitle' => 'Our Services',
        'service1Title' => 'Expert Consultation',
        'service1Text' => 'Connect with specialized doctors',
        'service2Title' => '24/7 Support',
        'service2Text' => 'Medical guidance anytime',
        'service3Title' => 'Secure Platform',
        'service3Text' => 'Your privacy is protected',
        'service4Title' => 'Quick Response',
        'service4Text' => 'Fast answers to your questions',
        'footer' => '© 2025 Medical Consultation'
    ],
    'ar' => [
        'home' => 'الرئيسية',
        'login' => 'تسجيل الدخول',
        'register' => 'تسجيل',
        'profile' => 'الملف الشخصي',
        'myQuestion' => 'أسئلتي',
        'logout' => 'تسجيل الخروج',
        'doctorPanel' => 'لوحة الطبيب',
        'adminPanel' => 'لوحة الإدارة',
        'heroTitle' => 'صحتك، أولويتنا',
        'heroSubtitle' => 'تواصل مع أطباء محترفين للحصول على استشارات طبية مخصصة',
        'aboutTitle' => 'استشارة طبية احترافية',
        'aboutText' => 'منصتنا تربط المرضى بأطباء مؤهلين في مختلف التخصصات. احصل على نصائح طبية من خبراء، اطرح أسئلة، واحصل على رعاية شخصية من منزلك.',
        'servicesTitle' => 'خدماتنا',
        'service1Title' => 'استشارة خبراء',
        'service1Text' => 'تواصل مع أطباء متخصصين',
        'service2Title' => 'دعم على مدار الساعة',
        'service2Text' => 'إرشادات طبية في أي وقت',
        'service3Title' => 'منصة آمنة',
        'service3Text' => 'خصوصيتك محمية',
        'service4Title' => 'استجابة سريعة',
        'service4Text' => 'إجابات سريعة لأسئلتك',
        'footer' => '© 2025 الاستشارات الطبية'
    ]
];

$t = $text[$lang];
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>" <?php echo $lang === 'ar' ? 'dir="rtl"' : ''; ?>>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Consultation Platform</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="<?php echo $lang === 'ar' ? 'rtl' : ''; ?>">
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="index.php?lang=<?php echo $lang; ?>" class="navbar-logo">
                <div class="logo-icon">🏥</div>
                <span>Medical Consultant</span>
            </a>

            <ul class="navbar-menu">
                <li><a href="index.php?lang=<?php echo $lang; ?>"><?php echo $t['home']; ?></a></li>

                <?php if (!isLoggedIn()): ?>
                    <li><a href="login.php?lang=<?php echo $lang; ?>"><?php echo $t['login']; ?></a></li>
                    <li><a href="register.php?lang=<?php echo $lang; ?>"
                            class="btn btn-primary"><?php echo $t['register']; ?></a></li>
                <?php else: ?>
                    <li><a href="profile.php?lang=<?php echo $lang; ?>"><?php echo $t['profile']; ?></a></li>

                    <?php if (hasRole('patient')): ?>
                        <li><a href="my_questions.php?lang=<?php echo $lang; ?>"><?php echo $t['myQuestion']; ?></a></li>
                    <?php endif; ?>

                    <?php if (hasRole('doctor')): ?>
                        <li><a href="doctor_panel.php?lang=<?php echo $lang; ?>"><?php echo $t['doctorPanel']; ?></a></li>
                    <?php endif; ?>

                    <?php if (hasRole('superadmin')): ?>
                        <li><a href="admin_panel.php?lang=<?php echo $lang; ?>"><?php echo $t['adminPanel']; ?></a></li>
                    <?php endif; ?>

                    <li><a href="logout.php?lang=<?php echo $lang; ?>"
                            class="btn btn-danger"><?php echo $t['logout']; ?></a></li>
                <?php endif; ?>

                <li>
                    <button onclick="toggleLanguage()" class="lang-switch">
                        <?php echo $lang === 'en' ? 'العربية' : 'English'; ?>
                    </button>
                </li>
            </ul>

            <div class="navbar-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1><?php echo $t['heroTitle']; ?></h1>
            <p><?php echo $t['heroSubtitle']; ?></p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container">
        <!-- About Section -->
        <div class="text-center mb-3">
            <h2 class="section-title"><?php echo $t['aboutTitle']; ?></h2>
            <p class="section-subtitle"><?php echo $t['aboutText']; ?></p>
        </div>

        <!-- Services Section -->
        <h3 class="section-title"><?php echo $t['servicesTitle']; ?></h3>
        <div class="cards-grid">
            <div class="card">
                <div class="card-icon">🩺</div>
                <h3><?php echo $t['service1Title']; ?></h3>
                <p><?php echo $t['service1Text']; ?></p>
            </div>

            <div class="card">
                <div class="card-icon">❤️</div>
                <h3><?php echo $t['service2Title']; ?></h3>
                <p><?php echo $t['service2Text']; ?></p>
            </div>

            <div class="card">
                <div class="card-icon">🛡️</div>
                <h3><?php echo $t['service3Title']; ?></h3>
                <p><?php echo $t['service3Text']; ?></p>
            </div>

            <div class="card">
                <div class="card-icon">💬</div>
                <h3><?php echo $t['service4Title']; ?></h3>
                <p><?php echo $t['service4Text']; ?></p>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p><?php echo $t['footer']; ?></p>
    </footer>

    <script src="script.js"></script>
</body>

</html>