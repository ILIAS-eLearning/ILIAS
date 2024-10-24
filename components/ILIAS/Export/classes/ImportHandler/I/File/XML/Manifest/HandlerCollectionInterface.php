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

namespace ILIAS\Export\ImportHandler\I\File\XML\Manifest;

use Countable;
use ILIAS\Export\ImportHandler\File\XML\Manifest\ExportObjectType;
use ILIAS\Export\ImportHandler\I\File\XML\Manifest\HandlerInterface as ManifestXMLFileHandlerInterface;
use ILIAS\Export\ImportStatus\I\ilCollectionInterface as ImportStatusHandlerCollectionInterface;
use Iterator;

interface HandlerCollectionInterface extends Iterator, Countable
{
    public function withMerged(HandlerCollectionInterface $other): HandlerCollectionInterface;

    public function withElement(ManifestXMLFileHandlerInterface $element): HandlerCollectionInterface;

    public function validateElements(): ImportStatusHandlerCollectionInterface;

    public function containsExportObjectType(ExportObjectType $type): bool;

    public function findNextFiles(): HandlerCollectionInterface;

    /**
     * @return ManifestXMLFileHandlerInterface[]
     */
    public function toArray(): array;

    public function current(): ManifestXMLFileHandlerInterface;

    public function next(): void;

    public function key(): int;

    public function valid(): bool;

    public function rewind(): void;
}
