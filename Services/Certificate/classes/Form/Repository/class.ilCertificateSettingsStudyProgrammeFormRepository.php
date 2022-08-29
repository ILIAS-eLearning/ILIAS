<?php

declare(strict_types=1);

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

use ILIAS\Filesystem\Exception\IOException;
use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Filesystem\Exception\FileNotFoundException;

class ilCertificateSettingsStudyProgrammeFormRepository implements ilCertificateFormRepository
{
    private ilCertificateSettingsFormRepository $settingsFormRepository;

    public function __construct(
        ilObject $object,
        string $certificatePath,
        bool $hasAdditionalElements,
        ilLanguage $language,
        ilCtrlInterface $ctrl,
        ilAccess $access,
        ilToolbarGUI $toolbar,
        ilCertificatePlaceholderDescription $placeholderDescriptionObject,
        ?ilCertificateSettingsFormRepository $settingsFormRepository = null
    ) {
        if (null === $settingsFormRepository) {
            $settingsFormRepository = new ilCertificateSettingsFormRepository(
                $object->getId(),
                $certificatePath,
                $hasAdditionalElements,
                $language,
                $ctrl,
                $access,
                $toolbar,
                $placeholderDescriptionObject
            );
        }

        $this->settingsFormRepository = $settingsFormRepository;
    }

    /**
     * @throws FileAlreadyExistsException
     * @throws FileNotFoundException
     * @throws IOException
     * @throws ilDatabaseException
     * @throws ilException
     * @throws ilWACException
     */
    public function createForm(ilCertificateGUI $certificateGUI): ilPropertyFormGUI
    {
        return $this->settingsFormRepository->createForm($certificateGUI);
    }

    public function save(array $formFields): void
    {
    }

    /**
     * @return array{pageformat: string, pagewidth: mixed, pageheight: mixed, margin_body_top: mixed, margin_body_right: mixed, margin_body_bottom: mixed, margin_body_left: mixed, certificate_text: string}
     */
    public function fetchFormFieldData(string $content): array
    {
        return $this->settingsFormRepository->fetchFormFieldData($content);
    }
}
