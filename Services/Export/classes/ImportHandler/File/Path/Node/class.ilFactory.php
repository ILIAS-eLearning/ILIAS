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
use ImportHandler\I\File\Path\Node\ilAttributableInterface as ilAttributableFilePathNodeInterface;
use ImportHandler\I\File\Path\Node\ilFactoryInterface as ilFilePathNodeFactoryInterface;
use ImportHandler\I\File\Path\Node\ilIndexableInterface as ilIndexableFilePathNodeInterface;
use ImportHandler\I\File\Path\Node\ilSimpleInterface as ilSimpleFilePathNodeInterface;
use ImportHandler\File\Path\Node\ilAnyElement as ilAnyElementFilePathNode;
use ImportHandler\File\Path\Node\ilAnyNode as ilAnyNodeFilePathNode;
use ImportHandler\File\Path\Node\ilAttributable as ilAttributableFilePathNode;
use ImportHandler\File\Path\Node\ilFactory as ilFilePathNodeFactory;
use ImportHandler\File\Path\Node\ilIndexable as ilIndexableFilePathNode;
use ImportHandler\File\Path\Node\ilSimple as ilSimpleFilePathNode;

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

    public function attributable(): ilAttributableFilePathNodeInterface
    {
        return new ilAttributableFilePathNode();
    }

    public function indexable(): ilIndexableFilePathNodeInterface
    {
        return new ilIndexableFilePathNode();
    }

    public function simple(): ilSimpleFilePathNodeInterface
    {
        return new ilSimpleFilePathNode($this->logger);
    }
}
