<?php


class TestPlaceHolderValues implements ilCertificatePlaceholderValues
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
	 * @throws ilInvalidCertificateException
	 * @return mixed - [PLACEHOLDER] => 'actual value'
	 * @throws ilException
	 */
	public function getPlaceholderValues($userId, $objId)
	{
		$testObject = ilObjectFactory::getInstanceByObjId($objId);

		$active_id = $testObject->getActiveIdOfUser($userId);
		$pass = ilObjTest::_getResultPass($active_id);

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

		$mark_obj = $testObject->mark_schema->getMatchingMark($percentage);
		$user_id = $testObject->_getUserIdFromActiveId($active_id);
		$user_data = ilObjUser::_lookupFields($user_id);

		$completionDate = false;
		if($user_data['usr_id'] > 0) {
			$completionDate = ilLPStatus::_lookupStatusChanged($objId, $userId);
		}

		$placeholders = $this->defaultPlaceHolderValuesObject->getPlaceholderValues($userId, $objId);

		$placeholders['RESULT_PASSED']      = ilUtil::prepareFormOutput($passed);
		$placeholders['RESULT_POINTS']      = ilUtil::prepareFormOutput($result_array['test']['total_reached_points']);
		$placeholders['RESULT_PERCENT']     = sprintf('%2.2f', $percentage) . '%';
		$placeholders['MAX_POINTS']         = ilUtil::prepareFormOutput($result_array['test']['total_max_points']);
		$placeholders['RESULT_MARK_SHORT']  = ilUtil::prepareFormOutput($mark_obj->getShortName());
		$placeholders['RESULT_MARK_LONG']   = ilUtil::prepareFormOutput($mark_obj->getOfficialName());
		$placeholders['TEST_TITLE']         = ilUtil::prepareFormOutput($testObject->getTitle());
		$placeholders['DATE_COMPLETED']     = ilDatePresentation::formatDate(new ilDate($completionDate, IL_CAL_DATETIME));
		$placeholders['DATETIME_COMPLETED'] = ilDatePresentation::formatDate(new ilDateTime($completionDate, IL_CAL_DATETIME));

		return $placeholders;
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
		// TODO: Implement getPlaceholderValuesForPreview() method.
	}
}
