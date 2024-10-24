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

namespace ILIAS\Export\ImportHandler\I\Path\Node;

use ILIAS\Export\ImportHandler\I\Path\Node\AnyElementInterface as PathNodeAnyElementInterface;
use ILIAS\Export\ImportHandler\I\Path\Node\AnyNodeInterface as PathNodeAnyNodeInterface;
use ILIAS\Export\ImportHandler\I\Path\Node\AttributeInterface as PathNodeAttributeInterface;
use ILIAS\Export\ImportHandler\I\Path\Node\CloseRoundBrackedInterface as PathNodeCloseRoundBrackedInterface;
use ILIAS\Export\ImportHandler\I\Path\Node\IndexInterface as PathNodeIndexInterface;
use ILIAS\Export\ImportHandler\I\Path\Node\OpenRoundBrackedInterface as PathNodeOpenRoundBrackedInterface;
use ILIAS\Export\ImportHandler\I\Path\Node\SimpleInterface as PathNodeSimpleInterface;

interface FactoryInterface
{
    public function anyElement(): PathNodeAnyElementInterface;

    public function anyNode(): PathNodeAnyNodeInterface;

    public function attribute(): PathNodeAttributeInterface;

    public function index(): PathNodeIndexInterface;

    public function simple(): PathNodeSimpleInterface;

    public function openRoundBracked(): PathNodeOpenRoundBrackedInterface;

    public function closeRoundBracked(): PathNodeCloseRoundBrackedInterface;
}
