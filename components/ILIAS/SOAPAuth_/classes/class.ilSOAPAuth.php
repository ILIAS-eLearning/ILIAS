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
 */

declare(strict_types=1);

include_once("./webservice/soap/lib/nusoap.php");

class ilSOAPAuth
{
    public static function testConnection(string $a_ext_uid, string $a_soap_pw, bool $a_new_user): string
    {
        global $ilSetting;

        $settings = $ilSetting->getAll();

        $server_hostname = (string) ($settings["soap_auth_server"] ?? '');
        $server_port = (int) ($settings["soap_auth_port"] ?? 0);
        $server_uri = (string) ($settings["soap_auth_uri"] ?? '');
        $namespace = (string) ($settings["soap_auth_namespace"] ?? '');
        $use_dotnet = (bool) ($settings["soap_auth_use_dotnet"] ?? false);
        $uri = "http://";
        if (isset($settings["soap_auth_use_https"]) && $settings["soap_auth_use_https"]) {
            $uri = "https://";
        }

        $uri .= $server_hostname;

        if ($server_port > 0) {
            $uri .= ":" . $server_port;
        }

        if ($server_uri !== "") {
            $uri .= "/" . $server_uri;
        }

        require_once './webservice/soap/lib/nusoap.php';
        $soap_client = new nusoap_client($uri);
        if ($err = $soap_client->getError()) {
            return "SOAP Authentication Initialisation Error: " . $err;
        }

        $soapAction = "";
        $nspref = "";
        if ($use_dotnet) {
            $soapAction = $namespace . "/isValidSession";
            $nspref = "ns1:";
        }

        $valid = $soap_client->call(
            'isValidSession',
            [
                $nspref . 'ext_uid' => $a_ext_uid,
                $nspref . 'soap_pw' => $a_soap_pw,
                $nspref . 'new_user' => $a_new_user
            ],
            $namespace,
            $soapAction
        );

        return
            "<br>== Request ==" .
            '<br><pre>' . htmlspecialchars(str_replace("\" ", "\"\n ", str_replace(">", ">\n", $soap_client->request)), ENT_QUOTES) . '</pre><br>' .
            "<br>== Response ==" .
            "<br>Valid: -" . $valid["valid"] . "-" .
            '<br><pre>' . htmlspecialchars(str_replace("\" ", "\"\n ", str_replace(">", ">\n", $soap_client->response)), ENT_QUOTES) . '</pre>';
    }
}
