<?php


class ilExercisePlaceHolderValues implements ilCertificatePlaceholderValues
{
	/**
	 * @var ilLanguage|null
	 */
	private $language;

	/**
	 * @var ilDefaultPlaceholderValues|null
	 */
	private $defaultPlaceHolderValuesObject;

	/**
	 * @param ilDefaultPlaceholderValues|null $defaultPlaceholderValues
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
	 * @throws ilDateTimeException
	 * @throws ilException
	 */
	public function getPlaceholderValues(int $userId, int $objId) : array
	{
		$exerciseObject = ilObjectFactory::getInstanceByObjId($objId);

		$mark = ilLPMarks::_lookupMark($userId, $objId);
		$status = ilExerciseMembers::_lookupStatus($objId, $userId);

		$completionDate = ilLPStatus::_lookupStatusChanged($objId, $userId);

		$placeHolders = $this->defaultPlaceHolderValuesObject->getPlaceholderValues($userId, $objId);

		$placeHolders['RESULT_PASSED']      = ilUtil::prepareFormOutput($this->language->txt('exc_' . $status));
		$placeHolders['RESULT_MARK']        = ilUtil::prepareFormOutput($mark);
		$placeHolders['EXERCISE_TITLE']     = ilUtil::prepareFormOutput($exerciseObject->getTitle());
		$placeholders['DATE_COMPLETED']     = ilDatePresentation::formatDate(new ilDate($completionDate, IL_CAL_DATETIME));
		$placeholders['DATETIME_COMPLETED'] = ilDatePresentation::formatDate(new ilDateTime($completionDate, IL_CAL_DATETIME));

		return $placeHolders;
	}

	/**
	 * This method is different then the 'getPlaceholderValues' method, this
	 * method is used to create a placeholder value array containing dummy values
	 * that is used to create a preview certificate.
	 *
	 * @return array
	 */
	public function getPlaceholderValuesForPreview()
	{
		return $this->defaultPlaceHolderValuesObject->getPlaceholderValuesForPreview();
	}
}
