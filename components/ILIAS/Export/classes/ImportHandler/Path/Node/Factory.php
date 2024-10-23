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

namespace ILIAS\Export\ImportHandler\Path\Node;

use ILIAS\Export\ImportHandler\I\Path\Node\AnyElementInterface as ilImportHandlerPathNodeAnyElementInterface;
use ILIAS\Export\ImportHandler\I\Path\Node\AnyNodeInterface as ilImportHandlerPathNodeAnyNodeInterface;
use ILIAS\Export\ImportHandler\I\Path\Node\AttributeInterface as ilImportHandlerPathNodeAttributeInterface;
use ILIAS\Export\ImportHandler\I\Path\Node\CloseRoundBrackedInterface as ilImportHandlerPathNodeCloseRoundBrackedInterface;
use ILIAS\Export\ImportHandler\I\Path\Node\FactoryInterface as ilImportHandlerPathNodeFactoryInterface;
use ILIAS\Export\ImportHandler\I\Path\Node\IndexInterface as ilImportHandlerPathNodeIndexInterface;
use ILIAS\Export\ImportHandler\I\Path\Node\OpenRoundBrackedInterface as ilImportHandlerPathNodeOpenRoundBrackedInterface;
use ILIAS\Export\ImportHandler\I\Path\Node\SimpleInterface as ilImportHandlerPathNodeSimpleInterface;
use ILIAS\Export\ImportHandler\Path\Node\AnyElement as ilImportHandlerPathNodeAnyElement;
use ILIAS\Export\ImportHandler\Path\Node\AnyNode as ilImportHandlerPathNodeAnyNode;
use ILIAS\Export\ImportHandler\Path\Node\Attribute as ilImportHandlerPathNodeAttribute;
use ILIAS\Export\ImportHandler\Path\Node\CloseRoundBracked as ilImportHandlerPathNodeCloseRoundBracked;
use ILIAS\Export\ImportHandler\Path\Node\Index as ilImportHandlerPathNodeIndex;
use ILIAS\Export\ImportHandler\Path\Node\OpenRoundBracked as ilImportHandlerPathNodeOpenRoundBracked;
use ILIAS\Export\ImportHandler\Path\Node\Simple as ilImportHandlerPathNodeSimple;
use ilLogger;

class Factory implements ilImportHandlerPathNodeFactoryInterface
{
    protected ilLogger $logger;

    public function __construct(ilLogger $logger)
    {
        $this->logger = $logger;
    }

    public function anyElement(): ilImportHandlerPathNodeAnyElementInterface
    {
        return new ilImportHandlerPathNodeAnyElement();
    }

    public function anyNode(): ilImportHandlerPathNodeAnyNodeInterface
    {
        return new ilImportHandlerPathNodeAnyNode();
    }

    public function attribute(): ilImportHandlerPathNodeAttributeInterface
    {
        return new ilImportHandlerPathNodeAttribute();
    }

    public function index(): ilImportHandlerPathNodeIndexInterface
    {
        return new ilImportHandlerPathNodeIndex();
    }

    public function simple(): ilImportHandlerPathNodeSimpleInterface
    {
        return new ilImportHandlerPathNodeSimple();
    }

    public function openRoundBracked(): ilImportHandlerPathNodeOpenRoundBrackedInterface
    {
        return new ilImportHandlerPathNodeOpenRoundBracked();
    }

    public function closeRoundBracked(): ilImportHandlerPathNodeCloseRoundBrackedInterface
    {
        return new ilImportHandlerPathNodeCloseRoundBracked();
    }
}
