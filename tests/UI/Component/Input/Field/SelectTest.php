<?php

/* Copyright (c) 2018 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../Base.php");

class SelectForTest extends ILIAS\UI\Implementation\Component\Input\Field\Select
{
    public function _isClientSideValueOk($value)
    {
        return $this->isClientSideValueOk($value);
    }
}

class SelectInputTest extends ILIAS_UI_TestBase
{
    public function testOnlyValuesFromOptionsAreAcceptableClientSideValues()
    {
        $options = ["one" => "Eins", "two" => "Zwei", "three" => "Drei"];
        $select = new SelectForTest(
            $this->createMock(ILIAS\Data\Factory::class),
            $this->createMock(ILIAS\Validation\Factory::class),
            $this->createMock(ILIAS\Transformation\Factory::class),
            "",
            $options,
            ""
        );

        $this->assertTrue($select->_isClientSideValueOk("one"));
        $this->assertTrue($select->_isClientSideValueOk("two"));
        $this->assertTrue($select->_isClientSideValueOk("three"));
        $this->assertFalse($select->_isClientSideValueOk("four"));
    }

    public function testEmptyStringIsAcceptableClientSideValueIfSelectIsNotRequired()
    {
        $options = [];
        $select = new SelectForTest(
            $this->createMock(ILIAS\Data\Factory::class),
            $this->createMock(ILIAS\Validation\Factory::class),
            $this->createMock(ILIAS\Transformation\Factory::class),
            "",
            $options,
            ""
        );

        $this->assertTrue($select->_isClientSideValueOk(""));
    }

    public function testEmptyStringIsNoAcceptableClientSideValueIfSelectIsRequired()
    {
        $options = [];
        $select = (new SelectForTest(
            $this->createMock(ILIAS\Data\Factory::class),
            $this->createMock(ILIAS\Validation\Factory::class),
            $this->createMock(ILIAS\Transformation\Factory::class),
            "",
            $options,
            ""
        ))->withRequired(true);

        $this->assertTrue($select->_isClientSideValueOk(""));
    }
}
