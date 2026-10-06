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

namespace ILIAS\SystemCheck\Table\Task;

use ilCtrl;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\DI\UIServices;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\SystemCheck\I\Table\Task\DataRetrievalInterface;
use ILIAS\SystemCheck\I\Table\Task\FactoryInterface;
use ILIAS\SystemCheck\I\Table\Task\HandlerInterface;
use ilLanguage;

readonly class Factory implements FactoryInterface
{
    public function __construct(
        protected DataFactory $data_factory,
        protected UIServices $ui,
        protected ilLanguage $lng,
        protected HTTPServices $http,
        protected RefineryFactory $refinery,
        protected ilCtrl $ctrl
    ) {
    }

    final public function handler(
        DataRetrievalInterface $data_retrieval,
        bool $actions_permitted = false
    ): HandlerInterface {
        return new Handler(
            $this->data_factory,
            $this->ui,
            $this->lng,
            $this->http,
            $data_retrieval,
            $this->refinery,
            $this->ctrl,
            $actions_permitted
        );
    }

    final public function dataRetrieval(
        int $group_id
    ): DataRetrievalInterface {
        return new DataRetrieval($group_id);
    }
}
