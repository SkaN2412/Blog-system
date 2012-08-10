<?php
/*
 * Callback module
 * Needed to send callbacks onto e-mail of admin
 */
/*
 * callback_send() is the main function. It takes parameters and put 'em into template. Then it calls callback_mailSend() function
 */
function callback_send($name, $email, $text)
{
    try {
        // Initialize templater
        $templater = new inviTemplater(config_get("system->templatesDir"));

        // Load mail template
        $templater->load("callback_text");
        
        // Prepare array with variables
        $vars = array(
            'name' => $name,
            'email' => $email,
            'text' => $text
        );
        
        // Parse template with variables
        $mailText = $templater->parse($vars);
    } catch ( inviException $e ) {}
    
    $adminEmail = config_get("admin->email");
    $siteName = config_get("site_data->name");
    
    // Prepare headers
    $headers = "From: {$email}\r\n";
    $headers .= "Reply-To: {$email}\r\n";
    
    // Send email
    return mail($adminEmail, "Обратная связь сайта {$siteName}", $mailText, $headers);
}
?>