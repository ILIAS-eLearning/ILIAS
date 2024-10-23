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

namespace ILIAS\Export\ExportHandler\Wrapper;

use ILIAS\Data\Factory as ilDataFactory;
use ILIAS\Export\ExportHandler\I\Wrapper\DataFactory\FactoryInterface as ilExportHandlerDataFactoryWrapperFactoryInterface;
use ILIAS\Export\ExportHandler\I\Wrapper\FactoryInterface as ilExportHandlerWrapperFactoryInterface;
use ILIAS\Export\ExportHandler\Wrapper\DataFactory\Factory as ilExportHandlerDataFactoryWrapperFactory;

class Factory implements ilExportHandlerWrapperFactoryInterface
{
    protected ilDataFactory $data_factory;

    public function __construct(
        ilDataFactory $data_factory
    ) {
        $this->data_factory = $data_factory;
    }

    public function dataFactory(): ilExportHandlerDataFactoryWrapperFactoryInterface
    {
        return new ilExportHandlerDataFactoryWrapperFactory(
            $this->data_factory
        );
    }
}
