<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once ('./Services/Verification/classes/class.ilVerificationObject.php');

/**
* SCORM Verification
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
*
* @version $Id$
*
* @ingroup ModulesScormAicc
*/
class ilObjSCORMVerification extends ilVerificationObject
{
	protected function initType()
	{
		$this->type = "scov";
	}

	protected function getPropertyMap()
	{
		return array("issued_on" => self::TYPE_DATE,
			"file" => self::TYPE_STRING
			);
	}

	/**
	 * Import relevant properties from given learning module
	 *
	 * @param ilObjSAHSLearningModule $a_lm
	 * @return object
	 */
	public static function createFromSCORMLM(ilObjSAHSLearningModule $a_lm, $a_user_id)
	{
		global $lng;
		
		$lng->loadLanguageModule("sahs");
		
		$newObj = new self();
		$newObj->setTitle($a_lm->getTitle());
		$newObj->setDescription($a_lm->getDescription());

		include_once "Services/Tracking/classes/class.ilLPMarks.php";
		$lp_marks = new ilLPMarks($a_lm->getId(), $a_user_id);
		$newObj->setProperty("issued_on", 
			new ilDate($lp_marks->getStatusChanged(), IL_CAL_DATETIME));
							
		// create certificate
		if(!stristr(get_class($a_lm), "2004"))
		{
			$last_access = ilObjSCORMLearningModule::_lookupLastAccess($a_lm->getId(), $a_user_id);
		}
		else
		{				
			$last_access = ilObjSCORM2004LearningModule::_lookupLastAccess($a_lm->getId(), $a_user_id);
		}			
		$params = array(
			"user_data" => ilObjUser::_lookupFields($a_user_id),
			"last_access" => $last_access
		);				
		include_once "Services/Certificate/classes/class.ilCertificate.php";
		include_once "Modules/ScormAicc/classes/class.ilSCORMCertificateAdapter.php";
		$certificate = new ilCertificate(new ilSCORMCertificateAdapter($a_lm));	
		$certificate = $certificate->outCertificate($params, false);
		
		// save pdf file
		if($certificate)
		{
			// we need the object id for storing the certificate file
			$newObj->create();
			
			$path = self::initStorage($newObj->getId(), "certificate");
			
			$file_name = "sahs_".$a_lm->getId()."_".$a_user_id.".pdf";			
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