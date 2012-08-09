<?php
function parseDate($input_date)
{
	$datetemp = explode(".", $input_date);
	$date = $datetemp[2]." ";
	switch ($datetemp[1])
	{
		case "01":
			$date .= "января ";
			break;
		case "02":
			$date .= "февраля ";
			break;
		case "03":
			$date .= "марта ";
			break;
		case "04":
			$date .= "апреля ";
			break;
		case "05":
			$date .= "мая ";
			break;
		case "06":
			$date .= "июня ";
			break;
		case "07":
			$date .= "июля ";
			break;
		case "08":
			$date .= "августа ";
			break;
		case "09":
			$date .= "сентября ";
			break;
		case "10":
			$date .= "октября ";
			break;
		case "11":
			$date .= "ноября ";
			break;
		case "12":
			$date .= "декабря ";
			break;
	}
	$date .= $datetemp[0]." года, в ".$datetemp[3];
	return $date;
}
/*
 * inviDate() returns current date and time in inviCMS format
 */
function inviDate()
{
    return date("Y.m.d.H:i");
}
/*
 * This function returns database connection handle
 */
function db_connect()
{
    // Get connection data from configs
    $conn_data = config_get("database");
    // Create PDO object with data got
    $DBH = new inviPDO("mysql:host={$conn_data['server']};dbname={$conn_data['db']}", $conn_data['login'], $conn_data['password']);
    // Set encode to utf8. Needed to fix troubles with encode in articles, comments etc.
    $DBH->query("SET NAMES utf8");
    // Return connection handle
    return $DBH;
}
?>