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

use ILIAS\UI\Component\Item\Group;
use ILIAS\UI\Component\Item\Item;

/**
 * Dashboard recommended content UI
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilDashboardRecommendedContentGUI
{
    protected ilObjUser $user;
    protected ilRecommendedContentManager $rec_manager;
    /** @var int[] */
    protected array $recommendations;
    public static array $list_by_type = [];
    protected \ILIAS\DI\UIServices $ui;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;
    protected ilSetting $settings;
    protected ilFavouritesManager $fav_manager;
    protected ilObjectDefinition $objDefinition;
    protected int $requested_item_ref_id;
    private ilGlobalTemplateInterface $main_tpl;

    public function __construct()
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $this->user = $DIC->user();
        $this->rec_manager = new ilRecommendedContentManager();
        $this->fav_manager = new ilFavouritesManager();
        $this->objDefinition = $DIC["objDefinition"];
        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->settings = $DIC->settings();

        $this->lng->loadLanguageModule("rep");

        $request = $DIC->repository()->internal()->gui()->standardRequest();
        $this->requested_item_ref_id = $request->getItemRefId();

        $this->recommendations = $this->rec_manager->getOpenRecommendationsOfUser($this->user->getId());
    }

    public function executeCommand() : void
    {
        $ctrl = $this->ctrl;

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd();

        switch ($next_class) {
            default:
                if (in_array($cmd, ["remove", "makeFavourite"])) {
                    $this->$cmd();
                }
        }
    }

    public function render() : string
    {
        if (count($this->recommendations) === 0) {
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
     * @return Group[]
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

    protected function getListItemForData(int $ref_id) : ?Item
    {
        $short_desc = $this->settings->get("rep_shorten_description");
        $short_desc_max_length = (int) $this->settings->get("rep_shorten_description_length");
        $ctrl = $this->ctrl;

        $obj_id = ilObject::_lookupObjectId($ref_id);
        $type = ilObject::_lookupType($obj_id);
        $title = ilObject::_lookupTitle($obj_id);
        $desc = ilObject::_lookupDescription($obj_id);
        if ($short_desc && $short_desc_max_length !== 0) {
            $desc = ilStr::shortenTextExtended($desc, $short_desc_max_length, true);
        }
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
            $ref_id,
            $obj_id,
            $type,
            $title,
            $desc
        );

        return $list_item;
    }

    /**
     * @throws ilException
     */
    public function byType(string $a_type) : ilObjectListGUI
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

    protected function remove() : void
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $this->rec_manager->declineObjectRecommendation($this->user->getId(), $this->requested_item_ref_id);
        $this->main_tpl->setOnScreenMessage('success', $lng->txt("dash_item_removed"), true);
        $ctrl->returnToParent($this);
    }

    protected function makeFavourite() : void
    {
        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $this->fav_manager->add($this->user->getId(), $this->requested_item_ref_id);
        $this->main_tpl->setOnScreenMessage('success', $lng->txt("dash_added_to_favs"), true);
        $ctrl->returnToParent($this);
    }
}
