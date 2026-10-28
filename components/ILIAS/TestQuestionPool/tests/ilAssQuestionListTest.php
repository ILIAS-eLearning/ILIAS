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

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Notes\Service as NotesService;
use ILIAS\Refinery\Factory as Refinery;

class ilAssQuestionListTest extends assBaseTestCase
{
    protected $backupGlobals = false;

    private ilAssQuestionList $object;

    protected function setUp(): void
    {
        parent::setUp();

        $db = $this->createMock(ilDBInterface::class);
        $lng = $this->createMock(ilLanguage::class);
        $refinery = $this->createMock(Refinery::class);
        $component_repository = $this->createMock(ilComponentRepository::class);
        $notes_service = $this->createMock(NotesService::class);

        $this->object = new ilAssQuestionList($db, $lng, $refinery, $component_repository, $notes_service);
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilAssQuestionList::class, $this->object);
    }

    public function testTaxonomyFlagQueryUsesComponentEquality(): void
    {
        $captured = new stdClass();
        $captured->query = '';
        $statement = $this->createMock(ilDBStatement::class);
        $db = $this->createMock(ilDBInterface::class);
        $db->method('query')->willReturnCallback(
            static function (string $sql) use ($captured, $statement): ilDBStatement {
                $captured->query = $sql;
                return $statement;
            }
        );
        $db->method('fetchAssoc')->willReturn(null);

        $transformation = $this->createMock(ILIAS\Refinery\Transformation::class);
        $encode = $this->createMock(ILIAS\Refinery\Encode\Group::class);
        $encode->method('htmlSpecialCharsAsEntities')->willReturn($transformation);
        $refinery = $this->createMock(ILIAS\Refinery\Factory::class);
        $refinery->method('encode')->willReturn($encode);

        $list = new ilAssQuestionList(
            $db,
            $this->createMock(ilLanguage::class),
            $refinery,
            $this->createMock(ilComponentRepository::class)
        );
        $list->load();

        $this->assertStringContainsString("tax_node_assignment.component = 'qpl'", $captured->query);
        $this->assertStringContainsString("tax_node_assignment.component = 'tst'", $captured->query);
        $this->assertStringContainsString("tax_node_assignment.item_type = 'quest'", $captured->query);
        $this->assertStringContainsString('tax_node_assignment.item_id = qpl_questions.question_id', $captured->query);
    }

    public function testPagedLoadSortsByTitleBeforeComputingFlags(): void
    {
        $captured = new stdClass();
        $list = $this->newListCapturingLoadQuery($captured);
        $list->setOrder(new Order('title', Order::ASC));
        $list->setRange(new Range(20, 10));
        $list->load();

        [$outer, $inner] = $this->splitPagedQuery($captured->query);

        $this->assertStringContainsString('qpl_questions.title AS sort_key', $inner);
        $this->assertStringContainsString('ORDER BY sort_key ASC, qpl_questions.question_id ASC', $inner);
        $this->assertStringContainsString('LIMIT 10 OFFSET 20', $inner);
        $this->assertStringNotContainsString('qpl_fb_generic', $inner);
        $this->assertStringNotContainsString('qpl_fb_specific', $inner);
        $this->assertStringNotContainsString('qpl_hints', $inner);
        $this->assertStringNotContainsString('tax_node_assignment', $inner);
        $this->assertStringNotContainsString('LIMIT', $outer);
        $this->assertStringContainsString('AS feedback', $outer);
        $this->assertStringContainsString('AS hints', $outer);
        $this->assertStringContainsString('AS taxonomies', $outer);
        $this->assertStringContainsString('ORDER BY paged.sort_key ASC, paged.question_id ASC', $captured->query);
    }

    public function testPagedLoadKeepsFeedbackFilterInsideThePageQuery(): void
    {
        $captured = new stdClass();
        $list = $this->newListCapturingLoadQuery($captured);
        $list->setOrder(new Order('title', Order::ASC));
        $list->setRange(new Range(0, 800));
        $list->addFieldFilter('feedback', 'true');
        $list->load();

        [, $inner] = $this->splitPagedQuery($captured->query);

        $this->assertStringContainsString('INNER JOIN qpl_fb_generic', $inner);
        $this->assertStringContainsString('AS feedback', $inner);
        $this->assertStringContainsString('HAVING feedback IS TRUE', $inner);
        $this->assertStringNotContainsString('qpl_hints', $inner);
        $this->assertStringNotContainsString('tax_node_assignment', $inner);
        $this->assertStringContainsString('LIMIT 800 OFFSET 0', $inner);
    }

    public function testPagedLoadKeepsHintFilterInsideThePageQuery(): void
    {
        $captured = new stdClass();
        $list = $this->newListCapturingLoadQuery($captured);
        $list->setOrder(new Order('description', Order::ASC));
        $list->setRange(new Range(0, 10));
        $list->addFieldFilter('hints', 'false');
        $list->load();

        [, $inner] = $this->splitPagedQuery($captured->query);

        $this->assertStringContainsString('LEFT JOIN qpl_hints', $inner);
        $this->assertStringContainsString('AS hints', $inner);
        $this->assertStringContainsString('HAVING hints IS FALSE', $inner);
        $this->assertStringContainsString('qpl_questions.description AS sort_key', $inner);
        $this->assertStringNotContainsString('qpl_fb_generic', $inner);
        $this->assertStringNotContainsString('tax_node_assignment', $inner);
    }

    public function testPagedLoadSortsByFeedbackBeforeLimiting(): void
    {
        $captured = new stdClass();
        $list = $this->newListCapturingLoadQuery($captured);
        $list->setOrder(new Order('feedback', Order::DESC));
        $list->setRange(new Range(10, 10));
        $list->load();

        [, $inner] = $this->splitPagedQuery($captured->query);

        $this->assertStringContainsString('qpl_fb_generic', $inner);
        $this->assertStringContainsString('AS sort_key', $inner);
        $this->assertStringNotContainsString('AS feedback', $inner);
        $this->assertStringContainsString('ORDER BY sort_key DESC, qpl_questions.question_id ASC', $inner);
        $this->assertStringNotContainsString('HAVING', $inner);
        $this->assertStringContainsString(
            'ORDER BY paged.sort_key DESC, paged.question_id ASC',
            $captured->query
        );
    }

    public function testPagedLoadKeepsFeedbackAliasWhenFilteringAndSortingByIt(): void
    {
        $captured = new stdClass();
        $list = $this->newListCapturingLoadQuery($captured);
        $list->setOrder(new Order('feedback', Order::ASC));
        $list->setRange(new Range(0, 10));
        $list->addFieldFilter('feedback', 'true');
        $list->load();

        [, $inner] = $this->splitPagedQuery($captured->query);

        $this->assertStringContainsString('AS feedback', $inner);
        $this->assertStringContainsString('AS sort_key', $inner);
        $this->assertStringContainsString('HAVING feedback IS TRUE', $inner);
        $this->assertStringContainsString('ORDER BY sort_key ASC, qpl_questions.question_id ASC', $inner);
    }

    public function testPagedLoadSortsByTaxonomiesInsideThePageQuery(): void
    {
        $captured = new stdClass();
        $list = $this->newListCapturingLoadQuery($captured);
        $list->setOrder(new Order('taxonomies', Order::ASC));
        $list->setRange(new Range(0, 5));
        $list->load();

        [, $inner] = $this->splitPagedQuery($captured->query);

        $this->assertStringContainsString("tax_node_assignment.component = 'qpl'", $inner);
        $this->assertStringContainsString("tax_node_assignment.component = 'tst'", $inner);
        $this->assertStringNotContainsString('AS taxonomies', $inner);
        $this->assertStringContainsString('ORDER BY sort_key ASC, qpl_questions.question_id ASC', $inner);
    }

    public function testPagedLoadSortsByTypeAndParentTitleWithTheirJoins(): void
    {
        $captured = new stdClass();
        $list = $this->newListCapturingLoadQuery($captured);
        $list->setOrder(new Order('type_tag', Order::ASC));
        $list->setRange(new Range(0, 5));
        $list->addFieldFilter('parent_title', 'Pool');
        $list->load();

        [, $inner] = $this->splitPagedQuery($captured->query);

        $this->assertStringContainsString('qpl_qst_type.type_tag AS sort_key', $inner);
        $this->assertStringContainsString('qpl_qst_type', $inner);
        $this->assertStringContainsString('object_data', $inner);
        $this->assertStringContainsString("title LIKE '%x%'", $inner);
    }

    public function testPagedLoadAlwaysJoinsQuestionTypeAndObjectDataForRowSelection(): void
    {
        $captured = new stdClass();
        $list = $this->newListCapturingLoadQuery($captured);
        $list->setOrder(new Order('title', Order::ASC));
        $list->setRange(new Range(0, 10));
        $list->load();

        [$outer, $inner] = $this->splitPagedQuery($captured->query);

        // The enrichment (outer) query always INNER JOINs these tables, regardless
        // of filters or sort order.
        $this->assertStringContainsString('qpl_qst_type', $outer);
        $this->assertStringContainsString('object_data', $outer);

        // The row-selecting (inner) query must join the very same tables even
        // though nothing here filters or sorts by type or parent title. Otherwise
        // a question without a matching qpl_qst_type or object_data row would be
        // paged/counted here but dropped later by the enrichment INNER JOIN,
        // shrinking the page below LIMIT and making that question unreachable.
        $this->assertStringContainsString('qpl_qst_type', $inner);
        $this->assertStringContainsString('object_data', $inner);
    }

    public function testPagedLoadOmitsObjectDataJoinForRowSelectionWhenDisabled(): void
    {
        $captured = new stdClass();
        $list = $this->newListCapturingLoadQuery($captured);
        $list->setJoinObjectData(false);
        $list->setOrder(new Order('title', Order::ASC));
        $list->setRange(new Range(0, 10));
        $list->load();

        [$outer, $inner] = $this->splitPagedQuery($captured->query);

        $this->assertStringNotContainsString('object_data', $outer);
        $this->assertStringNotContainsString('object_data', $inner);
        $this->assertStringContainsString('qpl_qst_type', $inner);
    }

    public function testTotalRowCountWithHintsFilterDeduplicatesJoinedHintRows(): void
    {
        $captured = new stdClass();
        $list = $this->newListCapturingLoadQuery($captured);
        $list->setOrder(new Order('title', Order::ASC));
        $list->setRange(new Range(0, 10));
        $list->addFieldFilter('hints', 'true');

        $this->assertSame(4, $list->getTotalRowCount([], null));

        // A question with several rows in qpl_hints is multiplied by the INNER
        // JOIN below, so the counting subquery must stay SELECT DISTINCT on
        // question_id, otherwise such a question would be counted more than once.
        $this->assertStringStartsWith('SELECT COUNT(*) FROM (SELECT DISTINCT', $captured->query);
        $this->assertStringContainsString('INNER JOIN qpl_hints', $captured->query);
        $this->assertStringContainsString('HAVING hints IS TRUE', $captured->query);
    }

    public function testCountWithHintsFilterKeepsTestUsageJoinSeparated(): void
    {
        $captured = new stdClass();
        $list = $this->newListCapturingLoadQuery($captured);
        $list->setParentObjectType('tst');
        $list->setQuestionInstanceTypeFilter(ilAssQuestionList::QUESTION_INSTANCE_TYPE_ALL);
        $list->setOrder(new Order('title', Order::ASC));
        $list->setRange(new Range(0, 10));
        $list->addFieldFilter('hints', 'true');

        $list->getTotalRowCount([], null);

        $this->assertStringNotContainsString('question_idINNER', $captured->query);
        $this->assertStringContainsString(
            'qpl_questions.question_id INNER JOIN qpl_hints',
            preg_replace('/\s+/', ' ', $captured->query)
        );
    }

    public function testPagedLoadKeepsTestUsageJoinInsideThePageQuery(): void
    {
        $captured = new stdClass();
        $list = $this->newListCapturingLoadQuery($captured);
        $list->setParentObjectType('tst');
        $list->setQuestionInstanceTypeFilter(ilAssQuestionList::QUESTION_INSTANCE_TYPE_ALL);
        $list->setOrder(new Order('title', Order::ASC));
        $list->setRange(new Range(0, 5));
        $list->load();

        [$outer, $inner] = $this->splitPagedQuery($captured->query);

        $this->assertStringContainsString('tst_test_question', $inner);
        $this->assertStringNotContainsString('tst_test_question', $outer);
    }

    public function testPagedLoadKeepsAnswerStatusFilterInsideAndPointsOutside(): void
    {
        $captured = new stdClass();
        $list = $this->newListCapturingLoadQuery($captured);
        $list->setAnswerStatusActiveId(15);
        $list->setAnswerStatusFilter(ilAssQuestionList::ANSWER_STATUS_FILTER_WRONG_ANSWERED_ONLY);
        $list->setOrder(new Order('title', Order::ASC));
        $list->setRange(new Range(0, 5));
        $list->load();

        [$outer, $inner] = $this->splitPagedQuery($captured->query);

        $this->assertStringContainsString('tst_test_result', $inner);
        $this->assertStringContainsString("tst_test_result.active_fi = '15'", $inner);
        $this->assertStringContainsString('tst_test_result.question_fi IS NOT NULL', $inner);
        $this->assertStringContainsString('reached_points', $outer);
        $this->assertStringNotContainsString('tst_test_result.question_fi IS NOT NULL', $outer);
    }

    public function testPagedLoadShowsAnswerPointsWithoutFilteringInsideThePageQuery(): void
    {
        $captured = new stdClass();
        $list = $this->newListCapturingLoadQuery($captured);
        $list->setAnswerStatusActiveId(15);
        $list->setOrder(new Order('title', Order::ASC));
        $list->setRange(new Range(0, 5));
        $list->load();

        [$outer, $inner] = $this->splitPagedQuery($captured->query);

        $this->assertStringNotContainsString('tst_test_result', $inner);
        $this->assertStringContainsString('reached_points', $outer);
        $this->assertStringContainsString('tst_test_result', $outer);
    }

    public function testUnpagedLoadKeepsASingleQuery(): void
    {
        $captured = new stdClass();
        $list = $this->newListCapturingLoadQuery($captured);
        $list->setOrder(new Order('title', Order::DESC));
        $list->addFieldFilter('feedback', 'false');
        $list->load();

        $this->assertStringNotContainsString('paged', $captured->query);
        $this->assertStringContainsString('ORDER BY qpl_questions.title DESC', $captured->query);
        $this->assertStringNotContainsString('`title`', $captured->query);
        $this->assertStringContainsString('HAVING feedback IS FALSE', $captured->query);
        $this->assertStringContainsString("tax_node_assignment.component = 'qpl'", $captured->query);
    }

    public function testLoadRejectsOrderFieldOutsideAllowList(): void
    {
        $captured = new stdClass();
        $list = $this->newListCapturingLoadQuery($captured);
        $list->setOrder(new Order('title; DROP TABLE qpl_questions', Order::ASC));
        $list->setRange(new Range(0, 10));

        $this->expectException(ilTestQuestionPoolException::class);
        $list->load();
    }

    public function testTotalRowCountWithoutFlagFilterStaysADirectCount(): void
    {
        $captured = new stdClass();
        $list = $this->newListCapturingLoadQuery($captured);
        $list->setOrder(new Order('feedback', Order::ASC));
        $list->setRange(new Range(0, 10));

        $this->assertSame(4, $list->getTotalRowCount([], null));
        $this->assertStringStartsWith('SELECT COUNT(*) FROM qpl_questions', $captured->query);
        $this->assertStringNotContainsString('paged', $captured->query);
        $this->assertStringNotContainsString('HAVING', $captured->query);
        $this->assertStringNotContainsString('LIMIT', $captured->query);
    }

    public function testTotalRowCountWithFlagFilterUsesTheUnlimitedPageQuery(): void
    {
        $captured = new stdClass();
        $list = $this->newListCapturingLoadQuery($captured);
        $list->setOrder(new Order('title', Order::ASC));
        $list->setRange(new Range(40, 10));
        $list->addFieldFilter('feedback', 'true');
        $list->addFieldFilter('hints', 'false');

        $this->assertSame(4, $list->getTotalRowCount([], null));
        $this->assertStringStartsWith('SELECT COUNT(*) FROM (', $captured->query);
        $this->assertStringContainsString('HAVING feedback IS TRUE AND hints IS FALSE', $captured->query);
        $this->assertStringContainsString('AS feedback', $captured->query);
        $this->assertStringContainsString('AS hints', $captured->query);
        $this->assertStringContainsString('qpl_questions.title AS sort_key', $captured->query);
        $this->assertStringContainsString('ORDER BY sort_key ASC, qpl_questions.question_id ASC', $captured->query);
        $this->assertStringNotContainsString('LIMIT', $captured->query);
    }

    public function testPagedLoadDoesNotExposeTheSortKey(): void
    {
        $captured = new stdClass();
        $list = $this->newListCapturingLoadQuery(
            $captured,
            [
                'question_id' => 5,
                'title' => 'Same title',
                'description' => '',
                'author' => 'Ann',
                'plugin' => 0,
                'type_tag' => 'assSingleChoice',
                'feedback' => 1,
                'hints' => 0,
                'obj_fi' => 1,
                'sort_key' => 'Same title',
            ]
        );
        $list->setOrder(new Order('title', Order::ASC));
        $list->setRange(new Range(0, 10));
        $list->load();

        $this->assertArrayNotHasKey('sort_key', $list->getDataArrayForQuestionId(5));
        $this->assertSame(5, $list->getDataArrayForQuestionId(5)['question_id']);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitPagedQuery(string $sql): array
    {
        $marker = 'INNER JOIN (';
        $this->assertStringContainsString($marker, $sql);
        $this->assertStringContainsString(') paged', $sql);

        $start = strpos($sql, $marker);
        $end = strpos($sql, ') paged');

        return [
            substr($sql, 0, $start),
            substr($sql, $start + strlen($marker), $end - ($start + strlen($marker))),
        ];
    }

    private function newListCapturingLoadQuery(stdClass &$captured, ?array $row = null): ilAssQuestionList
    {
        $captured = new stdClass();
        $captured->query = '';
        $statement = $this->createMock(ilDBStatement::class);
        $statement->method('fetch')->willReturn(['COUNT(*)' => 4]);
        $db = $this->createMock(ilDBInterface::class);
        $db->method('query')->willReturnCallback(
            static function (string $sql) use ($captured, $statement): ilDBStatement {
                $captured->query = $sql;
                return $statement;
            }
        );
        if ($row !== null) {
            $db->method('fetchAssoc')->willReturnOnConsecutiveCalls($row, null);
        } else {
            $db->method('fetchAssoc')->willReturn(null);
        }
        $db->method('quote')->willReturnCallback(
            static fn(mixed $value): string => "'" . $value . "'"
        );
        $db->method('like')->willReturn("title LIKE '%x%'");
        $db->method('in')->willReturn('question_id IN (1)');

        $transformation = $this->createMock(ILIAS\Refinery\Transformation::class);
        $encode = $this->createMock(ILIAS\Refinery\Encode\Group::class);
        $encode->method('htmlSpecialCharsAsEntities')->willReturn($transformation);
        $refinery = $this->createMock(ILIAS\Refinery\Factory::class);
        $refinery->method('encode')->willReturn($encode);

        return new ilAssQuestionList(
            $db,
            $this->createMock(ilLanguage::class),
            $refinery,
            $this->createMock(ilComponentRepository::class)
        );
    }
}
