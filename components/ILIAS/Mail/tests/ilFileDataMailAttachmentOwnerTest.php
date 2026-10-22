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

class ilFileDataMailAttachmentOwnerTest extends ilMailBaseTestCase
{
    public function testAttachmentsOfMailsOfOtherUsersAreNotFound(): void
    {
        $queries = [];
        $db = $this->createStub(ilDBInterface::class);
        $db->method('queryF')->willReturnCallback(
            function (string $sql, array $types, array $values) use (&$queries): ilDBStatement {
                $queries[] = [$sql, $values];

                return $this->createStub(ilDBStatement::class);
            }
        );
        // The mail belongs to another user: neither the collection nor a directory is returned
        $db->method('fetchAssoc')->willReturn(null);
        $db->method('numRows')->willReturn(0);

        $sut = new ReflectionClass(ilFileDataMail::class)->newInstanceWithoutConstructor();
        $sut->user_id = 4711;
        new ReflectionClass(ilFileDataMail::class)->getProperty('db')->setValue($sut, $db);

        try {
            $sut->getAttachmentPathAndFilenameByMd5Hash(md5('report.pdf'), 42);
            $this->fail('An attachment of a foreign mail must not be found');
        } catch (OutOfBoundsException) {
        }

        foreach ($queries as [$sql, $values]) {
            $this->assertStringContainsString('mail.user_id = %s', $sql);
            $this->assertSame([42, 4711], $values);
        }
    }
}
