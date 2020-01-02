<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
include_once './Services/LDAP/interfaces/interface.ilLDAPRoleAssignmentPlugin.php';

/**
* Plugin definition
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesLDAP
*/
abstract class ilLDAPPlugin extends ilPlugin
{

    /**
     * @see ilPlugin::getComponentName()
     */
    final public function getComponentName()
    {
        return 'LDAP';
    }
    
    /**
     * @see ilPlugin::getComponentType()
     */
    final public function getComponentType()
    {
        return IL_COMP_SERVICE;
    }
    
    /**
     * @see ilPlugin::getSlot()
     */
    final public function getSlot()
    {
        return 'LDAPHook';
    }
    
    /**
     * @see ilPlugin::getSlotId()
     */
    final public function getSlotId()
    {
        return 'ldaphk';
    }
    
    /**
     * @see ilPlugin::slotInit()
     */
    public function slotInit()
    {
    }
    
    /**
     * Check if user data matches a keyword value combination
     * @return
     * @param object $a_user_data
     * @param object $a_keyword
     * @param object $a_value
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
}
