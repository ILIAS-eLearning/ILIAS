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

use ILIAS\Test\Logging\TestLogger;
use ILIAS\Test\RequestDataCollector;
use ILIAS\TestQuestionPool\Questions\GeneralQuestionPropertiesRepository;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Taxonomy\DomainService as TaxonomyService;

class ilTestQuestionBrowserTableGUIBrowseTestsFilterTest extends ilTestBaseTestCase
{
    public function testBrowseTestsLimitsQuestionsToWritableTests(): void
    {
        $parent_filters = $this->parentObjFiltersForWritableTests([11, 22]);

        $this->assertContains([11, 22], $parent_filters);
    }

    public function testBrowseTestsWithoutWritableTestsMatchesNoQuestions(): void
    {
        $parent_filters = $this->parentObjFiltersForWritableTests([]);

        $this->assertContains([0], $parent_filters);
    }

    /**
     * @param int[] $writable_test_obj_ids
     * @return array<int, int[]>
     */
    private function parentObjFiltersForWritableTests(array $writable_test_obj_ids): array
    {
        $parent_filters = [];
        $db = $this->createMock(ilDBInterface::class);
        $db->method('in')->willReturnCallback(
            static function (string $field, array $values) use (&$parent_filters): string {
                if ($field === 'qpl_questions.obj_fi') {
                    $parent_filters[] = $values;
                }
                return "{$field} IN (" . implode(',', $values) . ')';
            }
        );
        $db->method('query')->willReturn($this->createMock(ilDBStatement::class));
        $db->method('fetchAssoc')->willReturn(null);

        $request = $this->createMock(RequestDataCollector::class);
        $request->method('raw')->willReturn(ilTestQuestionBrowserTableGUI::MODE_BROWSE_TESTS);

        $test = $this->createMock(ilObjTest::class);
        $test->method('getQuestions')->willReturn([]);
        $test->method('getId')->willReturn(5);

        $browser = new class (
            $this->createMock(ilTabsGUI::class),
            $this->createMock(ilTree::class),
            $db,
            $this->createMock(TestLogger::class),
            $this->createMock(ilComponentRepository::class),
            $test,
            $this->createMock(ilObjUser::class),
            $this->createMock(ilAccessHandler::class),
            $this->createMock(GlobalHttpState::class),
            new Refinery(new DataFactory(), $this->createMock(ilLanguage::class)),
            $this->createMock(UIFactory::class),
            $this->createMock(UIRenderer::class),
            $request,
            $this->createMock(GeneralQuestionPropertiesRepository::class),
            $this->createMock(ilLanguage::class),
            $this->createMock(ilCtrl::class),
            $this->createMock(ilGlobalTemplateInterface::class),
            $this->createMock(ilUIService::class),
            $this->createMock(DataFactory::class),
            $this->createMock(TaxonomyService::class),
            $writable_test_obj_ids
        ) extends ilTestQuestionBrowserTableGUI {
            /** @param int[] $writable_test_obj_ids */
            public function __construct(
                ilTabsGUI $tabs,
                ilTree $tree,
                ilDBInterface $db,
                TestLogger $logger,
                ilComponentRepository $component_repository,
                ilObjTest $test_obj,
                ilObjUser $current_user,
                ilAccessHandler $access,
                GlobalHttpState $http_state,
                Refinery $refinery,
                UIFactory $ui_factory,
                UIRenderer $ui_renderer,
                RequestDataCollector $testrequest,
                GeneralQuestionPropertiesRepository $questionrepository,
                ilLanguage $lng,
                ilCtrl $ctrl,
                ilGlobalTemplateInterface $main_tpl,
                ilUIService $ui_service,
                DataFactory $data_factory,
                TaxonomyService $taxonomy,
                private readonly array $writable_test_obj_ids
            ) {
                parent::__construct(
                    $tabs,
                    $tree,
                    $db,
                    $logger,
                    $component_repository,
                    $test_obj,
                    $current_user,
                    $access,
                    $http_state,
                    $refinery,
                    $ui_factory,
                    $ui_renderer,
                    $testrequest,
                    $questionrepository,
                    $lng,
                    $ctrl,
                    $main_tpl,
                    $ui_service,
                    $data_factory,
                    $taxonomy
                );
            }

            protected function getWritableTestObjIds(): array
            {
                return $this->writable_test_obj_ids;
            }

            public function applyMode(ilAssQuestionList $question_list): void
            {
                $method = new ReflectionMethod(ilTestQuestionBrowserTableGUI::class, 'addModeParametersToQuestionList');
                $method->invoke($this, $question_list);
            }
        };

        $question_list = new ilAssQuestionList(
            $db,
            $this->createMock(ilLanguage::class),
            new Refinery(new DataFactory(), $this->createMock(ilLanguage::class)),
            $this->createMock(ilComponentRepository::class)
        );
        $browser->applyMode($question_list);
        $question_list->load();

        return $parent_filters;
    }
}
