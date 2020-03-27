<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilModulesCourseTasks
*
* @author Maximilian Becker <mbecker@databay.de>
* @version $Id$
*
*/
class ilModulesCourseTasks
{
    /**
     * @param ilNode $context
     * @param array  $params
     *
     * @return array
     */
    public static function readLearnersFromCourse($context, $params)
    {
        /*
         * Modelling:

      <bpmn2:extensionElements>
          <ilias:properties>
              <ilias:libraryCall location="Services/WorkflowEngine/classes/tasks/class.ilModulesCourseTasks.php" api="ilModulesCourseTasks" method="readLearnersFromCourse" />
          </ilias:properties>
      </bpmn2:extensionElements>

         */
        require_once './Modules/Course/classes/class.ilCourseParticipants.php';
        $input_params = $params[0];
        $output_params = $params[1];

        $participants = ilCourseParticipants::_getInstanceByObjId(ilObject::_lookupObjectId($input_params['crsRefId']));
        $learners = $participants->getMembers();
        $retval = array($output_params[0] => $learners);

        return $retval;
    }

    /**
     * @param ilNode $context
     * @param array  $params
     *
     * @return array
     */
    public static function readTutorsFromCourse($context, $params)
    {
        /*
         * Modelling:

      <bpmn2:extensionElements>
          <ilias:properties>
              <ilias:libraryCall location="Services/WorkflowEngine/classes/tasks/class.ilModulesCourseTasks.php" api="ilModulesCourseTasks" method="readTutorsFromCourse" />
          </ilias:properties>
      </bpmn2:extensionElements>

         */

        require_once './Modules/Course/classes/class.ilCourseParticipants.php';
        $input_params = $params[0];
        $output_params = $params[1];

        $participants = ilCourseParticipants::_getInstanceByObjId(ilObject::_lookupObjectId($input_params['crsRefId']));
        $tutors = $participants->getTutors();
        $retval = array($output_params[0] => $tutors);

        return $retval;
    }

    /**
     * @param ilNode $context
     * @param array  $params
     *
     * @return array
     */
    public static function readAdminsFromCourse($context, $params)
    {
        /*
         * Modelling:

      <bpmn2:extensionElements>
          <ilias:properties>
              <ilias:libraryCall location="Services/WorkflowEngine/classes/tasks/class.ilModulesCourseTasks.php" api="ilModulesCourseTasks" method="readAdminsFromCourse" />
          </ilias:properties>
      </bpmn2:extensionElements>

         */

        require_once './Modules/Course/classes/class.ilCourseParticipants.php';
        $input_params = $params[0];
        $output_params = $params[1];

        $participants = ilCourseParticipants::_getInstanceByObjId(ilObject::_lookupObjectId($input_params['crsRefId']));
        $admins = $participants->getAdmins();
        $retval = array($output_params[0] => $admins);

        return $retval;
    }

    /**
     * @param ilNode $context
     * @param array  $params
     *
     * @return array
     */
    public static function createCourse($context, $params)
    {
        /*
         * Modelling:

      <bpmn2:extensionElements>
          <ilias:properties>
              <ilias:libraryCall location="Services/WorkflowEngine/classes/tasks/class.ilModulesCourseTasks.php" api="ilModulesCourseTasks" method="createCourse" />
          </ilias:properties>
      </bpmn2:extensionElements>

         */


        $input_params = $params[0];
        $output_params = $params[1];

        require_once './Modules/Course/classes/class.ilObjCourse.php';

        $course_object = new ilObjCourse();
        $course_object->setType('crs');
        $course_object->setTitle($input_params['crsTitle']);
        $course_object->setDescription("");
        $course_object->create(true); // true for upload
        $course_object->createReference();
        $course_object->putInTree($input_params['destRefId']);
        $course_object->setPermissions($input_params['destRefId']);

        $retval = array($output_params[0] => $course_object->getRefId());

        return $retval;
    }

    /**
     * @param ilNode $context
     * @param array  $params
     *
     * @return array
     */
    public static function assignLearnersToCourse($context, $params)
    {
        /*
         * Modelling:

      <bpmn2:extensionElements>
          <ilias:properties>
              <ilias:libraryCall location="Services/WorkflowEngine/classes/tasks/class.ilModulesCourseTasks.php" api="ilModulesCourseTasks" method="assignLearnersToCourse" />
          </ilias:properties>
      </bpmn2:extensionElements>

         */

        require_once './Modules/Course/classes/class.ilCourseParticipants.php';
        $input_params = $params[0];
        $output_params = $params[1];

        $participants = ilCourseParticipants::_getInstanceByObjId(ilObject::_lookupObjectId($input_params['crsRefId']));
        foreach ($input_params['usrIdList'] as $user_id) {
            $participants->add($user_id, IL_CRS_MEMBER);
        }

        return;
    }

    /**
     * @param ilNode $context
     * @param array  $params
     */
    public static function assignTutorsToCourse($context, $params)
    {
        /*
         * Modelling:

      <bpmn2:extensionElements>
          <ilias:properties>
              <ilias:libraryCall location="Services/WorkflowEngine/classes/tasks/class.ilModulesCourseTasks.php" api="ilModulesCourseTasks" method="assignTutorsToCourse" />
          </ilias:properties>
      </bpmn2:extensionElements>

         */

        require_once './Modules/Course/classes/class.ilCourseParticipants.php';
        $input_params = $params[0];
        $output_params = $params[1];

        $participants = ilCourseParticipants::_getInstanceByObjId(ilObject::_lookupObjectId($input_params['crsRefId']));
        foreach ($input_params['usrIdList'] as $user_id) {
            $participants->add($user_id, IL_CRS_TUTOR);
        }
        return;
    }

    /**
     * @param ilNode $context
     * @param array  $params
     */
    public static function assignAdminsToCourse($context, $params)
    {
        /*
         * Modelling:

      <bpmn2:extensionElements>
          <ilias:properties>
              <ilias:libraryCall location="Services/WorkflowEngine/classes/tasks/class.ilModulesCourseTasks.php" api="ilModulesCourseTasks" method="assignAdminsToCourse" />
          </ilias:properties>
      </bpmn2:extensionElements>

         */

        require_once './Modules/Course/classes/class.ilCourseParticipants.php';
        $input_params = $params[0];
        $output_params = $params[1];

        $participants = ilCourseParticipants::_getInstanceByObjId(ilObject::_lookupObjectId($input_params['crsRefId']));
        foreach ($input_params['usrIdList'] as $user_id) {
            $participants->add($user_id, IL_CRS_ADMIN);
        }
        return;
    }
}
