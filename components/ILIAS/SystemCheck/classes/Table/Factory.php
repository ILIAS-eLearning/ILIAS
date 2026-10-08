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

namespace ILIAS\SystemCheck\Table;

use ilCtrl;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\DI\UIServices;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\SystemCheck\I\Table\FactoryInterface;
use ILIAS\SystemCheck\I\Table\Group\FactoryInterface as GroupFactoryInterface;
use ILIAS\SystemCheck\I\Table\Task\FactoryInterface as TaskFactoryInterface;
use ILIAS\SystemCheck\Table\Group\Factory as GroupFactory;
use ILIAS\SystemCheck\Table\Task\Factory as TaskFactory;
use ilLanguage;

readonly class Factory implements FactoryInterface
{
    protected DataFactory $data_factory;
    protected UIServices $ui;
    protected ilLanguage $lng;
    protected HTTPServices $http;
    protected ilCtrl $ctrl;
    protected RefineryFactory $refinery;

    public function __construct()
    {
        global $DIC;
        $this->data_factory = new DataFactory();
        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
        $this->http = $DIC->http();
        $this->ctrl = $DIC->ctrl();
        $this->refinery = $DIC->refinery();
    }

    final public function group(): GroupFactoryInterface
    {
        return new GroupFactory(
            $this->data_factory,
            $this->ui,
            $this->lng,
            $this->http,
            $this->ctrl
        );
    }

    final public function task(): TaskFactoryInterface
    {
        return new TaskFactory(
            $this->data_factory,
            $this->ui,
            $this->lng,
            $this->http,
            $this->refinery,
            $this->ctrl
        );
    }
}
