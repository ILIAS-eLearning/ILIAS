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

namespace ILIAS\MetaData\Copyright\Search;

use ILIAS\MetaData\Elements\RessourceID\RessourceIDInterface;
use ILIAS\MetaData\Repository\RepositoryInterface as LOMRepository;
use ILIAS\MetaData\Search\Filters\FactoryInterface as SearchFilterFactory;
use ILIAS\MetaData\Search\Clauses\FactoryInterface as SearchClauseFactory;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\Copyright\Identifiers\HandlerInterface as CopyrightIdentifierHandler;
use ILIAS\MetaData\Search\Clauses\Mode;
use ILIAS\MetaData\Search\Clauses\Operator;
use ILIAS\MetaData\Search\Filters\Placeholder;

class Searcher implements SearcherInterface
{
    protected SearchFilterFactory $search_filter_factory;
    protected SearchClauseFactory $search_clause_factory;
    protected PathFactory $path_factory;
    protected CopyrightIdentifierHandler $copyright_identifier_handler;

    /**
     * @var string[]
     */
    protected array $types = [];
    protected bool $restricted_to_repo_objects = false;

    public function __construct(
        SearchFilterFactory $search_filter_factory,
        SearchClauseFactory $search_clause_factory,
        PathFactory $path_factory,
        CopyrightIdentifierHandler $copyright_identifier_handler,
    ) {
        $this->search_filter_factory = $search_filter_factory;
        $this->search_clause_factory = $search_clause_factory;
        $this->path_factory = $path_factory;
        $this->copyright_identifier_handler = $copyright_identifier_handler;
    }

    /**
     * @return RessourceIDInterface[]
     */
    public function search(
        LOMRepository $lom_repository,
        int $first_entry_id,
        int ...$further_entry_ids
    ): \Generator {
        $path_to_copyright = $this->path_factory->custom()
                                                ->withNextStep('rights')
                                                ->withNextStep('description')
                                                ->withNextStep('string')
                                                ->get();

        $copyright_search_clauses = [];
        foreach ([$first_entry_id, ...$further_entry_ids] as $entry_id) {
            $copyright_search_clauses[] = $this->search_clause_factory->getBasicClause(
                $path_to_copyright,
                Mode::EQUALS,
                $this->copyright_identifier_handler->buildIdentifierFromEntryID($entry_id)
            );
        }
        $full_search_clause = $this->search_clause_factory->getJoinedClauses(
            Operator::OR,
            ...$copyright_search_clauses
        );

        $filters = [];
        foreach ($this->types as $type) {
            $filters[] = $this->search_filter_factory->get(
                Placeholder::ANY,
                $this->restricted_to_repo_objects ? Placeholder::OBJ_ID : Placeholder::ANY,
                $type
            );
        }
        if (empty($filters) && $this->restricted_to_repo_objects) {
            $filters[] = $this->search_filter_factory->get(
                Placeholder::ANY,
                Placeholder::OBJ_ID,
                Placeholder::ANY
            );
        }

        yield from $lom_repository->searchMD($full_search_clause, null, null, ...$filters);
    }

    public function withRestrictionToRepositoryObjects(bool $restricted): SearcherInterface
    {
        $clone = clone $this;
        $clone->restricted_to_repo_objects = $restricted;
        return $clone;
    }

    public function withAdditionalTypeFilter(string $type): SearcherInterface
    {
        $clone = clone $this;
        $clone->types[] = $type;
        return $clone;
    }
}
