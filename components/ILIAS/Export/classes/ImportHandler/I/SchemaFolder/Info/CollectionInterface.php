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

namespace ILIAS\Export\ImportHandler\I\SchemaFolder\Info;

use Countable;
use ILIAS\Data\Version;
use ILIAS\Export\ImportHandler\I\SchemaFolder\Info\HandlerInterface as SchemaInfoInterface;
use Iterator;

interface CollectionInterface extends Iterator, Countable
{
    public function withElement(
        SchemaInfoInterface $element
    ): CollectionInterface;

    public function getLatest(
        string $component,
        string $sub_type = ''
    ): SchemaInfoInterface|null;

    public function getByVersion(
        Version $version,
        string $type,
        string $sub_type = ''
    ): SchemaInfoInterface|null;

    public function getByVersionOrLatest(
        Version $version,
        string $type,
        string $sub_type = ''
    ): SchemaInfoInterface|null;

    public function next(): void;

    public function rewind(): void;

    public function valid(): bool;

    public function key(): int;

    public function current(): SchemaInfoInterface;

    public function count(): int;
}
