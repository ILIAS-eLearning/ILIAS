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

namespace ILIAS\Export\ImportHandler\Parser\NodeInfo\Attribute;

use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\Attribute\CollectionInterface as ilImportHandlerParserNodeInfoAttributeCollectionInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\Attribute\FactoryInterface as ilImportHandlerParserNodeInfoAttributeFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\Attribute\HandlerInterface as ilImportHandlerParserNodeInfoAttributeInterface;
use ILIAS\Export\ImportHandler\Parser\NodeInfo\Attribute\Collection as ilImportHandlerParserNodeInfoAttribureCollection;
use ILIAS\Export\ImportHandler\Parser\NodeInfo\Attribute\Handler as ilImportHandlerParserNodeInfoAttribute;
use ilLogger;

class Factory implements ilImportHandlerParserNodeInfoAttributeFactoryInterface
{
    protected ilLogger $logger;

    public function __construct(ilLogger $logger)
    {
        $this->logger = $logger;
    }

    public function handler(): ilImportHandlerParserNodeInfoAttributeInterface
    {
        return new ilImportHandlerParserNodeInfoAttribute();
    }

    public function collection(): ilImportHandlerParserNodeInfoAttributeCollectionInterface
    {
        return new ilImportHandlerParserNodeInfoAttribureCollection(
            $this->logger
        );
    }
}
