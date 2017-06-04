<?php
$data = $_POST['data'];
$fileName = $_POST['name'];
$serverFile = $fileName;
$fp = fopen('temp/'.$serverFile,'w'); //Prepends timestamp to prevent overwriting
fwrite($fp, $data);
fclose($fp);
$returnData = array( "serverFile" => $serverFile );
echo json_encode($returnData);