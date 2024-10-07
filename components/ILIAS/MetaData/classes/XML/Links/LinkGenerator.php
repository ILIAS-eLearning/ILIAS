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

namespace ILIAS\MetaData\XML\Copyright\Links;

use ILIAS\StaticURL\Services as URLService;
use ILIAS\Data\URI;
use ILIAS\Data\ReferenceId;
use ILIAS\Export\ExportHandler\Factory as ExportService;
use ILIAS\Data\Factory as DataFactory;

class LinkGenerator implements LinkGeneratorInterface
{
    protected URLService $url_service;
    protected ExportService $export_service;
    protected DataFactory $data_factory;

    public function __construct(
        URLService $url_service,
        ExportService $export_service,
        DataFactory $data_factory
    ) {
        $this->url_service = $url_service;
        $this->export_service = $export_service;
        $this->data_factory = $data_factory;
    }

    public function generateLinkForReference(
        int $ref_id,
        string $type
    ): URI {
        $ref_id = $this->data_factory->refId($ref_id);
        return $this->url_service->builder()->build($type, $ref_id);
    }

    public function doesReferenceHavePublicAccessExport(
        int $ref_id
    ): bool {
        $ref_id = $this->data_factory->refId($ref_id);
        return $this->export_service->publicAccess()->handler()->hasPublicAccessFile($ref_id->toObjectId());
    }

    public function generateLinkForPublicAccessExportOfReference(
        int $ref_id
    ): ?URI {
        if (!$this->doesReferenceHavePublicAccessExport($ref_id)) {
            return null;
        }
        $ref_id = $this->data_factory->refId($ref_id);
        return $this->data_factory->uri(
            $this->export_service->publicAccess()->handler()->downloadLinkOfPublicAccessFile($ref_id)
        );
    }
}
