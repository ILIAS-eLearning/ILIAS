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

namespace ILIAS\Export\ImportHandler\Parser\NodeInfo\Tree;

use ILIAS\Export\ImportHandler\I\File\XML\HandlerInterface as XMLFileInterface;
use ILIAS\Export\ImportHandler\I\Parser\FactoryInterface as ParserFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\Attribute\CollectionInterface as ParserNodeInfoAttributeCollectionInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\CollectionInterface as ParserNodeInfoCollectionInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\FactoryInterface as ParserNodeInfoFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\HandlerInterface as ParserNodeInfoInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\Tree\HandlerInterface as ParserNodeInfoTreeInterface;
use ILIAS\Export\ImportHandler\I\Path\HandlerInterface as PathInterface;
use ILIAS\Export\ImportStatus\Exception\ilException as ImportStatusException;
use ilLogger;

class Handler implements ParserNodeInfoTreeInterface
{
    protected ParserNodeInfoInterface $root;
    protected ParserNodeInfoFactoryInterface $info;
    protected ParserFactoryInterface $parser;
    protected XMLFileInterface $xml;
    protected ilLogger $logger;

    public function __construct(
        ParserNodeInfoFactoryInterface $info,
        ParserFactoryInterface $parser,
        ilLogger $logger
    ) {
        $this->info = $info;
        $this->parser = $parser;
        $this->logger = $logger;
    }

    public function withRoot(ParserNodeInfoInterface $node_info): ParserNodeInfoTreeInterface
    {
        $clone = clone $this;
        $clone->root = $node_info;
        return $clone;
    }

    /**
     * @throws ImportStatusException
     */
    public function withRootInFile(
        XMLFileInterface $xml_handler,
        PathInterface $path_handler
    ): ParserNodeInfoTreeInterface {
        $clone = clone $this;
        $clone->xml = $xml_handler;
        $items = $this->parser->DOM()->handler()
            ->withFileHandler($xml_handler)
            ->getNodeInfoAt($path_handler);
        if ($items->count() === 0) {
            unset($clone->root);
        }
        if ($items->count() > 0) {
            $clone->root = $items->getFirst();
        }
        return $clone;
    }

    public function getNodesWith(
        ParserNodeInfoAttributeCollectionInterface $attribute_pairs
    ): ParserNodeInfoCollectionInterface {
        if (!isset($this->root)) {
            return $this->info->collection();
        }
        $nodes = $this->info->collection()->withMerged($this->root->getChildren());
        $found = $this->info->collection();
        while (count($nodes) > 0) {
            $current_node = $nodes->getFirst();
            $nodes = $nodes->removeFirst();
            $nodes = $nodes->withMerged($current_node->getChildren());
            if ($attribute_pairs->matches($current_node)) {
                $found = $found->withElement($current_node);
            }
        }
        return $found;
    }

    public function getFirstNodeWith(
        ParserNodeInfoAttributeCollectionInterface $attribute_pairs
    ): ParserNodeInfoInterface|null {
        $nodes = $this->getNodesWith($attribute_pairs);
        return count($nodes) === 0 ? null : $nodes->getFirst();
    }

    public function getAttributePath(
        ParserNodeInfoInterface $startNode,
        string $attribute_name,
        string $path_separator,
        bool $skip_nodes_without_attribute = true
    ): string {
        $path_str = '';
        $current_node = $startNode;
        while (!is_null($current_node)) {
            if ($skip_nodes_without_attribute && !$current_node->hasAttribute($attribute_name)) {
                break;
            }
            $path_str = $current_node->hasAttribute($attribute_name)
                ? $path_separator . $current_node->getValueOfAttribute($attribute_name) . $path_str
                : $path_separator . '..' . $path_str;
            $current_node = $current_node->getParent();
        }
        return $path_str;
    }
}
