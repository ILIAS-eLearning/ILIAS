<?php

require_once "Services/ADT/classes/Bridges/class.ilADTSearchBridgeRange.php";

class ilADTDateSearchBridgeRange extends ilADTSearchBridgeRange
{
    protected function isValidADTDefinition(ilADTDefinition $a_adt_def)
    {
        return ($a_adt_def instanceof ilADTDateDefinition);
    }
    
    
    // table2gui / filter
    
    public function loadFilter()
    {
        $value = $this->readFilter();
        if ($value !== null) {
            if ($value["lower"]) {
                $this->getLowerADT()->setDate(new ilDate($value["lower"], IL_CAL_DATE));
            }
            if ($value["upper"]) {
                $this->getUpperADT()->setDate(new ilDate($value["upper"], IL_CAL_DATE));
            }
        }
    }
    
    
    // form
    
    public function addToForm()
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        if ($this->getForm() instanceof ilPropertyFormGUI) {
            $check = new ilCustomInputGUI($this->getTitle());

            $date_from = new ilDateTimeInputGUI($lng->txt('from'), $this->addToElementId("lower"));
            $date_from->setShowTime(false);
            $check->addSubItem($date_from);
            
            if ($this->getLowerADT()->getDate() && !$this->getLowerADT()->isNull()) {
                $date_from->setDate($this->getLowerADT()->getDate());
                $checked = true;
            }

            $date_until = new ilDateTimeInputGUI($lng->txt('until'), $this->addToElementId("upper"));
            $date_until->setShowTime(false);
            $check->addSubItem($date_until);
            
            if ($this->getUpperADT()->getDate() && !$this->getUpperADT()->isNull()) {
                $date_until->setDate($this->getUpperADT()->getDate());
                $checked = true;
            }
            
            $this->addToParentElement($check);
        } else {
            include_once("./Services/Form/classes/class.ilCombinationInputGUI.php");
            include_once("./Services/Form/classes/class.ilDateTimeInputGUI.php");
            
            $item = new ilCombinationInputGUI($this->getTitle(), $this->getElementId());
            
            $lower = new ilDateTimeInputGUI("", $this->addToElementId("lower"));
            $item->addCombinationItem("lower", $lower, $lng->txt("from"));
            
            if ($this->getLowerADT()->getDate() && !$this->getLowerADT()->isNull()) {
                $lower->setDate($this->getLowerADT()->getDate());
            }
            
            $upper = new ilDateTimeInputGUI("", $this->addToElementId("upper"));
            $item->addCombinationItem("upper", $upper, $lng->txt("to"));
            
            if ($this->getUpperADT()->getDate() && !$this->getUpperADT()->isNull()) {
                $upper->setDate($this->getUpperADT()->getDate());
            }
            
            $item->setComparisonMode(ilCombinationInputGUI::COMPARISON_ASCENDING);
            
            $this->addToParentElement($item);
        }
    }

    protected function shouldBeImportedFromPost($a_post)
    {
        if ($this->getForm() instanceof ilPropertyFormGUI &&
            !(bool) $this->text_input) {
            return (bool) $a_post["tgl"];
        }
        return parent::shouldBeImportedFromPost($a_post);
    }
    
    public function importFromPost(array $a_post = null)
    {
        $post = $this->extractPostValues($a_post);
    
        if ($post && $this->shouldBeImportedFromPost($post)) {
            include_once "Services/Calendar/classes/class.ilCalendarUtil.php";
            $start = ilCalendarUtil::parseIncomingDate($post["lower"]);
            $end = ilCalendarUtil::parseIncomingDate($post["upper"]);
            
            if ($start && $end && $start->get(IL_CAL_UNIX) > $end->get(IL_CAL_UNIX)) {
                $tmp = $start;
                $start = $end;
                $end = $tmp;
            }
            
            if ($this->getForm() instanceof ilPropertyFormGUI) {
                $item = $this->getForm()->getItemByPostVar($this->getElementId() . "[lower]");
                $item->setDate($start);

                $item = $this->getForm()->getItemByPostVar($this->getElementId() . "[upper]");
                $item->setDate($end);
            } elseif (array_key_exists($this->getElementId(), $this->table_filter_fields)) {
                $this->table_filter_fields[$this->getElementId()]->getCombinationItem("lower")->setDate($start);
                $this->table_filter_fields[$this->getElementId()]->getCombinationItem("upper")->setDate($end);
                $this->writeFilter(array(
                    "lower" => (!$start || $start->isNull()) ? null: $start->get(IL_CAL_DATE),
                    "upper" => (!$end || $end->isNull()) ? null : $end->get(IL_CAL_DATE)
                ));
            }
            
            $this->getLowerADT()->setDate($start);
            $this->getUpperADT()->setDate($end);
        } else {
            $this->getLowerADT()->setDate();
            $this->getUpperADT()->setDate();
        }
    }
            
    
    // db
    
    public function getSQLCondition($a_element_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        if (!$this->isNull() && $this->isValid()) {
            $sql = array();
            if (!$this->getLowerADT()->isNull()) {
                $sql[] = $a_element_id . " >= " . $ilDB->quote($this->getLowerADT()->getDate()->get(IL_CAL_DATE), "date");
            }
            if (!$this->getUpperADT()->isNull()) {
                $sql[] = $a_element_id . " <= " . $ilDB->quote($this->getUpperADT()->getDate()->get(IL_CAL_DATE), "date");
            }
            return "(" . implode(" AND ", $sql) . ")";
        }
    }
    
    public function isInCondition(ilADT $a_adt)
    {
        assert($a_adt instanceof ilADTDate);
        
        if (!$this->getLowerADT()->isNull() && !$this->getUpperADT()->isNull()) {
            return $a_adt->isInbetweenOrEqual($this->getLowerADT(), $this->getUpperADT());
        } elseif (!$this->getLowerADT()->isNull()) {
            return $a_adt->isLargerOrEqual($this->getLowerADT());
        } else {
            return $a_adt->isSmallerOrEqual($this->getUpperADT());
        }
    }
    
    
    //  import/export
        
    public function getSerializedValue()
    {
        if (!$this->isNull() && $this->isValid()) {
            $res = array();
            if (!$this->getLowerADT()->isNull()) {
                $res["lower"] = $this->getLowerADT()->getDate()->get(IL_CAL_DATE);
            }
            if (!$this->getUpperADT()->isNull()) {
                $res["upper"] = $this->getUpperADT()->getDate()->get(IL_CAL_DATE);
            }
            return serialize($res);
        }
    }
    
    public function setSerializedValue($a_value)
    {
        $a_value = unserialize($a_value);
        if (is_array($a_value)) {
            if (isset($a_value["lower"])) {
                $this->getLowerADT()->setDate(new ilDate($a_value["lower"], IL_CAL_DATE));
            }
            if (isset($a_value["upper"])) {
                $this->getUpperADT()->setDate(new ilDate($a_value["upper"], IL_CAL_DATE));
            }
        }
    }
}
