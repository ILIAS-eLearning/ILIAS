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

use PHPUnit\Framework\TestCase;

class ilObjLearningSequenceTest extends TestCase
{
    public function testMapCopiedRefIdUsesClonedRefIdFromMapping(): void
    {
        $this->assertSame(
            456,
            $this->mapCopiedRefId(123, [123 => '456', 'meta_key' => 'ignored'])
        );
    }

    public function testMapCopiedRefIdDropsUnmappedAndInvalidRefIds(): void
    {
        $this->assertSame(0, $this->mapCopiedRefId(123, []));
        $this->assertSame(0, $this->mapCopiedRefId(0, [123 => 456]));
    }

    /**
     * @param array<int|string, mixed> $mapping
     */
    private function mapCopiedRefId(int $source_ref_id, array $mapping): int
    {
        return TestableObjLearningSequence::mapCopiedRefIdForTest($source_ref_id, $mapping);
    }
}

class TestableObjLearningSequence extends ilObjLearningSequence
{
    public function __construct()
    {
    }

    /**
     * @param array<int|string, mixed> $mapping
     */
    public static function mapCopiedRefIdForTest(int $source_ref_id, array $mapping): int
    {
        return parent::mapCopiedRefId($source_ref_id, $mapping);
    }
}
