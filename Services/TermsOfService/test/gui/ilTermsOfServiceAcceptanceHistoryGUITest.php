<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ilTermsOfServiceAcceptanceHistoryGUITest
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceAcceptanceHistoryGUITest extends \ilTermsOfServiceBaseTest
{
    /** @var PHPUnit_Framework_MockObject_MockObject|\ilTermsOfServiceTableDataProviderFactory */
    protected $tableDataProviderFactory;

    /** @var PHPUnit_Framework_MockObject_MockObject|\ilObjTermsOfService */
    protected $tos;

    /** @var PHPUnit_Framework_MockObject_MockObject|\ilTemplate */
    protected $tpl;

    /** @var PHPUnit_Framework_MockObject_MockObject|\ilCtrl */
    protected $ctrl;

    /** @var PHPUnit_Framework_MockObject_MockObject|\ilLanguage */
    protected $lng;

    /** @var PHPUnit_Framework_MockObject_MockObject|\ilRbacSystem */
    protected $rbacsystem;

    /** @var PHPUnit_Framework_MockObject_MockObject|\ilErrorHandling */
    protected $error;

    /** @var PHPUnit_Framework_MockObject_MockObject|Factory */
    protected $uiFactory;

    /** @var PHPUnit_Framework_MockObject_MockObject|Renderer */
    protected $uiRenderer;

    /** @var PHPUnit_Framework_MockObject_MockObject|ServerRequestInterface */
    protected $request;

    /** @var PHPUnit_Framework_MockObject_MockObject|\ilTermsOfServiceCriterionTypeFactoryInterface */
    protected $criterionTypeFactory;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->tos                      = $this->getMockBuilder(\ilObjTermsOfService::class)->disableOriginalConstructor()->getMock();
        $this->criterionTypeFactory     = $this->getMockBuilder(\ilTermsOfServiceCriterionTypeFactoryInterface::class)->disableOriginalConstructor()->getMock();
        $this->tpl                      = $this->getMockBuilder(\ilTemplate::class)->disableOriginalConstructor()->setMethods(['g'])->getMock();
        $this->ctrl                     = $this->getMockBuilder(\ilCtrl::class)->disableOriginalConstructor()->getMock();
        $this->lng                      = $this->getMockBuilder(\ilLanguage::class)->disableOriginalConstructor()->getMock();
        $this->rbacsystem               = $this->getMockBuilder(\ilRbacSystem::class)->disableOriginalConstructor()->getMock();
        $this->error                    = $this->getMockBuilder(\ilErrorHandling::class)->disableOriginalConstructor()->getMock();
        $this->request                  = $this->getMockBuilder(ServerRequestInterface::class)->disableOriginalConstructor()->getMock();
        $this->uiFactory                = $this->getMockBuilder(Factory::class)->disableOriginalConstructor()->getMock();
        $this->uiRenderer               = $this->getMockBuilder(Renderer::class)->disableOriginalConstructor()->getMock();
        $this->tableDataProviderFactory = $this->getMockBuilder(\ilTermsOfServiceTableDataProviderFactory::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @expectedException \ilException
     */
    public function testAccessDeniedErrorIsRaisedWhenPermissionsAreMissing()
    {
        $this->tos
            ->expects($this->any())
            ->method('getRefId')
            ->willReturn(4711);

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
            ->willThrowException(new \ilException('no_permission'));

        $gui = new \ilTermsOfServiceAcceptanceHistoryGUI(
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

        $this->assertException(\ilException::class);

        $gui->executeCommand();
    }
}
