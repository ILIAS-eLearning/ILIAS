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
 * If you are not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\Data\Order;
use ILIAS\Data\Range;

class ilAssQuestionListTwoPhaseTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    private ilAssQuestionList $object;

    protected function setUp(): void
    {
        parent::setUp();

        $db = $this->createMock(ilDBInterface::class);
        $db->method('like')->willReturn('1');
        $db->method('in')->willReturnCallback(function (string $field, array $values, bool $negate = false): string {
            if ($values === []) {
                return $negate ? ' 1=1 ' : ' 1=2 ';
            }
            $list = implode(',', array_map('intval', $values));
            return ($negate ? 'NOT ' : '') . "{$field} IN ({$list})";
        });
        $db->method('quote')->willReturnCallback(fn($v) => is_numeric($v) ? (string) $v : "'{$v}'");

        $lng = $this->createMock(ilLanguage::class);
        $refinery = $this->createMock(ILIAS\Refinery\Factory::class);
        $component_repository = $this->createMock(ilComponentRepository::class);
        $notes_service = $this->createMock(ILIAS\Notes\Service::class);

        $this->object = new ilAssQuestionList($db, $lng, $refinery, $component_repository, $notes_service);
    }

    private function setPrivateProperty(string $name, mixed $value): void
    {
        $prop = new ReflectionProperty(ilAssQuestionList::class, $name);
        $prop->setAccessible(true);
        $prop->setValue($this->object, $value);
    }

    private function invokePrivate(string $name, array $args = []): mixed
    {
        $method = new ReflectionMethod(ilAssQuestionList::class, $name);
        $method->setAccessible(true);
        return $method->invokeArgs($this->object, $args);
    }

    public function testRequiredComputedFieldsReturnsEmptyWithoutFiltersOrComputedOrder(): void
    {
        $this->setPrivateProperty('order', new Order('title', Order::ASC));
        $this->assertSame([], $this->invokePrivate('requiredComputedFields'));
    }

    public function testRequiredComputedFieldsReturnsFeedbackForOrderByFeedback(): void
    {
        $this->setPrivateProperty('order', new Order('feedback', Order::ASC));
        $result = $this->invokePrivate('requiredComputedFields');
        $this->assertContains('feedback', $result);
        $this->assertCount(1, $result);
    }

    public function testRequiredComputedFieldsReturnsHintsForOrderByHints(): void
    {
        $this->setPrivateProperty('order', new Order('hints', Order::ASC));
        $result = $this->invokePrivate('requiredComputedFields');
        $this->assertContains('hints', $result);
        $this->assertCount(1, $result);
    }

    public function testRequiredComputedFieldsReturnsTaxonomiesForOrderByTaxonomies(): void
    {
        $this->setPrivateProperty('order', new Order('taxonomies', Order::ASC));
        $result = $this->invokePrivate('requiredComputedFields');
        $this->assertContains('taxonomies', $result);
        $this->assertCount(1, $result);
    }

    public function testRequiredComputedFieldsReturnsFeedbackForFeedbackFilter(): void
    {
        $this->setPrivateProperty('order', new Order('title', Order::ASC));
        $this->setPrivateProperty('fieldFilters', ['feedback' => 'false']);
        $result = $this->invokePrivate('requiredComputedFields');
        $this->assertContains('feedback', $result);
        $this->assertCount(1, $result);
    }

    public function testRequiredComputedFieldsReturnsHintsForHintsFilter(): void
    {
        $this->setPrivateProperty('order', new Order('title', Order::ASC));
        $this->setPrivateProperty('fieldFilters', ['hints' => 'false']);
        $result = $this->invokePrivate('requiredComputedFields');
        $this->assertContains('hints', $result);
        $this->assertCount(1, $result);
    }

    public function testRequiredComputedFieldsReturnsMultipleForFeedbackFilterAndHintsOrder(): void
    {
        $this->setPrivateProperty('order', new Order('hints', Order::ASC));
        $this->setPrivateProperty('fieldFilters', ['feedback' => 'true']);
        $result = $this->invokePrivate('requiredComputedFields');
        $this->assertContains('feedback', $result);
        $this->assertContains('hints', $result);
        $this->assertCount(2, $result);
    }

    public function testRequiredComputedFieldsReturnsEmptyWithoutOrder(): void
    {
        $this->setPrivateProperty('order', null);
        $this->assertSame([], $this->invokePrivate('requiredComputedFields'));
    }

    public function testQualifyFieldMapsKnownFields(): void
    {
        $this->assertSame('qpl_questions.title', $this->invokePrivate('qualifyField', ['title']));
        $this->assertSame('qpl_questions.description', $this->invokePrivate('qualifyField', ['description']));
        $this->assertSame('qpl_questions.author', $this->invokePrivate('qualifyField', ['author']));
        $this->assertSame('qpl_questions.points', $this->invokePrivate('qualifyField', ['points']));
        $this->assertSame('qpl_questions.points', $this->invokePrivate('qualifyField', ['max_points']));
        $this->assertSame('qpl_questions.created', $this->invokePrivate('qualifyField', ['created']));
        $this->assertSame('qpl_questions.tstamp', $this->invokePrivate('qualifyField', ['tstamp']));
        $this->assertSame('qpl_qst_type.type_tag', $this->invokePrivate('qualifyField', ['type_tag']));
        $this->assertSame('object_data.title', $this->invokePrivate('qualifyField', ['parent_title']));
    }

    public function testQualifyFieldLeavesComputedFieldsUnchanged(): void
    {
        $this->assertSame('feedback', $this->invokePrivate('qualifyField', ['feedback']));
        $this->assertSame('hints', $this->invokePrivate('qualifyField', ['hints']));
        $this->assertSame('taxonomies', $this->invokePrivate('qualifyField', ['taxonomies']));
    }

    public function testQualifyAndBacktickFieldWrapsSimpleField(): void
    {
        $this->assertSame('`qpl_questions`.`title`', $this->invokePrivate('qualifyAndBacktickField', ['title']));
    }

    public function testQualifyAndBacktickFieldWrapsAlreadyQualifiedField(): void
    {
        $result = $this->invokePrivate('qualifyAndBacktickField', ['type_tag']);
        $this->assertSame('`qpl_qst_type`.`type_tag`', $result);
    }

    public function testBuildOrderQueryExpressionQualifiesAndBackticksField(): void
    {
        $this->setPrivateProperty('order', new Order('title', Order::ASC));
        $sql = $this->invokePrivate('buildOrderQueryExpression');
        $this->assertStringContainsString('`qpl_questions`.`title`', $sql);
        $this->assertStringContainsString('ASC', $sql);
    }

    public function testBuildOrderQueryExpressionReturnsEmptyWithoutOrder(): void
    {
        $this->setPrivateProperty('order', null);
        $this->assertSame('', $this->invokePrivate('buildOrderQueryExpression'));
    }

    public function testBuildBasicQueryWithoutComputedHasNoExistsSubqueries(): void
    {
        $this->setPrivateProperty('join_obj_data', true);
        $sql = $this->invokePrivate('buildBasicQuery', [[]]);
        $this->assertStringContainsString('qpl_questions.*', $sql);
        $this->assertStringNotContainsString('EXISTS', $sql);
        $this->assertStringNotContainsString('qpl_fb_generic', $sql);
        $this->assertStringNotContainsString('qpl_hints', $sql);
        $this->assertStringNotContainsString('tax_node_assignment', $sql);
    }

    public function testBuildBasicQueryWithAllComputedContainsAllExistsSubqueries(): void
    {
        $this->setPrivateProperty('join_obj_data', true);
        $sql = $this->invokePrivate('buildBasicQuery', [['feedback', 'hints', 'taxonomies']]);
        $this->assertStringContainsString('EXISTS', $sql);
        $this->assertStringContainsString('qpl_fb_generic', $sql);
        $this->assertStringContainsString('qpl_hints', $sql);
        $this->assertStringContainsString('tax_node_assignment', $sql);
    }

    public function testBuildBasicQueryWithOnlyFeedbackContainsOnlyFeedbackSubquery(): void
    {
        $this->setPrivateProperty('join_obj_data', true);
        $sql = $this->invokePrivate('buildBasicQuery', [['feedback']]);
        $this->assertStringContainsString('EXISTS', $sql);
        $this->assertStringContainsString('qpl_fb_generic', $sql);
        $this->assertStringNotContainsString('qpl_hints', $sql);
        $this->assertStringNotContainsString('tax_node_assignment', $sql);
    }

    public function testBuildEnrichmentQueryContainsOnlyRequestedExistsSubqueries(): void
    {
        $this->setPrivateProperty('join_obj_data', true);
        $sql = $this->invokePrivate('buildEnrichmentQuery', [[10, 20, 30], ['hints', 'taxonomies']]);
        $this->assertStringContainsString('EXISTS', $sql);
        $this->assertStringNotContainsString('qpl_fb_generic', $sql);
        $this->assertStringContainsString('qpl_hints', $sql);
        $this->assertStringContainsString('tax_node_assignment', $sql);
        $this->assertStringContainsString('qpl_questions.question_id IN (10,20,30)', $sql);
        $this->assertStringNotContainsString('LIMIT', $sql);
    }

    public function testBuildEnrichmentQueryHandlesEmptyIdList(): void
    {
        $this->setPrivateProperty('join_obj_data', true);
        $sql = $this->invokePrivate('buildEnrichmentQuery', [[], ['feedback']]);
        $this->assertStringContainsString('1=2', $sql);
    }

    public function testBuildQueryWithoutComputedAppliesRangeAndOrder(): void
    {
        $this->setPrivateProperty('range', new Range(0, 800));
        $this->setPrivateProperty('order', new Order('title', Order::ASC));
        $this->setPrivateProperty('join_obj_data', true);
        $sql = $this->invokePrivate('buildQuery');
        $this->assertStringNotContainsString('EXISTS', $sql);
        $this->assertStringContainsString('`qpl_questions`.`title`', $sql);
        $this->assertStringContainsString('LIMIT 800 OFFSET 0', $sql);
    }

    public function testBuildQueryWithComputedOrderIncludesOnlyRelevantSubquery(): void
    {
        $this->setPrivateProperty('range', new Range(0, 800));
        $this->setPrivateProperty('order', new Order('feedback', Order::ASC));
        $this->setPrivateProperty('join_obj_data', true);
        $sql = $this->invokePrivate('buildQuery');
        $this->assertStringContainsString('EXISTS', $sql);
        $this->assertStringContainsString('qpl_fb_generic', $sql);
        $this->assertStringNotContainsString('qpl_hints', $sql);
        $this->assertStringNotContainsString('tax_node_assignment', $sql);
        $this->assertStringContainsString('LIMIT 800 OFFSET 0', $sql);
    }
}
