<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

if (!file_exists('../ilias.ini.php')) {
    die('The ILIAS setup is not completed. Please run the setup routine.');
}

require_once '../vendor/composer/vendor/autoload.php';

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
if ($_SERVER['REQUEST_METHOD'] === 'PROPFIND'
    || $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    $status = '404 Not Found';
    header("HTTP/1.1 $status");
    header("X-WebDAV-Status: $status", true);
    exit();
}
// END WebDAV: Block WebDAV Requests from Microsoft WebDAV MiniRedir client.

require_once __DIR__ . '/../artifacts/bootstrap_default.php';
entry_point('ILIAS Legacy Initialisation Adapter');

global $DIC;
$DIC->ctrl()->callBaseClass(ilStartUpGUI::class);
$DIC['ilBench']->save();
