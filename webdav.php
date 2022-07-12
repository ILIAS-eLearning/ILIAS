<?php declare(strict_types = 1);

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

require_once("Services/Init/classes/class.ilInitialisation.php");

$path_info_components = explode('/', $_SERVER['PATH_INFO']);
$client_id = $path_info_components[1];
$show_mount_instr = isset($_GET['mount-instructions']);

try {
    ilAuthFactory::setContext(ilAuthFactory::CONTEXT_HTTP);

    $_GET["client_id"] = $client_id;
    $context = ilContext::CONTEXT_WEBDAV;
    ilContext::init($context);
    $post_array = $_POST;
    ilInitialisation::initILIAS();
} catch (InvalidArgumentException $e) {
    header("HTTP/1.1 400 Bad Request");
    header("X-WebDAV-Status: 400 Bad Request", true);
    echo '<?xml version="1.0" encoding="utf-8"?>
    <d:error xmlns:d="DAV:" xmlns:s="http://sabredav.org/ns">
      <s:sabredav-version>3.2.2</s:sabredav-version>
      <s:exception>Sabre\DAV\Exception\BadRequest</s:exception>
      <s:message/>
    </d:error>';
    exit;
}

if (!ilDAVActivationChecker::_isActive()) {
    header("HTTP/1.1 403 Forbidden");
    header("X-WebDAV-Status: 403 Forbidden", true);
    echo '<html><body><h1>Sorry</h1>' .
      '<p><b>Please enable the WebDAV plugin in the ILIAS Administration panel.</b></p>' .
      '<p>You can only access this page, if WebDAV is enabled on this server.</p>' .
      '</body></html>';
    exit;
}

$webdav_dic = new ilWebDAVDIC();
$webdav_dic->init($DIC);

if ($show_mount_instr) {
    $mount_gui = $webdav_dic->mountinstructions();
    $mount_gui->renderMountInstructionsContent();
} else {
    $server = new ilWebDAVRequestHandler($webdav_dic);
    $server->handleRequest($post_array);
}
