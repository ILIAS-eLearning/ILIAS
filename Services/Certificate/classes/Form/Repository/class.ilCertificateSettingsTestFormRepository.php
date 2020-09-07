<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateSettingsTestFormRepository implements ilCertificateFormRepository
{
    /**
     * @var ilCertificateSettingsFormRepository
     */
    private $settingsFromFactory;

    /**
     * @var ilLanguage
     */
    private $language;

    /**
     * @var ilObjTest
     */
    private $testObject;

    /**
     * @param int $objectId
     * @param string $certificatePath
     * @param ilObjTest $testObject
     * @param ilLanguage $language
     * @param ilTemplate $template
     * @param ilCtrl $controller
     * @param ilAccess $access
     * @param ilToolbarGUI $toolbar
     * @param ilCertificatePlaceholderDescription $placeholderDescriptionObject
     * @param ilCertificateSettingsFormRepository|null $settingsFormRepository
     */
    public function __construct(
        int $objectId,
        string $certificatePath,
        ilObjTest $testObject,
        ilLanguage $language,
        ilTemplate $template,
        ilCtrl $controller,
        ilAccess $access,
        ilToolbarGUI $toolbar,
        ilCertificatePlaceholderDescription $placeholderDescriptionObject,
        ilCertificateSettingsFormRepository $settingsFormRepository = null
    ) {
        $this->testObject = $testObject;
        $this->language = $language;

        if (null === $settingsFormRepository) {
            $settingsFormRepository = new ilCertificateSettingsFormRepository(
                $objectId,
                $certificatePath,
                $language,
                $template,
                $controller,
                $access,
                $toolbar,
                $placeholderDescriptionObject
            );
        }
        $this->settingsFromFactory = $settingsFormRepository;
    }

    /**
     * @param ilCertificateGUI $certificateGUI
     * @param ilCertificate $certificateObject
     * @return ilPropertyFormGUI
     * @throws ilException
     * @throws ilWACException
     */
    public function createForm(ilCertificateGUI $certificateGUI, ilCertificate $certificateObject)
    {
        $form = $this->settingsFromFactory->createForm($certificateGUI, $certificateObject);

        return $form;
    }

    /**
     * @param array $formFields
     */
    public function save(array $formFields)
    {
    }

    /**
     * @param string $content
     * @return array|mixed
     */
    public function fetchFormFieldData(string $content)
    {
        $formFields = $this->settingsFromFactory->fetchFormFieldData($content);

        return $formFields;
    }
}
