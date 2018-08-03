<?php


class ilCertificateGUIFactory
{
	/**
	 * @var
	 */
	private $dic;

	/**
	 * @param \ILIAS\DI\Container|null $dic
	 */
	public function __construct(\ILIAS\DI\Container $dic = null)
	{
		if (null === $dic) {
			global $DIC;
			$dic = $DIC;
		}
		$this->dic = $dic;
	}

	/**
	 * @param ilObject $object
	 * @return ilCertificateGUI
	 * @throws ilException
	 */
	public function create(\ilObject $object)
	{
		global $DIC;

		$type = $object->getType();
		$objectId = $object->getId();

		$templateRepository = new ilCertificateTemplateRepository($this->dic->database());
		$deleteAction = new ilCertificateTemplateDeleteAction($templateRepository);

		switch ($type) {
			case 'tst':
				$placeholderDescriptionObject = new TestPlaceholderDescription();
				$placeholderValuesObject = new TestPlaceHolderValues();
				$adapter = new ilTestCertificateAdapter($object);

				$formFactory = new ilCertificateSettingsTestFormRepository(
					$object,
					$DIC->language(),
					$DIC->ui()->mainTemplate(),
					$DIC->ctrl(),
					$DIC->access(),
					$DIC->toolbar(),
					$placeholderDescriptionObject
				);

				$certificatePath = ilCertificatePathConstants::TEST_PATH . $objectId . '/';

				$deleteAction = new ilCertificateTestTemplateDeleteAction($deleteAction);

				break;
			case 'crs':
				$adapter = new ilCourseCertificateAdapter($object);
				$placeholderDescriptionObject = new CoursePlaceholderDescription();
				$placeholderValuesObject = new CoursePlaceholderValues();

				$formFactory = new ilCertificateSettingsFormRepository(
					$DIC->language(),
					$DIC->ui()->mainTemplate(),
					$DIC->ctrl(),
					$DIC->access(),
					$DIC->toolbar(),
					$placeholderDescriptionObject
				);

				$certificatePath = ilCertificatePathConstants::COURSE_PATH . $objectId . '/';
				break;
			case 'exc':
				$adapter = new ilExerciseCertificateAdapter($object);
				$placeholderDescriptionObject = new ExercisePlaceholderDescription();
				$placeholderValuesObject = new ilExercisePlaceHolderValues();

				$formFactory = new ilCertificateSettingsExerciseRepository(
					$DIC->language(),
					$DIC->ui()->mainTemplate(),
					$DIC->ctrl(),
					$DIC->access(),
					$DIC->toolbar(),
					$placeholderDescriptionObject
				);

				$certificatePath = ilCertificatePathConstants::EXERCISE_PATH . $objectId . '/';
				break;
			case 'scorm':
				$adapter = new ilSCORMCertificateAdapter($object);
				$placeholderDescriptionObject = new ilScormPlaceholderDescription();
				$placeholderValuesObject = new ilScormPlaceholderValues();

				$formFactory = new ilCertificateSettingsScormFormRepository(
					$DIC->language(),
					$DIC->ui()->mainTemplate(),
					$DIC->ctrl(),
					$DIC->access(),
					$DIC->toolbar(),
					$placeholderDescriptionObject
				);

				$certificatePath = ilCertificatePathConstants::SCORM_PATH . $objectId . '/';

				break;
			case 'skl':
				$skillLevelId = (int) $_GET["level_id"];

				$adapter = new ilSkillCertificateAdapter($object, $skillLevelId);
				$placeholderDescriptionObject = new ilDefaultPlaceholderDescription();
				$placeholderValuesObject = new ilDefaultPlaceholderValues();

				$formFactory = new ilCertificateSettingsFormRepository(
					$DIC->language(),
					$DIC->ui()->mainTemplate(),
					$DIC->ctrl(),
					$DIC->access(),
					$DIC->toolbar(),
					$placeholderDescriptionObject
				);

				$certificatePath = ilCertificatePathConstants::SKILL_PATH . $objectId . '/' . $skillLevelId;

				break;
			default:
				throw new ilException(sprintf('The type "%s" is currently not defined for certificates', $type));
				break;
		}

		$gui = new ilCertificateGUI(
			$adapter,
			$placeholderDescriptionObject,
			$placeholderValuesObject,
			$objectId,
			$certificatePath,
			$formFactory,
			$deleteAction
		);

		return $gui;
	}
}
