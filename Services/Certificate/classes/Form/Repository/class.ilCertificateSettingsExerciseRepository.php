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

use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\IOException;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateSettingsExerciseRepository implements ilCertificateFormRepository
{
    private readonly ilCertificateSettingsFormRepository $settingsFormFactory;

    public function __construct(
        ilObject $object,
        string $certificatePath,
        bool $hasAdditionalElements,
        ilLanguage $language,
        ilCtrlInterface $ctrl,
        ilAccessHandler $access,
        ilToolbarGUI $toolbar,
        ilCertificatePlaceholderDescription $placeholderDescriptionObject,
        ?ilCertificateSettingsFormRepository $settingsFormFactory = null
    ) {
        global $DIC;

        $this->settingsFormFactory = $settingsFormFactory ?? new ilCertificateSettingsFormRepository(
            $object->getId(),
            $certificatePath,
            $hasAdditionalElements,
            $language,
            $ctrl,
            $access,
            $toolbar,
            $placeholderDescriptionObject,
            $DIC->ui()->factory(),
            $DIC->ui()->renderer()
        );
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
        return $this->settingsFormFactory->createForm($certificateGUI);
    }

    public function save(array $formFields): void
    {
    }

    public function fetchFormFieldData(string $content): array
    {
        return $this->settingsFormFactory->fetchFormFieldData($content);
    }
}
