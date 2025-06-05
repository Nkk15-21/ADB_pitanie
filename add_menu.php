<?php
require_once 'auth.php';
ensure_logged_in();
ensure_admin();
global $yhendus;
require_once 'zoneconf.php';

$title       = isset($_POST['title'])       ? trim($_POST['title'])       : "";
$description = isset($_POST['description']) ? trim($_POST['description']) : "";
$calories    = isset($_POST['calories'])    ? (int)$_POST['calories']     : -1;

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($title === "" || $description === "") {
        $error = "Поля Название и Описание обязательны.";
    } elseif ($calories < 0) {
        $error = "Калории не могут быть отрицательными.";
    } else {
        $stmt = $yhendus->prepare("
            INSERT INTO menu (title, description, calories)
            VALUES (?, ?, ?)
        ");
        if ($stmt) {
            $stmt->bind_param("ssi", $title, $description, $calories);
            if ($stmt->execute()) {
                header("Location: index.php?msg=menu_added");
                exit();
            } else {
                $error = "Не удалось добавить пункт меню.";
                error_log("MySQL execute error (add_menu): " . $stmt->error);
            }
            $stmt->close();
        } else {
            $error = "Ошибка подготовки запроса.";
            error_log("MySQL prepare error (add_menu): " . $yhendus->error);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавление меню</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="form-container">
    <h2>Добавить новый пункт меню</h2>
    <?php if ($error !== ""): ?>
        <p class="error-message"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <form method="POST" action="add_menu.php">
        <div class="form-group">
            <label for="title">Название:</label>
            <input type="text" id="title" name="title" value="<?= isset($title) ? htmlspecialchars($title, ENT_QUOTES, 'UTF-8') : "" ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Описание:</label>
            <input type="text" id="description" name="description" value="<?= isset($description) ? htmlspecialchars($description, ENT_QUOTES, 'UTF-8') : "" ?>" required>
        </div>
        <div class="form-group">
            <label for="calories">Калории:</label>
            <input type="number" id="calories" name="calories" value="<?= $calories >= 0 ? $calories : "" ?>" min="0" required>
        </div>
        <div class="form-group">
            <button type="submit">Добавить меню</button>
            <a href="index.php" class="back-button">← Назад на главную</a>
        </div>
    </form>
</div>
</body>
</html>
