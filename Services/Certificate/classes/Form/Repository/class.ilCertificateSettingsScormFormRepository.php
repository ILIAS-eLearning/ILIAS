<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateSettingsScormFormRepository implements ilCertificateFormRepository
{
    /**
     * @var ilObject
     */
    private $object;

    /**
     * @var ilLanguage
     */
    private $language;

    /**
     * @var ilCertificateSettingsFormRepository
     */
    private $settingsFromFactory;

    /**
     * @var ilSetting
     */
    private $setting;

    /**
     * @param ilObject $object
     * @param string $certificatePath
     * @param ilLanguage $language
     * @param ilCtrl $controller
     * @param ilAccess $access
     * @param ilToolbarGUI $toolbar
     * @param ilCertificatePlaceholderDescription $placeholderDescriptionObject
     * @param ilCertificateSettingsFormRepository|null $settingsFormRepository
     * @param ilSetting|null $setting
     */
    public function __construct(
        ilObject $object,
        string $certificatePath,
        bool $hasAdditionalElements,
        ilLanguage $language,
        ilCtrl $controller,
        ilAccess $access,
        ilToolbarGUI $toolbar,
        ilCertificatePlaceholderDescription $placeholderDescriptionObject,
        ilCertificateSettingsFormRepository $settingsFormRepository = null,
        ilSetting $setting = null
    ) {
        $this->object = $object;

        $this->language = $language;

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

        $this->settingsFromFactory = $settingsFormRepository;

        if (null === $setting) {
            $setting = new ilSetting('scorm');
        }
        $this->setting = $setting;
    }

    /**
     * @param ilCertificateGUI $certificateGUI
     * @param ilCertificate    $certificateObject
     * @param string           $certificatePath
     * @return ilPropertyFormGUI
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

        $short_name = new ilTextInputGUI($this->language->txt('certificate_short_name'), 'short_name');
        $short_name->setRequired(true);
        $short_name->setValue(ilStr::subStr($this->object->getTitle(), 0, 30));
        $short_name->setSize(30);

        $infoText = $this->language->txt('certificate_short_name_description');
        $short_name->setInfo($infoText);

        $form->addItem($short_name);

        return $form;
    }

    /**
     * @param array $formFields
     */
    public function save(array $formFields)
    {
        $this->setting->set('certificate_' . $this->object->getId(), $formFields['certificate_enabled_scorm']);
        $this->setting->set('certificate_short_name_' . $this->object->getId(), $formFields['short_name']);
    }

    /**
     * @param string $content
     * @return array|mixed
     */
    public function fetchFormFieldData(string $content)
    {
        $formFields = $this->settingsFromFactory->fetchFormFieldData($content);
        $formFields['certificate_enabled_scorm'] = $this->setting->get('certificate_' . $this->object->getId(), $formFields['certificate_enabled_scorm']);
        $formFields['short_name'] = $this->setting->get('certificate_short_name_' . $this->object->getId(), $formFields['short_name']);

        return $formFields;
    }
}
