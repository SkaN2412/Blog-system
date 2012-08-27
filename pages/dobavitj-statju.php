<html>
    <head>
        <title>Добавить статью</title>
        <meta http-equiv="Content-type" content="text/html; Charset=utf8" />
        <style>
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
                $("ul").each(function(){
                    switch ($(this).attr("id")) {
                        case "list1":
                            $.ajax({
                                url: "?id=rubriki&action=loadChildren&cat=1",
                                dataType: "html",
                                success: function(html) {
                                    $("ul#list1").append(html);

                                    $("ul#list1").children("ul").children("li").each(function(){
                                        catLoadChildren($(this).children("span").attr("id"));
                                    });
                                    $("span").unbind("click");
                                    spanListener();
                                }
                            });
                            break;
                        case "list2":
                            $.ajax({
                                url: "?id=rubriki&action=loadChildren&cat=2",
                                dataType: "html",
                                success: function(html) {
                                    $("ul#list2").append(html);
                                }
                            });
                            break;
                    }
                });
            });
            
            function spanListener() {
                $("span").click(function(){
                    if ($(this).parents("#list1").length > 0) {
                        $("#list1 span.selected").removeClass("selected");
                        $(this).addClass("selected");
                        $("#category1").val( $(this).attr("id") );
                        alert($("#category1").val());
                    }
                    if ($(this).parents("#list2").length > 0) {
                        $("#list2 span.selected").removeClass("selected");
                        $(this).addClass("selected");
                        $("#category2").val( $(this).attr("id") );
                        alert($("#category2").val());
                    }
                });
            }
            
            function catLoadChildren(i) {
                $.ajax({
                    url: "?id=rubriki&action=loadChildren&cat="+i,
                    dataType: "html",
                    success: function(html) {
                        if (i==1 || i==2){
                            $("ul#list"+i).append(html);
                        } else {
                            $("span#"+i).after(html);
                        }
                        
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
    exit;
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
?>
        <form method="post">
            <label for="name">Заголовок: </label><input type="text" id="name" name="name" value="<?=$name ?>" /><br />
            <label for="text">Текст статьи:</label><br />
            <textarea id="text" name="text" cols="60" rows="20"><?=$text ?></textarea><br />
            <input type="hidden" id="category1" name="category1" /><input type="hidden" id="category2" name="category2" />
            <h3>Рубрика из первого списка: </h3>
            <ul id="list1" name="list1">
                
            </ul><br />
            <h3>Рубрика из 2-го списка: </h3>
            <ul id="list2" name="list2">
                
            </ul><br />
            <label for="date">Дата в формате YYYY-MM-DD HH:MM:SS : </label><input type="text" id="date" name="date" value="<?=$date ?>" /><br />
            <input type="submit" value="Добавить" /><br />
            <p>Текст будет разбит на превью и остальной текст автоматически</p>
        </form>
    </body>
</html>
