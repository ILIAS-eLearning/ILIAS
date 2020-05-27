<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilStudyProgrammeTypeSettingsTest extends TestCase
{
    const VALID_TYPE_1 = 11;
    const VALID_TYPE_2 = 22;

    public function testSuccessCreate() : void
    {
        $obj = new ilStudyProgrammeTypeSettings(self::VALID_TYPE_1);

        $this->assertEquals(self::VALID_TYPE_1, $obj->getTypeId());
    }

    public function testSuccessfulWithTypeId() : void
    {
        $obj = new ilStudyProgrammeTypeSettings(self::VALID_TYPE_1);

        $new = $obj->withTypeId(self::VALID_TYPE_2);

        $this->assertEquals(self::VALID_TYPE_1, $obj->getTypeId());
        $this->assertEquals(self::VALID_TYPE_2, $new->getTypeId());
    }

    public function testToFormInput() : void
    {
        $lng = $this->createMock(ilLanguage::class);
        $df = new ILIAS\Data\Factory();
        $refinery = new ILIAS\Refinery\Factory($df, $lng);

        $f = new ILIAS\UI\Implementation\Component\Input\Field\Factory(
            new ILIAS\UI\Implementation\Component\SignalGenerator(),
            $df,
            $refinery,
            $lng
        );

        $obj = new ilStudyProgrammeTypeSettings(self::VALID_TYPE_1);

        $lng->expects($this->atLeastOnce())
            ->method('txt')
            ->withConsecutive(['type'], ['prg_type_byline'], ['prg_type'])
            ->will($this->onConsecutiveCalls('type', 'prg_type_byline', 'prg_type'))
        ;

        $field = $obj->toFormInput(
            $f,
            $lng,
            $refinery,
            [
                self::VALID_TYPE_1 => 'first',
                self::VALID_TYPE_2 => 'second'
            ]
        );

        /** @var ILIAS\UI\Implementation\Component\Input\Field\Select $select */
        $select = $field->getInputs()['type'];

        $this->assertInstanceOf(
            ILIAS\UI\Implementation\Component\Input\Field\Select::class,
            $select
        );
        $this->assertEquals(self::VALID_TYPE_1, $select->getValue());
    }
}
