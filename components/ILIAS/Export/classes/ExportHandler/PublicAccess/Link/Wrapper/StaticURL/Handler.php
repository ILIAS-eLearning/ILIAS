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

namespace ILIAS\Export\ExportHandler\PublicAccess\Link\Wrapper\StaticURL;

use ILIAS\Data\ReferenceId;
use ILIAS\Data\URI;
use ILIAS\Export\ExportHandler\I\PublicAccess\Link\Wrapper\StaticURL\HandlerInterface as ilExportHandlerPublicAccessLinkStaticURLWrapperInterface;
use ILIAS\Export\ExportHandler\StaticUrlHandler as ilExportHandlerStaticUrlHandler;
use ILIAS\StaticURL\Services as StaticUrl;

class Handler implements ilExportHandlerPublicAccessLinkStaticURLWrapperInterface
{
    protected StaticUrl $static_url;

    public function withStaticURL(
        StaticUrl $static_url
    ): ilExportHandlerPublicAccessLinkStaticURLWrapperInterface {
        $clone = clone $this;
        $clone->static_url = $static_url;
        return $clone;
    }

    public function getStatucURL(): StaticUrl
    {
        return $this->static_url;
    }

    public function buildDownloadURI(
        ReferenceId $reference_id
    ): URI {
        return $this->static_url->builder()->build(
            ilExportHandlerStaticUrlHandler::NAMESPACE,
            $reference_id,
            [ilExportHandlerStaticUrlHandler::DOWNLOAD]
        );
    }
}
