<?php

declare(strict_types=1);

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

    // hardcoded namespace
    // attention: maybe a problem with composer v2 / psr4 autoload  requires exact matching of namespace and parent folder name?

    namespace XapiProxy;

    // hardcoded context for better performance
    // $plugin = file_exists(__DIR__."/plugin.php"); // for testing
    $plugin = false;

    /**
     * handle preflight
     */
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "OPTIONS") {
        header('HTTP/1.1 204 No Content');
        header('Access-Control-Allow-Origin: ' . $_SERVER["HTTP_ORIGIN"]);
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: X-Experience-API-Version,Accept,Authorization,Etag,Cache-Control,Content-Type,DNT,If-Modified-Since,Keep-Alive,Origin,User-Agent,X-Mx-ReqToken,X-Requested-With');
        exit;
    }

    /**
     * handle basic auth
     */
    if (!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])) {
        $client = $_SERVER['PHP_AUTH_USER'];
        $token = $_SERVER['PHP_AUTH_PW'];
    } elseif (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        $basicAuth = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
        $client = $basicAuth[0];
        $token = $basicAuth[1];
    } else {
        header('HTTP/1.1 401 Authorization Required');
        header('Access-Control-Allow-Origin: ' . $_SERVER["HTTP_ORIGIN"]);
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: X-Experience-API-Version,Accept,Authorization,Etag,Cache-Control,Content-Type,DNT,If-Modified-Since,Keep-Alive,Origin,User-Agent,X-Mx-ReqToken,X-Requested-With');
        exit;
    }

    /**
     * handle path context
     */

    if ($plugin) {
        /**
         *
         * required for Plugin in ILIAS 5.x
        */
        //require_once __DIR__.'/classes/XapiProxy/vendor/autoload.php';

        chdir("../../../../../../../");
    } else {
        chdir("../../");
    }
    require_once("Services/Init/classes/class.ilInitialisation.php");
    DataService::initIlias($client);
    $dic = $GLOBALS['DIC'];

    $xapiproxy = new XapiProxy($client, $token, $plugin);

    /**
     * handle Lrs Init
     */
    try {
        $xapiproxy->initLrs();
    } catch (\Exception $e) {
        $xapiproxy->log()->error($dic['xapiproxy']->getLogMessage($e->getMessage()));
    }
    $req = new XapiProxyRequest($xapiproxy);
    $resp = new XapiProxyResponse($xapiproxy);

    $xapiproxy->setXapiProxyRequest($req);
    $xapiproxy->setXapiProxyResponse($resp);

    $req->handle();
