<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
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
    public function create(\ilObject $object) : ilCertificateGUI
    {
        global $DIC;

        $type = $object->getType();
        $objectId = $object->getId();

        $logger = $DIC->logger()->cert();

        $templateRepository = new ilCertificateTemplateRepository($this->dic->database(), $logger);
        $deleteAction = new ilCertificateTemplateDeleteAction($templateRepository);
        $pathFactory = new ilCertificatePathFactory();

        $certificatePath = $pathFactory->create($object);

        switch ($type) {
            case 'tst':
                $placeholderDescriptionObject = new ilTestPlaceholderDescription();
                $placeholderValuesObject = new ilTestPlaceholderValues();

                $formFactory = new ilCertificateSettingsTestFormRepository(
                    $objectId,
                    $certificatePath,
                    false,
                    $object,
                    $DIC->language(),
                    $DIC->ctrl(),
                    $DIC->access(),
                    $DIC->toolbar(),
                    $placeholderDescriptionObject
                );

                $deleteAction = new ilCertificateTestTemplateDeleteAction(
                    $deleteAction,
                    new ilCertificateObjectHelper()
                );

                break;
            case 'crs':
                $hasAdditionalElements = true;

                $placeholderDescriptionObject = new ilCoursePlaceholderDescription($objectId);
                $placeholderValuesObject = new ilCoursePlaceholderValues();


                $formFactory = new ilCertificateSettingsCourseFormRepository(
                    $object,
                    $certificatePath,
                    false,
                    $DIC->language(),
                    $DIC->ctrl(),
                    $DIC->access(),
                    $DIC->toolbar(),
                    $placeholderDescriptionObject
                );

                break;
            case 'exc':
                $placeholderDescriptionObject = new ilExercisePlaceholderDescription();
                $placeholderValuesObject = new ilExercisePlaceholderValues();

                $formFactory = new ilCertificateSettingsExerciseRepository(
                    $object,
                    $certificatePath,
                    false,
                    $DIC->language(),
                    $DIC->ctrl(),
                    $DIC->access(),
                    $DIC->toolbar(),
                    $placeholderDescriptionObject
                );

                break;
            case 'sahs':
                $placeholderDescriptionObject = new ilScormPlaceholderDescription($object);
                $placeholderValuesObject = new ilScormPlaceholderValues();

                $formFactory = new ilCertificateSettingsScormFormRepository(
                    $object,
                    $certificatePath,
                    true,
                    $DIC->language(),
                    $DIC->ctrl(),
                    $DIC->access(),
                    $DIC->toolbar(),
                    $placeholderDescriptionObject
                );
                break;
            case 'lti':
                $placeholderDescriptionObject = new ilLTIConsumerPlaceholderDescription();
                $placeholderValuesObject = new ilLTIConsumerPlaceholderValues();

                $formFactory = new ilCertificateSettingsLTIConsumerFormRepository(
                    $object,
                    $certificatePath,
                    true,
                    $DIC->language(),
                    $DIC->ctrl(),
                    $DIC->access(),
                    $DIC->toolbar(),
                    $placeholderDescriptionObject
                );
                break;
            case 'cmix':
                $placeholderDescriptionObject = new ilCmiXapiPlaceholderDescription();
                $placeholderValuesObject = new ilCmiXapiPlaceholderValues();

                $formFactory = new ilCertificateSettingsCmiXapiFormRepository(
                    $object,
                    $certificatePath,
                    true,
                    $DIC->language(),
                    $DIC->ctrl(),
                    $DIC->access(),
                    $DIC->toolbar(),
                    $placeholderDescriptionObject
                );
                break;
            case 'prg':
                $placeholderDescriptionObject =
                new ilStudyProgrammePlaceholderDescription();
                $placeholderValuesObject =
                new ilStudyProgrammePlaceholderValues();
                $formFactory = new ilCertificateSettingsStudyProgrammeFormRepository(
                    $object,
                    $certificatePath,
                    true,
                    $DIC->language(),
                    $DIC->ctrl(),
                    $DIC->access(),
                    $DIC->toolbar(),
                    $placeholderDescriptionObject
                );
                break;
            default:
                throw new ilException(sprintf('The type "%s" is currently not defined for certificates', $type));
                break;
        }

        $gui = new ilCertificateGUI(
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
