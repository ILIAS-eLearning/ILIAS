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

use ILIAS\Services\Dashboard\Block\BlockDTO;

class ilDashboardRecommendedContentGUI extends ilDashboardBlockGUI
{
    public function __construct()
    {
        global $DIC;
        parent::__construct();
        $request = $DIC->repository()->internal()->gui()->standardRequest();
    }

    protected function removeRecommendationObject(): void
    {
        $rec_manager = new ilRecommendedContentManager();
        $rec_manager->declineObjectRecommendation($this->user->getId(), $this->requested_item_ref_id);
        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt("dash_item_removed"), true);
        $this->ctrl->returnToParent($this);
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

        $recommendations = array_map(static function (int $ref_id) use ($short_desc, $short_desc_max_length): BlockDTO {
            $obj_id = ilObject::_lookupObjectId($ref_id);
            $desc = ilObject::_lookupDescription($obj_id);
            if ($short_desc && $short_desc_max_length !== 0) {
                $desc = ilStr::shortenTextExtended($desc, $short_desc_max_length, true);
            }
            $obj = ilObjectFactory::getInstanceByRefId($ref_id);
            $start = null;
            $end = null;
            if ($obj) {
                switch (get_class($obj)) {
                    case ilObjGroup::class:
                        $start = new ilDateTime($obj->getStart());
                        $end = new ilDateTime($obj->getEnd());
                        break;
                    case ilObjCourse::class:
                        $start = new ilDateTime($obj->getActivationStart());
                        $end = new ilDateTime($obj->getActivationEnd());
                        break;
                    case ilObjTest::class:
                        $start = new ilDateTime($obj->getStartingTime());
                        $end = new ilDateTime($obj->getEndingTime());
                        break;
                }
            }
            return new BlockDTO(
                ilObject::_lookupType($obj_id),
                $ref_id,
                $obj_id,
                ilObject::_lookupTitle($obj_id),
                $desc,
                $start,
                $end,
            );
        }, $recommendations);

        $this->setData(['' => $recommendations]);
    }

    public function getBlockType(): string
    {
        return 'pdrecc';
    }

    public function addCustomCommandsToActionMenu(ilObjectListGUI $itemListGui, int $ref_id): void
    {
        $this->ctrl->setParameter($this, "item_ref_id", $ref_id);
        $itemListGui->addCustomCommand(
            $this->ctrl->getLinkTarget($this, "removeRecommendation"),
            "dash_remove_from_list"
        );
    }

    public function confirmedRemoveObject(): void
    {
        $rec_manager = new ilRecommendedContentManager();
        $refIds = (array) ($this->http->request()->getParsedBody()['ref_id'] ?? []);
        if ($refIds === []) {
            $this->ctrl->redirect($this, 'manage');
        }

        foreach ($refIds as $ref_id) {
            $rec_manager->declineObjectRecommendation($this->user->getId(), (int) $ref_id);
        }
        $this->main_tpl->setOnScreenMessage('success', $this->lng->txt('pd_remove_multi_confirm'), true);
        $this->ctrl->returnToParent($this);
    }

    public function removeMultipleEnabled(): bool
    {
        return true;
    }

    public function getRemoveMultipleActionText(): string
    {
        return $this->lng->txt('pd_remove_multiple');
    }
}
