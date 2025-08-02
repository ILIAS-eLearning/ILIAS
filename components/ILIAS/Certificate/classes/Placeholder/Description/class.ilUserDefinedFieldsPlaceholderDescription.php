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

use ILIAS\Language\Language;
use ILIAS\User\Context;
use ILIAS\User\Profile\Profile;
use ILIAS\User\Profile\Fields\Field;

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilUserDefinedFieldsPlaceholderDescription implements ilCertificatePlaceholderDescription
{
    private array $placeholder;

    public function __construct(
        Language $lng,
        Profile $user_profile
    ) {
        $this->placeholder = array_reduce(
            $user_profile->getVisibleUserDefinedFields(Context::Certificate),
            static function (array $c, Field $v) use ($lng): array {
                $c["#{$v->getLabel($lng)}"] = $v->getLabel($lng);
                return $c;
            },
            []
        );
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

    /**
     * @return string - HTML that can used to be displayed in the GUI
     */
    public function createPlaceholderHtmlDescription(): string
    {
        $template = new ilTemplate(
            'tpl.common_desc.html',
            true,
            true,
            'components/ILIAS/Certificate'
        );

        foreach ($this->getPlaceholderDescriptions() as $key => $field) {
            $template->setCurrentBlock('cert_field');
            $template->setVariable('PH', $key);
            $template->setVariable('PH_TXT', $field);
            $template->parseCurrentBlock();
        }

        return $template->get();
    }
}
