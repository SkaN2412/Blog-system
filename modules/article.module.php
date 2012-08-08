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
    
    // Get article name & text
    $stmt = $DBH->prepare("SELECT `name`, `author`, `preview`, `text`, `date`, `judged`, `category` FROM `articles` WHERE `id` = :id");
    $stmt->execute(array( 'id' => $id ));
    if ($stmt->rowCount() < 1)
    {
        return NULL;
    }
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $article = $stmt->fetch();
    // Parse date to readable view
    $article['date'] = parseDate($article['date']);
    // Get category name
    $article['category_name'] = article_getCategory($article['category']);
    // Get rating
    $article['rating'] = article_getRating($id);
    return $article;
}
/*
 * function article_getCategory() returns category's name of article
 */
function article_getCategory($id)
{
    $DBH = db_connect();
    
    // Get category name. Nothing here is hard. Extended comments are not nessesary
    $stmt = $DBH->prepare("SELECT `name` FROM `caegories` WHERE `id` = :id");
    $stmt->execute(array( 'id' => $id ));
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $name = $stmt->fetch();
    return $name['name'];
}
/*
 * function article_getRating() returns rating of article
 */
function article_getRating($id)
{
    //Connect to database. $DBH is DataBase Handler. About PDO you can know here: http://habrahabr.ru/post/137664/
    $DBH = db_connect();
    
    // Get rating from database
    $stmt = $DBH->prepare("SELECT `good`, `bad` FROM `rating` WHERE `article` = :id");
    $stmt->execute(array( 'id' => $id ));
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $rating = $stmt->fetch();
    //Calculate, what to return and return it.
    return ($rating['good'] - $rating['bad']);
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
    $stmt = $DBH->prepare("SELECT `id`, `author`, `text`, `date`, `parent` FROM `comments` WHERE `article` = :id LIMIT {$startEntry}, {$CPP}");
    $stmt->execute(array( 'id' => $id ));
    // If no comments, return null
    if ($stmt->rowCount() < 1)
    {
        return NULL;
    }
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $comments = array();
    // Fill $comments array with rows selected
    while ( $row = $stmt->fetch() )
    {
        $row['date'] = parseDate($row['date']);
        $comments[] = $row;
    }
    return $comments;
}
/*
 * This function returns number of comments for article given
 */
function article_countComments($id)
{
    $DBH = db_connect();
    
    // Select count from DB. It simple
    $stmt = $DBH->prepare("SELECT COUNT(*) FROM `comments` WHERE `article` = :id");
    $stmt->execute(array( 'id' => $id ));
    $stmt->setFetchMode(PDO::FETCH_NUM);
    $result = $stmt->fetch();
    return $result[0];
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
        $stmt = $DBH->prepare("SELECT * FROM `voters` WHERE `article` = :article AND `login` = :login");
        $stmtParams = array(
            'article' => $id,
            'login' => User::get("login")
        );
        $stmt->execute($stmtParams);
        if ( $stmt->rowCount() < 1 )
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
    $stmt = $DBH->prepare("SELECT `{$voice}` FROM `rating` WHERE `article` = :id");
    $stmt->execute( array( 'id' => $id ) );
    $stmt->setFetchMode(PDO::FETCH_NUM);
    $rating = $stmt->fetch();
    // Increase rating by one
    $rating = $rating[0]++;
    // Update rating in DB with new value
    $stmt = $DBH->prepare("UPDATE `rating` SET `{$voice}` = :rating WHERE `article` = :id");
    if ( ! $stmt->execute( array( 'rating' => $rating, 'id' => $id ) ) )
    {
        return FALSE;
    }
    // Insert voter's data into DB: his browser data, IP address. Also set cookie "voted" = TRUE
    $_COOKIE['voted'] = TRUE;
    $stmt = $DBH->prepare("INSERT INTO `voters` VALUES (:article, :login)");
    $stmtParams = array(
        'article' => $id,
        'login' => User::get("login")
    );
    if ( ! $stmt->execute($stmtParams) )
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
    $stmt = $DBH->prepare("SELECT `id` FROM `articles` WHERE `id` = :id");
    $stmt->execute( array( 'id' => $article ) );
    if ( $stmt->rowCount() < 1 )
    {
        return FALSE;
    }
    
    // Generate date
    $date = inviDate();
    
    // Insert comment into DB
    $stmt = $DBH->prepare("INSERT INTO `comments` (`author`, `text`, `date`) VALUES (:name, :text, :date)");
    $stmtParams = array(
        'name' => $name,
        'text' => $text,
        'date' => $date
    );
    if ( ! $stmt->execute($stmtParams) )
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
    $stmt = $DBH->prepare("INSERT INTO `complaints` VALUES (:article, :name, :email, :text)");
    $stmtParams = array(
        'article' => $article,
        'name' => $name,
        'email' => $email,
        'text' => $text
    );
    if ( ! $stmt->execute($stmtParams) )
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
    
    // Check gategory given existing
    $stmt = $DBH->prepare("SELECT `id` FROM `categories` WHERE `id` = :id");
    $stmt->execute( array( 'id' => $category ) );
    if ( $stmt->rowCount() < 1 )
    {
        return FALSE;
    }
    
    // Explode text to preview and main text by BB-tag [more]
    $text = explode("[more]", $text);
    
    // Insert data given and date generated into DB
    $date = inviDate();
    $stmt = $DBH->prepare("INSERT INTO `articles` (`author`, `name`, `preview`, `text`, `category`, `date`) VALUES (:author, :name, :preview, :text, :category, :date)");
    $stmtParams = array(
        'author' => User::get("login"),
        'name' => $name,
        'preview' => $text[0],
        'text' => $text[1],
        'category' => $category,
        'date' => $date
    );
    if ( ! $stmt->execute($stmtParams) )
    {
        return FALSE;
    }
    
    // Get ID of this article using date
    $stmt = $DBH->prepare("SELECT `id` FROM `articles` WHERE `date` = :date");
    $stmt->execute( array( 'date' => $date ) );
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $id = $stmt->fetch();
    $id = $id['id'];
    
    // Create entry in rating table for this article
    $stmt = $DBH->prepare("INSERT INTO `rating` (`article`) VALUES (:id)");
    if ( ! $stmt->execute( array( 'id' => $id ) ) )
    {
        return FALSE;
    } else {
        return TRUE;
    }
}
?>