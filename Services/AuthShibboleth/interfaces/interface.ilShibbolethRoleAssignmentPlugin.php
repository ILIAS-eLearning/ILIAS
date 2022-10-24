<?php
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
 * Interface for shibboleth role assignment plugins
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ServicesAuthShibboleth
 */
interface ilShibbolethRoleAssignmentPlugin
{
    /**
     * check role assignment for a specific plugin id
     * (defined in the shibboleth role assignment administration).
     *
     * @param int $a_plugin_id Unique plugin id
     * @param array $a_user_data Array with user data ($_SERVER)
     * @return bool whether the condition is fullfilled or not
     */
    public function checkRoleAssignment(int $a_plugin_id, array $a_user_data): bool;
}
