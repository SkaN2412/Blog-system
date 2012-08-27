<?php
if (isset($_POST['server']))
{
    try {
        $content = "[database]\nserver = \"{$_POST['server']}\"\nlogin = \"{$_POST['login']}\"\npassword = \"{$_POST['password']}\"\ndb = \"{$_POST['db']}\"";
        file_put_contents("etc/config.ini", $content);
        $DBH = new inviPDO();
        $DBH->query( file_get_contents("etc/dump.sql") );
        header("Location: ./");
    } catch ( PDOException $e ) {
        print( $e->getMessage() );
    }
}
?>
<html>
    <head>
        <title>Установка демо-версии</title>
        <meta http-equiv="Content-type" content="text/html; Charset=utf8" />
    </head>
    <body>
        <form method="post">
            <label for="server">Сервер СУБД MySQL: </label><input type="text" id="server" name="server" /><br />
            <label for="login">Логин для доступа к БД: </label><input type="text" id="login" name="login" /><br />
            <label for="password">Пароль: </label><input type="password" id="password" name="password" /><br />
            <label for="db">Имя базы данных: </label><input type="text" id="db" name="db" /><br />
            <input type="submit" value="Продолжить" />
        </form>
    </body>
</html>