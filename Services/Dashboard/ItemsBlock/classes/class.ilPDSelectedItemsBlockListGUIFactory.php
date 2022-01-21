<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

class ilPDSelectedItemsBlockListGUIFactory
{
    /** @var ilObjectListGUI[] */
    protected static array $list_by_type = [];
    protected ilObjectDefinition $objDefinition;
    protected ilPDSelectedItemsBlockGUI $block;
    protected ilPDSelectedItemsBlockViewGUI $blockView;

    public function __construct(
        ilPDSelectedItemsBlockGUI $block,
        ilPDSelectedItemsBlockViewGUI $blockView
    ) {
        global $DIC;

        $this->objDefinition = $DIC['objDefinition'];
        $this->block = $block;
        $this->blockView = $blockView;
    }

    /**
     * @throws ilException
     */
    public function byType(string $a_type) : \ilObjectListGUI
    {
        /** @var $item_list_gui ilObjectListGUI */
        if (!array_key_exists($a_type, self::$list_by_type)) {
            $class = $this->objDefinition->getClassName($a_type);
            if (!$class) {
                throw new ilException(sprintf("Could not find a class for object type: %s", $a_type));
            }

            $location = $this->objDefinition->getLocation($a_type);
            if (!$location) {
                throw new ilException(sprintf("Could not find a class location for object type: %s", $a_type));
            }

            $full_class = 'ilObj' . $class . 'ListGUI';
            require_once $location . '/class.' . $full_class . '.php';
            $item_list_gui = new $full_class();

            $item_list_gui->setContainerObject($this->block);
            $item_list_gui->enableNotes(false);
            $item_list_gui->enableComments(false);
            $item_list_gui->enableTags(false);

            $item_list_gui->enableIcon(true);
            $item_list_gui->enableDelete(false);
            $item_list_gui->enableCut(false);
            $item_list_gui->enableCopy(false);
            $item_list_gui->enableLink(false);
            $item_list_gui->enableInfoScreen(true);
            $item_list_gui->enableSubscribe($this->block->getViewSettings()->enabledSelectedItems());

            //$item_list_gui->enableDescription(false);
            //$item_list_gui->enableProperties(false);
            //$item_list_gui->enablePreconditions(false);
            //$item_list_gui->enableNoticeProperties(false);

            $item_list_gui->enableCommands(true, true);

            self::$list_by_type[$a_type] = $item_list_gui;
        }

        return (clone self::$list_by_type[$a_type]);
    }
}
