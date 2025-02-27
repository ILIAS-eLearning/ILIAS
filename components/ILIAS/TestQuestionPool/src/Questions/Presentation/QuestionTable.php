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

namespace ILIAS\TestQuestionPool\Questions\Presentation;

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Table;
use ILIAS\UI\Component\Input\Container\Filter\Standard as Filter;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Taxonomy\DomainService as TaxonomyService;
use ILIAS\Notes\Service as NotesService;

class QuestionTable extends \ilAssQuestionList implements Table\DataRetrieval
{
    public function __construct(
        protected UIFactory $ui_factory,
        protected UIRenderer $ui_renderer,
        protected DataFactory $data_factory,
        protected Refinery $refinery,
        protected URLBuilder $url_builder,
        protected URLBuilderToken $action_parameter_token,
        protected URLBuilderToken $row_id_token,
        protected \ilDBInterface $db,
        protected \ilLanguage $lng,
        protected \ilComponentRepository $component_repository,
        protected \ilRbacSystem $rbac,
        protected \ilObjUser $current_user,
        protected TaxonomyService $taxonomy,
        protected NotesService $notes_service,
        protected int $parent_obj_id,
        protected int $request_ref_id
    ) {
        $lng->loadLanguageModule('qpl');
        parent::__construct($db, $lng, $refinery, $component_repository, $notes_service);
        $this->setAvailableTaxonomyIds($taxonomy->getUsageOfObject($parent_obj_id));
    }

    public function getTable(): Table\Data
    {
        return $this->ui_factory->table()->data(
            $this->lng->txt('questions'),
            $this->getColums(),
            $this
        )
        ->withActions($this->getActions())
        ->withId('qpt' . $this->parent_obj_id . '_' . $this->request_ref_id);
    }

    /**
     * Filters should be part of the Table; for now, since they are not fully
     * integrated, they are rendered and applied seperately
     */
    public function getFilter(\ilUIService $ui_service, string $action): Filter
    {
        $lifecycle_options = array_merge(
            ['' => $this->lng->txt('qst_lifecycle_filter_all')],
            \ilAssQuestionLifecycle::getDraftInstance()->getSelectOptions($this->lng)
        );
        $question_type_options = [
            '' => $this->lng->txt('filter_all_question_types')
        ];
        $question_types = \ilObjQuestionPool::_getQuestionTypes();
        foreach ($question_types as $translation => $row) {
            $question_type_options[$row['type_tag']] = $translation;
        }

        $field_factory = $this->ui_factory->input()->field();
        $filter_inputs = [
            'title' => $field_factory->text($this->lng->txt("title")),
            'description' => $field_factory->text($this->lng->txt("description")),
            'author' => $field_factory->text($this->lng->txt("author")),
            'lifecycle' => $field_factory->select($this->lng->txt("qst_lifecycle"), $lifecycle_options),
            'type' => $field_factory->select($this->lng->txt("type"), $question_type_options),
            'commented' => $field_factory->select(
                $this->lng->txt("ass_comments"),
                [
                    \ilAssQuestionList::QUESTION_COMMENTED_ONLY => $this->lng->txt('qpl_filter_commented_only'),
                    \ilAssQuestionList::QUESTION_COMMENTED_EXCLUDED => $this->lng->txt('qpl_filter_commented_exclude')
                ]
            )
        ];

        $taxs = $this->taxonomy->getUsageOfObject($this->parent_obj_id, true);
        $tax_filter_options = [
            'null' => '<b>' . $this->lng->txt('tax_filter_notax') . '</b>'
        ];

        foreach ($taxs as $tax_entry) {
            $tax = new \ilObjTaxonomy($tax_entry['tax_id']);
            $tax_tree = $tax->getTree();
            $sortfield = $tax->getSortingMode() === \ilObjTaxonomy::SORT_ALPHABETICAL ? 'title' : 'order_nr';
            $children = $this->taxNodeReader($tax_tree, $sortfield, $tax_tree->readRootId());
            $nodes = implode('-', array_map(fn($node) => $node['obj_id'], $children));

            $tax_id = $tax_entry['tax_id'] . '-0-' . $nodes;
            $tax_title = '<b>' . $tax_entry['title'] . '</b>';
            $tax_filter_options[$tax_id] = $tax_title;

            foreach ($children as $subtax) {
                $stax_id = $subtax['tax_id'] . '-' . $subtax['obj_id'];
                $stax_title = str_repeat('&nbsp; ', ($subtax['depth'] - 2) * 2)
                    . ' &boxur;&HorizontalLine; '
                    . $subtax['title'];

                $tax_filter_options[$stax_id] = $stax_title;
            }
        }
        $filter_inputs['taxonomies'] = $field_factory->multiSelect($this->lng->txt("tax_filter"), $tax_filter_options);

        $active = array_fill(0, count($filter_inputs), true);

        $filter = $ui_service->filter()->standard(
            'question_table_filter_id',
            $action,
            $filter_inputs,
            $active,
            true,
            true
        );
        return $filter;
    }


