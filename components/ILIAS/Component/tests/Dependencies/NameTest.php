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

namespace ILIAS\Component\Tests\Dependencies;

use PHPUnit\Framework\TestCase;
use ILIAS\Component\Dependencies\Name;

class NameTest extends TestCase
{
    /**
     * @dataProvider properNames
     */
    public function testProperNames(string $name): void
    {
        $n = new Name($name);
        $this->assertEquals($name, (string) $n);
    }

    /**
     * @dataProvider improperNames
     */
    public function testImproperNames(string $name): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $n = new Name($name);
    }

    public function properNames(): array
    {
        return [
            [\ILIAS\Component\Tests::class],
            [\Foo\Bar\Baz::class]
        ];
    }

    public function improperNames(): array
    {
        return [
            ['ILIAS \Component\Tests'],
            [\ILIAS\Component::class]
        ];
    }
}
