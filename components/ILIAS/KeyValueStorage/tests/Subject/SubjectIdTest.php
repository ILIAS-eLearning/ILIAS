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

namespace ILIAS\Tests\KeyValueStorage\Subject;

use ILIAS\KeyValueStorage\Subject\SubjectId;
use PHPUnit\Framework\TestCase;

class SubjectIdTest extends TestCase
{
    public function testALowercaseIdentifierIsKeptAsTheStorageSegment(): void
    {
        $id = new SubjectId('u42');

        self::assertSame('u42', $id->storageSegment());
    }

    public function testAnEmptySegmentIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Subject segment must not be empty.');

        new SubjectId('');
    }

    public function testASegmentLongerThanTheLimitIsRejected(): void
    {
        $segment = str_repeat('a', SubjectId::MAX_LENGTH + 1);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Subject segment must not exceed ' . SubjectId::MAX_LENGTH . ' characters, got '
            . (SubjectId::MAX_LENGTH + 1) . '.'
        );

        new SubjectId($segment);
    }

    public function testAMultibyteSegmentLongerThanTheLimitIsRejectedByCharacterCount(): void
    {
        $segment = str_repeat('ä', SubjectId::MAX_LENGTH + 1);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'Subject segment must not exceed ' . SubjectId::MAX_LENGTH . ' characters, got '
            . (SubjectId::MAX_LENGTH + 1) . '.'
        );

        new SubjectId($segment);
    }

    public function testASegmentAtTheLimitIsAccepted(): void
    {
        $segment = str_repeat('a', SubjectId::MAX_LENGTH);

        self::assertSame($segment, (new SubjectId($segment))->storageSegment());
    }

    public function testASegmentThatIsNotALowercaseIdentifierIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Subject segment must be a lowercase identifier, got "U42".');

        new SubjectId('U42');
    }

    public function testASegmentThatStartsWithADigitIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Subject segment must be a lowercase identifier, got "42".');

        new SubjectId('42');
    }
}
