<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Filesystem\Filesystems;
use ILIAS\FileUpload\FileUpload;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\UI\Component\Button\Standard;
use ILIAS\UI\Component\MessageBox\MessageBox;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ilTermsOfServiceDocumentGUITest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceDocumentGUITest extends ilTermsOfServiceBaseTest
{
    /** @var MockObject|ilTermsOfServiceTableDataProviderFactory */
    protected $tableDataProviderFactory;

    /** @var MockObject|ilObjTermsOfService */
    protected $tos;

    /** @var MockObject|ilGlobalPageTemplate */
    protected $tpl;

    /** @var MockObject|ilCtrl */
    protected $ctrl;

    /** @var MockObject|ilLanguage */
    protected $lng;

    /** @var MockObject|ilRbacSystem */
    protected $rbacsystem;

    /** @var MockObject|ilErrorHandling */
    protected $error;

    /** @var MockObject|ilObjUser */
    protected $user;

    /** @var MockObject|ilLogger */
    protected $log;

    /** @var MockObject|Factory */
    protected $uiFactory;

    /** @var MockObject|Renderer */
    protected $uiRenderer;

    /** @var MockObject|ILIAS\HTTP\GlobalHttpState */
    protected $httpState;

    /** @var MockObject|ilToolbarGUI */
    protected $toolbar;

    /** @var MockObject|FileUpload */
    protected $fileUpload;

    /** @var MockObject|Filesystems */
    protected $fileSystems;

    /** @var MockObject|ilTermsOfServiceCriterionTypeFactoryInterface */
    protected $criterionTypeFactory;

    /** @var MockObject|ilHtmlPurifierInterface */
    protected $documentPurifier;

    /**
     * @throws ReflectionException
     */
    public function setUp() : void
    {
        parent::setUp();

        $this->tos = $this->getMockBuilder(ilObjTermsOfService::class)->disableOriginalConstructor()->getMock();
        $this->criterionTypeFactory = $this->getMockBuilder(ilTermsOfServiceCriterionTypeFactoryInterface::class)->disableOriginalConstructor()->getMock();
        $this->tpl = $this->getMockBuilder(ilGlobalPageTemplate::class)->disableOriginalConstructor()->getMock();
        $this->ctrl = $this->getMockBuilder(ilCtrl::class)->disableOriginalConstructor()->getMock();
        $this->lng = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $this->rbacsystem = $this->getMockBuilder(ilRbacSystem::class)->disableOriginalConstructor()->getMock();
        $this->error = $this->getMockBuilder(ilErrorHandling::class)->disableOriginalConstructor()->getMock();
        $this->user = $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock();
        $this->log = $this->getMockBuilder(ilLogger::class)->disableOriginalConstructor()->getMock();
        $this->toolbar = $this->getMockBuilder(ilToolbarGUI::class)->disableOriginalConstructor()->getMock();
        $this->httpState = $this->getMockBuilder(GlobalHttpState::class)->getMock();
        $this->uiFactory = $this->getMockBuilder(Factory::class)->disableOriginalConstructor()->getMock();
        $this->uiRenderer = $this->getMockBuilder(Renderer::class)->disableOriginalConstructor()->getMock();
        $this->fileSystems = $this->getMockBuilder(Filesystems::class)->getMock();
        $this->fileUpload = $this->getMockBuilder(FileUpload::class)->getMock();
        $this->tableDataProviderFactory = $this->getMockBuilder(ilTermsOfServiceTableDataProviderFactory::class)->disableOriginalConstructor()->getMock();
        $this->documentPurifier = $this->getMockBuilder(ilHtmlPurifierInterface::class)->getMock();
    }

    /**
     * @return string[]
     */
    public function commandProvider() : array
    {
        return [
            ['default_____read', [false]],
            ['confirmReset', [true, false]],
            ['reset', [true, false]],
            ['saveAddDocumentForm', [true, false]],
            ['showAddDocumentForm', [true, false]],
            ['saveEditDocumentForm', [true, false]],
            ['showEditDocumentForm', [true, false]],
            ['deleteDocuments', [true, false]],
            ['saveDocumentSorting', [true, false]],
            ['showAttachCriterionForm', [true, false]],
            ['saveAttachCriterionForm', [true, false]],
            ['showChangeCriterionForm', [true, false]],
            ['saveChangeCriterionForm', [true, false]],
            ['detachCriterionAssignment', [true, false]]
        ];
    }

    /**
     * @dataProvider commandProvider
     * @param string $command
     * @param bool[] $accessResults
     */
    public function testAccessDeniedErrorIsRaisedWhenPermissionsAreMissing(string $command, array $accessResults) : void
    {
        $this->ctrl
            ->expects($this->once())
            ->method('getCmd')
            ->willReturn($command);

        $accessResultCounter = 0;
        $this->rbacsystem
            ->expects($this->exactly(count($accessResults)))
            ->method('checkAccess')
            ->willReturnCallback(function () use ($accessResults, &$accessResultCounter) {
                $result = $accessResults[$accessResultCounter];

                $accessResultCounter++;

                return $result;
            });

        $this->error
            ->expects($this->any())
            ->method('raiseError')
            ->willThrowException(new ilException('no_permission'));

        $gui = new ilTermsOfServiceDocumentGUI(
            $this->tos,
            $this->criterionTypeFactory,
            $this->tpl,
            $this->user,
            $this->ctrl,
            $this->lng,
            $this->rbacsystem,
            $this->error,
            $this->log,
            $this->toolbar,
            $this->httpState,
            $this->uiFactory,
            $this->uiRenderer,
            $this->fileSystems,
            $this->fileUpload,
            $this->tableDataProviderFactory,
            $this->documentPurifier
        );

        $this->expectException(ilException::class);

        $gui->executeCommand();
    }

    /**
     * @throws ReflectionException
     */
    public function testLastResetDateIsDisplayedInMessageBoxWhenAgreementsHaveBeenResetAtLeastOnce() : void
    {
        $this->setGlobalVariable('lng', clone $this->lng);
        $this->setGlobalVariable('ilUser', clone $this->user);

        $lastResetDate = $this->getMockBuilder(ilDate::class)
                              ->disableOriginalConstructor()
                              ->getMock();

        $date = new DateTime();

        $lastResetDate->setDate($date->getTimestamp(), IL_CAL_UNIX);

        $lastResetDate
            ->expects($this->any())
            ->method('get')
            ->willReturn([
                'seconds' => (int) $date->format('s'),
                'minutes' => (int) $date->format('i'),
                'hours' => (int) $date->format('G'),
                'mday' => (int) $date->format('j'),
                'wday' => (int) $date->format('w'),
                'mon' => (int) $date->format('n'),
                'year' => (int) $date->format('Y'),
                'yday' => (int) $date->format('z'),
                'weekday' => $date->format('l'),
                'month' => $date->format('F'),
                'isoday' => (int) $date->format('N')
            ]);

        $lastResetDate
            ->expects($this->any())
            ->method('isNull')
            ->willReturn(true); // Required because of \ilDatePresentation static calls

        $this->tos
            ->expects($this->any())
            ->method('getLastResetDate')
            ->willReturn($lastResetDate);

        $this->ctrl
            ->expects($this->once())
            ->method('getCmd')
            ->willReturn('getResetMessageBoxHtml');

        $this->ctrl
            ->expects($this->once())
            ->method('getLinkTarget')
            ->with($this->isInstanceOf(ilTermsOfServiceDocumentGUI::class), 'confirmReset')
            ->willReturn('confirmReset');

        $this->rbacsystem
            ->expects($this->any())
            ->method('checkAccess')
            ->willReturn(true);

        $buttonFactory = $this->getMockBuilder(\ILIAS\UI\Component\Button\Factory::class)->getMock();
        $button = $this->getMockBuilder(Standard::class)->getMock();

        $buttonFactory
            ->expects($this->once())
            ->method('standard')
            ->with($this->isType('string'), $this->equalTo('confirmReset'))
            ->willReturn($button);

        $this->uiFactory
            ->expects($this->once())
            ->method('button')
            ->willReturn($buttonFactory);

        $messageBoxFactory = $this->getMockBuilder(\ILIAS\UI\Component\MessageBox\Factory::class)->getMock();
        $info = $this->getMockBuilder(MessageBox::class)->getMock();

        $messageBoxFactory
            ->expects($this->once())
            ->method('info')
            ->with($this->stringContains('Some date:'))
            ->willReturn($info);

        $info
            ->expects($this->once())
            ->method('withButtons')
            ->with($this->countOf(1));

        $this->uiFactory
            ->expects($this->once())
            ->method('messageBox')
            ->willReturn($messageBoxFactory);

        $this->error
            ->expects($this->never())
            ->method('raiseError');

        $this->uiRenderer
            ->expects($this->atLeast(1))
            ->method('render')
            ->willReturn('');

        $this->lng
            ->expects($this->exactly(2))
            ->method('txt')
            ->willReturnOnConsecutiveCalls(
                'Some date: %s',
                'Some button text'
            );

        $gui = new ilTermsOfServiceDocumentGUI(
            $this->tos,
            $this->criterionTypeFactory,
            $this->tpl,
            $this->user,
            $this->ctrl,
            $this->lng,
            $this->rbacsystem,
            $this->error,
            $this->log,
            $this->toolbar,
            $this->httpState,
            $this->uiFactory,
            $this->uiRenderer,
            $this->fileSystems,
            $this->fileUpload,
            $this->tableDataProviderFactory,
            $this->documentPurifier
        );

        $gui->executeCommand();
    }

    /**
     * @throws ReflectionException
     */
    public function testNoLastResetDateIsDisplayedInMessageBoxWhenAgreementsHaveBeenResetAtLeastOnce() : void
    {
        $this->setGlobalVariable('lng', clone $this->lng);
        $this->setGlobalVariable('ilUser', clone $this->user);

        $lastResetDate = $this->getMockBuilder(ilDate::class)
                              ->disableOriginalConstructor()
                              ->getMock();

        $lastResetDate
            ->expects($this->any())
            ->method('get')
            ->willReturn(0);
        $lastResetDate
            ->expects($this->any())
            ->method('isNull')
            ->willReturn(true);

        $this->tos
            ->expects($this->any())
            ->method('getLastResetDate')
            ->willReturn($lastResetDate);

        $this->ctrl
            ->expects($this->once())
            ->method('getCmd')
            ->willReturn('getResetMessageBoxHtml');

        $this->ctrl
            ->expects($this->once())
            ->method('getLinkTarget')
            ->with($this->isInstanceOf(ilTermsOfServiceDocumentGUI::class), 'confirmReset')
            ->willReturn('confirmReset');

        $this->rbacsystem
            ->expects($this->any())
            ->method('checkAccess')
            ->willReturn(true);

        $buttonFactory = $this->getMockBuilder(\ILIAS\UI\Component\Button\Factory::class)->getMock();
        $button = $this->getMockBuilder(Standard::class)->getMock();

        $buttonFactory
            ->expects($this->once())
            ->method('standard')
            ->with($this->isType('string'), $this->equalTo('confirmReset'))
            ->willReturn($button);

        $this->uiFactory
            ->expects($this->once())
            ->method('button')
            ->willReturn($buttonFactory);

        $messageBoxFactory = $this->getMockBuilder(\ILIAS\UI\Component\MessageBox\Factory::class)->getMock();
        $info = $this->getMockBuilder(MessageBox::class)->getMock();

        $messageBoxFactory
            ->expects($this->once())
            ->method('info')
            ->with($this->stringContains('Agreements never reset'))
            ->willReturn($info);

        $info
            ->expects($this->once())
            ->method('withButtons')
            ->with($this->countOf(1));

        $this->uiFactory
            ->expects($this->once())
            ->method('messageBox')
            ->willReturn($messageBoxFactory);

        $this->error
            ->expects($this->never())
            ->method('raiseError');

        $this->uiRenderer
            ->expects($this->atLeast(1))
            ->method('render')
            ->willReturn('');

        $this->lng
            ->expects($this->exactly(2))
            ->method('txt')
            ->willReturnOnConsecutiveCalls(
                'Agreements never reset',
                'Some button text'
            );

        $gui = new ilTermsOfServiceDocumentGUI(
            $this->tos,
            $this->criterionTypeFactory,
            $this->tpl,
            $this->user,
            $this->ctrl,
            $this->lng,
            $this->rbacsystem,
            $this->error,
            $this->log,
            $this->toolbar,
            $this->httpState,
            $this->uiFactory,
            $this->uiRenderer,
            $this->fileSystems,
            $this->fileUpload,
            $this->tableDataProviderFactory,
            $this->documentPurifier
        );

        $gui->executeCommand();
    }
}
