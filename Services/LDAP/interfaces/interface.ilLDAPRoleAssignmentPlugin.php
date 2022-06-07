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
* Interface for ldap role assignment plugins
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @ingroup ServicesLDAP
*/
interface ilLDAPRoleAssignmentPlugin
{
    
    /**
     * check role assignment for a specific plugin id
     * (defined in the ldap role assignment administration).
     *
     * @param int	$a_plugin_id	Unique plugin id
     * @param array $a_user_data	Array with user data ($_SERVER)
     * @return bool whether the condition is fullfilled or not
     */
    public function checkRoleAssignment(int $a_plugin_id, array $a_user_data) : bool;
    
    /**
     * If additional LDAP attributes values are required in the plugin return an array
     * with these attribute names.
     * <code>
     * public function getAdditionalAttributeNames()
     * {
     * 		return array('employeetype','employeenumber','loginshell');
     * }
     * </code>
     * @return string[]
     */
    public function getAdditionalAttributeNames() : array;
}
