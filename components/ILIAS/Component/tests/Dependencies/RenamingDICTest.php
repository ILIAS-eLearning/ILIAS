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
use ILIAS\Component\Dependencies\RenamingDIC;

class RenamingDICTest extends TestCase
{
    public function testRenaming()
    {
        $wrapped = new class () implements \ArrayAccess {
            public array $data = [];

            public function offsetSet($id, $value): void
            {
                $this->data[] = [$id, $value];
            }

            public function offsetGet($id): null
            {
            }
            public function offsetExists($id): false
            {
            }
            public function offsetUnset($id): void
            {
            }
        };
        $wrapper = new RenamingDIC($wrapped);

        $wrapper["Foo"] = "Bar";
        $wrapper["Baz"] = "Bla";
        $wrapper["Foo"] = "Foobar";

        $expected = [["Foo_0", "Bar"], ["Baz_1", "Bla"], ["Foo_2", "Foobar"]];
        $this->assertEquals($expected, $wrapped->data);
    }
}
