<?php

require '../../../config/config.php';
header('Access-Control-Allow-Origin: *');

function getFileType($filename)
{
	return substr($filename, strrpos($filename, '.') + 1);
}
if (isset($_GET['id']) && isset($_GET['folder']))
{
	if (in_array(getFileType($_GET['id']), array('jpg', 'jpeg', 'gif', 'png', 'swf', 'bmp')))
	{
		$image_data = file_get_contents($_SERVER['REQUEST_SCHEME'].'://'.DOMAIN_NAME.'/'.$_GET['folder'].$_GET['id']);
		echo base64_encode($image_data);
	}
}

?>
