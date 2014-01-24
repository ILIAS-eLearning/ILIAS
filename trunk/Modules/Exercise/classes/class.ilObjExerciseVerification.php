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
			"file" => self::TYPE_STRING
			/*
			"success" => self::TYPE_BOOL,
			"mark" => self::TYPE_STRING,
			"comment" => self::TYPE_STRING			 
			*/
			);
	}

	/**
	 * Import relevant properties from given exercise
	 *
	 * @param ilObjExercise $a_test
	 * @return object
	 */
	public static function createFromExercise(ilObjExercise $a_exercise, $a_user_id)
	{
		global $lng;
		
		$lng->loadLanguageModule("exercise");
		
		$newObj = new self();
		$newObj->setTitle($a_exercise->getTitle());
		$newObj->setDescription($a_exercise->getDescription());

		include_once "Services/Tracking/classes/class.ilLPMarks.php";
		$lp_marks = new ilLPMarks($a_exercise->getId(), $a_user_id);
		$newObj->setProperty("issued_on", 
			new ilDate($lp_marks->getStatusChanged(), IL_CAL_DATETIME));
		
		// create certificate
		include_once "Services/Certificate/classes/class.ilCertificate.php";
		include_once "Modules/Exercise/classes/class.ilExerciseCertificateAdapter.php";
		$certificate = new ilCertificate(new ilExerciseCertificateAdapter($a_exercise));
		$certificate = $certificate->outCertificate(array("user_id" => $a_user_id), false);
		
		// save pdf file
		if($certificate)
		{
			// we need the object id for storing the certificate file
			$newObj->create();
			
			$path = self::initStorage($newObj->getId(), "certificate");
			
			$file_name = "exc_".$a_exercise->getId()."_".$a_user_id.".pdf";			
			if(file_put_contents($path.$file_name, $certificate))
			{							
				$newObj->setProperty("file", $file_name);
				$newObj->update();
				
				return $newObj;
			}
		
			// file creation failed, so remove to object, too
			$newObj->delete();
		}
	}
}

?>