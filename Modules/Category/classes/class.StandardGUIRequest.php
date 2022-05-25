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

namespace ILIAS\Category;

use ILIAS\Repository;

class StandardGUIRequest
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

    public function getCmdClass() : string
    {
        return $this->str("cmdClass");
    }

    public function getBaseClass() : string
    {
        return $this->str("baseClass");
    }

    /** @return int[] */
    public function getUserIds() : array
    {
        return $this->intArray("user_ids");
    }

    /** @return int[] */
    public function getIds() : array
    {
        return $this->intArray("id");
    }

    /** @return int[] */
    public function getRoleIds() : array
    {
        return $this->intArray("role_ids");
    }

    public function getFetchAll() : int
    {
        return $this->int("fetchall");
    }

    public function getTerm() : string
    {
        return $this->str("term");
    }
}
