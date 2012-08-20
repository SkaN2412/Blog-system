<?php
if ( isset($_POST['email']) )
{
    try {
        User::register($_POST['email'], $_POST['password'], $_POST['nickname']);
        print( "Пользователь зарегистрирован!" );
    } catch ( inviException $e ) {
        print( $e->getMessage() );
    }
}
?>
<html>
    <head>
        <title>Регистрация</title>
        <meta http-equiv="Content-type" content="text/html; Charset=utf8" />
        <script src="styles/js/jquery.js"></script>
        <script>
            $(document).ready(function(){
                $("form").submit(function(){
                    if ($("#password").val() != $("#pass_confirm").val())
                    {
                        $("input[type=submit]").after("Пароли не совпадают!");
                        return false;
                    }
                });
            });
        </script>
    </head>
    <body>
        <form method="post">
            <label for="email">Email: </label><input type="text" id="email" name="email" /><br />
            <label for="password">Пароль: </label><input type="password" id="password" name="password" /><br />
            <label for="pass_confirm">Пароль еще раз: </label><input type="password" id="pass_confirm" name="pass_confirm" /><br />
            <label for="nickname">Никнейм: </label><input type="text" id="nickname" name="nickname" /><br />
            <input type="submit" value="Регистрация" />
        </form>
    </body>
</html>