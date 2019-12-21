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
     * @param int                                      $objectId
     * @param string                                   $certificatePath
     * @param bool                                     $hasAdditionalElements
     * @param ilObjTest                                $testObject
     * @param ilLanguage                               $language
     * @param ilCtrl                                   $controller
     * @param ilAccess                                 $access
     * @param ilToolbarGUI                             $toolbar
     * @param ilCertificatePlaceholderDescription      $placeholderDescriptionObject
     * @param ilCertificateSettingsFormRepository|null $settingsFormRepository
     */
    public function __construct(
        int $objectId,
        string $certificatePath,
        bool $hasAdditionalElements,
        ilObjTest $testObject,
        ilLanguage $language,
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
                $hasAdditionalElements,
                $language,
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
    public function createForm(ilCertificateGUI $certificateGUI)
    {
        $form = $this->settingsFromFactory->createForm($certificateGUI);

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
