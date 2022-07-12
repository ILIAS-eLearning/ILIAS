<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Group Pool listener. Listens to events of other components.
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ModulesGroup
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
    public function getLogger() : ilLogger
    {
        return $this->logger;
    }
    
    protected function handleUserAssignments(string $a_event, array $a_parameters) : void
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
    protected static function doAutoFill(int $a_obj_id) : void
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
    * @param	string	$a_component	component, e.g. "Modules/Forum" or "Services/User"
    * @param	string	$a_event		event e.g. "createUser", "updateUser", "deleteUser", ...
    * @param	array	$a_parameter	parameter array (assoc), array("name" => ..., "phone_office" => ...)
    */
    public static function handleEvent(string $a_component, string $a_event, array $a_parameter) : void
    {
        if ($a_component == 'Services/AccessControl') {
            $listener = new self();
            $listener->handleUserAssignments($a_event, $a_parameter);
        }
    }
}
