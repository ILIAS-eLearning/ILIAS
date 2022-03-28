<?php declare(strict_types=1);

class ilADTTextSearchBridgeSingle extends ilADTSearchBridgeSingle
{
    public const SQL_STRICT = 1;
    public const SQL_LIKE = 2;
    public const SQL_LIKE_END = 3;
    public const SQL_LIKE_START = 4;

    protected function isValidADTDefinition(ilADTDefinition $a_adt_def) : bool
    {
        return ($a_adt_def instanceof ilADTTextDefinition);
    }

    // table2gui / filter

    public function loadFilter() : void
    {
        $value = $this->readFilter();
        if ($value !== null) {
            $this->getADT()->setText($value);
        }
    }

    // form

    public function addToForm() : void
    {
        $text = new ilTextInputGUI($this->getTitle(), $this->getElementId());
        $text->setSize(20);
        $text->setMaxLength(512);
        $text->setSubmitFormOnEnter(true);

        $text->setValue($this->getADT()->getText());

        $this->addToParentElement($text);
    }

    public function importFromPost(array $a_post = null) : bool
    {
        $post = $this->extractPostValues($a_post);

        if ($post && $this->shouldBeImportedFromPost($post)) {
            if ($this->getForm() instanceof ilPropertyFormGUI) {
                $item = $this->getForm()->getItemByPostVar($this->getElementId());
                $item->setValue($post);
            } elseif (array_key_exists($this->getElementId(), $this->table_filter_fields)) {
                $this->table_filter_fields[$this->getElementId()]->setValue($post);
                $this->writeFilter($post);
            }

            $this->getADT()->setText($post);
        } else {
            $this->writeFilter();
            $this->getADT()->setText();
        }
        return true;
    }

    // db

    public function getSQLCondition(string $a_element_id, int $mode = self::SQL_LIKE, array $quotedWords = []) : string
    {
        if (!$quotedWords) {
            if ($this->isNull() || !$this->isValid()) {
                return '';
            }
            $quotedWords = $this->getADT()->getText();
        }

        switch ($mode) {
            case self::SQL_STRICT:
                if (!is_array($quotedWords)) {
                    return $a_element_id . " = " . $this->db->quote($quotedWords, "text");
                } else {
                    return $this->db->in($a_element_id, $quotedWords, false, "text");
                }

                // no break
            case self::SQL_LIKE:
                if (!is_array($quotedWords)) {
                    return $this->db->like($a_element_id, "text", "%" . $quotedWords . "%");
                } else {
                    $tmp = array();
                    foreach ($quotedWords as $word) {
                        if ($word) {
                            $tmp[] = $this->db->like($a_element_id, "text", "%" . $word . "%");
                        }
                    }
                    if (count($tmp)) {
                        return "(" . implode(" OR ", $tmp) . ")";
                    }
                }
                break;

            case self::SQL_LIKE_END:
                if (!is_array($quotedWords)) {
                    return $this->db->like($a_element_id, "text", $quotedWords . "%");
                }
                break;

            case self::SQL_LIKE_START:
                if (!is_array($quotedWords)) {
                    return $this->db->like($a_element_id, "text", "%" . $quotedWords);
                }
                break;
        }
        return '';
    }

    public function isInCondition(ilADT $a_adt) : bool
    {
        assert($a_adt instanceof ilADTText);

        // :TODO: search mode (see above)
        return $this->getADT()->equals($a_adt);
    }

    //  import/export

    public function getSerializedValue() : string
    {
        if (!$this->isNull() && $this->isValid()) {
            return serialize(array($this->getADT()->getText()));
        }
        return '';
    }

    public function setSerializedValue(string $a_value) : void
    {
        $a_value = unserialize($a_value);
        if (is_array($a_value)) {
            $this->getADT()->setText($a_value[0]);
        }
    }
}
