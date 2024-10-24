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

namespace ILIAS\Export\ImportHandler\Parser\DOM;

use DOMDocument;
use DOMNode;
use DOMXPath;
use ILIAS\Export\ImportHandler\I\File\XML\HandlerInterface as XMLFileHandlerInterface;
use ILIAS\Export\ImportHandler\I\Parser\DOM\HandlerInterface as DOMParserHandlerInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\CollectionInterface as XMLFileNodeInfoCollectionInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\FactoryInterface as XMLFileNodeInfoFactoryInterface;
use ILIAS\Export\ImportHandler\I\Path\HandlerInterface as PathInterface;
use ILIAS\Export\ImportStatus\Exception\ilException as ImportStatusException;
use ilLogger;

class Handler implements DOMParserHandlerInterface
{
    protected XMLFileHandlerInterface $xml_file_handler;
    protected XMLFileNodeInfoFactoryInterface $info;
    protected ilLogger $logger;
    protected DOMDocument $dom_doc;

    public function __construct(
        ilLogger $logger,
        XMLFileNodeInfoFactoryInterface $info,
    ) {
        $this->logger = $logger;
        $this->info = $info;
    }

    /**
     * @throws ImportStatusException
     */
    public function withFileHandler(XMLFileHandlerInterface $file_handler): DOMParserHandlerInterface
    {
        $clone = clone $this;
        $clone->xml_file_handler = $file_handler;
        $clone->dom_doc = $file_handler->loadDomDocument();
        return $clone;
    }

    public function getNodeInfoAt(PathInterface $path): XMLFileNodeInfoCollectionInterface
    {
        $dom_xpath = new DOMXPath($this->dom_doc);
        foreach ($this->xml_file_handler->getNamespaces() as $namespace) {
            $dom_xpath->registerNamespace($namespace->getPrefix(), $namespace->getNamespace());
        }
        $nodes = $dom_xpath->query($path->toString());
        $node_info_collection = $this->info->collection();
        /** @var DOMNode $node **/
        foreach ($nodes as $node) {
            $node_info = $this->info->DOM()->withDOMNode($node);
            $node_info_collection = $node_info_collection->withElement($node_info);
        }
        return $node_info_collection;
    }
}
