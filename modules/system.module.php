<?php
/**
 * Here will be all the system modules 
 */
/*
 * inviTemplater 3.4beta
 * Dependencies: inviExceptions
 * Documentation can be found in https://github.com/SkaN2412/inviCMS/wiki/%D0%A0%D0%B0%D0%B1%D0%BE%D1%82%D0%B0-%D1%81-inviTemplater
 */
class inviTemplater {
	//Metadata
	public $name = "inviTemplater";
	public $version = "3.4beta";
	//Directory with templates
	private $dir;
	//Content of current template, filles with method "load"
	private $content;
	//Variables for current template, filles with method "parse"
	private $vars;

	//Set templater dir
	public function __construct($dir) {
		if ($dir[strlen($dir)-1] != DS)
		{
			$dir .= DS;
		}
		//If directory doesn't exist, throw exception
		if (!is_dir($dir))
		{
			throw new inviException(10001, "Directory {$dir} does not exist.");
		}
		//Set $this->dir
		$this->dir = $dir.DS;
	}

	//Load template for parsing
	public function load($page) {
		//Check existing file and check, is it with .htm or .html extention
		if (file_exists($this->dir.$page.".htm"))
		{
			$file = $this->dir.$page.".htm";
		} elseif (file_exists($this->dir.$page.".html")) {
			$file = $this->dir.$page.".html";
		} else {
			throw new inviException(10001, "File {$this->dir}{$page}.htm(l) does not exist.");
		}
		//Check emptyness of file
		if (!filesize($file))
		{
			throw new inviException(10002, "File ".$file." is empty");
		}
		//Fill $this->content with file contents
		$this->content = file_get_contents($file);
	}
	
	//Loading file and parsing it
	public function parse($vars) {
		//Vars must be an array!
		if (!is_array($vars))
		{
			throw new inviException(10003, "\$vars is not array");
		}
		$this->vars = $vars;
		unset($vars);
		//Parsing file and returning final value
		return $this->parseCode($this->content);
	}

	//Main function that parses all the content
	private function parseCode($code) {
		//I don't know, how is working this template, but it works.
		preg_match_all('/\{(case|array|var|include)=\'(.*?)\'\}(((?:(?!\{\/?\1=\'\2\').)*)\{\/\1=\'\2\'\})?/s', $code, $matches);
		for ($i=0; $i<count($matches[0]); $i++) { //Parsing all the conditions with their methods
			switch ($matches[1][$i]) {
				case "case":
					$code = $this->parseCase($matches[0][$i], $code);
					break;
				case "array":
					$code = $this->parseCycle($matches[0][$i], $code);
					break;
				case "var":
					$code = $this->parseVar($matches[0][$i], $code);
					break;
				case "include":
					$code = $this->parseInclude($matches[0][$i], $code);
					break;
			}
		}
		return $code;
	}

	//Insert variable. It's simple, I don't want to comment it...
	private function parseVar($match, $code) {
		preg_match('/\{var=\'(.*?)\'\}/', $match, $var);
		if (!isset($this->vars[$var[1]]))
		{ var_dump($this->vars);
			throw new inviException(10004, "Variable $".$var[1]." does not exist");
		}
		$code = str_replace($match, $this->vars[$var[1]], $code);
		return $code;
	}

	//It loads given template and parse it with variables from $this->vars. Method is similar with method "load"
	private function parseInclude($match, $code) {
		preg_match('/\{include=\'(.*?)\'\}/', $match, $matches);
		if (file_exists($this->dir.$matches[1].".htm"))
		{
			$file = $this->dir.$matches[1].".htm";
        	} elseif (file_exists($this->dir.$matches[1].".html")) {
			$file = $this->dir.$matches[1].".html";
        	} else {
			throw new inviException(10001, "File ".$this->dir.$matches[1].".htm(l) does not exist");
		}
		//Check emptyness of file
		if (!filesize($file))
		{
			throw new inviException(10002, "File ".$file." is empty");
		}
		$content = file_get_contents($file);
		//Recursive parsing of code
		$content = $this->parseCode($content);
		$code = str_replace($matches[0], $content, $code);
		return $code;
	}

