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

namespace ILIAS\AdvancedMetaData\Data;

abstract class PersistenceTrackingDataImplementation implements PersistenceTrackingData
{
    private bool $contains_changes = false;

    /**
     * Is this data is already persisted?
     */
    abstract public function isPersisted(): bool;

    /**
     * @return PersistenceTrackingData[]
     */
    abstract protected function getSubData(): \Generator;

    /**
     * Was the contained data (including sub-objects) altered with respect
     * to what is persisted?
     * Returns true if not persisted.
     */
    final public function containsChanges(): bool
    {
        if (!$this->isPersisted() || $this->contains_changes) {
            return true;
        }
        foreach ($this->getSubData() as $sub_datum) {
            if ($sub_datum->containsChanges()) {
                return true;
            }
        }
        return false;
    }

    final protected function markAsChanged(): void
    {
        $this->contains_changes = true;
    }
}
