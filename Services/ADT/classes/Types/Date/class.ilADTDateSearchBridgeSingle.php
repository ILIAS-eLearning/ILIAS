<?php declare(strict_types=1);

class ilADTDateSearchBridgeSingle extends ilADTSearchBridgeSingle
{
    protected function isValidADTDefinition(ilADTDefinition $a_adt_def) : bool
    {
        return ($a_adt_def instanceof ilADTDateDefinition);
    }

    // table2gui / filter

    public function loadFilter() : void
    {
        $value = $this->readFilter();
        if ($value !== null) {
            // $this->getADT()->setDate(new ilDate($value, IL_CAL_DATE));
        }
    }

    // form

    public function addToForm() : void
    {
        $adt_date = $this->getADT()->getDate();

        $date = new ilDateTimeInputGUI($this->getTitle(), $this->getElementId());
        $date->setShowTime(false);

        $date->setDate($adt_date);

        $this->addToParentElement($date);
    }

    /**
     * @inheritDoc
     */
    protected function shouldBeImportedFromPost($a_post) : bool
    {
        return (bool) $a_post["tgl"];
    }

    public function importFromPost(array $a_post = null) : bool
    {
        $post = $this->extractPostValues($a_post);

        if ($post && $this->shouldBeImportedFromPost($post)) {
            $date = ilCalendarUtil::parseIncomingDate($post);

            if ($this->getForm() instanceof ilPropertyFormGUI) {
                $item = $this->getForm()->getItemByPostVar($this->getElementId());
                $item->setDate($date);
            } elseif (array_key_exists($this->getElementId(), $this->table_filter_fields)) {
                $this->table_filter_fields[$this->getElementId()]->setDate($date);
                $this->writeFilter($date->get(IL_CAL_DATE));
            }

            $this->getADT()->setDate($date);
        } else {
            $this->writeFilter();
            $this->getADT()->setDate();
        }
        return true;
    }

    // db

    public function getSQLCondition(string $a_element_id, int $mode = self::SQL_LIKE, array $quotedWords = []) : string
    {
        if (!$this->isNull() && $this->isValid()) {
            return $a_element_id . " = " . $this->db->quote($this->getADT()->getDate()->get(IL_CAL_DATE), "date");
        }
        return '';
    }

    public function isInCondition(ilADT $a_adt) : bool
    {
        assert($a_adt instanceof ilADTDate);

        return $this->getADT()->equals($a_adt);
    }

    //  import/export

    public function getSerializedValue() : string
    {
        if (!$this->isNull() && $this->isValid()) {
            return serialize(array($this->getADT()->getDate()->get(IL_CAL_DATE)));
        }
        return '';
    }

    public function setSerializedValue(string $a_value) : void
    {
        $a_value = unserialize($a_value);
        if (is_array($a_value)) {
            $this->getADT()->setDate(new ilDate($a_value[0], IL_CAL_DATE));
        }
    }
}
