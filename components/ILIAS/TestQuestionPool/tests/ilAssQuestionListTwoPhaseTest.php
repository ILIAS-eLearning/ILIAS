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

use ILIAS\Data\Order;
use ILIAS\Data\Range;

/**
 * Unit tests for the two-phase query loading optimisation in
 * ilAssQuestionList (performance fix for the test "add from pool"
 * question browser).
 */
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
            return ($negate ? 'NOT ' : '') . "$field IN ($list)";
        });
        $db->method('quote')->willReturnCallback(fn($v) => is_numeric($v) ? (string) $v : "'$v'");

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

    public function testCanUseTwoPhaseQueryReturnsFalseWithoutRange(): void
    {
        $this->setPrivateProperty('range', null);
        $this->assertFalse($this->invokePrivate('canUseTwoPhaseQuery'));
    }

    public function testCanUseTwoPhaseQueryReturnsTrueWithRangeAndSimpleOrder(): void
    {
        $this->setPrivateProperty('range', new Range(0, 800));
        $this->setPrivateProperty('order', new Order('title', Order::ASC));
        $this->assertTrue($this->invokePrivate('canUseTwoPhaseQuery'));
    }

    public function testCanUseTwoPhaseQueryReturnsFalseForOrderByFeedback(): void
    {
        $this->setPrivateProperty('range', new Range(0, 800));
        $this->setPrivateProperty('order', new Order('feedback', Order::ASC));
        $this->assertFalse($this->invokePrivate('canUseTwoPhaseQuery'));
    }

    public function testCanUseTwoPhaseQueryReturnsFalseForOrderByHints(): void
    {
        $this->setPrivateProperty('range', new Range(0, 800));
        $this->setPrivateProperty('order', new Order('hints', Order::ASC));
        $this->assertFalse($this->invokePrivate('canUseTwoPhaseQuery'));
    }

    public function testCanUseTwoPhaseQueryReturnsFalseForOrderByTaxonomies(): void
    {
        $this->setPrivateProperty('range', new Range(0, 800));
        $this->setPrivateProperty('order', new Order('taxonomies', Order::ASC));
        $this->assertFalse($this->invokePrivate('canUseTwoPhaseQuery'));
    }

    public function testCanUseTwoPhaseQueryReturnsFalseWithFeedbackFalseFilter(): void
    {
        $this->setPrivateProperty('range', new Range(0, 800));
        $this->setPrivateProperty('order', new Order('title', Order::ASC));
        $this->setPrivateProperty('fieldFilters', ['feedback' => 'false']);
        $this->assertFalse($this->invokePrivate('canUseTwoPhaseQuery'));
    }

    public function testCanUseTwoPhaseQueryReturnsFalseWithFeedbackTrueFilter(): void
    {
        // feedback=true uses an INNER JOIN but ALSO a HAVING clause -> fallback
        $this->setPrivateProperty('range', new Range(0, 800));
        $this->setPrivateProperty('order', new Order('title', Order::ASC));
        $this->setPrivateProperty('fieldFilters', ['feedback' => 'true']);
        $this->assertFalse($this->invokePrivate('canUseTwoPhaseQuery'));
    }

    public function testQualifyOrderFieldMapsKnownFields(): void
    {
        $this->assertSame('qpl_questions.title', $this->invokePrivate('qualifyOrderField', ['title']));
        $this->assertSame('qpl_questions.description', $this->invokePrivate('qualifyOrderField', ['description']));
        $this->assertSame('qpl_questions.author', $this->invokePrivate('qualifyOrderField', ['author']));
        $this->assertSame('qpl_questions.points', $this->invokePrivate('qualifyOrderField', ['points']));
        $this->assertSame('qpl_questions.points', $this->invokePrivate('qualifyOrderField', ['max_points']));
        $this->assertSame('qpl_questions.created', $this->invokePrivate('qualifyOrderField', ['created']));
        $this->assertSame('qpl_questions.tstamp', $this->invokePrivate('qualifyOrderField', ['tstamp']));
        $this->assertSame('qpl_qst_type.type_tag', $this->invokePrivate('qualifyOrderField', ['type_tag']));
        $this->assertSame('object_data.title', $this->invokePrivate('qualifyOrderField', ['parent_title']));
    }

    public function testQualifyOrderFieldLeavesUnknownFieldsUnchanged(): void
    {
        $this->assertSame('feedback', $this->invokePrivate('qualifyOrderField', ['feedback']));
        $this->assertSame('hints', $this->invokePrivate('qualifyOrderField', ['hints']));
        $this->assertSame('taxonomies', $this->invokePrivate('qualifyOrderField', ['taxonomies']));
    }

    public function testBuildPaginatedIdsQueryDoesNotContainExistsSubqueries(): void
    {
        $this->setPrivateProperty('range', new Range(0, 800));
        $this->setPrivateProperty('order', new Order('title', Order::ASC));
        $this->setPrivateProperty('join_obj_data', true);

        $sql = $this->invokePrivate('buildPaginatedIdsQuery');

        $this->assertStringContainsString('SELECT qpl_questions.question_id', $sql);
        $this->assertStringNotContainsString('EXISTS', $sql);
        $this->assertStringNotContainsString('qpl_fb_generic', $sql);
        $this->assertStringNotContainsString('qpl_hints', $sql);
        $this->assertStringNotContainsString('tax_node_assignment', $sql);
        $this->assertStringContainsString('`qpl_questions`.`title`', $sql);
        $this->assertStringContainsString('GROUP BY qpl_questions.question_id', $sql);
        $this->assertStringContainsString('LIMIT 800 OFFSET 0', $sql);
    }

    public function testBuildEnrichmentQueryContainsExistsSubqueriesAndInClause(): void
    {
        $this->setPrivateProperty('join_obj_data', true);

        $sql = $this->invokePrivate('buildEnrichmentQuery', [[10, 20, 30]]);

        $this->assertStringContainsString('EXISTS', $sql);
        $this->assertStringContainsString('qpl_fb_generic', $sql);
        $this->assertStringContainsString('qpl_hints', $sql);
        $this->assertStringContainsString('tax_node_assignment', $sql);
        $this->assertStringContainsString('qpl_questions.question_id IN (10,20,30)', $sql);
        $this->assertStringNotContainsString('LIMIT', $sql);
    }

    public function testBuildEnrichmentQueryHandlesEmptyIdList(): void
    {
        $this->setPrivateProperty('join_obj_data', true);
        $sql = $this->invokePrivate('buildEnrichmentQuery', [[]]);
        $this->assertStringContainsString('1=2', $sql);
    }

    public function testBuildOrderQueryExpressionQualifiesWhenRequested(): void
    {
        $this->setPrivateProperty('order', new Order('title', Order::ASC));
        $sql = $this->invokePrivate('buildOrderQueryExpression', [true]);
        $this->assertStringContainsString('`qpl_questions`.`title`', $sql);
    }

    public function testBuildOrderQueryExpressionDoesNotQualifyByDefault(): void
    {
        $this->setPrivateProperty('order', new Order('title', Order::ASC));
        $sql = $this->invokePrivate('buildOrderQueryExpression');
        $this->assertStringNotContainsString('qpl_questions', $sql);
        $this->assertStringContainsString('`title`', $sql);
    }

    public function testIsOrderByComputedFieldReturnsFalseWithoutOrder(): void
    {
        $this->setPrivateProperty('order', null);
        $this->assertFalse($this->invokePrivate('isOrderByComputedField'));
    }
}
