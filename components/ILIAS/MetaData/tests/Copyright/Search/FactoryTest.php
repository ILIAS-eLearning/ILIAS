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

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Copyright\Identifiers\NullHandler;
use ILIAS\MetaData\Paths\NullFactory as NullPathFactory;
use ILIAS\MetaData\Search\Clauses\NullFactory as NullClauseFactory;
use ILIAS\MetaData\Search\Filters\NullFactory as NullFilterFactory;

class FactoryTest extends TestCase
{
    public function testGet(): void
    {
        $factory = new Factory(
            new NullFilterFactory(),
            new NullClauseFactory(),
            new NullPathFactory(),
            new NullHandler()
        );

        $searcher = $factory->get();

        $this->assertInstanceOf(Searcher::class, $searcher);
    }
}