	//This method parses cycles with multi-dimensional arrays
	private function parseCycle($match, $code) {
		preg_match('|\{array=\'(\w+)\'\}((?:(?!\{/?array).)*){/array=\'\1\'}|s', $match, $matches);
		if (!isset($this->vars[$matches[1]]))
		{
			throw new inviException(10004, "Variable $".$matches[1]." does not exist");
		}
		if (!is_array($this->vars[$matches[1]]))
		{
			throw new inviException(10005, "Variable $".$matches[1]." is not array");
		}
		if (!isset($this->vars[$matches[1]][0]) || !is_array($this->vars[$matches[1]][0]))
		{
			throw new inviException(10006, "Array $".$matches[1]." is not multi-dimensional");
		}
		$coden = "";
		//$coden (code new) is temp varible, which will have final content for replacing in $code. $temp is one-cycle variable, which will have only one entry and add it to $coden.
		for ($c=0; $c<count($this->vars[$matches[1]]); $c++) {
			$temp = $matches[2];
			//Replace all the entries with array
			foreach ($this->vars[$matches[1]][$c] as $key=>$value) {
				$temp = str_replace("{".$key."}", $value, $temp);
			}
			$temp = $this->parseCode($temp);
			$coden .= $temp;
		}
		$code = str_replace($matches[0], $coden, $code);
		$code = $this->parseCode($code);
		return $code;
	}

	//Parsing all the cases. VERY big method, will be shorted in time...
	private function parseCase($match, $code) {
		preg_match('/\{case=\'([0-9A-Za-z_]+)(==|!=|<=|>=|<|>|\|isset\|)([0-9A-Za-z_]+)\'\}((?:(?!\{\/?case=\'\1\2\3\').)*)\{\/case=\'\1\2\3\'\}/s', $match, $matches);
		switch ($matches[2]) {
			case "==":
				if (!isset($this->vars[$matches[1]]))
				{
					throw new inviException(10004, "Variable $".$matches[1]." does not exist");
				}
				if (isset($this->vars[$matches[3]])) {
					if ($this->vars[$matches[1]] == $this->vars[$matches[3]]) {
						$matches[4] = parseCode($matches[4]);
						$code = str_replace($matches[0], $matches[4], $code);
					}
					else {
						$code = str_replace($matches[0], " ", $code);
					}
				}
				else {
					if ($this->vars[$matches[1]] == $matches[3]) {
						$matches[4] = $this->parseCode($matches[4]);
						$code = str_replace($matches[0], $matches[4], $code);
					}
					else {
						$code = str_replace($matches[0], " ", $code);
					}
				}
				break;
			case "!=":
				if (!isset($this->vars[$matches[1]]))
				{
					throw new inviException(10004, "Variable $".$matches[1]." does not exist");
				}
				if (isset($this->vars[$matches[3]])) {
					if ($this->vars[$matches[1]] != $this->vars[$matches[3]]) {
						$matches[4] = $this->parseCode($matches[4]);
						$code = str_replace($matches[0], $matches[4], $code);
					}
					else {
						$code = str_replace($matches[0], " ", $code);
					}
				}
				else {
					if ($this->vars[$matches[1]] != $matches[3]) {
						$matches[4] = $this->parseCode($matches[4]);
						$code = str_replace($matches[0], $matches[4], $code);
					}
					else {
						$code = str_replace($matches[0], " ", $code);
					}
				}
				break;
			case "<=":
				if (!isset($this->vars[$matches[1]]))
				{
					throw new inviException(10004, "Variable $".$matches[1]." does not exist");
				}
				if (isset($this->vars[$matches[3]])) {
					if ($this->vars[$matches[1]] <= $this->vars[$matches[3]]) {
						$matches[4] = $this->parseCode($matches[4]);
						$code = str_replace($matches[0], $matches[4], $code);
					}
					else {
						$code = str_replace($matches[0], " ", $code);
					}
				}
				else {
					if ($this->vars[$matches[1]] <= $matches[3]) {
						$matches[4] = $this->parseCode($matches[4]);
						$code = str_replace($matches[0], $matches[4], $code);
					}
					else {
						$code = str_replace($matches[0], " ", $code);
					}
				}
				break;
			case ">=":
				if (!isset($this->vars[$matches[1]]))
				{
					throw new inviException(10004, "Variable $".$matches[1]." does not exist");
				}
				if (isset($this->vars[$matches[3]])) {
					if ($this->vars[$matches[1]] >= $this->vars[$matches[3]]) {
						$matches[4] = $this->parseCode($matches[4]);
						$code = str_replace($matches[0], $matches[4], $code);
					}
					else {
						$code = str_replace($matches[0], " ", $code);
					}
				}
				else {
					if ($this->vars[$matches[1]] >= $matches[3]) {
						$matches[4] = $this->parseCode($matches[4]);
						$code = str_replace($matches[0], $matches[4], $code);
					}
					else {
						$code = str_replace($matches[0], " ", $code);
					}
				}
				break;
			case "<":
				if (!isset($this->vars[$matches[1]]))
				{
					throw new inviException(10004, "Variable $".$matches[1]." does not exist");
				}
				if (isset($this->vars[$matches[3]])) {
					if ($this->vars[$matches[1]] < $this->vars[$matches[3]]) {
						$matches[4] = $this->parseCode($matches[4]);
						$code = str_replace($matches[0], $matches[4], $code);
					}
					else {
						$code = str_replace($matches[0], " ", $code);
					}
				}
				else {
					if ($this->vars[$matches[1]] < $matches[3]) {
						$matches[4] = $this->parseCode($matches[4]);
						$code = str_replace($matches[0], $matches[4], $code);
					}
					else {
						$code = str_replace($matches[0], " ", $code);
					}
				}
				break;
			case ">":
				if (!isset($this->vars[$matches[1]]))
				{
					throw new inviException(10004, "Variable $".$matches[1]." does not exist");
				}
				if (isset($this->vars[$matches[3]])) {
					if ($this->vars[$matches[1]] > $this->vars[$matches[3]]) {
						$matches[4] = $this->parseCode($matches[4]);
						$code = str_replace($matches[0], $matches[4], $code);
					}
					else {
						$code = str_replace($matches[0], " ", $code);
					}
				}
				else {
					if ($this->vars[$matches[1]] > $matches[3]) {
						$matches[4] = $this->parseCode($matches[4]);
						$code = str_replace($matches[0], $matches[4], $code);
					}
					else {
						$code = str_replace($matches[0], " ", $code);
					}
				}
				break;
			case "|isset|":
				if ($matches[3] == "true") {
					if (isset($this->vars[$matches[1]])) {
						$matches[4] = $this->parseCode($matches[4]);
						$code = str_replace($matches[0], $matches[4], $code);
					}
					else {
						$code = str_replace($matches[0], " ", $code);
					}
				}
				if ($matches[3] == "false") {
					if (!isset($this->vars[$matches[1]])) {
						$matches[4] = $this->parseCode($matches[4]);
						$code = str_replace($matches[0], $matches[4], $code);
					}
					else {
						$code = str_replace($matches[0], " ", $code);
					}
				}
				break;
			default:
				throw new inviException(10007, $matches[2]." is not a valid case");
		}
		return $code;
	}

