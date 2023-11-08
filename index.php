<?php

$xUrl   =   explode("/", $_GET['url']);

if ($xUrl[0] == 'api') {
    require_once './src/routes/api.php';
} else {
    require_once './src/routes/pages.php';
}
