<?php

require_once './vendor/autoload.php';

use Monlib\Models\Migrations\{ImportTables, ExportTables};

$importTables   =   new ImportTables;
$exportTables   =   new ExportTables;

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
