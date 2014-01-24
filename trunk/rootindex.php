<?php
// BEGIN WebDAV
// This page is required to support Microsoft WebDAV clients with ILIAS.

// Note: Put this page at the root of the website and rename it to index.php.

// We MUST block WebDAV requests on the root page of the Web-Server
// in order to make the "Microsoft WebDAV MiniRedir" client work with ILIAS.
// If we don't do this, the client will display a non-working login-dialog.

if ($_SERVER['REQUEST_METHOD'] == 'PROPFIND'
|| $_SERVER['REQUEST_METHOD'] == 'OPTIONS')
{
	// Block WebDAV Requests from "Microsoft WebDAV MiniRedir" client.
	$status = '404 Not Found';
	header("HTTP/1.1 $status");
	header("X-WebDAV-Status: $status", true);
	exit;
} else {
	// Redirect browser to the ILIAS Start page
	header("Location: /ilias/index.php");
	exit;
}
// END WebDAV
?>
