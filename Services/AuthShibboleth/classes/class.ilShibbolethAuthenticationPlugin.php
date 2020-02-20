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

include_once './Services/Component/classes/class.ilPlugin.php';
include_once './Services/AuthShibboleth/interfaces/interface.ilShibbolethRoleAssignmentPlugin.php';
require_once('./Services/AuthShibboleth/interfaces/interface.ilShibbolethAuthenticationPluginInt.php');

/**
 * Plugin definition
 *
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 *
 *
 * @ingroup ServicesAuthShibboleth
 */
abstract class ilShibbolethAuthenticationPlugin extends ilPlugin implements ilShibbolethAuthenticationPluginInt
{

    /**
     * @var array
     */
    protected $active_plugins = array();


    /**
     * Get Component Type
     *
     * @return        string        Component Type
     */
    final public function getComponentType()
    {
        return IL_COMP_SERVICE;
    }


    /**
     * Get Component Name
     *
     * @return    string Component Name
     */
    final public function getComponentName()
    {
        return 'AuthShibboleth';
    }


    /**
     * Get Slot Name
     *
     * @return string Slot Name
     */
    final public function getSlot()
    {
        return 'ShibbolethAuthenticationHook';
    }


    /**
     * Get Slot Id
     *
     * @return string Slot Id
     */
    final public function getSlotId()
    {
        return 'shibhk';
    }


    /**
     *  Object initialization done by slot.
     */
    final protected function slotInit()
    {
    }


    /**
     * @param $a_user_data
     * @param $a_keyword
     * @param $a_value
     *
     * @return bool
     */
    protected function checkValue($a_user_data, $a_keyword, $a_value)
    {
        if (!$a_user_data[$a_keyword]) {
            return false;
        }
        if (is_array($a_user_data[$a_keyword])) {
            foreach ($a_user_data[$a_keyword] as $values) {
                if (strcasecmp(trim($values), $a_value) == 0) {
                    return true;
                }
            }

            return false;
        }
        if (strcasecmp(trim($a_user_data[$a_keyword]), trim($a_value)) == 0) {
            return true;
        }

        return false;
    }


    /**
     * @param ilObjUser $user
     *
     * @return ilObjUser
     */
    public function beforeLogin(ilObjUser $user)
    {
        return $user;
    }


    /**
     * @param ilObjUser $user
     *
     * @return ilObjUser
     */
    public function afterLogin(ilObjUser $user)
    {
        return $user;
    }


    /**
     * @param ilObjUser $user
     *
     * @return ilObjUser
     */
    public function beforeCreateUser(ilObjUser $user)
    {
        return $user;
    }


    /**
     * @param ilObjUser $user
     *
     * @return ilObjUser
     */
    public function afterCreateUser(ilObjUser $user)
    {
        return $user;
    }


    /**
     * @param ilObjUser $user
     *
     * @return ilObjUser
     */
    public function beforeLogout(ilObjUser $user)
    {
        return $user;
    }


    /**
     * @param ilObjUser $user
     *
     * @return ilObjUser
     */
    public function afterLogout(ilObjUser $user)
    {
        return $user;
    }


    /**
     * @param ilObjUser $user
     *
     * @return ilObjUser
     */
    public function beforeUpdateUser(ilObjUser $user)
    {
        return $user;
    }


    /**
     * @param ilObjUser $user
     *
     * @return ilObjUser
     */
    public function afterUpdateUser(ilObjUser $user)
    {
        return $user;
    }
}
