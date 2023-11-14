<?php

$xUrl   =   explode("/", $_GET['urlRooter']);

if ($xUrl[0] == 'api') {
    require_once './src/routes/api.php';
} else {
    require_once './src/routes/pages.php';
}
