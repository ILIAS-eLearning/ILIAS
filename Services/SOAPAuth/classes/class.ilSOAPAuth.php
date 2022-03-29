<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

include_once("./webservice/soap/lib/nusoap.php");

/**
* Class SOAPAuth
*
* SOAP Authentication class.
*
*/
class ilSOAPAuth
{
    /**
    * Test connection with values of soap auth administration settings
    */
    public static function testConnection($a_ext_uid, $a_soap_pw, $a_new_user) : string
    {
        global $ilSetting;

        $settings = $ilSetting->getAll();

        $server_hostname = $settings["soap_auth_server"];
        $server_port = (int) $settings["soap_auth_port"];
        $server_uri = $settings["soap_auth_uri"];
        $namespace = $settings["soap_auth_namespace"];
        $use_dotnet = isset($settings["soap_auth_use_dotnet"]) ?
            $settings["soap_auth_use_dotnet"] : false;
        if (isset($settings["soap_auth_use_https"]) && $settings["soap_auth_use_https"]) {
            $uri = "https://";
        } else {
            $uri = "http://";
        }

        $uri .= $server_hostname;

        if ($server_port > 0) {
            $uri .= ":" . $server_port;
        }

        if ($server_uri != "") {
            $uri .= "/" . $server_uri;
        }

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
            array($nspref . 'ext_uid' => $a_ext_uid,
                $nspref . 'soap_pw' => $a_soap_pw,
                $nspref . 'new_user' => $a_new_user),
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
