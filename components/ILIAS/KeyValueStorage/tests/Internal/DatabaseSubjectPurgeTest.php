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

namespace ILIAS\Tests\KeyValueStorage\Internal;

use ILIAS\KeyValueStorage\Internal\DatabaseSubjectPurge;
use ILIAS\KeyValueStorage\Subject\SubjectId;
use ILIAS\Tests\KeyValueStorage\InMemorySubjectRepository;
use PHPUnit\Framework\TestCase;

class DatabaseSubjectPurgeTest extends TestCase
{
    public function testPurgeRemovesThatSubject(): void
    {
        $subjects = new InMemorySubjectRepository();
        $subjects->entries['u42']['ui.storage']['sort'] = '"mine"';
        $subjects->entries['u7']['ui.storage']['sort'] = '"theirs"';

        (new DatabaseSubjectPurge($subjects))->purge(new SubjectId('u42'));

        self::assertSame(1, $subjects->remove_subject_calls);
        self::assertArrayNotHasKey('u42', $subjects->entries);
        self::assertSame(['sort' => '"theirs"'], $subjects->entries['u7']['ui.storage']);
    }

    public function testPurgeManyRemovesEachGivenSubject(): void
    {
        $subjects = new InMemorySubjectRepository();
        $subjects->entries['u42']['ui.storage']['sort'] = '"mine"';
        $subjects->entries['u7']['ui.storage']['sort'] = '"theirs"';
        $subjects->entries['u9']['ui.storage']['sort'] = '"kept"';

        (new DatabaseSubjectPurge($subjects))->purgeMany([
            new SubjectId('u42'),
            new SubjectId('u7'),
        ]);

        self::assertSame(1, $subjects->remove_subjects_calls);
        self::assertSame(['u9' => ['ui.storage' => ['sort' => '"kept"']]], $subjects->entries);
    }

    public function testPurgeManyWithAnEmptyListRemovesNothing(): void
    {
        $subjects = new InMemorySubjectRepository();
        $subjects->entries['u42']['ui.storage']['sort'] = '"mine"';

        (new DatabaseSubjectPurge($subjects))->purgeMany([]);

        self::assertSame(1, $subjects->remove_subjects_calls);
        self::assertSame(0, $subjects->remove_subject_calls);
        self::assertArrayHasKey('u42', $subjects->entries);
    }
}
