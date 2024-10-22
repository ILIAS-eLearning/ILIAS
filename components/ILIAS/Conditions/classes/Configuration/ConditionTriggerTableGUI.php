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

namespace ILIAS\Conditions\Configuration;

use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\Data\Order;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Table\Data as Data;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\URLBuilder;
use Generator;
use ilLanguage;
use ilObjUser;
use ilCtrl;
use ilConditionHandlerGUI;

class ConditionTriggerTableGUI implements DataRetrieval
{
    public const ACTION_TOKEN = 'action';
    public const ID_TOKEN = 'id';
    public const TABLE_NS = 'cond_trigger_table';

    public const ACTION_TOKEN_NS = self::TABLE_NS . '_' . self::ACTION_TOKEN;

    public const ID_TOKEN_NS = self::TABLE_NS . '_' . self::ID_TOKEN;

    protected \Psr\Http\Message\ServerRequestInterface $http_request;
    protected \ILIAS\UI\Renderer $ui_renderer;
    protected \ILIAS\UI\Factory $ui_factory;
    protected DataFactory $data_factory;

    protected ConditionTriggerProvider $provider;
    protected bool $allow_optional_conditions;

    protected readonly ilLanguage $lng;
    protected readonly ilObjUser $user;
    protected readonly ilCtrl $ctrl;


    public function __construct(ConditionTriggerProvider $provider, bool $allow_optional_conditions)
    {
        global $DIC;

        $this->provider = $provider;
        $this->allow_optional_conditions = $allow_optional_conditions;

        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('rbac');
        $this->user = $DIC->user();
        $this->ctrl = $DIC->ctrl();
        $this->ui_factory = $DIC->ui()->factory();
        $this->data_factory = new DataFactory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->http_request = $DIC->http()->request();
    }


    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        ?array $filter_data,
        ?array $additional_parameters
    ): Generator {
        $records = $this->provider->limitData($range, $order);
        foreach ($records as $row) {
            $id = $row['id'];
            yield $row_builder->buildDataRow((string) $id, $row);
        }
    }

    public function getTotalRowCount(?array $filter_data, ?array $additional_parameters): ?int
    {
        return count($this->provider->getData());
    }

    protected function getColumns(): array
    {
        return [
            'trigger' => $this->ui_factory
                ->table()
                ->column()
                ->link($this->lng->txt('rbac_precondition_source')),
            'condition' => $this->ui_factory
                ->table()
                ->column()
                ->text($this->lng->txt('condition'))
                ->withIsSortable(true),
            'obligatory' => $this->ui_factory
                ->table()
                ->column()
                ->statusIcon($this->lng->txt('precondition_obligatory'))
                ->withIsSortable(false)
        ];
    }

    /**
     * @return array<string, \ILIAS\UI\Component\Table\Action\Action>
     */
    protected function getActions(): array
    {
        $uri_command_handler = $this->data_factory->uri(
            ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(
                ilConditionHandlerGUI::class,
                'handleConditionTriggerTableActions'
            )
        );
        [
            $url_builder,
            $action_parameter_token,
            $row_id_token
        ] =
            (new URLBuilder($uri_command_handler))->acquireParameters(
                [self::TABLE_NS],
                self::ACTION_TOKEN,
                self::ID_TOKEN
            );

        $actions['edit'] = $this->ui_factory->table()->action()->single(
            $this->lng->txt('edit'),
            $url_builder->withParameter($action_parameter_token, 'editConditionTrigger'),
            $row_id_token
        );
        if ($this->allow_optional_conditions) {
            $actions['saveCompulsory'] = $this->ui_factory->table()->action()->multi(
                $this->lng->txt('rbac_precondition_save_obligatory'),
                $url_builder->withParameter($action_parameter_token, 'saveCompulsory'),
                $row_id_token
            );
        }
        $actions['confirmDeleteConditionTrigger'] = $this->ui_factory->table()->action()->standard(
            $this->lng->txt('delete'),
            $url_builder->withParameter($action_parameter_token, 'confirmDeleteConditionTrigger'),
            $row_id_token
        )->withAsync(true);
        return $actions;
    }


    public function get(): Data
    {
        return $this->ui_factory
            ->table()
            ->data(
                $this->lng->txt('active_preconditions'),
                $this->getColumns(),
                $this
            )
            ->withId(self::class)
            ->withActions($this->getActions())
            ->withRequest($this->http_request);
    }


    public function render(): string
    {
        return $this->ui_renderer->render(
            [
                $this->get()
            ]
        );
    }

}
