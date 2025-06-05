<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Система питания детей</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="center-container">
    <h1>Система питания детей</h1>

    <ul class="menu-list">
        <li><a href="form_child.php" class="main-button">Добавить ребёнка</a></li>
        <li><a href="form_mealplan.php" class="main-button">Добавить план питания</a></li>
        <li><a href="form_menu.php" class="main-button">Добавить пункт меню</a></li>
        <li><a href="mealplan_list.php" class="main-button">Управление планами питания</a></li>
    </ul>

    <?php if (isset($_GET['msg'])): ?>
        <p class="success-message">
            <?php
            switch ($_GET['msg']) {
                case 'child_added':
                    echo 'Ребёнок успешно добавлен!';
                    break;
                case 'mealplan_added':
                    echo 'План питания успешно добавлен!';
                    break;
                case 'menu_added':
                    echo 'Пункт меню успешно добавлен!';
                    break;
                default:
                    echo '';
            }
            ?>
        </p>
    <?php endif; ?>
</div>
</body>
</html>
