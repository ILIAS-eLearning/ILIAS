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
 * Embeds booking information into info screen
 * @author Alexander Killing <killing@leifos.de>
 */
class ilBookingInfoScreenAdapter
{
    protected ilInfoScreenGUI $info_screen_gui;
    protected ?int $context_obj_id;
    protected ilObjUseBookDBRepository $use_book_repo;

    public function __construct(
        ilInfoScreenGUI $info_screen_gui
    ) {
        global $DIC;
        $this->info_screen_gui = $info_screen_gui;
        $this->context_obj_id = $this->info_screen_gui->getContextObjId();

        $this->use_book_repo = new ilObjUseBookDBRepository($DIC->database());
    }

    /**
     * Get pool ids
     * @return int[]
     */
    protected function getPoolIds() : array
    {
        return array_map(static function ($ref_id) {
            return ilObject::_lookupObjId($ref_id);
        }, $this->use_book_repo->getUsedBookingPools($this->context_obj_id));
    }

    /**
     * Get reservation list
     * @return array[]
     */
    protected function getList() : array
    {
        $filter = ["context_obj_ids" => [$this->context_obj_id]];
        $filter['past'] = true;
        $filter['status'] = -ilBookingReservation::STATUS_CANCELLED;
        $f = new ilBookingReservationDBRepositoryFactory();
        $repo = $f->getRepo();
        $list = $repo->getListByDate(true, null, $filter, $this->getPoolIds());
        $list = ilArrayUtil::sortArray($list, "slot", "asc", true);
        $list = ilArrayUtil::stableSortArray($list, "date", "asc", true);
        $list = ilArrayUtil::stableSortArray($list, "pool_id", "asc", true);
        return $list;
    }


    /**
     * Add info to info screen
     */
    public function add() : void
    {
        $info = $this->info_screen_gui;
        $current_pool_id = 0;

        foreach ($this->getList() as $item) {
            // headings (pool title)
            if ($current_pool_id != $item["pool_id"]) {
                $info->addSection(ilObject::_lookupTitle($item["pool_id"]));
            }
            // booking object
            $info->addProperty(
                $item["title"] . " (" . $item["counter"] . ")",
                ilDatePresentation::formatDate(new ilDate($item["date"], IL_CAL_DATE)) . ", " . $item["slot"]
            );
            $current_pool_id = $item["pool_id"];
        }
    }
}
