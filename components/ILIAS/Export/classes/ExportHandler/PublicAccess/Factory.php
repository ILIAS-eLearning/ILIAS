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

namespace ILIAS\Export\ExportHandler\PublicAccess;

use ilDBInterface;
use ILIAS\Export\ExportHandler\I\FactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\FactoryInterface as ilExportHandlerPublicAccessFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\HandlerInterface as ilExportHandlerPublicAccessInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Link\FactoryInterface as ilExportHandlerPublicAccessLinkFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Repository\FactoryInterface as ilExportHandlerPublicAccessRepositoryFactoryInterface;
use ILIAS\Export\ExportHandler\PublicAccess\Handler as ilExportHandlerPublicAccess;
use ILIAS\Export\ExportHandler\PublicAccess\Link\Factory as ilExportHandlerPublicAccessLinkFactory;
use ILIAS\Export\ExportHandler\PublicAccess\Repository\Factory as ilExportHandlerPublicAccessRepositoryFactory;
use ILIAS\StaticURL\Services as StaticUrl;

class Factory implements ilExportHandlerPublicAccessFactoryInterface
{
    protected ilExportHandlerFactoryInterface $export_handler;
    protected ilDBInterface $db;
    protected StaticURL $static_url;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler,
        ilDBInterface $db,
        StaticURL $static_url
    ) {
        $this->export_handler = $export_handler;
        $this->db = $db;
        $this->static_url = $static_url;
    }

    public function handler(): ilExportHandlerPublicAccessInterface
    {
        return new ilExportHandlerPublicAccess(
            $this->export_handler->publicAccess()->repository()->handler(),
            $this->export_handler->publicAccess()->repository()->element(),
            $this->export_handler->publicAccess()->repository()->key(),
            $this->export_handler->publicAccess()->link(),
            $this->export_handler->publicAccess()->repository()->values()
        );
    }

    public function link(): ilExportHandlerPublicAccessLinkFactoryInterface
    {
        return new ilExportHandlerPublicAccessLinkFactory(
            $this->export_handler,
            $this->static_url
        );
    }

    public function repository(): ilExportHandlerPublicAccessRepositoryFactoryInterface
    {
        return new ilExportHandlerPublicAccessRepositoryFactory(
            $this->export_handler,
            $this->db
        );
    }
}
