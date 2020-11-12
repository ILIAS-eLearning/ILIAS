<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * @author  Niels Theen <ntheen@databay.de>
 */
class ilUserDefinedFieldsPlaceholderDescription implements ilCertificatePlaceholderDescription
{
    private $placeholder;

    /**
     * @param ilUserDefinedFields|null $userDefinedFieldsObject
     */
    public function __construct(ilUserDefinedFields $userDefinedFieldsObject = null)
    {
        $this->placeholder = array();

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
     *
     * @return array - [PLACEHOLDER] => 'description'
     */
    public function getPlaceholderDescriptions() : array
    {
        return $this->placeholder;
    }

    /**
     * @return string - HTML that can used to be displayed in the GUI
     */
    public function createPlaceholderHtmlDescription() : string
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
