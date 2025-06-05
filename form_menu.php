<?php require('conf.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Menu</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h2>Add New Menu Item</h2>

<p style="text-align: center;">
    <a href="index.php" class="back-button">← Назад на главную</a>
</p>

<form method="POST" action="add_menu.php">
    <label>Title:</label>
    <input type="text" name="title" required>

    <label>Description:</label>
    <input type="text" name="description" required>

    <label>Calories:</label>
    <input type="number" name="calories" required min="0">


    <button type="submit">Add Menu</button>
</form>

</body>
</html>
