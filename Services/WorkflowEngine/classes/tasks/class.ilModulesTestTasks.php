<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilModulesTestTasks
 *
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 *
 */
class ilModulesTestTasks
{
	/**
	 * @param ilNode $context
	 * @param array  $params
	 *
	 * @return array
	 */
	public static function createTestInCourse($context, $params)
	{
		//IN: targetref, titlestring
		//OUT: refid
		$input_params = $params[0];
		$output_params =$params[1];

		require_once './Modules/Test/classes/class.ilObjTest.php';

		$test_object = new ilObjTest();
		$test_object->setType('tst');
		$test_object->setTitle('Prüfung'); // Input?
		$test_object->setDescription("");
		$test_object->create(true); // true for upload
		$test_object->createReference();
		$test_object->putInTree($input_params['crsRefId']);
		$test_object->setPermissions($input_params['crsRefId']);
		$test_object->setFixedParticipants(1);
		$test_object->createMetaData();
		$test_object->updateMetaData();
		$test_object->saveToDb();

		$retval = array($output_params[0] => $test_object->getRefId());

		return $retval;
	}

	/**
	 * @param ilNode $context
	 * @param array  $params
	 *
	 * @return array
	 */
	public static function assignUsersToTest($context, $params)
	{
		require_once './Modules/Test/classes/class.ilObjTest.php';
		//IN: anonuserlist
		//OUT: void

		$input_params = $params[0];
		$output_params =$params[1];

		$test_object = new ilObjTest($input_params['tstRefId']);
		foreach($input_params['usrIdList'] as $user_id)
		{
			$test_object->inviteUser($user_id);
		}
	}
}