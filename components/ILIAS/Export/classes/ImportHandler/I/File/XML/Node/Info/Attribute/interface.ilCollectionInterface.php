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

namespace ImportHandler\I\File\XML\Node\Info\Attribute;

use Iterator;
use Countable;
use ImportHandler\I\File\XML\Node\Info\Attribute\ilPairInterface as ilXMLFileNodeInfoAttributePairInterface;
use ImportHandler\I\File\XML\Node\Info\ilHandlerInterface as ilXMLFileNodeInfoInterface;

interface ilCollectionInterface extends Iterator, Countable
{
    public function matches(ilXMLFileNodeInfoInterface $node_info): bool;

    public function withElement(ilXMLFileNodeInfoAttributePairInterface $element): ilCollectionInterface;

    /**
     * @return ilXMLFileNodeInfoAttributePairInterface[]
     */
    public function toArray(): array;

    public function current(): ilXMLFileNodeInfoAttributePairInterface;

    public function next(): void;

    public function key(): int;

    public function valid(): bool;

    public function rewind(): void;
}
