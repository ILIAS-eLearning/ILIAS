<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once ('./Services/Verification/classes/class.ilVerificationObject.php');

/**
* Exercise Verification
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
*
* @version $Id$
*
* @ingroup ModulesExercise
*/
class ilObjExerciseVerification extends ilVerificationObject
{
	protected function initType()
	{
		$this->type = "excv";
	}

	protected function getPropertyMap()
	{
		return array("issued_on" => self::TYPE_DATE,
			"success" => self::TYPE_BOOL,
			"mark" => self::TYPE_STRING,
			"comment" => self::TYPE_STRING);
	}

	/**
	 * Import relevant properties from given exercise
	 *
	 * @param ilObjExercise $a_test
	 * @return object
	 */
	public static function createFromExercise(ilObjExercise $a_exercise, $a_user_id)
	{
		$newObj = new self();
		$newObj->setTitle($a_exercise->getTitle());
		$newObj->setDescription($a_exercise->getDescription());

		// :TODO: issued on

		include_once "Modules/Exercise/classes/class.ilExerciseMembers.php";
		
		$status = ilExerciseMembers::_lookupStatus($a_exercise->getId(), $a_user_id);
		$newObj->setProperty("success", ($status == "passed"));

		include_once "Services/Tracking/classes/class.ilLPMarks.php";

		$mark = ilLPMarks::_lookupMark($a_user_id, $a_exercise->getId());
		$newObj->setProperty("mark", $mark);

		$comment = ilLPMarks::_lookupComment($a_user_id, $a_exercise->getId());
		$newObj->setProperty("comment", $comment);

		return $newObj;
	}
}

?>