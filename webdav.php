<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
* This script provides a WebDAV interface for the ILIAS repository.
*
* @author Werner Randelshofer, Hochschule Luzern, werner.randelshofer@hslu.ch
* @version $Id: class.ilDAVServer.php,v 1.0 2005/07/08 12:00:00 wrandelshofer Exp $
*
* @package webdav
*/
// Initialize
// -----------------------------------------------------
// Retrieve the client id from PATH_INFO
// Component 1 contains the ILIAS client_id.
$path_info_components = explode('/', $_SERVER['PATH_INFO']);
$client_id = $path_info_components[1];
$show_mount_instr = isset($_GET['mount-instructions']);

// Set context for authentication
include_once 'Services/Authentication/classes/class.ilAuthFactory.php';
ilAuthFactory::setContext(ilAuthFactory::CONTEXT_HTTP);

// Launch ILIAS using the client id we have determined
$_GET["client_id"] = $client_id;
include_once "Services/Context/classes/class.ilContext.php";
$context =  ilContext::CONTEXT_WEBDAV;
ilContext::init($context);
require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

if (!ilDAVActivationChecker::_isActive()) {
    header("HTTP/1.1 403 Forbidden");
    header("X-WebDAV-Status: 403 Forbidden", true);
    echo '<html><body><h1>Sorry</h1>' .
      '<p><b>Please enable the WebDAV plugin in the ILIAS Administration panel.</b></p>' .
      '<p>You can only access this page, if WebDAV is enabled on this server.</p>' .
      '</body></html>';
    exit;
}

if (!$show_mount_instr) {
    // Launch the WebDAV Server
    include_once "Services/WebDAV/classes/class.ilWebDAVRequestHandler.php";
    $server =  ilWebDAVRequestHandler::getInstance();
    $server->handleRequest();
} else {
    // Show mount isntructions page for WebDAV
    include_once "Services/WebDAV/classes/class.ilWebDAVMountInstructionsGUI.php";
    $mount_gui = new ilWebDAVMountInstructionsGUI();
    $mount_gui->showMountInstructionPage();
}
