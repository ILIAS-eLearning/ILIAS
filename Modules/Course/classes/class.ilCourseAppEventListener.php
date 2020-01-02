<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Course Pool listener. Listens to events of other components.
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ModulesMediaPool
 */
class ilCourseAppEventListener
{
    private $logger = null;

    protected static $timings_mode = null;

    protected static $course_mode = array();
    protected static $blocked_for_lp;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->logger = ilLoggerFactory::getInstance()->getLogger('crs');
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
        if ($a_parameters['type'] != 'crs') {
            $this->getLogger()->debug('Ignoring event for type ' . $a_parameters['type']);
            return true;
        }
        
        if ($a_event == 'assignUser') {
            $this->getLogger()->debug('Handling assign user event for type crs.');
            $new_status = 1;
        } elseif ($a_event == 'deassignUser') {
            $this->getLogger()->debug('Handling assign user event for type crs.');
            $new_status = 0;
        }
        
        ilLoggerFactory::getInstance()->getLogger('crs')->debug(print_r($a_parameters, true));
        ilLoggerFactory::getInstance()->getLogger('crs')->debug(print_r($new_status, true));
        
        include_once './Modules/Course/classes/class.ilCourseParticipant.php';
        ilCourseParticipant::updateMemberRoles(
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
    protected function doAutoFill($a_obj_id)
    {
        $this->getLogger()->debug('Handling event deassign user -> waiting list auto fill');
        
        // #16694
        include_once("./Modules/Course/classes/class.ilObjCourse.php");
        $ref_id = array_pop(ilObject::_getAllReferences($a_obj_id));
        
        include_once './Services/Object/classes/class.ilObjectFactory.php';
        $factory = new ilObjectFactory();
        
        $course = $factory->getInstanceByRefId($ref_id, false);
        if (!$course instanceof ilObjCourse) {
            $this->getLogger()->warning('Cannot handle event deassign user since passed obj_id is not of type course: ' . $a_obj_id);
        }
        
        $course->handleAutoFill();
    }

    /**
     * initialize timings
     */
    public static function initializeTimings($a_obj_id, $a_usr_id, $a_role_id)
    {
        static $timing_mode = array();
        
        if (!array_key_exists($a_obj_id, $timing_mode)) {
            $timing_mode[$a_obj_id] = (
                (ilObjCourse::lookupTimingMode($a_obj_id) == ilCourseConstants::IL_CRS_VIEW_TIMING_RELATIVE) ?
                    true :
                    false
            );
        }
        if (!$timing_mode[$a_obj_id]) {
            return true;
        }
        
        $user_timings = ilTimingsUser::getInstanceByContainerId($a_obj_id);
        $user_timings->init();
        $user_timings->handleNewMembership($a_usr_id, new ilDateTime(time(), IL_CAL_UNIX));
        return true;
    }

    /**
     * Delete timings for user
     * @param int $a_obj_id
     * @param int $a_usr_id
     * @return boolean
     */
    public static function destroyTimings($a_obj_id, $a_usr_id)
    {
        include_once './Modules/Course/classes/Timings/class.ilTimingsUser.php';
        $user_timings = ilTimingsUser::getInstanceByContainerId($a_obj_id);
        $user_timings->init();
        $user_timings->handleUnsubscribe($a_usr_id);
        return true;
    }

    // cognos-blu-patch: end
        
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
    
        switch ($a_component) {
            case 'Modules/Course':
                if ($a_event == 'addParticipant') {
                    self::initializeTimings($a_parameter['obj_id'], $a_parameter['usr_id'], $a_parameter['role_id']);
                    return true;
                }
                if ($a_event == 'deleteParticipant') {
                    self::destroyTimings($a_parameter['obj_id'], $a_parameter['usr_id']);
                    return true;
                }
                break;
        }

        if ($a_component == "Services/Tracking" && $a_event == "updateStatus") {
            // see ilObjCourseGUI::updateLPFromStatus()
            if ((bool) self::$blocked_for_lp) {
                return;
            }
            
            // #13905
            include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
            if (!ilObjUserTracking::_enabledLearningProgress()) {
                return;
            }
            
            $obj_id = $a_parameter["obj_id"];
            $user_id = $a_parameter["usr_id"];
            $status = $a_parameter["status"];
            
            if ($obj_id && $user_id) {
                if (ilObject::_lookupType($obj_id) != "crs") {
                    return;
                }
                
                // determine couse setting only once
                if (!isset(self::$course_mode[$obj_id])) {
                    include_once("./Modules/Course/classes/class.ilObjCourse.php");
                    $crs = new ilObjCourse($obj_id, false);
                    if ($crs->getStatusDetermination() == ilObjCourse::STATUS_DETERMINATION_LP) {
                        include_once './Services/Object/classes/class.ilObjectLP.php';
                        $olp = ilObjectLP::getInstance($obj_id);
                        $mode = $olp->getCurrentMode();
                    } else {
                        $mode = false;
                    }
                    self::$course_mode[$obj_id] = $mode;
                }
                
                $is_completed = ($status == ilLPStatus::LP_STATUS_COMPLETED_NUM);
                
                // we are NOT using the members object because of performance issues
                switch (self::$course_mode[$obj_id]) {
                    case ilLPObjSettings::LP_MODE_MANUAL_BY_TUTOR:
                        // #11600
                        include_once "Modules/Course/classes/class.ilCourseParticipants.php";
                        ilCourseParticipants::_updatePassed($obj_id, $user_id, $is_completed, true);
                        break;

                    case ilLPObjSettings::LP_MODE_COLLECTION:
                    case ilLPObjSettings::LP_MODE_OBJECTIVES:
                        // overwrites course passed status if it was set automatically (full sync)
                        // or toggle manually set passed status to completed (1-way-sync)
                        $do_update = $is_completed;
                        include_once "Modules/Course/classes/class.ilCourseParticipants.php";
                        if (!$do_update) {
                            $part = new ilCourseParticipants($obj_id);
                            $passed = $part->getPassedInfo($user_id);
                            if (!is_array($passed) ||
                                $passed["user_id"] == -1) {
                                $do_update = true;
                            }
                        }
                        if ($do_update) {
                            ilCourseParticipants::_updatePassed($obj_id, $user_id, $is_completed);
                        }
                        break;
                }
            }
        }
    }
    
    /**
     * Toggle LP blocking property status
     *
     * @param bool $a_status
     */
    public static function setBlockedForLP($a_status)
    {
        self::$blocked_for_lp = (bool) $a_status;
    }
}
