<?php declare(strict_types=1);
/*
 +-----------------------------------------------------------------------------+
 | ILIAS open source                                                           |
 +-----------------------------------------------------------------------------+
 | Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
 * soap server
 * Base class for all SOAP registered methods. E.g ilSoapUserAdministration
 * @author Stefan Meyer <meyer@leifos.com>
 */

include_once './webservice/soap/lib/nusoap.php';
include_once("./Services/Authentication/classes/class.ilAuthUtils.php");        // to get auth mode constants

class ilSoapAdministration
{
    public const NUSOAP = 1;
    public const PHP5 = 2;

    protected bool $soap_check = true;
    protected string $message = '';
    protected string $message_code = '';

    /**
     * Defines type of error handling (PHP5 || NUSOAP)
     */
    public int $error_method;

    public function __construct(bool $use_nusoap = true)
    {
        if (
            defined('IL_SOAPMODE') &&
            defined('IL_SOAPMODE_NUSOAP') &&
            IL_SOAPMODE == IL_SOAPMODE_NUSOAP
        ) {
            $this->error_method = self::NUSOAP;
        } else {
            $this->error_method = self::PHP5;
        }

        $this->__initAuthenticationObject();
    }

    protected function __checkSession(string $sid) : bool
    {
        global $DIC;

        $ilUser = $DIC->user();

        list($sid, $client) = $this->__explodeSid($sid);

        if (!strlen($sid)) {
            $this->__setMessage('No session id given');
            $this->__setMessageCode('Client');
            return false;
        }
        if (!$client) {
            $this->__setMessage('No client given');
            $this->__setMessageCode('Client');
            return false;
        }

        if (!$GLOBALS['DIC']['ilAuthSession']->isAuthenticated()) {
            $this->__setMessage('Session invalid');
            $this->__setMessageCode('Client');
            return false;
        }

        if ($ilUser->hasToAcceptTermsOfService()) {
            $this->__setMessage('User agreement no accepted.');
            $this->__setMessageCode('Server');
            return false;
        }

        if ($this->soap_check) {
            $set = new ilSetting();
            $this->__setMessage('SOAP is not enabled in ILIAS administration for this client');
            $this->__setMessageCode('Server');
            return $set->get("soap_user_administration") == 1;
        }

        return true;
    }

    protected function __explodeSid(string $sid) : array
    {
        $exploded = explode('::', $sid);

        return is_array($exploded) ? $exploded : array('sid' => '', 'client' => '');
    }

    protected function __setMessage(string $a_str) : void
    {
        $this->message = $a_str;
    }

    public function __getMessage() : string
    {
        return $this->message;
    }

    public function __appendMessage(string $a_str) : void
    {
        $this->message .= isset($this->message) ? ' ' : '';
        $this->message .= $a_str;
    }

    public function __setMessageCode(string $a_code) : void
    {
        $this->message_code = $a_code;
    }

    public function __getMessageCode() : string
    {
        return $this->message_code;
    }

    protected function initAuth(string $sid) : void
    {
        list($sid, $client) = $this->__explodeSid($sid);
        define('CLIENT_ID', $client);
        $_COOKIE['ilClientId'] = $client;
        $_COOKIE[session_name()] = $sid;
    }

    protected function initIlias() : void
    {
        if (ilContext::getType() == ilContext::CONTEXT_SOAP) {
            try {
                require_once("Services/Init/classes/class.ilInitialisation.php");
                ilInitialisation::reinitILIAS();
            } catch (Exception $e) {
                // #10608
                // no need to do anything here, see __checkSession() below
            }
        }
    }

    protected function __initAuthenticationObject() : void
    {
        include_once './Services/Authentication/classes/class.ilAuthFactory.php';
        ilAuthFactory::setContext(ilAuthFactory::CONTEXT_SOAP);
    }

    /**
     * @param string $a_message
     * @param string|int $a_code
     * @return soap_fault|SoapFault|null
     */
    protected function __raiseError(string $a_message, $a_code)
    {
        switch ($this->error_method) {
            case self::NUSOAP:
                return new soap_fault($a_code, '', $a_message);
            case self::PHP5:
                return new SoapFault($a_code, $a_message);
        }
        return null;
    }

    public function isFault($object)
    {
        switch ($this->error_method) {
            case self::NUSOAP:
                return $object instanceof soap_fault;
            case self::PHP5:
                return $object instanceof SoapFault;
        }
        return true;
    }

