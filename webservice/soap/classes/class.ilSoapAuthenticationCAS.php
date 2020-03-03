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
* this class authenticates via CAS for a soap request
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package ilias
*/

include_once './webservice/soap/classes/class.ilSoapAuthentication.php';

class ilSoapAuthenticationCAS extends ilSOAPAuthentication
{
    //
    // inherited from ilSOAPAuthentication
    //
    /*
        function disableSoapCheck()
        function authenticate()
        function validateSession()
        function __checkSOAPEnabled()
    */
    
    //
    // inherited from ilBaseAuthentication via ilSOAPAuthentication
    //
    /*
        function setClient($a_client)
        function getClient()
        function setUsername($a_username)
        function getUsername()
        function setPassword($a_password)		// not needed
        function getPassword()					// not needed
        function setSid($a_sid)
        function getSid()
        function getMessage()
        function getMessageCode()
        function __setMessage($a_message)
        function __setMessageCode($a_message_code)
        function setPasswordType($a_type)
        function getPasswordType()
        function start()
        function logout()
        function __buildDSN()
        function __setSessionSaveHandler()
        function __getAuthStatus()
    */

    // set ticket
    public function setPT($a_pt)
    {
        $this->pt = $a_pt;
        $_GET['ticket'] = $a_pt;
    }
    public function getPT()
    {
        return $this->pt;
    }

    public function authenticate()
    {
        include_once("./Services/Init/classes/class.ilInitialisation.php");
        $this->init = new ilInitialisation();
        $this->init->requireCommonIncludes();
        //$init->initSettings();
        
        
        if (!$this->getClient()) {
            $this->__setMessage('No client given');
            return false;
        }
        
        if (!$this->getUsername()) {
            $this->__setMessage('No username given');
            return false;
        }
        // Read ilias ini
        if (!$this->__buildDSN()) {
            $this->__setMessage('Error building dsn/Wrong client Id?');
            return false;
        }
        
        if (!$this->__setSessionSaveHandler()) {
            return false;
        }
        if (!$this->__checkAgreement('cas')) {
            return false;
        }

        if (!$this->__buildAuth()) {
            return false;
        }

        if ($this->soap_check and !$this->__checkSOAPEnabled()) {
            $this->__setMessage('SOAP is not enabled in ILIAS administration for this client');
            $this->__setMessageCode('Server');

            return false;
        }

        // check whether authentication is valid
        //if (!$this->auth->checkCASAuth())
        if (!phpCAS::checkAuthentication()) {
            $this->__setMessage('ilSOAPAuthenticationCAS::authenticate(): No valid CAS authentication.');
            return false;
        }

        $this->auth->forceCASAuth();

        if ($this->getUsername() != $this->auth->getCASUser()) {
            $this->__setMessage('ilSOAPAuthenticationCAS::authenticate(): SOAP CAS user does not match to ticket user.');
            return false;
        }

        include_once('./Services/User/classes/class.ilObjUser.php');
        $local_user = ilObjUser::_checkExternalAuthAccount("cas", $this->auth->getCASUser());
        if ($local_user == "") {
            $this->__setMessage('ilSOAPAuthenticationCAS::authenticate(): SOAP CAS user authenticated but not existing in ILIAS user database.');
            return false;
        }
                
        /*
        $init->initIliasIniFile();
        $init->initSettings();
        $ilias = new ILIAS();
        $GLOBALS['DIC']['ilias'] =& $ilias;*/

        $this->auth->start();

        if (!$this->auth->getAuth()) {
            $this->__getAuthStatus();

            return false;
        }

        $this->setSid(session_id());

        return true;
    }


    public function validateSession()
    {
        if (!$this->getClient()) {
            $this->__setMessage('No client given');
            return false;
        }
        if (!$this->getSid()) {
            $this->__setMessage('No session id given');
            return false;
        }

        if (!$this->__buildDSN()) {
            $this->__setMessage('Error building dsn');
            return false;
        }
        if (!$this->__setSessionSaveHandler()) {
            return false;
        }
        if (!$this->__buildAuth()) {
            return false;
        }
        if ($this->soap_check and !$this->__checkSOAPEnabled()) {
            $this->__setMessage('SOAP is not enabled in ILIAS administration for this client');
            $this->__setMessageCode('Server');

            return false;
        }
        
        // check whether authentication is valid
        if (!$this->auth->checkCASAuth()) {
            $this->__setMessage('ilSOAPAuthenticationCAS::authenticate(): No valid CAS authentication.');
            return false;
        }
        $this->auth->forceCASAuth();

        $this->auth->start();
        if (!$this->auth->getAuth()) {
            $this->__setMessage('Session not valid');

            return false;
        }

        return true;
    }

    public function __buildAuth()
    {
        if (!is_object($this->db)) {
            require_once("./Services/Database/classes/class.ilDBWrapperFactory.php");
            $ilDB = ilDBWrapperFactory::getWrapper();
            $ilDB->initFromIniFile();
            $ilDB->connect();
            $this->db = $ilDB;
        }

        $GLOBALS['DIC']["ilDB"] = $this->db;
        $this->init->initSettings();
        
        $this->init->buildHTTPPath();
        include_once './Services/Administration/classes/class.ilSetting.php';
        $set = new ilSetting();

        /*$query = "SELECT * FROM sett ings WHERE ".
            " keyword = ".$this->db->quote("cas_server")." OR ".
            " keyword = ".$this->db->quote("cas_port")." OR ".
            " keyword = ".$this->db->quote("cas_uri");
        $res = $this->db->query($query);
        $cas_set = array();
        while ($rec = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC))
        {
            $cas_set[$rec["keyword"]] = $rec["value"];
        }*/
        $cas_set["cas_server"] = $set->get("cas_server");
        $cas_set["cas_port"] = $set->get("cas_port");
        $cas_set["cas_uri"] = $set->get("cas_uri");

        $auth_params = array(
            "server_version" => CAS_VERSION_2_0,
            "server_hostname" => $cas_set["cas_server"],
            "server_port" => $cas_set["cas_port"],
            "server_uri" => $cas_set["cas_uri"]);

        include_once("Services/CAS/classes/class.ilCASAuth.php");
        $this->auth = new ilCASAuth($auth_params);
        
        // HTTP path will return full path to server.php directory
        phpCAS::setFixedServiceURL(ILIAS_HTTP_PATH . "/webservice/soap/server.php");

        return true;
    }
}
