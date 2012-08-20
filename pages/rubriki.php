<?php
if ( isset( $_GET['action'] ) )
{
switch ( $_GET['action'] )
{
    case 'getCategoriesTrace':
        $trace = Categories::trace($_GET['cat']);
        foreach ( $trace as $value )
        {
            print( Categories::name($value) . " > " );
        }
        exit;
}
}

if ( isset($_POST['name']) )
{
    try {
        Categories::add($_POST['name'], $_POST['parent']);
        print( "Рубрика добавлена!" );
    } catch ( inviException $e ) {
        print( $e->getMessage() );
    }
}

$DBH = DB::$DBH;

$DBH->query( "SELECT `id`, `name` FROM `categories`" );
$categories = $DBH->fetch();
for ($i=0; $i<count($categories); $i++)
{
    switch ($categories[$i]["id"])
    {
        case 1:
            $categories[$i]['name'] = "1-ый список";
            break;
        case 2:
            $categories[$i]['name'] = "2-ой список";
            break;
    }
}
?>
<html>
    <head>
        <title>Добавление рубрик</title>
        <meta http-equiv="Content-type" content="text/html; Charset=utf8" />
        <script src="styles/js/jquery.js"></script>
        <script>
            $(document).ready(function(){
                $("#parent, #name").change(function(){
                    $.ajax({
                        url: "?id=rubriki&action=getCategoriesTrace&cat="+$("#parent").val(),
                        dataType: "html",
                        success: function(html) {
                            $text = html + $("#name").val();
                            $("#trace").text($text);
                        }
                    });
                });
            });
        </script>
    </head>
    <body>
        <form method="post">
            <label for="name">Имя рубрики: </label><input type="text" id="name" name="name" /><br />
            <label for="parent">Родительская рубрика: </label><select id="parent" name="parent">
                <?php
                foreach ($categories as $cat)
                {
                    ?>
                <option value="<?=$cat['id'] ?>"><?=$cat['name'] ?></option>
                
                    <?php
                }
                ?>
            </select><span id="trace" name="trace"></span><br />
            <input type="submit" value="Добавить" />
        </form>
    </body>
</html>
