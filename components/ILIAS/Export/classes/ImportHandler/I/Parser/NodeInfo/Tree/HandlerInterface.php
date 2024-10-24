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

use ILIAS\Export\ImportHandler\I\File\XML\HandlerInterface as XMLFileHandlerInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\Attribute\CollectionInterface as XMLFileNodeInfoAttributePairCollectionInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\CollectionInterface as XMLFileNodeInfoCollectionInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\HandlerInterface as XMLFileNodeInfoInterface;
use ILIAS\Export\ImportHandler\I\Path\HandlerInterface as PathInterface;

interface HandlerInterface
{
    public function withRoot(XMLFileNodeInfoInterface $node_info): HandlerInterface;

    public function withRootInFile(
        XMLFileHandlerInterface $xml_handler,
        PathInterface $path_handler
    ): HandlerInterface;

    public function getNodesWith(
        XMLFileNodeInfoAttributePairCollectionInterface $attribute_pairs
    ): XMLFileNodeInfoCollectionInterface;

    public function getFirstNodeWith(
        XMLFileNodeInfoAttributePairCollectionInterface $attribute_pairs
    ): XMLFileNodeInfoInterface|null;

    public function getAttributePath(
        XMLFileNodeInfoInterface $startNode,
        string $attribute_name,
        string $path_separator,
        bool $skip_nodes_without_attribute = true
    ): string;
}
