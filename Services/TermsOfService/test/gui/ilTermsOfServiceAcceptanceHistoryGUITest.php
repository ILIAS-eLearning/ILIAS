<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ilTermsOfServiceAcceptanceHistoryGUITest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAcceptanceHistoryGUITest extends ilTermsOfServiceBaseTest
{
    /** @var MockObject|ilTermsOfServiceTableDataProviderFactory */
    protected ilTermsOfServiceTableDataProviderFactory $tableDataProviderFactory;
    /** @var MockObject|ilObjTermsOfService */
    protected ilObjTermsOfService $tos;
    /** @var MockObject|ilGlobalTemplateInterface */
    protected ilGlobalTemplateInterface $tpl;
    /** @var MockObject|ilCtrl */
    protected ilCtrl $ctrl;
    /** @var MockObject|ilLanguage */
    protected ilLanguage $lng;
    /** @var MockObject|ilRbacSystem */
    protected ilRbacSystem $rbacsystem;
    /** @var MockObject|ilErrorHandling */
    protected ilErrorHandling $error;
    /** @var MockObject|Factory */
    protected Factory $uiFactory;
    /** @var MockObject|Renderer */
    protected Renderer $uiRenderer;
    /** @var MockObject|ServerRequestInterface */
    protected ServerRequestInterface $request;
    /** @var MockObject|ilTermsOfServiceCriterionTypeFactoryInterface */
    protected ilTermsOfServiceCriterionTypeFactoryInterface $criterionTypeFactory;

    protected function setUp() : void
    {
        parent::setUp();

        $this->tos = $this->getMockBuilder(ilObjTermsOfService::class)->disableOriginalConstructor()->getMock();
        $this->criterionTypeFactory = $this->getMockBuilder(ilTermsOfServiceCriterionTypeFactoryInterface::class)->disableOriginalConstructor()->getMock();
        $this->tpl = $this->getMockBuilder(ilGlobalTemplateInterface::class)->getMock();
        $this->ctrl = $this->getMockBuilder(ilCtrl::class)->disableOriginalConstructor()->getMock();
        $this->lng = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $this->rbacsystem = $this->getMockBuilder(ilRbacSystem::class)->disableOriginalConstructor()->getMock();
        $this->error = $this->getMockBuilder(ilErrorHandling::class)->disableOriginalConstructor()->getMock();
        $this->request = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $this->uiFactory = $this->getMockBuilder(Factory::class)->disableOriginalConstructor()->getMock();
        $this->uiRenderer = $this->getMockBuilder(Renderer::class)->disableOriginalConstructor()->getMock();
        $this->tableDataProviderFactory = $this->getMockBuilder(ilTermsOfServiceTableDataProviderFactory::class)->disableOriginalConstructor()->getMock();
    }

    public function testAccessDeniedErrorIsRaisedWhenPermissionsAreMissing() : void
    {
        $this->ctrl
            ->method('getCmd')
            ->willReturnOnConsecutiveCalls(
                'showAcceptanceHistory'
            );

        $this->rbacsystem
            ->method('checkAccess')
            ->willReturn(false);

        $this->error
            ->method('raiseError')
            ->willThrowException(new ilException('no_permission'));

        $gui = new ilTermsOfServiceAcceptanceHistoryGUI(
            $this->tos,
            $this->criterionTypeFactory,
            $this->tpl,
            $this->ctrl,
            $this->lng,
            $this->rbacsystem,
            $this->error,
            $this->request,
            $this->uiFactory,
            $this->uiRenderer,
            $this->tableDataProviderFactory
        );

        $this->expectException(ilException::class);

        $gui->executeCommand();
    }
}
