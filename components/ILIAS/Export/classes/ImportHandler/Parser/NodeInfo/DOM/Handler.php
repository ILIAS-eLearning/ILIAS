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

namespace ILIAS\Export\ImportHandler\Parser\NodeInfo\DOM;

use DOMAttr;
use DOMNode;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\CollectionInterface as ilImportHandlerParserNodeInfoCollectionInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\DOM\HandlerInterface as ilImportHandlerParserNodeInfoilDOMNodeInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\FactoryInterface as ilImportHandlerParserNodeInfoFactoryInterface;
use ilImportException;

class Handler implements ilImportHandlerParserNodeInfoilDOMNodeInterface
{
    /**
     * @var array<string, string>
     */
    protected array $attributes;
    protected DOMNode $node;
    protected ilImportHandlerParserNodeInfoFactoryInterface $info;

    public function __construct(
        ilImportHandlerParserNodeInfoFactoryInterface $info
    ) {
        $this->attributes = [];
        $this->info = $info;
    }

    protected function initAttributes()
    {
        if (is_null($this->node->attributes)) {
            return;
        }
        /** @var DOMAttr $attribute **/
        foreach ($this->node->attributes as $attribute) {
            $this->attributes[$attribute->name] = $attribute->value;
        }
    }

    public function withDOMNode(DOMNode $node): Handler
    {
        $clone = clone $this;
        $clone->node = $node;
        $clone->initAttributes();
        return $clone;
    }

    public function getXML(): string
    {
        return $this->node->ownerDocument->saveXML($this->node);
    }

    public function getNodeName(): string
    {
        return $this->node->nodeName;
    }

    /**
     * @throws ilImportException when the attribute with $attribute_name does not exist.
     */
    public function getValueOfAttribute(string $attribute_name): string
    {
        return $this->attributes[$attribute_name];
    }

    public function getChildren(): ilImportHandlerParserNodeInfoCollectionInterface
    {
        $collection = $this->info->collection();
        $children = $this->node->childNodes;
        foreach ($children as $child) {
            $collection = $collection->withElement($this->info->DOM()->withDOMNode($child));
        }
        return $collection;
    }

    public function getParent(): Handler|null
    {
        if (!is_null($this->node->parentNode)) {
            return $this->info->DOM()->withDOMNode($this->node->parentNode);
        }
        return null;
    }

    public function hasAttribute(string $attribute_name): bool
    {
        return array_key_exists($attribute_name, $this->attributes);
    }

    public function toString(): string
    {
        $msg = $this->getNodeName();
        foreach ($this->attributes as $attribute) {
            $msg .= "\n" . $attribute;
        }
        return $msg;
    }
}
