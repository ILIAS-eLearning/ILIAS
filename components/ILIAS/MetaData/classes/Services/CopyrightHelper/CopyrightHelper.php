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

namespace ILIAS\MetaData\Services\CopyrightHelper;

use ILIAS\MetaData\Services\Reader\ReaderInterface;
use ILIAS\MetaData\Services\Manipulator\ManipulatorInterface;
use ILIAS\MetaData\Search\Clauses\ClauseInterface as SearchClause;
use ILIAS\MetaData\Settings\SettingsInterface;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\Copyright\Identifiers\HandlerInterface as IdentifierHandler;
use ILIAS\MetaData\Copyright\RepositoryInterface as CopyrightRepository;
use ILIAS\MetaData\Copyright\EntryInterface;
use ILIAS\MetaData\Copyright\RendererInterface;
use ILIAS\MetaData\Search\Clauses\FactoryInterface as SearchClauseFactory;
use ILIAS\MetaData\Search\Clauses\Mode;
use ILIAS\MetaData\Search\Clauses\Operator;

class CopyrightHelper implements CopyrightHelperInterface
{
    protected SettingsInterface $settings;
    protected PathFactory $path_factory;
    protected IdentifierHandler $identifier_handler;
    protected CopyrightRepository $copyright_repo;
    protected RendererInterface $renderer;
    protected SearchClauseFactory $search_clause_factory;

    public function __construct(
        SettingsInterface $settings,
        PathFactory $path_factory,
        CopyrightRepository $copyright_repo,
        IdentifierHandler $identifier_handler,
        RendererInterface $renderer,
        SearchClauseFactory $search_clause_factory
    ) {
        $this->settings = $settings;
        $this->path_factory = $path_factory;
        $this->copyright_repo = $copyright_repo;
        $this->identifier_handler = $identifier_handler;
        $this->renderer = $renderer;
        $this->search_clause_factory = $search_clause_factory;
    }

    public function isCopyrightSelectionActive(): bool
    {
        return $this->settings->isCopyrightSelectionActive();
    }

    public function hasPresetCopyright(ReaderInterface $reader): bool
    {
        if (!$this->isCopyrightSelectionActive()) {
            return false;
        }
        $raw = $this->getRawCopyright($reader);

        if ($raw === '') {
            return true;
        }

        return $this->identifier_handler->isIdentifierValid($this->getRawCopyright($reader));
    }

    public function readPresetCopyright(ReaderInterface $reader): CopyrightInterface
    {
        if (!$this->isCopyrightSelectionActive()) {
            return $this->getNullCopyrightEntryWrapper();
        }
        $raw = $this->getRawCopyright($reader);

        $entry = null;
        if ($this->identifier_handler->isIdentifierValid($raw)) {
            $entry = $this->copyright_repo->getEntry(
                $this->identifier_handler->parseEntryIDFromIdentifier($raw)
            );
        } elseif ($raw === '') {
            $entry = $this->copyright_repo->getDefaultEntry();
        }

        if (is_null($entry)) {
            return $this->getNullCopyrightEntryWrapper();
        }
        return $this->getCopyrightEntryWrapper($entry);
    }

    public function readCustomCopyright(ReaderInterface $reader): string
    {
        $copyright = $this->getRawCopyright($reader);

        if (
            $this->isCopyrightSelectionActive() &&
            $this->identifier_handler->isIdentifierValid($copyright)
        ) {
            return '';
        }
        return $copyright;
    }

    public function prepareCreateOrUpdateOfCopyrightFromPreset(
        ManipulatorInterface $manipulator,
        string $copyright_id
    ): ManipulatorInterface {
        return $this->prepareCreateOrUpdateOfCopyright($manipulator, $copyright_id);
    }

    public function prepareCreateOrUpdateOfCustomCopyright(
        ManipulatorInterface $manipulator,
        string $custom_copyright
    ): ManipulatorInterface {
        return $this->prepareCreateOrUpdateOfCopyright($manipulator, $custom_copyright);
    }

    public function getAllCopyrightPresets(): \Generator
    {
        if (!$this->isCopyrightSelectionActive()) {
            return;
        }

        foreach ($this->copyright_repo->getAllEntries() as $entry) {
            yield $this->getCopyrightEntryWrapper($entry);
        }
    }

    public function getNonOutdatedCopyrightPresets(): \Generator
    {
        if (!$this->isCopyrightSelectionActive()) {
            return;
        }

        foreach ($this->copyright_repo->getActiveEntries() as $entry) {
            yield $this->getCopyrightEntryWrapper($entry);
        }
    }

    public function getCopyrightSearchClause(
        string $first_copyright_id,
        string ...$further_copyright_ids
    ): SearchClause {
        $selection_active = $this->isCopyrightSelectionActive();
        $default_entry_id = 0;
        if ($selection_active) {
            $default_entry_id = $this->copyright_repo->getDefaultEntry()->id();
        }

        $copyright_search_clauses = [];
        foreach ([$first_copyright_id, ...$further_copyright_ids] as $copyright_id) {
            $copyright_search_clauses[] = $this->search_clause_factory->getBasicClause(
                $this->getCopyrightDescriptionPath(),
                Mode::EQUALS,
                $copyright_id
            );

            if (
                !$selection_active ||
                !$this->identifier_handler->isIdentifierValid($copyright_id) ||
                $this->identifier_handler->parseEntryIDFromIdentifier($copyright_id) !== $default_entry_id
            ) {
                continue;
            }
            $copyright_search_clauses[] = $this->search_clause_factory->getBasicClause(
                $this->getCopyrightDescriptionPath(),
                Mode::EQUALS,
                ''
            );
        }

        return $this->search_clause_factory->getJoinedClauses(
            Operator::OR,
            ...$copyright_search_clauses
        );
    }

    protected function prepareCreateOrUpdateOfCopyright(
        ManipulatorInterface $manipulator,
        string $value
    ): ManipulatorInterface {
        return $manipulator->prepareCreateOrUpdate(
            $this->getCopyrightDescriptionPath(),
            $value
        );
    }

    protected function getRawCopyright(ReaderInterface $reader): string
    {
        return $reader->firstData($this->getCopyrightDescriptionPath())->value();
    }

    protected function getCopyrightDescriptionPath(): PathInterface
    {
        return $this->path_factory->custom()
                                  ->withNextStep('rights')
                                  ->withNextStep('description')
                                  ->withNextStep('string')
                                  ->get();
    }

    protected function getCopyrightEntryWrapper(EntryInterface $entry): CopyrightInterface
    {
        return new Copyright(
            $this->renderer,
            $this->identifier_handler,
            $entry
        );
    }

    protected function getNullCopyrightEntryWrapper(): CopyrightInterface
    {
        return new NullCopyright();
    }
}
