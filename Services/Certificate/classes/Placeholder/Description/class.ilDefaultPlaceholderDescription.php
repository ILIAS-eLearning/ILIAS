<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Collection of basic placeholder values that can be used
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilDefaultPlaceholderDescription implements ilCertificatePlaceholderDescription
{
    /**
     * @var array
     */
    private $placeholder;

    /**
     * @var ilLanguage
     */
    private $language;

    /**
     * @param ilLanguage $language
     * @param ilUserDefinedFieldsPlaceholderDescription|null $userDefinedFieldPlaceHolderDescriptionObject
     */
    public function __construct(ilLanguage $language, ilUserDefinedFieldsPlaceholderDescription $userDefinedFieldPlaceHolderDescriptionObject = null)
    {
        if (null === $userDefinedFieldPlaceHolderDescriptionObject) {
            $userDefinedFieldPlaceHolderDescriptionObject = new ilUserDefinedFieldsPlaceholderDescription();
        }
        $userDefinedPlaceholderHtmlDescription = $userDefinedFieldPlaceHolderDescriptionObject->getPlaceholderDescriptions();

        $this->language = $language;

        $this->placeholder = array(
            'USER_LOGIN' => $language->txt('certificate_ph_login'),
            'USER_FULLNAME' => $language->txt('certificate_ph_fullname'),
            'USER_FIRSTNAME' => $language->txt('certificate_ph_firstname'),
            'USER_LASTNAME' => $language->txt('certificate_ph_lastname'),
            'USER_TITLE' => $language->txt('certificate_ph_title'),
            'USER_SALUTATION' => $language->txt('certificate_ph_salutation'),
            'USER_BIRTHDAY' => $language->txt('certificate_ph_birthday'),
            'USER_INSTITUTION' => $language->txt('certificate_ph_institution'),
            'USER_DEPARTMENT' => $language->txt('certificate_ph_department'),
            'USER_STREET' => $language->txt('certificate_ph_street'),
            'USER_CITY' => $language->txt('certificate_ph_city'),
            'USER_ZIPCODE' => $language->txt('certificate_ph_zipcode'),
            'USER_COUNTRY' => $language->txt('certificate_ph_country'),
            'USER_MATRICULATION' => $language->txt('certificate_ph_matriculation'),
            'DATE' => $language->txt("certificate_ph_date"),
            'DATETIME' => $language->txt("certificate_ph_datetime"),
        );

        $this->placeholder = array_merge($this->placeholder, $userDefinedPlaceholderHtmlDescription);
    }


    /**
     * This method is different then the 'getPlaceholderValues' method, this
     * method is used to create a placeholder value array containing dummy values
     * that is used to create a preview certificate.
     *
     * @param null $template
     * @return array|mixed
     */
    public function createPlaceholderHtmlDescription(ilTemplate $template = null) : string
    {
        if (null === $template) {
            $template = new ilTemplate('tpl.default_description.html', true, true, 'Services/Certificate');
        }

        $template->setVariable('PLACEHOLDER_INTRODUCTION', $this->language->txt('certificate_ph_introduction'));

        $template->setCurrentBlock('items');
        foreach ($this->placeholder as $id => $caption) {
            $template->setVariable('ID', $id);
            $template->setVariable('TXT', $caption);
            $template->parseCurrentBlock();
        }

        return $template->get();
    }

    /**
     * This method MUST return an array containing an array with
     * the the description as array value.
     *
     * @return mixed - [PLACEHOLDER] => 'description'
     */
    public function getPlaceholderDescriptions() : array
    {
        return $this->placeholder;
    }
}
