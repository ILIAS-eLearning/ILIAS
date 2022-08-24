<?php

declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * external link search bridge
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesADT
 */
class ilADTInternalLinkSearchBridgeSingle extends ilADTSearchBridgeSingle
{
    /**
     * Is valid type
     * @param ilADT $a_adt
     * @return bool
     */
    protected function isValidADTDefinition(\ilADTDefinition $a_adt_def): bool
    {
        return $a_adt_def instanceof ilADTInternalLinkDefinition;
    }

    /*
     * Add search property to form
     */
    public function addToForm(): void
    {
        $title = new ilTextInputGUI($this->getTitle(), $this->getElementId());
        $title->setSize(255);
        $this->addToParentElement($title);
    }

    /**
     * Load from filter
     */
    public function loadFilter(): void
    {
        $value = $this->readFilter();
        if ($value !== null) {
            $this->getADT()->setTargetRefId($value);
        }
    }

    public function importFromPost(array $a_post = null): bool
    {
        $post = $this->extractPostValues($a_post);

        if ($post && $this->shouldBeImportedFromPost($post)) {
            $item = $this->getForm()->getItemByPostVar($this->getElementId());
            $item->setValue($post);
            $this->getADT()->setTargetRefId($post);
        } else {
            $this->getADT()->setTargetRefId(null);
        }
        return true;
    }

    /**
     * Get sql condition
     * @param string $a_element_id
     * @param int    $mode
     * @param array  $quotedWords
     * @return string
     */
    public function getSQLCondition(string $a_element_id, int $mode = self::SQL_LIKE, array $quotedWords = []): string
    {
        $a_value = '';
        if (!$quotedWords) {
            if ($this->isNull() || !$this->isValid()) {
                return '';
            }
            $a_value = $this->getADT()->getTargetRefId();
        } elseif (count($quotedWords)) {
            $a_value = $quotedWords[0];
        }
        if (!strlen($a_value)) {
            return '';
        }

        $subselect = $a_element_id . ' IN ' .
            '( select ref_id from object_reference obr join object_data obd on obr.obj_id = obd.obj_id ' .
            'where ' . $this->db->like('title', 'text', $a_value . '%') . ' ' .
            ')';
        return $subselect;
    }

    /**
     * Is in condition
     * @param ilADT $a_adt
     * @return bool
     */
    public function isInCondition(ilADT $a_adt): bool
    {
        if ($this->getADT()->getCopyOfDefinition()->isComparableTo($a_adt)) {
            return $this->getADT()->equals($a_adt);
        }
        throw new InvalidArgumentException('Invalid argument given');
    }

    /**
     * get serialized value
     * @return string
     */
    public function getSerializedValue(): string
    {
        if (!$this->isNull() && $this->isValid()) {
            return serialize(array($this->getADT()->getTargetRefId()));
        }
        return '';
    }

    /**
     * Set serialized value
     * @param string $a_value
     */
    public function setSerializedValue(string $a_value): void
    {
        $a_value = unserialize($a_value);
        if (is_array($a_value)) {
            $this->getADT()->setTargetRefId($a_value[0]);
        }
    }
}
