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

namespace ILIAS\Session\Overview\Table;

use ilAccess;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\DI\UIServices;
use ILIAS\HTTP\Services as HTTPServices;
use ilLanguage;
use ilObjUser;
use ilTree;

class Factory
{
    public function __construct(
        protected readonly DataFactory $data_factory,
        protected readonly UIServices $ui,
        protected readonly ilLanguage $lng,
        protected readonly HTTPServices $http,
        protected readonly ilTree $tree,
        protected readonly ilAccess $access,
    ) {
    }

    public function handler(
        DataRetrieval $data_retrieval
    ): Handler {
        return new Handler(
            $this->data_factory,
            $this->ui,
            $this->lng,
            $this->http,
            $data_retrieval
        );
    }

    public function dataRetrieval(
        int $crs_ref_id,
        int ...$session_member_ids
    ): DataRetrieval {
        return new DataRetrieval(
            $this->ui,
            $this->tree,
            $this->access,
            $crs_ref_id,
            ...$session_member_ids
        );
    }
}
