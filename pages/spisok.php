<?php
try {

if ( isset($_GET['page']) )
{
    $page = (int)$_GET['page'];
} else {
    $page = 1;
}

$articles = Articles::get( $page );

$templater = new inviTemplater("styles".DS."templates");
$templater->load("articles_list");
$content = $templater->parse( array( 'list' => $articles ) );

System::out("Список статей", $content);

} catch ( inviException $e ) {} catch ( PDOException $e ) {} catch ( Exception $e ) {}
?>