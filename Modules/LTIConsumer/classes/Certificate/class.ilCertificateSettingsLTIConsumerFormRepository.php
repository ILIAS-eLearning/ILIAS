<?php declare(strict_types=1);

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

/**
 * Class ilCertificateSettingsLTIConsumerFormRepository
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
class ilCertificateSettingsLTIConsumerFormRepository implements ilCertificateFormRepository
{
    private \ilCertificateSettingsFormRepository $settingsFormRepository;

//    private \ilLanguage $language;
//
//    private \ilObjLTIConsumer $object;

    public function __construct(
        ilObjLTIConsumer $object,
        string $certificatePath,
        bool $hasAdditionalElements,
        ilLanguage $language,
        ilCtrl $controller,
        ilAccess $access,
        ilToolbarGUI $toolbar,
        ilCertificatePlaceholderDescription $placeholderDescriptionObject,
        ilCertificateSettingsFormRepository $settingsFormRepository = null
    ) {
//        $this->object = $object;
//        $this->language = $language;

        if (null === $settingsFormRepository) {
            $settingsFormRepository = new ilCertificateSettingsFormRepository(
                $object->getId(),
                $certificatePath,
                $hasAdditionalElements,
                $language,
                $controller,
                $access,
                $toolbar,
                $placeholderDescriptionObject
            );
        }
        $this->settingsFormRepository = $settingsFormRepository;
    }

    public function createForm(ilCertificateGUI $certificateGUI) : ilPropertyFormGUI
    {
        return $this->settingsFormRepository->createForm($certificateGUI);
    }


    public function save(array $formFields) : void
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchFormFieldData(string $content) : array
    {
        return $this->settingsFormRepository->fetchFormFieldData($content);
    }
}
