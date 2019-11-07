<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Dashboard recommended content UI
 *
 * @author killing@leifos.de
 */
class ilDashboardRecommendedContentGUI
{
    /**
     * @var \ilObjUser
     */
    protected $user;

    /**
     * @var \ilRecommendedContentManager
     */
    protected $rec_manager;

    /**
     * @var int[]
     */
    protected $recommendations;

    /**
     * @var array
     */
    static $list_by_type = [];


    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var \ilLanguage
     */
    protected $lng;


    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->rec_manager = new ilRecommendedContentManager();
        $this->objDefinition = $DIC["objDefinition"];
        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();

        $this->lng->loadLanguageModule("rep");

        $this->recommendations = $this->rec_manager->getOpenRecommendationsOfUser($this->user->getId());
    }

    /**
     * Render
     *
     * @return string
     */
    public function render()
    {
        return $this->ui->renderer()->render(
            $this->ui->factory()->panel()->listing()->standard(
                $this->lng->txt("rep_recommended_content"),
            $this->getListItemGroups()
            )
        );
    }

    /**
     * Get items
     *
     * @return \ILIAS\UI\Component\Item\Group[]
     */
    protected function getListItemGroups(): array
    {
        global $DIC;
        $factory = $DIC->ui()->factory();

        $item_groups = [];
        $list_items = [];

        foreach ($this->recommendations as $ref_id) {
            try {
                $list_items[] = $this->getListItemForData($ref_id);

            } catch (ilException $e) {
                continue;
            }
        }

        $item_groups[] = $factory->item()->group("", $list_items);

        return $item_groups;
    }


    /**
     * @inheritdoc
     */
    protected function getListItemForData($ref_id): \ILIAS\UI\Component\Item\Item
    {
        $obj_id = ilObject::_lookupObjectId($ref_id);
        $type = ilObject::_lookupType($obj_id);
        $title = ilObject::_lookupTitle($obj_id);
        $desc = ilObject::_lookupDescription($obj_id);
        $item = [
            "ref_id" => $ref_id,
            "obj_id" => $obj_id,
            "type" => $type,
            "title" => $title,
            "description" => $desc,
        ];

        /** @var ilObjectListGUI $itemListGui */
        $itemListGui = $this->byType($type);
        ilObjectActivation::addListGUIActivationProperty($itemListGui, $item);

        $list_item = $itemListGui->getAsListItem(
            (int) $ref_id,
            (int) $obj_id,
            (string) $type,
            (string) $title,
            (string) $desc
        );

        return $list_item;
    }

    /**
     * @param string $a_type
     * @return ilObjectListGUI
     * @throws ilException
     */
    public function byType($a_type)
    {
        /** @var $item_list_gui ilObjectListGUI */
        if(!array_key_exists($a_type, self::$list_by_type))
        {
            $class = $this->objDefinition->getClassName($a_type);
            if(!$class)
            {
                throw new ilException(sprintf("Could not find a class for object type: %s", $a_type));
            }

            $location = $this->objDefinition->getLocation($a_type);
            if(!$location)
            {
                throw new ilException(sprintf("Could not find a class location for object type: %s", $a_type));
            }

            $full_class = 'ilObj' . $class . 'ListGUI';
            $item_list_gui = new $full_class();

            $item_list_gui->setContainerObject($this);
            $item_list_gui->enableNotes(false);
            $item_list_gui->enableComments(false);
            $item_list_gui->enableTags(false);

            $item_list_gui->enableIcon(true);
            $item_list_gui->enableDelete(false);
            $item_list_gui->enableCut(false);
            $item_list_gui->enableCopy(false);
            $item_list_gui->enableLink(false);
            $item_list_gui->enableInfoScreen(true);
            //$item_list_gui->enableSubscribe($this->block->getViewSettings()->enabledSelectedItems());

            $item_list_gui->enableCommands(true, true);

            self::$list_by_type[$a_type] = $item_list_gui;
        }

        return (clone self::$list_by_type[$a_type]);
    }

}