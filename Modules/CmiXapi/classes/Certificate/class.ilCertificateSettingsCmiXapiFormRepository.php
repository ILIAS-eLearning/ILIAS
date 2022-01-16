<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCertificateSettingsCmiXapiFormRepository
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 */
class ilCertificateSettingsCmiXapiFormRepository implements ilCertificateFormRepository
{
    /**
     * @var ilCertificateSettingsFormRepository
     */
    private $settingsFormRepository;

    public function __construct(
        ilObjCmiXapi $object,
        string $certificatePath,
        bool $hasAdditionalElements,
        ilLanguage $language,
        ilCtrl $controller,
        ilAccess $access,
        ilToolbarGUI $toolbar,
        ilCertificatePlaceholderDescription $placeholderDescriptionObject,
        ilCertificateSettingsFormRepository $settingsFormRepository = null
    ) {

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
        $this->settingsFormRepository = $settingsFormRepository;
    }

    public function createForm(ilCertificateGUI $certificateGUI) : ilPropertyFormGUI
    {
        $form = $this->settingsFormRepository->createForm($certificateGUI);

        return $form;
    }


    public function save(array $formFields) : void
    {
    }

    public function fetchFormFieldData(string $content) : array
    {
        $formFields = $this->settingsFormRepository->fetchFormFieldData($content);

        return $formFields;
    }
}
