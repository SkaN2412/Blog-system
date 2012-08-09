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
?>