    public function getColums(): array
    {
        $f = $this->ui_factory->table()->column();
        $df = $this->data_factory->dateFormat();
        $icon_yes = $this->ui_factory->symbol()->icon()->custom(\ilUtil::getImagePath('standard/icon_checked.svg'), 'yes');
        $icon_no = $this->ui_factory->symbol()->icon()->custom(\ilUtil::getImagePath('standard/icon_unchecked.svg'), 'no');

        return [
            'title' => $f->link($this->lng->txt('title')),
            'description' => $f->text($this->lng->txt('description'))->withIsOptional(true, true),
            'ttype' => $f->text($this->lng->txt('question_type'))->withIsOptional(true, true),
            'points' => $f->number($this->lng->txt('points'))->withDecimals(2)->withIsOptional(true, true),
            'author' => $f->text($this->lng->txt('author'))->withIsOptional(true, true),
            'lifecycle' => $f->text($this->lng->txt('qst_lifecycle'))->withIsOptional(true, true),
            'taxonomies' => $f->text($this->lng->txt('qpl_settings_subtab_taxonomies'))->withIsOptional(true, true),
            'feedback' => $f->boolean($this->lng->txt('feedback'), $icon_yes, $icon_no)->withIsOptional(true, true),
            'hints' => $f->boolean($this->lng->txt('hints'), $icon_yes, $icon_no)->withIsOptional(true, true),
            'created' => $f->date(
                $this->lng->txt('create_date'),
                $this->current_user->getDateTimeFormat()
            )->withIsOptional(true, true),
            'tstamp' => $f->date(
                $this->lng->txt('last_update'),
                $this->current_user->getDateTimeFormat()
            )->withIsOptional(true, true),
            'comments' => $f->number($this->lng->txt('comments'))->withIsOptional(true, false),
        ];
    }

    private function treeify(&$pointer, $stack)
    {
        $hop = array_shift($stack);
        if (!$hop) {
            return;
        }
        if (! array_key_exists($hop, $pointer)) {
            $pointer[$hop] = [];
        }
        $this->treeify($pointer[$hop], $stack);
    }

    private function toNestedList(array $nodes): string
    {
        $entries = [];
        foreach ($nodes as $k => $n) {
            if ($n === []) {
                $entries[] = $k;
            } else {
                $entries[] = $k . $this->toNestedList($n);
            }
        }
        return $this->ui_renderer->render(
            $this->ui_factory->listing()->unordered($entries)
        );
    }

    private function taxNodeReader($tree, $sortfield, $node_id): array
    {
        $ret = [];
        $nodes = $tree->getChildsByTypeFilter($node_id, ['taxn']);
        usort(
            $nodes,
            fn($a, $b) => strcmp(
                (string) $a[$sortfield],
                (string) $b[$sortfield]
            )
        );

        foreach ($nodes as $node) {
            $ret[] = $node;
            foreach ($this->taxNodeReader($tree, $sortfield, $node['obj_id']) as $c) {
                $ret[] = $c;
            }
        }
        return $ret;
    }

    private function singleTaxonomyRepresentation(
        int $tax_id,
        array $stored_tax_data,
        string $check_marker
    ): string {
        $tax = new \ilObjTaxonomy($tax_id);
        $tax_tree = $tax->getTree();
        $sortfield = $tax->getSortingMode() === \ilObjTaxonomy::SORT_ALPHABETICAL ? 'title' : 'order_nr';
        $taxnodes = $this->taxNodeReader($tax_tree, $sortfield, $tax_tree->readRootId());

        $nodes = [];
        foreach ($taxnodes as $taxnode) {
            $taxdata = array_filter(
                $stored_tax_data,
                fn($data_child) => $data_child['node_id'] === $taxnode['obj_id']
            );

            foreach (array_keys($taxdata) as $node_obj_id) {
                $path = array_map(
                    fn($n) => in_array($n['obj_id'], array_keys($stored_tax_data)) ? $check_marker . $n['title'] : $n['title'],
                    $tax_tree->getPathFull($node_obj_id),
                );
                $path[0] = \ilObject::_lookupTitle($tax_id);
                $this->treeify($nodes, $path);
            }
        }
        return $this->toNestedList($nodes);
    }

    private function taxonomyRepresentation(array $taxonomy_data): string
    {
        $check = $this->ui_renderer->render(
            $this->ui_factory->symbol()->icon()->custom(\ilUtil::getImagePath('standard/icon_checked.svg'), 'checked')
        );

        $taxonomies = [];
        $taxs = $this->taxonomy->getUsageOfObject($this->parent_obj_id, true);
        foreach ($taxs as $tax_entry) {
            $tax_id = $tax_entry['tax_id'];
            if (!array_key_exists($tax_id, $taxonomy_data)) {
                continue;
            }
            $taxonomies[] = $this->singleTaxonomyRepresentation(
                $tax_id,
                $taxonomy_data[$tax_id],
                $check
            );
        }
        return implode('', $taxonomies);
    }

