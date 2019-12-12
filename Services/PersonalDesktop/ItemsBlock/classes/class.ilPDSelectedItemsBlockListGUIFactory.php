<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilPDSelectedItemsBlockListGUIFactory
 */
class ilPDSelectedItemsBlockListGUIFactory
{
    /**
     * @var ilObjectListGUI[]
     */
    protected static $list_by_type = array();

    /**
     * @var ilObjectDefinition
     */
    protected $objDefinition;

    /**
     * @var ilPDSelectedItemsBlockGUI
     */
    protected $block;

    /**
     * ilPDSelectedItemsBlockListGUIFactory constructor.
     * @param ilPDSelectedItemsBlockGUI $block
     */
    public function __construct(ilPDSelectedItemsBlockGUI $block)
    {
        global $DIC;

        $this->objDefinition = $DIC['objDefinition'];
        $this->block         = $block;
    }

    /**
     * @param string $a_type
     * @return ilObjectListGUI
     * @throws ilException
     */
    public function byType($a_type)
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

            if ($this->block->getCurrentDetailLevel() < 3 || $this->block->isManagedView()) {
                $item_list_gui->enableDescription(false);
                $item_list_gui->enableProperties(false);
                $item_list_gui->enablePreconditions(false);
                $item_list_gui->enableNoticeProperties(false);
            }

            if ($this->block->getCurrentDetailLevel() < 2 || $this->block->isManagedView()) {
                $item_list_gui->enableCommands(true, true);
            }

            self::$list_by_type[$a_type] = $item_list_gui;
        }

        return (clone self::$list_by_type[$a_type]);
    }
}
