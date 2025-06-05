<?php
// auth.php
global $yhendus;
session_start();
require_once 'zoneconf.php';

/**
 * Проверяет, залогинен ли пользователь. Если нет — перенаправляет на login.php.
 */
function ensure_logged_in() {
    if (empty($_SESSION['kasutaja_id'])) {
        header("Location: login.php");
        exit();
    }
}

/**
 * Возвращает информацию о текущем пользователе из таблицы kasutajad:
 *   [ 'id' => ..., 'kasutaja' => ..., 'onadmin' => ... ]
 * Если не залогинен, возвращает null.
 */
function current_user() {
    global $yhendus;
    if (empty($_SESSION['kasutaja_id'])) {
        return null;
    }
    $uid = (int)$_SESSION['kasutaja_id'];
    $stmt = $yhendus->prepare("SELECT id, kasutaja, onadmin FROM kasutajad WHERE id = ?");
    if (!$stmt) {
        return null;
    }
    $stmt->bind_param("i", $uid);
    $stmt->execute();
    $stmt->bind_result($id, $login, $onadmin);
    if ($stmt->fetch()) {
        $stmt->close();
        return [
            "id"       => $id,
            "kasutaja" => $login,
            "onadmin"  => (int)$onadmin
        ];
    } else {
        $stmt->close();
        return null;
    }
}

/**
 * Проверяет, что текущий пользователь — админ (onadmin == 1). Иначе выдаёт 403.
 */
function ensure_admin() {
    $user = current_user();
    if (!$user || $user['onadmin'] !== 1) {
        header("HTTP/1.1 403 Forbidden");
        echo "<h1>403 Forbidden</h1><p>У вас нет прав для доступа к этой странице.</p>";
        exit();
    }
}

/**
 * Проверяет, что текущий пользователь — хотя бы обычный (он залогинен, onadmin может быть 0 или 1).
 * Если не залогинен — кидает на формy входа.
 */
function ensure_parent_or_admin() {
    $user = current_user();
    if (!$user) {
        header("Location: login.php");
        exit();
    }
}

/**
 * Разлогинить и вернуть на login.php
 */
function logout_and_redirect() {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
