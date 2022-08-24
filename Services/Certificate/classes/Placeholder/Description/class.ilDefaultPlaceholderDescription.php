<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Collection of basic placeholder values that can be used
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilDefaultPlaceholderDescription implements ilCertificatePlaceholderDescription
{
    private array $placeholder;
    private ilLanguage $language;

    public function __construct(
        ilLanguage $language,
        ?ilUserDefinedFieldsPlaceholderDescription $userDefinedFieldPlaceHolderDescriptionObject = null
    ) {
        if (null === $userDefinedFieldPlaceHolderDescriptionObject) {
            $userDefinedFieldPlaceHolderDescriptionObject = new ilUserDefinedFieldsPlaceholderDescription();
        }
        $userDefinedPlaceholderHtmlDescription = $userDefinedFieldPlaceHolderDescriptionObject->getPlaceholderDescriptions();

        $language->loadLanguageModule('certificate');
        $this->language = $language;

        $this->placeholder = [
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
        ];

        $this->placeholder = array_merge($this->placeholder, $userDefinedPlaceholderHtmlDescription);
    }

    /**
     * This method is different then the 'getPlaceholderValues' method, this
     * method is used to create a placeholder value array containing dummy values
     * that is used to create a preview certificate.
     * @param ilTemplate|null $template
     * @return string
     */
    public function createPlaceholderHtmlDescription(?ilTemplate $template = null): string
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
     * @return array - [PLACEHOLDER] => 'description'
     */
    public function getPlaceholderDescriptions(): array
    {
        return $this->placeholder;
    }
}
