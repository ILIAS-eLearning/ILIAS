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

namespace ILIAS\SystemCheck\Table\Group;

use ilCtrl;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\DI\UIServices;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\SystemCheck\I\Table\Group\DataRetrievalInterface;
use ILIAS\SystemCheck\I\Table\Group\FactoryInterface;
use ILIAS\SystemCheck\I\Table\Group\HandlerInterface;
use ilLanguage;

readonly class Factory implements FactoryInterface
{
    public function __construct(
        protected DataFactory $data_factory,
        protected UIServices $ui,
        protected ilLanguage $lng,
        protected HTTPServices $http,
        protected ilCtrl $ctrl
    ) {
    }

    final public function handler(
        DataRetrievalInterface|null $data_retrieval = null
    ): HandlerInterface {
        return new Handler(
            $this->data_factory,
            $this->ui,
            $this->lng,
            $this->http,
            $data_retrieval ?? $this->dataRetrieval(),
        );
    }

    final public function dataRetrieval(): DataRetrievalInterface
    {
        return new DataRetrieval(
            $this->ui,
            $this->ctrl
        );
    }
}
