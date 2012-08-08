<?php
/*
 * Function 'articles_list' returns multi-dimensional array with 10 articles.
 * Parameter $pagenum is nessesary, it says for which page function should load articles. Function loads from the end
 * Parameter $filter is optional. It says which category to load. If category given does not exist, will be returned FALSE.
 */
function articles_list($page, $filter = "all")
{
    //Connect to database. $DBH is DataBase Handler. About PDO you can know here: http://habrahabr.ru/post/137664/
    $DBH = db_connect();
    
    //If $filter != "all", check category given for existing
    if ($filter != "all")
    {
        $stmt = $DBH->prepare("SELECT `id` FROM `categories` WHERE `id` = :category");
        $stmt = execute(array( 'category' => $filter ));
        //If MySQL returned empty result, return false
        if ($stmt->rowCount() == 0)
        {
            return FALSE;
        }
        $stmt = NULL;
    }
    
    /*
     * Load articles for page given
     */
    //Watch config - how much articles per page. $EPP is Entries Per Page
    $EPP = config_get("blog->entriesPerPage");
    //Count all the entries in database and get {$pagenum}-last entries.
    $query = "SELECT COUNT(*) FROM `articles` WHERE `confirmed` = 1";
    $data = array();
    if ($filter != "all")
    {
        $query .= " AND `category` = :category";
        $data['category'] = $filter;
    }
    $stmt = $DBH->prepare($query);
    $stmt->execute($data);
    $stmt->setFetchMode(PDO::FETCH_NUM);
    $entriesNum = $stmt->fetch();
    $entriesNum = $entriesNum[0];
    $stmt = NULL;
    //Articles are loading from the end. For example: there are 33 articles. On 1st page will be articles from 33 to 24, on 2nd page - 23-14 etc.
    $startEntry = ( $entriesNum - ( ( $EPP * $page ) - 1) );
    //Selecting entries
    $query = "SELECT `id`, `author`, `name`, `preview`, `category` FROM `articles` WHERE `confirmed` = 1 ";
    $data = array();
    if ($filter != "all")
    {
        $query .= "AND `category` = :category ";
        $data['category'] = $filter;
    }
    $query .= "LIMIT {$startEntry}, {$EPP}";
    $stmt = $DBH->prepare($query);
    $stmt->execute($data);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $clean = array();
    $judged = array();
    while ($row = $stmt->fetch())
    {
        // Load extended data
        $row['category_name'] = article_getCategory($row['category']);
        $row['comments_count'] = article_commentsCount($row['id']);
        $row['date'] = parseDate($row['date']);
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
?>