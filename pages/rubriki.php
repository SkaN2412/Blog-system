<?php
if ( isset( $_GET['action'] ) )
{
switch ( $_GET['action'] )
{
    case 'loadChildren':
        $children = Categories::get($_GET['cat']);
        if ( $children == NULL )
        {
            exit;
        }
        $content = "<ul>";
        foreach ($children as $category) {
            $content .= "<li><span id=\"{$category['id']}\">{$category['name']}</span></li>";
        }
        $content .= "</ul>";
        print( $content );
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
?>
<html>
    <head>
        <title>Добавление рубрик</title>
        <meta http-equiv="Content-type" content="text/html; Charset=utf8" />
        <style>
            li {
                width: 200px;
            }
            
            li:hover {
                cursor: pointer;
            }
            
            span.selected {
                background-color: #d9ea53;
            }
        </style>
        <script src="styles/js/jquery.js"></script>
        <script>
            $(document).ready(function(){
                $("li").each(function(){
                    catLoadChildren($(this).children("span").attr("id"));
                });
                
                spanListener();
                
                $("span").click(function(){
                    $("span.selected").removeClass("selected");
                    $(this).addClass("selected");
                    $("#parent").val( $(this).attr("id") );
                });
            });
            
            function spanListener() {
                $("span").click(function(){
                    $("span.selected").removeClass("selected");
                    $(this).addClass("selected");
                    $("#parent").val( $(this).attr("id") );
                });
            }
            
            function catLoadChildren(i) {
                $.ajax({
                    url: "?id=rubriki&action=loadChildren&cat="+i,
                    dataType: "html",
                    success: function(html) {
                        $("span#"+i).after(html);
                        $("span#"+i).next().children("li").each(function(){
                            catLoadChildren($(this).children("span").attr("id"));
                        });
                        $("span").unbind("click");
                        spanListener();
                    }
                });
            }
        </script>
    </head>
    <body>
        <form method="post">
            <label for="name">Имя рубрики: </label><input type="text" id="name" name="name" /><br />
            <input type="hidden" id="parent" name="parent" />
            <h3>Выберите родительскую рубрику</h3>
            <ul>
                <li>
                    <span id="1">Первый список</span>
                </li>
                <li>
                    <span id="2">Второй список</span>
                </li>
            </ul>
            <input type="submit" value="Добавить" />
        </form>
    </body>
</html>
