<?php

declare(strict_types=1);

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

namespace ILIAS\InfoScreen;

use ILIAS\Repository;
use ILIAS\HTTP\Services;
use ILIAS\Refinery\Factory;

class StandardGUIRequest
{
    use Repository\BaseGUIRequest;

    public function __construct(
        Services $http,
        Factory $refinery,
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

    public function getUserId(): int
    {
        return $this->int("user_id");
    }

    public function getLPEdit(): int
    {
        return $this->int("lp_edit");
    }
}
