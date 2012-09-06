<?php
if ( $_POST['email'] == "" || $_POST['password'] == "" )
{
    $return = array(
        'success' => FALSE
    );
    
    print( json_encode($return) );
    exit;
}
try{

User::authorize();
$return = array(
    'success' => TRUE
);

print( json_encode($return) );
exit;

} catch ( inviException $e ) {
    $return = array(
        'success' => FALSE,
        'error' => 45
    );
    
    print( json_encode($return) );
    exit;
}
?>