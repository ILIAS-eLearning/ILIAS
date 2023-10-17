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

namespace ILIAS\MetaData\Paths\Services;

use ILIAS\MetaData\Paths\FactoryInterface;
use ILIAS\MetaData\Paths\Factory;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactoryInterface;
use ILIAS\MetaData\Paths\Navigator\NavigatorFactory;
use ILIAS\MetaData\Paths\Steps\NavigatorBridge;
use ILIAS\MetaData\Structure\Services\Services as StructureServices;

class Services
{
    protected FactoryInterface $path_factory;
    protected NavigatorFactoryInterface $navigator_factory;

    protected StructureServices $structure_services;

    public function __construct(
        StructureServices $structure_services
    ) {
        $this->structure_services = $structure_services;
    }

    public function pathFactory(): FactoryInterface
    {
        if (isset($this->path_factory)) {
            return $this->path_factory;
        }
        return $this->path_factory = new Factory(
            $this->structure_services->structure()
        );
    }

    public function navigatorFactory(): NavigatorFactoryInterface
    {
        if (isset($this->navigator_factory)) {
            return $this->navigator_factory;
        }
        return $this->navigator_factory = new NavigatorFactory(
            new NavigatorBridge()
        );
    }
}
