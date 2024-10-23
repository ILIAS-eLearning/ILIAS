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

namespace ILIAS\Export\ExportHandler\PublicAccess\Link;

use ILIAS\Export\ExportHandler\I\FactoryInterface as ilExportHandlerFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Link\FactoryInterface as ilExportHandlerPublicAccessLinkFactoryInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Link\HandlerInterface as ilExportHandlerPublicAccessLinkInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Link\Wrapper\FactoryInterface as ilExportHandlerPublicAccessLinkWrapperFactoryInterface;
use ILIAS\Export\ExportHandler\PublicAccess\Link\Handler as ilExportHandlerPublicAccessLink;
use ILIAS\Export\ExportHandler\PublicAccess\Link\Wrapper\Factory as ilExportHandlerPublicAccessLinkWrapperFactory;
use ILIAS\StaticURL\Services as StaticUrl;

class Factory implements ilExportHandlerPublicAccessLinkFactoryInterface
{
    protected ilExportHandlerFactoryInterface $export_handler;
    protected StaticURL $static_url;

    public function __construct(
        ilExportHandlerFactoryInterface $export_handler,
        StaticURL $static_url
    ) {
        $this->export_handler = $export_handler;
        $this->static_url = $static_url;
    }

    public function handler(): ilExportHandlerPublicAccessLinkInterface
    {
        return (new ilExportHandlerPublicAccessLink())
            ->withStaticURLWrapper($this->wrapper()->staticURL()->handler());
    }

    public function wrapper(): ilExportHandlerPublicAccessLinkWrapperFactoryInterface
    {
        return new ilExportHandlerPublicAccessLinkWrapperFactory(
            $this->export_handler,
            $this->static_url
        );
    }
}
