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

use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;

class ilFileDataMailCollectionReferenceTest extends ilMailBaseTestCase
{
    public function testCollectionOfTheOwnersPoolIsReferenced(): void
    {
        $this->assertTrue($this->isReferenced('pool-rcid', 'pool-rcid'));
    }

    public function testOtherCollectionIsNotReferencedByThePool(): void
    {
        $this->assertFalse($this->isReferenced('pool-rcid', 'stage-rcid'));
    }

    public function testUserWithoutPoolDoesNotReferenceTheCollection(): void
    {
        $this->assertFalse($this->isReferenced(null, 'stage-rcid'));
    }

    private function isReferenced(?string $pool_rcid, string $rcid): bool
    {
        $queries = [];
        $db = $this->createMock(ilDBInterface::class);
        $db->method('queryF')->willReturnCallback(
            function (string $sql, array $types, array $values) use (&$queries): ilDBStatement {
                $queries[] = [$sql, $values];

                return $this->createStub(ilDBStatement::class);
            }
        );
        // neither a mail nor the compose stage references the collection
        $db->method('numRows')->willReturn(0);
        $db->method('fetchAssoc')->willReturn($pool_rcid === null ? null : ['value' => $pool_rcid]);

        $sut = new ReflectionClass(ilFileDataMail::class)->newInstanceWithoutConstructor();
        $sut->user_id = 4711;
        new ReflectionClass(ilFileDataMail::class)->getProperty('db')->setValue($sut, $db);

        $result = $sut->isCollectionReferenced(new ResourceCollectionIdentification($rcid));

        // The pool is looked up by primary key (usr_id, keyword), never searched by value
        [$pool_sql, $pool_values] = end($queries);
        $this->assertStringContainsString('WHERE usr_id = %s AND keyword = %s', $pool_sql);
        $this->assertSame([4711, 'mail_attachment_pool_rcid'], $pool_values);

        return $result;
    }
}
