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

class ilADTLocalizedTextSearchBridgeSingle extends ilADTTextSearchBridgeSingle
{
    protected function isValidADTDefinition(ilADTDefinition $a_adt_def): bool
    {
        return ($a_adt_def instanceof ilADTLocalizedTextDefinition);
    }

    // table2gui / filter

    public function loadFilter(): void
    {
        $value = $this->readFilter();
        if ($value !== null) {
            $this->getADT()->setText($value);
        }
    }

    // form

    public function addToForm(): void
    {
        $text = new ilTextInputGUI($this->getTitle(), $this->getElementId());
        $text->setSize(20);
        $text->setMaxLength(512);
        $text->setSubmitFormOnEnter(true);

        $text->setValue($this->getADT()->getText());

        $this->addToParentElement($text);
    }

    public function importFromPost(array $a_post = null): bool
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

    public function getSQLCondition(string $a_element_id, int $mode = self::SQL_LIKE, array $quotedWords = []): string
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

    public function isInCondition(ilADT $a_adt): bool
    {
        if ($this->getADT()->getCopyOfDefinition()->isComparableTo($a_adt)) {
            foreach ($a_adt->getTranslations() as $language => $txt) {
                if (strcasecmp($txt, $this->getADT()->getText()) === 0) {
                    return true;
                }
            }
        }
        return false;
    }

    //  import/export

    public function getSerializedValue(): string
    {
        if (!$this->isNull() && $this->isValid()) {
            return serialize(array($this->getADT()->getText()));
        }
        return '';
    }

    public function setSerializedValue(string $a_value): void
    {
        $a_value = unserialize($a_value);
        if (is_array($a_value)) {
            $this->getADT()->setText($a_value[0]);
        }
    }
}
