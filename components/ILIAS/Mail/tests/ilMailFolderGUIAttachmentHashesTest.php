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

use ILIAS\Mail\Attachments\MailAttachments;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;

/**
 * Regression: the single attachment download cast the MailAttachments object to an array
 * and passed its properties to md5(), which raised a TypeError.
 */
class ilMailFolderGUIAttachmentHashesTest extends ilMailBaseTestCase
{
    public function testIrssAttachmentHashesAreTakenFromTheListing(): void
    {
        $mail_file_data = $this->createMock(ilFileDataMail::class);
        $mail_file_data->method('getAttachmentListing')->willReturn([
            'a.pdf' => ['md5' => md5('a.pdf'), 'name' => 'a.pdf', 'size' => 1, 'ctime' => ''],
            'b.pdf' => ['md5' => md5('b.pdf'), 'name' => 'b.pdf', 'size' => 1, 'ctime' => ''],
        ]);

        $hashes = $this->invoke(
            ['attachments' => MailAttachments::fromIrss(new ResourceCollectionIdentification('rcid'))],
            $mail_file_data
        );

        $this->assertSame([md5('a.pdf'), md5('b.pdf')], $hashes);
    }

    public function testLegacyAttachmentHashesAreTheHashedFilenames(): void
    {
        $hashes = $this->invoke(
            ['attachments' => MailAttachments::fromLegacyFilenames(['a.pdf'])],
            $this->createStub(ilFileDataMail::class)
        );

        $this->assertSame([md5('a.pdf')], $hashes);
    }

    public function testMailsWithoutAttachmentsHaveNoHashes(): void
    {
        $this->assertSame([], $this->invoke(['attachments' => null], $this->createStub(ilFileDataMail::class)));
    }

    /**
     * @param array<string, mixed> $mail_data
     * @return list<string>
     */
    private function invoke(array $mail_data, ilFileDataMail $mail_file_data): array
    {
        $reflection = new ReflectionClass(ilMailFolderGUI::class);
        $gui = $reflection->newInstanceWithoutConstructor();

        return $reflection->getMethod('getAttachmentHashes')->invoke($gui, $mail_data, $mail_file_data);
    }
}
