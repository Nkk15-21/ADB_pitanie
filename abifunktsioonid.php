<?php
require('zoneconf.php');

function kysiLasteAndmed($otsisona = '') {
    global $yhendus;
    $otsisona = addslashes(stripslashes($otsisona));
    $stmt = $yhendus->prepare("SELECT child_id, first_name, last_name, age FROM Child WHERE first_name LIKE ? OR last_name LIKE ? ORDER BY first_name");
    $like = "%$otsisona%";
    $stmt->bind_param("ss", $like, $like);
    $stmt->execute();
    $stmt->bind_result($id, $first, $last, $age);
    $result = [];
    while ($stmt->fetch()) {
        $child = new stdClass();
        $child->id = $id;
        $child->first_name = htmlspecialchars($first);
        $child->last_name = htmlspecialchars($last);
        $child->age = $age;
        $result[] = $child;
    }
    return $result;
}

function lisaLaps($first_name, $last_name, $age) {
    global $yhendus;
    $stmt = $yhendus->prepare("INSERT INTO Child (first_name, last_name, age) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $first_name, $last_name, $age);
    $stmt->execute();
}

function lisaMealPlan($child_id, $mealtime_id, $date, $menu_id) {
    global $yhendus;
    $stmt = $yhendus->prepare("INSERT INTO MealPlan (child_id, mealtime_id, date, menu_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iisi", $child_id, $mealtime_id, $date, $menu_id);
    $stmt->execute();
}
?>
