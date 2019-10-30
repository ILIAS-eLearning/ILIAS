<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateSettingsCourseFormRepository implements ilCertificateFormRepository
{
	/**
	 * @var ilLanguage
	 */
	private $language;

	/**
	 * @var ilCertificateSettingsFormRepository
	 */
	private $settingsFromFactory;

	/**
	 * @var \ilObjCourse
	 */
	private $object;

	/**
	 * @var ilObjectLP|mixed
	 */
	private $learningProgressObject;

	/**
	 * @var ilCertificateObjUserTrackingHelper|null
	 */
	private $trackingHelper;

	/**
	 * @var ilCertificateObjectHelper|null
	 */
	private $objectHelper;

	/**
	 * @var ilCertificateObjectLPHelper
	 */
	private $lpHelper;

	/**
	 * @var ilTree|mixed|null
	 */
	private $tree;

	/**
	 * @var ilSetting|null
	 */
	private $setting;

	/**
	 * @param ilObject $object
	 * @param string $certificatePath
	 * @param ilLanguage $language
	 * @param ilTemplate $template
	 * @param ilCtrl $controller
	 * @param ilAccess $access
	 * @param ilToolbarGUI $toolbar
	 * @param ilCertificatePlaceholderDescription $placeholderDescriptionObject
	 * @param ilObjectLP|null $learningProgressObject
	 * @param ilCertificateSettingsFormRepository|null $settingsFromFactory
	 * @param ilCertificateObjUserTrackingHelper|null $trackingHelper
	 * @param ilCertificateObjectHelper|null $objectHelper
	 * @param ilCertificateObjectLPHelper|null $lpHelper
	 * @param ilTree|null $tree
	 * @param ilSetting|null $setting
	 */
	public function __construct(
		\ilObject $object,
		string $certificatePath,
		ilLanguage $language,
		ilTemplate $template,
		ilCtrl $controller,
		ilAccess $access,
		ilToolbarGUI $toolbar,
		ilCertificatePlaceholderDescription $placeholderDescriptionObject,
		ilObjectLP $learningProgressObject = null,
		ilCertificateSettingsFormRepository $settingsFromFactory = null,
		ilCertificateObjUserTrackingHelper $trackingHelper = null,
		ilCertificateObjectHelper $objectHelper = null,
		ilCertificateObjectLPHelper $lpHelper = null,
		ilTree $tree = null,
		ilSetting $setting = null
	) {
		$this->object = $object;

		$this->language = $language;

		if (null === $settingsFromFactory) {
			$settingsFromFactory = new ilCertificateSettingsFormRepository(
				$object->getId(),
				$certificatePath,
				$language,
				$template,
				$controller,
				$access,
				$toolbar,
				$placeholderDescriptionObject
			);
		}
		$this->settingsFromFactory = $settingsFromFactory;

		if (null === $learningProgressObject) {
			$learningProgressObject = ilObjectLP::getInstance($this->object->getId());
		}
		$this->learningProgressObject = $learningProgressObject;

		if (null === $trackingHelper) {
			$trackingHelper = new ilCertificateObjUserTrackingHelper();
		}
		$this->trackingHelper = $trackingHelper;

		if (null === $objectHelper) {
			$objectHelper = new ilCertificateObjectHelper();
		}
		$this->objectHelper = $objectHelper;

		if (null === $lpHelper) {
			$lpHelper = new ilCertificateObjectLPHelper();
		}
		$this->lpHelper = $lpHelper;

		if (null === $tree) {
			global $DIC;
			$tree = $DIC['tree'];
		}
		$this->tree = $tree;

		if (null === $setting) {
			$setting = new ilSetting('crs');
		}
		$this->setting = $setting;
	}

	/**
	 * @param ilCertificateGUI $certificateGUI
	 * @param ilCertificate $certificateObject
	 *
	 * @return ilPropertyFormGUI
	 *
	 * @throws \ILIAS\Filesystem\Exception\FileAlreadyExistsException
	 * @throws \ILIAS\Filesystem\Exception\FileNotFoundException
	 * @throws \ILIAS\Filesystem\Exception\IOException
	 * @throws ilDatabaseException
	 * @throws ilException
	 * @throws ilWACException
	 */
	public function createForm(ilCertificateGUI $certificateGUI, ilCertificate $certificateObject)
	{
		$form = $this->settingsFromFactory->createForm($certificateGUI, $certificateObject);

		$objectLearningProgressSettings = new ilLPObjSettings($this->object->getId());

		$mode = $objectLearningProgressSettings->getMode();
		if (!$this->trackingHelper->enabledLearningProgress() || $mode == ilLPObjSettings::LP_MODE_DEACTIVATED) {
			$subitems = new ilRepositorySelector2InputGUI($this->language->txt('objects'), 'subitems', true);

			$formSection = new \ilFormSectionHeaderGUI();
			$formSection->setTitle($this->language->txt('cert_form_sec_add_features'));
			$form->addItem($formSection);

			$exp = $subitems->getExplorerGUI();
			$exp->setSkipRootNode(true);
			$exp->setRootId($this->object->getRefId());
			$exp->setTypeWhiteList($this->getLPTypes($this->object->getId()));

			$objectHelper = $this->objectHelper;
			$lpHelper = $this->lpHelper;
			$subitems->setTitleModifier(function ($id) use ($objectHelper, $lpHelper) {
				if (null === $id) {
					return '';
				}
				$obj_id = $objectHelper->lookupObjId((int) $id);
				$olp = $lpHelper->getInstance($obj_id);

				$invalid_modes = $this->getInvalidLPModes();

				$mode = $olp->getModeText($olp->getCurrentMode());

				if(in_array($olp->getCurrentMode(), $invalid_modes)) {
					$mode = '<strong>' . $mode . '</strong>';
				}
				return $objectHelper->lookupTitle($obj_id) . ' (' . $mode . ')';
			});

			$subitems->setRequired(true);
			$form->addItem($subitems);
		}

		return $form;
	}

	/**
	 * @param array $formFields
	 */
	public function save(array $formFields)
	{
		$invalidModes = $this->getInvalidLPModes();

		$titlesOfObjectsWithInvalidModes = array();
		$refIds = array();
		if (isset($formFields['subitems'])) {
			$refIds = $formFields['subitems'];
		}

		foreach($refIds as $refId) {
			$objectId = $this->objectHelper->lookupObjId((int) $refId);
			$learningProgressObject = $this->lpHelper->getInstance($objectId);
			$currentMode = $learningProgressObject->getCurrentMode();
			if(in_array($currentMode, $invalidModes)) {
				$titlesOfObjectsWithInvalidModes[] = $this->objectHelper->lookupTitle($objectId);
			}
		}

		if(sizeof($titlesOfObjectsWithInvalidModes)) {
			$message = sprintf($this->language->txt('certificate_learning_progress_must_be_active'), implode(', ', $titlesOfObjectsWithInvalidModes));
			throw new ilException($message);
		}

		$this->setting->set('cert_subitems_' . $this->object->getId(), json_encode($formFields['subitems']));
	}

	/**
	 * @param string $content
	 * @return array|mixed
	 */
	public function fetchFormFieldData(string $content)
	{
		$formFields = $this->settingsFromFactory->fetchFormFieldData($content);

		$formFields['subitems'] = json_decode($this->setting->get('cert_subitems_' . $this->object->getId(), json_encode(array())));
		if ($formFields['subitems'] === 'null' || $formFields['subitems'] === null) {
			$formFields['subitems'] = array();
		}
		return $formFields;
	}

	/**
	 * @param int $a_parent_ref_id
	 * @return array
	 */
	private function getLPTypes(int $a_parent_ref_id) : array
	{
		$result = array();

		$root = $this->tree->getNodeData($a_parent_ref_id);
		$sub_items = $this->tree->getSubTree($root);
		array_shift($sub_items); // remove root

		foreach($sub_items as $node) {
			if($this->lpHelper->isSupportedObjectType($node['type'])) {
				$class = $this->lpHelper->getTypeClass($node['type']);
				$modes = $class::getDefaultModes($this->trackingHelper->enabledLearningProgress());

				if(sizeof($modes) > 1) {
					$result[] = $node['type'];
				}
			}
		}

		return $result;
	}

	/**
	 * @return array
	 */
	private function getInvalidLPModes()
	{
		$invalid_modes = array(
			ilLPObjSettings::LP_MODE_DEACTIVATED,
			ilLPObjSettings::LP_MODE_UNDEFINED
		);

		// without active LP the following modes cannot be supported
		if(!$this->trackingHelper->enabledLearningProgress()) {
			// status cannot be set without active LP
			$invalid_modes[] = ilLPObjSettings::LP_MODE_MANUAL;
			$invalid_modes[] = ilLPObjSettings::LP_MODE_MANUAL_BY_TUTOR;
			$invalid_modes[] = ilLPObjSettings::LP_MODE_COLLECTION_MANUAL;

			// mode cannot be configured without active LP
			$invalid_modes[] = ilLPObjSettings::LP_MODE_COLLECTION;
			$invalid_modes[] = ilLPObjSettings::LP_MODE_COLLECTION_MOBS;
			$invalid_modes[] = ilLPObjSettings::LP_MODE_COLLECTION_TLT;
			$invalid_modes[] = ilLPObjSettings::LP_MODE_SCORM;
			$invalid_modes[] = ilLPObjSettings::LP_MODE_VISITS; // ?
		}

		return $invalid_modes;
	}
}
