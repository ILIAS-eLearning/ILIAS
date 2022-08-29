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

namespace ILIAS\MetaData\Editor\Digest;

class CopyrightHandler
{
    /**
     * @var \ilMDCopyrightSelectionEntry[]
     */
    protected array $entries;

    /**
     * @var \ilOerHarvesterObjectStatus[]
     */
    protected array $statuses = [];

    public function isCPSelectionActive(): bool
    {
        $settings = \ilMDSettings::_getInstance();
        return $settings->isCopyrightSelectionActive() && $this->hasCPEntries();
    }

    protected function getOerHarvesterSettings(): \ilOerHarvesterSettings
    {
        return \ilOerHarvesterSettings::getInstance();
    }

    public function doesObjectTypeSupportHarvesting(string $type): bool
    {
        return $this->getOerHarvesterSettings()->supportsHarvesting($type);
    }

    public function isCopyrightTemplateActive(\ilMDCopyrightSelectionEntry $entry): bool
    {
        return $this->getOerHarvesterSettings()->isActiveCopyrightTemplate($entry->getEntryId());
    }

    protected function hasCPEntries(): bool
    {
        $this->initCPEntries();
        return !empty($this->entries);
    }

    /**
     * @return \ilMDCopyrightSelectionEntry[]
     */
    public function getCPEntries(): \Generator
    {
        $this->initCPEntries();
        yield from $this->entries;
    }

    protected function initCPEntries(): void
    {
        if (!isset($this->entries)) {
            $this->entries = \ilMDCopyrightSelectionEntry::_getEntries();
        }
    }

    public function extractCPEntryID(string $description): int
    {
        return \ilMDCopyrightSelectionEntry::_extractEntryId($description);
    }

    public function getDefaultCPEntryID(): int
    {
        return \ilMDCopyrightSelectionEntry::getDefault();
    }

    public function createIdentifierForID(int $entry_id): string
    {
        return \ilMDCopyrightSelectionEntry::createIdentifier($entry_id);
    }

    public function isOerHarvesterBlocked(int $obj_id): bool
    {
        $status = $this->getHarvesterStatus($obj_id);
        return $status->isBlocked();
    }

    public function setOerHarvesterBlocked(
        int $obj_id,
        bool $is_blocked
    ): void {
        $status = $this->getHarvesterStatus($obj_id);
        $status->setBlocked($is_blocked);
        $status->save();
    }

    protected function getHarvesterStatus(int $obj_id): \ilOerHarvesterObjectStatus
    {
        if (isset($this->statuses[$obj_id])) {
            return $this->statuses[$obj_id];
        }
        return $this->statuses[$obj_id] = new \ilOerHarvesterObjectStatus($obj_id);
    }
}
