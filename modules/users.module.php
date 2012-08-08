<?php

/*
 * Module for registering user and authorizing him
 */
class User
{
    protected static $authed;
    protected static $login;
    protected static $email;
    protected static $group;
    protected static $blocked_until = NULL;
    /*
     * function register() adds user to DB and euthorizes him
     */
    public static function register($login, $password, $email)
    {
        // Connect to DB
        $DBH = db_connect();
        
        // Check login given for existing
        if ( self::isRegistered($login) )
        {
            throw new inviException(1, "This login is already registered");
        }
        
        // Check email given for existing
        $stmt = $DBH->prepare("SELECT `email` FROM `users` WHERE `email` = :email");
        $stmt->bindParam('email', $email);
        $stmt->execute();
        if ( $stmt->rowCount() > 0 )
        {
            throw new inviException(2, "This email is already used");
        }
        
        // All is right, user with data given does not exist. Now generate password hash with Bcrypt class
        $crypt = new Bcrypt(15);
        $hash = $crypt->hash($password);
        
        // And now insert data into DB
        $stmt = $DBH->prepare("INSERT INTO `users` (`login`, `password`, `email`) VALUES (:login, :password, :email)");
        $stmtParams = array(
            'login' => $login,
            'password' => $hash,
            'email' => $email
        );
        if ( ! $stmt->execute( $stmtParams ) )
        {
            throw new inviException(3, "MySQL error: {$stmt->errorInfo()}");
        }
        
        // Now authorize user
        self::authorize($login, $password);
    }
   /*
    * authorize() checks password correctness and authorize user. Information about user you can get with get($what) method of this class
    */
    public static function authorize($login, $password)
    {
        // Connect to DB
        $DBH = db_connect();
        
        // Generate hash of password
        $crypt = new Bcrypt(15);
        $hash = $crypt->hash($password);
        
        // Get data from DB
        $stmt = $DBH->prepare("SELECT * FROM `users` WHERE `login` = :login");
        $stmt->execute( array( 'login' => $login ) );
        // If nothing is returned, throw exception
        if ( $stmt->rowCount() < 1 )
        {
            throw new inviException(4, "Login is not registered");
        }
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $userData = $stmt->fetch();
        
        // Check password correctness
        if ( ! $crypt->verify( $password, $userData['password'] ) )
        {
            throw new inviException(5, "Incorrect password");
        }
        
        // Fill class properties with data selected
        self::$authed = TRUE;
        self::$login = $userData['login'];
        self::$email = $userData['email'];
        self::$group = $userData['group'];
        if ( $userData['blocked_until'] != NULL )
        {
            self::$blocked_until = $userData['blocked_until'];
        }
        return TRUE;
    }
    /*
     * Method get() returns data of user. It requires login and returns array with data.
     */
    public static function get($login)
    {
        // Connect to DB
        $DBH = db_connect();
        
        // Select data
        $stmt = $DBH->prepare("SELECT `email`, `group`, `blocked_until` FROM `users` WHERE `login` = :login");
        $stmt->execute( array( 'login' => $login ) );
        // If nothing is returned, throw exception
        if ( ! $stmt->rowCount() < 1 )
        {
            throw new inviException(4, "Login is not registered");
        }
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt->fetch();
    }
    /*
     * changePassword() requires old password and new password. User must be authorized - login will be taken from auth-data. 
     */
    public static function chandePassword($password, $newPassword)
    {
        // Connect to DB
        $DBH = db_connect();
        
        // Take login from class property
        $login = self::$login;
        
        // Check, is the old password correct
        $stmt = $DBH->prepare("SELECT `password` FROM `users` WHERE `login` = :login");
        $stmt->execute( array( 'login' => $login ) );
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $checkPassword = $stmt->fetch();
        
        // Check password correctness
        $crypt = new Bcrypt(15);
        if ( $crypt->hash($password) != $checkPassword['password'] )
        {
            throw new inviException(5, "Incorrect password");
        }
        
        // Update password in DB
        $stmt = $DBH->prepare("UPDATE `users` SET `password` = :password WHERE `login` = :login");
        $stmtParams = array(
            'password' => $crypt->hash($newPassword),
            'login' => $login
        );
        $stmt->execute( $stmtParams );
        if ( $stmt->rowCount() < 1 )
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
        $DBH = db_connect();
        
        // Select entry with this login
        $stmt = $DBH->prepare("SELECT `login` FROM `users` WHERE `login` = :login");
        $stmt->execute( array( 'login' => $login ) );
        if ( $stmt->rowCount() < 1 )
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
?>
