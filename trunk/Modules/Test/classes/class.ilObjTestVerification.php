<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

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
		$newObj->setTitle($a_test->getTitle());
		$newObj->setDescription($a_test->getDescription());

		$active_id = $a_test->getActiveIdOfUser($a_user_id);
		$pass = ilObjTest::_getResultPass($active_id);
		
		$date = $a_test->getPassFinishDate($active_id, $pass);
		$newObj->setProperty("issued_on", new ilDate($date, IL_CAL_UNIX));

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