<?php
$templater = new inviTemplater("styles".DS."templates");
$templater->load("main");
System::out( "Стартовая страница", $templater->parse(array()) );
?>
