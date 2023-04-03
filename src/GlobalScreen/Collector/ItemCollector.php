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
namespace ILIAS\GlobalScreen\Collector;

use LogicException;
use Generator;

/**
 * Interface Collector
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface ItemCollector extends Collector
{
    public function collectStructure() : void;

    public function filterItemsByVisibilty(bool $async_only = false) : void;

    public function prepareItemsForUIRepresentation() : void;

    /**
     * @return Generator
     */
    public function getItemsForUIRepresentation() : Generator;

    /**
     * @return bool
     * @throws LogicException if collectOnce() has not been run first
     */
    public function hasItems() : bool;

    public function hasVisibleItems() : bool;
}
