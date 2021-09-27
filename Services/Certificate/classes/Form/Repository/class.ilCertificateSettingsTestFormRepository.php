<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Filesystem\Exception\IOException;
use ILIAS\Filesystem\Exception\FileNotFoundException;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateSettingsTestFormRepository implements ilCertificateFormRepository
{
    private ilCertificateSettingsFormRepository $settingsFromFactory;
    private ilLanguage $language;
    private ilObjTest $testObject;

    public function __construct(
        int $objectId,
        string $certificatePath,
        bool $hasAdditionalElements,
        ilObjTest $testObject,
        ilLanguage $language,
        ilCtrl $ctrl,
        ilAccess $access,
        ilToolbarGUI $toolbar,
        ilCertificatePlaceholderDescription $placeholderDescriptionObject,
        ?ilCertificateSettingsFormRepository $settingsFormRepository = null
    ) {
        $this->testObject = $testObject;
        $this->language = $language;

        if (null === $settingsFormRepository) {
            $settingsFormRepository = new ilCertificateSettingsFormRepository(
                $objectId,
                $certificatePath,
                $hasAdditionalElements,
                $language,
                $ctrl,
                $access,
                $toolbar,
                $placeholderDescriptionObject
            );
        }
        $this->settingsFromFactory = $settingsFormRepository;
    }

    /**
     * @param ilCertificateGUI $certificateGUI
     * @return ilPropertyFormGUI
     * @throws FileAlreadyExistsException
     * @throws FileNotFoundException
     * @throws IOException
     * @throws ilDatabaseException
     * @throws ilException
     * @throws ilWACException
     */
    public function createForm(ilCertificateGUI $certificateGUI) : ilPropertyFormGUI
    {
        $form = $this->settingsFromFactory->createForm($certificateGUI);

        return $form;
    }

    public function save(array $formFields) : void
    {
    }

    public function fetchFormFieldData(string $content) : array
    {
        $formFields = $this->settingsFromFactory->fetchFormFieldData($content);

        return $formFields;
    }
}
