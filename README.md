# 🏥 Medical Consultation Platform

<p align="center">
  <img src="https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" />
  <img src="https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white" />
  <img src="https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white" />
  <img src="https://img.shields.io/badge/Responsive_Design-00C7B7?style=for-the-badge" />
  <img src="https://img.shields.io/badge/Multilingual-EN%2FAR-4CAF50?style=for-the-badge&logo=google-translate&logoColor=white" />
  <img src="https://img.shields.io/badge/License-MIT-blue?style=for-the-badge" />
</p>

---

# 🇬🇧 English Version

## 📌 Overview  
The **Medical Consultation Platform** is a full web-based system that allows patients to submit medical questions while doctors with assigned specialties can answer them using dedicated dashboards.  
The project includes authentication, user roles, medical specialty system, full Q&A flow, multilingual UI, strong validation, and a modern responsive design.

---

## 🛠️ Technologies Used  
- **PHP (Native)**  
- **MySQL Database**  
- **HTML5 / CSS3**  
- **JavaScript (Vanilla)**  
- **Responsive Design**  
- **Password Hashing & Input Validation**  
- **Multilingual Support (EN/AR)**  

---

## 📂 Project Structure  
```
/project-root
│── index.php               # Home Page
│── login.php               # User Login
│── register.php            # Register New Account
│── logout.php              # Logout
│── profile.php             # User Profile
│── navbar.php              # Navigation Header
│── my_questions.php        # Questions by User
│── doctor_panel.php        # Doctor Dashboard
│── admin_panel.php         # Super Admin Dashboard
│── config.php              # Database Configuration
│── script.js               # Form Validation + Language System
│── style.css               # Global Styling
│── medical_consultation.sql # Database + Sample Data
```

---

## 🧬 Database Structure  
### ✔️ specialties  
Medical specialties such as:
- Cardiology  
- Dermatology  
- Pediatrics  
- Orthopedics  
…etc.

### ✔️ users  
Three main roles:
- **patient**  
- **doctor** (linked to a specialty)  
- **superadmin**

### ✔️ questions  
Table containing the user's questions.

### ✔️ answers  
Table containing doctor responses.

---

## ✨ Features  
### 🔐 Authentication  
- Secure password hashing  
- Strong password rules (6+ chars + special char)  
- Email validation  
- Form validation on frontend + backend  

### 👨‍⚕️ User Roles  
- **Super Admin:** full control  
- **Doctor:** answer questions  
- **Patient:** create questions  

### 🌐 Multilingual  
- English & Arabic  
- Save language in localStorage  
- RTL support for Arabic layout  

### 💅 Modern UI  
- Smooth animations  
- Beautiful cards & tables  
- Dark shaded sections  
- Full mobile responsiveness  

---

## 🚀 How to Run Locally  
### 1️⃣ Import Database  
Import:
```
medical_consultation.sql
```

### 2️⃣ Update Database Config  
Inside `config.php`:

```php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "medical_consultation";
```

### 3️⃣ Launch the project  
Place folder inside:
```
htdocs/
```

Open in browser:
```
http://localhost/medical-consultation/
```

---

## 👤 Test Accounts  
### 🔑 Super Admin  
```
Email: admin@medical.com
Password: 123456@
```

### 👨‍⚕️ Doctor  
```
Email: ahmed@medical.com
Password: 123456@
```

### 👤 Patient  
```
Email: john@example.com
Password: 123456@
```

---

## 📄 License  
Distributed under the MIT License.

---

# 🇸🇦 النسخة العربية

<p align="center">
  <img src="https://img.shields.io/badge/لغة_البرمجة-PHP-777BB4?style=for-the-badge&logo=php&logoColor=white" />
  <img src="https://img.shields.io/badge/قاعدة_البيانات-MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white" />
  <img src="https://img.shields.io/badge/JavaScript-مدعوم-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" />
  <img src="https://img.shields.io/badge/HTML5-مدعوم-E34F26?style=for-the-badge&logo=html5&logoColor=white" />
  <img src="https://img.shields.io/badge/CSS3-مدعوم-1572B6?style=for-the-badge&logo=css3&logoColor=white" />
  <img src="https://img.shields.io/badge/تصميم_متجاوب-نعم-00C7B7?style=for-the-badge" />
  <img src="https://img.shields.io/badge/تعدد_اللغات-EN%2FAR-4CAF50?style=for-the-badge&logo=google-translate&logoColor=white" />
  <img src="https://img.shields.io/badge/الرخصة-MIT-blue?style=for-the-badge" />
</p>

---

## 📌 نظرة عامة  
**منصة الاستشارات الطبية** هي نظام ويب كامل يتيح للمرضى إرسال الأسئلة الطبية، بينما يقوم الأطباء بالإجابة عليها من خلال لوحات تحكم مخصصة.  
يشمل النظام: تسجيل الدخول، إنشاء حسابات، نظام أدوار، تخصصات طبية، إدارة أسئلة وإجابات، واجهة متجاوبة، ودعم للغتين العربية والإنجليزية.

---

## 🛠️ التقنيات المستخدمة  
- PHP  
- MySQL  
- HTML / CSS  
- JavaScript  
- تصميم متجاوب  
- نظام لغات  
- حماية وتشفير كلمات المرور  

---

## 📂 هيكل الملفات  
(نفس الهيكل الموضح بالإنجليزي)

---

## ✨ المميزات  
### 🔐 الأمان  
- كلمة مرور قوية  
- تشفير وحماية  
- فحص بيانات المستخدم  

### 👨‍⚕️ الأدوار  
- مدير  
- طبيب  
- مريض  

### 🌐 تعدد اللغات  
- عربية / إنجليزية  
- دعم RTL  

### 💅 واجهة احترافية  
- تصميم حديث  
- تأثيرات  
- متجاوب 100%

---

## 🚀 طريقة التشغيل  
(كما في النسخة الإنجليزية)

---

## 👤 حسابات تجربة  
(كما في النسخة الإنجليزية)

---

## ✨ المطور  
GitHub: https://github.com/empeo

