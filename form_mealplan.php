<?php
require('conf.php');
require('abifunktsioonid.php');
global $yhendus;


// Удаление
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $yhendus->prepare("DELETE FROM MealPlan WHERE mealplan_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo "<p style='color:red;'> Meal plan deleted.</p>";
}

// Обновление
if (isset($_POST['update_id'])) {
    $stmt = $yhendus->prepare("UPDATE MealPlan SET child_id=?, mealtime_id=?, date=?, menu_id=? WHERE mealplan_id=?");
    $stmt->bind_param("iisii", $_POST['child_id'], $_POST['mealtime_id'], $_POST['date'], $_POST['menu_id'], $_POST['update_id']);
    $stmt->execute();
    echo "<script>alert(' Meal plan updated successfully!'); window.location.href = 'index.php';</script>";
    exit();
}



// Добавление
if (isset($_POST['add_new'])) {
    lisaMealPlan($_POST['child_id'], $_POST['mealtime_id'], $_POST['date'], $_POST['menu_id']);
    echo "<script>alert(' Meal plan added successfully!'); window.location.href = 'index.php';</script>";
    exit();
}



// Выпадающие списки
$children = $yhendus->query("SELECT child_id, first_name, last_name FROM child");
$mealtimes = $yhendus->query("SELECT mealtime_id, type FROM mealtime");
$menus = $yhendus->query("SELECT menu_id, title FROM menu");

// Данные таблицы
$sql = "SELECT mp.mealplan_id, c.first_name, c.last_name, mp.child_id, mp.date, mp.mealtime_id, mp.menu_id,
               mt.type AS mealtime, m.title AS menu
        FROM mealplan mp
        JOIN child c ON mp.child_id = c.child_id
        JOIN mealtime mt ON mp.mealtime_id = mt.mealtime_id
        JOIN menu m ON mp.menu_id = m.menu_id
        ORDER BY mp.date DESC";

$plans = $yhendus->query($sql);

// Для редактирования одной строки
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="style.css">
    <meta charset="UTF-8">
    <title>Meal Plan Management</title>
</head>
<body>

<h2> Meal Plans</h2>

<!-- Добавление новой записи -->
<form method="POST">
    <input type="hidden" name="add_new" value="1">
    <label>Child:</label>
    <select name="child_id">
        <?php $res = $yhendus->query("SELECT child_id, first_name, last_name FROM Child");
        while ($c = $res->fetch_assoc()): ?>
            <option value="<?= $c['child_id'] ?>"><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?></option>
        <?php endwhile; ?>
    </select>

    <label>Meal Time:</label>
    <select name="mealtime_id">
        <?php $res = $yhendus->query("SELECT mealtime_id, type FROM MealTime");
        while ($m = $res->fetch_assoc()): ?>
            <option value="<?= $m['mealtime_id'] ?>"><?= htmlspecialchars($m['type']) ?></option>
        <?php endwhile; ?>
    </select>

    <label>Date:</label>
    <input type="date" name="date" required>

    <label>Menu:</label>
    <select name="menu_id">
        <?php $res = $yhendus->query("SELECT menu_id, title FROM Menu");
        while ($m = $res->fetch_assoc()): ?>
            <option value="<?= $m['menu_id'] ?>"><?= htmlspecialchars($m['title']) ?></option>
        <?php endwhile; ?>
    </select>

    <button type="submit"> Add</button>
</form>

<hr>

<table border="1" cellpadding="5">
    <tr>
        <th>Child</th>
        <th>Date</th>
        <th>Meal Time</th>
        <th>Menu</th>
        <th>Action</th>
    </tr>
    <?php while ($row = $plans->fetch_assoc()): ?>
        <?php if ($edit_id === (int)$row['mealplan_id']): ?>
            <!-- Форма редактирования -->
            <tr>
                <form method="POST">
                    <input type="hidden" name="update_id" value="<?= $row['mealplan_id'] ?>">
                    <td>
                        <select name="child_id">
                            <?php $res = $yhendus->query("SELECT child_id, first_name, last_name FROM Child");
                            while ($c = $res->fetch_assoc()): ?>
                                <option value="<?= $c['child_id'] ?>" <?= $c['child_id'] == $row['child_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </td>
                    <td><input type="date" name="date" value="<?= $row['date'] ?>" required></td>
                    <td>
                        <select name="mealtime_id">
                            <?php $res = $yhendus->query("SELECT mealtime_id, type FROM MealTime");
                            while ($m = $res->fetch_assoc()): ?>
                                <option value="<?= $m['mealtime_id'] ?>" <?= $m['mealtime_id'] == $row['mealtime_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['type']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </td>
                    <td>
                        <select name="menu_id">
                            <?php $res = $yhendus->query("SELECT menu_id, title FROM Menu");
                            while ($m = $res->fetch_assoc()): ?>
                                <option value="<?= $m['menu_id'] ?>" <?= $m['menu_id'] == $row['menu_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['title']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </td>
                    <td><button type="submit">Save</button></td>
                </form>
            </tr>
        <?php else: ?>
            <!-- Обычная строка -->
            <tr>
                <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                <td><?= $row['date'] ?></td>
                <td><?= htmlspecialchars($row['mealtime']) ?></td>
                <td><?= htmlspecialchars($row['menu']) ?></td>
                <td>
                    <a href="?edit=<?= $row['mealplan_id'] ?>">Edit</a> |
                    <a href="?delete=<?= $row['mealplan_id'] ?>" onclick="return confirm('Delete this meal plan?')">Delete</a>
                </td>
            </tr>
        <?php endif; ?>
    <?php endwhile; ?>
</table>
<p style="text-align: center;">
    <a href="index.php" class="back-button">← Назад на главную</a>
</p>
</body>
</html>