	public function uload() {
		$this->vars = NULL;
		$this->content = NULL;
		return true;
	}
}

/**
 * config_get returns value for link given. If value doesn't exist, will be returned FALSE
 * @param string $what What to return. This parameter requires path to value needed like a path to file with unix-like separator. For example: database/server
 * @return string Value
 */
function config_get($what)
{
    // Get data from config
    $config = parse_ini_file("etc".DS."config.ini", TRUE);
    
    $tree = explode("/", $what);
    
    $value = $config[$tree[0]];
    
    for ( $index=1; $index<count($tree); $index++ )
    {
        if ( ! isset( $value[$tree[$index]] ) )
        {
            return FALSE;
        } else {
            $value = $value[$tree[$index]];
        }
    }
    return $value;
}

/*
 * inviException
 * Extended exceptions tool for php
 */

final class inviException extends Exception {
    //Metadata
    const NAME = "inviException";
    const VER = "0.2beta";
    
    private static $logging = true;
    private static $printing = true;
    private static $pf = "html";
    private static $lf = "html";
    private static $logfile;
    private $plain_template = "[{date}] {file}: #{errno} - {error}\n\t{trace}\n";
    private $html_template = "<style>table, td, tr {border: 1px solid red} td.h {font-weight: bold; background-color: red} td.c {background-color: orange}</style><table><tr><td class=\"h\">File:</td><td class=\"c\">{file}</td></tr><tr><td class=\"h\">Date:</td><td class=\"c\">{date}</td></tr><tr><td class=\"h\">Error:</td><td class=\"c\">#{errno}: {error}</td></tr><tr><td class=\"h\">Trace:</td><td class=\"c\">{trace}</td></tr></table>";
    
