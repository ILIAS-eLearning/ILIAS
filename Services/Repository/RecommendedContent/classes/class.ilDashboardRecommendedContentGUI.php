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
    public static $list_by_type = [];


    /**
     * @var \ILIAS\DI\UIServices
     */
    protected $ui;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var \ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilFavouritesManager
     */
    protected $fav_manager;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->rec_manager = new ilRecommendedContentManager();
        $this->fav_manager = new ilFavouritesManager();
        $this->objDefinition = $DIC["objDefinition"];
        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();

        $this->lng->loadLanguageModule("rep");

        $this->requested_item_ref_id = (int) $_GET["item_ref_id"];

        $this->recommendations = $this->rec_manager->getOpenRecommendationsOfUser($this->user->getId());
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd();

        switch ($next_class) {
            default:
                if (in_array($cmd, array("remove", "makeFavourite"))) {
                    $this->$cmd();
                }
        }
    }

    /**
     * Render
     *
     * @return string
     */
    public function render()
    {
        if (count($this->recommendations) == 0) {
            return "";
        }
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
    protected function getListItemGroups() : array
    {
        global $DIC;
        $factory = $DIC->ui()->factory();

        $item_groups = [];
        $list_items = [];

        foreach ($this->recommendations as $ref_id) {
            try {
                if (!$DIC->access()->checkAccess('visible', '', $ref_id)) {
                    continue;
                }
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
    protected function getListItemForData($ref_id) : \ILIAS\UI\Component\Item\Item
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;

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
        $item_gui = $this->byType($type);
        ilObjectActivation::addListGUIActivationProperty($item_gui, $item);

        $ctrl->setParameter($this, "item_ref_id", $ref_id);

        $item_gui->addCustomCommand(
            $ctrl->getLinkTarget($this, "remove"),
            "dash_remove_from_list"
        );

        $item_gui->addCustomCommand(
            $ctrl->getLinkTarget($this, "makeFavourite"),
            "dash_make_favourite"
        );

        $ctrl->clearParameterByClass(self::class, "item_ref_id");


        $list_item = $item_gui->getAsListItem(
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

    /**
     * Remove from list
     */
    protected function remove()
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $this->rec_manager->declineObjectRecommendation($this->user->getId(), $this->requested_item_ref_id);
        ilUtil::sendSuccess($lng->txt("dash_item_removed"), true);
        $ctrl->returnToParent($this);
    }


    /**
     * Make favourite
     */
    protected function makeFavourite()
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $this->fav_manager->add($this->user->getId(), $this->requested_item_ref_id);
        ilUtil::sendSuccess($lng->txt("dash_added_to_favs"), true);
        $ctrl->returnToParent($this);
    }
}