    /**
     * check access for ref id: expected type, permission, return object instance if returnobject is true
     */
    protected function checkObjectAccess(
        int $ref_id,
        array $expected_type,
        string $permission,
        bool $returnObject = false
    ) {
        global $DIC;

        $rbacsystem = $DIC->rbac()->system();
        if (!is_numeric($ref_id)) {
            return $this->__raiseError(
                'No valid id given.',
                'Client'
            );
        }
        if (!ilObject::_exists($ref_id, true)) {
            return $this->__raiseError(
                'No object for id.',
                'CLIENT_OBJECT_NOT_FOUND'
            );
        }

        if (ilObject::_isInTrash($ref_id)) {
            return $this->__raiseError(
                'Object is already trashed.',
                'CLIENT_OBJECT_DELETED'
            );
        }

        $type = ilObject::_lookupType(ilObject::_lookupObjId($ref_id));
        if (!in_array($type, $expected_type)) {
            return $this->__raiseError("Wrong type $type for id. Expected: " . join(",", $expected_type),
                'CLIENT_OBJECT_WRONG_TYPE');
        }
        if (!$rbacsystem->checkAccess($permission, $ref_id, $type)) {
            return $this->__raiseError('Missing permission $permission for type $type.',
                'CLIENT_OBJECT_WRONG_PERMISSION');
        }
        if ($returnObject) {
            try {
                return ilObjectFactory::getInstanceByRefId($ref_id);
            } catch (ilObjectNotFoundException $e) {
                return $this->__raiseError('No valid ref_id given', 'Client');
            }
        }
        return $type;
    }

    public function getInstallationInfoXML() : string
    {
        include_once "Services/Context/classes/class.ilContext.php";
        ilContext::init(ilContext::CONTEXT_SOAP_WITHOUT_CLIENT);

        require_once("Services/Init/classes/class.ilInitialisation.php");
        ilInitialisation::initILIAS();

        $clientdirs = glob(ILIAS_WEB_DIR . "/*", GLOB_ONLYDIR);
        require_once("webservice/soap/classes/class.ilSoapInstallationInfoXMLWriter.php");
        $writer = new ilSoapInstallationInfoXMLWriter();
        $writer->start();
        if (is_array($clientdirs)) {
            foreach ($clientdirs as $clientdir) {
                if (is_object($clientInfo = $this->getClientInfo(null, $clientdir))) {
                    $writer->addClient($clientInfo);
                }
            }
        }
        $writer->end();
        return $writer->getXML();
    }

    /**
     * @param string $clientid
     * @return string|soap_fault|SoapFault|string|null
     */
    public function getClientInfoXML(string $clientid)
    {
        include_once "Services/Context/classes/class.ilContext.php";
        ilContext::init(ilContext::CONTEXT_SOAP_WITHOUT_CLIENT);

        require_once("Services/Init/classes/class.ilInitialisation.php");
        ilInitialisation::initILIAS();

        $clientdir = ILIAS_WEB_DIR . "/" . $clientid;
        require_once("webservice/soap/classes/class.ilSoapInstallationInfoXMLWriter.php");
        $writer = new ilSoapInstallationInfoXMLWriter();
        $writer->start();
        if (is_object($client = $this->getClientInfo(null, $clientdir))) {
            $writer->addClient($client);
        } else {
            return $this->__raiseError("Client ID $clientid does not exist!", 'Client');
        }
        $writer->end();
        return $writer->getXML();
    }

    private function getClientInfo($init, $client_dir)
    {
        global $DIC;

        $ini_file = "./" . $client_dir . "/client.ini.php";

        // get settings from ini file
        require_once("./Services/Init/classes/class.ilIniFile.php");

        $ilClientIniFile = new ilIniFile($ini_file);
        $ilClientIniFile->read();
        if ($ilClientIniFile->ERROR != "") {
            return false;
        }
        $client_id = $ilClientIniFile->readVariable('client', 'name');
        if ($ilClientIniFile->variableExists('client', 'expose')) {
            $client_expose = $ilClientIniFile->readVariable('client', 'expose');
            if ($client_expose == "0") {
                return false;
            }
        }

        // build dsn of database connection and connect
        $ilDB = ilDBWrapperFactory::getWrapper(
            $ilClientIniFile->readVariable("db", "type")
        );
        $ilDB->initFromIniFile($ilClientIniFile);
        if ($ilDB->connect(true)) {
            unset($DIC['ilDB']);
            $DIC['ilDB'] = $ilDB;

            require_once("Services/Administration/classes/class.ilSetting.php");

            $settings = new ilSetting();
            unset($DIC["ilSetting"]);
            $DIC["ilSetting"] = $settings;
            // workaround to determine http path of client
            define("IL_INST_ID", (int) $settings->get("inst_id", '0'));
            $settings->access = $ilClientIniFile->readVariable("client", "access");
            $settings->description = $ilClientIniFile->readVariable("client", "description");
            $settings->session = min((int) ini_get("session.gc_maxlifetime"),
                (int) $ilClientIniFile->readVariable("session", "expire"));
            $settings->language = $ilClientIniFile->readVariable("language", "default");
            $settings->clientid = basename($client_dir); //pathinfo($client_dir, PATHINFO_FILENAME);
            $settings->default_show_users_online = $settings->get("show_users_online");
            $settings->default_hits_per_page = $settings->get("hits_per_page");
            $skin = $ilClientIniFile->readVariable("layout", "skin");
            $style = $ilClientIniFile->readVariable("layout", "style");
            $settings->default_skin_style = $skin . ":" . $style;
            return $settings;
        }
        return null;
    }
}