    public function getRows(
        Table\DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): \Generator {
        $no_write_access = !($this->rbac->checkAccess('write', $this->request_ref_id));
        $timezone = new \DateTimeZone($this->current_user->getTimeZone());
        foreach ($this->getData($order, $range) as $record) {
            $row_id = (string) $record['question_id'];
            $record['created'] = (new \DateTimeImmutable("@{$record['created']}"))->setTimezone($timezone);
            $record['tstamp'] = (new \DateTimeImmutable("@{$record['tstamp']}"))->setTimezone($timezone);
            $lifecycle = \ilAssQuestionLifecycle::getInstance($record['lifecycle']);
            $record['lifecycle'] = $lifecycle->getTranslation($this->lng);

            $title = $record['title'];
            $to_question = $this->url_builder
                ->withParameter($this->action_parameter_token, 'preview')
                ->withParameter($this->row_id_token, $row_id)
                ->buildURI()->__toString();
            if (!(bool) $record['complete']) {
                $title .= ' (' . $this->lng->txt('warning_question_not_complete') . ')';
            }
            $record['title'] = $this->ui_factory->link()->standard($title, $to_question);
            $record['taxonomies'] = $this->taxonomyRepresentation($record['taxonomies']);

            yield $row_builder->buildDataRow($row_id, $record)
                ->withDisabledAction('move', $no_write_access)
                ->withDisabledAction('copy', $no_write_access)
                ->withDisabledAction('delete', $no_write_access)
                ->withDisabledAction('feedback', $no_write_access)
                ->withDisabledAction('hints', $no_write_access)
            ;
        }
    }

    public function getTotalRowCount(
        ?array $filter_data,
        ?array $additional_parameters
    ): ?int {
        $this->setParentObjId($this->parent_obj_id);
        $this->load();
        return count($this->getQuestionDataArray());
    }

    protected function getData(Order $order, Range $range): array
    {
        $this->setParentObjId($this->parent_obj_id);
        $this->load();
        $data = $this->postOrder($this->getQuestionDataArray(), $order);
        [$offset, $length] = $range->unpack();
        $length = $length > 0 ? $length : null;
        return array_slice($data, $offset, $length);
    }

    protected function getActions(): array
    {
        return array_merge(
            $this->buildAction('copy', 'standard'),
            $this->buildAction('move', 'standard'),
            $this->buildAction('delete', 'standard'),
            $this->buildAction('export', 'multi'),
            $this->buildAction('preview', 'single'),
            $this->buildAction('statistics', 'single'),
            $this->buildAction('edit_question', 'single'),
            $this->buildAction('edit_page', 'single'),
            $this->buildAction('feedback', 'single'),
            $this->buildAction('hints', 'single'),
            $this->buildAction(\ilBulkEditQuestionsGUI::CMD_EDITTAUTHOR, 'multi'),
            $this->buildAction(\ilBulkEditQuestionsGUI::CMD_EDITLIFECYCLE, 'multi'),
            $this->buildAction(\ilBulkEditQuestionsGUI::CMD_EDITTAXONOMIES, 'multi'),
            $this->showCommentAction() ? $this->buildAction('comments', 'single', true) : []
        );
    }

    protected function buildAction(string $act, string $type, bool $async = false): array
    {
        $action = $this->ui_factory->table()->action()
            ->$type(
                $this->lng->txt($act),
                $this->url_builder->withParameter($this->action_parameter_token, $act),
                $this->row_id_token
            );
        if ($async) {
            $action = $action->withAsync(true);
        }

        return [$act => $action];
    }

    protected function postOrder(array $list, \ILIAS\Data\Order $order): array
    {
        [$aspect, $direction] = $order->join('', function ($i, $k, $v) {
            return [$k, $v];
        });
        usort($list, static function (array $a, array $b) use ($aspect): int {
            if (is_numeric($a[$aspect]) || is_bool($a[$aspect])) {
                return $a[$aspect] <=> $b[$aspect];
            }
            if (is_array($a[$aspect])) {
                return $a[$aspect] <=> $b[$aspect];
            }

            $aspect_a = '';
            $aspect_b = '';
            if ($a[$aspect] !== null) {
                $aspect_a = $a[$aspect];
            }
            if ($b[$aspect] !== null) {
                $aspect_b = $b[$aspect];
            }

            return strcoll($aspect_a, $aspect_b);
        });

        if ($direction === $order::DESC) {
            $list = array_reverse($list);
        }
        return $list;
    }

    private function showCommentAction(): bool
    {
        return $this->notes_service->domain()->commentsActive($this->parent_obj_id)
            || $this->rbac->checkAccess('write', $this->request_ref_id);
    }
}
