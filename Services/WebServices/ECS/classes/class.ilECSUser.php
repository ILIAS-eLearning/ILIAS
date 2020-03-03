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
* Stores relevant user data.
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ilCtrl_Calls
* @ingroup ServicesWebServicesECS
*/

class ilECSUser
{
    protected $source;
    
    public $login;
    public $email;
    public $firstname;
    public $lastname;
    public $institution;
    public $uid_hash;

    /**
     * Constructor
     * @param mixed ilObjUser or encoded json string
     * @access public
     *
     */
    public function __construct($a_data)
    {
        $this->source = $a_data;
        if (is_object($a_data)) {
            $this->loadFromObject();
        } elseif (is_array($a_data)) {
            $this->loadFromGET();
        } else {
            $this->loadFromJSON();
        }
    }
    
    /**
     * get login
     *
     * @access public
     *
     */
    public function getLogin()
    {
        return $this->login;
    }
    
    /**
     * get firstname
     *
     * @access public
     *
     */
    public function getFirstname()
    {
        return $this->firstname;
    }
    
    /**
     * getLastname
     *
     * @access public
     */
    public function getLastname()
    {
        return $this->lastname;
    }
    
    /**
     * get email
     *
     * @access public
     */
    public function getEmail()
    {
        return $this->email;
    }
    /**
     * get institution
     *
     * @access public
     *
     */
    public function getInstitution()
    {
        return $this->institution;
    }
    
    /**
     * get Email
     *
     * @access public
     *
     */
    public function getImportId()
    {
        return $this->uid_hash;
    }
    
    /**
     * load from object
     *
     * @access public
     *
     */
    public function loadFromObject()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        
        $this->login = $this->source->getLogin();
        $this->firstname = $this->source->getFirstname();
        $this->lastname = $this->source->getLastname();
        $this->email = $this->source->getEmail();
        $this->institution = $this->source->getInstitution();
        
        $this->uid_hash = 'il_' . $ilSetting->get('inst_id', 0) . '_usr_' . $this->source->getId();
    }
    
    /**
     * load from json
     *
     * @access public
     *
     */
    public function loadFromJSON()
    {
        $this->source = json_decode(urldecode($this->source));
        
        $this->login = $this->source->login();
        $this->firstname = $this->source->firstname();
        $this->lastname = $this->source->lastname();
        $this->email = $this->source->email();
        $this->institution = $this->source->institution();
        
        $this->uid_hash = $this->source->uid_hash;
    }
    
    /**
     * load user data from GET parameters
     *
     * @access public
     *
     */
    public function loadFromGET()
    {
        $this->login = ilUtil::stripSlashes(urldecode($_GET['ecs_login']));
        $this->firstname = ilUtil::stripSlashes(urldecode($_GET['ecs_firstname']));
        $this->lastname = ilUtil::stripSlashes(urldecode($_GET['ecs_lastname']));
        $this->email = ilUtil::stripSlashes(urldecode($_GET['ecs_email']));
        $this->institution = ilUtil::stripSlashes(urldecode($_GET['ecs_institution']));
        
        if ($_GET['ecs_uid_hash']) {
            $this->uid_hash = ilUtil::stripSlashes(urldecode($_GET['ecs_uid_hash']));
        } elseif ($_GET['ecs_uid']) {
            $this->uid_hash = ilUtil::stripSlashes(urldecode($_GET['ecs_uid']));
        }
    }
    
    /**
     *
     *
     * @access public
     * @param
     *
     */
    public function toJSON()
    {
        return urlencode(json_encode($this));
    }
    
    /**
     * get GET parameter string
     *
     * @access public
     *
     */
    public function toGET()
    {
        return '&ecs_login=' . urlencode((string) $this->login) .
            '&ecs_firstname=' . urlencode((string) $this->firstname) .
            '&ecs_lastname=' . urlencode((string) $this->lastname) .
            '&ecs_email=' . urlencode((string) $this->email) .
            '&ecs_institution=' . urlencode((string) $this->institution) .
            '&ecs_uid_hash=' . urlencode((string) $this->uid_hash);
        '&ecs_uid=' . urlencode((string) $this->uid_hash);
    }
    
    /**
     * Concatenate all attributes to one string
     * @return string
     */
    public function toREALM()
    {
        return
            (string) $this->login . '' .
            (string) $this->firstname . '' .
            (string) $this->lastname . '' .
            (string) $this->email . '' .
            (string) $this->institution . '' .
            (string) $this->uid_hash;
    }
}
