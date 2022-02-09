<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilCoursePlaceholderDescription implements ilCertificatePlaceholderDescription
{
    private ilDefaultPlaceholderDescription $defaultPlaceHolderDescriptionObject;
    private ilObjectCustomUserFieldsPlaceholderDescription $customUserFieldsPlaceholderDescriptionObject;
    private ilLanguage $language;
    private array $placeholder;

    public function __construct(
        int $objectId,
        ?ilDefaultPlaceholderDescription $defaultPlaceholderDescriptionObject = null,
        ?ilLanguage $language = null,
        ?ilUserDefinedFieldsPlaceholderDescription $userDefinedFieldPlaceHolderDescriptionObject = null,
        ?ilObjectCustomUserFieldsPlaceholderDescription $customUserFieldsPlaceholderDescriptionObject = null
    ) {
        global $DIC;

        if (null === $language) {
            $language = $DIC->language();
            $language->loadLanguageModule('certificate');
        }
        $this->language = $language;

        if (null === $defaultPlaceholderDescriptionObject) {
            $defaultPlaceholderDescriptionObject = new ilDefaultPlaceholderDescription(
                $language,
                $userDefinedFieldPlaceHolderDescriptionObject
            );
        }
        $this->defaultPlaceHolderDescriptionObject = $defaultPlaceholderDescriptionObject;

        if (null === $customUserFieldsPlaceholderDescriptionObject) {
            $customUserFieldsPlaceholderDescriptionObject = new ilObjectCustomUserFieldsPlaceholderDescription($objectId);
        }
        $this->customUserFieldsPlaceholderDescriptionObject = $customUserFieldsPlaceholderDescriptionObject;

        $customUserFieldsPlaceholderHtmlDescription = $this->customUserFieldsPlaceholderDescriptionObject->getPlaceholderDescriptions();
        $defaultPlaceholderDescription = $this->defaultPlaceHolderDescriptionObject->getPlaceholderDescriptions();

        $this->placeholder = array_merge($defaultPlaceholderDescription, $customUserFieldsPlaceholderHtmlDescription);
        $this->placeholder['COURSE_TITLE'] = $this->language->txt('crs_title');
        $this->placeholder['DATE_COMPLETED'] = ilLegacyFormElementsUtil::prepareFormOutput(
            $language->txt('certificate_ph_date_completed')
        );
        $this->placeholder['DATETIME_COMPLETED'] = ilLegacyFormElementsUtil::prepareFormOutput(
            $language->txt('certificate_ph_datetime_completed')
        );
    }

    public function createPlaceholderHtmlDescription(?ilTemplate $template = null) : string
    {
        if (null === $template) {
            $template = new ilTemplate('tpl.default_description.html', true, true, 'Services/Certificate');
        }

        $template->setVariable("PLACEHOLDER_INTRODUCTION", $this->language->txt('certificate_ph_introduction'));

        $template->setCurrentBlock("items");
        foreach ($this->placeholder as $id => $caption) {
            $template->setVariable("ID", $id);
            $template->setVariable("TXT", $caption);
            $template->parseCurrentBlock();
        }

        return $template->get();
    }

    /**
     * This method MUST return an array containing an array with
     * the the description as array value.
     * @return array - [PLACEHOLDER] => 'description'
     */
    public function getPlaceholderDescriptions() : array
    {
        return $this->placeholder;
    }
}
