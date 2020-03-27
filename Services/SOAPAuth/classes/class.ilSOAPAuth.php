<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/


include_once("Auth/Auth.php");
include_once("./webservice/soap/lib/nusoap.php");

/**
* Class SOAPAuth
*
* SOAP Authentication class.
*
*/
class ilSOAPAuth extends Auth
{
    public $valid = array();
    
    /**
    * Constructor
    * @access	public
    */
    
    /**
    * Test connection with values of soap auth administration settings
    */
    public static function testConnection($a_ext_uid, $a_soap_pw, $a_new_user)
    {
        global $ilSetting;
        
        $settings = $ilSetting->getAll();
        
        $server_hostname = $settings["soap_auth_server"];
        $server_port = (int) $settings["soap_auth_port"];
        $server_uri = $settings["soap_auth_uri"];
        $namespace = $settings["soap_auth_namespace"];
        $use_dotnet = $settings["soap_auth_use_dotnet"];
        if ($settings["soap_auth_use_https"]) {
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
} // END class.ilSOAPAuth
