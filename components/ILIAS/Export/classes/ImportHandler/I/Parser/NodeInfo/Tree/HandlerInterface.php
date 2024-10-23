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

namespace ILIAS\Export\ImportHandler\I\Parser\NodeInfo\Tree;

use ILIAS\Export\ImportHandler\I\File\XML\HandlerInterface as ilXMLFileHandlerInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\Attribute\CollectionInterface as ilXMLFileNodeInfoAttributePairCollectionInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\CollectionInterface as ilXMLFileNodeInfoCollectionInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\HandlerInterface as ilXMLFileNodeInfoInterface;
use ILIAS\Export\ImportHandler\I\Path\HandlerInterface as ilImportHandlerPathInterface;

interface HandlerInterface
{
    public function withRoot(ilXMLFileNodeInfoInterface $node_info): HandlerInterface;

    public function withRootInFile(
        ilXMLFileHandlerInterface $xml_handler,
        ilImportHandlerPathInterface $path_handler
    ): HandlerInterface;

    public function getNodesWith(
        ilXMLFileNodeInfoAttributePairCollectionInterface $attribute_pairs
    ): ilXMLFileNodeInfoCollectionInterface;

    public function getFirstNodeWith(
        ilXMLFileNodeInfoAttributePairCollectionInterface $attribute_pairs
    ): ilXMLFileNodeInfoInterface|null;

    public function getAttributePath(
        ilXMLFileNodeInfoInterface $startNode,
        string $attribute_name,
        string $path_separator,
        bool $skip_nodes_without_attribute = true
    ): string;
}
