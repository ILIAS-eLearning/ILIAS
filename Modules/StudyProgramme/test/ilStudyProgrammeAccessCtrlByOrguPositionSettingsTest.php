<?php

/* Copyright (c) 2019 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ilStudyProgrammeAccessCtrlByOrguPositionSettingsTest extends TestCase
{
    const VALID_ACCESSS_BY_ORGU_1 = true;
    const VALID_ACCESSS_BY_ORGU_2 = false;

    public function testSuccessfulCreate()
    {
        $obj = new ilStudyProgrammeAccessCtrlByOrguPositionSettings(self::VALID_ACCESSS_BY_ORGU_1);

        $this->assertEquals(self::VALID_ACCESSS_BY_ORGU_1, $obj->getAccessByOrgu());
    }

    public function testSuccessfulWithAccessByOrgu() : void
    {
        $obj = new ilStudyProgrammeAccessCtrlByOrguPositionSettings(self::VALID_ACCESSS_BY_ORGU_1);

        $new = $obj->withAccessByOrgu(self::VALID_ACCESSS_BY_ORGU_2);

        $this->assertEquals(self::VALID_ACCESSS_BY_ORGU_1, $obj->getAccessByOrgu());
        $this->assertEquals(self::VALID_ACCESSS_BY_ORGU_2, $new->getAccessByOrgu());
    }

    public function testToFormInput() : void
    {
        $lng = $this->createMock(ilLanguage::class);
        $df = new ILIAS\Data\Factory();
        $refinery = new ILIAS\Refinery\Factory($df, $lng);

        $f = new ILIAS\UI\Implementation\Component\Input\Field\Factory(
            new ILIAS\UI\Implementation\Component\SignalGenerator(),
            $df,
            $refinery
        );

        $obj = new ilStudyProgrammeAccessCtrlByOrguPositionSettings(self::VALID_ACCESSS_BY_ORGU_1);

        $lng->expects($this->atLeastOnce())
            ->method('txt')
            ->withConsecutive(
                ['prg_access_by_orgu'],
                ['prg_access_by_orgu_byline'],
                ['prg_additional_settings']
            )
            ->will(
                $this->onConsecutiveCalls(
                'prg_access_by_orgu',
                'prg_access_by_orgu_byline',
                'prg_additional_settings'
            )
            )
        ;

        $field = $obj->toFormInput(
            $f,
            $lng,
            $refinery
        );

        /** @var ILIAS\UI\Implementation\Component\Input\Field\Checkbox $checkbox */
        $checkbox = $field->getInputs()['access_ctr_by_orgu_position'];

        $this->assertInstanceOf(
            ILIAS\UI\Implementation\Component\Input\Field\Checkbox::class,
            $checkbox
        );
        $this->assertEquals(self::VALID_ACCESSS_BY_ORGU_1, $checkbox->getValue());
    }
}
