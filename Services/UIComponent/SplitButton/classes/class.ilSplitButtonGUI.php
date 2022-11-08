<?php

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
 * Class ilSplitButton
 * @author Michael Jansen <mjansen@databay.de>
 *
 * @deprecated 10
 */
class ilSplitButtonGUI extends ilButtonBase
{
    protected ilButtonBase $default_button;
    /**
     * @var ilSplitButtonMenuItem[]
     */
    protected array $menu_items = [];

    protected function __construct(int $a_type)
    {
        parent::__construct($a_type);
    }

    public static function getInstance(): self
    {
        return new self(self::TYPE_SPLIT);
    }

    public function addMenuItem(ilSplitButtonMenuItem $menu_item): void
    {
        $this->menu_items[] = $menu_item;
    }

    public function removeMenuItem(ilSplitButtonMenuItem $menu_item): void
    {
        $key = array_search($menu_item, $this->menu_items);
        if ($key !== false) {
            unset($this->menu_items[$key]);
        }
    }

    public function hasMenuItems(): bool
    {
        return count($this->menu_items) > 0;
    }

    /**
     * @return ilSplitButtonMenuItem[]
     */
    public function getMenuItems(): array
    {
        return $this->menu_items;
    }

    /**
     * @param ilSplitButtonMenuItem[] $menu_items
     * @throws ilSplitButtonException
     */
    public function setMenuItems(array $menu_items): void
    {
        array_walk($menu_items, static function ($item, $idx): void {
            if (!($item instanceof ilSplitButtonMenuItem)) {
                throw new ilSplitButtonException(sprintf(
                    "Cannot set menu items, element at index '%s' is not of type 'ilSplitButtonItem'",
                    $idx
                ));
            }
        });

        $this->menu_items = $menu_items;
    }

    public function getDefaultButton(): ilButtonBase
    {
        return $this->default_button;
    }

    public function hasDefaultButton(): bool
    {
        return ($this->default_button instanceof ilButtonBase);
    }

    public function setDefaultButton(ilButtonBase $default_button): void
    {
        $this->default_button = $default_button;
    }

    /**
     * @throws ilSplitButtonException
     */
    public function render(): string
    {
        $tpl = new ilTemplate('tpl.split_button.html', true, true, 'Services/UIComponent/SplitButton');

        if (!$this->hasDefaultButton()) {
            throw new ilSplitButtonException(
                "Cannot render a split button without a default button"
            );
        }

        $tpl->setVariable('DEFAULT_ITEM_CONTENT', $this->getDefaultButton()->render());
        if ($this->hasMenuItems()) {
            $btn_classes = $this->getDefaultButton()->getCSSClasses();
            if ($this->getDefaultButton()->isPrimary()) {
                $btn_classes[] = 'btn-primary';
            }
            $tpl->setVariable('BTN_CSS_CLASS', implode(' ', $btn_classes));

            foreach ($this->getMenuItems() as $item) {
                if ($item instanceof ilSplitButtonSeparatorMenuItem) {
                    $tpl->setCurrentBlock('separator');
                    $tpl->touchBlock('separator');
                } else {
                    $tpl->setCurrentBlock('item');
                    $tpl->setVariable('CONTENT', $item->getContent());
                }
                $tpl->parseCurrentBlock();

                $tpl->setCurrentBlock('items');
                $tpl->parseCurrentBlock();
            }

            $tpl->setVariable('TXT_TOGGLE_DROPDOWN', $this->lng->txt('toggle_dropdown'));
        }

        return $tpl->get();
    }
}
