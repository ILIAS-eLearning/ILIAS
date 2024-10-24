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

use ILIAS\Export\ImportHandler\I\FactoryInterface as ImportHandlerFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\Attribute\FactoryInterface as ParserNodeInfoAttributeFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\CollectionInterface as ParserNodeInfoCollectionInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\DOM\FactoryInterface as ParserDOMNodeInfoFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\FactoryInterface as ParserNodeInfoFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\Tree\FactoryInterface as ParserNodeInfoTreeFactoryInterface;
use ILIAS\Export\ImportHandler\Parser\NodeInfo\Attribute\Factory as ParserNodeInfoAttributeFactory;
use ILIAS\Export\ImportHandler\Parser\NodeInfo\Collection as ParserNodeInfoCollection;
use ILIAS\Export\ImportHandler\Parser\NodeInfo\DOM\Factory as ParserDOMNodeInfoFactory;
use ILIAS\Export\ImportHandler\Parser\NodeInfo\Tree\Factory as ParserNodeInfoTreeFactory;
use ilLogger;

class Factory implements ParserNodeInfoFactoryInterface
{
    protected ImportHandlerFactoryInterface $import_handler;
    protected ilLogger $logger;

    public function __construct(
        ImportHandlerFactoryInterface $import_handler,
        ilLogger $logger
    ) {
        $this->import_handler = $import_handler;
        $this->logger = $logger;
    }

    public function collection(): ParserNodeInfoCollectionInterface
    {
        return new ParserNodeInfoCollection();
    }

    public function tree(): ParserNodeInfoTreeFactoryInterface
    {
        return new ParserNodeInfoTreeFactory(
            $this->import_handler,
            $this->logger
        );
    }

    public function attribute(): ParserNodeInfoAttributeFactoryInterface
    {
        return new ParserNodeInfoAttributeFactory(
            $this->logger
        );
    }

    public function DOM(): ParserDOMNodeInfoFactoryInterface
    {
        return new ParserDOMNodeInfoFactory(
            $this
        );
    }
}
