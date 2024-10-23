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

namespace ILIAS\Export\ImportHandler\Parser\NodeInfo;

use ILIAS\Export\ImportHandler\I\FactoryInterface as ilImportHandlerFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\Attribute\FactoryInterface as ilImportHandlerParserNodeInfoAttributeFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\CollectionInterface as ilImportHandlerParserNodeInfoCollectionInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\DOM\FactoryInterface as ilImportHandlerParserDOMNodeInfoFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\FactoryInterface as ilImportHandlerParserNodeInfoFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\Tree\FactoryInterface as ilImportHandlerParserNodeInfoTreeFactoryInterface;
use ILIAS\Export\ImportHandler\Parser\NodeInfo\Attribute\Factory as ilImportHandlerParserNodeInfoAttributeFactory;
use ILIAS\Export\ImportHandler\Parser\NodeInfo\Collection as ilImportHandlerParserNodeInfoCollection;
use ILIAS\Export\ImportHandler\Parser\NodeInfo\DOM\Factory as ilImportHandlerParserDOMNodeInfoFactory;
use ILIAS\Export\ImportHandler\Parser\NodeInfo\Tree\Factory as ilImportHandlerParserNodeInfoTreeFactory;
use ilLogger;

class Factory implements ilImportHandlerParserNodeInfoFactoryInterface
{
    protected ilImportHandlerFactoryInterface $import_handler;
    protected ilLogger $logger;

    public function __construct(
        ilImportHandlerFactoryInterface $import_handler,
        ilLogger $logger
    ) {
        $this->import_handler = $import_handler;
        $this->logger = $logger;
    }

    public function collection(): ilImportHandlerParserNodeInfoCollectionInterface
    {
        return new ilImportHandlerParserNodeInfoCollection();
    }

    public function tree(): ilImportHandlerParserNodeInfoTreeFactoryInterface
    {
        return new ilImportHandlerParserNodeInfoTreeFactory(
            $this->import_handler,
            $this->logger
        );
    }

    public function attribute(): ilImportHandlerParserNodeInfoAttributeFactoryInterface
    {
        return new ilImportHandlerParserNodeInfoAttributeFactory(
            $this->logger
        );
    }

    public function DOM(): ilImportHandlerParserDOMNodeInfoFactoryInterface
    {
        return new ilImportHandlerParserDOMNodeInfoFactory(
            $this
        );
    }
}
