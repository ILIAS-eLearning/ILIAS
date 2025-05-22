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

namespace ILIAS\Export\ExportHandler\I\Repository\Element;

use ILIAS\Export\ExportHandler\I\Repository\Element\Wrapper\IRSS\HandlerInterface as ilExportHandlerRepositoryElementIRSSWrapperInterface;
use ILIAS\Export\ExportHandler\I\Repository\Element\Wrapper\IRSSInfo\HandlerInterface as ilExportHandlerRepositoryElementIRSSInfoWrapperInterface;
use ILIAS\Export\ExportHandler\I\Repository\Key\HandlerInterface as ilExportHandlerRepositoryKeyInterface;
use ILIAS\Export\ExportHandler\I\Repository\Values\HandlerInterface as ilExportHandlerRepositoryValuesInterface;

interface HandlerInterface
{
    public const ELEMENT_TYPE = 'xml';

    public function withKey(
        ilExportHandlerRepositoryKeyInterface $key
    ): HandlerInterface;

    public function withValues(
        ilExportHandlerRepositoryValuesInterface $values
    ): HandlerInterface;

    public function getKey(): ilExportHandlerRepositoryKeyInterface;

    public function getValues(): ilExportHandlerRepositoryValuesInterface;

    public function getIRSSInfo(): ilExportHandlerRepositoryElementIRSSInfoWrapperInterface;

    public function getIRSS(): ilExportHandlerRepositoryElementIRSSWrapperInterface;

    public function getFileType(): string;

    public function isStorable(): bool;

    public function equals(
        HandlerInterface $other_element
    ): bool;
}
