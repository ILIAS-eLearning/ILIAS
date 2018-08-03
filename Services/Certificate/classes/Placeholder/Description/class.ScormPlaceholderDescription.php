<?php


class ScormPlaceholderDescription implements ilCertificatePlaceholderDescription
{
	/**
	 * @var ilDefaultPlaceholderDescription
	 */
	private $defaultPlaceHolderDescriptionObject;

	/**
	 * @var ilLanguage|null
	 */
	private $language;

	/**
	 * @var array
	 */
	private $placeHolders;

	/**
	 * @param ilDefaultPlaceholderDescription|null $defaultPlaceholderDescriptionObject
	 * @param ilLanguage|null $language
	 */
	public function __construct(ilDefaultPlaceholderDescription $defaultPlaceholderDescriptionObject = null, ilLanguage $language = null)
	{
		global $DIC;

		if (null === $language) {
			$language = $DIC->language();
		}
		$this->language = $language;

		if (null === $defaultPlaceholderDescriptionObject) {
			$defaultPlaceholderDescriptionObject = new ilDefaultPlaceholderDescription($language);
		}
		$this->defaultPlaceHolderDescriptionObject = $defaultPlaceholderDescriptionObject;

		$this->placeHolders = $this->defaultPlaceHolderDescriptionObject->getPlaceholderDescriptions();

		$this->placeHolders['SCORM_TITLE']      = $language->txt('certificate_ph_scormtitle');
		$this->placeHolders['SCORM_POINTS']     = $language->txt('certificate_ph_scormpoints');
		$this->placeHolders['SCORM_POINTS_MAX'] = $language->txt('certificate_ph_scormmaxpoints');
	}

	/**
	 * This methods MUST return an array containing an array with
	 * the the description as array value.
	 *
	 * @return mixed - [PLACEHOLDER] => 'description'
	 */
	public function createPlaceholderHtmlDescription()
	{
		$template = new ilTemplate('tpl.scorm_description.html', true, true, 'Services/Certificates');
		$template->setCurrentBlock('items');

		foreach($this->placeHolders as $id => $caption) {
			$template->setVariable('ID', $id);
			$template->setVariable('TXT', $caption);
			$template->parseCurrentBlock();
		}

		$template->setVariable('PH_INTRODUCTION', $this->language->txt('certificate_ph_introduction'));

		$olp = ilObjectLP::getInstance($this->object->getId());
		$collection = $olp->getCollectionInstance();
		if($collection) {
			$items = $collection->getPossibleItems();
		}

		if(!$items) {
			$template->setCurrentBlock('NO_SCO');
			$template->setVariable('PH_NO_SCO',$this->language->txt('certificate_ph_no_sco'));
			$template->parseCurrentBlock();
		}
		else {
			$template->setCurrentBlock('SCOS');
			$template->setVariable('PH_SCOS',$this->language->txt('certificate_ph_scos'));
			$template->parseCurrentBlock();
			$template->setCurrentBlock('SCO_HEADER');
			$template->setVariable('PH_TITLE_SCO',$this->language->txt('certificate_ph_title_sco'));
			//$template->setVariable('PH_PH',$lng->txt('certificate_ph_ph'));
			$template->setVariable('PH_SCO_TITLE',$this->language->txt('certificate_ph_sco_title'));
			$template->setVariable('PH_SCO_POINTS_RAW',$this->language->txt('certificate_ph_sco_points_raw'));
			$template->setVariable('PH_SCO_POINTS_MAX',$this->language->txt('certificate_ph_sco_points_max'));
			$template->setVariable('PH_SCO_POINTS_SCALED',$this->language->txt('certificate_ph_sco_points_scaled'));
			$template->parseCurrentBlock();
		}

		if($collection) {
			$counter = 0;
			foreach($items as $item_id => $sahs_item) {
				if($collection->isAssignedEntry($item_id)) {
					$template->setCurrentBlock('SCO');
					$template->setVariable('SCO_TITLE',$sahs_item['title']);
					$template->setVariable('PH_SCO_TITLE','[SCO_T_' . $counter . ']');
					$template->setVariable('PH_SCO_POINTS_RAW','[SCO_P_' . $counter . ']');
					$template->setVariable('PH_SCO_POINTS_MAX','[SCO_PM_' . $counter . ']');
					$template->setVariable('PH_SCO_POINTS_SCALED','[SCO_PP_' . $counter . ']');
					$template->parseCurrentBlock();
					$counter++;
				}
			}
		}

		return $template->get();
	}

	/**
	 * This method MUST return an array containing an array with
	 * the the description as array value.
	 *
	 * @return mixed - [PLACEHOLDER] => 'description'
	 */
	public function getPlaceholderDescriptions()
	{
		return $this->placeHolders;
	}
}
