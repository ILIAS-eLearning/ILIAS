<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * external link search bridge
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesADT
 */
class ilADTExternalLinkSearchBridgeSingle extends ilADTSearchBridgeSingle
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
        return $a_adt_def instanceof ilADTExternalLinkDefinition;
    }


    /**
     * Load from filter
     */
    public function loadFilter()
    {
        $value = $this->readFilter();
        if ($value !== null) {
            $this->getADT()->setUrl($value);
        }
    }

    /**
     * add external link property to form
     */
    public function addToForm()
    {
        $def = $this->getADT()->getCopyOfDefinition();

        $url = new ilTextInputGUI($this->getTitle(), $this->getElementId());
        $url->setSize(255);
        $url->setValue($this->getADT()->getUrl());
        $this->addToParentElement($url);
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
            $this->getADT()->setUrl($post);
        } else {
            $this->getADT()->setUrl();
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
            $a_value = $this->getADT()->getUrl();
        }

        switch ($a_mode) {
            case self::SQL_STRICT:
                if (!is_array($a_value)) {
                    return $a_element_id . " = " . $db->quote($a_value, "text");
                } else {
                    return $db->in($a_element_id, $a_value, "", "text");
                }
                break;

            case self::SQL_LIKE:
                if (!is_array($a_value)) {
                    return $db->like($a_element_id, "text", "%" . $a_value . "%");
                } else {
                    $tmp = array();
                    foreach ($a_value as $word) {
                        if ($word) {
                            $tmp[] = $db->like($a_element_id, "text", "%" . $word . "%");
                        }
                    }
                    if (sizeof($tmp)) {
                        return "(" . implode(" OR ", $tmp) . ")";
                    }
                }
                break;

            case self::SQL_LIKE_END:
                if (!is_array($a_value)) {
                    return $db->like($a_element_id, "text", $a_value . "%");
                }
                break;

            case self::SQL_LIKE_START:
                if (!is_array($a_value)) {
                    return $db->like($a_element_id, "text", "%" . $a_value);
                }
                break;
        }
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
            return serialize(array($this->getADT()->getUrl()));
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
            $this->getADT()->setUrl($a_value[0]);
        }
    }
}
