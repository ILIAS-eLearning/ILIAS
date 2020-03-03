<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilHtmlDomNodeIterator
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilHtmlDomNodeIterator implements \RecursiveIterator
{
    /** @var Integer */
    protected $position = 0;

    /** @var \DOMNodeList */
    protected $nodeList;

    /**
     * ilHtmlDomNodeIterator constructor.
     * @param \DOMNode $el
     */
    public function __construct(\DOMNode $el)
    {
        $this->position = 0;
        if ($el instanceof \DOMDocument) {
            $root = $el->documentElement;
        } else {
            if ($el instanceof \DOMElement) {
                $root = $el;
            } else {
                throw new \InvalidArgumentException("Invalid arguments, expected DOMElement or DOMDocument");
            }
        }

        $this->nodeList = $root->childNodes;
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * @inheritdoc
     * @return \DOMNode
     */
    public function current()
    {
        return $this->nodeList->item($this->position);
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        return $this->position < $this->nodeList->length;
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * @inheritdoc
     */
    public function hasChildren()
    {
        return $this->current()->hasChildNodes();
    }

    /**
     * @inheritdoc
     */
    public function getChildren()
    {
        return new self($this->current());
    }
}
