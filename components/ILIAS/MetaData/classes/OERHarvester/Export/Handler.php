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

namespace ILIAS\MetaData\OERHarvester\Export;

use ILIAS\Export\ExportHandler\Factory as ExportService;
use ILIAS\Data\Factory as DataFactory;

class Handler implements HandlerInterface
{
    protected \ilObjUser $user;
    protected ExportService $export_service;
    protected DataFactory $data_factory;

    public function __construct(
        \ilObjUser $user,
        ExportService $export_service,
        DataFactory $data_factory
    ) {
        $this->export_service = $export_service;
        $this->data_factory = $data_factory;
        $this->user = $user;
    }

    public function hasPublicAccessExport(int $obj_id): bool
    {
        $obj_id = $this->data_factory->objId($obj_id);
        return $this->export_service->publicAccess()->handler()->hasPublicAccessFile($obj_id);
    }

    public function createPublicAccessExport(int $obj_id): void
    {
        if ($this->hasPublicAccessExport($obj_id)) {
            return;
        }
        $obj_id = $this->data_factory->objId($obj_id);
        $export_result = $this->export_service->consumer()->handler()->createStandardExport(
            $this->user->getId(),
            $obj_id
        );
        $this->export_service->consumer()->handler()->publicAccess()->setPublicAccessFile(
            $obj_id,
            "expxml",
            $export_result->getIRSS()->getResourceIdSerialized()
        );
    }
}
