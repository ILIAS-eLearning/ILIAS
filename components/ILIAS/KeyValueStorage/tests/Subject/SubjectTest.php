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

use ILIAS\KeyValueStorage\Subject\Subject;
use ILIAS\KeyValueStorage\Subject\SubjectId;
use PHPUnit\Framework\TestCase;

class SubjectTest extends TestCase
{
    public function testAnonymousHasNoId(): void
    {
        $subject = Subject::anonymous();

        self::assertTrue($subject->isAnonymous());
        self::assertFalse($subject->isNamed());
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Subject has no id.');

        $subject->id();
    }

    public function testANamedSubjectReturnsTheIdItWasBuiltWith(): void
    {
        $id = new SubjectId('u42');
        $subject = Subject::named($id);

        self::assertFalse($subject->isAnonymous());
        self::assertTrue($subject->isNamed());
        self::assertSame($id, $subject->id());
    }
}
