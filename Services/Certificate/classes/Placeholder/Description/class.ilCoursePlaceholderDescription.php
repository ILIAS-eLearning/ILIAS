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
