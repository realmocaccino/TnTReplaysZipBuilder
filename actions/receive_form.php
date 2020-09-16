<?php
require(__DIR__ . '/../lib/ReplaysZipBuilder.php');

if(!isset($_POST) or !$_POST) exit('No request received');
if(!isset($_FILES) or !$_FILES) exit('No files sent');

$class = new ReplaysZipBuilder($_FILES['replays'], $_POST['bestOf']);
$class->build();
$class->downloadZipFile();