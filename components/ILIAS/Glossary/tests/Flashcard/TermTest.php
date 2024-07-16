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

namespace ILIAS\Glossary\Flashcard;

use PHPUnit\Framework\TestCase;

class TermTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
    }

    public function testProperties()
    {
        $term = new Term(
            1,
            2,
            3,
            4,
            "2024-01-01 01:00:00"
        );

        $this->assertSame(1, $term->getTermId());
        $this->assertSame(2, $term->getUserId());
        $this->assertSame(3, $term->getGloId());
        $this->assertSame(4, $term->getBoxNr());
        $this->assertSame("2024-01-01 01:00:00", $term->getLastAccess());
    }

    public function testPropertiesWithNoLastAccess()
    {
        $term = new Term(
            1,
            2,
            3,
            4
        );

        $this->assertSame(1, $term->getTermId());
        $this->assertSame(2, $term->getUserId());
        $this->assertSame(3, $term->getGloId());
        $this->assertSame(4, $term->getBoxNr());
        $this->assertNull($term->getLastAccess());
    }
}
