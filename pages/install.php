<?php
if (isset($_POST['server']))
{
    try {
        $content = "[database]\nserver = \"{$_POST['server']}\"\nlogin = \"{$_POST['login']}\"\npassword = \"{$_POST['password']}\"\ndb = \"{$_POST['db']}\"\n[blog]\nentriesPerPage = 10\ncommentsPerPage = 30\n[site_data]\nname = \"{$_POST['name']}\"\ndescription = \"{$_POST['desc']}\"";
        file_put_contents("etc/config.ini", $content);
        $DBH = new inviPDO();
        header("Location: ./");
    } catch ( PDOException $e ) {
        print( $e->getMessage() );
    }
}

$templater = new inviTemplater("styles" . DS . "templates");
$templater->load("install");
print( $templater->parse(array()) );
?>