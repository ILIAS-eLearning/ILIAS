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

use ILIAS\MetaData\Search\Filters\FactoryInterface as SearchFilterFactory;
use ILIAS\MetaData\Search\Clauses\FactoryInterface as SearchClauseFactory;
use ILIAS\MetaData\Paths\FactoryInterface as PathFactory;
use ILIAS\MetaData\Copyright\Identifiers\HandlerInterface as CopyrightIdentifierHandler;

class Factory implements FactoryInterface
{
    protected SearchFilterFactory $search_filter_factory;
    protected SearchClauseFactory $search_clause_factory;
    protected PathFactory $path_factory;
    protected CopyrightIdentifierHandler $copyright_identifier_handler;

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

    public function get(): SearcherInterface
    {
        return new Searcher(
            $this->search_filter_factory,
            $this->search_clause_factory,
            $this->path_factory,
            $this->copyright_identifier_handler
        );
    }
}
