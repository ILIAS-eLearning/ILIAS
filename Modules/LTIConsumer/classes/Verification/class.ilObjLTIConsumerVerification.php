<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilObjLTIConsumerVerification
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/LTIConsumer
 */
class ilObjLTIConsumerVerification extends ilVerificationObject
{
	protected function initType()
	{
		$this->type = "ltiv";
	}
	
	protected function getPropertyMap()
	{
		return array("issued_on" => self::TYPE_DATE,
			"file" => self::TYPE_STRING
		);
	}
	
	/**
	 * Import relevant properties from given course
	 *
	 * @param ilObjCourse $a_course
	 * @return object
	 */
	public static function createFromObject(ilObjLTIConsumer $object, $a_user_id)
	{
		global $lng;
		
		$lng->loadLanguageModule("lti");
		
		$newObj = new self();
		$newObj->setTitle($object->getTitle());
		$newObj->setDescription($object->getDescription());
		
		// create certificate
		include_once "Services/Certificate/classes/class.ilCertificate.php";
		include_once "Modules/Course/classes/class.ilCourseCertificateAdapter.php";
		$certificate = new ilCertificate(new ilLTIConsumerCertificateAdapter($object));
		$certificate = $certificate->outCertificate(array("user_id" => $a_user_id), false);
		
		// save pdf file
		if($certificate)
		{
			// we need the object id for storing the certificate file
			$newObj->create();
			
			$path = self::initStorage($newObj->getId(), "certificate");
			
			$file_name = "lti_".$object->getId()."_".$a_user_id.".pdf";
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
