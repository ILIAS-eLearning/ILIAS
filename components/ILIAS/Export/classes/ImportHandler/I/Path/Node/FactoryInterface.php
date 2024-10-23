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

use ILIAS\Export\ImportHandler\I\Path\Node\AnyElementInterface as ilImportHandlerPathNodeAnyElementInterface;
use ILIAS\Export\ImportHandler\I\Path\Node\AnyNodeInterface as ilImportHandlerPathNodeAnyNodeInterface;
use ILIAS\Export\ImportHandler\I\Path\Node\AttributeInterface as ilImportHandlerPathNodeAttributeInterface;
use ILIAS\Export\ImportHandler\I\Path\Node\CloseRoundBrackedInterface as ilImportHandlerPathNodeCloseRoundBrackedInterface;
use ILIAS\Export\ImportHandler\I\Path\Node\IndexInterface as ilImportHandlerPathNodeIndexInterface;
use ILIAS\Export\ImportHandler\I\Path\Node\OpenRoundBrackedInterface as ilImportHandlerPathNodeOpenRoundBrackedInterface;
use ILIAS\Export\ImportHandler\I\Path\Node\SimpleInterface as ilImportHandlerPathNodeSimpleInterface;

interface FactoryInterface
{
    public function anyElement(): ilImportHandlerPathNodeAnyElementInterface;

    public function anyNode(): ilImportHandlerPathNodeAnyNodeInterface;

    public function attribute(): ilImportHandlerPathNodeAttributeInterface;

    public function index(): ilImportHandlerPathNodeIndexInterface;

    public function simple(): ilImportHandlerPathNodeSimpleInterface;

    public function openRoundBracked(): ilImportHandlerPathNodeOpenRoundBrackedInterface;

    public function closeRoundBracked(): ilImportHandlerPathNodeCloseRoundBrackedInterface;
}
