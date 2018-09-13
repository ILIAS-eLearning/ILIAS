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
	 * @var ilCertificateLPMarksHelper|null
	 */
	private $lpMarksHelper;

	/**
	 * @var ilCertificateObjectHelper|null
	 */
	private $objectHelper;

	/**
	 * @var ilCertificateExerciseMembersHelper|null
	 */
	private $exerciseMembersHelper;

	/**
	 * @var ilCertificateLPStatusHelper|null
	 */
	private $lpStatusHelper;

	/**
	 * @var ilCertificateUtilHelper|null
	 */
	private $utilHelper;

	/**
	 * @var ilCertificateDateHelper|null
	 */
	private $dateHelper;

	/**
	 * @param ilDefaultPlaceholderValues|null $defaultPlaceholderValues
	 * @param ilLanguage|null $language
	 * @param ilCertificateObjectHelper|null $objectHelper
	 * @param ilCertificateLPMarksHelper|null $lpMarksHelper
	 * @param ilCertificateExerciseMembersHelper|null $exerciseMembersHelper
	 * @param ilCertificateLPStatusHelper|null $lpStatusHelper
	 * @param ilCertificateUtilHelper|null $utilHelper
	 * @param ilCertificateDateHelper|null $dateHelper
	 */
	public function __construct(
		ilDefaultPlaceholderValues $defaultPlaceholderValues = null,
		ilLanguage $language = null,
		ilCertificateObjectHelper $objectHelper = null,
		ilCertificateLPMarksHelper $lpMarksHelper = null,
		ilCertificateExerciseMembersHelper $exerciseMembersHelper = null,
		ilCertificateLPStatusHelper $lpStatusHelper = null,
		ilCertificateUtilHelper $utilHelper = null,
		ilCertificateDateHelper $dateHelper = null
	) {
		if (null === $language) {
			global $DIC;
			$language = $DIC->language();
		}
		$this->language = $language;

		if (null === $defaultPlaceholderValues) {
			$defaultPlaceholderValues = new ilDefaultPlaceholderValues($language);
		}
		$this->defaultPlaceHolderValuesObject = $defaultPlaceholderValues;

		if (null === $objectHelper) {
			$objectHelper = new ilCertificateObjectHelper();
		}
		$this->objectHelper = $objectHelper;

		if (null === $lpMarksHelper) {
			$lpMarksHelper = new ilCertificateLPMarksHelper();
		}
		$this->lpMarksHelper = $lpMarksHelper;

		if (null === $exerciseMembersHelper) {
			$exerciseMembersHelper = new ilCertificateExerciseMembersHelper();
		}
		$this->exerciseMembersHelper = $exerciseMembersHelper;

		if (null === $lpStatusHelper) {
			$lpStatusHelper = new ilCertificateLPStatusHelper();
		}
		$this->lpStatusHelper = $lpStatusHelper;

		if (null === $utilHelper) {
			$utilHelper = new ilCertificateUtilHelper();
		}
		$this->utilHelper = $utilHelper;

		if (null === $dateHelper) {
			$dateHelper = new ilCertificateDateHelper();
		}
		$this->dateHelper = $dateHelper;
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
		$exerciseObject = $this->objectHelper->getInstanceByObjId($objId);

		$mark = $this->lpMarksHelper->lookUpMark($userId, $objId);
		$status = $this->exerciseMembersHelper->lookUpStatus($objId, $userId);

		$completionDate = $this->lpStatusHelper->lookupStatusChanged($objId, $userId);

		$placeHolders = $this->defaultPlaceHolderValuesObject->getPlaceholderValues($userId, $objId);

		$placeHolders['RESULT_PASSED']      = $this->utilHelper->prepareFormOutput($this->language->txt('exc_' . $status));
		$placeHolders['RESULT_MARK']        = $this->utilHelper->prepareFormOutput($mark);
		$placeHolders['EXERCISE_TITLE']     = $this->utilHelper->prepareFormOutput($exerciseObject->getTitle());
		$placeHolders['DATE_COMPLETED']     = $this->dateHelper->formatDate($completionDate);
		$placeHolders['DATETIME_COMPLETED'] = $this->dateHelper->formatDateTime($completionDate);

		return $placeHolders;
	}

	/**
	 * This method is different then the 'getPlaceholderValues' method, this
	 * method is used to create a placeholder value array containing dummy values
	 * that is used to create a preview certificate.
	 *
	 * @return array
	 */
	public function getPlaceholderValuesForPreview() : array
	{
		return $this->defaultPlaceHolderValuesObject->getPlaceholderValuesForPreview();
	}
}
