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

namespace ILIAS\Course\Grouping\Table;

use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\HTTP\Services as HTTP;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Component\Table\Column\Column;
use ILIAS\UI\Component\Table\Action\Action;
use ilObject;
use ilLanguage;
use ilObjCourseGrouping;
use ilObjUser;
use ilTree;

class AssignmentHandler
{
    public const string ID = 'ref_id';
    protected const string ACTION = 'table_action';
    protected const string NAMESPACE = 'grouping_assign';

    public const string COL_TITLE = 'title';
    public const string COL_PATH = 'path';
    public const string COL_ASSIGNED = 'assigned';

    public const string ACTION_TOGGLE_ASSIGNMENT = 'toggle_assignment';

    protected URLBuilder $url_builder;
    protected URLBuilderToken $action_token;
    protected URLBuilderToken $id_token;
    protected DataRetrieval $data_retrieval;

    public function __construct(
        protected string $action,
        protected int $content_obj_id,
        protected ilObjCourseGrouping $grouping,
        protected ilLanguage $lng,
        protected UIFactory $ui_factory,
        protected DataFactory $data_factory,
        protected HTTP $http,
        protected Refinery $refinery,
        ilObjUser $user,
        ilTree $tree
    ) {
        $this->data_retrieval = new AssignmentRetrieval(
            $this->content_obj_id,
            $this->grouping,
            $user,
            $tree,
            $this->ui_factory,
            $this->lng,
            $this->data_factory
        );
    }

    public function getTable(): DataTable
    {
        return $this->ui_factory->table()->data(
            $this->data_retrieval,
            $this->lng->txt('crs_grp_assign_crs') . ' (' . $this->grouping->getTitle() . ')',
            $this->buildColumns()
        )->withRequest($this->http->request())
         ->withActions($this->buildActions());
    }

    /**
     * @return Column[]
     */
    protected function buildColumns(): array
    {
        $f = $this->ui_factory->table()->column();
        $type = ilObject::_lookupType($this->content_obj_id);
        return [
            self::COL_TITLE => $f->text($this->lng->txt('title'))->withIsSortable(true),
            self::COL_PATH => $f->text($this->lng->txt('path'))->withIsSortable(true),
            self::COL_ASSIGNED => $f->statusIcon($this->lng->txt('assigned'))->withIsSortable(true)
        ];
    }

    /**
     * @return Action[]
     */
    protected function buildActions(): array
    {
        $f = $this->ui_factory->table()->action();
        return [
            self::ACTION_TOGGLE_ASSIGNMENT => $f->standard(
                $this->lng->txt('grouping_change_assignment'),
                $this->URLBuilder()->withParameter($this->actionToken(), self::ACTION_TOGGLE_ASSIGNMENT),
                $this->IDToken()
            )
        ];
    }

    protected function URLBuilder(): URLBuilder
    {
        if (!isset($this->url_builder)) {
            $this->initURLBuilderAndTokens();
        }
        return $this->url_builder;
    }

    protected function actionToken(): URLBuilderToken
    {
        if (!isset($this->action_token)) {
            $this->initURLBuilderAndTokens();
        }
        return $this->action_token;
    }

    protected function IDToken(): URLBuilderToken
    {
        if (!isset($this->id_token)) {
            $this->initURLBuilderAndTokens();
        }
        return $this->id_token;
    }

    protected function initURLBuilderAndTokens(): void
    {
        $url_builder = new URLBuilder($this->data_factory->uri(
            rtrim(ILIAS_HTTP_PATH, '/') . '/' . $this->action
        ));
        list($url_builder, $action_token, $id_token) = $url_builder->acquireParameters(
            [self::NAMESPACE],
            self::ACTION,
            self::ID
        );
        $this->url_builder = $url_builder;
        $this->action_token = $action_token;
        $this->id_token = $id_token;
    }

    public function getSelectedTableAction(): string
    {
        $action = '';
        if ($this->http->wrapper()->query()->has($this->actionToken()->getName())) {
            $action = $this->http->wrapper()->query()->retrieve(
                $this->actionToken()->getName(),
                $this->refinery->identity()
            );
        }
        return $action;
    }

    /**
     * @return int[]
     */
    public function getSelectedRefIDs(): array
    {
        $grouping_ids = [];
        $data_retrieval = $this->data_retrieval;

        $retrieval_trafo = $this->refinery->byTrying([
            $this->refinery->custom()->transformation(function ($v) use ($data_retrieval) {
                if ((string) $v[0] === 'ALL_OBJECTS') {
                    $res = [];
                    foreach ($data_retrieval->getAllEligibleRefIDs() as $ref_id) {
                        $res[] = $ref_id->toInt();
                    }
                    return $res;
                }
                throw new \Exception('not all selected');
            }),
            $this->refinery->kindlyTo()->listOf(
                $this->refinery->kindlyTo()->int()
            )
        ]);

        if ($this->http->wrapper()->query()->has($this->IDToken()->getName())) {
            $grouping_ids = $this->http->wrapper()->query()->retrieve(
                $this->IDToken()->getName(),
                $retrieval_trafo
            );
        }

        return $grouping_ids;
    }
}
