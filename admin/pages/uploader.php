<?php
$title = "Менеджер загрузок";
require_once(".".DS."admin".DS."etc".DS."start_templater.php");

if (isset($_GET['action'])) {
	switch ($_GET['action']) {
		case "remove":
			$file = ".".DS.$_config->get("uploads_directory").DS.$_POST['filename'];
			unlink($file);
			$templater->load("act_stat");
			if (!file_exists($file)) {
				$vars = array(
					'error' => "false",
					'act_message' => "Файл успешно удален!"
				);
			}
			else {
				$vars = array(
					'error' => "true",
					'act_message' => "Удаление завершено ошибкой"
				);
			}
			$content = $templater->parse($vars);
			$files = array();
			$temp = scandir(".".DS.$_config->get("uploads_directory"));
			foreach ($temp as $key => $file) {
				if ($file == "." || $file == "..") {
					unset($temp[$key]);
					continue;
				}
				$files[] = array( 'name' => $file, 'link' => "?id=file&name=".$file );
			}
			unset($temp);
			if ($files === array()) $files = "empty";
			$templater->load("filemanager");
			$vars = array(
				'files' => $files
			);
			$content .= $templater->parse($vars);
			print($content);
			exit;
	}
}

include(".".DS."admin".DS."etc".DS."header.php");

if (isset($_FILES['filename'])) {
	$filename = ".".DS.$_config->get("uploads_directory").DS.$_FILES['filename']['name'];
	$templater->load("act_stat");
	if (file_exists($filename)) {
		$vars = array(
			'act_message' => "Файл уже существует!",
			'error' => "true"
		);
	}
	else {
		copy($_FILES['filename']['tmp_name'], $filename);
		$vars = array(
			'act_message' => "Файл успешно загружен!",
			'error' => "false"
		);
	}
	$upload_stat = $templater->parse($vars);
}
else $upload_stat = "";

$files = array();
$temp = scandir(".".DS.$_config->get("uploads_directory"));
foreach ($temp as $key => $file) {
	if ($file == "." || $file == "..") {
		unset($temp[$key]);
		continue;
	}
	$files[] = array( 'name' => $file, 'link' => "?id=file&name=".$file );
}
unset($temp);

if ($files === array()) $files = "empty";

$templater->load("uploader");
$vars = array(
	'upload_stat' => $upload_stat,
	'files' => $files
);
print($templater->parse($vars));
include(".".DS."admin".DS."etc".DS."footer.php");
?>