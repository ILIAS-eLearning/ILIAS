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

/*
use ILIAS\UI\Implementation\Component\Table as T;
use ILIAS\UI\Component\Table as I;
*/

use ILIAS\UI\Factory as UIFactory;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
//use ILIAS\UI\Renderer as UIRenderer;
use  ILIAS\UI\Component\Table;

use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use Psr\Http\Message\ServerRequestInterface;

class QuestionTable extends ilAssQuestionList implements Table\DataRetrieval
{
    public function __construct(
        protected UIFactory $ui_factory,
        protected DataFactory $data_factory,
        protected URLBuilder $url_builder,
        protected URLBuilderToken $action_parameter_token,
        protected URLBuilderToken $row_id_token,
        protected ilDBInterface $db,
        protected ilLanguage $lng,
        protected ilComponentRepository $component_repository,
        protected ilRbacSystem $rbac,
        protected int $parent_obj_id,
        protected int $request_ref_id
    ) {
        parent::__construct($db, $lng, $component_repository);
    }

    public function getTable(): Table\Data
    {
        return $this->ui_factory->table()->data(
            'a data table from a repository',
            $this->getColums(),
            $this
        )
        ->withActions($this->getActions());
    }

    public function getColums(): array
    {
        $f = $this->ui_factory->table()->column();
        $df = $this->data_factory->dateFormat();
        $date_format = $df->withTime24($this->data_factory->dateFormat()->germanShort());

        return  [
            //'obj_fi' => $f->text($this->lng->txt('obj_fi')),
            //'question_id' => $f->text($this->lng->txt('question_id')),
            //'external_id' => $f->text($this->lng->txt('external_id')),
            'title' => $f->text($this->lng->txt('title')),
            'description' => $f->text($this->lng->txt('description')),
            'ttype' => $f->text($this->lng->txt('question_type')),
            'points' => $f->number($this->lng->txt('points')),
            'statistics' => $f->text($this->lng->txt('statistics')), //take out, it's an action, really
            'author' => $f->text($this->lng->txt('author')),
            'title' => $f->text($this->lng->txt('title')),
            'lifecycle' => $f->text($this->lng->txt('qst_lifecycle')),
            'comments' => $f->text($this->lng->txt('comments')),
            'created' => $f->date($this->lng->txt('create_date'), $date_format),
            'tstamp' => $f->date($this->lng->txt('last_update'), $date_format),
            'taxonomies' => $f->text($this->lng->txt('taxonomies')),
            'nodes' => $f->text($this->lng->txt('nodes')),
            'feedback' => $f->text($this->lng->txt('feedback')),
            'hints' => $f->text($this->lng->txt('hints'))
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
            $row_id = (string)$record['obj_fi'];
            $row_id = (string)$record['question_id'];
            $record['created'] = (new \DateTimeImmutable())->setTimestamp($record['created']);
            $record['tstamp'] = (new \DateTimeImmutable())->setTimestamp($record['tstamp']);
            $record['taxonomies'] = implode(',', $record['taxonomies']);
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
        return count($this->load());
    }

    protected function getData(Order $order, Range $range): array
    {
        $this->setParentObjId($this->parent_obj_id);
        $this->load();
        return $this->getQuestionDataArray();
    }

    protected function getActions(): array
    {
        return
        array_merge(
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
            $this->buildAction('comments', 'single')
        );
    }

    protected function buildAction(string $act, $type): array
    {
        return [
            $act => $this->ui_factory->table()->action()
            ->$type(
                $this->lng->txt($act),
                $this->url_builder->withParameter($this->action_parameter_token, $act),
                $this->row_id_token
            )
        ];
    }
}
