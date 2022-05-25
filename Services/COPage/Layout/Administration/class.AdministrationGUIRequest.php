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

namespace ILIAS\COPage\Layout;

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

    public function getRefId() : int
    {
        return $this->int("ref_id");
    }

    public function getObjId() : int
    {
        return $this->int("obj_id");
    }

    public function getLayoutId() : int
    {
        return $this->int("layout_id");
    }

    public function getLayoutIds() : array
    {
        return $this->intArray("pglayout");
    }

    public function getLayoutTypes() : array
    {
        return $this->strArray("type");
    }

    public function getLayoutModules() : array
    {
        return $this->arrayArray("module");
    }
}
