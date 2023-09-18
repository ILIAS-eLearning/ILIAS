<?php

declare(strict_types=1);

class ilADTGroupDBBridge extends ilADTDBBridge
{
    protected array $elements = [];

    protected function isValidADT(ilADT $a_adt): bool
    {
        return ($a_adt instanceof ilADTGroup);
    }

    // elements

    protected function prepareElements(): void
    {
        if (count($this->elements)) {
            return;
        }

        $this->elements = array();
        $factory = ilADTFactory::getInstance();

        // convert ADTs to DB bridges

        foreach ($this->getADT()->getElements() as $name => $element) {
            $this->elements[$name] = $factory->getDBBridgeForInstance($element);
            $this->elements[$name]->setElementId((string) $name);
            $this->elements[$name]->setTable($this->getTable());
            $this->elements[$name]->setPrimary($this->getPrimary());
        }
    }

    /**
     * @return ilADTDBBridge[]
     */
    public function getElements(): array
    {
        $this->prepareElements();
        return $this->elements;
    }

    public function getElement(string $a_element_id): ?ilADTDBBridge
    {
        if (array_key_exists($a_element_id, $this->getElements())) {
            return $this->elements[$a_element_id];
        }
        return null;
    }

    // properties
    public function setTable(string $a_table): void
    {
        parent::setTable($a_table);
        if (count($this->elements)) {
            foreach (array_keys($this->getADT()->getElements()) as $name) {
                $this->elements[$name]->setTable($this->getTable());
            }
        }
    }

    public function setPrimary(array $a_value): void
    {
        parent::setPrimary($a_value);

        if (count($this->elements)) {
            foreach (array_keys($this->getADT()->getElements()) as $name) {
                $this->elements[$name]->setPrimary($this->getPrimary());
            }
        }
    }

    // CRUD

    public function readRecord(array $a_row): void
    {
        foreach ($this->getElements() as $element) {
            $element->readRecord($a_row);
        }
    }

    public function prepareInsert(array &$a_fields): void
    {
        foreach ($this->getElements() as $element) {
            $element->prepareInsert($a_fields);
        }
    }

    public function afterInsert(): void
    {
        foreach ($this->getElements() as $element) {
            $element->afterInsert();
        }
    }

    public function afterUpdate(): void
    {
        foreach ($this->getElements() as $element) {
            $element->afterUpdate();
        }
    }

    /**
     * @param string $field_type
     * @param string $field_name
     * @param int    $field_id
     */
    public function afterUpdateElement(string $field_type, string $field_name, int $field_id)
    {
        $element = $this->getElement((string) $field_id);
        if (!$element) {
            return;
        }
        $element->setPrimary(
            array_merge(
                $this->getPrimary(),
                [
                    $field_name => [$field_type, $field_id]
                ]
            )
        );
        $element->setElementId((string) $field_id);
        $element->afterUpdate();
    }

    public function afterDelete(): void
    {
        foreach ($this->getElements() as $element) {
            $element->afterDelete();
        }
    }
}
