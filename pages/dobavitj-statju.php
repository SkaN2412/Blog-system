<?php
try{
if ( ! User::authorized() )
{
    $templater = new inviTemplater("styles".DS."templates");
    $templater->load("article_add_unauth");
    System::out( "Авторизуйтесь для добавления!", $templater->parse(array()) );
    exit;
}

if ( isset($_GET['action']) )
{
switch ($_GET['action']) {
    case 'loadCategories':
        $categories = Categories::get($_GET['parent']);
        if ( $categories == NULL )
        {
            $categories = array();
        }
        
        print( json_encode($categories) );
        exit;
    case 'add':
        $_POST['date'] .= ":".rand(00, 59);
        Article::add($_POST['name'], $_POST['text'], $_POST['category1'], $_POST['category2'], $_POST['date']);
        $return = array(
            'success' => TRUE
        );
        
        print( json_encode($return) );
        exit;
}
}

$templater = new inviTemplater("styles".DS."templates");
$templater->load("article_add");

$params = array(
    'list1' => Categories::get(1),
    'list2' => Categories::get(2)
);

System::out( "Добавление статьи", $templater->parse($params) );
} catch ( inviException $e ) {
    $return = array(
        'success' => FALSE,
        'message' => $e->getMessage()
    );
    
    print( json_encode($return) );
    exit;
}
?>