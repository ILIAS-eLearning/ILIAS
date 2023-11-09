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
use ILIAS\UI\Component\Table;
use ILIAS\UI\Component\Input\Container\Filter\Standard as Filter;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\Taxonomy\DomainService as TaxonomyService;

class QuestionTable extends ilAssQuestionList implements Table\DataRetrieval
{
    public function __construct(
        protected UIFactory $ui_factory,
        protected UIRenderer $ui_renderer,
        protected DataFactory $data_factory,
        protected URLBuilder $url_builder,
        protected URLBuilderToken $action_parameter_token,
        protected URLBuilderToken $row_id_token,
        protected ilDBInterface $db,
        protected ilLanguage $lng,
        protected ilComponentRepository $component_repository,
        protected ilRbacSystem $rbac,
        protected TaxonomyService $taxonomy,
        protected int $parent_obj_id,
        protected int $request_ref_id
    ) {
        parent::__construct($db, $lng, $component_repository);
        $this->setAvailableTaxonomyIds($taxonomy->getUsageOfObject($parent_obj_id));
    }

    public function getTable(): Table\Data
    {
        return $this->ui_factory->table()->data(
            $this->lng->txt('questions'),
            $this->getColums(),
            $this
        )
        ->withActions($this->getActions());
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

        $taxs = $this->taxonomy->getUsageOfObject($this->parent_obj_id, true);
        $tax_filter_options = [
            'null' => $this->lng->txt('tax_filter_notax')
        ];
        foreach($taxs as $tax_entry) {
            $tax = new ilObjTaxonomy($tax_entry['tax_id']);
            $children = $tax->getTree()->getChilds($tax->getTree()->readRootId());
            $nodes = implode('-', array_map(fn($node) => $node['obj_id'], $children));

            $tax_id = $tax_entry['tax_id'] . '-0-' . $nodes;
            $tax_title = $tax_entry['title'];
            $tax_filter_options[$tax_id] = $tax_title;

            foreach($children as $subtax) {
                $stax_id = $subtax['tax_id'] . '-' . $subtax['obj_id'];
                $stax_title = '---' . $subtax['title'];
                $tax_filter_options[$stax_id] = $stax_title;
            }
        }

        $field_factory = $this->ui_factory->input()->field();
        $filter_inputs = [
            'title' => $field_factory->text($this->lng->txt("title")),
            'description' => $field_factory->text($this->lng->txt("description")),
            'author' => $field_factory->text($this->lng->txt("author")),
            'lifecycle' => $field_factory->select($this->lng->txt("qst_lifecycle"), $lifecycle_options),
            'type' => $field_factory->select($this->lng->txt("type"), $question_type_options),
            'commented' => $field_factory->select($this->lng->txt("ass_commented_questions_only"), ['1' => $this->lng->txt('yes'), '0' => $this->lng->txt('no')]),
            'taxonomies' => $field_factory->select($this->lng->txt("tax_filter"), $tax_filter_options),
        ];

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
        $icon_yes = $this->ui_renderer->render($this->ui_factory->symbol()->icon()->custom(ilUtil::getImagePath('standard/icon_checked.svg'), 'yes'));
        $icon_no = $this->ui_renderer->render($this->ui_factory->symbol()->icon()->custom(ilUtil::getImagePath('standard/icon_unchecked.svg'), 'no'));

        return  [
            'title' => $f->link($this->lng->txt('title')),
            'description' => $f->text($this->lng->txt('description'))->withIsOptional(true, true),
            'ttype' => $f->text($this->lng->txt('question_type'))->withIsOptional(true, true),
            'points' => $f->number($this->lng->txt('points'))->withIsOptional(true, true),
            'author' => $f->text($this->lng->txt('author'))->withIsOptional(true, true),
            'lifecycle' => $f->text($this->lng->txt('qst_lifecycle'))->withIsOptional(true, true),
            'taxonomies' => $f->text($this->lng->txt('qpl_settings_subtab_taxonomies'))->withIsOptional(true, true),
            'feedback' => $f->boolean($this->lng->txt('feedback'), $icon_yes, $icon_no)->withIsOptional(true, true),
            'hints' => $f->boolean($this->lng->txt('hints'), $icon_yes, $icon_no)->withIsOptional(true, true),
            'created' => $f->date($this->lng->txt('create_date'), $date_format)->withIsOptional(true, true),
            'tstamp' => $f->date($this->lng->txt('last_update'), $date_format)->withIsOptional(true, true),
        ];
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
            $row_id = (string)$record['question_id'];
            $record['created'] = (new \DateTimeImmutable())->setTimestamp($record['created']);
            $record['tstamp'] = (new \DateTimeImmutable())->setTimestamp($record['tstamp']);
            $lifecycle = ilAssQuestionLifecycle::getInstance($record['lifecycle']);
            $record['lifecycle'] = $lifecycle->getTranslation($this->lng);

            $to_question = $this->url_builder
                ->withParameter($this->action_parameter_token, 'preview')
                ->withParameter($this->row_id_token, $row_id)
                ->buildURI()->__toString();
            $record['title'] = $this->ui_factory->link()->standard($record['title'], $to_question);

            $taxonomies = [];
            foreach ($record['taxonomies'] as $taxonomy_id => $tax_data) {
                $taxonomy = new ilObjTaxonomy($taxonomy_id);
                $title = ilObject::_lookupTitle($taxonomy_id);

                $nodes = [];
                foreach ($tax_data as $ids => $node) {
                    $nodes[] = ilTaxonomyNode::_lookupTitle($node['node_id']);
                }
                $taxonomies[] = ilObject::_lookupTitle($taxonomy_id);
                $taxonomies[] = $this->ui_renderer->render(
                    $this->ui_factory->listing()->unordered($nodes)
                );
            }

            $record['taxonomies'] = implode('', $taxonomies);

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
        $offset = max($offset - 1, 0);
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

            return strcmp($a[$aspect], $b[$aspect]);
        });

        if ($direction === $order::DESC) {
            $list = array_reverse($list);
        }
        return $list;
    }
}
