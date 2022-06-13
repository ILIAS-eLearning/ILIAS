<?php declare(strict_types=1);
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

namespace ILIAS\GlobalScreen\Collector;

/**
 * Class AbstractBaseCollector
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractBaseCollector implements Collector
{
    private bool $has_been_collected = false;

    private function setCollected() : void
    {
        $this->has_been_collected = true;
    }

    public function hasBeenCollected() : bool
    {
        return $this->has_been_collected;
    }

    public function collectOnce() : void
    {
        if (!$this->hasBeenCollected()) {
            $this->collectStructure();
            $this->prepareItemsForUIRepresentation();
            $this->filterItemsByVisibilty();
            $this->cleanupItemsForUIRepresentation();
            $this->sortItemsForUIRepresentation();
            $this->setCollected();
        }
    }
}
