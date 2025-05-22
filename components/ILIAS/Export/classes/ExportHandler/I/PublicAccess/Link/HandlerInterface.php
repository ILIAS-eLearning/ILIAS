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

namespace ILIAS\Export\ExportHandler\I\PublicAccess\Link;

use ILIAS\Data\ReferenceId;
use ILIAS\Data\URI;
use ILIAS\Export\ExportHandler\I\PublicAccess\Link\HandlerInterface as ilExportHandlerPublicAccessLinkHandlerInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\Link\Wrapper\StaticURL\HandlerInterface as ilExportHandlerPublicAccessLinkStaticURLWrapperInterface;

interface HandlerInterface
{
    public function withReferenceId(ReferenceId $referenceId): HandlerInterface;

    public function withStaticURLWrapper(
        ilExportHandlerPublicAccessLinkStaticURLWrapperInterface $static_url_wrapper
    ): ilExportHandlerPublicAccessLinkHandlerInterface;

    public function getReferenceId(): ReferenceId;

    public function getStaticURLWrapper(): ilExportHandlerPublicAccessLinkStaticURLWrapperInterface;

    public function getLink(): URI;
}
