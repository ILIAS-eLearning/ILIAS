<?php

declare(strict_types=1);

/**
 * Class ilADTDateTimeSearchBridgeRange
 */
class ilADTDateTimeSearchBridgeRange extends ilADTSearchBridgeRange
{
    protected function isValidADTDefinition(ilADTDefinition $a_adt_def): bool
    {
        return ($a_adt_def instanceof ilADTDateTimeDefinition);
    }

    // table2gui / filter

    public function loadFilter(): void
    {
        $value = $this->readFilter();
        if (isset($value) && is_array($value)) {
            if ($value["lower"] ?? false) {
                $this->getLowerADT()->setDate(new ilDateTime($value["lower"], IL_CAL_DATETIME));
            }
            if ($value["upper"] ?? false) {
                $this->getUpperADT()->setDate(new ilDateTime($value["upper"], IL_CAL_DATETIME));
            }
        }
    }

    // form

    public function addToForm(): void
    {
        if ($this->getForm() instanceof ilPropertyFormGUI) {
            // :TODO: use DateDurationInputGUI ?!

            $check = new ilCustomInputGUI($this->getTitle());

            $date_from = new ilDateTimeInputGUI($this->lng->txt('from'), $this->addToElementId("lower"));
            $date_from->setShowTime(true);
            $check->addSubItem($date_from);

            if ($this->getLowerADT()->getDate() && !$this->getLowerADT()->isNull()) {
                $date_from->setDate($this->getLowerADT()->getDate());
                $checked = true;
            }

            $date_until = new ilDateTimeInputGUI($this->lng->txt('until'), $this->addToElementId("upper"));
            $date_until->setShowTime(true);
            $check->addSubItem($date_until);

            if ($this->getUpperADT()->getDate() && !$this->getUpperADT()->isNull()) {
                $date_until->setDate($this->getUpperADT()->getDate());
                $checked = true;
            }

            $this->addToParentElement($check);
        } else {
            // see ilTable2GUI::addFilterItemByMetaType()
            $item = new ilCombinationInputGUI($this->getTitle(), $this->getElementId());

            $lower = new ilDateTimeInputGUI("", $this->addToElementId("lower"));
            $lower->setShowTime(true);
            $item->addCombinationItem("lower", $lower, $this->lng->txt("from"));

            if ($this->getLowerADT()->getDate() && !$this->getLowerADT()->isNull()) {
                $lower->setDate($this->getLowerADT()->getDate());
            }

            $upper = new ilDateTimeInputGUI("", $this->addToElementId("upper"));
            $upper->setShowTime(true);
            $item->addCombinationItem("upper", $upper, $this->lng->txt("to"));

            if ($this->getUpperADT()->getDate() && !$this->getUpperADT()->isNull()) {
                $upper->setDate($this->getUpperADT()->getDate());
            }

            $item->setComparisonMode(ilCombinationInputGUI::COMPARISON_ASCENDING);

            $this->addToParentElement($item);
        }
    }

    protected function shouldBeImportedFromPost($a_post): bool
    {
        if ($this->getForm() instanceof ilPropertyFormGUI) {
            return (bool) $a_post["tgl"];
        }
        return parent::shouldBeImportedFromPost($a_post);
    }

    public function importFromPost(array $a_post = null): bool
    {
        $post = $this->extractPostValues($a_post);

        if ($post && $this->shouldBeImportedFromPost($post)) {
            $start = ilCalendarUtil::parseIncomingDate($post["lower"], true);
            $end = ilCalendarUtil::parseIncomingDate($post["upper"], true);
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
                    "lower" => (!$start || $start->isNull()) ? null : $start->get(IL_CAL_DATETIME),
                    "upper" => (!$end || $end->isNull()) ? null : $end->get(IL_CAL_DATETIME)
                ));
            }

            $this->getLowerADT()->setDate($start);
            $this->getUpperADT()->setDate($end);
        } else {
            $this->getLowerADT()->setDate();
            $this->getUpperADT()->setDate();
        }
        return true;
    }

    // db

    public function getSQLCondition(string $a_element_id, int $mode = self::SQL_LIKE, array $quotedWords = []): string
    {
        if (!$this->isNull() && $this->isValid()) {
            $sql = array();
            if (!$this->getLowerADT()->isNull()) {
                $sql[] = $a_element_id . " >= " . $this->db->quote(
                    $this->getLowerADT()->getDate()->get(IL_CAL_DATETIME),
                    "timestamp"
                );
            }
            if (!$this->getUpperADT()->isNull()) {
                $sql[] = $a_element_id . " <= " . $this->db->quote(
                    $this->getUpperADT()->getDate()->get(IL_CAL_DATETIME),
                    "timestamp"
                );
            }
            return "(" . implode(" AND ", $sql) . ")";
        }
        return '';
    }

    public function isInCondition(ilADT $a_adt): bool
    {
        assert($a_adt instanceof ilADTDateTime);

        if (!$this->getLowerADT()->isNull() && !$this->getUpperADT()->isNull()) {
            return $a_adt->isInbetweenOrEqual($this->getLowerADT(), $this->getUpperADT());
        } elseif (!$this->getLowerADT()->isNull()) {
            return $a_adt->isLargerOrEqual($this->getLowerADT());
        } else {
            return $a_adt->isSmallerOrEqual($this->getUpperADT());
        }
    }

    //  import/export

    public function getSerializedValue(): string
    {
        if (!$this->isNull() && $this->isValid()) {
            $res = array();
            if (!$this->getLowerADT()->isNull()) {
                $res["lower"] = $this->getLowerADT()->getDate()->get(IL_CAL_DATETIME);
            }
            if (!$this->getUpperADT()->isNull()) {
                $res["upper"] = $this->getUpperADT()->getDate()->get(IL_CAL_DATETIME);
            }
            return serialize($res);
        }
        return '';
    }

    public function setSerializedValue(string $a_value): void
    {
        $a_value = unserialize($a_value);
        if (is_array($a_value)) {
            if (isset($a_value["lower"])) {
                $this->getLowerADT()->setDate(new ilDateTime($a_value["lower"], IL_CAL_DATETIME));
            }
            if (isset($a_value["upper"])) {
                $this->getUpperADT()->setDate(new ilDateTime($a_value["upper"], IL_CAL_DATETIME));
            }
        }
    }
}
