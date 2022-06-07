<?php declare(strict_types=1);

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

namespace ILIAS\Administration;

use ILIAS\Repository;

class AdminGUIRequest
{
    use Repository\BaseGUIRequest;

    public function __construct(
        \ILIAS\HTTP\Services $http,
        \ILIAS\Refinery\Factory $refinery,
        ?array $passed_query_params = null,
        ?array $passed_post_data = null
    ) {
        $this->initRequest(
            $http,
            $refinery,
            $passed_query_params,
            $passed_post_data
        );
    }

    public function getRefId() : int
    {
        return $this->int("ref_id");
    }

    public function getObjId() : int
    {
        return $this->int("obj_id");
    }

    public function getItemRefId() : int
    {
        return $this->int("item_ref_id");
    }

    public function getAdminMode() : string
    {
        return $this->str("admin_mode");
    }

    public function getCType() : string
    {
        return $this->str("ctype");
    }

    public function getCName() : string
    {
        return $this->str("cname");
    }

    public function getSlotId() : string
    {
        return $this->str("slot_id");
    }

    public function getPluginId() : string
    {
        return $this->str("plugin_id");
    }

    public function getJumpToUserId() : int
    {
        return $this->int("jmpToUser");
    }

    public function getNewType() : string
    {
        return $this->str("new_type");
    }

    // @return int[]
    public function getSelectedIds() : array
    {
        $ids = $this->intArray("id");
        if (count($ids) === 0) {
            if ($this->getItemRefId() > 0) {
                return [$this->getItemRefId()];
            }
        }
        return $ids;
    }
}
