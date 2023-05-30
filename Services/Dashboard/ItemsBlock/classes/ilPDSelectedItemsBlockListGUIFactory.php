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

declare(strict_types=1);

class ilPDSelectedItemsBlockListGUIFactory
{
    /** @var ilObjectListGUI[] */
    protected static array $list_by_type = [];
    protected readonly ilObjectDefinition $objDefinition;

    public function __construct(
        protected readonly ilPDSelectedItemsBlockGUI $block,
        protected readonly ilPDSelectedItemsBlockViewGUI $blockView
    ) {
        global $DIC;

        $this->objDefinition = $DIC['objDefinition'];
    }

    /**
     * @throws ilException
     */
    public function byType(string $type): ilObjectListGUI
    {
        /** @var $item_list_gui ilObjectListGUI */
        if (!array_key_exists($type, self::$list_by_type)) {
            $class = $this->objDefinition->getClassName($type);
            if (!$class) {
                throw new ilException(sprintf('Could not find a class for object type: %s', $type));
            }

            $location = $this->objDefinition->getLocation($type);
            if (!$location) {
                throw new ilException(sprintf('Could not find a class location for object type: %s', $type));
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

            $item_list_gui->enableCommands(true, true);

            self::$list_by_type[$type] = $item_list_gui;
        }

        return (clone self::$list_by_type[$type]);
    }
}
