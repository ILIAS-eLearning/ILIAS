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
use ImportHandler\I\File\Path\Node\ilSimpleInterface as ilSimpleFilePathNodeInterface;
use XMLReader;

class ilSimple implements ilSimpleFilePathNodeInterface
{
    protected ilLogger $logger;
    protected string $node_name;

    public function __construct(ilLogger $logger)
    {
        $this->logger = $logger;
        $this->node_name = '';
    }

    public function withName(string $node_name): ilSimpleFilePathNodeInterface
    {
        $clone = clone $this;
        $clone->node_name = $node_name;
        return $clone;
    }

    public function toString(): string
    {
        return $this->node_name;
    }
}
