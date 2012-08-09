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
        $stmt = $DBH->prepare("SELECT `id` FROM `categories` WHERE `id` = :id");
        $stmt->execute( array( 'id' => $parent ) );
        if ( $stmt->rowCount() < 1 )
        {
            return FALSE;
        }
    }
    
    // Select categories IDs and names
    $stmt = $DBH->prepare("SELECT * FROM `categories` WHERE `parent` = :parent");
    $stmt->execute( array( 'parent' => $parent ) );
    // If no entries returned, return NULL
    if ( $stmt->rowCount() < 1 )
    {
        return NULL;
    }
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $children = array();
    while ( $child = $stmt->fetch() )
    {
        $children[] = $child;
    }
    
    // Return children
    return $categories;
}
?>