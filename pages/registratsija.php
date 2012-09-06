<?php
try {
if ( isset($_GET['action']) )
{
        $email = User::check("email");
        $nickname = User::check("nickname");
        $return = array();
        if ( ! $email || ! $nickname )
        {
            $return['success'] = FALSE;
            $return['email'] = $email;
            $return['nickname'] = $nickname;
        } else {
            User::register($_POST['email'], $_POST['password'], $_POST['nickname']);
            $return['success'] = TRUE;
        }
        print( json_encode($return) );
        exit;
}
} catch ( inviException $e ) {
    $return = array(
        'success' => FALSE,
        'error' => $e->getMessage()
    );
    print( json_encode($return) );
    exit;
}

$templater = new inviTemplater("styles".DS."templates");
$templater->load("register");
System::out( "Регстрация", $templater->parse(array()) );
?>