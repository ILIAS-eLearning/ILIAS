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

namespace ILIAS\Export\ImportHandler\I\File\Path\Node;

use ILIAS\Export\ImportHandler\I\File\Path\Node\ilSimpleInterface as ilSimpleFilePathNodeInterface;
use ILIAS\Export\ImportHandler\I\File\Path\Node\ilAnyElementInterface as ilAnyElementFilePathNodeInterface;
use ILIAS\Export\ImportHandler\I\File\Path\Node\ilAnyNodeInterface as ilAnyNodeFilePathNodeInterface;
use ILIAS\Export\ImportHandler\I\File\Path\Node\ilAttributeInterface as ilAttributeFilePathNodeInterface;
use ILIAS\Export\ImportHandler\I\File\Path\Node\ilIndexInterface as ilIndexFilePathNodeInterface;
use ILIAS\Export\ImportHandler\I\File\Path\Node\ilOpenRoundBrackedInterface as ilOpenRoundBrackedFilePathNodeInterface;
use ILIAS\Export\ImportHandler\I\File\Path\Node\ilCloseRoundBrackedInterface as ilCloseRoundBrackedFilePathNodeInterface;

interface ilFactoryInterface
{
    public function anyElement(): ilAnyElementFilePathNodeInterface;

    public function anyNode(): ilAnyNodeFilePathNodeInterface;

    public function attribute(): ilAttributeFilePathNodeInterface;

    public function index(): ilIndexFilePathNodeInterface;

    public function simple(): ilSimpleFilePathNodeInterface;

    public function openRoundBracked(): ilOpenRoundBrackedFilePathNodeInterface;

    public function closeRoundBracked(): ilCloseRoundBrackedFilePathNodeInterface;
}
