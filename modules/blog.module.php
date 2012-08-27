<?php
class Articles {
    /**
     * Function gets articles from DB
     * 
     * @param int $page [optional] Number of navigation page
     * @param int $category [optional] ID of category
     * 
     * @return array Multi-dimensional array with articles
     */
    public static function get($page = 1, $category = NULL)
    {
        // Connect to DB
        $DBH = DB::$DBH;
        
        // Get entry from which should start selecting
        $startEntry = self::startEntry($page, $category);
        // Get number of entries per page
        $EPP = config_get("blog/entriesPerPage");
        
        // Select entries
        $query = "SELECT `id`, `author_id`, `name`, `preview`, `date`, `category1`, `category2`, `good_voices`, `bad_voices`, `judged` FROM `articles` WHERE `";
        // TODO: end method...
    }
    
    /**
     * Function generates navigation links
     * 
     * @param int $page Number of navigation page
     * 
     * @return string HTML code with navigaiton links
     */
    public static function navigation($page)
    {
        
    }
    
    private static function startEntry($page, $category = NULL)
    {
        // Connect to DB
        $DBH = DB::$DBH;

        //Watch config - how much articles per page. $EPP is Entries Per Page
        $EPP = config_get("blog/entriesPerPage");

        //Count all the entries in database and get {$pagenum}-last entries.
        $query = "SELECT COUNT(*) FROM `articles` WHERE `confirmed` = 1";
        $params = array();
        if ( $category != NULL )
        {
            // Check category existing
            if ( ! Categories::exists($category) )
            {
                throw new inviException(100, "Unexisting category given");
            }
            
            switch ( Categories::fromList($category) )
            {
                case 1:
                    $query .= " AND `category1` = :category";
                    break;
                case 2:
                    $query .= " AND `category2` = :category";
                    break;
            }
            
            $params['category'] = $category;
        }
        
        $DBH->query( $query, $params );
        $entriesNum = $DBH->fetch("num");
        $entriesNum = $entriesNum[0][0];

        //Articles are loading from the end. For example: there are 33 articles. On 1st page will be articles from 33 to 24, on 2nd page - 23-14 etc.
        return ( $entriesNum - ( ( $EPP * $page ) - 1) );
    }
}

class Article {
    /**
     * Method gets name, text, rating and category trace of article
     * 
     * @param int $id ID of article
     * 
     * @return array with article data
     */
    public static function get($id)
    {
        
    }
    
    /**
     * Method gets rating of article. If you give it type, it will return number of voices in type given
     * 
     * @param int $id ID of article
     * @param string $type [optional] Type of voices. Should be "good" or "bad"
     * 
     * @return int Rating
     */
    public static function rating($id, $type = NULL)
    {
        
    }
    
    /**
     * Method changes article rating in the DB
     * 
     * @param int $id ID of article
     * @param string $type Type of voice. Should be good or bad
     * 
     * @return void
     */
    public static function vote($id, $type)
    {
        
    }
    
    /**
     * Method inserts article into DB
     * 
     * @param string $name Name of article
     * @param string $text Article's text
     * @param int $category1 ID of category from 1st list
     * @param int $category2 ID of category from 2nd list
     * 
     * @return void
     */
    public static function add($name, $text, $category1, $category2, $date)
    {
        // Explode text to preview and text
        $index = 128;
        while ( substr($text, $index, 1) != " " )
        {
            $index--;
        }
        $preview = substr($text, 0, $index);
        $full = substr($text, $index);
        unset($text);
        
        // Check categories' existing
        if ( ! Categories::exists($category1) || ! Categories::exists($category2) )
        {
            throw new inviException(1001, "Category given does not exist");
        }
        
        // All's right, insert article
        $DBH = DB::$DBH;
        
        $author = User::get();
        $author = $author['id'];
        $params = array(
            'author' => $author,
            'name' => $name,
            'preview' => $preview,
            'full' => $full,
            'category1' => $category1,
            'category2' => $category2,
            'date' => $date
        );
        
        $DBH->query( "INSERT INTO `articles` (`author_id`, `name`, `preview`, `full`, `category1`, `category2`, `date`) VALUES (:author, :name, :preview, :full, :category1, :category2, :date)", $params );
        if ( $DBH->stmt->rowCount() < 1 )
        {
            throw new inviException(102, "MySQL error");
        }
    }
    
    /**
     * Method inserts complaint into DB
     * 
     * @param int $article ID of article
     * @param string $name Complainer's name
     * @param string $email Complainer's name
     * @param string $text Comaplint's text
     * 
     * @return void
     */
    public static function complain($article, $name, $email, $text)
    {
        
    }
}

class Comments {
    /**
     * Method counts comments in article given
     * 
     * @param int $article ID of article
     * 
     * @return int Number of comments
     */
    public static function count($article)
    {
        
    }
    
    /**
     * Method gets comments for this article and this navigation page
     * 
     * @param int $article ID of article
     * @param int $page Number of navigation page
     * 
     * @return array Multi-dimensional array with comments
     */
    public static function get($article, $page)
    {
        
    }
    
    /**
     * Method inserts comment into DB
     * 
     * @param int $article ID of article
     * @param string $name Commentator's name
     * @param string $text Text of comment
     * 
     * @return void
     */
    public static function send($article, $name, $text)
    {
        
    }
}

