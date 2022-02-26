<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCourseReferencePathInfo
 */
class ilCourseReferenceAppEventListener implements ilAppEventListener
{
    private static $instance = null;

    /**
     * @var \ilLogger | null
     */
    private $logger = null;


    private $tree = null;

    /**
     * ilCourseReferenceAppEventHandler constructor.
     */
    private function __construct()
    {
        global $DIC;

        $this->logger = $DIC->logger()->crsr();
        $this->tree = $DIC->repositoryTree();
    }

    /**
     * @param $a_event
     * @param $a_parameter
     */
    protected function handleUserAssignments($a_event, $a_parameter)
    {
        if ($a_parameter['type'] != 'crs') {
            $this->logger->debug('Ignoring event for type ' . $a_parameter['type']);
            return true;
        }

        $this->logger->info('Current event is: ' . $a_event);
        if ($a_event == 'assignUser') {
            $this->logger->debug('Handling assign user event for type crs.');
            $this->handleReferences($a_parameter['obj_id'], $a_parameter['usr_id'], $a_parameter['role_id']);
        } else {
            $this->logger->debug('Ignoring event: ' . $a_event);
        }
    }


    /**
     *
     * @param $a_course_obj_id
     * @param $a_usr_id
     * @param $a_role_id
     */
    protected function handleReferences($a_course_obj_id, $a_usr_id, $a_role_id)
    {
        $role_title = ilObject::_lookupTitle($a_role_id);
        if (substr($role_title, 0, 10) !== 'il_crs_mem') {
            $this->logger->debug('Ignoring non member role: ' . $role_title);
            return;
        }


        // find all crs references for course
        $course_ref_ids = ilObject::_getAllReferences($a_course_obj_id);
        $course_ref_id = end($course_ref_ids);

        $childs = $this->tree->getChildsByType($course_ref_id, 'crsr');
        $this->logger->dump($childs, ilLogLevel::DEBUG);

        foreach ($childs as $tree_node) {
            $this->logger->debug('Handling course reference: ' . $tree_node['title']);
            $path_info = ilCourseReferencePathInfo::getInstanceByRefId($tree_node['child']);

            // this also checks for structure crs -> grp -> crsr, which return false
            if (!$path_info->hasParentCourse()) {
                $this->logger->debug('No reference member update: no direct parent course');
                continue;
            }

            if (!$path_info->isMemberUpdateEnabled()) {
                $this->logger->debug('No reference member update: update disabled.');
                continue;
            }

            $this->logger->debug('Reference member update: adding user to course.');
            $target_course_ref_id = $path_info->getTargetId();
            $part = ilCourseParticipants::getInstance($target_course_ref_id);
            $part->add($a_usr_id, ilParticipants::IL_CRS_MEMBER);
        }
    }





    /**
     * Handle an event in a listener.
     * @param    string $a_component component, e.g. "Modules/Forum" or "Services/User"
     * @param    string $a_event     event e.g. "createUser", "updateUser", "deleteUser", ...
     * @param    array  $a_parameter parameter array (assoc), array("name" => ..., "phone_office" => ...)
     */
    public static function handleEvent(string $a_component, string $a_event, array $a_parameter) : void
    {
        ilLoggerFactory::getLogger('crs')->warning($a_component);
        switch ($a_component) {

            case 'Services/AccessControl':
                $self = new self();
                $self->handleUserAssignments($a_event, $a_parameter);
                break;
        }
    }
}
