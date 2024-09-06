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

namespace Questions;

use ILIAS\Test\Questions\QuestionsTableBinding;
use ILIAS\UI\Implementation\Component\Link\Standard;
use ilTestBaseTestCase;
use PHPUnit\Framework\MockObject\Exception;
use ReflectionException;

class QuestionsTableBindingTest extends ilTestBaseTestCase
{
    /**
     * @throws ReflectionException|Exception
     */
    public function testConstruct(): void
    {
        $question_table_binding = $this->createInstanceOf(QuestionsTableBinding::class);
        $this->assertInstanceOf(QuestionsTableBinding::class, $question_table_binding);
    }

    /**
     * @dataProvider getTitleLinkDataProvider
     * @throws ReflectionException|Exception
     */
    public function testGetTitleLink(array $input, Standard $output): void
    {
        $question_table_binding = $this->createInstanceOf(QuestionsTableBinding::class, [
            'title_link_builder' => fn($title, $question_id) => new Standard($title, $question_id)
        ]);
        $this->assertEquals($output, self::callMethod($question_table_binding, 'getTitleLink', $input));
    }

    public static function getTitleLinkDataProvider(): array
    {
        return [
            'string_negative_one' => [
                [
                    'title' => 'string',
                    'question_id' => '-1'
                ],
                new Standard('string', '-1')
            ],
            'string_zero' => [
                [
                    'title' => 'string',
                    'question_id' => '0'
                ],
                new Standard('string', '0')
            ],
            'string_one' => [
                [
                    'title' => 'string',
                    'question_id' => '1'
                ],
                new Standard('string', '1')
            ],
            'strING_negative_one' => [
                [
                    'title' => 'strING',
                    'question_id' => '-1'
                ],
                new Standard('strING', '-1')
            ],
            'strING_zero' => [
                [
                    'title' => 'strING',
                    'question_id' => '0'
                ],
                new Standard('strING', '0')
            ],
            'strING_one' => [
                [
                    'title' => 'strING',
                    'question_id' => '1'
                ],
                new Standard('strING', '1')
            ],
            'STRING_negative_one' => [
                [
                    'title' => 'STRING',
                    'question_id' => '-1'
                ],
                new Standard('STRING', '-1')
            ],
            'STRING_zero' => [
                [
                    'title' => 'STRING',
                    'question_id' => '0'
                ],
                new Standard('STRING', '0')
            ],
            'STRING_one' => [
                [
                    'title' => 'STRING',
                    'question_id' => '1'
                ],
                new Standard('STRING', '1')
            ]
        ];
    }

    /**
     * @dataProvider getQuestionPoolLinkDataProvider
     * @throws ReflectionException|Exception
     */
    public function testGetQuestionPoolLink(?int $input, string $output): void
    {
        $question_table_binding = $this->createInstanceOf(QuestionsTableBinding::class, [
            'qpl_link_builder' => fn(?int $qpl_id) => $qpl_id . '_x'
        ]);
        $this->assertEquals($output, self::callMethod($question_table_binding, 'getQuestionPoolLink', [$input]));
    }

    public static function getQuestionPoolLinkDataProvider(): array
    {
        return [
            'null' => [
                null,
                '_x'
            ],
            'negative_one' => [
                -1,
                '-1_x'
            ],
            'zero' => [
                0,
                '0_x'
            ],
            'one' => [
                1,
                '1_x'
            ]
        ];
    }
}
