<?php
try{

$article = Article::get($_GET['sid']);
$params = array(
    'id' => $article['id'],
    'name' => $article['name'],
    'preview' => $article['preview'],
    'full' => $article['full'],
    'author' => $article['author_id'],
    'category1' => $article['category1'],
    'category2' => $article['category2'],
    'category2_name' => $article['category2_name'],
    'judged' => $article['judged']
);

$templater = new inviTemplater("styles".DS."templates");
$templater->load("article");
System::out($article['name'], $templater->parse($params));

} catch ( inviException $e ) {
    print( $e->getMessage() );
}
?>