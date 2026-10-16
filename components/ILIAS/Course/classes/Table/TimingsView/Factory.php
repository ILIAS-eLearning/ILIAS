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

namespace ILIAS\Course\Table\TimingsView;

use ILIAS\Course\I\Table\TimingsView\DataRetrievalInterface;
use ILIAS\Course\I\Table\TimingsView\FactoryInterface;
use ILIAS\Course\I\Table\TimingsView\HandlerInterface;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\DI\UIServices;
use ILIAS\HTTP\Services as HTTPServices;
use ilLanguage;
use ilObjUser;

readonly class Factory implements FactoryInterface
{
    public function __construct(
        protected ilObjUser $user,
        protected UIServices $ui,
        protected HTTPServices $http,
        protected ilLanguage $lng,
    ) {
    }

    public function handler(
        DataRetrievalInterface $data_retrieval
    ): HandlerInterface {
        return new Handler(
            $this->ui,
            $this->http,
            $data_retrieval,
            $this->lng
        );
    }

    public function dataRetrieval(
        int $user_id,
        int $parent_object_ref_id
    ): DataRetrievalInterface {
        return new DataRetrieval(
            $this->ui,
            $this->user,
            $user_id,
            $parent_object_ref_id
        );
    }
}
