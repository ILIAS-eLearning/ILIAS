<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilHtmlDomNodeIterator
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilHtmlDomNodeIterator implements RecursiveIterator
{
    protected int $position = 0;
    protected DOMNodeList $nodeList;

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

    public function key() : int
    {
        return $this->position;
    }

    public function next() : void
    {
        $this->position++;
    }

    public function current() : DOMNode
    {
        return $this->nodeList->item($this->position);
    }

    public function valid() : bool
    {
        return $this->position < $this->nodeList->length;
    }

    public function rewind() : void
    {
        $this->position = 0;
    }

    public function hasChildren() : bool
    {
        return $this->current()->hasChildNodes();
    }

    public function getChildren() : self
    {
        return new self($this->current());
    }
}
