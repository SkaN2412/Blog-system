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
    protected static $blocked_until;
    /*
     * function user_register() adds user to DB and euthorizes him
     */
    public static function register($login, $password, $email)
    {

    }
   /*
    * user_authorize() checks password correctness and authorize user. Information about user you can get with get($what) method of this class
    */
    public static function authorize($login, $password)
    {

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
