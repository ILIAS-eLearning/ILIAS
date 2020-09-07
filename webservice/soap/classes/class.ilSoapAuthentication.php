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
* soap server
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @package ilias
*/
include_once 'Auth/Auth.php';
include_once './Services/Authentication/classes/class.ilBaseAuthentication.php';

class ilSoapAuthentication extends ilBaseAuthentication
{
    public $soap_check = true;


    /**
     * Constructor
     */
    public function __construct()
    {
        // First unset all cookie inforamtions
        unset($_COOKIE[session_name()]);

        parent::__construct();
        $this->__setMessageCode('Client');
    }

    public function disableSoapCheck()
    {
        $this->soap_check = false;
    }

    public function authenticate()
    {
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
        if (!$this->__checkAgreement('local')) {
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


        $this->auth->start();

        if (!$this->auth->getAuth()) {
            $this->__getAuthStatus();

            return false;
        }

        $this->setSid(session_id());

        return true;
    }
    
    /**
     * Check if user agreement is accepted
     *
     * @access protected
     * @param string auth_mode local,ldap or cas
     *
     */
    protected function __checkAgreement($a_auth_mode)
    {
        include_once('./Services/User/classes/class.ilObjUser.php');
        include_once('./Services/Administration/classes/class.ilSetting.php');

        $GLOBALS['DIC']['ilSetting'] = new ilSetting();

        if (!$login = ilObjUser::_checkExternalAuthAccount($a_auth_mode, $this->getUsername())) {
            return true;
        }

        if (ilObjUser::hasUserToAcceptTermsOfService($login)) {
            $this->__setMessage('User agreement no accepted.');
            return false;
        }

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
        if (!$this->__checkClientEnabled()) {
            $this->__setMessage('Client disabled.');
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
        $this->auth->start();
        if (!$this->auth->getAuth()) {
            $this->__setMessage('Session not valid');

            return false;
        }

        return true;
    }

    // PRIVATE
    public function __checkSOAPEnabled()
    {
        include_once './Services/Database/classes/MDB2/class.ilDB.php';

        //$db = new ilDB($this->dsn);
        $ilDB = $this->db;
        $ilDB->connect();

        $GLOBALS['DIC']["ilDB"] = $ilDB;
        include_once './Services/Administration/classes/class.ilSetting.php';
        $set = new ilSetting();
        return ($set->get("soap_user_administration") == 1);

        /*$query = "SELECT * FROM set tings WHERE keyword = 'soap_user_administration' AND value = 1";

        $res = $db->query($query);

        return $res->numRows() ? true : false;*/
    }
    
    public function __checkClientEnabled()
    {
        if (is_object($this->ini) and $this->ini->readVariable('client', 'access')) {
            return true;
        }
        return false;
    }
}
