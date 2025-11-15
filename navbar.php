<nav class="navbar">
    <div class="navbar-container">
        <a href="index.php?lang=<?php echo $lang; ?>" class="navbar-logo">
            <div class="logo-icon">🏥</div>
            <span>Medical Consultant</span>
        </a>

        <ul class="navbar-menu">
            <li><a href="index.php?lang=<?php echo $lang; ?>"><?php echo $lang === 'en' ? 'Home' : 'الرئيسية'; ?></a>
            </li>

            <?php if (!isLoggedIn()): ?>
                <li><a
                        href="login.php?lang=<?php echo $lang; ?>"><?php echo $lang === 'en' ? 'Login' : 'تسجيل الدخول'; ?></a>
                </li>
                <li><a href="register.php?lang=<?php echo $lang; ?>"
                        class="btn btn-primary"><?php echo $lang === 'en' ? 'Register' : 'تسجيل'; ?></a></li>
            <?php else: ?>
                <li><a
                        href="profile.php?lang=<?php echo $lang; ?>"><?php echo $lang === 'en' ? 'Profile' : 'الملف الشخصي'; ?></a>
                </li>

                <?php if (hasRole('patient')): ?>
                    <li><a
                            href="my_questions.php?lang=<?php echo $lang; ?>"><?php echo $lang === 'en' ? 'My Questions' : 'أسئلتي'; ?></a>
                    </li>
                <?php endif; ?>

                <?php if (hasRole('doctor')): ?>
                    <li><a
                            href="doctor_panel.php?lang=<?php echo $lang; ?>"><?php echo $lang === 'en' ? 'Doctor Panel' : 'لوحة الطبيب'; ?></a>
                    </li>
                <?php endif; ?>

                <?php if (hasRole('superadmin')): ?>
                    <li><a
                            href="admin_panel.php?lang=<?php echo $lang; ?>"><?php echo $lang === 'en' ? 'Admin Panel' : 'لوحة الإدارة'; ?></a>
                    </li>
                <?php endif; ?>

                <li><a href="logout.php?lang=<?php echo $lang; ?>"
                        class="btn btn-danger"><?php echo $lang === 'en' ? 'Logout' : 'تسجيل الخروج'; ?></a></li>
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