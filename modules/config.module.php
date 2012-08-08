<?php
include_once("etc".DS."config.php");

function config_get($what)
{
    $tree = explode("->", $what);
    for ($i=0; $i<count($tree); $i++)
    {
        if (!isset($value[$tree[$i]]))
        {
            throw new Exception("Config value $${tree[$i]} does not exist.");
        }
        $value = $value[$tree[$i]];
    }
    return $value;
}
?>
