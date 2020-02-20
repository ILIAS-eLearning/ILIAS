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


/**
* SOAP dummy authentication server
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package ilias
*/

include_once './webservice/soap/lib/nusoap.php';

/**
* isValidSession
*/
function isValidSession($ext_uid, $soap_pw, $new_user)
{
    $ret = array(
        "valid" => false,
        "firstname" => "",
        "lastname" => "",
        "email" => "");
        
    // generate some dummy values
    if ($new_user) {
        $ret["firstname"] = "first " . $ext_uid;
        $ret["lastname"] = "last " . $ext_uid;
        $ret["email"] = $ext_uid . "@de.de";
    }
    
    // return valid authentication if user id equals soap password
    if ($ext_uid == $soap_pw) {
        $ret["valid"] = true;
    } else {
        $ret["valid"] = false;
    }
    
    return $ret;
}


class ilSoapDummyAuthServer
{
    /*
     * @var object Nusoap-Server
     */
    public $server = null;


    public function __construct($a_use_wsdl = true)
    {
        define('SERVICE_NAME', 'ILIAS SOAP Dummy Authentication Server');
        define('SERVICE_NAMESPACE', 'urn:ilSoapDummyAuthServer');
        define('SERVICE_STYLE', 'rpc');
        define('SERVICE_USE', 'encoded');

        $this->server = new soap_server();

        if ($a_use_wsdl) {
            $this->__enableWSDL();
        }

        $this->__registerMethods();
    }

    public function start()
    {
        $postdata = file_get_contents("php://input");
        $this->server->service($postdata);
        exit();
    }

    // PRIVATE
    public function __enableWSDL()
    {
        $this->server->configureWSDL(SERVICE_NAME, SERVICE_NAMESPACE);

        return true;
    }


    public function __registerMethods()
    {

        // Add useful complex types. E.g. array("a","b") or array(1,2)
        $this->server->wsdl->addComplexType(
            'intArray',
            'complexType',
            'array',
            '',
            'SOAP-ENC:Array',
            array(),
            array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'xsd:int[]')),
            'xsd:int'
        );


        $this->server->wsdl->addComplexType(
            'stringArray',
            'complexType',
            'array',
            '',
            'SOAP-ENC:Array',
            array(),
            array(array('ref'=>'SOAP-ENC:arrayType','wsdl:arrayType'=>'xsd:string[]')),
            'xsd:string'
        );

        // isValidSession()
        $this->server->register(
            'isValidSession',
            array('ext_uid' => 'xsd:string',
                                      'soap_pw' => 'xsd:string',
                                      'new_user' => 'xsd:boolean'),
            array('valid' => 'xsd:boolean',
                                    'firstname' => 'xsd:string',
                                    'lastname' => 'xsd:string',
                                    'email' => 'xsd:string'),
            SERVICE_NAMESPACE,
            SERVICE_NAMESPACE . '#isValidSession',
            SERVICE_STYLE,
            SERVICE_USE,
            'Dummy Session Validation'
        );

        return true;
    }
}
