<?php
/**
 * Finish the ILIAS-Setup
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
define('ILIAS_ABSOLUTE_PATH', '/var/www/ilias');
define('ILIAS_WEB_DIR', 'data');
define('ILIAS_DATA_DIR', '/var/iliasdata/ilias');

chdir(ILIAS_ABSOLUTE_PATH);
error_reporting(((ini_get("error_reporting") & ~E_NOTICE) & ~E_DEPRECATED) & ~E_STRICT);
$client = $_SERVER['argv'][1];
$client = "default";
session_start();

require_once('./libs/composer/vendor/autoload.php');
require_once('./setup/classes/class.ilClient.php');
require_once('./setup/classes/class.ilDBConnections.php');
require_once('./setup/classes/class.ilCtrlStructureReader.php');

// Some global
global $ilCtrlStructureReader, $ilDB, $ilLog, $lng, $DIC, $ilIliasIniFile, $ilClientIniFile;
$DIC = new \ILIAS\DI\Container();

$ilIliasIniFile = new ilIniFile("./ilias.ini.php");
$ilIliasIniFile->read();

// Init CLient-ini
$ini_file = "./".ILIAS_WEB_DIR."/".$client."/client.ini.php";
$ilClientIniFile = new ilIniFile($ini_file);
$ilClientIniFile->read();

// Read Client
$ilClient = new ilClient($client, new ilDBConnections());
$ilClient->init();
$ilClient->provideGlobalDB();


$DIC['lng'] = $lng = new ilLanguage('en');

$logging_settings = new ilLoggingSetupSettings();
$logging_settings->init();
$ilLog = ilLoggerFactory::newInstance($logging_settings)->getComponentLogger('setup');
$DIC['ilLog'] = $ilLog;

$ilCtrlStructureReader = new ilCtrlStructureReader();
$ilCtrlStructureReader->setIniFile($ilClient->ini);

// Update DB
$dbupdate = new ilDBUpdate($ilDB);
$dbupdate->applyUpdate(9999);
$dbupdate->applyUpdate(9999); //  Due to possible errors in TestModule

$dbupdate->applyHotfix();
$dbupdate->applyCustomUpdates();


// Proxy Settings
$ilClient->setSetting('proxy_status', '');
$ilClient->setSetting('proxy_host', '');
$ilClient->setSetting('proxy_port', '');

// Password Setting
$ilClient->ini->setVariable('auth', 'password_encoder', 'md5');

// NIC
$ilClient->setSetting("inst_id", "0");
$ilClient->setSetting("nic_enabled", "0");

// Contact
$ilClient->setSetting("admin_firstname", 'srag');
$ilClient->setSetting("admin_lastname", 'srag');
$ilClient->setSetting("inst_name", 'srag');
$ilClient->setSetting("admin_email", 'info@studer-raimann.ch');
$ilClient->setDescription('srag development');
$ilClient->ini->write();

// Finish Setup
$ilClient->setSetting('setup_ok', 1);
