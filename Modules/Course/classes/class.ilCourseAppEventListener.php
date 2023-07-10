<?php

declare(strict_types=0);
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
 * Course Pool listener. Listens to events of other components.
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ModulesMediaPool
 */
class ilCourseAppEventListener
{
    private ilLogger $logger;

    protected static array $course_mode = array();
    protected static bool $blocked_for_lp = false;

    /**
     * Constructor
     * @noinspection PhpUndefinedMethodInspection
     */
    public function __construct()
    {
        global $DIC;
        $this->logger = $DIC->logger()->crs();
    }

    public function getLogger(): ilLogger
    {
        return $this->logger;
    }

    protected function handleUserAssignments(string $a_event, array $a_parameters): void
    {
        if ($a_parameters['type'] != 'crs') {
            $this->getLogger()->debug('Ignoring event for type ' . $a_parameters['type']);
            return;
        }
        $new_status = 0;
        if ($a_event == 'assignUser') {
            $this->getLogger()->debug('Handling assign user event for type crs.');
            $new_status = 1;
        } elseif ($a_event == 'deassignUser') {
            $this->getLogger()->debug('Handling assign user event for type crs.');
            $new_status = 0;
        }

        ilLoggerFactory::getInstance()->getLogger('crs')->debug(print_r($a_parameters, true));
        ilLoggerFactory::getInstance()->getLogger('crs')->debug(print_r($new_status, true));

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
     */
    protected function doAutoFill(int $a_obj_id): void
    {
        $this->getLogger()->debug('Handling event deassign user -> waiting list auto fill');

        // #16694
        $refs = ilObject::_getAllReferences($a_obj_id);
        $ref_id = array_pop($refs);

        $course = ilObjectFactory::getInstanceByRefId($ref_id, false);
        if (!$course instanceof ilObjCourse) {
            $this->getLogger()->warning('Cannot handle event deassign user since passed obj_id is not of type course: ' . $a_obj_id);
        }
        $course->handleAutoFill();
    }

    public static function initializeTimings(int $a_obj_id, int $a_usr_id, int $a_role_id): bool
    {
        static $timing_mode = array();

        if (!array_key_exists($a_obj_id, $timing_mode)) {
            $timing_mode[$a_obj_id] = ilObjCourse::lookupTimingMode($a_obj_id) == ilCourseConstants::IL_CRS_VIEW_TIMING_RELATIVE;
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
     */
    public static function destroyTimings(int $a_obj_id, int $a_usr_id): bool
    {
        $user_timings = ilTimingsUser::getInstanceByContainerId($a_obj_id);
        $user_timings->init();
        $user_timings->handleUnsubscribe($a_usr_id);
        return true;
    }

    public static function handleEvent(string $a_component, string $a_event, array $a_parameter): void
    {
        if ($a_component == 'Services/AccessControl') {
            $listener = new self();
            $listener->handleUserAssignments($a_event, $a_parameter);
        }

        switch ($a_component) {
            case 'Modules/Course':
                if ($a_event == 'addParticipant') {
                    self::initializeTimings($a_parameter['obj_id'], $a_parameter['usr_id'], $a_parameter['role_id']);
                    return;
                }
                if ($a_event == 'deleteParticipant') {
                    self::destroyTimings($a_parameter['obj_id'], $a_parameter['usr_id']);
                    return;
                }
                break;
        }

        if ($a_component == "Services/Tracking" && $a_event == "updateStatus") {
            // see ilObjCourseGUI::updateLPFromStatus()
            if (self::$blocked_for_lp) {
                return;
            }

            // #13905
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
                    $crs = new ilObjCourse($obj_id, false);
                    if ($crs->getStatusDetermination() == ilObjCourse::STATUS_DETERMINATION_LP) {
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
                        ilCourseParticipants::_updatePassed($obj_id, $user_id, $is_completed, true);
                        break;

                    case ilLPObjSettings::LP_MODE_COLLECTION:
                    case ilLPObjSettings::LP_MODE_OBJECTIVES:
                        // overwrites course passed status if it was set automatically (full sync)
                        // or toggle manually set passed status to completed (1-way-sync)
                        $do_update = $is_completed;
                        if (!$do_update) {
                            $part = new ilCourseParticipants($obj_id);
                            $passed = $part->getPassedInfo($user_id);
                            if (
                                !is_array($passed) ||
                                ((int) ($passed["user_id"] ?? 0)) === -1) {
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
     */
    public static function setBlockedForLP(bool $a_status): void
    {
        self::$blocked_for_lp = $a_status;
    }
}
