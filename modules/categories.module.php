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
    $DBH = new inviPDO();
    
    // Check category existing
    if ( ! category_exists($parent) )
    {
        return FALSE;
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
    $DBH = new inviPDO();
    
    // Get category name. Nothing here is hard. Extended comments are not nessesary
    $DBH->query( "SELECT `name` FROM `caegories` WHERE `id` = :id", array( 'id' => $id ) );
    $name = $DBH->fetch();
    return $name[0]['name'];
}

/*
 * category_getArticles() returns article of category. If category have children, will be returned all childen's articles
 */
function category_getArticles($id, $page = 1)
{
    // Connect to DB
    $DBH = new inviPDO();
    
    // Check category for existing
    if ( ! category_exists($id) )
    {
        return FALSE;
    }
    
    // $IDs - array with category given and its children. If no children, only with category given. You will know later, for what it's needed
    $IDs = array( $id );
    $query = "SELECT `id`, `author`, `name`, `preview`, `category` FROM `articles` WHERE `confirmed` = 1 AND `category` = ?";
    
    // Get category's children
    $children = categories_get($id);
    if ( $children != NULL )
    {
        $childrenIDs = array();
        for ($i=0; $i<count($children); $i++)
        {
            $childrenIDs[] = $children[$i]['id'];
            $query .= " AND `category` = ?";
        }
        $IDs = array_merge($IDs, $childrenIDs);
        unset($childrenIDs);
    }
    unset($children);
    
    // Calculate start entry
    $startEntry = category_startEntry($id, $page);
    
    // EPP is entries per page
    $EPP = config_get("blog->entriesPerPage");
    
    // End query
    $query .= " LIMIT {$startEntry}, {$EPP} ORDER `date` DESC";
    
    // And now get articles for all the members of array IDs
    $DBH->query( $query, $IDs );
    $articles = $DBH->fetch();
    
    // Get extended data for each article
    for ($i=0; $i<count($articles); $i++)
    {
        article_extendedData($articles[$i]);
    }
    
    return $articles;
}

/*
 * category_exists() return TRUE in case of exist or FALSE in case of not
 */
function category_exists($id)
{
    // 0 is root category, is exists
    if ( $id == 0)
    {
        return TRUE;
    }
    
    // Connect to DB
    $DBH = new inviPDO();
    
    // Check category existing
    $DBH->query( "SELECT `id` FROM `categories` WHERE `id` = :id", array( 'id' => $id ) );
    if ( $DBH->stmt->rowCount() < 1 )
    {
        return FALSE;
    } else {
        return TRUE;
    }
}

/*
 * articles_startEntry() returns number of entry, from which you should start selecting for category given
 */
function category_startEntry($category, $page)
{
    // Watch config - how much articles per page. $EPP is Entries Per Page
    $EPP = config_get("blog->entriesPerPage");
    
    $query = "SELECT COUNT(*) FROM `articles` WHERE `confirmed` = 1 AND `category` = ?";
    $IDs = array( $category );
    
    // Get category's children
    $children = categories_get($category);
    if ( $children != NULL )
    {
        $childrenIDs = array();
        for ($i=0; $i<count($children); $i++)
        {
            $childrenIDs[] = $children[$i]['id'];
            $query .= " AND `category` = ?";
        }
        $IDs = array_merge($IDs, $childrenIDs);
        unset($childrenIDs);
    }
    unset($children);
    
    // Count all the entries in database and get {$pagenum}-last entries.
    $DBH->query( $query, $IDs );
    $entriesNum = $DBH->fetch("num");
    $entriesNum = $entriesNum[0][0];
    
    // Articles are loading from the end. For example: there are 33 articles. On 1st page will be articles from 33 to 24, on 2nd page - 23-14 etc.
    return ( $entriesNum - ( ( $EPP * $page ) - 1) );
}
?>