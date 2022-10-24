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
 * Get list item properties for booking info
 * @author Alexander Killing <killing@leifos.de>
 */
class ilBookingInfoListItemPropertiesAdapter
{
    protected ilBookingReservationDBRepository $repo;

    public function __construct(
        ilBookingReservationDBRepository $repo = null
    ) {
        $this->repo = $repo;
    }

    public function appendProperties(
        int $obj_id,
        array $props
    ): array {
        $repo = $this->repo;
        $info = [];
        foreach ($repo->getCachedContextObjBookingInfo($obj_id) as $item) {
            $info[$item["pool_id"]]["title"] = ilObject::_lookupTitle($item["pool_id"]);
            $info[$item["pool_id"]]["object"][$item["object_id"]]["title"] = $item["title"];
            $info[$item["pool_id"]]["object"][$item["object_id"]]["bookings"][] =
                ilDatePresentation::formatDate(new ilDate($item["date"], IL_CAL_DATE)) . ", " . $item["slot"] . " (" . $item["counter"] . ")";
        }
        foreach ($info as $pool) {
            $val = "";
            foreach ($pool["object"] as $o) {
                $val .= $o["title"] . ": " . implode(", ", $o["bookings"]);
            }
            $props[] = array("alert" => false, "property" => $pool["title"], "value" => $val);
        }
        return $props;
    }
}
