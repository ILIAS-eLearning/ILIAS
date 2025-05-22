<?php

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

declare(strict_types=1);
/**
 * Group Pool listener. Listens to events of other components.
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup components\ILIASGroup
 */
class ilGroupAppEventListener
{
    private ilLogger $logger;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->grp();
    }

    /**
     */
    public function getLogger(): ilLogger
    {
        return $this->logger;
    }

    protected function handleUserAssignments(string $a_event, array $a_parameters): void
    {
        if ($a_parameters['type'] != 'grp') {
            $this->getLogger()->debug('Ignoring event for type ' . $a_parameters['type']);
            return;
        }

        if ($a_event == 'assignUser') {
            $this->getLogger()->debug('Handling assign user event for type grp.');
            $new_status = 1;
        } elseif ($a_event == 'deassignUser') {
            $this->getLogger()->debug('Handling assign user event for type grp.');
            $new_status = 0;
        } else {
            return;
        }
        ilParticipant::updateMemberRoles(
            (int) $a_parameters['obj_id'],
            (int) $a_parameters['usr_id'],
            (int) $a_parameters['role_id'],
            $new_status
        );

        if ($a_event == 'deassignUser') {
            self::doAutoFill((int) $a_parameters['obj_id']);
        }
    }

    /**
     * Trigger autofill from waiting list
     */
    protected static function doAutoFill(int $a_obj_id): void
    {
        global $DIC;

        $logger = $DIC->logger()->grp();
        $refs = ilObject::_getAllReferences($a_obj_id);
        $ref_id = end($refs);

        $group = ilObjectFactory::getInstanceByRefId($ref_id, false);
        if (!$group instanceof ilObjGroup) {
            $logger->warning('Cannot handle event deassign user since passed obj_id is not of type group: ' . $a_obj_id);
        }
        $group->handleAutoFill();
    }

    /**
    * Handle an event in a listener.
    *
    * @param	string	$a_component	component, e.g. "components/ILIAS/Forum" or "components/ILIAS/User"
    * @param	string	$a_event		event e.g. "createUser", "updateUser", "deleteUser", ...
    * @param	array	$a_parameter	parameter array (assoc), array("name" => ..., "phone_office" => ...)
    */
    public static function handleEvent(string $a_component, string $a_event, array $a_parameter): void
    {
        if ($a_component == 'components/ILIAS/AccessControl') {
            $listener = new self();
            $listener->handleUserAssignments($a_event, $a_parameter);
        }
    }
}
