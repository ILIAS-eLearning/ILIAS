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

    public function getBlockType(): string
    {
        return 'pdrecc';
    }

    protected function removeRecommendationObject(): void
    {
        $rec_manager = new ilRecommendedContentManager();
        $rec_manager->declineObjectRecommendation($this->user->getId(), $this->requested_item_ref_id);
        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt("dash_item_removed"), true);
        $this->ctrl->returnToParent($this);
    }

    public function addCustomCommandsToActionMenu(ilObjectListGUI $itemListGui, mixed $ref_id): void
    {
        $this->ctrl->setParameter($this, "item_ref_id", $ref_id);
        $itemListGui->addCustomCommand(
            $this->ctrl->getLinkTarget($this, "removeRecommendation"),
            "dash_remove_from_list"
        );
        $this->ctrl->clearParameterByClass(self::class, "item_ref_id");
    }
}
