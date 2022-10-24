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
 ********************************************************************
 */

use PHPUnit\Framework\TestCase;

class ilQTIAssessmentcontrolTest extends TestCase
{
    public function testConstruct(): ilQTIAssessmentcontrol
    {
        $instance = new ilQTIAssessmentcontrol();

        $this->assertInstanceOf(ilQTIAssessmentcontrol::class, $instance);

        return $instance;
    }

    /**
     * @depends testConstruct
     */
    public function testGetView(ilQTIAssessmentcontrol $instance): void
    {
        $this->assertEquals('All', $instance->getView());
    }

    /**
     * @dataProvider validViews
     * @depends testGetView
     */
    public function testSetViewValid(string $view): void
    {
        $instance = new ilQTIAssessmentcontrol();
        $instance->setView($view);
        $this->assertEquals($view, $instance->getView());
    }

    /**
     * @depends testSetViewValid
     */
    public function testSetViewInvalid(): void
    {
        $instance = new ilQTIAssessmentcontrol();
        $instance->setView('Some random content.');
        $this->assertEquals('All', $instance->getView());
    }

    /**
     * @dataProvider switches
     * @depends testConstruct
     */
    public function testSwitchInitializeValue(string $suffix): void
    {
        $instance = new ilQTIAssessmentcontrol();
        $get = 'get' . ucfirst($suffix);

        $this->assertEquals('', $instance->$get());
    }

    /**
     * @dataProvider switches
     * @depends testConstruct
     */
    public function testSwitchValuesConsideredAsYes(string $suffix): void
    {
        $instance = new ilQTIAssessmentcontrol();
        $get = 'get' . ucfirst($suffix);
        $set = 'set' . ucfirst($suffix);

        $consideredAsYes = ['Yes', 'yes', 'no', '', 'Some random thing.'];
        foreach ($consideredAsYes as $value) {
            $instance->$set($value);
            $this->assertEquals('Yes', $instance->$get());
        }
    }


    /**
     * @dataProvider switches
     * @depends testConstruct
     */
    public function testSwitchValuesConsideredAsNo(string $suffix): void
    {
        $instance = new ilQTIAssessmentcontrol();
        $get = 'get' . ucfirst($suffix);
        $set = 'set' . ucfirst($suffix);

        $instance->$set('No');
        $this->assertEquals('No', $instance->$get());
    }

    public function validViews(): array
    {
        return [
            ['Administrator'],
            ['AdminAuthority'],
            ['Assessor'],
            ['Author'],
            ['Candidate'],
            ['InvigilatorProctor'],
            ['Psychometrician'],
            ['Scorer'],
            ['Tutor'],
        ];
    }

    public function switches(): array
    {
        return [
            ['hintswitch'],
            ['solutionswitch'],
            ['feedbackswitch'],
        ];
    }
}
