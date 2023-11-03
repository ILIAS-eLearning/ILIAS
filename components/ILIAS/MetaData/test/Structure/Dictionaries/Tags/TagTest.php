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

class TagTest extends TestCase
{
    protected function getTag(int ...$indices): Tag
    {
        return new class (...$indices) extends Tag {
            public function __construct(int ...$indices)
            {
                parent::__construct(...$indices);
            }
        };
    }

    public function testIndices(): void
    {
        $indices = [12, 0, -3, 99999];
        $tag = $this->getTag(...$indices);
        $this->assertSame(
            $indices,
            iterator_to_array($tag->indices())
        );
    }

    public function testIsRestrictedToIndices(): void
    {
        $tag_without_indices = $this->getTag();
        $tag_with_indices = $this->getTag(6);

        $this->assertFalse($tag_without_indices->isRestrictedToIndices());
        $this->assertTrue($tag_with_indices->isRestrictedToIndices());
    }
}
