<?php
require_once 'config.php';
$lang = isset($_GET['lang']) ? $_GET['lang'] : (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en');
session_destroy();
header("Location: index.php?lang=$lang");
exit();
?>