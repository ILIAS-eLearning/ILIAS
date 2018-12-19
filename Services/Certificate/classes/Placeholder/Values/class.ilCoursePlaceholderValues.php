<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCoursePlaceholderValues implements ilCertificatePlaceholderValues
{
	/**
	 * @var ilDefaultPlaceholderValues
	 */
	private $defaultPlaceHolderValuesObject;

	/**
	 * @var ilLanguage|null
	 */
	private $language;

	/**
	 * @var ilCertificateObjectHelper|null
	 */
	private $objectHelper;

	/**
	 * @var ilCertificateParticipantsHelper|null
	 */
	private $participantsHelper;

	/**
	 * @var ilCertificateUtilHelper
	 */
	private $ilUtilHelper;

	/**
	 * @param ilDefaultPlaceholderValues $defaultPlaceholderValues
	 * @param ilLanguage|null $language
	 * @param ilCertificateObjectHelper|null $objectHelper
	 * @param ilCertificateParticipantsHelper|null $participantsHelper
	 * @param ilCertificateUtilHelper $ilUtilHelper
	 * @param ilCertificateDateHelper|null $ilDateHelper
	 */
	public function __construct(
		ilDefaultPlaceholderValues $defaultPlaceholderValues = null,
		ilLanguage $language = null,
		ilCertificateObjectHelper $objectHelper = null,
		ilCertificateParticipantsHelper $participantsHelper = null,
		ilCertificateUtilHelper $ilUtilHelper = null
	) {
		if (null === $language) {
			global $DIC;
			$language = $DIC->language();
		}
		$this->language = $language;

		if (null === $defaultPlaceholderValues) {
			$defaultPlaceholderValues = new ilDefaultPlaceholderValues();
		}

		if (null === $objectHelper) {
			$objectHelper = new ilCertificateObjectHelper();
		}
		$this->objectHelper = $objectHelper;

		if (null === $participantsHelper) {
			$participantsHelper = new ilCertificateParticipantsHelper();
		}
		$this->participantsHelper = $participantsHelper;

		if (null === $ilUtilHelper) {
			$ilUtilHelper = new ilCertificateUtilHelper();
		}
		$this->ilUtilHelper = $ilUtilHelper;

		$this->defaultPlaceHolderValuesObject = $defaultPlaceholderValues;
	}

	/**
	 * This method MUST return an array that contains the
	 * actual data for the given user of the given object.
	 *
	 * ilInvalidCertificateException MUST be thrown if the
	 * data could not be determined or the user did NOT
	 * achieve the certificate.
	 *
	 * @param $userId
	 * @param $objId
	 * @return mixed - [PLACEHOLDER] => 'actual value'
	 * @throws ilException
	 */
	public function getPlaceholderValues(int $userId, int $objId) : array
	{
		$courseObject = $this->objectHelper->getInstanceByObjId($objId);

		$placeholders = $this->defaultPlaceHolderValuesObject->getPlaceholderValues($userId, $objId);

		$placeholders['COURSE_TITLE']  = $this->ilUtilHelper->prepareFormOutput($courseObject->getTitle());

		return $placeholders;
	}

	/**
	 * This method is different then the 'getPlaceholderValues' method, this
	 * method is used to create a placeholder value array containing dummy values
	 * that is used to create a preview certificate.
	 *
	 * @param int $userId
	 * @param int $objId
	 * @return mixed
	 */
	public function getPlaceholderValuesForPreview(int $userId, int $objId)
	{
		$placeholders =  $this->defaultPlaceHolderValuesObject->getPlaceholderValuesForPreview($userId, $objId);

		$object = $this->objectHelper->getInstanceByObjId($objId);

		$placeholders['COURSE_TITLE'] = ilUtil::prepareFormOutput($object->getTitle());

		return $placeholders;
	}
}
