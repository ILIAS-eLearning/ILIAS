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
class ilADTExternalLinkSearchBridgeSingle extends ilADTSearchBridgeSingle
{
    /**
     * Is valid type
     * @param ilADT $a_adt
     * @return bool
     */
    protected function isValidADTDefinition(\ilADTDefinition $a_adt_def): bool
    {
        return $a_adt_def instanceof ilADTExternalLinkDefinition;
    }

    /**
     * Load from filter
     */
    public function loadFilter(): void
    {
        $value = $this->readFilter();
        if ($value !== null) {
            $this->getADT()->setUrl($value);
        }
    }

    /**
     * add external link property to form
     */
    public function addToForm(): void
    {
        $url = new ilTextInputGUI($this->getTitle(), $this->getElementId());
        $url->setSize(255);
        $url->setValue($this->getADT()->getUrl());
        $this->addToParentElement($url);
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

            $this->getADT()->setUrl($post);
        } else {
            $this->writeFilter();
            $this->getADT()->setUrl(null);
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
        if (!$quotedWords) {
            if ($this->isNull() || !$this->isValid()) {
                return '';
            }
            $quotedWords = $this->getADT()->getUrl();
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

    /**
     * Is in condition
     * @param ilADT $a_adt
     * @return bool
     */
    public function isInCondition(ilADT $a_adt): bool
    {
        if ($this->getADT()->getCopyOfDefinition()->isComparableTo($a_adt)) {
            return !strcmp(
                trim($this->getADT()->getUrl() ?? ''),
                trim($a_adt->getUrl() ?? '')
            );
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
            return serialize(array($this->getADT()->getUrl()));
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
            $this->getADT()->setUrl($a_value[0]);
        }
    }
}
