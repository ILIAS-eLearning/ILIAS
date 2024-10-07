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

use ILIAS\MetaData\Copyright\RepositoryInterface as CopyrightRepository;
use ILIAS\MetaData\Copyright\EntryInterface;
use ILIAS\MetaData\Settings\SettingsInterface as Settings;
use ILIAS\MetaData\OERHarvester\Settings\SettingsInterface as OERHarvesterSettings;
use ILIAS\MetaData\OERHarvester\ResourceStatus\RepositoryInterface as HarvestStatusRepository;
use ILIAS\MetaData\Copyright\Identifiers\HandlerInterface as CopyrightIDHandler;

class CopyrightHandler
{
    protected CopyrightRepository $repository;
    protected Settings $settings;
    protected OERHarvesterSettings $harvester_settings;
    protected HarvestStatusRepository $harvest_status_repo;
    protected CopyrightIDHandler $copyright_id_handler;

    /**
     * @var EntryInterface[]
     */
    protected array $entries;

    public function __construct(
        CopyrightRepository $repository,
        Settings $settings,
        OERHarvesterSettings $harvester_settings,
        HarvestStatusRepository $harvest_status_repo,
        CopyrightIDHandler $copyright_id_handler
    ) {
        $this->repository = $repository;
        $this->settings = $settings;
        $this->harvester_settings = $harvester_settings;
        $this->harvest_status_repo = $harvest_status_repo;
        $this->copyright_id_handler = $copyright_id_handler;
    }

    public function isCPSelectionActive(): bool
    {
        return $this->settings->isCopyrightSelectionActive() && $this->hasCPEntries();
    }

    public function isObjectTypeHarvested(string $type): bool
    {
        return $this->harvester_settings->isObjectTypeSelectedForHarvesting($type);
    }

    public function isCopyrightTemplateActive(EntryInterface $entry): bool
    {
        return $this->harvester_settings->isCopyrightEntryIDSelectedForHarvesting($entry->id());
    }

    protected function hasCPEntries(): bool
    {
        $this->initCPEntries();
        return !empty($this->entries);
    }

    /**
     * @return EntryInterface[]
     */
    public function getCPEntries(): \Generator
    {
        $this->initCPEntries();
        yield from $this->entries;
    }

    protected function initCPEntries(): void
    {
        if (!isset($this->entries)) {
            $this->entries = iterator_to_array($this->repository->getAllEntries());
        }
    }

    public function extractCPEntryID(string $description): int
    {
        return $this->copyright_id_handler->parseEntryIDFromIdentifier($description);
    }

    public function createIdentifierForID(int $entry_id): string
    {
        return $this->copyright_id_handler->buildIdentifierFromEntryID($entry_id);
    }

    public function isOerHarvesterBlocked(int $obj_id): bool
    {
        return $this->harvest_status_repo->isHarvestingBlocked($obj_id);
    }

    public function setOerHarvesterBlocked(
        int $obj_id,
        bool $is_blocked
    ): void {
        $this->harvest_status_repo->setHarvestingBlocked($obj_id, $is_blocked);
    }
}
