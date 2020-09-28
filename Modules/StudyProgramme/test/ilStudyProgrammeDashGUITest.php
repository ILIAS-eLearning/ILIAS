<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;

/**
 * Encapsulation of GUI
 */
class mockSPRGDashGUI extends ilStudyProgrammeDashboardViewGUI
{
    public function __construct()
    {
    }

    public function mockCalculatePercent($prg, int $current_points)
    {
        return $this->calculatePercent($prg, $current_points);
    }
}

/**
 * TestCase for SPRG-Section of dashboard
 */
class ilStudyProgrammeDashGUITest extends TestCase
{
    protected function setUp() : void
    {
        $this->gui = new mockSPRGDashGUI();
        $this->prg = $this->getMockBuilder(ilObjStudyProgramme::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'hasLPChildren',
                'getAllPrgChildren',
                'getPoints'
            ])
            ->getMock();
    }

    public function userPointsDataProvider()
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
    public function testPercentageWithoutChildren(int $current_user_points)
    {
        $this->prg->method('hasLPChildren')
            ->willReturn(false);
        $this->prg->method('getAllPrgChildren')
            ->willReturn([]);

        list($minimum_percents, $current_percents)
            = $this->gui->mockCalculatePercent($this->prg, $current_user_points);

        $this->assertEquals(0, $minimum_percents);
        $this->assertEquals(0, $current_percents);
    }

    /**
     * @dataProvider userPointsDataProvider
     */
    public function testPercentageWithCoursesAtTopLevel(int $current_user_points)
    {
        $this->prg->method('hasLPChildren')
            ->willReturn(true);

        list($minimum_percents, $current_percents)
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
    public function testPercentageWithPrograms(int $current_user_points, float $expected)
    {
        $node = $this->getMockBuilder(ilObjStudyProgramme::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPoints'])
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

        list($minimum_percents, $current_percents)
            = $this->gui->mockCalculatePercent($this->prg, $current_user_points);

        $this->assertEquals(37.5, $minimum_percents); //37.5 = (160 max points /  60 root-prg points) * 100
        $this->assertEquals($expected, $current_percents);
    }
}
