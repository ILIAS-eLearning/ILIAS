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

use ILIAS\Export\ImportHandler\I\Path\Node\AnyElementInterface as PathNodeAnyElementInterface;
use ILIAS\Export\ImportHandler\I\Path\Node\AnyNodeInterface as PathNodeAnyNodeInterface;
use ILIAS\Export\ImportHandler\I\Path\Node\AttributeInterface as PathNodeAttributeInterface;
use ILIAS\Export\ImportHandler\I\Path\Node\CloseRoundBrackedInterface as PathNodeCloseRoundBrackedInterface;
use ILIAS\Export\ImportHandler\I\Path\Node\FactoryInterface as PathNodeFactoryInterface;
use ILIAS\Export\ImportHandler\I\Path\Node\IndexInterface as PathNodeIndexInterface;
use ILIAS\Export\ImportHandler\I\Path\Node\OpenRoundBrackedInterface as PathNodeOpenRoundBrackedInterface;
use ILIAS\Export\ImportHandler\I\Path\Node\SimpleInterface as PathNodeSimpleInterface;
use ILIAS\Export\ImportHandler\Path\Node\AnyElement as PathNodeAnyElement;
use ILIAS\Export\ImportHandler\Path\Node\AnyNode as PathNodeAnyNode;
use ILIAS\Export\ImportHandler\Path\Node\Attribute as PathNodeAttribute;
use ILIAS\Export\ImportHandler\Path\Node\CloseRoundBracked as PathNodeCloseRoundBracked;
use ILIAS\Export\ImportHandler\Path\Node\Index as PathNodeIndex;
use ILIAS\Export\ImportHandler\Path\Node\OpenRoundBracked as PathNodeOpenRoundBracked;
use ILIAS\Export\ImportHandler\Path\Node\Simple as PathNodeSimple;
use ilLogger;

class Factory implements PathNodeFactoryInterface
{
    protected ilLogger $logger;

    public function __construct(ilLogger $logger)
    {
        $this->logger = $logger;
    }

    public function anyElement(): PathNodeAnyElementInterface
    {
        return new PathNodeAnyElement();
    }

    public function anyNode(): PathNodeAnyNodeInterface
    {
        return new PathNodeAnyNode();
    }

    public function attribute(): PathNodeAttributeInterface
    {
        return new PathNodeAttribute();
    }

    public function index(): PathNodeIndexInterface
    {
        return new PathNodeIndex();
    }

    public function simple(): PathNodeSimpleInterface
    {
        return new PathNodeSimple();
    }

    public function openRoundBracked(): PathNodeOpenRoundBrackedInterface
    {
        return new PathNodeOpenRoundBracked();
    }

    public function closeRoundBracked(): PathNodeCloseRoundBrackedInterface
    {
        return new PathNodeCloseRoundBracked();
    }
}
