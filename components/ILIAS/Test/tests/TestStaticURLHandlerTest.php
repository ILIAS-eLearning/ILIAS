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

namespace ILIAS\Test\Tests;

use ilCtrl;
use ilTestBaseTestCase;
use PHPUnit\Framework\MockObject\Exception;
use ReflectionException;
use TestStaticURLHandler;

class TestStaticURLHandlerTest extends ilTestBaseTestCase
{
    /**
     * @throws ReflectionException|Exception
     */
    public function testConstruct(): void
    {
        $this->assertInstanceOf(TestStaticURLHandler::class, $this->createInstanceOf(TestStaticURLHandler::class));
    }

    /**
     * @throws ReflectionException|Exception
     */
    public function testGetNamespace(): void
    {
        $test_static_url_handler = $this->createInstanceOf(TestStaticURLHandler::class);

        $this->assertEquals('tst', $test_static_url_handler->getNamespace());
    }

    /**
     * @dataProvider buildQuestionURLDataProvider
     * @throws ReflectionException|Exception
     */
    public function testBuildQuestionURL(string $input, string $output): void
    {
        $test_static_url_handler = $this->createInstanceOf(TestStaticURLHandler::class);

        $this->assertEquals(
            $output,
            self::callMethod(
                $test_static_url_handler,
                'buildQuestionURL',
                [$input, $this->createMock(ilCtrl::class)]
            )
        );
    }

    public static function buildQuestionURLDataProvider(): array
    {
        return [
            'empty' => ['', ''],
            'string' => ['string', ''],
            'strING' => ['strING', ''],
            'STRING' => ['STRING', '']
        ];
    }
}
