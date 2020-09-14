<?php
require(__DIR__ . '/../lib/ReplaysZipBuilder.php');

$class = new ReplaysZipBuilder();
$class->build();
$class->downloadZipFile();