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
class ilUserDefinedFieldsPlaceholderDescription implements ilCertificatePlaceholderDescription
{
    private array $placeholder;

    public function __construct(?ilUserDefinedFields $userDefinedFieldsObject = null)
    {
        $this->placeholder = [];

        if (null === $userDefinedFieldsObject) {
            $userDefinedFieldsObject = ilUserDefinedFields::_getInstance();
        }
        $userDefinedFields = $userDefinedFieldsObject->getDefinitions();

        foreach ($userDefinedFields as $field) {
            if ($field['certificate']) {
                $placeholderText = '#' . str_replace(' ', '_', ilStr::strToUpper($field['field_name']));

                $this->placeholder[$placeholderText] = $field['field_name'];
            }
        }
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
            'Services/Certificate'
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
