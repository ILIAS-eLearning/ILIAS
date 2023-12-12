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

namespace ImportHandler\File\Path\Node;

use ilLogger;
use ImportHandler\I\File\Path\Node\ilAnyElementInterface as ilAnyElementFilePathNodeInterface;
use ImportHandler\I\File\Path\Node\ilAnyNodeInterface as ilAnyNodeFilePathNodeInterface;
use ImportHandler\I\File\Path\Node\ilAttributeInterface as ilAttributeFilePathNodeInterface;
use ImportHandler\I\File\Path\Node\ilCloseRoundBrackedInterface as ilCloseRoundBrackedFilePathNodeInterface;
use ImportHandler\I\File\Path\Node\ilFactoryInterface as ilFilePathNodeFactoryInterface;
use ImportHandler\I\File\Path\Node\ilIndexInterface as ilIndexFilePathNodeInterface;
use ImportHandler\I\File\Path\Node\ilOpenRoundBrackedInterface as ilOpenRoundBrackedFilePathNodeInterface;
use ImportHandler\I\File\Path\Node\ilSimpleInterface as ilSimpleFilePathNodeInterface;
use ImportHandler\File\Path\Node\ilAnyElement as ilAnyElementFilePathNode;
use ImportHandler\File\Path\Node\ilAnyNode as ilAnyNodeFilePathNode;
use ImportHandler\File\Path\Node\ilAttribute as ilAttributeFilePathNode;
use ImportHandler\File\Path\Node\ilFactory as ilFilePathNodeFactory;
use ImportHandler\File\Path\Node\ilIndex as ilIndexFilePathNode;
use ImportHandler\File\Path\Node\ilSimple as ilSimpleFilePathNode;
use ImportHandler\File\Path\Node\ilOpenRoundBracked as ilOpenRoundBrackedFilePathNode;
use ImportHandler\File\Path\Node\ilCloseRoundBracked as ilCloseRoundBrackedFilePathNode;

class ilFactory implements ilFilePathNodeFactoryInterface
{
    protected ilLogger $logger;

    public function __construct(ilLogger $logger)
    {
        $this->logger = $logger;
    }

    public function anyElement(): ilAnyElementFilePathNodeInterface
    {
        return new ilAnyElementFilePathNode();
    }

    public function anyNode(): ilAnyNodeFilePathNodeInterface
    {
        return new ilAnyNodeFilePathNode();
    }

    public function attribute(): ilAttributeFilePathNodeInterface
    {
        return new ilAttributeFilePathNode();
    }

    public function index(): ilIndexFilePathNodeInterface
    {
        return new ilIndexFilePathNode();
    }

    public function simple(): ilSimpleFilePathNodeInterface
    {
        return new ilSimpleFilePathNode();
    }

    public function openRoundBracked(): ilOpenRoundBrackedFilePathNodeInterface
    {
        return new ilOpenRoundBrackedFilePathNode();
    }

    public function closeRoundBracked(): ilCloseRoundBrackedFilePathNodeInterface
    {
        return new ilCloseRoundBrackedFilePathNode();
    }
}
