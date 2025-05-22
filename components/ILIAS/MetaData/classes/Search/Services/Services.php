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

namespace ILIAS\MetaData\Search\Services;

use ILIAS\MetaData\Search\Clauses\FactoryInterface as ClauseFactoryInterface;
use ILIAS\MetaData\Search\Filters\FactoryInterface as FilterFactoryInterface;
use ILIAS\MetaData\Search\Clauses\Factory as ClauseFactory;
use ILIAS\MetaData\Search\Filters\Factory as FilterFactory;

class Services
{
    protected ClauseFactoryInterface $search_clause_factory;
    protected FilterFactoryInterface $search_filter_factory;

    public function searchClauseFactory(): ClauseFactoryInterface
    {
        if (isset($this->search_clause_factory)) {
            return $this->search_clause_factory;
        }
        return $this->search_clause_factory = new ClauseFactory();
    }

    public function searchFilterFactory(): FilterFactoryInterface
    {
        if (isset($this->search_filter_factory)) {
            return $this->search_filter_factory;
        }
        return $this->search_filter_factory = new FilterFactory();
    }
}
