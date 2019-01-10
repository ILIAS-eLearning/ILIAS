<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificatePathFactory
{
	/**
	 * @param ilObject $object
	 * @return string
	 * @throws ilException
	 */
	public function createCertificatePath(ilObject $object)
	{
		$type = $object->getType();
		$objectId = $object->getId();

		if ($type === 'tst') {
			return ilCertificatePathConstants::TEST_PATH . $objectId . '/';
		} else if ($type === 'exc') {
			return ilCertificatePathConstants::EXERCISE_PATH . $objectId . '/';
		} else if ($type === 'sahs') {
			return ilCertificatePathConstants::SCORM_PATH . $objectId . '/';
		} else if ($type === 'crs') {
			return ilCertificatePathConstants::COURSE_PATH . $objectId . '/';
		}

		throw new ilException(sprintf('Unknown type "%s" for object (obj_id: "%s")creating certificate path', $type, $objectId));
	}
}
