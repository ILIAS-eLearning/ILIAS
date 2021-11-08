<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

class ilObjectCustomUserFieldsPlaceholderDescription implements ilCertificatePlaceholderDescription
{
    private array $placeholder;
    private int $objectId;

    public function __construct(int $objectId)
    {
        $this->placeholder = [];
        $this->objectId = $objectId;

        $this->initPlaceholders();
    }

    private function initPlaceholders() : void
    {
        $courseDefinedFields = ilCourseDefinedFieldDefinition::_getFields($this->objectId);

        foreach ($courseDefinedFields as $field) {
            $name = $field->getName();

            $placeholderText = '+' . str_replace(' ', '_', ilStr::strToUpper($name));

            $this->placeholder[$placeholderText] = $name;
        }
    }

    /**
     * This method MUST return an array containing an array with
     * the the description as array value.
     * @return array - [PLACEHOLDER] => 'description'
     */
    public function getPlaceholderDescriptions() : array
    {
        return $this->placeholder;
    }

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
