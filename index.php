<?php
// index.php
require_once 'auth.php';
$user = current_user();

// Если не залогинен — кидаем на login.php
if (!$user) {
    header("Location: login.php");
    exit();
}

// Параметры пользователя
$login   = htmlspecialchars($user['kasutaja'], ENT_QUOTES, 'UTF-8');
$isAdmin = (int)$user['onadmin'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Система питания детей</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="center-container">
    <h1>Система питания детей</h1>
    <p>Здравствуйте, <?= $login ?>! (<?= $isAdmin ? "Администратор" : "Родитель" ?>)</p>

    <ul class="menu-list">
        <?php if ($isAdmin === 1): ?>
            <li><a href="form_child.php"    class="main-button">Добавить ребёнка</a></li>
            <li><a href="form_mealplan.php" class="main-button">Добавить план питания</a></li>
            <li><a href="form_menu.php"     class="main-button">Добавить пункт меню</a></li>
            <li><a href="mealplan_list.php" class="main-button">Управление планами питания</a></li>
            <li><a href="logout.php"        class="main-button">Выйти</a></li>
        <?php else: ?>
            <!-- Для родителя: просто просмотр расписания (без CRUD) -->
            <li><a href="mealplan_list.php?view_only=1" class="main-button">Посмотреть расписание</a></li>
            <li><a href="logout.php"                     class="main-button">Выйти</a></li>
        <?php endif; ?>
    </ul>
</div>
</body>
</html>
