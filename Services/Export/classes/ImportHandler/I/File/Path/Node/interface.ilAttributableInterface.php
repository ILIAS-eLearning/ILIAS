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

namespace ImportHandler\I\File\Path\Node;

use ImportHandler\I\File\Path\ilComparisonInterface as ilFilePathComparisonInterface;
use ImportHandler\I\File\Path\Node\ilSimpleInterface as ilSimpleFilePathNodeInterface;

interface ilAttributableInterface extends ilSimpleFilePathNodeInterface
{
    public function withAttribute(string $attribute): ilAttributableInterface;

    public function withComparison(ilFilePathComparisonInterface $comparison): ilAttributableInterface;

    public function withAnyAttributeEnabled(bool $enabled): ilAttributableInterface;
}
