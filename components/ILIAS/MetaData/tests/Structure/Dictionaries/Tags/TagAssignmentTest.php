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

namespace ILIAS\MetaData\Structure\Dictionaries\Tags;

use PHPUnit\Framework\TestCase;
use ILIAS\MetaData\Paths\PathInterface;
use ILIAS\MetaData\Paths\NullPath;

class TagAssignmentTest extends TestCase
{
    protected function getPath(string $name): PathInterface
    {
        return new class ($name) extends NullPath {
            public function __construct(protected string $name)
            {
            }

            public function toString(): string
            {
                return $this->name;
            }
        };
    }

    public function testTag(): void
    {
        $tag = new NullTag();
        $assignment = new TagAssignment($this->getPath('name'), $tag);

        $this->assertSame(
            $tag,
            $assignment->tag()
        );
    }

    public function testMatchesPath(): void
    {
        $first_path = $this->getPath('first path');
        $second_path = $this->getPath('second path');
        $assignment = new TagAssignment($first_path, new NullTag());

        $this->assertTrue($assignment->matchesPath($first_path));
        $this->assertFalse($assignment->matchesPath($second_path));
    }
}
