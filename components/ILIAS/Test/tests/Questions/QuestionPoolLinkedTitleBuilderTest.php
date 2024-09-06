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

use ilAccessHandler;
use ilCtrl;
use ILIAS\Test\Questions\QuestionPoolLinkedTitleBuilder;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ilLanguage;
use ilTestBaseTestCase;
use PHPUnit\Framework\MockObject\Exception;
use ReflectionException;

class QuestionPoolLinkedTitleBuilderTest extends ilTestBaseTestCase
{
    /**
     * @throws ReflectionException|Exception
     */
    public function testConstruct(): void
    {
        $question_pool_linked_title_builder = $this->createTraitInstanceOf(QuestionPoolLinkedTitleBuilder::class);
        $this->assertInstanceOf(self::DYNAMIC_CLASS . self::$DYNAMIC_CLASS_COUNT, $question_pool_linked_title_builder);
    }

    /**
     * @dataProvider buildPossiblyLinkedTestTitleDataProvider
     * @throws ReflectionException|Exception
     */
    public function testBuildPossiblyLinkedTestTitle(string $input, string $output): void
    {
        $question_pool_linked_title_builder = $this->createTraitInstanceOf(QuestionPoolLinkedTitleBuilder::class);

        $this->assertEquals(
            $output,
            $question_pool_linked_title_builder->buildPossiblyLinkedTestTitle(
                $this->createMock(ilCtrl::class),
                $this->createMock(ilAccessHandler::class),
                $this->createMock(ilLanguage::class),
                $this->createMock(Factory::class),
                $this->createMock(Renderer::class),
                1,
                $input,
                false
            )
        );
    }

    public static function buildPossiblyLinkedTestTitleDataProvider(): array
    {
        return [
            'empty' => ['', ' ()'],
            'string' => ['string', 'string ()'],
            'strING' => ['strING', 'strING ()'],
            'STRING' => ['STRING', 'STRING ()']
        ];
    }

    /**
     * @dataProvider buildPossiblyLinkedQuestionPoolTitleDataProvider
     * @throws ReflectionException|Exception
     */
    public function testBuildPossiblyLinkedQuestionPoolTitle(array $input, string $output): void
    {
        $question_pool_linked_title_builder = $this->createTraitInstanceOf(QuestionPoolLinkedTitleBuilder::class);

        $this->assertEquals(
            $output,
            $question_pool_linked_title_builder->buildPossiblyLinkedQuestionPoolTitle(
                $this->createMock(ilCtrl::class),
                $this->createMock(ilAccessHandler::class),
                $this->createMock(ilLanguage::class),
                $this->createMock(Factory::class),
                $this->createMock(Renderer::class),
                $input['qpl_id'],
                $input['title'],
                false
            )
        );
    }

    public static function buildPossiblyLinkedQuestionPoolTitleDataProvider(): array
    {
        return [
            'null_empty' => [['qpl_id' => null, 'title' => ''], ''],
            'negative_one_empty' => [['qpl_id' => -1, 'title' => ''], ''],
            'zero_empty' => [['qpl_id' => 0, 'title' => ''], ''],
            'one_empty' => [['qpl_id' => 1, 'title' => ''], ''],
            'null_string' => [['qpl_id' => null, 'title' => 'string'], 'string'],
            'negative_one_string' => [['qpl_id' => -1, 'title' => 'string'], ''],
            'zero_string' => [['qpl_id' => 0, 'title' => 'string'], ''],
            'one_string' => [['qpl_id' => 1, 'title' => 'string'], ''],
            'null_strING' => [['qpl_id' => null, 'title' => 'strING'], 'strING'],
            'negative_one_strING' => [['qpl_id' => -1, 'title' => 'strING'], ''],
            'zero_strING' => [['qpl_id' => 0, 'title' => 'strING'], ''],
            'one_strING' => [['qpl_id' => 1, 'title' => 'strING'], ''],
            'null_STRING' => [['qpl_id' => null, 'title' => 'STRING'], 'STRING'],
            'negative_one_STRING' => [['qpl_id' => -1, 'title' => 'STRING'], ''],
            'zero_STRING' => [['qpl_id' => 0, 'title' => 'STRING'], ''],
            'one_STRING' => [['qpl_id' => 1, 'title' => 'STRING'], '']
        ];
    }
}
