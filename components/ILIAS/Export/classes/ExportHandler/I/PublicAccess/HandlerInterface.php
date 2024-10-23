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

namespace ILIAS\Export\ExportHandler\I\PublicAccess;

use ILIAS\Data\ObjectId;
use ILIAS\Data\ReferenceId;

interface HandlerInterface
{
    public function setPublicAccessFile(
        ObjectId $object_id,
        string $export_option_id,
        string $file_identifier
    ): void;

    public function hasPublicAccessFile(
        ObjectId $object_id
    ): bool;

    public function removePublicAccessFile(
        ObjectId $object_id
    ): void;

    public function getPublicAccessFileIdentifier(
        ObjectId $object_id
    ): string;

    public function getPublicAccessFileExportOptionId(
        ObjectId $object_id
    ): string;

    public function downloadLinkOfPublicAccessFile(
        ReferenceId $reference_id
    ): string;
}