    protected $error;
    protected $errno;
    protected $file;
    protected $trace;
    
    public static function __init($mode, $logfile = "log.html", $lf = "html", $pf = "html")
    {
        switch ($mode)
        {
            case 0:
                self::$printing = false;
                self::$logging = false;
                break;
            case 1:
                self::$printing = true;
                self::$logging = false;
                break;
            case 2:
                self::$printing = false;
                self::$logging = true;
                break;
            case 3:
                self::$printing = true;
                self::$logging = true;
                break;
            default:
                return "Invalid mode argument.";
        }
        if (!file_exists($logfile) && self::$logging == true)
        {
            touch($logfile);
        }
        self::$logfile = $logfile;
        if ($pf == "plain" || $pf == "html")
        {
            self::$pf = $pf;
        } else {
            self::$pf = "html";
        }
        if ($lf == "plain" || $lf == "html")
        {
            self::$lf = $lf;
        } else {
            self::$lf = "html";
        }
        return true;
    }
    
    public function __construct($errno, $error)
    {
        parent::__construct($error, intval($errno));
        $this->error = parent::getMessage();
        $this->errno = parent::getCode();
        $this->file = parent::getFile().":".parent::getLine();
        $this->trace = parent::getTraceAsString();
        if (self::$printing)
        {
            $this->print_error();
        }
        if (self::$logging)
        {
            $this->log_error();
        }
        return true;
    }
    
    private function print_error()
    {
        print($this->prepare_data("p"));
        return true;
    }
    
    private function log_error()
    {
	$file_contents = file_get_contents(self::$logfile);
        file_put_contents(self::$logfile, $file_contents.$this->prepare_data("l"));
        return true;
    }
    
    public function errno()
    {
        return $this->errno;
    }
    
    public function error()
    {
        return $this->error;
    }
    
    private function prepare_data($for)
    {
        switch ($for)
        {
            case "p":
                $format = self::$pf;
                break;
            case "l":
                $format = self::$lf;
                break;
        }
        switch ($format)
        {
            case "plain":
                $content = $this->plain_template;
                break;
            case "html":
                $content = $this->html_template;
                break;
        }
        $content = str_replace("{date}", date("d F Y, H:i:s"), $content);
        $content = str_replace("{file}", $this->file, $content);
        $content = str_replace("{errno}", $this->errno, $content);
        $content = str_replace("{error}", $this->error, $content);
        $content = str_replace("{trace}", $this->trace, $content);
        return $content;
    }
    
    public function return_metadata()
    {
        return self::NAME." ".self::VER;
    }
}

/**
 * Class inviPDO extends PDO and redefines some his methods for simplifying work with it 
 * 
 * @author Andrey "SkaN" Kamozin <andreykamozin@gmail.com>
 */
class inviPDO extends PDO {
    /**
     * This variable includes object of PDOStatement class
     * 
     * @var object Object of PDOStatement class
     */
    public $stmt;

    /**
    * Method for connect to database
    * 
    * @throws inviException In case of connection error
    * @return void
    */
    
    public function __construct()
    {
        // Get database server data from config
        $conn_data = config_get("database");

        try { // Try to connect
            parent::__construct("mysql:host={$conn_data['server']};dbname={$conn_data['db']};charset=utf8", $conn_data['login'], $conn_data['password']);
        } catch ( PDOException $e ) { // If there's any errors, throw exception
            throw new inviException( (int)$e->getCode(), $e->getMessage() );
        }

        // Errors will throw exceptions
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    }
    
    /**
     * Method executes query given with data if any.
     * 
     * @param type $query
     * @param type $data
     * @throws inviException In case of MySQL error
     */
    public function query($query, $data = array())
    {
        // Prepare query
        $this->stmt = $this->prepare($query);
        
        // Execute statement with data given
        $this->stmt->execute((array)$data);
        
        // Check for errors. If any, throw inviException
        if ( $this->stmt->errorCode() != "00000" )
        {
            $error = $this->stmt->errorInfo();
            throw new inviException( $error[0], $error[2] );
        }
    }

