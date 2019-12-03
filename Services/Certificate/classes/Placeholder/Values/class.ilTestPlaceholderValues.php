<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilTestPlaceholderValues implements ilCertificatePlaceholderValues
{
	/**
	 * @var ilDefaultPlaceholderValues
	 */
	private $defaultPlaceholderValuesObject;

	/**
	 * @var ilCertificateObjectHelper|null
	 */
	private $objectHelper;

	/**
	 * @var ilCertificateTestObjectHelper
	 */
	private $testObjectHelper;

	/**
	 * @var ilCertificateUserObjectHelper
	 */
	private $userObjectHelper;

	/**
	 * @var ilCertificateUtilHelper|null
	 */
	private $utilHelper;

	/**
	 * @var ilCertificateLPStatusHelper|null
	 */
	private $lpStatusHelper;

	/**
	 * @var ilCertificateDateHelper|ilDatePresentation|null
	 */
	private $dateHelper;

	/**
	 * @var ilLanguage|null
	 */
	private $language;

	/**
	 * @param ilDefaultPlaceholderValues $defaultPlaceholderValues
	 * @param ilLanguage|null $language
	 * @param ilCertificateObjectHelper|null $objectHelper
	 * @param ilCertificateTestObjectHelper|null $testObjectHelper
	 * @param ilCertificateUserObjectHelper|null $userObjectHelper
	 * @param ilCertificateLPStatusHelper|null $lpStatusHelper
	 * @param ilCertificateUtilHelper|null $utilHelper
	 * @param ilDatePresentation|null $dateHelper
	 */
	public function __construct(
		ilDefaultPlaceholderValues $defaultPlaceholderValues = null,
		ilLanguage $language = null,
		ilCertificateObjectHelper $objectHelper = null,
		ilCertificateTestObjectHelper $testObjectHelper = null,
		ilCertificateUserObjectHelper $userObjectHelper = null,
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
			$defaultPlaceholderValues = new ilDefaultPlaceholderValues();
		}
		$this->defaultPlaceholderValuesObject = $defaultPlaceholderValues;

		if (null === $objectHelper) {
			$objectHelper = new ilCertificateObjectHelper();
		}
		$this->objectHelper = $objectHelper;

		if (null === $testObjectHelper) {
			$testObjectHelper = new ilCertificateTestObjectHelper();
		}
		$this->testObjectHelper = $testObjectHelper;

		if (null === $userObjectHelper) {
			$userObjectHelper = new ilCertificateUserObjectHelper();
		}
		$this->userObjectHelper = $userObjectHelper;

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
	 * @throws ilInvalidCertificateException
	 * @return mixed - [PLACEHOLDER] => 'actual value'
	 * @throws ilException
	 */
	public function getPlaceholderValues(int $userId, int $objId): array
	{
		/** @var ilObjTest $testObject */
		$testObject = $this->objectHelper->getInstanceByObjId($objId);

		$active_id = $testObject->getActiveIdOfUser($userId);
		$pass = $this->testObjectHelper->getResultPass($active_id);

		$result_array =& $testObject->getTestResult($active_id);
		if (strlen($pass)) {
			$result_array =& $testObject->getTestResult($active_id, $pass);
		}

		$passed = $this->language->txt('certificate_failed');
		if ($result_array['test']['passed']) {
			$passed = $this->language->txt('certificate_passed');
		}

		$percentage = 0;
		if ($result_array['test']['total_max_points']) {
			$percentage = ($result_array['test']['total_reached_points'] / $result_array['test']['total_max_points']) * 100;
		}

		$mark_obj = $testObject->getMarkSchema()->getMatchingMark($percentage);
		$user_data = $this->userObjectHelper->lookupFields($userId);

		$completionDate = false;
		if($user_data['usr_id'] > 0) {
			$completionDate = $this->lpStatusHelper->lookupStatusChanged($objId, $userId);
		}

		$placeholders = $this->defaultPlaceholderValuesObject->getPlaceholderValues($userId, $objId);

		$placeholders['RESULT_PASSED']      = $this->utilHelper->prepareFormOutput($passed);
		$placeholders['RESULT_POINTS']      = $this->utilHelper->prepareFormOutput($result_array['test']['total_reached_points']);
		$placeholders['RESULT_PERCENT']     = sprintf('%2.2f', $percentage) . '%';
		$placeholders['MAX_POINTS']         = $this->utilHelper->prepareFormOutput($result_array['test']['total_max_points']);
		$placeholders['RESULT_MARK_SHORT']  = $this->utilHelper->prepareFormOutput($mark_obj->getShortName());
		$placeholders['RESULT_MARK_LONG']   = $this->utilHelper->prepareFormOutput($mark_obj->getOfficialName());
		$placeholders['TEST_TITLE']         = $this->utilHelper->prepareFormOutput($testObject->getTitle());
		$placeholders['DATE_COMPLETED']     = '';
		$placeholders['DATETIME_COMPLETED'] = '';

		if ($completionDate !== false &&
			$completionDate !== null &&
			$completionDate !== ''
		) {
			$placeholders['DATE_COMPLETED']     = $this->dateHelper->formatDate($completionDate);
			$placeholders['DATETIME_COMPLETED'] = $this->dateHelper->formatDateTime($completionDate);
		}

		return $placeholders;
	}

	/**
	 * This method is different then the 'getPlaceholderValues' method, this
	 * method is used to create a placeholder value array containing dummy values
	 * that is used to create a preview certificate.
	 *
	 * @param int $userId
	 * @param int $objId
	 * @return array
	 */
	public function getPlaceholderValuesForPreview(int $userId, int $objId) : array
	{
		$placeholders = $this->defaultPlaceholderValuesObject->getPlaceholderValuesForPreview($userId, $objId);

		$object = $this->objectHelper->getInstanceByObjId($objId);

		$placeholders['RESULT_PASSED'] = $this->utilHelper->prepareFormOutput($this->language->txt('certificate_var_result_passed'));
		$placeholders['RESULT_POINTS'] = $this->utilHelper->prepareFormOutput($this->language->txt('certificate_var_result_points'));
		$placeholders['RESULT_PERCENT'] = $this->utilHelper->prepareFormOutput($this->language->txt('certificate_var_result_percent'));
		$placeholders['MAX_POINTS'] = $this->utilHelper->prepareFormOutput($this->language->txt('certificate_var_max_points'));
		$placeholders['RESULT_MARK_SHORT'] = $this->utilHelper->prepareFormOutput($this->language->txt('certificate_var_result_mark_short'));
		$placeholders['RESULT_MARK_LONG'] = $this->utilHelper->prepareFormOutput($this->language->txt('certificate_var_result_mark_long'));
		$placeholders['TEST_TITLE'] = $this->utilHelper->prepareFormOutput($object->getTitle());

		return $placeholders;
	}
}
