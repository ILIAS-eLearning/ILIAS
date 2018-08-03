<?php


class ilScormPlaceholderValues implements ilCertificatePlaceholderValues
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
	 * @throws ilInvalidCertificateException
	 * @return mixed - [PLACEHOLDER] => 'actual value'
	 * @throws ilException
	 */
	public function getPlaceholderValues($userId, $objId)
	{
		$this->lng->loadLanguageModule('certificate');

		$points = $this->object->getPointsInPercent();
		$txtPoints = number_format($points, 1, $this->lng->txt('lang_sep_decimal'), $this->lng->txt('lang_sep_thousand')) . ' %';
		if (is_null($points)) {
			$txtPoints = $this->lng->txt('certificate_points_notavailable');
		}

		$max_points = $this->object->getMaxPoints();
		$txtMaxPoints = $max_points;
		if (is_null($max_points)) {
			$txtMaxPoints = $this->lng->txt('certificate_points_notavailable');
		} else if($max_points != floor($max_points)) {
			$txtMaxPoints = number_format($max_points, 1, $this->lng->txt('lang_sep_decimal'), $this->lng->txt('lang_sep_thousand'));
		}

		$completionDate = $this->getUserCompletionDate($userId);

		$placeHolders = $this->defaultPlaceHolderValuesObject->getPlaceholderValues($userId, $objId);

		$placeHolders['SCORM_TITLE']        = ilUtil::prepareFormOutput($this->object->getTitle());
		$placeHolders['SCORM_POINTS']       = $txtPoints;
		$placeHolders['SCORM_POINTS_MAX']   = $txtMaxPoints;
		$placeholders['DATE_COMPLETED']     = ilDatePresentation::formatDate(new ilDate($completionDate, IL_CAL_DATETIME));
		$placeholders['DATETIME_COMPLETED'] = ilDatePresentation::formatDate(new ilDateTime($completionDate, IL_CAL_DATETIME));

		$olp = ilObjectLP::getInstance($this->object->getId());
		$collection = $olp->getCollectionInstance();

		if($collection) {
			$counter = 0;
			foreach($collection->getPossibleItems() as $item_id => $sahs_item) {
				if($collection->isAssignedEntry($item_id)) {
					$placeHolders['[SCO_T_' . $counter . ']'] = $sahs_item['title'];
					$a_scores = $collection->getScoresForUserAndCP_Node_Id($item_id, $GLOBALS['DIC']['ilUser']->getId());

					$placeHolders['[SCO_P_' . $counter . ']'] = $this->lng->txt('certificate_points_notavailable');
					if ($a_scores['raw'] !== null) {
						$placeHolders['[SCO_P_' . $counter . ']'] = number_format(
							$a_scores['raw'],
							1,
							$this->language->txt('lang_sep_decimal'),
							$this->language->txt('lang_sep_thousand')
						);
					}

					$placeHolders['[SCO_PM_' . $counter . ']'] = $this->language->txt('certificate_points_notavailable');
					if ($a_scores['max'] !== null) {
						$placeHolders['[SCO_PM_' . $counter . ']'] = number_format(
							$a_scores['max'],
							1,
							$this->language->txt('lang_sep_decimal'),
							$this->language->txt('lang_sep_thousand')
						);
					}

					$placeHolders['[SCO_PP_' . $counter . ']'] = $this->lng->txt('certificate_points_notavailable');
					if ($a_scores['scaled'] !== null) {
						$placeHolders['[SCO_PP_' . $counter . ']'] = number_format(
							($a_scores['scaled'] * 100),
							1,
							$this->lng->txt('lang_sep_decimal'),
							$this->lng->txt('lang_sep_thousand')
						);

						$placeHolders['[SCO_PP_' . $counter . ']'] .= ' %';
					}

					$counter++;
				}
			}
		}

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
		// TODO: Implement getPlaceholderValuesForPreview() method.
	}
}
