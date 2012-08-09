<?php
/*
 * This module is needed for static pages getting
 */
/*
 * page_get() function returns name and text of page
 */
function page_get($id)
{
    // Connect to DB
    $DBH = new inviPDO();
    
    // Select page with ID given
    $DBH->query( "SELECT * FROM `pages` WHERE `id` = :id", array( 'id' => $id ) );
    $page = $DBH->fetch();
    return $page[0];
}
?>