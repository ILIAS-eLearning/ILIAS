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

/**
 * The certificate settings of an LTI object, which are the general ones: an LTI object adds none.
 * ilCertificateGUIFactory creates it by this name.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilCertificateSettingsLTIConsumerFormRepository implements ilCertificateFormRepository
{
    private readonly ilCertificateSettingsFormRepository $settings_form_repository;

    public function __construct(
        ilObject $object,
        string $certificatePath,
        bool $hasAdditionalElements,
        ilLanguage $language,
        ilCtrlInterface $controller,
        ilAccess $access,
        ilToolbarGUI $toolbar,
        ilCertificatePlaceholderDescription $placeholderDescriptionObject
    ) {
        $this->settings_form_repository = new ilCertificateSettingsFormRepository(
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

    public function createForm(ilCertificateGUI $certificateGUI): ilPropertyFormGUI
    {
        return $this->settings_form_repository->createForm($certificateGUI);
    }

    public function save(array $formFields): void
    {
    }

    /**
     * @return array
     */
    public function fetchFormFieldData(string $content): array
    {
        return $this->settings_form_repository->fetchFormFieldData($content);
    }
}
