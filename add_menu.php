<?php
require_once 'conf.php';

$title       = isset($_POST['title'])       ? trim($_POST['title'])       : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$calories    = isset($_POST['calories'])    ? (int)$_POST['calories']     : -1;

global $yhendus;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Валидация полей
    if ($title === '' || $description === '') {
        $error = 'Пожалуйста, заполните все поля.';
    } elseif ($calories < 0) {
        $error = 'Калории не могут быть отрицательными.';
    } else {
        $stmt = $yhendus->prepare("
            INSERT INTO Menu (title, description, calories)
            VALUES (?, ?, ?)
        ");
        if (!$stmt) {
            error_log("MySQL prepare error (add_menu): " . $yhendus->error);
            $error = 'Ошибка при подготовке запроса к базе.';
        } else {
            $stmt->bind_param('ssi', $title, $description, $calories);
            if (!$stmt->execute()) {
                error_log("MySQL execute error (add_menu): " . $stmt->error);
                $error = 'Ошибка при выполнении запроса к базе.';
            }
            $stmt->close();
        }
    }

    if ($error === '') {
        header('Location: index.php?msg=menu_added');
        exit();
    }
}
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

    <?php if ($error !== ''): ?>
        <p class="error-message"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <form method="POST" action="add_menu.php">
        <div class="form-group">
            <label for="title">Название:</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Описание:</label>
            <input type="text" id="description" name="description" value="<?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        <div class="form-group">
            <label for="calories">Калории:</label>
            <input type="number" id="calories" name="calories" value="<?= $calories >= 0 ? $calories : ''; ?>" min="0" required>
        </div>
        <div class="form-group">
            <button type="submit">Добавить меню</button>
            <a href="index.php" class="back-button">← Назад на главную</a>
        </div>
    </form>
</div>
</body>
</html>
