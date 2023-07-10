<?php

declare(strict_types=1);

class ilADTGroupActiveRecordBridge extends ilADTActiveRecordBridge
{
    /**
     * @var ilADTActiveRecordBridge[]
     */
    protected array $elements = [];

    protected function isValidADT(ilADT $a_adt): bool
    {
        return ($a_adt instanceof ilADTGroup);
    }

    /**
     * @inheritDoc
     */
    public function getFieldValue(string $a_field_name)
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function setFieldValue(string $a_field_name, $a_field_value): void
    {
    }

    // elements

    protected function prepareElements(): void
    {
        if (count($this->elements)) {
            return;
        }

        $this->elements = array();
        $factory = ilADTFactory::getInstance();

        // convert ADTs to ActiveRecord bridges

        foreach ($this->getADT()->getElements() as $name => $element) {
            $this->elements[$name] = $factory->getActiveRecordBridgeForInstance($element);
            $this->elements[$name]->setElementId($name);
        }
    }

    public function getElements(): array
    {
        $this->prepareElements();
        return $this->elements;
    }

    public function getElement(string $a_element_id): ?ilADTActiveRecordBridge
    {
        if (array_key_exists($a_element_id, $this->getElements())) {
            return $this->elements[$a_element_id];
        }
        return null;
    }

    public function getActiveRecordFields(): array
    {
        $fields = array();
        foreach ($this->getElements() as $element_id => $element) {
            $element_fields = $element->getActiveRecordFields();
            if ($element_fields) {
                $fields[$element_id] = $element_fields;
            }
        }
        return $fields;
    }
}
