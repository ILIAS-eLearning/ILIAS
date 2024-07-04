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

class QuestionTable extends ilAssQuestionList implements Table\DataRetrieval
{
    public function __construct(
        protected UIFactory $ui_factory,
        protected UIRenderer $ui_renderer,
        protected DataFactory $data_factory,
        protected Refinery $refinery,
        protected URLBuilder $url_builder,
        protected URLBuilderToken $action_parameter_token,
        protected URLBuilderToken $row_id_token,
        protected ilDBInterface $db,
        protected ilLanguage $lng,
        protected ilComponentRepository $component_repository,
        protected ilRbacSystem $rbac,
        protected ?TaxonomyService $taxonomy,
        protected NotesService $notes_service,
        protected int $parent_obj_id,
        protected int $request_ref_id
    ) {
        $lng->loadLanguageModule('qpl');
        parent::__construct($db, $lng, $refinery, $component_repository, $notes_service);
        if ($this->taxonomy) {
            $this->setAvailableTaxonomyIds($taxonomy->getUsageOfObject($parent_obj_id));
        }
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
    public function getFilter(ilUIService $ui_service, string $action): Filter
    {
        $lifecycle_options = array_merge(
            ['' => $this->lng->txt('qst_lifecycle_filter_all')],
            ilAssQuestionLifecycle::getDraftInstance()->getSelectOptions($this->lng)
        );
        $question_type_options = [
            '' => $this->lng->txt('filter_all_question_types')
        ];
        $question_types = ilObjQuestionPool::_getQuestionTypes();
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
                    ilAssQuestionList::QUESTION_COMMENTED_ONLY => $this->lng->txt('qpl_filter_commented_only'),
                    ilAssQuestionList::QUESTION_COMMENTED_EXCLUDED => $this->lng->txt('qpl_filter_commented_exclude')
                ]
            )
        ];

        if ($this->taxonomy) {

            $taxs = $this->taxonomy->getUsageOfObject($this->parent_obj_id, true);
            $tax_filter_options = [
                'null' => '<b>' . $this->lng->txt('tax_filter_notax') . '</b>'
            ];
            foreach($taxs as $tax_entry) {
                $tax = new ilObjTaxonomy($tax_entry['tax_id']);
                $children = array_filter(
                    $tax->getTree()->getFilteredSubTree($tax->getTree()->readRootId()),
                    fn($ar) => $ar['type'] === 'taxn'
                );
                $nodes = implode('-', array_map(fn($node) => $node['obj_id'], $children));

                $tax_id = $tax_entry['tax_id'] . '-0-' . $nodes;
                $tax_title = '<b>' . $tax_entry['title'] . '</b>';
                $tax_filter_options[$tax_id] = $tax_title;

                foreach($children as $subtax) {
                    $stax_id = $subtax['tax_id'] . '-' . $subtax['obj_id'];
                    $stax_title = str_repeat('&nbsp; ', ($subtax['depth'] - 2) * 2)
                        . ' &boxur;&HorizontalLine; '
                        . $subtax['title'];

                    $tax_filter_options[$stax_id] = $stax_title;
                }
            }
            $filter_inputs['taxonomies'] = $field_factory->multiSelect($this->lng->txt("tax_filter"), $tax_filter_options);
        }


        $active = array_fill(0, count($filter_inputs), true);

        $filter = $ui_service->filter()->standard(
            "question_table_filter_id",
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
        $date_format = $df->withTime24($this->data_factory->dateFormat()->germanShort());
        $icon_yes = $this->ui_factory->symbol()->icon()->custom(ilUtil::getImagePath('standard/icon_checked.svg'), 'yes');
        $icon_no = $this->ui_factory->symbol()->icon()->custom(ilUtil::getImagePath('standard/icon_unchecked.svg'), 'no');

        $cols = [
            'title' => $f->link($this->lng->txt('title')),
            'description' => $f->text($this->lng->txt('description'))->withIsOptional(true, true),
            'ttype' => $f->text($this->lng->txt('question_type'))->withIsOptional(true, true),
            'points' => $f->number($this->lng->txt('points'))->withIsOptional(true, true),
            'author' => $f->text($this->lng->txt('author'))->withIsOptional(true, true),
            'lifecycle' => $f->text($this->lng->txt('qst_lifecycle'))->withIsOptional(true, true),
        ];
        if ($this->taxonomy) {
            $cols['taxonomies'] = $f->text($this->lng->txt('qpl_settings_subtab_taxonomies'))->withIsOptional(true, true);
        }
        $cols = array_merge($cols, [
            'feedback' => $f->boolean($this->lng->txt('feedback'), $icon_yes, $icon_no)->withIsOptional(true, true),
            'hints' => $f->boolean($this->lng->txt('hints'), $icon_yes, $icon_no)->withIsOptional(true, true),
            'created' => $f->date($this->lng->txt('create_date'), $date_format)->withIsOptional(true, true),
            'tstamp' => $f->date($this->lng->txt('last_update'), $date_format)->withIsOptional(true, true),
            'comments' => $f->number($this->lng->txt('comments'))->withIsOptional(true, false),
        ]);
        return $cols;
    }

    private function treeify(&$pointer, $stack)
    {
        $hop = array_shift($stack);
        if(!$hop) {
            return;
        }
        if (! array_key_exists($hop, $pointer)) {
            $pointer[$hop] = [];
        }
        $this->treeify($pointer[$hop], $stack);
    }

    private function toNestedList(array $nodes)
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

    private function taxonomyRepresentation(array $taxonomy_data): string
    {
        $taxonomies = [];
        $check = $this->ui_renderer->render(
            $this->ui_factory->symbol()->icon()->custom(ilUtil::getImagePath('standard/icon_checked.svg'), 'checked')
        );
        foreach ($taxonomy_data as $taxonomy_id => $tax_data) {
            $taxonomy = new ilObjTaxonomy($taxonomy_id);
            $title = ilObject::_lookupTitle($taxonomy_id);
            $tree = $taxonomy->getTree();
            $nodes = [];
            foreach ($tax_data as $id => $tax_node) {
                $path = array_map(
                    fn($n) => in_array($n['obj_id'], array_keys($tax_data)) ? $check . $n['title'] : $n['title'],
                    // getNodePath has dependencies to object_data and object_reference
                    //$tree->getNodePath($tax_node['node_id'])
                    array_filter(
                        $tree->getPathFull($tax_node['node_id']),
                        fn($ar) => $ar['type'] === 'taxn'
                    )
                );
                $this->treeify($nodes, $path);
                $listing = $this->toNestedList($nodes);
            }

            $taxonomies[] = ilObject::_lookupTitle($taxonomy_id);
            $taxonomies[] = $listing;
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
        foreach ($this->getData($order, $range) as $idx => $record) {
            $row_id = (string) $record['question_id'];
            $record['created'] = (new \DateTimeImmutable())->setTimestamp($record['created']);
            $record['tstamp'] = (new \DateTimeImmutable())->setTimestamp($record['tstamp']);
            $lifecycle = ilAssQuestionLifecycle::getInstance($record['lifecycle']);
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
            if ($this->taxonomy) {
                $record['taxonomies'] = $this->taxonomyRepresentation($record['taxonomies']);
            }

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
            $this->buildAction('comments', 'single', true)
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

            return strcmp($aspect_a, $aspect_b);
        });

        if ($direction === $order::DESC) {
            $list = array_reverse($list);
        }
        return $list;
    }
}
