<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilModulesGroupTasks
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 */
class ilModulesGroupTasks
{
    /**
     * @param ilNode $context
     * @param array  $params
     *
     * @return array
     */
    public static function readMembersFromGroup($context, $params)
    {
        /*
         * Modelling:

      <bpmn2:extensionElements>
          <ilias:properties>
              <ilias:libraryCall location="Services/WorkflowEngine/classes/tasks/class.ilModulesGroupTasks.php" api="ilModulesGroupTasks" method="readMembersFromGroup" />
          </ilias:properties>
      </bpmn2:extensionElements>

         */
        require_once './Modules/Group/classes/class.ilGroupParticipants.php';
        $input_params = $params[0];
        $output_params = $params[1];

        $participants = ilGroupParticipants::_getInstanceByObjId(ilObject::_lookupObjectId($input_params['grpRefId']));
        $members = $participants->getMembers();
        $retval = array($output_params[0] => $members);

        return $retval;
    }

    /**
     * @param ilNode $context
     * @param array  $params
     *
     * @return array
     */
    public static function readAdminsFromGroup($context, $params)
    {
        /*
         * Modelling:

      <bpmn2:extensionElements>
          <ilias:properties>
              <ilias:libraryCall location="Services/WorkflowEngine/classes/tasks/class.ilModulesGroupTasks.php" api="ilModulesGroupTasks" method="readAdminsFromGroup" />
          </ilias:properties>
      </bpmn2:extensionElements>

         */

        require_once './Modules/Group/classes/class.ilGroupParticipants.php';
        $input_params = $params[0];
        $output_params = $params[1];

        $participants = ilGroupParticipants::_getInstanceByObjId(ilObject::_lookupObjectId($input_params['grpRefId']));
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
    public static function assignMembersToGroup($context, $params)
    {
        /*
         * Modelling:

      <bpmn2:extensionElements>
          <ilias:properties>
              <ilias:libraryCall location="Services/WorkflowEngine/classes/tasks/class.ilModulesGroupTasks.php" api="ilModulesGroupTasks" method="assignMembersToGroup" />
          </ilias:properties>
      </bpmn2:extensionElements>

         */

        require_once './Modules/Group/classes/class.ilGroupParticipants.php';
        $input_params = $params[0];
        $output_params = $params[1];

        $members = ilGroupParticipants::_getInstanceByObjId(ilObject::_lookupObjectId($input_params['grpRefId']));
        foreach ($input_params['usrIdList'] as $user_id) {
            $members->add($user_id, IL_GRP_MEMBER);
        }

        return;
    }

    /**
     * @param ilNode $context
     * @param array  $params
     */
    public static function assignAdminsToGroup($context, $params)
    {
        /*
         * Modelling:

      <bpmn2:extensionElements>
          <ilias:properties>
              <ilias:libraryCall location="Services/WorkflowEngine/classes/tasks/class.ilModulesGroupTasks.php" api="ilModulesGroupTasks" method="assignAdminsToGroup" />
          </ilias:properties>
      </bpmn2:extensionElements>

         */

        require_once './Modules/Group/classes/class.ilGroupParticipants.php';
        $input_params = $params[0];
        $output_params = $params[1];

        $participants = ilGroupParticipants::_getInstanceByObjId(ilObject::_lookupObjectId($input_params['grpRefId']));
        foreach ($input_params['usrIdList'] as $user_id) {
            $participants->add($user_id, IL_GRP_ADMIN);
        }
        return;
    }
}
