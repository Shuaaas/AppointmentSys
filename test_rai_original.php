<?php
$zip = new ZipArchive();
$zip->open('resources/templates/Report on Appointment Issued.xlsx');

echo "=== Original Template Shared Strings ===\n";
$ss = $zip->getFromIndex($zip->locateName('xl/sharedStrings.xml'));
$xml = simplexml_load_string($ss);
$idx = 0;
foreach ($xml->si as $si) {
    $t = (string) $si->t;
    if ($t !== '') {
        echo "$idx => $t\n";
        $idx++;
    }
}

echo "\n=== Original Template sheet1 cells ===\n";
$sheet = $zip->getFromIndex($zip->locateName('xl/worksheets/sheet1.xml'));
$xml = simplexml_load_string($sheet);
foreach ($xml->sheetData->row as $row) {
    foreach ($row->c as $cell) {
        $ref = (string) $cell['r'];
        $t = (string) $cell['t'];
        $v = (string) $cell->v;
        if ($v !== '' || $t !== '') {
            echo "$ref type=$t v=$v\n";
        }
    }
}

$zip->close();
