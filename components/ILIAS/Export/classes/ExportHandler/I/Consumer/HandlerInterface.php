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

namespace ILIAS\Export\ExportHandler\I\Consumer;

use ILIAS\Data\ObjectId;
use ILIAS\Export\ExportHandler\I\Consumer\ExportWriter\HandlerInterface as ilExportHandlerConsumerExportWriterInterface;
use ILIAS\Export\ExportHandler\I\PublicAccess\HandlerInterface as ilExportHandlerPublicAccessInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\HandlerInterface as ilExportHandlerRepositoryElementInterface;

interface HandlerInterface
{
    public function publicAccess(): ilExportHandlerPublicAccessInterface;

    public function createStandardExport(
        int $user_id,
        ObjectId $object_id
    ): ilExportHandlerRepositoryElementInterface;

    public function exportWriter(
        ilExportHandlerRepositoryElementInterface $element
    ): ilExportHandlerConsumerExportWriterInterface;
}
