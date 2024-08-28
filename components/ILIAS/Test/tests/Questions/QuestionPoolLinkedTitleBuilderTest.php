<?php

namespace Questions;

use ILIAS\Test\Questions\QuestionPoolLinkedTitleBuilder;
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
        $this->assertInstanceOf(self::DYNAMIC_CLASS, $question_pool_linked_title_builder);
    }
}