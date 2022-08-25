<?php

declare(strict_types=1);

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

/**
 * Class ilHtmlDomNodeIterator
 * @author Michael Jansen <mjansen@databay.de>
 */
final class ilHtmlDomNodeIterator implements RecursiveIterator
{
    private int $position;
    private DOMNodeList $nodeList;

    public function __construct(DOMNode $el)
    {
        $this->position = 0;
        if ($el instanceof DOMDocument) {
            $root = $el->documentElement;
        } elseif ($el instanceof DOMElement) {
            $root = $el;
        } else {
            throw new InvalidArgumentException('Invalid arguments, expected DOMElement or DOMDocument');
        }

        $this->nodeList = $root->childNodes;
    }

    public function key(): int
    {
        return $this->position;
    }

    public function next(): void
    {
        $this->position++;
    }

    public function current(): DOMNode
    {
        return $this->nodeList->item($this->position);
    }

    public function valid(): bool
    {
        return $this->position < $this->nodeList->length;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function hasChildren(): bool
    {
        return $this->current()->hasChildNodes();
    }

    public function getChildren(): self
    {
        return new self($this->current());
    }
}
