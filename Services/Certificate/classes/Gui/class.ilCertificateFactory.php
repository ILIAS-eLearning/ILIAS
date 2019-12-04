<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateFactory
{
	/**
	 * @param ilObject $object
	 * @return ilCertificate
	 * @throws ilException
	 */
	public function create(ilObject $object) : ilCertificate
	{
		$type = $object->getType();

		switch ($type) {
			case 'tst':
				$adapter = new ilTestCertificateAdapter($object);
				$placeholderDescriptionObject = new ilTestPlaceholderDescription();
				$placeholderValuesObject = new ilTestPlaceholderValues();
				$certificatePath = ilCertificatePathConstants::TEST_PATH . $object->getId() . '/';
				break;
			case 'crs':
				$adapter = new ilCourseCertificateAdapter($object);
				$placeholderDescriptionObject = new ilCoursePlaceholderDescription();
				$placeholderValuesObject = new ilCoursePlaceholderValues();
				$certificatePath = ilCertificatePathConstants::COURSE_PATH . $object->getId() . '/';
				break;
			case 'scrm':
				$adapter = new ilSCORMCertificateAdapter($object);
				$placeholderDescriptionObject = new ilScormPlaceholderDescription($object);
				$placeholderValuesObject = new ilScormPlaceholderValues();
				$certificatePath = ilCertificatePathConstants::SCORM_PATH . $object->getId() . '/';
				break;
			case 'exc':
				$adapter = new ilExerciseCertificateAdapter($object);
				$placeholderDescriptionObject = new ilExercisePlaceholderDescription();
				$placeholderValuesObject = new ilExercisePlaceholderValues();
				$certificatePath = ilCertificatePathConstants::EXERCISE_PATH . $object->getId() . '/';
				break;
			default:
				throw new ilException(sprintf(
					'The type "%s" is currently not supported for certificates',
					$type
				));
				break;
		}

		$certificate = new ilCertificate(
			$adapter,
			$placeholderDescriptionObject,
			$placeholderValuesObject,
			$object->getId(),
			$certificatePath
		);

		return $certificate;
	}
}
