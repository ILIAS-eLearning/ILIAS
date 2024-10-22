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

namespace ILIAS\Export\ExportHandler\Wrapper\DataFactory;

use ILIAS\Data\Factory as ilDataFactory;
use ILIAS\Data\ObjectId;
use ILIAS\Export\ExportHandler\I\Wrapper\DataFactory\HandlerInterface as ilExportHandlerDataFactoryWrapperInterface;

class Handler implements ilExportHandlerDataFactoryWrapperInterface
{
    protected ilDataFactory $data_factory;

    public function __construct(
        ilDataFactory $data_factory
    ) {
        $this->data_factory = $data_factory;
    }

    public function objId(int $object_id): ObjectId
    {
        return $this->data_factory->objId($object_id);
    }
}
