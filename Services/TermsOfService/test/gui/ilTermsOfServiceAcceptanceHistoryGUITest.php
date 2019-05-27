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
    protected $tableDataProviderFactory;

    /** @var MockObject|ilObjTermsOfService */
    protected $tos;

    /** @var MockObject|ilGlobalTemplate */
    protected $tpl;

    /** @var MockObject|ilCtrl */
    protected $ctrl;

    /** @var MockObject|ilLanguage */
    protected $lng;

    /** @var MockObject|ilRbacSystem */
    protected $rbacsystem;

    /** @var MockObject|ilErrorHandling */
    protected $error;

    /** @var MockObject|Factory */
    protected $uiFactory;

    /** @var MockObject|Renderer */
    protected $uiRenderer;

    /** @var MockObject|ServerRequestInterface */
    protected $request;

    /** @var MockObject|ilTermsOfServiceCriterionTypeFactoryInterface */
    protected $criterionTypeFactory;

    /**
     * @throws ReflectionException
     */
    public function setUp() : void
    {
        parent::setUp();

        $this->tos                      = $this->getMockBuilder(ilObjTermsOfService::class)->disableOriginalConstructor()->getMock();
        $this->criterionTypeFactory     = $this->getMockBuilder(ilTermsOfServiceCriterionTypeFactoryInterface::class)->disableOriginalConstructor()->getMock();
        $this->tpl                      = $this->getMockBuilder(ilGlobalTemplate::class)->disableOriginalConstructor()->getMock();
        $this->ctrl                     = $this->getMockBuilder(ilCtrl::class)->disableOriginalConstructor()->getMock();
        $this->lng                      = $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $this->rbacsystem               = $this->getMockBuilder(ilRbacSystem::class)->disableOriginalConstructor()->getMock();
        $this->error                    = $this->getMockBuilder(ilErrorHandling::class)->disableOriginalConstructor()->getMock();
        $this->request                  = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $this->uiFactory                = $this->getMockBuilder(Factory::class)->disableOriginalConstructor()->getMock();
        $this->uiRenderer               = $this->getMockBuilder(Renderer::class)->disableOriginalConstructor()->getMock();
        $this->tableDataProviderFactory = $this->getMockBuilder(ilTermsOfServiceTableDataProviderFactory::class)->disableOriginalConstructor()->getMock();
    }

    /**
     *
     */
    public function testAccessDeniedErrorIsRaisedWhenPermissionsAreMissing() : void
    {
        $this->ctrl
            ->expects($this->any())
            ->method('getCmd')
            ->willReturnOnConsecutiveCalls(
                'showAcceptanceHistory'
            );

        $this->rbacsystem
            ->expects($this->any())
            ->method('checkAccess')
            ->willReturn(false);

        $this->error
            ->expects($this->any())
            ->method('raiseError')
            ->willThrowException(new ilException('no_permission'));

        $gui = new ilTermsOfServiceAcceptanceHistoryGUI(
            $this->tos, $this->criterionTypeFactory, $this->tpl,
            $this->ctrl, $this->lng,
            $this->rbacsystem, $this->error,
            $this->request, $this->uiFactory,
            $this->uiRenderer, $this->tableDataProviderFactory
        );

        $this->expectException(ilException::class);

        $gui->executeCommand();
    }
}