<?php declare(strict_types=1);

/* Copyright (c) 2021 - Daniel Weise <daniel.weise@concepts-and-training.de> - Extended GPL, see LICENSE */

use PHPUnit\Framework\TestCase;

require_once "class.ilDummyKioskModeView.php";

class ilKioskModeServiceTest extends TestCase
{
    public function test_createObject() : void
    {
        $ctrl = $this->createMock(ilCtrl::class);
        $lng = $this->createMock(ilLanguage::class);
        $access = $this->createMock(ilAccess::class);
        $obj_definition = $this->createMock(ilObjectDefinition::class);

        $obj = new ilKioskModeService($ctrl, $lng, $access, $obj_definition);

        $this->assertInstanceOf(ilKioskModeService::class, $obj);
    }

    public function test_getViewFor_non_existing_type() : void
    {
        $ctrl = $this->createMock(ilCtrl::class);
        $lng = $this->createMock(ilLanguage::class);
        $access = $this->createMock(ilAccess::class);

        $ilObject = $this->createMock(ilObject::class);
        $ilObject
            ->expects($this->once())
            ->method("getType")
            ->willReturn("testtype")
        ;

        $obj_definition = $this->createMock(ilObjectDefinition::class);
        $obj_definition
            ->expects($this->exactly(1))
            ->method("getClassName")
            ->with("testtype")
            ->willReturn("wrong")
        ;


        $obj = new ilKioskModeService($ctrl, $lng, $access, $obj_definition);
        $this->assertNull($obj->getViewFor($ilObject));
    }

    public function test_getViewFor() : void
    {
        $ctrl = $this->createMock(ilCtrl::class);
        $lng = $this->createMock(ilLanguage::class);
        $access = $this->createMock(ilAccess::class);

        $ilObject = $this->createMock(ilObject::class);
        $ilObject
            ->expects($this->once())
            ->method("getType")
            ->willReturn("testtype")
        ;

        $obj_definition = $this->createMock(ilObjectDefinition::class);
        $obj_definition
            ->expects($this->exactly(2))
            ->method("getClassName")
            ->with("testtype")
            ->willReturn("Dummy")
        ;


        $obj = new ilKioskModeService($ctrl, $lng, $access, $obj_definition);
        $result = $obj->getViewFor($ilObject);

        $this->assertInstanceOf(ilDummyKioskModeView::class, $result);
    }
}
