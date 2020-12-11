<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* start page of ilias
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/

// jump to setup if ILIAS3 is not installed
if (!file_exists(getcwd() . "/ilias.ini.php")) {
    header("Location: ./setup/setup.php");
    exit();
}

// BEGIN WebDAV: Block WebDAV Requests from Microsoft WebDAV MiniRedir client.
// We MUST block WebDAV requests on the root page of the Web-Server
// in order to make the "Microsoft WebDAV MiniRedir" client work with ILIAS
// WebDAV.
// Important: If this index.php page is NOT at the root of your Web-Server, you
// MUST create an index page at the root of your Web-Server with the same
// blocking behaviour. If you don't implement this, the "Microsoft WebDAV
// MiniRedir" client will not work with ILIAS.
// You can copy the file rootindex.php for this.

// Block WebDAV Requests from Microsoft WebDAV MiniRedir client.
if ($_SERVER['REQUEST_METHOD'] == 'PROPFIND'
|| $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    $status = '404 Not Found';
    header("HTTP/1.1 $status");
    header("X-WebDAV-Status: $status", true);
    exit;
}
// END WebDAV: Block WebDAV Requests from Microsoft WebDAV MiniRedir client.


require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

// begin-patch skyguide
if(
    !isset($_GET['cmd']) ||
    strcmp($_GET['cmd'], 'force_login') !== 0
) {
    ilLoggerFactory::getLogger('auth')->info('Checking for SSO request...');


    $target = (strlen($_GET['target']) ? ('?target='.$_GET['target']) : '?');
    switch($_SERVER['SKY_SSO'])
    {
        // netscaler session
        case '1':
        // kerberos session
        case '2':
            ilLoggerFactory::getLogger('auth')->info('Redirect to: ./intern' . $target);
            ilUtil::redirect('./intern'.$target);
            break;

        default:
            ilLoggerFactory::getLogger('auth')->info('No sso request, showing login page.');
            break;
    }
}
// end-patch skyguide

$ilCtrl->initBaseClass("ilStartUpGUI");
$ilCtrl->callBaseClass();
$ilBench->save();
