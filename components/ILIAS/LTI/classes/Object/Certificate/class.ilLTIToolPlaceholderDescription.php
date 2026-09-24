<?php

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

declare(strict_types=1);

/**
 * The placeholders a certificate of an LTI object offers: the default ones, the object, the scores and
 * the completion. ilCertificateGUIFactory creates it by this name.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTIToolPlaceholderDescription implements ilCertificatePlaceholderDescription
{
    private readonly ilLanguage $language;
    private readonly array $placeholders;

    public function __construct()
    {
        global $DIC;

        $this->language = $DIC->language();
        $this->language->loadLanguageModule('certificate');

        $default = new ilDefaultPlaceholderDescription($this->language, $DIC['user']->getProfile());
        $own = [
            'OBJECT_TITLE' => 'lti_cert_ph_object_title',
            'OBJECT_DESCRIPTION' => 'lti_cert_ph_object_description',
            'MASTERY_SCORE' => 'lti_cert_ph_mastery_score',
            'REACHED_SCORE' => 'lti_cert_ph_reached_score',
            'DATE_COMPLETED' => 'certificate_ph_date_completed',
            'DATETIME_COMPLETED' => 'certificate_ph_datetime_completed',
        ];

        $this->placeholders = $default->getPlaceholderDescriptions() + array_map(
            fn(string $txt): string => ilLegacyFormElementsUtil::prepareFormOutput($this->language->txt($txt)),
            $own
        );
    }

    /**
     * @return array
     */
    public function getPlaceholderDescriptions(): array
    {
        return $this->placeholders;
    }

    public function createPlaceholderHtmlDescription(?ilTemplate $template = null): string
    {
        $template ??= new ilTemplate('tpl.default_description.html', true, true, 'components/ILIAS/Certificate');
        $template->setVariable('PLACEHOLDER_INTRODUCTION', $this->language->txt('certificate_ph_introduction'));

        $template->setCurrentBlock('items');
        foreach ($this->placeholders as $id => $caption) {
            $template->setVariable('ID', $id);
            $template->setVariable('TXT', $caption);
            $template->parseCurrentBlock();
        }

        return $template->get();
    }
}
