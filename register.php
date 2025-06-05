<?php
// register.php
require_once 'zoneconf.php';
session_start();
global $yhendus;

$error = "";

// Если уже залогинен, редиректим на index.php
if (!empty($_SESSION['kasutaja_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login        = isset($_POST['kasutaja']) ? trim($_POST['kasutaja']) : '';
    $pass1        = isset($_POST['parool'])    ? $_POST['parool']             : '';
    $pass2        = isset($_POST['parool2'])   ? $_POST['parool2']            : '';

    // Простая валидация
    if ($login === "" || $pass1 === "" || $pass2 === "") {
        $error = "Все поля обязательны для заполнения.";
    } elseif ($pass1 !== $pass2) {
        $error = "Пароли не совпадают.";
    } else {
        // Проверяем, что такого логина ещё нет
        $stmt = $yhendus->prepare("SELECT id FROM kasutajad WHERE kasutaja = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = "Это имя пользователя уже занято!";
            $stmt->close();
        } else {
            $stmt->close();
            // Хешируем пароль (та же схема, что в вашем репозитории: crypt с фиксированной «солью» 'cool')
            // (Примечание: crypt($pass1, 'cool') даёт детерминированный результат.
            // Если нужно безопаснее, можно заменить на password_hash, но ниже:
            $sool  = 'cool';
            $krypt = crypt($pass1, $sool);

            $stmt = $yhendus->prepare("
                INSERT INTO kasutajad (kasutaja, parool, onadmin)
                VALUES (?, ?, 0)
            ");
            if ($stmt) {
                $stmt->bind_param("ss", $login, $krypt);
                if ($stmt->execute()) {
                    // После успешной регистрации сразу логиним пользователя
                    $_SESSION['kasutaja_id'] = $stmt->insert_id;
                    $_SESSION['kasutaja']    = $login;
                    $_SESSION['onadmin']     = 0; // новому пользователю по-умолчанию роль «родитель»
                    $stmt->close();
                    header("Location: index.php");
                    exit();
                } else {
                    $error = "Ошибка при записи в базу – попробуйте позже.";
                }
                $stmt->close();
            } else {
                $error = "Ошибка подготовки запроса к базе.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="form-container">
    <h2>Регистрация</h2>
    <?php if ($error !== ""): ?>
        <p class="error-message"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <form method="POST" action="register.php">
        <div class="form-group">
            <label for="kasutaja">Логин:</label>
            <input type="text" id="kasutaja" name="kasutaja" value="<?= isset($login) ? htmlspecialchars($login, ENT_QUOTES, 'UTF-8') : "" ?>" required>
        </div>
        <div class="form-group">
            <label for="parool">Пароль:</label>
            <input type="password" id="parool" name="parool" required>
        </div>
        <div class="form-group">
            <label for="parool2">Повторите пароль:</label>
            <input type="password" id="parool2" name="parool2" required>
        </div>
        <div class="form-group">
            <button type="submit">Зарегистрироваться</button>
        </div>
        <p style="text-align: center;">
            Уже есть аккаунт? <a href="login.php" class="main-button">Войти</a>
        </p>
    </form>
</div>
</body>
</html>
