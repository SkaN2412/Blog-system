<?php

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
?>