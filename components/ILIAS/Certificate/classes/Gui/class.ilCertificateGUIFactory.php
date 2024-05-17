<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

use ILIAS\DI\Container;
use ILIAS\Exercise\Certificate\ExercisePlaceholderValues;
use ILIAS\Exercise\Certificate\ExercisePlaceholderDescription;
use ILIAS\Exercise\Certificate\CertificateSettingsExerciseRepository;
use ILIAS\Course\Certificate\CoursePlaceholderValues;
use ILIAS\Course\Certificate\CoursePlaceholderDescription;
use ILIAS\Course\Certificate\CertificateSettingsCourseFormRepository;
use ILIAS\StudyProgramme\Certificate\ilStudyProgrammePlaceholderValues;
use ILIAS\StudyProgramme\Certificate\ilStudyProgrammePlaceholderDescription;
use ILIAS\StudyProgramme\Certificate\ilCertificateSettingsStudyProgrammeFormRepository;
use ILIAS\Test\Certificate\TestPlaceholderValues;
use ILIAS\Test\Certificate\TestPlaceholderDescription;
use ILIAS\Course\Certificate\CertificateTestTemplateDeleteAction;
use ILIAS\Test\Certificate\CertificateSettingsTestFormRepository;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateGUIFactory
{
    private readonly Container $dic;

    public function __construct(?Container $dic = null)
    {
        if (null === $dic) {
            global $DIC;
            $dic = $DIC;
        }
        $this->dic = $dic;
    }

    /**
     * @throws ilException
     */
    public function create(ilObject $object): ilCertificateGUI
    {
        global $DIC;

        $type = $object->getType();
        $objectId = $object->getId();

        $logger = $DIC->logger()->cert();

        $templateRepository = new ilCertificateTemplateDatabaseRepository($this->dic->database(), $logger);
        $deleteAction = new ilCertificateTemplateDeleteAction($templateRepository);
        $pathFactory = new ilCertificatePathFactory();

        $certificatePath = $pathFactory->create($object);

        switch ($type) {
            case 'tst':
                $placeholderDescriptionObject = new TestPlaceholderDescription();
                $placeholderValuesObject = new TestPlaceholderValues();

                $formFactory = new CertificateSettingsTestFormRepository(
                    $objectId,
                    $certificatePath,
                    false,
                    $DIC->language(),
                    $DIC->ctrl(),
                    $DIC->access(),
                    $DIC->toolbar(),
                    $placeholderDescriptionObject
                );

                $deleteAction = new CertificateTestTemplateDeleteAction(
                    $deleteAction
                );

                break;
            case 'crs':
                $placeholderDescriptionObject = new CoursePlaceholderDescription($objectId);
                $placeholderValuesObject = new CoursePlaceholderValues();

                $formFactory = new CertificateSettingsCourseFormRepository(
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
                $placeholderDescriptionObject = new ExercisePlaceholderDescription();
                $placeholderValuesObject = new ExercisePlaceholderValues();

                $formFactory = new CertificateSettingsExerciseRepository(
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
                $placeholderDescriptionObject = new ilStudyProgrammePlaceholderDescription();
                $placeholderValuesObject = new ilStudyProgrammePlaceholderValues();
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
        }

        return new ilCertificateGUI(
            $placeholderDescriptionObject,
            $placeholderValuesObject,
            $objectId,
            $certificatePath,
            $formFactory,
            $deleteAction
        );
    }
}
