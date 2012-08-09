<?php
/*
 * Module is required for get article, vote for it and leave comments
 */
/*
 * article_get() function returns name, category, date, rating etc. data, except comments
 */
function article_get($id)
{
    $DBH->db_connect();
    
    // Get article's name, text and extended data
    $DBH->query( "SELECT `name`, `author`, `preview`, `text`, `date`, `judged`, `category` FROM `articles` WHERE `id` = :id", array( 'id' => $id ) );
    $article = $DBH->fetch();
    if ( $article == NULL )
    {
        return NULL;
    }
    $article = $article[0];
    
    // Parse date to readable view
    $article['date'] = parseDate($article['date']);
    
    // Get category name
    $article['category_name'] = article_getCategory($article['category']);
    
    // Get rating
    $article['rating'] = article_getRating($id);
    
    return $article;
}
/*
 * function article_getRating() returns rating of article
 */
function article_getRating($id)
{
    //Connect to database. $DBH is DataBase Handler. About PDO you can know here: http://habrahabr.ru/post/137664/
    $DBH = db_connect();
    
    // Get rating from database
    $DBH->query( "SELECT `good`, `bad` FROM `rating` WHERE `article` = :id", array( 'id' => $id ) );
    $rating = $DBH->fetch();
    
    //Calculate, what to return and return it.
    return ($rating[0]['good'] - $rating[0]['bad']);
}
/*
 * function article_getComments() returns comments of article for page given
 */
function article_getComments( $id, $page )
{
    $DBH = db_connect();
    
    // Calculate entry, from which should start selecting
    $CPP = config_get("commentsPerPage");
    $startEntry = ( ( $CPP * $page ) - ( $CPP - 1 ) );
    
    // Select comments calculated
    $DBH->query( "SELECT `id`, `author`, `text`, `date`, `parent` FROM `comments` WHERE `article` = :id LIMIT {$startEntry}, {$CPP}", array( 'id' => $id ) );
    $comments = $DBH->fetch();
    
    // If $DBH->fetch() returned NULL, NULL will be returned.
    return $comments;
}
/*
 * This function returns number of comments for article given
 */
function article_countComments($id)
{
    $DBH = db_connect();
    
    // Select count from DB. It simple
    $DBH->query( "SELECT COUNT(*) FROM `comments` WHERE `article` = :id", array( 'id' => $id ) );
    $result = $DBH->fetch("num");
    return $result[0][0];
}
/*
 * article_vote() is needed for changing article's rating. It returns false in case of incorrect voice, in case of non existing article or in case of MySQL error
 */
function article_vote($id, $voice)
{
    // Connect to database
    $DBH = db_connect();
    
    // Check for cookie "voted" on voter's client
    if ( ! isset($_COOKIE['voted']) )
    {
        // If it doesn't set, check for voter's data in DB
        $stmtParams = array(
            'article' => $id,
            'login' => User::get("login")
        );
        $DBH->query( "SELECT * FROM `voters` WHERE `article` = :article AND `login` = :login", $stmtParams );
        if ( $DBH->stmt->rowCount() > 0 )
        {
            return "voted";
        }
    } else {
        return "voted";
    }
    
    // Check, is the voice correct. If not, return false
    if ( ( $voice != "good" ) || ( $voice != "bad" ) )
    {
        return FALSE;
    }
    
    // Select current rating
    $DBH->query( "SELECT `{$voice}` FROM `rating` WHERE `article` = :id", array( 'id' => $id ) );
    $rating = $DBH->fetch("num");
    
    // Increase rating by one
    $rating = $rating[0][0]++;
    
    // Update rating in DB with new value
    if ( ! $DBH->query( "UPDATE `rating` SET `{$voice}` = :rating WHERE `article` = :id", array( 'rating' => $rating, 'id' => $id ) ) )
    {
        return FALSE;
    }
    
    // Insert voter's data into DB: his browser data, IP address. Also set cookie "voted" = TRUE
    $_COOKIE['voted'] = TRUE;
    $stmtParams = array(
        'article' => $id,
        'login' => User::get("login")
    );
    if ( ! $DBH->query( "INSERT INTO `voters` VALUES (:article, :login)", $stmtParams ) )
    {
        return FALSE;
    }
    
    // Return true, all is done well
    return TRUE;
}
/*
 * article_comment() is function for leaving comment. It returns false in case of non existing article
 */
function article_comment($article, $name, $text)
{
    $DBH = db_connect();
    
    // Check article for existing
    $DBH->query( "SELECT `id` FROM `articles` WHERE `id` = :id", array( 'id' => $article ) );
    if ( $DBH->stmt->rowCount() < 1 )
    {
        return FALSE;
    }
    
    // Generate date
    $date = inviDate();
    
    // Insert comment into DB
    $stmtParams = array(
        'name' => $name,
        'text' => $text,
        'date' => $date
    );
    $result = $DBH->query( "INSERT INTO `comments` (`author`, `text`, `date`) VALUES (:name, :text, :date)", $stmtParams );
    if ( ! $result )
    {
        return FALSE;
    } else {
        return TRUE;
    }
}
/*
 * function article_complain() leaves complaint on article
 */
function article_complain($article, $name, $email, $text)
{
    // Connect to database
    $DBH = db_connect();
    
    // Insert data into DB
    $stmtParams = array(
        'article' => $article,
        'name' => $name,
        'email' => $email,
        'text' => $text
    );
    $result = $DBH->query( "INSERT INTO `complaints` VALUES (:article, :name, :email, :text)", $stmtParams );
    if ( ! $result )
    {
        return FALSE;
    } else {
        return TRUE;
    }
}
/*
 * article_add() adds article
 */
function article_add($name, $text, $category)
{
    // Connect to database
    $DBH = db_execute();
    
    // Check gategory given for existing
    $DBH->query( "SELECT `id` FROM `categories` WHERE `id` = :id",  array( 'id' => $category ) );
    if ( $DBH->stmt->rowCount() < 1 )
    {
        return FALSE;
    }
    
    // Explode text to preview and main text by BB-tag [more]
    $text = explode("[more]", $text);
    
    // Insert data given and date generated into DB
    $date = inviDate();
    $stmtParams = array(
        'author' => User::get("login"),
        'name' => $name,
        'preview' => $text[0],
        'text' => $text[1],
        'category' => $category,
        'date' => $date
    );
    $result = $DBH->query( "INSERT INTO `articles` (`author`, `name`, `preview`, `text`, `category`, `date`) VALUES (:author, :name, :preview, :text, :category, :date)", $stmtParams );
    if ( ! $result )
    {
        return FALSE;
    }
    
    // Get ID of this article using date
    $DBH->query( "SELECT `id` FROM `articles` WHERE `date` = :date", array( 'date' => $date ) );
    $id = $DBH->fetch();
    $id = $id[0]['id'];
    
    // Create entry in rating table for this article
    $result = $DBH->prepare("INSERT INTO `rating` (`article`) VALUES (:id)", array( 'id' => $id ) );
    if ( ! $result )
    {
        return FALSE;
    } else {
        return TRUE;
    }
}
?>