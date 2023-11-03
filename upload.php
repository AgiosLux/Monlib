<?php

require_once "vendor/autoload.php";

$listsCreate = new Monlib\Controllers\ListsCreate();

$listsCreate->uploadAndCreate();