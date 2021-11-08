<?php declare(strict_types=1);

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Filesystem\Exception\FileNotFoundException;
use ILIAS\Filesystem\Exception\FileAlreadyExistsException;
use ILIAS\Filesystem\Exception\IOException;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateSettingsScormFormRepository implements ilCertificateFormRepository
{
    private ilObject $object;
    private ilLanguage $language;
    private ilCertificateSettingsFormRepository $settingsFromFactory;
    private ilSetting $setting;

    public function __construct(
        ilObject $object,
        string $certificatePath,
        bool $hasAdditionalElements,
        ilLanguage $language,
        ilCtrl $ctrl,
        ilAccess $access,
        ilToolbarGUI $toolbar,
        ilCertificatePlaceholderDescription $placeholderDescriptionObject,
        ?ilCertificateSettingsFormRepository $settingsFormRepository = null,
        ?ilSetting $setting = null
    ) {
        $this->object = $object;

        $this->language = $language;

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

        $this->settingsFromFactory = $settingsFormRepository;

        if (null === $setting) {
            $setting = new ilSetting('scorm');
        }
        $this->setting = $setting;
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

        $short_name = new ilTextInputGUI($this->language->txt('certificate_short_name'), 'short_name');
        $short_name->setRequired(true);
        $short_name->setValue(ilStr::subStr($this->object->getTitle(), 0, 30));
        $short_name->setSize(30);

        $infoText = $this->language->txt('certificate_short_name_description');
        $short_name->setInfo($infoText);

        $form->addItem($short_name);

        return $form;
    }

    public function save(array $formFields) : void
    {
        $this->setting->set('certificate_' . $this->object->getId(), (string) $formFields['certificate_enabled_scorm']);
        $this->setting->set('certificate_short_name_' . $this->object->getId(), (string) $formFields['short_name']);
    }

    public function fetchFormFieldData(string $content) : array
    {
        $formFields = $this->settingsFromFactory->fetchFormFieldData($content);
        $formFields['certificate_enabled_scorm'] = $this->setting->get(
            'certificate_' . $this->object->getId(),
            (string) $formFields['certificate_enabled_scorm']
        );
        $formFields['short_name'] = $this->setting->get(
            'certificate_short_name_' . $this->object->getId(),
            (string) $formFields['short_name']
        );

        return $formFields;
    }
}
