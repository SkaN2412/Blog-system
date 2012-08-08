<?php
/**
 * inviEngine 5.3
 * @author: Andrey Kamozin
 */

define("DS", DIRECTORY_SEPARATOR);
define("WD", "./");

//Executing scripts from init folder
$includes = scandir(WD."init");
for ($i=0; $i<count($includes); $i++)
{
	if ($includes[$i] == "." || $includes[$i] == "..") continue;
	preg_match("|([^А-Яа-я]+).php|", $includes[$i], $match);
	if ($match != array())
	{
		include_once(WD."init".DS.$match[1].".php");
	}
}
unset($includes, $i, $match);
//Know, which page to open
if (isset($_GET['id']))
{ //If there's id GET param, page is $_GET['id']
	$pageid = $_GET['id'];
} else { //Else, open main page
	$pageid = "articles_list";
}
//Include page
if (in_array($pageid, $accepted_list) && file_exists(WD."pages".DS.$pageid.".php"))
{ //If page's accepted & page exists, include it
	include(WD."pages".DS.$pageid.".php");
} else { //Else include 404 page
	include(WD."pages".DS."404.php");
}
?>