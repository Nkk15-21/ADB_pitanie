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
    <form method="POST" action="add_child.php">
        <div class="form-group">
            <label for="first_name">Имя:</label>
            <input type="text" id="first_name" name="first_name" required placeholder="Введите имя">
        </div>
        <div class="form-group">
            <label for="last_name">Фамилия:</label>
            <input type="text" id="last_name" name="last_name" required placeholder="Введите фамилию">
        </div>
        <div class="form-group">
            <label for="age">Возраст:</label>
            <input type="number" id="age" name="age" min="1" required placeholder="Введите возраст">
        </div>
        <div class="form-group">
            <button type="submit">Сохранить</button>
            <a href="index.php" class="back-button">← Назад на главную</a>
        </div>
    </form>
</div>
</body>
</html>
