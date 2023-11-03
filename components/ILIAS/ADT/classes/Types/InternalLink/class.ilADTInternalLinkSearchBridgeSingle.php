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
 * external link search bridge
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesADT
 */
class ilADTInternalLinkSearchBridgeSingle extends ilADTSearchBridgeSingle
{
    private $title_query = '';

    /**
     * Is valid type
     * @param ilADT $a_adt
     * @return bool
     */
    protected function isValidADTDefinition(\ilADTDefinition $a_adt_def): bool
    {
        return $a_adt_def instanceof ilADTInternalLinkDefinition;
    }

    public function setTitleQuery(string $query): void
    {
        $this->title_query = $query;
    }

    public function getTitleQuery(): string
    {
        return $this->title_query;
    }

    /*
     * Add search property to form
     */
    public function addToForm(): void
    {
        $title = new ilTextInputGUI($this->getTitle(), $this->getElementId());
        $title->setSize(255);
        $title->setValue($this->getTitleQuery());
        $this->addToParentElement($title);
    }

    /**
     * Load from filter
     */
    public function loadFilter(): void
    {
        $value = $this->readFilter();
        if ($value !== null && $value !== '') {
            $this->getADT()->setTargetRefId(1);
            $this->setTitleQuery($value);
        }
    }

    public function importFromPost(array $a_post = null): bool
    {
        $post = $this->extractPostValues($a_post);

        if ($post && $this->shouldBeImportedFromPost($post)) {
            if ($this->getForm() instanceof ilPropertyFormGUI) {
                $item = $this->getForm()->getItemByPostVar($this->getElementId());
                $item->setValue($post);
                $this->setTitleQuery($post);
                $this->getADT()->setTargetRefId(1);
            } elseif (array_key_exists($this->getElementId(), $this->table_filter_fields)) {
                $this->table_filter_fields[$this->getElementId()]->setValue($post);
                $this->writeFilter($post);
                $this->setTitleQuery($post);
                $this->getADT()->setTargetRefId(1);
            }
        } else {
            $this->writeFilter();
            $this->setTitleQuery('');
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
            $ref_id = $a_adt->getTargetRefId();
            $title = ilObject::_lookupTitle((int) ilObject::_lookupObjId((int) $ref_id));
            return strcasecmp($title, $this->getTitleQuery()) === 0;
        }
        return false;
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
