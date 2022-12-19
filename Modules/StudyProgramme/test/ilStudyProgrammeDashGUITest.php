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
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Encapsulation of GUI
 */
class mockUserTable extends ilStudyProgrammeUserTable
{
    public function __construct()
    {
        $this->lng = new class () extends ilLanguage {
            public function __construct()
            {
            }
            public function txt(string $a_topic, string $a_default_lang_fallback_mod = ''): string
            {
                return $a_topic;
            }
        };
    }

    public function mockCalculatePercent(ilObjStudyProgramme $prg, ilPRGAssignment $ass): array
    {
        return $this->calculatePercent($prg, $ass);
    }
}

/**
 * TestCase for SPRG-Section of dashboard
 */
class ilStudyProgrammeDashGUITest extends TestCase
{
    private mockUserTable $user_table;
    /**
     * @var ilObjStudyProgramme|mixed|MockObject
     */
    private $prg;

    protected function setUp(): void
    {
        $this->user_table = new mockUserTable();
        $this->prg = $this->getMockBuilder(ilObjStudyProgramme::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'hasLPChildren'
            ])
            ->getMock();
    }

    public function userPointsDataProvider(): array
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
    public function testPercentageWithoutChildren(int $current_user_points): void
    {
        $this->prg->method('hasLPChildren')
            ->willReturn(false);

        $pgs1 = (new ilPRGProgress(1, ilPRGProgress::STATUS_COMPLETED))
            ->withAmountOfPoints(100);
        $ass = (new ilPRGAssignment(42, 7))->withProgressTree($pgs1);


        [$minimum_percents, $current_percents]
            = $this->user_table->mockCalculatePercent($this->prg, $ass);

        $this->assertEquals('0 percentage', $minimum_percents);
        $this->assertEquals('0 percentage', $current_percents);
    }

    /**
     * @dataProvider userPointsDataProvider
     */
    public function testPercentageWithCoursesAtTopLevel(int $current_user_points): void
    {
        $this->prg->method('hasLPChildren')
            ->willReturn(true);

        $pgs1 = (new ilPRGProgress(1, ilPRGProgress::STATUS_COMPLETED))
            ->withAmountOfPoints(100)
            ->withCurrentAmountOfPoints($current_user_points);
        $ass = (new ilPRGAssignment(42, 7))->withProgressTree($pgs1);

        [$minimum_percents, $current_percents]
            = $this->user_table->mockCalculatePercent($this->prg, $ass);

        $this->assertEquals('100 percentage', $minimum_percents);

        if ($current_user_points == 0) {
            $this->assertEquals('0 percentage', $current_percents);
        }
        if ($current_user_points > 0) {
            $this->assertEquals('100 percentage', $current_percents);
        }
    }

    /**
     * @dataProvider userPointsDataProvider
     */
    public function testPercentageWithPrograms(int $current_user_points, float $expected): void
    {
        $node = $this->getMockBuilder(ilObjStudyProgramme::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPoints'])
            ->getMock();
        $this->prg->method('hasLPChildren')
            ->willReturn(false);

        $status = ilPRGProgress::STATUS_COMPLETED;
        $pgs11 = (new ilPRGProgress(11, $status))->withAmountOfPoints(100);
        $pgs12 = (new ilPRGProgress(12, $status))->withAmountOfPoints(50);
        $pgs13 = (new ilPRGProgress(13, $status))->withAmountOfPoints(5);
        $pgs14 = (new ilPRGProgress(14, $status))->withAmountOfPoints(5);
        $pgs1 = (new ilPRGProgress(1, $status))
            ->setSubnodes([$pgs11, $pgs12, $pgs13, $pgs14])
            ->withAmountOfPoints(60)
            ->withCurrentAmountOfPoints($current_user_points);
        $ass = (new ilPRGAssignment(42, 7))->withProgressTree($pgs1);


        $this->assertEquals(160, $pgs1->getPossiblePointsOfRelevantChildren());
        $this->assertEquals($current_user_points, $pgs1->getCurrentAmountOfPoints());

        [$minimum_percents, $current_percents]
            = $this->user_table->mockCalculatePercent($this->prg, $ass);


        //37.5 = (160 max points /  60 root-prg points) * 100
        $this->assertEquals('37.5 percentage', $minimum_percents);


        $this->assertEquals((string) $expected . ' percentage', $current_percents);
    }
}
