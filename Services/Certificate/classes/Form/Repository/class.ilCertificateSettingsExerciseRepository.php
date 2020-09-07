<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCertificateSettingsExerciseRepository implements ilCertificateFormRepository
{
    /**
     * @var ilLanguage
     */
    private $language;

    /**
     * @var ilCertificateSettingsFormRepository
     */
    private $settingsFromFactory;

    /**
     * @var ilObject
     */
    private $object;

    /**
     * @param ilObject $object
     * @param string $certificatePath
     * @param ilLanguage $language
     * @param ilTemplate $template
     * @param ilCtrl $controller
     * @param ilAccess $access
     * @param ilToolbarGUI $toolbar
     * @param ilCertificatePlaceholderDescription $placeholderDescriptionObject
     * @param ilCertificateSettingsFormRepository|null $settingsFormFactory
     */
    public function __construct(
        ilObject $object,
        string $certificatePath,
        ilLanguage $language,
        ilTemplate $template,
        ilCtrl $controller,
        ilAccess $access,
        ilToolbarGUI $toolbar,
        ilCertificatePlaceholderDescription $placeholderDescriptionObject,
        ilCertificateSettingsFormRepository $settingsFormFactory = null
    ) {
        $this->object = $object;
        $this->language = $language;

        if (null === $settingsFormFactory) {
            $settingsFormFactory = new ilCertificateSettingsFormRepository(
                $object->getId(),
                $certificatePath,
                $language,
                $template,
                $controller,
                $access,
                $toolbar,
                $placeholderDescriptionObject
            );
        }

        $this->settingsFromFactory = $settingsFormFactory;
    }

    /**
     * @param ilCertificateGUI $certificateGUI
     * @param ilCertificate $certificateObject
     *
     * @return ilPropertyFormGUI
     *
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
     * @param $content
     * @return array|mixed
     */
    public function fetchFormFieldData(string $content)
    {
        $formFields = $this->settingsFromFactory->fetchFormFieldData($content);

        return $formFields;
    }
}
