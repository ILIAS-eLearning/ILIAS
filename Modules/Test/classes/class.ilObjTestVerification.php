<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once ('./Services/Verification/classes/class.ilVerificationObject.php');

/**
* Test Verification
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
*
* @version $Id$
*
* @ingroup ModulesTest
*/
class ilObjTestVerification extends ilVerificationObject
{
	protected function initType()
	{
		$this->type = "tstv";
	}

	protected function getPropertyMap()
	{
		return array("issued_on" => self::TYPE_DATE,
			"file" => self::TYPE_STRING
			/*
			"success" => self::TYPE_BOOL,
			"result" => self::TYPE_STRING,
			"mark" => self::TYPE_STRING
			*/
			);			
	}

	/**
	 * Import relevant properties from given test
	 *
	 * @param ilObjTest $a_test
	 * @return object
	 */
	public static function createFromTest(ilObjTest $a_test, $a_user_id)
	{
		global $lng;
		
		$lng->loadLanguageModule("wsp");
		
		$newObj = new self();
		$newObj->setTitle($lng->txt("wsp_type_tstv")." \"".$a_test->getTitle()."\"");
		$newObj->setDescription($a_test->getDescription());

		$active_id = $a_test->getActiveIdOfUser($a_user_id);
		$pass = $a_test::_getResultPass($active_id);
		
		$date = $a_test->getPassFinishDate($active_id, $pass);
		$newObj->setProperty("issued_on", new ilDate($date, IL_CAL_UNIX));

		/*
		$result = $a_test->getTestResult($active_id, $pass);
		$newObj->setProperty("success", (bool)$result["test"]["passed"]);
		$newObj->setProperty("result", $result["test"]["total_reached_points"]."/".
			$result["test"]["total_max_points"]);
		$mark_obj = $a_test->mark_schema->getMatchingMark($result["pass"]["percent"] * 100);
		if ($mark_obj)
		{
			$newObj->setProperty("mark", $mark_obj->getOfficialName());
		}		 
		*/
		
		// create certificate
		include_once "Services/Certificate/classes/class.ilCertificate.php";
		include_once "Modules/Test/classes/class.ilTestCertificateAdapter.php";
		$certificate = new ilCertificate(new ilTestCertificateAdapter($a_test));
		$certificate = $certificate->outCertificate(array("active_id" => $active_id, "pass" => $pass), false);
		
		// save pdf file
		if($certificate)
		{
			// we need the object id for storing the certificate file
			$newObj->create();
			
			$path = self::initStorage($newObj->getId(), "certificate");
			
			$file_name = "tst_".$a_test->getId()."_".$a_user_id."_".$active_id.".pdf";			
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