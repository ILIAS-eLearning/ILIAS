<?php

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

declare(strict_types=1);

namespace ILIAS\Test\Tests\Logging;

use ILIAS\Test\Logging\AdditionalInformationGenerator;
use ILIAS\Test\Logging\TestScoringInteraction;
use ILIAS\UI\Component\Listing\Descriptive as DescriptiveListing;
use ILIAS\UI\Factory as UIFactory;
use ilTestBaseTestCase;
use PHPUnit\Framework\MockObject\Exception;
use ReflectionException;

class TestScoringInteractionTest extends ilTestBaseTestCase
{
    /**
     * @throws ReflectionException|Exception
     */
    public function testConstruct(): void
    {
        $this->assertInstanceOf(TestScoringInteraction::class, $this->createInstanceOf(TestScoringInteraction::class));
    }

    /**
     * @dataProvider getUniqueIdentifierDataProvider
     * @throws ReflectionException|Exception
     */
    public function testWithIdAndGetUniqueIdentifier(int $input, string $output): void
    {
        $test_scoring_interaction = $this->createInstanceOf(TestScoringInteraction::class);
        $this->assertInstanceOf(TestScoringInteraction::class, $test_scoring_interaction = $test_scoring_interaction->withId($input));
        $this->assertEquals($output, $test_scoring_interaction->getUniqueIdentifier());
    }

    public static function getUniqueIdentifierDataProvider(): array
    {
        return [
            'negative_one' => [-1, 'si_-1'],
            'zero' => [0, 'si_0'],
            'one' => [1, 'si_1']
        ];
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testGetParsedAdditionalInformation(): void
    {
        $descriptive_listing = $this->createMock(DescriptiveListing::class);
        $additional_data = [];
        $environment = [];
        $additional_info = $this->createMock(AdditionalInformationGenerator::class);
        $additional_info
            ->expects($this->once())
            ->method('parseForTable')
            ->with($additional_data, $environment)
            ->willReturn($descriptive_listing);
        $ui_factory = $this->createMock(UIFactory::class);
        $test_scoring_interaction = $this->createInstanceOf(TestScoringInteraction::class, ['additional_data' => $additional_data]);

        $this->assertEquals(
            $descriptive_listing,
            $test_scoring_interaction->getParsedAdditionalInformation(
                $additional_info,
                $ui_factory,
                $environment
            )
        );
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testToStorage(): void
    {
        $test_scoring_interaction = $this->createInstanceOf(TestScoringInteraction::class);

        $this->assertEquals(
            [
                'ref_id' => ['integer', 0],
                'qst_id' => ['integer', 0],
                'admin_id' => ['integer', 0],
                'pax_id' => ['integer', 0],
                'interaction_type' => ['text', 'question_graded'],
                'modification_ts' => ['integer', 0],
                'additional_data' => ['clob', '[]']
            ],
            $test_scoring_interaction->toStorage()
        );
    }
}
