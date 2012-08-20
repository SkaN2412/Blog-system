<?php
if ( isset( $_GET['action'] ) )
{
switch ( $_GET['action'] )
{
    case 'getCategoriesTrace':
        $trace = Categories::trace($_GET['cat']);
        foreach ( $trace as $value )
        {
            print( Categories::name($value) );
            if ( end($trace) != $value )
            {
                print( " > " );
            }
        }
        exit;
}
}
?>
<html>
    <head>
        <title>Добавить статью</title>
        <meta http-equiv="Content-type" content="text/html; Charset=utf8" />
        <script src="styles/js/jquery.js"></script>
        <script>
            $(document).ready(function(){
                $("#category1").change(function(){
                    $.ajax({
                        url: "?id=dobavitj-statju&action=getCategoriesTrace&cat="+$("#category1").val(),
                        dataType: "html",
                        success: function(html) {
                            $("#trace").text(html);
                        }
                    });
                });
            });
        </script>
    </head>
    <body>
<?php
try {
    User::authorize();
} catch ( inviException $e ) {
    print( $e->getMessage );
    ?>
        <h1>Авторизуйтесь для добавления статьи!</h1>
        <form method="post">
            <label for="email">Email: </label><input type="text" id="email" name="email" /><br />
            <label for="password">Пароль: </label><input type="password" id="password" name="password" /><br />
            <input type="submit" value="Авторизоваться" />
        </form>
    <?php
}
$name = "";
$text = "";
$category1 = "";
$category2 = "";
$date = "";
if ( isset($_POST['name']) )
{
    try {
        Article::add($_POST['name'], $_POST['text'], $_POST['category1'], $_POST['category2'], $_POST['date']);
        print( "Статья добавлена!" );
        $name = "";
        $text = "";
        $category1 = "";
        $category2 = "";
        $date = "";
    } catch ( inviException $e ) {
        print( $e->getMessage() );
        $name = $_POST['name'];
        $text = $_POST['text'];
        $category1 = $_POST['category1'];
        $category2 = $_POST['category2'];
        $date = $_POST['date'];
    }
}

function catsRecursive($id = 1) {
    $cats = Categories::get($id);
    if ( $cats != NULL )
    {
        foreach ($cats as $one) {
            $children = catsRecursive($one['id']);
            if ($children == NULL)
            {
                continue;
            }
            $cats = array_merge($cats, $children);
        }
    }
    return $cats;
}
$categories1 = catsRecursive();
$categories2 = Categories::get(2);
?>
        <form method="post">
            <label for="name">Заголовок: </label><input type="text" id="name" name="name" value="<?=$name ?>" /><br />
            <label for="text">Текст статьи:</label><br />
            <textarea id="text" name="text" cols="60" rows="20"><?=$text ?></textarea><br />
            <label for="category1">Рубрика из 1-го списка: </label><select id="category1" name="category1">
                <?php
                foreach ($categories1 as $one) {
                    ?>
                <option value="<?=$one['id'] ?>"><?=$one['name'] ?></option>
                
                    <?php
                }
                ?>
            </select><span id="trace" name="trace"></span><br />
            <label for="category2">Рубрика из 2-го списка: </label><select id="category2" name="category2">
                <?php
                foreach ($categories2 as $one) {
                    ?>
                <option value="<?=$one['id'] ?>"><?=$one['name'] ?></option>
                
                    <?php
                }
                ?>
            </select><br />
            <label for="date">Дата в формате YYYY-MM-DD HH:MM:SS : </label><input type="text" id="date" name="date" value="<?=$date ?>" /><br />
            <input type="submit" value="Добавить" /><br />
            <p>Текст будет разбит на превью и остальной текст автоматически</p>
        </form>
    </body>
</html>
