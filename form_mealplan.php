<?php
require_once 'auth.php';
ensure_logged_in();

$user = current_user();
// Родитель сразу перенаправляем на «только просмотр» (mealplan_list.php?view_only=1)
if ($user['onadmin'] == 0) {
    header("Location: mealplan_list.php?view_only=1");
    exit();
}
ensure_admin(); // для всякого случая — проверка, что точно админ
?>
<?php
require_once 'zoneconf.php';
require_once 'abifunktsioonid.php';

global $yhendus;

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $child_id     = isset($_POST['child_id'])     ? (int)$_POST['child_id']     : 0;
    $mealtime_id  = isset($_POST['mealtime_id'])  ? (int)$_POST['mealtime_id']  : 0;
    $date         = isset($_POST['date'])         ? trim($_POST['date'])        : '';
    $menu_id      = isset($_POST['menu_id'])      ? (int)$_POST['menu_id']      : 0;

    if ($child_id <= 0 || $mealtime_id <= 0 || $menu_id <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $error = 'Некорректные данные для плана питания.';
    } else {
        $ok = lisaMealPlan($child_id, $mealtime_id, $date, $menu_id);
        if (!$ok) {
            $error = 'Ошибка при добавлении плана питания.';
        } else {
            $success = 'Новый план питания успешно добавлен!';
        }
    }
}

$children = [];
$res = $yhendus->query("SELECT child_id, first_name, last_name FROM Child ORDER BY first_name");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $children[] = $row;
    }
}

$mealtimes = kysiMealtimeList();
$menus     = kysiMenuList();

$plans = [];
$sql = "
    SELECT c.first_name, c.last_name, mp.date, mt.type AS mealtime, m.title AS menu
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
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Список планов питания</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="form-container">
    <h2>Добавить новый план питания</h2>

    <?php if ($error !== ''): ?>
        <p class="error-message"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php elseif ($success !== ''): ?>
        <p class="success-message"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>

    <form method="POST" action="form_mealplan.php">
        <div class="form-group">
            <label for="child_id">Ребёнок:</label>
            <select id="child_id" name="child_id" required>
                <option value="">-- Выберите ребёнка --</option>
                <?php foreach ($children as $c): ?>
                    <option value="<?= $c['child_id'] ?>">
                        <?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name'], ENT_QUOTES, 'UTF-8') ?>
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
            <label for="menu_id">Пункт меню:</label>
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
            <a href="index.php" class="back-button">← Назад на главную</a>
        </div>
    </form>

    <hr>

    <h2>Существующие планы питания</h2>
    <?php if (empty($plans)): ?>
        <p>Нет ни одного плана питания.</p>
    <?php else: ?>
        <table border="1" cellpadding="5" class="plans-table">
            <tr>
                <th>Ребёнок</th>
                <th>Дата</th>
                <th>Приём пищи</th>
                <th>Меню</th>
            </tr>
            <?php foreach ($plans as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['date'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['mealtime'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($row['menu'], ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
