<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
    private \ilDefaultPlaceholderDescription $defaultPlaceHolderDescriptionObject;

    private ?\ilLanguage $language;

    private array $placeholder;

    public function __construct(
        ?ilDefaultPlaceholderDescription $defaultPlaceholderDescriptionObject = null,
        ?ilLanguage $language = null,
        ?ilUserDefinedFieldsPlaceholderDescription $userDefinedFieldPlaceHolderDescriptionObject = null
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
        $this->placeholder['OBJECT_TITLE'] = ilLegacyFormElementsUtil::prepareFormOutput(
            $this->language->txt('lti_cert_ph_object_title')
        );
        $this->placeholder['OBJECT_DESCRIPTION'] = ilLegacyFormElementsUtil::prepareFormOutput(
            $this->language->txt('lti_cert_ph_object_description')
        );
        $this->placeholder['MASTERY_SCORE'] = ilLegacyFormElementsUtil::prepareFormOutput(
            $this->language->txt('lti_cert_ph_mastery_score')
        );
        $this->placeholder['REACHED_SCORE'] = ilLegacyFormElementsUtil::prepareFormOutput(
            $this->language->txt('lti_cert_ph_reached_score')
        );
        $this->placeholder['DATE_COMPLETED'] = ilLegacyFormElementsUtil::prepareFormOutput(
            $language->txt('certificate_ph_date_completed')
        );
        $this->placeholder['DATETIME_COMPLETED'] = ilLegacyFormElementsUtil::prepareFormOutput(
            $language->txt('certificate_ph_datetime_completed')
        );
    }

    /**
     * @return mixed[]
     */
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