class Categories {
    /**
     * Method gets categories for parent given from DB. $parent can't be 0
     * 
     * @param int $parent ID of parent category
     * 
     * @return array Multi-dimensional array with categories
     */
    public static function get($parent)
    {
        // Connect to DB
        $DBH = DB::$DBH;
        
        if ( $parent == 0 )
        {
            throw new inviException(101, "Nothing will be returned");
        }
        
        // If parent isn't 1st or 2nd list, check it for existing
        if ( $parent != 1 && $parent != 2 )
        {
            $DBH->query( "SELECT `id` FROM `categories` WHERE `id` = :id", array( 'id' => $parent ) );
            if ( $DBH->stmt->rowCount() < 1 )
            {
                throw new inviException(101, "Unexisting category given");
            }
        }
        
        // Select children
        $DBH->query( "SELECT `id`, `name` FROM `categories` WHERE `parent` = :id", array( 'id' => $parent ) );
        $categories = $DBH->fetch();
        
        // If MySQL returned nothing, return NULL
        return $categories;
    }
    
    /**
     * Method gets categories trace for article given
     * 
     * @param int $article ID of article
     * 
     * @return array Array with trace
     */
    public static function trace($id)
    {
        $id = (int)$id;
        // Connect to DB
        $DBH = DB::$DBH;
        
        // Select all parents of category
        $trace = array($id);
        while ( $id != 1 && $id != 2 )
        {
            $DBH->query( "SELECT `parent` FROM `categories` WHERE `id` = :id", array( 'id' => $id ) );
            $id = $DBH->fetch("num");
            $trace[] = $id = (int)$id[0][0];
        }
        
        krsort($trace);
        return $trace;
    }
    
    public static function fromList($id)
    {
        // If $id is 0, 1 or 2, throw exception
        if ( $id == 0 || $id == 1 || $id == 2 || ! self::exists($id) )
        {
            throw inviException(103, "Unexisting category given");
        }
            
        // Connect to DB
        $DBH = DB::$DBH;
        
        // Select parent of category
        $DBH->query( "SELECT `parent` FROM `categories` WHERE `id` = :id", array( 'id' => $id ) );
        $parent = $DBH->fetch();
        $parent = $parent[0]['parent'];
        
        // If parent is 2, category is from 2nd list, if not - from 1st
        if ( $parent == 2 )
        {
            return 2;
        } else {
            return 1;
        }
    }
    
    /**
     * Method gets category's name from DB
     * 
     * @param int $id Id of category
     * 
     * @return string Name of category
     */
    public static function name($id)
    {
        
        // If ID is 1 or 2, return "1-ый список" or "2-ой список"
        switch ($id)
        {
            case 1:
                return "1-ый список";
                break;
            case 2:
                return "2-ой список";
                break;
            default:
                // Connect to DB
                $DBH = DB::$DBH;
                
                // Select category's name
                $DBH->query( "SELECT `name` FROM `categories` WHERE `id` = :id", array( 'id' => $id ) );
                $name = $DBH->fetch();
                
                return $name[0]['name'];
        }
    }
    
    public static function exists($id)
    {
        // Connect to DB
        $DBH = DB::$DBH;
        
        // If $id is 0, 1 or 2, it exists
        if ( $id == 0 || $id == 1 || $id == 2 )
        {
            return TRUE;
        }
        
        // Select category
        $DBH->query( "SELECT `id` FROM `categories` WHERE `id` = :id", array( 'id' => $id ) );
        if ( $DBH->stmt->rowCount() < 1 )
        {
            return FALSE;
        } else {
            return TRUE;
        }
    }
    
    public static function level($id)
    {
        // Connect to DB
        $DBH = DB::$DBH;
        
        // If category is from 2nd list, throw exception
        if ( self::fromList($id) == 2 )
        {
            throw new inviException(101, "Category is from 2nd list");
        }
        
        // Select parent
        $DBH->query( "SELECT `parent` FROM `categories` WHERE `id` = :id", array( 'id' => $id ) );
        $parent = $DBH->fetch();
        $parent = $parent[0]['parent'];
        
        // If parent is 1, category is 1st level
        if ( $parent == 1 )
        {
            return 1;
        }
        
        // Select parent's parent
        $DBH->query( "SELECT `parent` FROM `categories` WHERE `id` = :id", array( 'id' => $parent ) );
        $parent = $DBH->fetch();
        $parent = $parent[0]['parent'];
        
        // If parent is 1, category is 2nd level, if not - 3rd level
        if ( $parent == 1 )
        {
            return 2;
        } else {
            return 3;
        }
    }
    
    public static function add($name, $parent)
    {
        // Connect to DB
        $DBH = DB::$DBH;
        
        // If parent is 0, throw excpetion
        if ( $parent == 0 )
        {
            throw new inviException(101, "Can't add category with parent 0");
        }
        
        // If parent isn't 1 or 2, check it for existing and check, can it have children
        if ( $parent != 1 && $parent != 2 )
        {
            // Check for existing
            if ( ! self::exists($parent) )
            {
                throw new inviException(101, "Unexisting parent given");
            }
            
            // Check, can it have children
            if ( self::fromList($parent) == 2 || ( self::fromList($parent) == 1 && self::level($parent) == 3 ) )
            {
                throw new inviException(105, "Category can't have children");
            }
        }
        
        // Insert category into DB
        $params = array(
            'name' => $name,
            'parent' => $parent
        );
        $DBH->query( "INSERT INTO `categories` (`name`, `parent`) VALUES (:name, :parent)", $params );
    }
}
?>
