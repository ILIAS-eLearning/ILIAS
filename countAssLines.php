#!/usr/bin/php
<?php

$ilBugs = 2177;
$assBugs = 335;


$command = 'find Modules Services webservice src -type f -name "*.php"';
$files = array();
exec($command, $files);
$ilLines = 0;
foreach($files as $file)
{
    $ilLines += count(file($file));
}


$command = 'find Modules/Test Modules/TestQuestionPool Services/Math Services/QTI -type f -name "*.php"';
$files = array();
exec($command, $files);
$assLines = 0;
foreach($files as $file)
{
    $assLines += count(file($file));
}


$assLinesPercent = sprintf("%.2f%%", $assLines / $ilLines * 100);
$assBugsPercent = sprintf("%.2f%%", $assBugs / $ilBugs * 100);
$ilBugsPerLine = $ilBugs / $ilLines;
$assBugsPerLine = $assBugs / $assLines;

$ilLines = sprintf("%8d", $ilLines);
$assLines = sprintf("%8d", $assLines);

$ilBugs = sprintf("%5d", $ilBugs);
$assBugs = sprintf("%5d", $assBugs);

echo "\n";
echo "ILIAS Total Code Lines:\t\t".$ilLines."\n";
echo "Assessment Total Code Lines:\t".$assLines."\t(".$assLinesPercent.")\n";
echo "\n";
echo "ILIAS Total Bugs:\t\t".$ilBugs."\n";
echo "Assessment Total Bugs:\t\t".$assBugs."\t\t(".$assBugsPercent.")\n";
echo "\n";
echo "ILIAS Bugs per Line:\t\t".$ilBugsPerLine."\n";
echo "Assessment Bugs per Line:\t".$assBugsPerLine."\n";
echo "\n";
