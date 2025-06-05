<?php
// mealplan_list.php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/zoneconf.php';
require_once __DIR__ . '/abifunktsioonid.php';
ensure_parent_or_admin();

$user    = current_user();
$isAdmin = (int)$user['onadmin'];
global $yhendus;

$viewOnly = false;
if ($isAdmin === 0 && isset($_GET['view_only']) && $_GET['view_only'] === '1') {
    $viewOnly = true;
}

$error   = '';
$success = '';

// Генерируем CSRF-токен (если еще не сгенерирован)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// === CRUD-операции (добавление, редактирование, удаление) доступны только администратору и только если не в режиме viewOnly ===
if ($isAdmin === 1 && !$viewOnly) {
    // --- Удаление (POST) ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'], $_POST['csrf_token'])) {
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $error = 'Неверный CSRF-токен при удалении.';
        } else {
            $deleteId = (int)$_POST['delete_id'];
            if ($deleteId > 0) {
                $stmt = $yhendus->prepare("DELETE FROM mealplan WHERE mealplan_id = ?");
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
                    $error = 'Ошибка подготовки запроса на удаление.';
                    error_log("MySQL prepare error (DELETE): " . $yhendus->error);
                }
            } else {
                $error = 'Некорректный ID для удаления.';
            }
        }
    }

    // --- Редактирование (POST) ---
    if (
        $_SERVER['REQUEST_METHOD'] === 'POST'
        && isset($_POST['update_id'], $_POST['csrf_token'], $_POST['child_id'], $_POST['mealtime_id'], $_POST['date'], $_POST['menu_id'])
    ) {
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $error = 'Неверный CSRF-токен при обновлении.';
        } else {
            $updateId   = (int)$_POST['update_id'];
            $childId    = (int)$_POST['child_id'];
            $mealtimeId = (int)$_POST['mealtime_id'];
            $date       = trim($_POST['date']);
            $menuId     = (int)$_POST['menu_id'];

            // Валидация
            if (
                $updateId > 0 &&
                $childId > 0 &&
                $mealtimeId > 0 &&
                $menuId > 0 &&
                preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
            ) {
                $stmt = $yhendus->prepare("
                    UPDATE mealplan 
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
                    $error = 'Ошибка подготовки запроса на обновление.';
                    error_log("MySQL prepare error (UPDATE): " . $yhendus->error);
                }
            } else {
                $error = 'Некорректные данные для обновления.';
            }
        }
    }

    // --- Добавление (POST) ---
    if (
        $_SERVER['REQUEST_METHOD'] === 'POST'
        && isset($_POST['add_new'], $_POST['csrf_token'], $_POST['child_id'], $_POST['mealtime_id'], $_POST['date'], $_POST['menu_id'])
    ) {
        if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $error = 'Неверный CSRF-токен при добавлении.';
        } else {
            $chId    = (int)$_POST['child_id'];
            $mtId    = (int)$_POST['mealtime_id'];
            $theDate = trim($_POST['date']);
            $mnId    = (int)$_POST['menu_id'];

            if (
                $chId > 0 &&
                $mtId > 0 &&
                $mnId > 0 &&
                preg_match('/^\d{4}-\d{2}-\d{2}$/', $theDate)
            ) {
                $stmt = $yhendus->prepare("
                    INSERT INTO mealplan (child_id, mealtime_id, date, menu_id) 
                    VALUES (?, ?, ?, ?)
                ");
                if ($stmt) {
                    $stmt->bind_param('iisi', $chId, $mtId, $theDate, $mnId);
                    if ($stmt->execute()) {
                        $success = 'Новый план питания добавлен!';
                    } else {
                        $error = 'Ошибка при добавлении плана питания.';
                        error_log("MySQL execute error (INSERT): " . $stmt->error);
                    }
                    $stmt->close();
                } else {
                    $error = 'Ошибка подготовки запроса на добавление.';
                    error_log("MySQL prepare error (INSERT): " . $yhendus->error);
                }
            } else {
                $error = 'Некорректные данные в форме добавления.';
            }
        }
    }
}


// === ПОДГОТОВКА ДАННЫХ ДЛЯ ВЫВОДА ===

// Список детей (из abifunktsioonid.php)
$children  = kysiLasteAndmed();

// Список приёмов пищи (из abifunktsioonid.php)
$mealtimes = kysiMealtimeList();

// Список пунктов меню (из abifunktsioonid.php)
$menus     = kysiMenuList();

// Список всех mealplan
$plans     = [];
$sql = "
    SELECT 
       mp.mealplan_id, 
       c.child_id, c.first_name, c.last_name, 
       mp.date, mp.mealtime_id, mp.menu_id,
       mt.type   AS mealtime, 
       m.title   AS menu
    FROM mealplan mp
      JOIN child    c ON mp.child_id    = c.child_id
      JOIN mealtime mt ON mp.mealtime_id = mt.mealtime_id
      JOIN menu      m ON mp.menu_id     = m.menu_id
    ORDER BY mp.date DESC
