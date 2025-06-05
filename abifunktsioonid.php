<?php
require_once 'conf.php';

/**
 * Возвращает массив объектов с данными детей, у которых имя или фамилия содержат $otsisona.
 * @param string $otsisona
 * @return array [ {id, first_name, last_name, age}, ... ]
 */
function kysiLasteAndmed(string $otsisona = ''): array {
    global $yhendus;

    // Обрезаем пробелы и удаляем HTML-теги
    $otsisona = trim(strip_tags($otsisona));
    $like     = '%' . $otsisona . '%';

    $stmt = $yhendus->prepare("
        SELECT child_id, first_name, last_name, age
        FROM Child
        WHERE first_name LIKE ? OR last_name LIKE ?
        ORDER BY first_name
    ");
    if (!$stmt) {
        error_log("MySQL prepare error (kysiLasteAndmed): " . $yhendus->error);
        return [];
    }

    $stmt->bind_param('ss', $like, $like);
    if (!$stmt->execute()) {
        error_log("MySQL execute error (kysiLasteAndmed): " . $stmt->error);
        $stmt->close();
        return [];
    }

    $stmt->bind_result($id, $first, $last, $age);
    $result = [];
    while ($stmt->fetch()) {
        $child = new stdClass();
        $child->id         = $id;
        $child->first_name = htmlspecialchars($first, ENT_QUOTES, 'UTF-8');
        $child->last_name  = htmlspecialchars($last, ENT_QUOTES, 'UTF-8');
        $child->age        = $age;
        $result[] = $child;
    }

    $stmt->close();
    return $result;
}

/**
 * Добавляет нового ребёнка. Возвращает ID вставленного ребёнка или false при ошибке.
 * @param string $first_name
 * @param string $last_name
 * @param int    $age
 * @return int|false
 */
function lisaLaps(string $first_name, string $last_name, int $age) {
    global $yhendus;

    // Валидация
    $first_name = trim(strip_tags($first_name));
    $last_name  = trim(strip_tags($last_name));
    if ($first_name === '' || $last_name === '' || $age <= 0) {
        return false;
    }

    $stmt = $yhendus->prepare("
        INSERT INTO Child (first_name, last_name, age)
        VALUES (?, ?, ?)
    ");
    if (!$stmt) {
        error_log("MySQL prepare error (lisaLaps): " . $yhendus->error);
        return false;
    }

    $stmt->bind_param('ssi', $first_name, $last_name, $age);
    if (!$stmt->execute()) {
        error_log("MySQL execute error (lisaLaps): " . $stmt->error);
        $stmt->close();
        return false;
    }

    $insertedId = $stmt->insert_id;
    $stmt->close();
    return $insertedId;
}

/**
 * Добавляет запись в MealPlan. Возвращает true при успехе, false при ошибке.
 * @param int    $child_id
 * @param int    $mealtime_id
 * @param string $date       формат "YYYY-MM-DD"
 * @param int    $menu_id
 * @return bool
 */
function lisaMealPlan(int $child_id, int $mealtime_id, string $date, int $menu_id): bool {
    global $yhendus;

    // Валидация
    if ($child_id <= 0 || $mealtime_id <= 0 || $menu_id <= 0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return false;
    }

    $stmt = $yhendus->prepare("
        INSERT INTO MealPlan (child_id, mealtime_id, date, menu_id)
        VALUES (?, ?, ?, ?)
    ");
    if (!$stmt) {
        error_log("MySQL prepare error (lisaMealPlan): " . $yhendus->error);
        return false;
    }

    $stmt->bind_param('iisi', $child_id, $mealtime_id, $date, $menu_id);
    if (!$stmt->execute()) {
        error_log("MySQL execute error (lisaMealPlan): " . $stmt->error);
        $stmt->close();
        return false;
    }

    $stmt->close();
    return true;
}

/**
 * Возвращает список пунктов меню (Menu) как массив ассоциативных массивов.
 * @return array [ ['menu_id' => ..., 'title' => ...], ... ]
 */
function kysiMenuList(): array {
    global $yhendus;
    $result = [];
    $res = $yhendus->query("SELECT menu_id, title FROM Menu ORDER BY title");
    if (!$res) {
        error_log("MySQL query error (kysiMenuList): " . $yhendus->error);
        return [];
    }
    while ($row = $res->fetch_assoc()) {
        $result[] = $row;
    }
    return $result;
}

/**
 * Возвращает список приёмов пищи (MealTime) как массив ассоциативных массивов.
 * @return array [ ['mealtime_id' => ..., 'type' => ...], ... ]
 */
function kysiMealtimeList(): array {
    global $yhendus;
    $result = [];
    $res = $yhendus->query("SELECT mealtime_id, type FROM MealTime ORDER BY type");
    if (!$res) {
        error_log("MySQL query error (kysiMealtimeList): " . $yhendus->error);
        return [];
    }
    while ($row = $res->fetch_assoc()) {
        $result[] = $row;
    }
    return $result;
}
?>