    /**
     * Method fetch is required for getting data returned by server
     * 
     * @param string $fetch_mode Should be assoc or num. If unknown mode given, will be fetched assoc
     * @return array Multi-dimensional array with data returned or NULL
     */
    public function fetch($fetch_mode = "assoc")
    {
        // Set fetch mode, default is assoc.
        switch ($fetch_mode)
        {
            case "num":
                $this->stmt->setFetchMode(PDO::FETCH_NUM);
                break;
            case "assoc":
            default:
                $this->stmt->setFetchMode(PDO::FETCH_ASSOC);
        }
        
        // If nothing is returned, throw exception
        if ($this->stmt->rowCount() == 0)
        {
            return NULL;
        }
        
        // Fetch $data array with rows
        $data = array();
        while ($row = $this->stmt->fetch())
        {
            $data[] = $row;
        }
        return $data;
    }
}

/**
 * This class is needed for not creating new connection each time. Execute connect() from this class just once and connection will be in the DB::$DBH availible from everywhere 
 */
class DB {
    public static $DBH;
    
    public static function connect()
    {
        self::$DBH = new inviPDO();
    }
}

/*
 * Module for registering user and authorizing him
 */
class User
{
    /*
     * function register() adds user to DB and euthorizes him
     */
    public static function register($email, $password, $nickname)
    {
        // Check, is user authorized. If authorized, he's trying to hack system
        @session_start();
        if ( isset($_SESSION['authorized']) )
        {
            throw new inviException(7, "Can't register while authorized.");
        }
        
        // Connect to DB
        $DBH = DB::$DBH;
        
        // Check login given for existing
        if ( self::isRegistered($email) )
        {
            throw new inviException(1, "This email is already registered");
        }
        
        // Check nickname given for existing
        $DBH->query( "SELECT `nickname` FROM `users` WHERE `nickname` = :nickname", array( 'nickname' => $nickname ) );
        if ( $DBH->stmt->rowCount() > 0 )
        {
            throw new inviException(2, "This nickname is already used");
        }
        
        // All is right, user with data given does not exist. Now generate password hash
        $hash = hash("md4", $password);
        
        // And now insert data into DB
        $stmtParams = array(
            'email' => $email,
            'password' => $hash,
            'nickname' => $nickname
        );
        $result = $DBH->query("INSERT INTO `users` (`email`, `password`, `nickname`) VALUES (:email, :password, :nickname)", $stmtParams );
        
        // Now authorize user
        self::authorize();
    }
   /*
    * authorize() checks password correctness and authorize user. Information about user you can get with get($what) method of this class
    */
    public static function authorize()
    {
        // Check for user data in session. If there's one, auth user.
        @session_start();
        if ( isset($_SESSION['authorized']) )
        {
            return TRUE;
        } else {
            // If there's nothing in session, get login and password from post variables
            $email = $_POST['email'];
            $password = $_POST['password'];
        }
        
        // Connect to DB
        $DBH = DB::$DBH;
        
        // Generate hash of password
        $hash = hash("md4", $password);
        
        // Get data from DB
        $DBH->query( "SELECT * FROM `users` WHERE `email` = :email", array( 'email' => $email ) );
        // If nothing is returned, throw exception
        if ( $DBH->stmt->rowCount() < 1 )
        {
            throw new inviException(4, "Login is not registered");
        }
        $userData = $DBH->fetch();
        $userData = $userData[0];
        
        // Check password correctness
        if ( $userData['password'] != $hash )
        {
            throw new inviException(5, "Incorrect password");
        }
        
        // Insert data into session variables
        unset($userData['password']);
        $_SESSION['authorized'] = TRUE;
        $_SESSION = array_merge($_SESSION, $userData);
        return TRUE;
    }
    /*
     * Method get() returns data of user. It requires login and returns array with data.
     */
    public static function get($login = NULL)
    {
        @session_start();
        
        // If $login isn't given, return data of current user
        if ( $login == NULL && isset($_SESSION['id']) )
        {
            $return = array(
                'id' => $_SESSION['id'],
                'email' => $_SESSION['email'],
                'nickname' => $_SESSION['nickname'],
                'group' => $_SESSION['group'],
                'blocked_until' => $_SESSION['blocked_until']
            );
            return $return;
        } else {
            return false;
        }
        
        // Connect to DB
        $DBH = DB::$DBH;
        
        // Select data
        $DBH->query( "SELECT `email`, `nickname`, `group`, `blocked_until` FROM `users` WHERE `email` = :login", array( 'login' => $login ) );
        
        // If nothing is returned, throw exception
        if ( $DBH->stmt->rowCount() < 1 )
        {
            throw new inviException(4, "Login is not registered");
        }
        $result = $DBH->fetch();
        return $result[0];
    }
    /*
     * changePassword() requires old password and new password. User must be authorized - login will be taken from auth-data. 
     */
    public static function chandePassword($old, $new)
    {
        // Connect to DB
        $DBH = DB::$DBH;
        
        // Take login from class property
        $login = self::get();
        $login = $login['email'];
        
        // Check, is the old password correct
        $DBH->query( "SELECT `password` FROM `users` WHERE `email` = :login", array( 'login' => $login ) );
        $checkPassword = $DBH->fetch();
        $checkPassword = $checkPassword[0];
        
        // Check password correctness
        $crypt = new Bcrypt(15);
        if ( ! $crypt->verify($old, $checkPassword['password']) )
        {
            throw new inviException(5, "Incorrect password");
        }
        
        // Update password in DB
        $stmtParams = array(
            'password' => $crypt->hash($new),
            'login' => $login
        );
        $result = $DBH->query( "UPDATE `users` SET `password` = :password WHERE `email` = :login", $stmtParams );
        if ( $stmt->rowCount() < 1 || ! $result )
        {
            throw new inviException(6, "Unknown error, nothing is changed");
        }
        return TRUE;
    }
    /*
     * generateRecoveryKey() returns key, that must be given for change password
     */
    public static function generateRecoveryKey($login)
    {

    }
    /*
     * function user_changeLostPassword() changes password of user if key given is similar with generated.
     */
    public static function changeLostPassword($key, $newPassword)
    {

    }
    /*
     * This private method is needed for checking user registered
     */
    private static function isRegistered($login)
    {
        // Connect to DB
        $DBH = DB::$DBH;
       
        // Select entry with this login
        $DBH->query( "SELECT `email` FROM `users` WHERE `email` = :login", array( 'login' => $login ) );
        if ( $DBH->stmt->rowCount() < 1 )
        {
            return FALSE;
        } else {
            return TRUE;
        }
    }
    
