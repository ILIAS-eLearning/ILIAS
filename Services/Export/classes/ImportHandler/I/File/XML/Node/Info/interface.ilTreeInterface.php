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

namespace ImportHandler\I\File\XML\Node\Info;

use ImportHandler\I\File\Path\ilHandlerInterface as ilXMLFilePathHandlerInterface;
use ImportHandler\I\File\XML\ilHandlerInterface as ilXMLFileHandlerInterface;
use ImportHandler\I\File\XML\Node\Info\Attribute\ilPairInterface as ilXMLFileNodeInfoAttributePairInterface;
use ImportHandler\I\File\XML\Node\Info\ilCollectionInterface as ilXMLFileNodeInfoCollectionInterface;
use ImportHandler\I\File\XML\Node\Info\ilHandlerInterface as ilXMLFileNodeInfoInterface;
use ImportHandler\I\File\XML\Node\Info\Attribute\ilCollectionInterface as ilXMLFileNodeInfoAttributePairCollectionInterface;

interface ilTreeInterface
{
    public function withRoot(ilXMLFileNodeInfoInterface $node_info): ilTreeInterface;

    public function withRootInFile(
        ilXMLFileHandlerInterface $xml_handler,
        ilXMLFilePathHandlerInterface $path_handler
    ): ilTreeInterface;

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
