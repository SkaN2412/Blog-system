<?php
$title = "Страницы не существует";
header("HTTP/1.0 404 Not Found");
$templater = new inviTemplater(config_get("system->templatesDir"));
$templater->load("404");
?>
<?=$templater->parse(array()) ?>