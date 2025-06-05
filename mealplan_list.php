<?php
require('conf.php');
require('abifunktsioonid.php');

global $yhendus;
// Обработка добавления записи
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $child_id = $_POST['child_id'];
    $mealtime_id = $_POST['mealtime_id'];
    $date = $_POST['date'];
    $menu_id = $_POST['menu_id'];
    lisaMealPlan($child_id, $mealtime_id, $date, $menu_id);
    echo "<p style='color:green;'>New meal plan added!</p>";
}

// Получаем данные для выпадающих списков
$children = $yhendus->query("SELECT child_id, first_name, last_name FROM Child");
$mealtimes = $yhendus->query("SELECT mealtime_id, type FROM MealTime");
$menus = $yhendus->query("SELECT menu_id, title FROM Menu");

// Получаем все существующие MealPlan'ы
$sql = "SELECT c.first_name, c.last_name, mp.date, mt.type AS mealtime, m.title AS menu
        FROM MealPlan mp
        JOIN Child c ON mp.child_id = c.child_id
        JOIN MealTime mt ON mp.mealtime_id = mt.mealtime_id
        JOIN Menu m ON mp.menu_id = m.menu_id
        ORDER BY mp.date DESC";
$plans = $yhendus->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Meal Plan List</title>
    <link rel="stylesheet" href="style.css">

</head>
<body>

<h2>Meal Plans</h2>

<!-- ФОРМА ДОБАВЛЕНИЯ -->
<form method="POST">
    <label>Child:</label>
    <select name="child_id">
        <?php while ($c = $children->fetch_assoc()): ?>
            <option value="<?= $c['child_id'] ?>">
                <?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <label>Meal Time:</label>
    <select name="mealtime_id">
        <?php while ($m = $mealtimes->fetch_assoc()): ?>
            <option value="<?= $m['mealtime_id'] ?>"><?= htmlspecialchars($m['type']) ?></option>
        <?php endwhile; ?>
    </select>

    <label>Date:</label>
    <input type="date" name="date" required>

    <label>Menu:</label>
    <select name="menu_id">
        <?php while ($m = $menus->fetch_assoc()): ?>
            <option value="<?= $m['menu_id'] ?>"><?= htmlspecialchars($m['title']) ?></option>
        <?php endwhile; ?>
    </select>

    <button type="submit">Add</button>
</form>

<hr>

<!-- ТАБЛИЦА -->
<table border="1" cellpadding="5">
    <tr>
        <th>Child</th>
        <th>Date</th>
        <th>Meal Time</th>
        <th>Menu</th>
    </tr>
    <?php while ($row = $plans->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
            <td><?= $row['date'] ?></td>
            <td><?= htmlspecialchars($row['mealtime']) ?></td>
            <td><?= htmlspecialchars($row['menu']) ?></td>
        </tr>
    <?php endwhile; ?>
</table>

</body>
</html>
