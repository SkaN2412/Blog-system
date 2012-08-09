<?php
/*
 * Function 'articles_list' returns multi-dimensional array with 10 articles.
 * Parameter $pagenum is nessesary, it says for which page function should load articles. Function loads from the end
 */
function articles_list($page)
{
    //Connect to database. $DBH is DataBase Handler. About PDO you can know here: http://habrahabr.ru/post/137664/
    $DBH = new inviPDO();
    
    /*
     * Load articles for page given
     */
    //Articles are loading from the end. For example: there are 33 articles. On 1st page will be articles from 33 to 24, on 2nd page - 23-14 etc.
    $startEntry = articles_startEntry($page);
    // EPP is entries per page
    $EPP = config_get("blog->entriesPerPage");
    
    //Selecting entries
    $DBH->query("SELECT `id`, `author`, `name`, `preview`, `category` FROM `articles` WHERE `confirmed` = 1 LIMIT {$startEntry}, {$EPP} ORDER `date` DESC");
    $DBH->stmt->setFetchMode(PDO::FETCH_ASSOC);
    $clean = array();
    $judged = array();
    while ($row = $DBH->stmt->fetch())
    {
        // Load extended data
        article_extendedData($row);
        // If article is judged, put it into judged array, if not - into clean array
        switch ($row['judged'])
        {
            case 0:
                $clean[] = $row;
                break;
            case 1:
                $judged[] = $row;
                break;
        }
    }
    // Merge arrays. Now judged articles are in the end!
    $articles = array_merge($clean, $judged);
    unset($clean, $judged);
    $stmt = NULL;
    
    //Return 'em!
    return $articles;
}
/*
 * function articles_updateTop() is used by cron. It calls this function to update articles rating top for period given in $period parameter
 */
function articles_updateTop($period)
{
    
}
/*
 * function articles_updateRSS() updates rss.xml file every hour
 */
function articles_updateRSS()
{
    
}

/*
 * articles_startEntry() returns number of entry, from which you should start selecting
 */
function articles_startEntry($page)
{
    // Connect to DB
    $DBH = new inviPDO();
    
    //Watch config - how much articles per page. $EPP is Entries Per Page
    $EPP = config_get("blog->entriesPerPage");
    
    //Count all the entries in database and get {$pagenum}-last entries.
    $DBH->query( "SELECT COUNT(*) FROM `articles` WHERE `confirmed` = 1");
    $entriesNum = $DBH->fetch("num");
    $entriesNum = $entriesNum[0][0];
    
    //Articles are loading from the end. For example: there are 33 articles. On 1st page will be articles from 33 to 24, on 2nd page - 23-14 etc.
    return ( $entriesNum - ( ( $EPP * $page ) - 1) );
}
?>