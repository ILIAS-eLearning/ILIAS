<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;
use ILIAS\HTTP\Response\Sender\ResponseSenderStrategy;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\Refinery\Factory as Refinery;

/**
 * Class ilCtrlTest
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class ilCtrlTest extends TestCase
{
    public function testCallBaseClassWithoutBaseClass() : void
    {
        $ctrl = $this->getMockedCtrl();

        $this->expectException(ilCtrlException::class);
        $this->expectExceptionMessage(ilCtrl::class . "::callBaseClass was not given a baseclass and the request doesn't include one either.");
        $ctrl->callBaseClass();
    }

    public function testCallBaseClassWithInvalidProvidedBaseClass() : void
    {
        $structure = $this->createMock(ilCtrlStructureInterface::class);
        $structure
            ->method('isBaseClass')
            ->willReturn(false)
        ;

        $ctrl = new ilCtrl(
            $structure,
            $this->createMock(ilCtrlTokenRepositoryInterface::class),
            $this->createMock(ilCtrlPathFactoryInterface::class),
            $this->createMock(ilCtrlContextInterface::class),
            $this->createMock(ResponseSenderStrategy::class),
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(RequestWrapper::class),
            $this->createMock(RequestWrapper::class),
            $this->createMock(Refinery::class),
            $this->createMock(ilComponentFactory::class),
        );

        $invalid_baseclass = ilCtrlInvalidGuiClass::class;

        $this->expectException(ilCtrlException::class);
        $this->expectExceptionMessage("Provided class '$invalid_baseclass' is not a baseclass");
        $ctrl->callBaseClass($invalid_baseclass);
    }

    public function testForwardCommandWithInvalidObject() : void
    {
        $ctrl = $this->getMockedCtrl();

        require_once __DIR__ . '/Data/GUI/class.ilCtrlInvalidGuiClass.php';

        $this->expectException(ilCtrlException::class);
        $this->expectExceptionMessage(ilCtrlInvalidGuiClass::class . " doesn't implement executeCommand().");
        $ctrl->forwardCommand(new ilCtrlInvalidGuiClass());
    }

    public function testForwardCommandWithValidObject() : void
    {
        $ctrl = $this->getMockedCtrl();

        require_once __DIR__ . '/Data/GUI/class.ilCtrlCommandClass1TestGUI.php';

        $this->assertEquals(
            ilCtrlCommandClass1TestGUI::class,
            $ctrl->forwardCommand(new ilCtrlCommandClass1TestGUI())
        );
    }

    public function testGetHtmlWithInvalidObject() : void
    {
        $ctrl = $this->getMockedCtrl();

        require_once __DIR__ . '/Data/GUI/class.ilCtrlInvalidGuiClass.php';

        $this->expectException(ilCtrlException::class);
        $this->expectExceptionMessage(ilCtrlInvalidGuiClass::class . " doesn't implement getHTML().");
        $ctrl->getHTML(new ilCtrlInvalidGuiClass());
    }

    public function testGetHtmlWithValidObject() : void
    {
        $ctrl = $this->getMockedCtrl();

        require_once __DIR__ . '/Data/GUI/class.ilCtrlCommandClass2TestGUI.php';

        $this->assertEquals('foo', $ctrl->getHTML(new ilCtrlCommandClass2TestGUI()));
    }

    public function testGetHtmlWithValidObjectAndParameters() : void
    {
        $ctrl = $this->getMockedCtrl();

        require_once __DIR__ . '/Data/GUI/class.ilCtrlCommandClass2TestGUI.php';

        $this->assertEquals('bar', $ctrl->getHTML(new ilCtrlCommandClass2TestGUI(), ['bar']));
    }

    public function testGetCmdWithoutProvidedCmd() : void
    {
        $ctrl = $this->getMockedCtrl();

        // @TODO: change this to assertNull() once null coalescing operators are removed.
        $this->assertEmpty($ctrl->getCmd());
    }

    public function testGetCmdWithoutProvidedCmdAndFallback() : void
    {
        $ctrl = new ilCtrl(
            $this->createMock(ilCtrlStructureInterface::class),
            $this->createMock(ilCtrlTokenRepositoryInterface::class),
            $this->createMock(ilCtrlPathFactoryInterface::class),
            $this->createMock(ilCtrlContextInterface::class),
            $this->createMock(ResponseSenderStrategy::class),
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(RequestWrapper::class),
            $this->createMock(RequestWrapper::class),
            $this->createMock(Refinery::class),
            $this->createMock(ilComponentFactory::class),
        );

        $fallback_cmd = 'fallback_cmd_test';

        $this->assertEquals(
            $fallback_cmd,
            $ctrl->getCmd($fallback_cmd)
        );
    }

    // @TODO: coming soon, I promise!

    /*
    public function testGetCmdWithUnsafePostCmd() : void
    {

    }

    public function testGetCmdWithSafePostCmd() : void
    {

    }

    public function testGetCmdWithUnsafeGetCmd() : void
    {

    }

    public function testGetCmdWithSafeGetCmd() : void
    {

    }

    public function testGetCmdWithValidCsrfToken() : void
    {

    }

    public function testGetCmdWithInvalidCsrfToken() : void
    {

    }

    public function testGetNextClass() : void
    {

    }

    public function testGetParameterArrayByClass() : void
    {

    }

    public function testGetLinkTargetByClass() : void
    {

    }

    public function testGetFormActionByClass() : void
    {

    }

    public function testGetParentReturnByClass() : void
    {

    }
    */

    /**
     * Helper function that returns an ilCtrl instance with mocked
     * dependencies.
     *
     * @return ilCtrlInterface
     */
    private function getMockedCtrl() : ilCtrlInterface
    {
        return new ilCtrl(
            $this->createMock(ilCtrlStructureInterface::class),
            $this->createMock(ilCtrlTokenRepositoryInterface::class),
            $this->createMock(ilCtrlPathFactoryInterface::class),
            $this->createMock(ilCtrlContextInterface::class),
            $this->createMock(ResponseSenderStrategy::class),
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(RequestWrapper::class),
            $this->createMock(RequestWrapper::class),
            $this->createMock(Refinery::class),
            $this->createMock(ilComponentFactory::class),
        );
    }
}
