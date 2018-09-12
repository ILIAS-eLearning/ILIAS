<?php


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
	 * @var ilObjectHelper|null
	 */
	private $objectHelper;

	/**
	 * @var ilParticipantsHelper|null
	 */
	private $participantsHelper;

	/**
	 * @var ilUtilHelper
	 */
	private $ilUtilHelper;

	/**
	 * @var ilDateHelper|null
	 */
	private $ilDateHelper;

	/**
	 * @param ilDefaultPlaceholderValues $defaultPlaceholderValues
	 * @param ilLanguage|null $language
	 * @param ilObjectHelper|null $objectHelper
	 * @param ilParticipantsHelper|null $participantsHelper
	 * @param ilUtilHelper $ilUtilHelper
	 * @param ilDateHelper|null $ilDateHelper
	 */
	public function __construct(
		ilDefaultPlaceholderValues $defaultPlaceholderValues = null,
		ilLanguage $language = null,
		ilObjectHelper $objectHelper = null,
		ilParticipantsHelper $participantsHelper = null,
		ilUtilHelper $ilUtilHelper = null,
		ilDateHelper $ilDateHelper = null
	) {
		if (null === $language) {
			global $DIC;
			$language = $DIC->language();
		}
		$this->language = $language;

		if (null === $defaultPlaceholderValues) {
			$defaultPlaceholderValues = new ilDefaultPlaceholderValues($language);
		}

		if (null === $objectHelper) {
			$objectHelper = new ilObjectHelper();
		}
		$this->objectHelper = $objectHelper;

		if (null === $participantsHelper) {
			$participantsHelper = new ilParticipantsHelper();
		}
		$this->participantsHelper = $participantsHelper;

		if (null === $ilUtilHelper) {
			$ilUtilHelper = new ilUtilHelper();
		}
		$this->ilUtilHelper = $ilUtilHelper;

		if (null === $ilDateHelper) {
			$ilDateHelper = new ilDateHelper();
		}
		$this->ilDateHelper = $ilDateHelper;

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
		$completionDate = $this->participantsHelper->getDateTimeOfPassed($objId, $userId);

		$placeholders['COURSE_TITLE']       = $this->ilUtilHelper->prepareFormOutput($courseObject->getTitle());
		$placeholders['DATE_COMPLETED']     = $this->ilDateHelper->formatDate($completionDate);
		$placeholders['DATETIME_COMPLETED'] = $this->ilDateHelper->formatDateTime($completionDate);

		return $placeholders;
	}

	/**
	 * This method is different then the 'getPlaceholderValues' method, this
	 * method is used to create a placeholder value array containing dummy values
	 * that is used to create a preview certificate.
	 *
	 * @return mixed
	 */
	public function getPlaceholderValuesForPreview()
	{
		return $this->defaultPlaceHolderValuesObject->getPlaceholderValuesForPreview();
	}
}
