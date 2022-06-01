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

namespace ILIAS\Repository\Administration;

use ILIAS\Repository;

class AdministrationGUIRequest
{
    use Repository\BaseGUIRequest;

    public function __construct(
        \ILIAS\HTTP\Services $http,
        \ILIAS\Refinery\Factory $refinery
    ) {
        $this->initRequest(
            $http,
            $refinery
        );
    }

    public function getNewItemGroupId() : int
    {
        return $this->int("grp_id");
    }

    /** @return int[] */
    public function getNewItemGroupIds() : array
    {
        return $this->intArray("grp_ids");
    }

    /** @return int[] */
    public function getNewItemPositions() : array
    {
        return $this->intArray("obj_pos");
    }

    /** @return int[] */
    public function getNewItemGroups() : array
    {
        return $this->intArray("obj_grp");
    }

    /** @return int[] */
    public function getNewItemEnablings() : array
    {
        return $this->intArray("obj_enbl_creation");
    }

    /** @return int[] */
    public function getNewItemGroupOrder() : array
    {
        return $this->intArray("grp_order");
    }
}
