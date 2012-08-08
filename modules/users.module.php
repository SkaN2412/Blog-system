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
     * function user_register() adds user to DB and euthorizes him
     */
    public static function register($login, $password, $email)
    {
        // Connect to DB
        $DBH = db_connect();
        
        // Check login given for existing
        $stmt = $DBH->prepare("SELECT `login` FROM `users` WHERE `login` = :login");
        $stmt->bindParam("login", $login);
        $stmt->execute();
        if ( $stmt->rowCount() > 0 )
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
    * user_authorize() checks password correctness and authorize user. Information about user you can get with get($what) method of this class
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
     * Method get() returns data of user. If you call it without parameters, it will return all user's data in array. Or you can call it with parameter "what to return". You can get login, email or group. If you call this method, while user is not authorized, it will throw exception
     */
    public static function get($what)
    {
        
    }
    /*
     * user_editData() editing data of user. It requires old password to change other data. If password given isn't correct, exception will be thrown
     */
    public static function edit($password, $login, $email, $newPassword)
    {

    }
    /*
     * user_generateRecoveryKey() returns key, that must be given for change password
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
}
?>
