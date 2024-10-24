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

use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\Attribute\CollectionInterface as ParserNodeInfoAttributeCollectionInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\Attribute\FactoryInterface as ParserNodeInfoAttributeFactoryInterface;
use ILIAS\Export\ImportHandler\I\Parser\NodeInfo\Attribute\HandlerInterface as ParserNodeInfoAttributeInterface;
use ILIAS\Export\ImportHandler\Parser\NodeInfo\Attribute\Collection as ParserNodeInfoAttribureCollection;
use ILIAS\Export\ImportHandler\Parser\NodeInfo\Attribute\Handler as ParserNodeInfoAttribute;
use ilLogger;

class Factory implements ParserNodeInfoAttributeFactoryInterface
{
    protected ilLogger $logger;

    public function __construct(ilLogger $logger)
    {
        $this->logger = $logger;
    }

    public function handler(): ParserNodeInfoAttributeInterface
    {
        return new ParserNodeInfoAttribute();
    }

    public function collection(): ParserNodeInfoAttributeCollectionInterface
    {
        return new ParserNodeInfoAttribureCollection(
            $this->logger
        );
    }
}
