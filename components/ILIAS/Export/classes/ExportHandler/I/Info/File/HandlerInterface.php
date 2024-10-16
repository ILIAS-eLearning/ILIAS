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

namespace ILIAS\Export\ExportHandler\I\Info\File;

use DateTimeImmutable;
use ILIAS\Export\ExportHandler\I\Consumer\ExportOption\HandlerInterface as ilExportHandlerConsumerExportOptionInterface;
use ILIAS\ResourceStorage\Identification\ResourceIdentification;
use SplFileInfo;

interface HandlerInterface
{
    public function withPublicAccessPossible(bool $enabled): HandlerInterface;

    public function withPublicAccessEnabled(bool $enabled): HandlerInterface;

    public function withResourceId(ResourceIdentification $resource_id): HandlerInterface;

    public function withType(string $type): HandlerInterface;

    public function withSplFileInfo(SplFileInfo $splFileInfo): HandlerInterface;

    public function withExportOption(ilExportHandlerConsumerExportOptionInterface $export_option): HandlerInterface;

    public function getExportOption(): ilExportHandlerConsumerExportOptionInterface;

    public function getPublicAccessPossible(): bool;

    public function getPublicAccessEnabled(): bool;

    public function getFileSize(): int;

    public function getFileName(): string;

    public function getFileType(): string;

    public function getDownloadInfo(): string;

    public function getLastChanged(): DateTimeImmutable;

    public function getLastChangedTimestamp(): int;

    public function getFileIdentifier(): string;
}