";
$resPlans = $yhendus->query($sql);
if ($resPlans) {
    while ($row = $resPlans->fetch_assoc()) {
        $plans[] = $row;
    }
}

// ID записи, которую сейчас редактируем (только у админа, если не в режиме viewOnly)
$edit_id = 0;
if ($isAdmin === 1 && !$viewOnly) {
    $edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
}
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

    <?php if ($error !== ""): ?>
        <p class="error-message"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php elseif ($success !== ""): ?>
        <p class="success-message"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($isAdmin === 1 && !$viewOnly): ?>
        <!-- ФОРМА ДЛЯ ДОБАВЛЕНИЯ НОВОГО ПЛАНА (только для админа) -->
        <form method="POST" action="mealplan_list.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="add_new" value="1">

            <div class="form-group">
                <label for="child_id">Ребёнок:</label>
                <select id="child_id" name="child_id" required>
                    <option value="">-- Выберите ребёнка --</option>
                    <?php foreach ($children as $c): ?>
                        <option value="<?= $c->id ?>">
                            <?= htmlspecialchars($c->first_name . ' ' . $c->last_name, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="mealtime_id">Приём пищи:</label>
                <select id="mealtime_id" name="mealtime_id" required>
                    <option value="">-- Выберите приём пищи --</option>
                    <?php foreach ($mealtimes as $m): ?>
                        <option value="<?= $m['mealtime_id'] ?>">
                            <?= htmlspecialchars($m['type'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="date">Дата:</label>
                <input type="date" id="date" name="date" required>
            </div>
            <div class="form-group">
                <label for="menu_id">Меню:</label>
                <select id="menu_id" name="menu_id" required>
                    <option value="">-- Выберите меню --</option>
                    <?php foreach ($menus as $m): ?>
                        <option value="<?= $m['menu_id'] ?>">
                            <?= htmlspecialchars($m['title'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <button type="submit">Добавить</button>
            </div>
        </form>
        <hr>
    <?php endif; ?>

    <!-- ТАБЛИЦА С ПЛАНАМИ ПИТАНИЯ -->
    <?php if (empty($plans)): ?>
        <p>Нет добавленных планов питания.</p>
    <?php else: ?>
        <table border="1" cellpadding="5" class="plans-table">
            <tr>
                <th>Ребёнок</th>
                <th>Дата</th>
                <th>Приём пищи</th>
                <th>Меню</th>
                <?php if ($isAdmin === 1 && !$viewOnly): ?>
                    <th>Действия</th>
                <?php endif; ?>
            </tr>
            <?php foreach ($plans as $row): ?>
                <?php if ($isAdmin === 1 && !$viewOnly && $edit_id === (int)$row['mealplan_id']): ?>
                    <!-- ФОРМА РЕДАКТИРОВАНИЯ (только для админа) -->
                    <tr>
                        <form method="POST" action="mealplan_list.php?edit=<?= $row['mealplan_id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="update_id" value="<?= $row['mealplan_id'] ?>">

                            <td>
                                <select name="child_id" required>
                                    <?php foreach ($children as $c): ?>
                                        <option value="<?= $c->id ?>" <?= $c->id === (int)$row['child_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($c->first_name . ' ' . $c->last_name, ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <input type="date" name="date" value="<?= htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8') ?>" required>
                            </td>
                            <td>
                                <select name="mealtime_id" required>
                                    <?php foreach ($mealtimes as $m): ?>
                                        <option value="<?= $m['mealtime_id'] ?>" <?= $m['mealtime_id'] === (int)$row['mealtime_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($m['type'], ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <select name="menu_id" required>
                                    <?php foreach ($menus as $m): ?>
                                        <option value="<?= $m['menu_id'] ?>" <?= $m['menu_id'] === (int)$row['menu_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($m['title'], ENT_QUOTES, 'UTF-8') ?>
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
                    <!-- Обычная строка (для всех пользователей; админ видит кнопки, родитель только данные) -->
                    <tr>
                        <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($row['mealtime'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($row['menu'], ENT_QUOTES, 'UTF-8') ?></td>
                        <?php if ($isAdmin === 1 && !$viewOnly): ?>
                            <td>
                                <a href="mealplan_list.php?edit=<?= $row['mealplan_id'] ?>" class="edit-link">✎</a>
                                <form method="POST" action="mealplan_list.php" class="inline-form" onsubmit="return confirm('Удалить этот план?');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
                                    <input type="hidden" name="delete_id" value="<?= $row['mealplan_id'] ?>">
                                    <button type="submit" class="delete-button">🗑</button>
                                </form>
                            </td>
                        <?php endif; ?>
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
