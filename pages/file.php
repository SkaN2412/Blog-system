<?php
$_GET['name'] = str_replace("/", "", $_GET['name']);

$file = ".".DS.$_config->get("uploads_directory").DS.$_GET['name'];

if (!file_exists($file)) die("Файл не найден");

header("Content-type: ".mime_content_type($file));
header("Content-disposition: inline; filename=".basename($file));
readfile($file);
?>