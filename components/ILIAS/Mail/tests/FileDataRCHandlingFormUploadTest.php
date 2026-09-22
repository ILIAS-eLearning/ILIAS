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

use ILIAS\ResourceStorage\Services;
use ILIAS\Mail\Attachments\MailAttachments;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;

class FileDataRCHandlingFormUploadTest extends ilMailBaseTestCase
{
    public function testFormUploadWithoutNewFilesKeepsStageAttachments(): void
    {
        $stage = MailAttachments::fromIrss(new ResourceCollectionIdentification('stage-rcid'));
        $subject = $this->createSubject();

        $result = $subject->fromFormUpload([], $stage);

        $this->assertSame($stage, $result);
    }

    public function testFormUploadWithoutNewFilesAndWithoutStageReturnsEmpty(): void
    {
        $subject = $this->createSubject();

        $result = $subject->fromFormUpload([], null);

        $this->assertTrue($result->isEmpty());
    }

    private function createSubject(): FileDataRCHandlingFormUploadTestSubject
    {
        return new FileDataRCHandlingFormUploadTestSubject(
            $this->createMock(ilFileDataMail::class),
            $this->createMock(ilMailFormUploadHandlerGUI::class),
            $this->createMock(ilLanguage::class),
            $this->createMock(Services::class)
        );
    }
}

class FileDataRCHandlingFormUploadTestSubject
{
    use FileDataRCHandling {
        attachmentsFromFormUpload as public fromFormUpload;
    }

    public function __construct(
        public ilFileDataMail $fdm,
        public ilMailFormUploadHandlerGUI $upload_handler,
        public ilLanguage $lng,
        public Services $storage
    ) {
    }
}
