<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilTestPlaceholderDescription implements ilCertificatePlaceholderDescription
{
    /**
     * @var ilDefaultPlaceholderDescription
     */
    private $defaultPlaceHolderDescriptionObject;

    /**
     * @var ilLanguage|null
     */
    private $language;

    /**
     * @var array
     */
    private $placeholder;

    /**
     * @param ilDefaultPlaceholderDescription|null $defaultPlaceholderDescriptionObject
     * @param ilLanguage|null $language
     * @param ilUserDefinedFieldsPlaceholderDescription|null $userDefinedFieldPlaceHolderDescriptionObject
     */
    public function __construct(
        ilDefaultPlaceholderDescription $defaultPlaceholderDescriptionObject = null,
        ilLanguage $language = null,
        ilUserDefinedFieldsPlaceholderDescription $userDefinedFieldPlaceHolderDescriptionObject = null
    ) {
        global $DIC;

        if (null === $language) {
            $language = $DIC->language();
            $language->loadLanguageModule('certificate');
        }
        $this->language = $language;

        if (null === $defaultPlaceholderDescriptionObject) {
            $defaultPlaceholderDescriptionObject = new ilDefaultPlaceholderDescription($language, $userDefinedFieldPlaceHolderDescriptionObject);
        }
        $this->defaultPlaceHolderDescriptionObject = $defaultPlaceholderDescriptionObject;

        $this->placeholder = $this->defaultPlaceHolderDescriptionObject->getPlaceholderDescriptions();

        $this->placeholder['RESULT_PASSED']      = ilUtil::prepareFormOutput($this->language->txt('certificate_var_result_passed'));
        $this->placeholder['RESULT_POINTS']      = ilUtil::prepareFormOutput($this->language->txt('certificate_var_result_points'));
        $this->placeholder['RESULT_PERCENT']     = ilUtil::prepareFormOutput($this->language->txt('certificate_var_result_percent'));
        $this->placeholder['MAX_POINTS']         = ilUtil::prepareFormOutput($this->language->txt('certificate_var_max_points'));
        $this->placeholder['RESULT_MARK_SHORT']  = ilUtil::prepareFormOutput($this->language->txt('certificate_var_result_mark_short'));
        $this->placeholder['RESULT_MARK_LONG']   = ilUtil::prepareFormOutput($this->language->txt('certificate_var_result_mark_long'));
        $this->placeholder['TEST_TITLE']         = ilUtil::prepareFormOutput($this->language->txt('certificate_ph_testtitle'));
        $this->placeholder['DATE_COMPLETED']     = ilUtil::prepareFormOutput($language->txt('certificate_ph_date_completed'));
        $this->placeholder['DATETIME_COMPLETED'] = ilUtil::prepareFormOutput($language->txt('certificate_ph_datetime_completed'));
    }


    /**
     * This methods MUST return an array containing an array with
     * the the description as array value.
     *
     * @param ilTemplate|null $template
     * @return mixed - [PLACEHOLDER] => 'description'
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
