<?php


class ilCoursePlaceholderValues implements ilCertificatePlaceholderValues
{
	/**
	 * @var ilDefaultPlaceholderValues
	 */
	private $defaultPlaceHolderValuesObject;

	/**
	 * @param ilDefaultPlaceholderValues $defaultPlaceholderValues
	 * @param ilLanguage|null $language
	 */
	public function __construct(ilDefaultPlaceholderValues $defaultPlaceholderValues = null, ilLanguage $language = null)
	{
		global $DIC;

		if (null === $language) {
			$language = $DIC->language();
		}
		$this->language = $language;

		if (null === $defaultPlaceholderValues) {
			$defaultPlaceholderValues = new ilDefaultPlaceholderValues($language);
		}

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
	public function getPlaceholderValues($userId, $objId)
	{
		$courseObject = ilObjectFactory::getInstanceByObjId($objId);

		$placeholders = $this->defaultPlaceHolderValuesObject->getPlaceholderValues($userId, $objId);
		$completionDate =  ilCourseParticipants::getDateTimeOfPassed($objId, $userId);

		$placeholders['COURSE_TITLE']       = ilUtil::prepareFormOutput($courseObject->getTitle());
		$placeholders["DATE_COMPLETED"]     = ilDatePresentation::formatDate(new ilDate($completionDate, IL_CAL_DATETIME));
		$placeholders["DATETIME_COMPLETED"] = ilDatePresentation::formatDate(new ilDateTime($completionDate, IL_CAL_DATETIME));

		return $placeholders;
	}

	/**
	 * This method is different then the 'getPlaceholderValues' method, this
	 * method is used to create a placeholder value array containing dummy values
	 * that is used to create a preview certificate.
	 *
	 * @param $userId
	 * @param $objId
	 * @return mixed
	 */
	public function getPlaceholderValuesForPreview()
	{
		return $this->defaultPlaceHolderValuesObject->getPlaceholderValuesForPreview();
	}
}
