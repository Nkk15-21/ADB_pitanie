<?php
require_once 'conf.php';
require_once 'abifunktsioonid.php';

$child      = isset($_POST['child_id'])     ? (int)$_POST['child_id']     : 0;
$mealtime   = isset($_POST['mealtime_id'])  ? (int)$_POST['mealtime_id']  : 0;
$date       = isset($_POST['date'])         ? trim($_POST['date'])        : '';
$menu       = isset($_POST['menu_id'])      ? (int)$_POST['menu_id']      : 0;

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Валидация
    if ($child <= 0 || $mealtime <= 0 || $menu <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $error = 'Некорректные данные для плана питания.';
    } else {
        $success = lisaMealPlan($child, $mealtime, $date, $menu);
        if (!$success) {
            $error = 'Ошибка при добавлении плана питания. Проверьте корректность данных.';
        } else {
            header('Location: index.php?msg=mealplan_added');
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить план питания</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="form-container">
    <h2>Добавить новый план питания</h2>

    <?php if ($error !== ''): ?>
        <p class="error-message"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <form method="POST" action="add_mealplan.php">
        <div class="form-group">
            <label for="child_id">Ребёнок (ID):</label>
            <input type="number" id="child_id" name="child_id" value="<?= $child > 0 ? $child : ''; ?>" min="1" required>
        </div>
        <div class="form-group">
            <label for="mealtime_id">Приём пищи (ID):</label>
            <input type="number" id="mealtime_id" name="mealtime_id" value="<?= $mealtime > 0 ? $mealtime : ''; ?>" min="1" required>
        </div>
        <div class="form-group">
            <label for="date">Дата:</label>
            <input type="date" id="date" name="date" value="<?= htmlspecialchars($date, ENT_QUOTES, 'UTF-8'); ?>" required>
        </div>
        <div class="form-group">
            <label for="menu_id">Меню (ID):</label>
            <input type="number" id="menu_id" name="menu_id" value="<?= $menu > 0 ? $menu : ''; ?>" min="1" required>
        </div>
        <div class="form-group">
            <button type="submit">Сохранить</button>
            <a href="index.php" class="back-button">← Назад на главную</a>
        </div>
    </form>
</div>
</body>
</html>
