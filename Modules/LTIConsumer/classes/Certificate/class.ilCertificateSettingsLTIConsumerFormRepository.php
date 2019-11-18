<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilCertificateSettingsLTIConsumerFormRepository
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
class ilCertificateSettingsLTIConsumerFormRepository implements ilCertificateFormRepository
{
    /**
     * @var ilCertificateSettingsFormRepository
     */
    private $settingsFormRepository;

    /**
     * @var ilLanguage
     */
    private $language;

    /**
     * @var ilObjLTIConsumer
     */
    private $object;

    public function __construct(
        ilObjLTIConsumer $object,
        string $certificatePath,
        bool $hasAdditionalElements,
        ilLanguage $language,
        ilCtrl $controller,
        ilAccess $access,
        ilToolbarGUI $toolbar,
        ilCertificatePlaceholderDescription $placeholderDescriptionObject,
        ilCertificateSettingsFormRepository $settingsFormRepository = null
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
        $this->settingsFormRepository = $settingsFormRepository;
    }

    public function createForm(ilCertificateGUI $certificateGUI)
    {
        $form = $this->settingsFormRepository->createForm($certificateGUI);

        return $form;
    }


    public function save(array $formFields)
    {
        return;
    }

    public function fetchFormFieldData(string $content)
    {
        $formFields = $this->settingsFormRepository->fetchFormFieldData($content);

        return $formFields;
    }
}
