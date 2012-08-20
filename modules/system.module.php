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
		{
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

/*
 * A little module for executing MySQL query safely, if there's will be only one data array.
 */
class inviPDO extends PDO {
    public $stmt;
    
    /*
     * Constructor redefines PDO's constructor. It simplifies work with DB in inviCMS
     */
    public function __construct() {
        // Get connection data from configs
        $conn_data = config_get("database");
        
        // Create PDO object with data got
        parent::__construct("mysql:host={$conn_data['server']};dbname={$conn_data['db']}", $conn_data['login'], $conn_data['password']);
        
        // Set encode to utf8. Needed to fix troubles with encode in articles, comments etc.
        $this->query("SET NAMES utf8");
    }
    
    /*
     * Method for prepare and execute query
     * It requires query to execute
     * Parameter $data is not nessesary, it's required only in case of holders in query given
     */
    public function query( $query, $data = array() )
    {
        try {
            $this->stmt = $this->prepare($query);
            $this->stmt->execute( (array)$data );
        } catch ( PDOException $e ) {
            throw new inviException( (int)$e->getCode(), $e->getMessage() );
        }
        return TRUE;
    }
    
    /*
     * Method for getting returned data if any
     */
    public function fetch( $mode = "assoc")
    {
        try {
            // Set fetch mode
            switch ($mode)
            {
                case "assoc":
                    $this->stmt->setFetchMode(PDO::FETCH_ASSOC);
                    break;
                case "num":
                    $this->stmt->setFetchMode(PDO::FETCH_NUM);
                    break;
                default:
                    throw new inviException(1, "Unknown fetch mode");
            }
            
            // If there's nothing returned, return NULL
            if ( $this->stmt->rowCount() < 1 )
            {
                return NULL;
            }
            
            // Fetch all entries into multi-dimensional array
            $data = array();
            while ( $row = $this->stmt->fetch() )
            {
                $data[] = $row;
            }
            
            // Return full array
            return $data;
        } catch ( PDOException $e ) {
            throw new inviException( (int)$e->getCode(), $e->getMessage() );
        }
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
            throw new inviException(1, "This login is already registered");
        }
        
        // Check nickname given for existing
        $DBH->query( "SELECT `nickname` FROM `users` WHERE `nickname` = :nickname", array( 'nickname' => $nickname ) );
        if ( $DBH->stmt->rowCount() > 0 )
        {
            throw new inviException(2, "This nickname is already used");
        }
        
        // All is right, user with data given does not exist. Now generate password hash with Bcrypt class
        $crypt = new Bcrypt(15);
        $hash = $crypt->hash($password);
        
        // And now insert data into DB
        $stmtParams = array(
            'email' => $email,
            'password' => $hash,
            'nickname' => $nickname
        );
        $result = $DBH->query("INSERT INTO `users` (`email`, `password`, `nickname`) VALUES (:email, :password, :nickname)", $stmtParams );
        if ( ! $result )
        {
            throw new inviException(3, "MySQL error: {$DBH->stmt->errorInfo()}");
        }
        
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
        $crypt = new Bcrypt(15);
        
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
        if ( ! $crypt->verify( $password, $userData['password'] ) )
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
        // If $login isn't given, return data of current user
        if ( $login == NULL )
        {
            $return = array(
                'id' => $_SESSION['id'],
                'email' => $_SESSION['email'],
                'nickname' => $_SESSION['nickname'],
                'group' => $_SESSION['group'],
                'blocked_until' => $_SESSION['blocked_until']
            );
            return $return;
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
    /*
     * Method is required for registering user and changing user's email
     */
    private static function verifyEmail($email)
    {
        // Пока обойдемся без этого, сделаю подтверждение почты в бета-версии
    }
}

/*
 * I took this class from http://stackoverflow.com/questions/4795385/how-do-you-use-bcrypt-for-hashing-passwords-in-php . Meybe I will comment it in time, but not now...
 */
class Bcrypt {
  private $rounds;
  public function __construct($rounds = 12) {
    if(CRYPT_BLOWFISH != 1) {
      throw new Exception("bcrypt not supported in this installation. See http://php.net/crypt");
    }

    $this->rounds = $rounds;
  }

  public function hash($input) {
    $hash = crypt($input, $this->getSalt());

    if(strlen($hash) > 13)
      return $hash;

    return false;
  }

  public function verify($input, $existingHash) {
    $hash = crypt($input, $existingHash);

    return $hash === $existingHash;
  }

  private function getSalt() {
    $salt = sprintf('$2a$%02d$', $this->rounds);

    $bytes = $this->getRandomBytes(16);

    $salt .= $this->encodeBytes($bytes);

    return $salt;
  }

  private $randomState;
  private function getRandomBytes($count) {
    $bytes = '';

    if(function_exists('openssl_random_pseudo_bytes') &&
        (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')) { // OpenSSL slow on Win
      $bytes = openssl_random_pseudo_bytes($count);
    }

    if($bytes === '' && is_readable('/dev/urandom') &&
       ($hRand = @fopen('/dev/urandom', 'rb')) !== FALSE) {
      $bytes = fread($hRand, $count);
      fclose($hRand);
    }

    if(strlen($bytes) < $count) {
      $bytes = '';

      if($this->randomState === null) {
        $this->randomState = microtime();
        if(function_exists('getmypid')) {
          $this->randomState .= getmypid();
        }
      }

      for($i = 0; $i < $count; $i += 16) {
        $this->randomState = md5(microtime() . $this->randomState);

        if (PHP_VERSION >= '5') {
          $bytes .= md5($this->randomState, true);
        } else {
          $bytes .= pack('H*', md5($this->randomState));
        }
      }

      $bytes = substr($bytes, 0, $count);
    }

    return $bytes;
  }

  private function encodeBytes($input) {
    // The following is code from the PHP Password Hashing Framework
    $itoa64 = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    $output = '';
    $i = 0;
    do {
      $c1 = ord($input[$i++]);
      $output .= $itoa64[$c1 >> 2];
      $c1 = ($c1 & 0x03) << 4;
      if ($i >= 16) {
        $output .= $itoa64[$c1];
        break;
      }

      $c2 = ord($input[$i++]);
      $c1 |= $c2 >> 4;
      $output .= $itoa64[$c1];
      $c1 = ($c2 & 0x0f) << 2;

      $c2 = ord($input[$i++]);
      $c1 |= $c2 >> 6;
      $output .= $itoa64[$c1];
      $output .= $itoa64[$c2 & 0x3f];
    } while (1);

    return $output;
  }
}
?>