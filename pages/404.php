<?php
$title = "Страницы не существует";
require_once("etc".DS."start_templater.php");
if (!AJAX) include("etc".DS."header.php");
$templater->load("404");
?>
<?=$templater->parse(array()) ?>
<?php
if (!AJAX) include("etc".DS."footer.php");
?>