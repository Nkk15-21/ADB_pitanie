<?php
require('conf.php');
require('abifunktsioonid.php');

$first = $_POST['first_name'];
$last = $_POST['last_name'];
$age = $_POST['age'];

lisaLaps($first, $last, $age);
?>
<link rel="stylesheet" href="style.css">
<script>
    alert("Ребёнок добавлен успешно!");
    window.location.href = "index.php";
</script>
