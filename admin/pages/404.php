<?php
$title = "Неправильный адрес";
require_once(".".DS."admin".DS."etc".DS."start_templater.php");
include(".".DS."admin".DS."etc".DS."header.php");
$templater->load("404");
?>
<?=$templater->parse(array()) ?>
<?php
include(".".DS."admin".DS."etc".DS."footer.php");
?>