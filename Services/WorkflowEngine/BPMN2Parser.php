<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

if ($_SERVER['argc'] < 5) {
    die("Usage:  " . basename(__FILE__) . " username password client infile [outfile]\n");
}

chdir(dirname(__FILE__));
chdir('../../../../');

include_once 'Services/Authentication/classes/class.ilAuthFactory.php';
ilAuthFactory::setContext(ilAuthFactory::CONTEXT_CRON);

$_COOKIE['ilClientId'] = $_SERVER['argv'][3];
$_POST['username']     = $_SERVER['argv'][1];
$_POST['password']     = $_SERVER['argv'][2];

include_once './include/inc.header.php';

echo "\r\n[Invoking BPMN2-Parser]\r\n";
// -----------------------------------------------------------------------------
require_once dirname(__FILE__) . '/classes/parser/class.ilBPMN2Parser.php';
$parser = new ilBPMN2Parser();
$infile_contents = file_get_contents($_SERVER['argv'][4]);
$parse_result = $parser->parseBPMN2XML($infile_contents);

if ($_SERVER['argv'][5]) {
    file_put_contents($_SERVER['argv'][5], $parse_result);
} else {
    echo $parse_result;
}
echo "\r\n[Finished Parsing]\r\n";
// -----------------------------------------------------------------------------
