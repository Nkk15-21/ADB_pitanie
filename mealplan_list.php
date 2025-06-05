<?php
require_once 'conf.php';
require_once 'abifunktsioonid.php';
session_start();

global $yhendus;
$error   = '';
$success = '';

// Генерация CSRF-токена (простая реализация)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Обработка удаления (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'], $_POST['csrf_token'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = 'Неверный CSRF-токен при удалении.';
    } else {
        $deleteId = (int)$_POST['delete_id'];
        if ($deleteId > 0) {
            $stmt = $yhendus->prepare("DELETE FROM MealPlan WHERE mealplan_id = ?");
            if ($stmt) {
                $stmt->bind_param('i', $deleteId);
                if ($stmt->execute()) {
                    $success = 'План питания удалён.';
                } else {
                    $error = 'Ошибка при удалении плана питания.';
                    error_log("MySQL execute error (DELETE): " . $stmt->error);
                }
                $stmt->close();
            } else {
                $error = 'Ошибка подготовки запроса удаления.';
                error_log("MySQL prepare error (DELETE): " . $yhendus->error);
            }
        } else {
            $error = 'Некорректный ID для удаления.';
        }
    }
}

// Обработка обновления (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'], $_POST['csrf_token'], $_POST['child_id'], $_POST['mealtime_id'], $_POST['date'], $_POST['menu_id'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = 'Неверный CSRF-токен при обновлении.';
    } else {
        $updateId     = (int)$_POST['update_id'];
        $childId      = (int)$_POST['child_id'];
        $mealtimeId   = (int)$_POST['mealtime_id'];
        $date         = trim($_POST['date']);
        $menuId       = (int)$_POST['menu_id'];

        if ($updateId > 0 && $childId > 0 && $mealtimeId > 0 && $menuId > 0 && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $stmt = $yhendus->prepare("
                UPDATE MealPlan
                SET child_id = ?, mealtime_id = ?, date = ?, menu_id = ?
                WHERE mealplan_id = ?
            ");
            if ($stmt) {
                $stmt->bind_param('iisii', $childId, $mealtimeId, $date, $menuId, $updateId);
                if ($stmt->execute()) {
                    $success = 'План питания успешно обновлён.';
                } else {
                    $error = 'Ошибка при обновлении плана питания.';
                    error_log("MySQL execute error (UPDATE): " . $stmt->error);
                }
                $stmt->close();
            } else {
                $error = 'Ошибка подготовки запроса обновления.';
                error_log("MySQL prepare error (UPDATE): " . $yhendus->error);
            }
        } else {
            $error = 'Некорректные данные при обновлении.';
        }
    }
}

// Получение списков для выпадающих
$children  = kysiLasteAndmed();
$mealtimes = kysiMealtimeList();
$menus     = kysiMenuList();

// Получение всех планов питания
$plans = [];
$sql = "
    SELECT mp.mealplan_id, c.child_id, c.first_name, c.last_name, 
           mp.date, mp.mealtime_id, mp.menu_id,
           mt.type AS mealtime, m.title AS menu
    FROM MealPlan mp
    JOIN Child c       ON mp.child_id = c.child_id
    JOIN MealTime mt   ON mp.mealtime_id = mt.mealtime_id
    JOIN Menu m        ON mp.menu_id = m.menu_id
    ORDER BY mp.date DESC
";
$resPlans = $yhendus->query($sql);
if ($resPlans) {
    while ($row = $resPlans->fetch_assoc()) {
        $plans[] = $row;
    }
}
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Управление планами питания</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="page-container">
    <h2>Планы питания</h2>

    <?php if ($error !== ''): ?>
        <p class="error-message"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php elseif ($success !== ''): ?>
        <p class="success-message"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <!-- Таблица с планами питания -->
    <?php if (empty($plans)): ?>
        <p>Нет добавленных планов питания.</p>
    <?php else: ?>
        <table border="1" cellpadding="5" class="plans-table">
            <tr>
                <th>Ребёнок</th>
                <th>Дата</th>
                <th>Приём пищи</th>
                <th>Меню</th>
                <th>Действия</th>
            </tr>
            <?php foreach ($plans as $row): ?>
                <?php if ($edit_id === (int)$row['mealplan_id']): ?>
                    <!-- Форма редактирования -->
                    <tr>
                        <form method="POST" action="mealplan_list.php">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                            <input type="hidden" name="update_id" value="<?= $row['mealplan_id']; ?>">
                            <td>
                                <select name="child_id" required>
                                    <?php foreach ($children as $c): ?>
                                        <option value="<?= $c->id; ?>" <?= $c->id === (int)$row['child_id'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($c->first_name . ' ' . $c->last_name, ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <input type="date" name="date" value="<?= htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8'); ?>" required>
                            </td>
                            <td>
                                <select name="mealtime_id" required>
                                    <?php foreach ($mealtimes as $m): ?>
                                        <option value="<?= $m['mealtime_id']; ?>" <?= $m['mealtime_id'] === (int)$row['mealtime_id'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($m['type'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <select name="menu_id" required>
                                    <?php foreach ($menus as $m): ?>
                                        <option value="<?= $m['menu_id']; ?>" <?= $m['menu_id'] === (int)$row['menu_id'] ? 'selected' : ''; ?>>
                                            <?= htmlspecialchars($m['title'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <button type="submit">Сохранить</button>
                                <a href="mealplan_list.php" class="cancel-link">Отмена</a>
                            </td>
                        </form>
                    </tr>
                <?php else: ?>
                    <!-- Обычная строка -->
                    <tr>
                        <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($row['mealtime'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($row['menu'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <a href="mealplan_list.php?edit=<?= $row['mealplan_id']; ?>" class="edit-link">✎ Редактировать</a>
                            <form method="POST" action="mealplan_list.php" class="inline-form" onsubmit="return confirm('Удалить этот план?');">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="delete_id" value="<?= $row['mealplan_id']; ?>">
                                <button type="submit" class="delete-button">🗑 Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <p style="text-align: center;">
        <a href="index.php" class="back-button">← Назад на главную</a>
    </p>
</div>
</body>
</html>