    public static function check($what)
    {
        switch ($what)
        {
            case "email":
                DB::$DBH->query( "SELECT `email` FROM `users` WHERE `email` = :email", array( 'email' => $_POST['email'] ) );
                if ( DB::$DBH->stmt->rowCount() < 1 )
                {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
            case "nickname":
                DB::$DBH->query( "SELECT `nickname` FROM `users` WHERE `nickname` = :nickname", array( 'nickname' => $_POST['nickname'] ) );
                if ( DB::$DBH->stmt->rowCount() < 1 )
                {
                    return TRUE;
                } else {
                    return FALSE;
                }
                break;
        }
    }
    
    public static function authorized()
    {
        @session_start();
        
        if ( isset($_SESSION['authorized']) && $_SESSION['authorized'] == TRUE )
        {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    /*
     * Method is required for registering user and changing user's email
     */
    private static function verifyEmail($email)
    {
        // Пока обойдемся без этого, сделаю подтверждение почты в бета-версии
    }
}

class System {
    public static function out($title, $content)
    {
        $page = "";
        
        $templater = new inviTemplater("styles".DS."templates");
        $templater->load("header");
        
        $siteData = config_get("site_data");
        
        if ( User::authorized() )
        {
            $authed = "true";
        } else {
            $authed = "false";
        }
        
        $params = array(
            'title' => $title,
            'site_name' => $siteData['name'],
            'site_desc' => $siteData['description'],
            'authed' => $authed
        );
        
        $page .= $templater->parse( $params );
        
        $templater->load("content");
        $page .= $templater->parse( array( 'content' => $content ) );
        
        $templater->load("categories");
        $params = array(
            'list1' => Categories::get(1),
            'list2' => Categories::get(2)
        );
        $page .= $templater->parse( $params );
        
        $templater->load("footer");
        $page .= $templater->parse(array());
        
        print($page);
    }
}
?>