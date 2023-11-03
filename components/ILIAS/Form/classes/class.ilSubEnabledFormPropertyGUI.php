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
 * This class represents a property that may include a sub form
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilSubEnabledFormPropertyGUI extends ilFormPropertyGUI
{
    protected array $sub_items = array();

    public function addSubItem(ilFormPropertyGUI $a_item): void
    {
        $a_item->setParent($this);
        $this->sub_items[] = $a_item;
    }

    public function getSubItems(): array
    {
        return $this->sub_items;
    }

    /**
     * returns a flat array of possibly existing subitems recursively
     */
    public function getSubInputItemsRecursive(): array
    {
        $subInputItems = array();

        foreach ($this->sub_items as $subItem) {
            if ($subItem->getType() == 'section_header') {
                continue;
            }

            $subInputItems[] = $subItem;

            if ($subItem instanceof ilSubEnabledFormPropertyGUI) {
                $subInputItems = array_merge($subInputItems, $subItem->getSubInputItemsRecursive());
            }
        }

        return $subInputItems;
    }

    /**
     * Check SubItems
     */
    final public function checkSubItemsInput(): bool
    {
        $ok = true;
        foreach ($this->getSubItems() as $item) {
            $item_ok = $item->checkInput();
            if (!$item_ok) {
                $ok = false;
            }
        }
        return $ok;
    }

    final public function getSubForm(): ?ilPropertyFormGUI
    {
        // subitems
        $pf = null;
        if (count($this->getSubItems()) > 0) {
            $pf = new ilPropertyFormGUI();
            $pf->setMode("subform");
            $pf->setItems($this->getSubItems());
        }

        return $pf;
    }

    public function getItemByPostVar(string $a_post_var): ?ilFormPropertyGUI
    {
        if ($this->getPostVar() == $a_post_var) {
            return $this;
        }

        foreach ($this->getSubItems() as $item) {
            if ($item->getType() != "section_header") {
                $ret = $item->getItemByPostVar($a_post_var);
                if (is_object($ret)) {
                    return $ret;
                }
            }
        }

        return null;
    }
}
