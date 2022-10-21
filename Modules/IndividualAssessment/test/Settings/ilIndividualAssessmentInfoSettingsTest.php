<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use PHPUnit\Framework\TestCase;
use ILIAS\UI\Component\Input\Field\Section;

class ilIndividualAssessmentInfoSettingsTest extends TestCase
{
    public function test_createObject_simple(): void
    {
        $obj = new ilIndividualAssessmentInfoSettings(22);

        $this->assertInstanceOf(ilIndividualAssessmentInfoSettings::class, $obj);

        $this->assertEquals(22, $obj->getObjId());
        $this->assertNull($obj->getContact());
        $this->assertNull($obj->getResponsibility());
        $this->assertNull($obj->getPhone());
        $this->assertNull($obj->getMails());
        $this->assertNull($obj->getConsultationHours());
    }

    public function test_createObject_full(): void
    {
        $obj = new ilIndividualAssessmentInfoSettings(
            33,
            "contact",
            "responsibility",
            "phone",
            "mails",
            "consultation_hours"
        );

        $this->assertInstanceOf(ilIndividualAssessmentInfoSettings::class, $obj);

        $this->assertEquals(33, $obj->getObjId());
        $this->assertEquals("contact", $obj->getContact());
        $this->assertEquals("responsibility", $obj->getResponsibility());
        $this->assertEquals("phone", $obj->getPhone());
        $this->assertEquals("mails", $obj->getMails());
        $this->assertEquals("consultation_hours", $obj->getConsultationHours());
    }

    public function test_to_form_input()
    {
        $lng = $this->createMock(ilLanguage::class);
        $lng->expects($this->atLeastOnce())
            ->method('txt')
            ->willReturn("label")
        ;

        $df = new ILIAS\Data\Factory();
        $refinery = new ILIAS\Refinery\Factory($df, $lng);
        $f = new ILIAS\UI\Implementation\Component\Input\Field\Factory(
            $this->createMock(\ILIAS\UI\Implementation\Component\Input\UploadLimitResolver::class),
            new ILIAS\UI\Implementation\Component\SignalGenerator(),
            $df,
            $refinery,
            $lng
        );

        $settings = new ilIndividualAssessmentInfoSettings(
            33,
            "contact",
            "responsibility",
            "phone",
            "mails",
            "consultation_hours"
        );

        $input = $settings->toFormInput(
            $f,
            $lng,
            $refinery
        );

        $this->assertInstanceOf(Section::class, $input);
    }
}
