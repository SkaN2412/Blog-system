<?php
$nums = "";

for ($i=0; $i<100; $i++){
$nums .= rand($i, 500);
}

$hash = md5($nums);

print( substr($hash, 2, 2+10) );
?>