<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilLTIConsumerPlaceholderDescription
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 */
class ilLTIConsumerPlaceholderDescription implements ilCertificatePlaceholderDescription
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
        $this->placeholder['OBJECT_TITLE'] = ilUtil::prepareFormOutput($this->language->txt('lti_cert_ph_object_title'));
        $this->placeholder['OBJECT_DESCRIPTION'] = ilUtil::prepareFormOutput($this->language->txt('lti_cert_ph_object_description'));
        $this->placeholder['MASTERY_SCORE'] = ilUtil::prepareFormOutput($this->language->txt('lti_cert_ph_mastery_score'));
        $this->placeholder['REACHED_SCORE'] = ilUtil::prepareFormOutput($this->language->txt('lti_cert_ph_reached_score'));
        $this->placeholder['DATE_COMPLETED'] = ilUtil::prepareFormOutput($language->txt('certificate_ph_date_completed'));
        $this->placeholder['DATETIME_COMPLETED'] = ilUtil::prepareFormOutput($language->txt('certificate_ph_datetime_completed'));
    }

    public function getPlaceholderDescriptions() : array
    {
        return $this->placeholder;
    }

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
}
