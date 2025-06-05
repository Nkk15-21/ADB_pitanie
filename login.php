<?php
// login.php
require_once 'zoneconf.php';
session_start();
global $yhendus;

// Если уже залогинен — сразу на index.php
if (!empty($_SESSION['kasutaja_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login  = isset($_POST['kasutaja']) ? trim($_POST['kasutaja']) : "";
    $pass   = isset($_POST['parool'])   ? $_POST['parool']           : "";

    if ($login === "" || $pass === "") {
        $error = "Введите, пожалуйста, логин и пароль.";
    } else {
        // Ищем пользователя в таблице kasutajad
        $stmt = $yhendus->prepare("SELECT id, parool, onadmin FROM kasutajad WHERE kasutaja = ?");
        if ($stmt) {
            $stmt->bind_param("s", $login);
            $stmt->execute();
            $stmt->bind_result($uid, $hashFromDb, $onadmin);
            if ($stmt->fetch()) {
                // Проверяем хеш пароля (crypt с солью 'cool')
                $krypt = crypt($pass, 'cool');
                if ($krypt === $hashFromDb) {
                    // Успешно — сохраняем в сессии
                    $_SESSION['kasutaja_id'] = $uid;
                    $_SESSION['kasutaja']    = $login;
                    $_SESSION['onadmin']     = $onadmin; // 0 или 1
                    $stmt->close();
                    header("Location: index.php");
                    exit();
                } else {
                    $error = "Неправильный пароль.";
                }
            } else {
                $error = "Пользователь не найден.";
            }
            $stmt->close();
        } else {
            $error = "Ошибка при обращении к базе.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход в систему</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="form-container">
    <h2>Вход</h2>
    <?php if ($error !== ""): ?>
        <p class="error-message"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php endif; ?>
    <form method="POST" action="login.php">
        <div class="form-group">
            <label for="kasutaja">Логин:</label>
            <input type="text" id="kasutaja" name="kasutaja" value="<?= isset($login) ? htmlspecialchars($login, ENT_QUOTES, 'UTF-8') : ""; ?>" required>
        </div>
        <div class="form-group">
            <label for="parool">Пароль:</label>
            <input type="password" id="parool" name="parool" required>
        </div>
        <div class="form-group">
            <button type="submit">Войти</button>
        </div>
        <p style="text-align: center;">
            Нет аккаунта? <a href="register.php" class="main-button">Зарегистрироваться</a>
        </p>
    </form>
</div>
</body>
</html>
