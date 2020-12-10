<?php
require(__DIR__ . '/../lib/ReplaysZipBuilder.php');

if(!isset($_FILES) or !$_FILES) exit('No files sent');
if(!isset($_FILES['replays']) or !$_FILES['replays']) exit('No replays sent');

if(array_diff(array_unique($_FILES['replays']['type']), ['text/xml'])) exit('Only XML files are accepted');

$class = new ReplaysZipBuilder($_FILES['replays']['tmp_name']);
$class->build();
$class->downloadZipFile();