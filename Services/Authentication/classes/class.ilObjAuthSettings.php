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
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
* Class ilObjAuthSettings
*
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @version $Id$
*
* @extends ilObject
*/

class ilObjAuthSettings extends ilObject
{
    /**
    * Constructor
    * @access	public
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        $this->type = "auth";
        parent::__construct($a_id, $a_call_by_reference);
    }
    
    public function checkAuthLDAP()
    {
        $settings = $this->ilias->getAllSettings();
        
        if (!$settings["ldap_server"] or !$settings["ldap_basedn"] or !$settings["ldap_port"]) {
            return false;
        }
        
        $this->ilias->setSetting('ldap_active', true);
        
        return true;
    }
    
    public function checkAuthSHIB()
    {
        $settings = $this->ilias->getAllSettings();

        if (!$settings["shib_hos_type"] or !$settings["shib_user_default_role"] or !$settings["shib_login"]
             or !$settings["shib_firstname"] or !$settings["shib_lastname"]) {
            return false;
        }

        $this->ilias->setSetting('shibboleth_active', true);

        return true;
    }
    
    public function checkAuthRADIUS()
    {
        $settings = $this->ilias->getAllSettings();
        
        if (!$settings["radius_server"] or !$settings["radius_shared_secret"] or !$settings["radius_port"]) {
            return false;
        }
        
        $this->ilias->setSetting('radius_active', true);
        
        return true;
    }

    public function checkAuthScript()
    {
        $settings = $this->ilias->getAllSettings();
        
        if (!$settings["auth_script_name"]) {
            return false;
        }
        
        $this->ilias->setSetting('script_active', true);

        return true;
    }

    /**
    * update object data
    *
    * @access	public
    * @return	boolean
    */
    public function update()
    {
        if (!parent::update()) {
            return false;
        }

        // put here object specific stuff
        
        return true;
    }
    

    /**
    * delete object and all related data
    *
    * @access	public
    * @return	boolean	true if all object data were removed; false if only a references were removed
    */
    public function delete()
    {
        // always call parent delete function first!!
        if (!parent::delete()) {
            return false;
        }
        
        //put here your module specific stuff
        
        return true;
    }
} // END class.ilObjAuthSettings
