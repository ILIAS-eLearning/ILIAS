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

class ilStudyProgrammePlaceholderDescription implements ilCertificatePlaceholderDescription
{
    private ilDefaultPlaceholderDescription $defaultPlaceHolderDescriptionObject;
    private ilLanguage $language;
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
            $defaultPlaceholderDescriptionObject = new ilDefaultPlaceholderDescription(
                $language,
                $userDefinedFieldPlaceHolderDescriptionObject
            );
        }
        $this->defaultPlaceHolderDescriptionObject = $defaultPlaceholderDescriptionObject;

        $this->placeholder = $this->defaultPlaceHolderDescriptionObject->getPlaceholderDescriptions();
        $this->placeholder['PRG_TITLE'] = $this->language->txt('sp_certificate_title');
        $this->placeholder['PRG_DESCRIPTION'] = $this->language->txt('sp_certificate_description');
        $this->placeholder['PRG_TYPE'] = $this->language->txt('sp_certificate_type');
        $this->placeholder['PRG_POINTS'] = $this->language->txt('sp_certificate_points');
        $this->placeholder['PRG_COMPLETION_DATE'] = $this->language->txt('sp_certificate_completion_date');
        $this->placeholder['PRG_EXPIRES_AT'] = $this->language->txt('sp_certificate_progress_expires_at');
    }

    /**
     * This methods MUST return an array containing an array with
     * the the description as array value.
     */
    public function createPlaceholderHtmlDescription(?ilTemplate $template = null): string
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
    public function getPlaceholderDescriptions(): array
    {
        return $this->placeholder;
    }
}
