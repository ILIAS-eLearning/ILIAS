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
    private ilCtrlStructureInterface $structure;

    /**
     * @inheritDoc
     */
    protected function setUp() : void
    {
        $structure_artifact  = require __DIR__ . '/Data/Structure/test_ctrl_structure.php';
        $plugin_artifact     = require __DIR__ . '/Data/Structure/test_plugin_ctrl_structure.php';
        $base_class_artifact = require __DIR__ . '/Data/Structure/test_base_classes.php';

        $this->structure = new ilCtrlStructure(
            $structure_artifact,
            $plugin_artifact,
            $base_class_artifact,
            []
        );
    }

    public function testCallBaseClassWithoutBaseClass() : void
    {
        $ctrl = new ilCtrl(
            $this->createMock(ilCtrlStructureInterface::class),
            $this->createMock(ResponseSenderStrategy::class),
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(RequestWrapper::class),
            $this->createMock(RequestWrapper::class),
            $this->createMock(Refinery::class),
        );

        $this->expectException(ilCtrlException::class);
        $this->expectExceptionMessage(ilCtrl::class . "::callBaseClass was not given a baseclass and the request doesn't include one either.");
        $ctrl->callBaseClass();
    }

    public function testCallBaseClassWithInvalidProvidedBaseClass() : void
    {
        // mocked get request that returns ilCtrlBaseClass1TestGUI
        // when context is created.
        $get_request = $this->createMock(RequestWrapper::class);
        $get_request
            ->method('retrieve')
            ->willReturn(static function ($key) {
                return (ilCtrlInterface::PARAM_BASE_CLASS === $key) ? ilCtrlInvalidGuiClass::class : null;
            })
        ;

        $structure = $this->createMock(ilCtrlStructureInterface::class);
        $structure
            ->method('isBaseClass')
            ->willReturn(false)
        ;

        $ctrl = new ilCtrl(
            $structure,
            $this->createMock(ResponseSenderStrategy::class),
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(RequestWrapper::class),
            $get_request,
            $this->createMock(Refinery::class),
        );
    }

    public function testCallBaseClassWithValidProvidedBaseClass() : void
    {
        // mocked get request that returns ilCtrlBaseClass1TestGUI
        // when context is created.
        $get_request = $this->createMock(RequestWrapper::class);
        $get_request
            ->method('retrieve')
            ->willReturn(static function ($key) {
                return (ilCtrlInterface::PARAM_BASE_CLASS === $key) ? ilCtrlBaseClass1TestGUI::class : null;
            })
        ;

        $structure = $this->createMock(ilCtrlStructureInterface::class);
        $structure
            ->method('isBaseClass')
            ->willReturn(false)
        ;

        $ctrl = new ilCtrl(
            $structure,
            $this->createMock(ResponseSenderStrategy::class),
            $this->createMock(ServerRequestInterface::class),
            $this->createMock(RequestWrapper::class),
            $get_request,
            $this->createMock(Refinery::class),
        );
    }
}