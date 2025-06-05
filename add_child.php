<?php
require_once 'conf.php';
require_once 'abifunktsioonid.php';

$first = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
$last  = isset($_POST['last_name'])  ? trim($_POST['last_name'])  : '';
$age   = isset($_POST['age'])        ? (int)$_POST['age']         : 0;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Валидация
    if ($first === '' || $last === '' || $age <= 0) {
        $error = 'Пожалуйста, заполните все поля корректно.';
    } else {
        $insertedId = lisaLaps($first, $last, $age);
        if ($insertedId === false) {
            $error = 'Ошибка при добавлении ребёнка. Попробуйте ещё раз.';
        } else {
            header('Location: index.php?msg=child_added');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить ребёнка</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="form-container">
    <h2>Добавить нового ребёнка</h2>

    <?php if ($error !== ''): ?>
        <p class="error-message"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <form method="POST" action="add_child.php">
        <div class="form-group">
            <label for="first_name">Имя:</label>
            <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($first, ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        <div class="form-group">
            <label for="last_name">Фамилия:</label>
            <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($last, ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        <div class="form-group">
            <label for="age">Возраст:</label>
            <input type="number" id="age" name="age" value="<?= $age > 0 ? $age : ''; ?>" min="1" required>
        </div>
        <div class="form-group">
            <button type="submit">Сохранить</button>
            <a href="index.php" class="back-button">← Назад на главную</a>
        </div>
    </form>
</div>
</body>
</html>
