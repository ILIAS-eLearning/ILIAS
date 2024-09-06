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

namespace Logging;

use ILIAS\Test\Logging\AdditionalInformationGenerator;
use ILIAS\Test\Logging\TestError;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Factory as UIFactory;
use ilTestBaseTestCase;
use PHPUnit\Framework\MockObject\Exception;
use ReflectionException;

class TestErrorTest extends ilTestBaseTestCase
{
    /**
     * @throws ReflectionException|Exception
     */
    public function testConstruct(): void
    {
        $this->assertInstanceOf(TestError::class, $this->createInstanceOf(TestError::class));
    }

    /**
     * @dataProvider getUniqueIdentifierDataProvider
     * @throws ReflectionException|Exception
     */
    public function testWithIdAndGetUniqueIdentifier(int $input, string $output): void
    {
        $test_error = $this->createInstanceOf(TestError::class);
        $this->assertInstanceOf(TestError::class, $test_error = $test_error->withId($input));
        $this->assertEquals($output, $test_error->getUniqueIdentifier());
    }

    public static function getUniqueIdentifierDataProvider(): array
    {
        return [
            'negative_one' => [-1, 'te_-1'],
            'zero' => [0, 'te_0'],
            'one' => [1, 'te_1']
        ];
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testGetParsedAdditionalInformation(): void
    {
        $additional_info = $this->createMock(AdditionalInformationGenerator::class);
        $error_message = '';
        $legacy = $this->createMock(Legacy::class);
        $ui_factory = $this->createMock(UIFactory::class);
        $ui_factory
            ->expects($this->once())
            ->method('legacy')
            ->with($error_message)
            ->willReturn($legacy);
        $environment = [];
        $test_error = $this->createInstanceOf(TestError::class);

        $this->assertEquals(
            $legacy,
            $test_error->getParsedAdditionalInformation(
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
        $test_error = $this->createInstanceOf(TestError::class);

        $this->assertEquals(
            [
                'ref_id' => ['integer', 0],
                'qst_id' => ['integer', 0],
                'admin_id' => ['integer', 0],
                'pax_id' => ['integer', 0],
                'interaction_type' => ['text', 'error_on_test_administration_interaction'],
                'modification_ts' => ['integer', 0],
                'error_message' => ['text', '']
            ],
            $test_error->toStorage()
        );
    }

    /**
     * @dataProvider getUserForPresentationDataProvider
     * @throws ReflectionException|Exception
     */
    public function testGetUserForPresentation(?int $input, string $output): void
    {
        $test_error = $this->createInstanceOf(TestError::class);

        $this->assertEquals(
            $output,
            self::callMethod($test_error, 'getUserForPresentation', [$input])
        );
    }

    public static function getUserForPresentationDataProvider(): array
    {
        return [
            'null' => [null, ''],
            'negative_one' => [-1, ''],
            'zero' => [0, ''],
            'one' => [1, '']
        ];
    }
}
