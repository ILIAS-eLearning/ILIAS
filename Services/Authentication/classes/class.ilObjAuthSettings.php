<?php declare(strict_types=1);

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
 *
 *********************************************************************/

/**
* @author Sascha Hofmann <saschahofmann@gmx.de>
*/
class ilObjAuthSettings extends ilObject
{
    /**
    * @param	integer	reference_id or object_id
    * @param	boolean	treat the id as reference_id (true) or object_id (false)
    */
    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        $this->type = "auth";
        parent::__construct($a_id, $a_call_by_reference);
    }

    public function checkAuthLDAP() : bool
    {
        $settings = $this->ilias->getAllSettings();

        if (!$settings["ldap_server"] || !$settings["ldap_basedn"] || !$settings["ldap_port"]) {
            return false;
        }

        $this->ilias->setSetting('ldap_active', "1");

        return true;
    }

    public function checkAuthSHIB() : bool
    {
        $settings = $this->ilias->getAllSettings();

        if (!$settings["shib_hos_type"] || !isset($settings["shib_user_default_role"]) || !$settings["shib_login"]
            || !$settings["shib_firstname"] || !$settings["shib_lastname"]) {
            return false;
        }

        $this->ilias->setSetting('shibboleth_active', "1");

        return true;
    }

    public function checkAuthScript() : bool
    {
        $settings = $this->ilias->getAllSettings();

        if (!$settings["auth_script_name"]) {
            return false;
        }

        $this->ilias->setSetting('script_active', "1");

        return true;
    }
}
