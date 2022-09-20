<?php

declare(strict_types=1);

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

use ILIAS\Filesystem\Filesystem;
use ILIAS\FileUpload\Collection\ImmutableStringMap;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\DTO\UploadResult;
use ILIAS\FileUpload\FileUpload;
use ILIAS\FileUpload\Location;

/**
 * Class ilTermsOfServiceAcceptanceHistoryCriteriaBagTest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentFormGUITest extends ilTermsOfServiceBaseTest
{
    public function testDocumentFormIsProperlyBuiltForNewDocuments(): void
    {
        $document = $this
            ->getMockBuilder(ilTermsOfServiceDocument::class)
            ->disableOriginalConstructor()
            ->addMethods(['getId'])
            ->getMock();

        $purifier = $this
            ->getMockBuilder(ilHtmlPurifierInterface::class)
            ->getMock();

        $user = $this
            ->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fs = $this
            ->getMockBuilder(Filesystem::class)
            ->getMock();

        $fu = $this
            ->getMockBuilder(FileUpload::class)
            ->getMock();

        $form = new ilTermsOfServiceDocumentFormGUI(
            $document,
            $purifier,
            $user,
            $fs,
            $fu,
            'action',
            'save',
            'cancel',
            true
        );

        $this->assertTrue(
            $form->getItemByPostVar('document')->getRequired(),
            'Failed asserting document upload is required for new documents'
        );

        $this->assertCount(
            2,
            $form->getCommandButtons(),
            'Failed asserting save and cancel buttons are given if form is editable'
        );
        $this->assertArrayHasKey(
            0,
            $form->getCommandButtons(),
            'Failed asserting save and cancel buttons are given if form is editable'
        );
        $this->assertArrayHasKey(
            1,
            $form->getCommandButtons(),
            'Failed asserting save and cancel buttons are given if form is editable'
        );
        $this->assertSame(
            'save',
            $form->getCommandButtons()[0]['cmd'],
            'Failed asserting save and cancel buttons are given if form is editable'
        );
        $this->assertSame(
            'cancel',
            $form->getCommandButtons()[1]['cmd'],
            'Failed asserting save and cancel buttons are given if form is editable'
        );

        $form = new ilTermsOfServiceDocumentFormGUI(
            $document,
            $purifier,
            $user,
            $fs,
            $fu,
            'action',
            'save',
            'cancel',
            false
        );

        $this->assertCount(
            1,
            $form->getCommandButtons(),
            'Failed asserting only cancel button is given if form is not editable'
        );
        $this->assertArrayHasKey(
            0,
            $form->getCommandButtons(),
            'Failed asserting only cancel button is given if form is not editable'
        );
        $this->assertSame(
            'cancel',
            $form->getCommandButtons()[0]['cmd'],
            'Failed asserting only cancel button is given if form is not editable'
        );
    }

    public function testFormForNewDocumentsCanBeSavedForValidInput(): void
    {
        $document = $this
            ->getMockBuilder(ilTermsOfServiceDocument::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchAllCriterionAssignments'])
            ->addMethods(['getId'])
            ->getMock();

        $document
            ->method('fetchAllCriterionAssignments');

        $purifier = $this
            ->getMockBuilder(ilHtmlPurifierInterface::class)
            ->getMock();

        $uploadResult = new UploadResult(
            'phpunit',
            1024,
            'text/xml',
            $this->getMockBuilder(ImmutableStringMap::class)->getMock(),
            new ProcessingStatus(ProcessingStatus::OK, 'uploaded'),
            '/tmp'
        );

        $user = $this
            ->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMock();

        $user
            ->expects($this->exactly(2))
            ->method('getId')
            ->willReturn(6);

        $fs = $this
            ->getMockBuilder(Filesystem::class)
            ->getMock();

        $fs
            ->expects($this->exactly(2))
            ->method('has')
            ->with('/agreements/' . $uploadResult->getName())
            ->willReturn(true);

        $fs
            ->expects($this->exactly(2))
            ->method('read')
            ->with('/agreements/' . $uploadResult->getName())
            ->willReturn('phpunit');

        $purifier
            ->expects($this->atLeast(1))
            ->method('purify')
            ->with('phpunit')
            ->willReturnArgument(0);

        $fs
            ->expects($this->exactly(2))
            ->method('delete')
            ->with('/agreements/' . $uploadResult->getName());

        $fu = $this
            ->getMockBuilder(FileUpload::class)
            ->onlyMethods([
                'moveFilesTo',
                'uploadSizeLimit',
                'register',
                'hasBeenProcessed',
                'hasUploads',
                'process',
                'getResults',
                'moveOneFileTo'
            ])
            ->getMock();

        $fu
            ->method('hasUploads')
            ->willReturn(true);

        $fu
            ->expects($this->exactly(2))
            ->method('hasBeenProcessed')
            ->willReturn(false);

        $fu
            ->expects($this->exactly(2))
            ->method('process');

        $fu
            ->expects($this->exactly(2))
            ->method('getResults')
            ->willReturn([
                0 => $uploadResult
            ]);

        $fu
            ->expects($this->exactly(2))
            ->method('moveOneFileTo')
            ->with(
                $uploadResult,
                '/agreements',
                Location::TEMPORARY,
                $this->isEmpty(),
                $this->isTrue()
            );

        $this->setGlobalVariable('upload', $fu);

        $documentConnector = $this->getMockBuilder(arConnector::class)->getMock();
        $criterionConnector = $this->getMockBuilder(arConnector::class)->getMock();

        $expectedSortingValueExistingDocuments = 10;

        $documentConnector
            ->expects($this->once())
            ->method('readSet')
            ->willReturnCallback(function () use ($expectedSortingValueExistingDocuments): array {
                return [
                    [
                        'id' => 666,
                        'title' => 'another',
                        'sorting' => $expectedSortingValueExistingDocuments - 1,
                    ]
                ];
            });
        $documentConnector->method('affectedRows')->willReturn(1);

        $criterionConnector
            ->expects($this->once())
            ->method('readSet')
            ->willReturn([]);

        arConnectorMap::register(new ilTermsOfServiceDocument(), $documentConnector);
        arConnectorMap::register(new ilTermsOfServiceDocumentCriterionAssignment(), $criterionConnector);
        arConnectorMap::register($document, $documentConnector);

        $form = $this->getMockBuilder(ilTermsOfServiceDocumentFormGUI::class)
                     ->setConstructorArgs([
                         $document,
                         $purifier,
                         $user,
                         $fs,
                         $fu,
                         'action',
                         'save',
                         'cancel',
                         true
                     ])
                     ->onlyMethods(['checkInput'])
                     ->getMock();

        $form
            ->expects($this->once())
            ->method('checkInput')
            ->willReturn(true);

        $_FILES['document'] = [];

        $form->setCheckInputCalled(true);

        $this->assertTrue($form->saveObject());
        $this->assertFalse($form->hasTranslatedError());
        $this->assertEmpty($form->getTranslatedError());
        $this->assertSame(
            $expectedSortingValueExistingDocuments,
            $document->getSorting(),
            'Failed asserting that the sorting of the new document equals the maximum incremented by one when other documents exist'
        );

        $documentConnector = $this->getMockBuilder(arConnector::class)->getMock();

        $documentConnector
            ->expects($this->once())
            ->method('readSet')
            ->willReturnCallback(function (): array {
                return [];
            });

        arConnectorMap::register(new ilTermsOfServiceDocument(), $documentConnector);

        $form = $this->getMockBuilder(ilTermsOfServiceDocumentFormGUI::class)
                     ->setConstructorArgs([
                         $document,
                         $purifier,
                         $user,
                         $fs,
                         $fu,
                         'action',
                         'save',
                         'cancel',
                         true
                     ])
                     ->onlyMethods(['checkInput'])
                     ->getMock();

        $form
            ->expects($this->once())
            ->method('checkInput')
            ->willReturn(true);

        $form->setCheckInputCalled(true);

        $this->assertTrue($form->saveObject());
        $this->assertFalse($form->hasTranslatedError());
        $this->assertEmpty($form->getTranslatedError());
        $this->assertSame(
            1,
            $document->getSorting(),
            'Failed asserting that the sorting of the new document equals 1 when no other document exists'
        );
    }

    public function testDocumentFormIsProperlyBuiltForExistingDocuments(): void
    {
        $document = $this
            ->getMockBuilder(ilTermsOfServiceDocument::class)
            ->disableOriginalConstructor()
            ->addMethods(['getId'])
            ->getMock();

        $document
            ->method('getId')
            ->willReturn(1);

        $purifier = $this
            ->getMockBuilder(ilHtmlPurifierInterface::class)
            ->getMock();

        $user = $this
            ->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMock();

        $fs = $this
            ->getMockBuilder(Filesystem::class)
            ->getMock();

        $fu = $this
            ->getMockBuilder(FileUpload::class)
            ->getMock();

        $form = new ilTermsOfServiceDocumentFormGUI(
            $document,
            $purifier,
            $user,
            $fs,
            $fu,
            'action',
            'save',
            'cancel',
            true
        );

        $this->assertFalse(
            $form->getItemByPostVar('document')->getRequired(),
            'Failed asserting document upload is not required for existing documents'
        );
    }

    public function testFormForExistingDocumentsCanBeSavedForValidInput(): void
    {
        $expectedSorting = 10;

        $document = $this
            ->getMockBuilder(ilTermsOfServiceDocument::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchAllCriterionAssignments'])
            ->getMock();

        $document->setId(4711);
        $document->setTitle('phpunit');
        $document->setSorting($expectedSorting);

        $purifier = $this
            ->getMockBuilder(ilHtmlPurifierInterface::class)
            ->getMock();

        $user = $this
            ->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getId'])
            ->getMock();

        $user
            ->expects($this->once())
            ->method('getId')
            ->willReturn(6);

        $fs = $this
            ->getMockBuilder(Filesystem::class)
            ->getMock();

        $fu = $this
            ->getMockBuilder(FileUpload::class)
            ->onlyMethods([
                'moveFilesTo',
                'uploadSizeLimit',
                'register',
                'hasBeenProcessed',
                'hasUploads',
                'process',
                'getResults',
                'moveOneFileTo'
            ])
            ->getMock();

        $fu
            ->method('hasUploads')
            ->willReturn(false);

        $this->setGlobalVariable('upload', $fu);

        $documentConnector = $this->getMockBuilder(arConnector::class)->getMock();
        $documentConnector->method('affectedRows')->willReturn(0);

        arConnectorMap::register(new ilTermsOfServiceDocument(), $documentConnector);
        arConnectorMap::register($document, $documentConnector);

        $form = $this->getMockBuilder(ilTermsOfServiceDocumentFormGUI::class)
                     ->setConstructorArgs([
                         $document,
                         $purifier,
                         $user,
                         $fs,
                         $fu,
                         'action',
                         'save',
                         'cancel',
                         true
                     ])
                     ->onlyMethods(['checkInput'])
                     ->getMock();

        $form
            ->expects($this->once())
            ->method('checkInput')
            ->willReturn(true);

        $form->setCheckInputCalled(true);

        $this->assertTrue($form->saveObject());
        $this->assertFalse($form->hasTranslatedError());
        $this->assertEmpty($form->getTranslatedError());
        $this->assertSame(
            $expectedSorting,
            $document->getSorting(),
            'Failed asserting that the sorting of the existing document has not been changed'
        );
    }

    public function testUploadIssuesAreHandledWhenDocumentFormIsSaved(): void
    {
        $lng = $this->getLanguageMock();

        $lng
            ->method('txt')
            ->willReturn('translation');

        $this->setGlobalVariable('lng', $lng);

        $document = $this
            ->getMockBuilder(ilTermsOfServiceDocument::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchAllCriterionAssignments'])
            ->addMethods(['getId'])
            ->getMock();

        $purifier = $this
            ->getMockBuilder(ilHtmlPurifierInterface::class)
            ->getMock();

        $user = $this
            ->getMockBuilder(ilObjUser::class)
            ->disableOriginalConstructor()
            ->getMock();

        $fu = $this
            ->getMockBuilder(FileUpload::class)
            ->onlyMethods([
                'moveFilesTo',
                'uploadSizeLimit',
                'register',
                'hasBeenProcessed',
                'hasUploads',
                'process',
                'getResults',
                'moveOneFileTo'
            ])
            ->getMock();

        $fu
            ->expects($this->exactly(3))
            ->method('hasUploads')
            ->willReturn(true);

        $fu
            ->expects($this->exactly(3))
            ->method('hasBeenProcessed')
            ->willReturn(false);

        $fu
            ->expects($this->exactly(3))
            ->method('process');

        $uploadResult = new UploadResult(
            'phpunit',
            1024,
            'text/xml',
            $this->getMockBuilder(ImmutableStringMap::class)->getMock(),
            new ProcessingStatus(ProcessingStatus::OK, 'uploaded'),
            '/tmp'
        );

        $uploadFailingResult = new UploadResult(
            'phpunit',
            1024,
            'text/xml',
            $this->getMockBuilder(ImmutableStringMap::class)->getMock(),
            new ProcessingStatus(ProcessingStatus::REJECTED, 'not uploaded'),
            '/tmp'
        );

        $fu
            ->expects($this->exactly(3))
            ->method('getResults')
            ->willReturnOnConsecutiveCalls(
                [false],
                [0 => $uploadFailingResult],
                [0 => $uploadResult]
            );

        $fs = $this
            ->getMockBuilder(Filesystem::class)
            ->getMock();

        $fs
            ->expects($this->once())
            ->method('has')
            ->with('/agreements/' . $uploadResult->getName())
            ->willReturn(false);

        $this->setGlobalVariable('upload', $fu);

        $documentConnector = $this->getMockBuilder(arConnector::class)->getMock();

        arConnectorMap::register(new ilTermsOfServiceDocument(), $documentConnector);
        arConnectorMap::register($document, $documentConnector);

        $form = $this->getMockBuilder(ilTermsOfServiceDocumentFormGUI::class)
                     ->setConstructorArgs([
                         $document,
                         $purifier,
                         $user,
                         $fs,
                         $fu,
                         'action',
                         'save',
                         'cancel',
                         true
                     ])
                     ->onlyMethods(['checkInput'])
                     ->getMock();

        $form
            ->expects($this->once())
            ->method('checkInput')
            ->willReturn(true);

        $form->setCheckInputCalled(true);

        $this->assertFalse($form->saveObject());
        $this->assertTrue($form->hasTranslatedError());
        $this->assertNotEmpty($form->getTranslatedError());

        $form = $this->getMockBuilder(ilTermsOfServiceDocumentFormGUI::class)
                     ->setConstructorArgs([
                         $document,
                         $purifier,
                         $user,
                         $fs,
                         $fu,
                         'action',
                         'save',
                         'cancel',
                         true
                     ])
                     ->onlyMethods(['checkInput'])
                     ->getMock();

        $form
            ->expects($this->once())
            ->method('checkInput')
            ->willReturn(true);

        $form->setCheckInputCalled(true);

        $this->assertFalse($form->saveObject());
        $this->assertTrue($form->hasTranslatedError());
        $this->assertNotEmpty($form->getTranslatedError());

        $form = $this->getMockBuilder(ilTermsOfServiceDocumentFormGUI::class)
                     ->setConstructorArgs([
                         $document,
                         $purifier,
                         $user,
                         $fs,
                         $fu,
                         'action',
                         'save',
                         'cancel',
                         true
                     ])
                     ->onlyMethods(['checkInput'])
                     ->getMock();

        $form
            ->expects($this->once())
            ->method('checkInput')
            ->willReturn(true);

        $form->setCheckInputCalled(true);

        $this->assertFalse($form->saveObject());
        $this->assertTrue($form->hasTranslatedError());
        $this->assertNotEmpty($form->getTranslatedError());
    }
}
