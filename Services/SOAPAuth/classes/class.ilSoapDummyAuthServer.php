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

/**
 * SOAP dummy authentication server
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */

include_once './webservice/soap/lib/nusoap.php';

function isValidSession(string $ext_uid, string $soap_pw, bool $new_user) : array
{
    $ret = [
        "firstname" => "",
        "lastname" => "",
        "email" => ""
    ];

    // generate some dummy values
    if ($new_user) {
        $ret["firstname"] = "first " . $ext_uid;
        $ret["lastname"] = "last " . $ext_uid;
        $ret["email"] = $ext_uid . "@de.de";
    }

    // return valid authentication if user id equals soap password
    if ($ext_uid === $soap_pw) {
        $ret["valid"] = true;
    } else {
        $ret["valid"] = false;
    }

    return $ret;
}

class ilSoapDummyAuthServer
{
    public ?soap_server $server = null;


    public function __construct($a_use_wsdl = true)
    {
        define('SERVICE_NAME', 'ILIAS SOAP Dummy Authentication Server');
        define('SERVICE_NAMESPACE', 'urn:ilSoapDummyAuthServer');
        define('SERVICE_STYLE', 'rpc');
        define('SERVICE_USE', 'encoded');

        $this->server = new soap_server();

        if ($a_use_wsdl) {
            $this->enableWSDL();
        }

        $this->registerMethods();
    }

    public function start() : void
    {
        $postdata = file_get_contents("php://input");
        $this->server->service($postdata);
        exit();
    }

    public function enableWSDL() : bool
    {
        $this->server->configureWSDL(SERVICE_NAME, SERVICE_NAMESPACE);

        return true;
    }

    public function registerMethods() : bool
    {

        // Add useful complex types. E.g. array("a","b") or array(1,2)
        $this->server->wsdl->addComplexType(
            'intArray',
            'complexType',
            'array',
            '',
            'SOAP-ENC:Array',
            [],
            [['ref' => 'SOAP-ENC:arrayType', 'wsdl:arrayType' => 'xsd:int[]']],
            'xsd:int'
        );


        $this->server->wsdl->addComplexType(
            'stringArray',
            'complexType',
            'array',
            '',
            'SOAP-ENC:Array',
            [],
            [['ref' => 'SOAP-ENC:arrayType', 'wsdl:arrayType' => 'xsd:string[]']],
            'xsd:string'
        );

        // isValidSession()
        $this->server->register(
            'isValidSession',
            [
                'ext_uid' => 'xsd:string',
                'soap_pw' => 'xsd:string',
                'new_user' => 'xsd:boolean'
            ],
            [
                'valid' => 'xsd:boolean',
                'firstname' => 'xsd:string',
                'lastname' => 'xsd:string',
                'email' => 'xsd:string'
            ],
            SERVICE_NAMESPACE,
            SERVICE_NAMESPACE . '#isValidSession',
            SERVICE_STYLE,
            SERVICE_USE,
            'Dummy Session Validation'
        );

        return true;
    }
}
