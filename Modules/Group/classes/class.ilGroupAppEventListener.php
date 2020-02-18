<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Group Pool listener. Listens to events of other components.
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * @ingroup ModulesGroup
 */
class ilGroupAppEventListener
{
    private $logger = null;
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->logger = ilLoggerFactory::getInstance()->getLogger('grp');
    }
    
    /**
     * @return ilLogger
     */
    public function getLogger()
    {
        return $this->logger;
    }
    
    /**
     * handle user assignments
     * @param type $a_event
     * @param type $a_parameters
     */
    protected function handleUserAssignments($a_event, $a_parameters)
    {
        if ($a_parameters['type'] != 'grp') {
            $this->getLogger()->debug('Ignoring event for type ' . $a_parameters['type']);
            return true;
        }
        
        if ($a_event == 'assignUser') {
            $this->getLogger()->debug('Handling assign user event for type grp.');
            $new_status = 1;
        } elseif ($a_event == 'deassignUser') {
            $this->getLogger()->debug('Handling assign user event for type grp.');
            $new_status = 0;
        }
        
        ilLoggerFactory::getInstance()->getLogger('grp')->debug(print_r($a_parameters, true));
        ilLoggerFactory::getInstance()->getLogger('grp')->debug(print_r($new_status, true));
        
        include_once './Services/Membership/classes/class.ilParticipant.php';
        
        ilParticipant::updateMemberRoles(
            $a_parameters['obj_id'],
            $a_parameters['usr_id'],
            $a_parameters['role_id'],
            $new_status
        );
        
        if ($a_event == 'deassignUser') {
            $self = new self();
            $self->doAutoFill($a_parameters['obj_id']);
        }
    }
    
    /**
     * Trigger autofill from waiting list
     *
     * @param int $a_obj_id
     */
    protected static function doAutoFill($a_obj_id)
    {
        global $DIC;

        $logger = $DIC->logger()->grp();

        $ref_id = array_pop(ilObject::_getAllReferences($a_obj_id));
        
        $group = \ilObjectFactory::getInstanceByRefId($ref_id, false);
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
    public static function handleEvent($a_component, $a_event, $a_parameter)
    {
        if ($a_component == 'Services/AccessControl') {
            $listener = new self();
            $listener->handleUserAssignments($a_event, $a_parameter);
        }
    }
}
