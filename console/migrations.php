<?php

require_once './vendor/autoload.php';

$importTables   =   new Monlib\Models\Migrations\ImportTables;
$exportTables   =   new Monlib\Models\Migrations\ExportTables;

if ($argv[1] == "import") {
    if (isset($argv[2]) && isset($argv[3])) {
        $importTables->importFromFile($argv[2], $argv[3]);
    } else {
        return throw(
            new RuntimeException('File import was not found')
        );
    }
} else if ($argv[1] == "export") {
    $exportTables->exportAllTables();
} else {
    return throw(
        new RuntimeException('Action is invalid')
    );
}
