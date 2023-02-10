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

class ilRecommendedContentBlockGUI extends ilDashboardBlockGUI
{
    private int $requested_item_ref_id;

    public function __construct()
    {
        global $DIC;
        parent::__construct();
        $request = $DIC->repository()->internal()->gui()->standardRequest();
        $this->requested_item_ref_id = $request->getItemRefId();
    }

    public function initViewSettings(): void
    {
        $this->viewSettings = new ilPDSelectedItemsBlockViewSettings(
            $this->user,
            ilPDSelectedItemsBlockConstants::VIEW_RECOMMENDED_CONTENT
        );

        $this->ctrl->setParameter($this, 'view', $this->viewSettings->getCurrentView());
    }

    public function emptyHandling(): string
    {
        return '';
    }

    public function initData(): void
    {
        $rec_manager = new ilRecommendedContentManager();
        $recommendations = $rec_manager->getOpenRecommendationsOfUser($this->user->getId());

        $short_desc = $this->settings->get("rep_shorten_description");
        $short_desc_max_length = (int) $this->settings->get("rep_shorten_description_length");
        $ctrl = $this->ctrl;

        $recommendations = array_map(static function ($ref_id) use ($short_desc, $short_desc_max_length) {
            $obj_id = ilObject::_lookupObjectId($ref_id);
            $desc = ilObject::_lookupDescription($obj_id);
            if ($short_desc && $short_desc_max_length !== 0) {
                $desc = ilStr::shortenTextExtended($desc, $short_desc_max_length, true);
            }
            $obj = ilObjectFactory::getInstanceByRefId($ref_id);

            return [
               'title' => ilObject::_lookupTitle($obj_id),
               'description' => $desc,
               'ref_id' => $ref_id,
               'obj_id' => $obj_id,
               'url' => '',
               'obj' => $obj,
               'type' => ilObject::_lookupType($obj_id),
               'start' => null,
                'end' => null,
            ];
        }, $recommendations);

        $this->setData(['' => $recommendations]);
    }

    public function getItemForData(array $data): ?\ILIAS\UI\Component\Item\Item
    {
        $item = $data;
        $item_gui = $this->byType($data['type']);
        ilObjectActivation::addListGUIActivationProperty($item_gui, $item);

        $this->ctrl->setParameterByClass(get_class($this), "item_ref_id", $data['ref_id']);

        $item_gui->addCustomCommand(
            $this->ctrl->getLinkTarget($this, "remove"),
            "dash_remove_from_list"
        );

        $this->ctrl->clearParameterByClass(self::class, "item_ref_id");


        $list_item = $item_gui->getAsListItem(
            $data['ref_id'],
            $data['obj_id'],
            $data['type'],
            $data['title'],
            $data['description'],
        );

        return $list_item;
    }

    public function getCardForData(array $data): ?\ILIAS\UI\Component\Card\RepositoryObject
    {
        return $this->byType($data['type'])->getAsCard(
            $data['ref_id'],
            $data['obj_id'],
            $data['type'],
            $data['title'],
            $data['description'],
        );
    }

    public function getBlockType(): string
    {
        return 'pdrecc';
    }

    protected function makeFavourite(): void
    {
        $fav_manager = new ilFavouritesManager();
        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $fav_manager->add($this->user->getId(), $this->requested_item_ref_id);
        $this->main_tpl->setOnScreenMessage('success', $lng->txt("dash_added_to_favs"), true);
        $ctrl->returnToParent($this);
    }

    protected function remove(): void
    {
        $rec_manager = new ilRecommendedContentManager();
        $ctrl = $this->ctrl;
        $lng = $this->lng;
        $rec_manager->declineObjectRecommendation($this->user->getId(), $this->requested_item_ref_id);
        $this->main_tpl->setOnScreenMessage('success', $lng->txt("dash_item_removed"), true);
        $ctrl->returnToParent($this);
    }
}
