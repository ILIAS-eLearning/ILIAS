<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * external link search bridge
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesADT
 */
class ilADTInternalLinkSearchBridgeSingle extends ilADTSearchBridgeSingle
{
    const SQL_STRICT = 1;
    const SQL_LIKE = 2;
    const SQL_LIKE_END = 3;
    const SQL_LIKE_START = 4;

    
    /**
     * Is valid type
     * @param ilADT $a_adt
     * @return bool
     */
    protected function isValidADTDefinition(\ilADTDefinition $a_adt_def)
    {
        return $a_adt_def instanceof ilADTInternalLinkDefinition;
    }


    /*
     * Add search property to form
     */
    public function addToForm()
    {
        $title = new ilTextInputGUI($this->getTitle(), $this->getElementId());
        $title->setSize(255);
        $this->addToParentElement($title);
    }
    

    /**


    /**
     * Load from filter
     */
    public function loadFilter()
    {
        $value = $this->readFilter();
        if ($value !== null) {
            $this->getADT()->setTargetRefId($value);
        }
    }

    /**
     * Import from post
     * @param array $a_post
     */
    public function importFromPost(array $a_post = null)
    {
        $post = $this->extractPostValues($a_post);

        if ($post && $this->shouldBeImportedFromPost($post)) {
            $item = $this->getForm()->getItemByPostVar($this->getElementId());
            $item->setValue($post);
            $this->getADT()->setTargetRefId($post);
        } else {
            $this->getADT()->setTargetRefId(null);
        }
    }

    /**
     * Get sql condition
     * @param int $a_element_id
     * @return string
     */
    public function getSQLCondition($a_element_id, $a_mode = self::SQL_LIKE, $a_value = null)
    {
        $db = $GLOBALS['DIC']->database();
        
        if (!$a_value) {
            if ($this->isNull() || !$this->isValid()) {
                return;
            }
            $a_value = $this->getADT()->getTargetRefId();
        }
        
        $subselect = $a_element_id . ' IN ' .
            '( select ref_id from object_reference obr join object_data obd on obr.obj_id = obd.obj_id ' .
            'where ' . $db->like('title', 'text', $a_value . '%') . ' ' .
            ')';
        return $subselect;
    }

    /**
     * Is in condition
     * @param ilADT $a_adt
     * @return bool
     */
    public function isInCondition(ilADT $a_adt)
    {
        if ($this->isValidADT($a_adt)) {
            return $this->getADT()->equals($a_adt);
        }
        // @todo throw exception
    }

    /**
     * get serialized value
     * @return type
     */
    public function getSerializedValue()
    {
        if (!$this->isNull() && $this->isValid()) {
            return serialize(array($this->getADT()->getTargetRefId()));
        }
    }

    /**
     * Set serialized value
     * @param string $a_value
     */
    public function setSerializedValue($a_value)
    {
        $a_value = unserialize($a_value);
        if (is_array($a_value)) {
            $this->getADT()->setTargetRefId($a_value[0]);
        }
    }
}
