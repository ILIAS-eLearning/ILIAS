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

class BoxTest extends TestCase
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
        $box = new Box(
            1,
            2,
            3,
            "2024-01-01 01:00:00"
        );

        $this->assertSame(1, $box->getBoxNr());
        $this->assertSame(2, $box->getUserId());
        $this->assertSame(3, $box->getGloId());
        $this->assertSame("2024-01-01 01:00:00", $box->getLastAccess());
    }

    public function testPropertiesWithNoLastAccess()
    {
        $box = new Box(
            1,
            2,
            3
        );

        $this->assertSame(1, $box->getBoxNr());
        $this->assertSame(2, $box->getUserId());
        $this->assertSame(3, $box->getGloId());
        $this->assertNull($box->getLastAccess());
    }
}
