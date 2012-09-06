<?php
try{
if ( isset($_GET['action']) )
{
switch ($_GET['action'])
{
    case "load":
        $rating = Article::rating($_GET['article']);
        
        $return = array(
            'rating' => $rating
        );
        
        print( json_encode($return) );
        exit;
    case "vote":
        Article::vote($_GET['article'], $_POST['voice']);
        $return = array(
            'success' => TRUE,
            'rating' => Article::rating($_GET['article'])
        );
        
        print( json_encode($return) );
        exit;
}
}
} catch ( inviException $e ) {
    switch ( $e->getCode() )
    {
        case 7:
        case 9:
            $return = array(
                'success' => FALSE,
                'message' => $e->getMessage()
            );
            
            print( json_encode($return) );
            exit;
        default:
            $return = array(
                'success' => FALSE,
                'message' => $e->getMessage()
            );
            
            print( json_encode($return) );
            exit;
    }
}
?>