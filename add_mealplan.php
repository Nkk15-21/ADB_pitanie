<?php
require('conf.php');
require('abifunktsioonid.php');

$child = $_POST['child_id'];
$mealtime = $_POST['mealtime_id'];
$date = $_POST['date'];
$menu = $_POST['menu_id'];

lisaMealPlan($child, $mealtime, $date, $menu);
?>
<link rel="stylesheet" href="style.css">

<script>
    alert("План питания добавлен успешно!");
    window.location.href = "index.php";
</script>
