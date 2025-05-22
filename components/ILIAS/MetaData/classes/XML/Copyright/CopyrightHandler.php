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

namespace ILIAS\MetaData\XML\Copyright;

use ILIAS\MetaData\Copyright\RepositoryInterface as CopyrightRepository;
use ILIAS\MetaData\Copyright\Identifiers\HandlerInterface as IdentifierHandler;
use ILIAS\MetaData\Copyright\RendererInterface as CopyrightRenderer;
use ILIAS\MetaData\Copyright\EntryInterface;
use ILIAS\MetaData\Settings\SettingsInterface;

class CopyrightHandler implements CopyrightHandlerInterface
{
    protected CopyrightRepository $copyright_repository;
    protected IdentifierHandler $identifier_handler;
    protected SettingsInterface $settings;

    /**
     * @var EntryInterface[]
     */
    protected array $copyright_entries;

    public function __construct(
        CopyrightRepository $copyright_repository,
        IdentifierHandler $identifier_handler,
        SettingsInterface $settings
    ) {
        $this->copyright_repository = $copyright_repository;
        $this->identifier_handler = $identifier_handler;
        $this->settings = $settings;
    }

    public function copyrightForExport(string $copyright): string
    {
        return $this->copyrightAsString($copyright);
    }

    public function copyrightFromExport(string $copyright): string
    {
        if (!$this->isCopyrightSelectionActive()) {
            return $copyright;
        }

        // url should be made to match regardless of scheme
        $normalized_copyright = str_replace('https://', 'http://', $copyright);

        $matches_by_name = null;
        foreach ($this->getAllCopyrightEntries() as $entry) {
            $entry_link = (string) $entry->copyrightData()->link();
            $normalized_link = str_replace('https://', 'http://', $entry_link);
            if ($normalized_link !== '' && str_contains($normalized_copyright, $normalized_link)) {
                return $this->identifier_handler->buildIdentifierFromEntryID($entry->id());
            }

            if (
                is_null($matches_by_name) &&
                trim($copyright) === trim($entry->copyrightData()->fullName())
            ) {
                $matches_by_name = $this->identifier_handler->buildIdentifierFromEntryID($entry->id());
            }
        }

        if (!is_null($matches_by_name)) {
            return $matches_by_name;
        }
        return $copyright;
    }

    public function copyrightAsString(string $copyright): string
    {
        if (!$this->isCopyrightSelectionActive()) {
            return $copyright;
        }

        if (!$this->identifier_handler->isIdentifierValid($copyright) && $copyright !== '') {
            return $copyright;
        }

        if ($copyright === '') {
            $entry_data = $this->copyright_repository->getDefaultEntry()->copyrightData();
        } else {
            $entry_id = $this->identifier_handler->parseEntryIDFromIdentifier($copyright);
            $entry_data = $this->copyright_repository->getEntry($entry_id)->copyrightData();
        }
        $full_name = $entry_data->fullName();
        $link = $entry_data->link();

        if (!is_null($link)) {
            return (string) $link;
        }
        return $full_name;
    }

    /**
     * @return EntryInterface[]
     */
    protected function getAllCopyrightEntries(): \Generator
    {
        if (!isset($this->copyright_entries)) {
            $this->copyright_entries = iterator_to_array($this->copyright_repository->getAllEntries());
        }
        yield from $this->copyright_entries;
    }

    public function isCopyrightSelectionActive(): bool
    {
        return $this->settings->isCopyrightSelectionActive();
    }
}
