<?php declare(strict_types=1);

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
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Encapsulation of GUI
 */
class mockSPRGDashGUI extends ilStudyProgrammeDashboardViewGUI
{
    public function __construct()
    {
    }

    public function mockCalculatePercent(ilObjStudyProgramme $prg, int $current_points) : array
    {
        return $this->calculatePercent($prg, $current_points);
    }
}

/**
 * TestCase for SPRG-Section of dashboard
 */
class ilStudyProgrammeDashGUITest extends TestCase
{
    private mockSPRGDashGUI $gui;
    /**
     * @var ilObjStudyProgramme|mixed|MockObject
     */
    private $prg;

    protected function setUp() : void
    {
        $this->gui = new mockSPRGDashGUI();
        $this->prg = $this->getMockBuilder(ilObjStudyProgramme::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'hasLPChildren',
                'getAllPrgChildren',
                'getPoints'
            ])
            ->getMock();
    }

    public function userPointsDataProvider() : array
    {
        return [
            'zero' => [0, 0],
            'one' => [1, 0.63],
            'ten' => [10, 6.25],
            'fiftyfive' => [55, 34.38],
            'hundred' => [100, 62.5],
            'oneOone' => [101, 63.13],
            'onesixty' => [160, 100]
        ];
    }

    /**
     * @dataProvider userPointsDataProvider
     */
    public function testPercentageWithoutChildren(int $current_user_points) : void
    {
        $this->prg->method('hasLPChildren')
            ->willReturn(false);
        $this->prg->method('getAllPrgChildren')
            ->willReturn([]);

        [$minimum_percents, $current_percents]
            = $this->gui->mockCalculatePercent($this->prg, $current_user_points);

        $this->assertEquals(0, $minimum_percents);
        $this->assertEquals(0, $current_percents);
    }

    /**
     * @dataProvider userPointsDataProvider
     */
    public function testPercentageWithCoursesAtTopLevel(int $current_user_points) : void
    {
        $this->prg->method('hasLPChildren')
            ->willReturn(true);

        [$minimum_percents, $current_percents]
            = $this->gui->mockCalculatePercent($this->prg, $current_user_points);

        $this->assertEquals(100, $minimum_percents);
        if ($current_user_points == 0) {
            $this->assertEquals(0, $current_percents);
        }
        if ($current_user_points > 0) {
            $this->assertEquals(100, $current_percents);
        }
    }

    /**
     * @dataProvider userPointsDataProvider
     */
    public function testPercentageWithPrograms(int $current_user_points, float $expected) : void
    {
        $node = $this->getMockBuilder(ilObjStudyProgramme::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPoints'])
            ->getMock();

        $node1 = clone $node;
        $node1->method('getPoints')->willReturn(100);
        $node2 = clone $node;
        $node2->method('getPoints')->willReturn(50);
        $node3 = clone $node;
        $node3->method('getPoints')->willReturn(5);
        $node4 = clone $node;
        $node4->method('getPoints')->willReturn(5);

        $this->prg->method('hasLPChildren')
            ->willReturn(false);
        $this->prg->method('getAllPrgChildren')
            ->willReturn([$node1, $node2, $node3, $node4]);

        $this->prg->method('getPoints')->willReturn(60);

        [$minimum_percents, $current_percents]
            = $this->gui->mockCalculatePercent($this->prg, $current_user_points);

        $this->assertEquals(37.5, $minimum_percents); //37.5 = (160 max points /  60 root-prg points) * 100
        $this->assertEquals($expected, $current_percents);
    }
}
