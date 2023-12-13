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

namespace ImportHandler\File\XML\Node\Info;

use ilLogger;
use ImportHandler\I\File\Path\ilHandlerInterface as ilXMLFilePathHandlerInterface;
use ImportHandler\I\File\XML\ilHandlerInterface as ilXMLFileHandlerInterface;
use ImportHandler\I\File\XML\Node\Info\Attribute\ilPairInterface as ilXMLFileNodeInfoAttributePairInterface;
use ImportHandler\I\File\XML\Node\Info\ilCollectionInterface as ilXMLFileNodeInfoCollectionInterface;
use ImportHandler\I\File\XML\Node\Info\ilFactoryInterface as ilXMLFileNodeInfoFactoryInterface;
use ImportHandler\I\File\XML\Node\Info\ilHandlerInterface as ilXMLFileNodeInfoInterface;
use ImportHandler\I\File\XML\Node\Info\ilTreeInterface as ilXMLFileNodeInfoTreeInterface;
use ImportHandler\I\Parser\ilFactoryInterface as ilParserFactoryInterface;
use ImportHandler\I\File\XML\Node\Info\Attribute\ilCollectionInterface as ilXMLFileNodeInfoAttributePairCollectionInterface;
use ImportStatus\Exception\ilException as ilImportStatusException;

class ilTree implements ilXMLFileNodeInfoTreeInterface
{
    protected ilXMLFileNodeInfoInterface $root;
    protected ilXMLFileNodeInfoFactoryInterface $info;
    protected ilParserFactoryInterface $parser;
    protected ilXMLFileHandlerInterface $xml;
    protected ilLogger $logger;

    public function __construct(
        ilXMLFileNodeInfoFactoryInterface $info,
        ilParserFactoryInterface $parser,
        ilLogger $logger
    ) {
        $this->info = $info;
        $this->parser = $parser;
        $this->logger = $logger;
    }

    public function withRoot(ilXMLFileNodeInfoInterface $node_info): ilXMLFileNodeInfoTreeInterface
    {
        $clone = clone $this;
        $clone->root = $node_info;
        return $clone;
    }

    /**
     * @throws ilImportStatusException
     */
    public function withRootInFile(
        ilXMLFileHandlerInterface $xml_handler,
        ilXMLFilePathHandlerInterface $path_handler
    ): ilXMLFileNodeInfoTreeInterface {
        $clone = clone $this;
        $clone->xml = $xml_handler;
        $items = $this->parser->DOM()->withFileHandler($xml_handler)->getNodeInfoAt($path_handler);
        if ($items->count() === 0) {
            unset($clone->root);
        }
        if ($items->count() > 0) {
            $clone->root = $items->getFirst();
        }
        return $clone;
    }

    public function getNodesWith(
        ilXMLFileNodeInfoAttributePairCollectionInterface $attribute_pairs
    ): ilXMLFileNodeInfoCollectionInterface {
        if(!isset($this->root)) {
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
        ilXMLFileNodeInfoAttributePairCollectionInterface $attribute_pairs
    ): ilXMLFileNodeInfoInterface|null {
        $nodes = $this->getNodesWith($attribute_pairs);
        return count($nodes) === 0 ? null : $nodes->getFirst();
    }

    public function getAttributePath(
        ilXMLFileNodeInfoInterface $startNode,
        string $attribute_name,
        string $path_separator,
        bool $skip_nodes_without_attribute = true
    ): string {
        $path_str = '';
        $current_node = $startNode;
        while (!is_null($current_node)) {
            if($skip_nodes_without_attribute && !$current_node->hasAttribute($attribute_name)) {
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
