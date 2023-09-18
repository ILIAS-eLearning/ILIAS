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

namespace ILIAS\Tests\Services\Database\Integrity;

use PHPUnit\Framework\TestCase;
use ILIAS\Services\Database\Integrity\Ignore;

class IgnoreTest extends TestCase
{
    public function testConstruct(): void
    {
        $ignore = new Ignore();
        $this->assertInstanceOf(Ignore::class, $ignore);
    }

    public function testValues(): void
    {
        $ignore = new Ignore('a', 'b', 'c');
        $this->assertEquals(['!= a', '!= b', '!= c'], $ignore->values());
    }

    public function testValuesWithNull(): void
    {
        $ignore = new Ignore('a', null, 'c');
        $this->assertEquals(['!= a', 'IS NOT NULL', '!= c'], $ignore->values());
    }
}
