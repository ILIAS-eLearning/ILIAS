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

use ILIAS\Mail\Cron\ExpiredOrOrphanedMails\MailDeletionHandler;
use ILIAS\Mail\Cron\ExpiredOrOrphanedMails\ExpiredOrOrphanedMailsCollector;

class MailDeletionHandlerAttachmentPathTest extends ilMailBaseTestCase
{
    /**
     * Regression: IRSS based `mail_attachment` rows have an empty path. Deleting their "directory"
     * resolved to the client's complete mail directory.
     */
    public function testEmptyPathsAreNeverDeletable(): void
    {
        $captured_sql = '';
        $db = $this->createMock(ilDBInterface::class);
        $db->method('prepare')->willReturn($this->createStub(ilDBStatement::class));
        $db->method('in')->willReturn('mail_id IN (1,2)');
        $db->method('query')->willReturnCallback(
            function (string $sql) use (&$captured_sql): ilDBStatement {
                $captured_sql = $sql;

                return $this->createStub(ilDBStatement::class);
            }
        );
        $db->method('execute')->willReturn($this->createStub(ilDBStatement::class));
        $db->method('fetchAssoc')->willReturnOnConsecutiveCalls(
            ['path' => '', 'cnt_mail_ids' => 2],
            ['cnt' => 2],
            ['path' => '/', 'cnt_mail_ids' => 1],
            ['cnt' => 1],
            ['path' => '6_1_2', 'cnt_mail_ids' => 1],
            ['cnt' => 1],
            null
        );

        $collector = $this->createStub(ExpiredOrOrphanedMailsCollector::class);
        $collector->method('mailIdsToDelete')->willReturn([1, 2]);

        $handler = new MailDeletionHandler(
            $this->createStub(ilMailCronOrphanedMails::class),
            $collector,
            $db,
            $this->createStub(ilSetting::class),
            $this->createStub(ilLogger::class),
            static function (string $directory): void {
            }
        );

        $paths = new ReflectionClass($handler)
            ->getMethod('determineDeletableAttachmentPaths')
            ->invoke($handler);

        $this->assertSame(['6_1_2'], $paths);
        $this->assertStringContainsString('path IS NOT NULL AND path != ""', $captured_sql);
    }
}
