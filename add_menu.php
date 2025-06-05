<?php
require('conf.php');
global $yhendus;

$title = $_POST['title'];
$description = $_POST['description'];
$calories = (int)$_POST['calories'];

if ($calories < 0) {
    echo "<script>alert('Калории не могут быть отрицательными!'); window.history.back();</script>";
    exit();
}

$stmt = $yhendus->prepare("INSERT INTO Menu (title, description, calories) VALUES (?, ?, ?)");
$stmt->bind_param("ssi", $title, $description, $calories);
$stmt->execute();
?>

<script>
    alert("Новое меню успешно добавлено!");
    window.location.href = "index.php";
</script>
