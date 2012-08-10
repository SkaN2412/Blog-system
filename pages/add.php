<?php
/**
 * Script, that generates add article form or adds article, that is given through AJAX
 * 
 * @author: Andrey "SkaN" Kamozin <andreykamozin@gmail.com>
 * 
 * @category: articles_manage
 */

// If there's get parameter called "add", add article.
if ( isset( $_GET['add'] ) )
{
    /**
     * @var: array $return includes data, that will be parsed to JSON format and returned to client 
     */
    $return = array();
    
    // Check for all data needed
    if ( ! isset( $_POST['name']) || $_POST['name'] == "" )
    {
        $return['name'] = FALSE;
    }
    
    if ( ! isset( $_POST['text'] ) || $_POST['text'] == "" )
    {
        $return['text'] = FALSE;
    }
    
    if ( ! isset( $_POST['category'] ) || $_POST['category'] == "" )
    {
        $return['category'] = FALSE;
    }
    
    // If $return is empty, there's no errors. Add article
    if ( $result == array() )
    {
        $result = article_add($_POST['name'], $_POST['text'], $_POST['category']);
        
        $return['added'] = $result;
    } else {
        // Article isn't added
        $return['added'] = FALSE;
    }
    
    // Parse $return to json and return it
    print( json_encode($result) );
    exit;
}

/*
 * Generate form
 */
// Get 1st level categories list
$categories = categories_get();

$templater = new inviTemplater( config_get("system->templatesDir") );

$params = array(
    'categories' => $categories
);

print( $templater->parse( $params ) );
?>