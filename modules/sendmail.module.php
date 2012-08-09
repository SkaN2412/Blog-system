<?php
/*
 * Module for sending emails
 */
class inviSendmail {
	//Metadata
	const NAME = "inviMailer";
	const VERSION = "1.0beta";
	
	//Connection var
	protected $conn;
    
	protected $selfmail;
    
	protected $selfname = "";
    
	protected $reply = true;
    
    /*
     * Costructor connects to server. It will throw exceptions in case of server errors
     */
	public function __construct($server, $login, $pass, $opt = array()) {
        // Fill properties
		if (isset($opt['selfmail']))
        {
            $this->selfmail = $opt['selfmail'];
        } else {
            $this->selfmail = $login;
        }
		if (isset($opt['selfname']))
        {
            $this->selfname = $opt['selfname'];
        }
        
		//Open connection
		$this->conn = fsockopen($server, 25, $errno, $error, 20);
		if ($this->get_respno() !== "220")
        {
            throw new inviException( $this->getRespno(), $this->getResponse() );
        }
        
		//Send self-name
		$this->query("EHLO", "250");
        
		//Start auth
		$this->query("AUTH LOGIN", "334");
        
		//Send login
		$this->query(base64_encode($login), "334");
        
		//Send pass
		$this->query(base64_encode($pass), "235");
	}
	
    /*
     * Method for sending email through opened connection
     */
	public function send($to, $subject, $text, $type) {
		// Send self-mail
        $this->query("MAIL FROM:".$this->selfmail, "250");
        
        // Send taker's email
        $this->query("RCPT TO:".$to, "250");
        
        //Prepare headers
        $header = "Date: ".date("D, j M Y G:i:s")." +0700\r\n";
        $header .= "From: =?utf8?Q?".str_replace("+","_",str_replace("%","=",urlencode($this->selfname)))."?= <".$this->selfmail.">\r\n";
        $header .= "X-Mailer: ".$mailername." ".$mailerver."\r\n";
        $header .= "X-Priority: 3 (Normal)\r\n";
        $header .= "Message-ID: <172562218.".date("YmjHis")."@89262207055.ru>\r\n";
        $header .= "Subject: =?utf8?Q?".str_replace("+","_",str_replace("%","=",urlencode($subject)))."?=\r\n";
        $header .= "MIME-Version: 1.0\r\n";
        $header .= "Content-Type: text/".$type."; charset=utf8\r\n";
        $header .= "Content-Transfer-Encoding: 8bit\r\n";
        
        //Start sending
        $this->query("DATA", "354");
        
        //Send
        $this->query($header."\r\n".$text."\r\n.", "250");
        
		return true;
	}
    
	/*
     * Method for closing connection
     */
	public function close() {
		$this->query("QUIT\r\n", "221");
		
		return true;
	}
    
    private function query($query, $OKcode)
    {
        fputs($this->conn, $query."\r\n");
        if ( $this->getRespno() != $OKcode )
        {
            throw new inviException($this->getRespno, $this->getResponse);
        }
        return true;
    }
	//Get server response
	private function getResponse() {
		$data = "";
		while ( $str = fgets( $this->conn ) )
        {
            $data .= $str;
        }
		return $data;
	}
	//Get only number of response
	private function getRespno() {
		return substr( $this->get_response(), 0, 3 );
	}
}
?>
