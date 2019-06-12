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
        ilCtrl $controller,
        ilAccessHandler $access,
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
    public function createForm(ilCertificateGUI $certificateGUI, ilCertificate $certificateObject, string $certificatePath)
    {
        $form = $this->settingsFromFactory->createForm($certificateGUI, $certificateObject, $certificatePath);

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
