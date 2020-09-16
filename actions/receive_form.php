<?php
require(__DIR__ . '/../lib/ReplaysZipBuilder.php');

if(!$_POST) exit('No request received');
if(!$_FILES) exit('No files sent');

$class = new ReplaysZipBuilder($_FILES['replays'], $_POST['bestOf']);
$class->build();
$class->downloadZipFile();