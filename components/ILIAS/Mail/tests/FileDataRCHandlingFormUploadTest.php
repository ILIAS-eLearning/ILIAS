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
use ILIAS\FileUpload\Handler\FileInfoResult;
use ILIAS\ResourceStorage\Identification\ResourceCollectionIdentification;

class FileDataRCHandlingFormUploadTest extends ilMailBaseTestCase
{
    /**
     * Regression: a mail composed after scheduling or saving a draft must not inherit the stage attachments,
     * and removing all files in the form must remove all attachments.
     */
    public function testFormUploadWithoutFilesIgnoresStageAttachments(): void
    {
        $fdm = $this->createMock(ilFileDataMail::class);
        $fdm->expects($this->never())->method('restrictStageTo');

        $this->assertTrue($this->createSubject($fdm)->fromFormUpload([])->isEmpty());
    }

    public function testStageReducedToNothingMeansNoAttachments(): void
    {
        $fdm = $this->createMock(ilFileDataMail::class);
        $fdm->method('getAttachmentsTotalSizeLimit')->willReturn(null);
        $fdm->expects($this->once())->method('restrictStageTo')->with(['gone-rid'])->willReturn(null);

        $this->assertTrue($this->createSubject($fdm)->fromFormUpload(['gone-rid'])->isEmpty());
    }

    public function testFormUploadKeepsTheRestrictedStage(): void
    {
        $fdm = $this->createMock(ilFileDataMail::class);
        $fdm->method('getAttachmentsTotalSizeLimit')->willReturn(null);
        $fdm->expects($this->once())
            ->method('restrictStageTo')
            ->with(['rid-a'])
            ->willReturn(new ResourceCollectionIdentification('stage-rcid'));

        $result = $this->createSubject($fdm)->fromFormUpload(['rid-a']);

        $this->assertSame('stage-rcid', $result->rcid()->serialize());
    }

    private function createSubject(ilFileDataMail $fdm): FileDataRCHandlingFormUploadTestSubject
    {
        $info = $this->createStub(FileInfoResult::class);
        $info->method('getFileIdentifier')->willReturn('unknown');
        $upload_handler = $this->createStub(ilMailFormUploadHandlerGUI::class);
        $upload_handler->method('getInfoResult')->willReturn($info);

        return new FileDataRCHandlingFormUploadTestSubject(
            $fdm,
            $upload_handler,
            $this->createStub(ilLanguage::class),
            $this->createStub(Services::class)
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
