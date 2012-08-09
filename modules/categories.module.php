<?php
/*
 * Categories module
 */
/*
 * categories_get() function return array with categories of parent given
 * parameter $parent says, which parent to load. By default function loads 1st level categories
 * Function returns FALSE in case of unexisting parent
 * Function returns NULL in case of there's no category's children in DB
 */
function categories_get( $parent = 0 )
{
    // Connect to DB
    $DBH = db_connect();
    
    // If parent isn't 0, check it for existing
    if ( $parent != 0 )
    {
        $DBH->query("SELECT `id` FROM `categories` WHERE `id` = :id", array( 'id' => $parent ) );
        if ( $DBH->stmt->rowCount() < 1 )
        {
            return FALSE;
        }
    }
    
    // Select categories IDs and names
    $DBH->query( "SELECT * FROM `categories` WHERE `parent` = :parent", array( 'parent' => $parent ) );
    
    // If no entries returned, return NULL
    if ( $DBH->stmt->rowCount() < 1 )
    {
        return NULL;
    }
    $children = $DBH->fetch();
    
    // Return children
    return $categories;
}
/*
 * function category_name() returns category's name for ID given
 */
function category_name($id)
{
    $DBH = db_connect();
    
    // Get category name. Nothing here is hard. Extended comments are not nessesary
    $DBH->query( "SELECT `name` FROM `caegories` WHERE `id` = :id", array( 'id' => $id ) );
    $name = $DBH->fetch();
    return $name[0]['name'];
}

/*
 * 
 */
?>