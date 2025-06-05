<?php
require_once 'auth.php';
ensure_logged_in();
ensure_admin();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить пункт меню</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="form-container">
    <h2>Добавить новый пункт меню</h2>
    <form method="POST" action="add_menu.php">
        <div class="form-group">
            <label for="title">Название:</label>
            <input type="text" id="title" name="title" required placeholder="Введите название">
        </div>
        <div class="form-group">
            <label for="description">Описание:</label>
            <input type="text" id="description" name="description" required placeholder="Введите описание">
        </div>
        <div class="form-group">
            <label for="calories">Калории:</label>
            <input type="number" id="calories" name="calories" min="0" required placeholder="Введите калории">
        </div>
        <div class="form-group">
            <button type="submit">Добавить меню</button>
            <a href="index.php" class="back-button">← Назад на главную</a>
        </div>
    </form>
</div>
</body>
</html>
