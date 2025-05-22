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

const IL_SOAPMODE_NUSOAP = 0;
const IL_SOAPMODE_INTERNAL = 1;
const IL_SOAPMODE = IL_SOAPMODE_INTERNAL;
const ILIAS_MODULE = 'components/ILIAS/soap';

chdir('../..');

require_once 'vendor/composer/vendor/autoload.php';

// Initialize the error_reporting level, until it will be overwritte when ILIAS gets initialized
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

ilContext::init(ilContext::CONTEXT_SOAP);

$ilIliasIniFile = new ilIniFile('./ilias.ini.php');
$ilIliasIniFile->read();

if ($ilIliasIniFile->readVariable('https', 'auto_https_detect_enabled')) {
    $headerName = $ilIliasIniFile->readVariable('https', 'auto_https_detect_header_name');
    $headerValue = $ilIliasIniFile->readVariable('https', 'auto_https_detect_header_value');

    $headerName = 'HTTP_' . str_replace('-', '_', strtoupper($headerName));
    if (strcasecmp($_SERVER[$headerName], $headerValue) === 0) {
        $_SERVER['HTTPS'] = 'on';
    }
}

if (strcasecmp($_SERVER['REQUEST_METHOD'], 'post') === 0) {
    // This is a SOAP request
    require_once './components/ILIAS/soap/include/inc.soap_functions.php';
    $uri = ilSoapFunctions::buildHTTPPath(false) . '/server.php';
    if (isset($_GET['client_id'])) {
        $uri .= '?client_id=' . $_GET['client_id'];
        $wsdl = $uri . '&wsdl';
    } else {
        $wsdl = $uri . '?wsdl';
    }

    $soapServer = new SoapServer($wsdl, ['uri' => $uri]);
    $soapServer->setObject(new ilSoapFunctions());
    $soapServer->handle();
} else {
    // This is a request to display the available SOAP methods or WSDL...
    ilInitialisation::initILIAS();
    require './components/ILIAS/soap/nusoapserver.php';
}
