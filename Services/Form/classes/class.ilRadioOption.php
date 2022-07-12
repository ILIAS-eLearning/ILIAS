<?php declare(strict_types=1);

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
 * This class represents an option in a radio group
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilRadioOption
{
    protected string $title = "";
    protected string $value = "";
    protected string $info = "";
    protected array $sub_items = array();
    protected bool $disabled = false;
    
    public function __construct(
        string $a_title = "",
        string $a_value = "",
        string $a_info = ""
    ) {
        $this->setTitle($a_title);
        $this->setValue($a_value);
        $this->setInfo($a_info);
    }
    
    public function setTitle(string $a_title) : void
    {
        $this->title = $a_title;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function setInfo(string $a_info) : void
    {
        $this->info = $a_info;
    }

    public function getInfo() : string
    {
        return $this->info;
    }

    public function setValue(string $a_value) : void
    {
        $this->value = $a_value;
    }

    public function getValue() : string
    {
        return $this->value;
    }
    
    public function setDisabled(bool $a_disabled) : void
    {
        $this->disabled = $a_disabled;
    }
    
    public function getDisabled() : bool
    {
        return $this->disabled;
    }

    /**
     * @param ilFormPropertyGUI|ilFormSectionHeaderGUI $a_item
     */
    public function addSubItem($a_item) : void
    {
        $this->sub_items[] = $a_item;
    }

    public function getSubItems() : array
    {
        return $this->sub_items;
    }

    public function getSubInputItemsRecursive() : array
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
}
