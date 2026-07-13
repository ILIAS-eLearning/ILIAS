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
use ILIAS\HTTP\Wrapper\WrapperFactory;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory;
use ILIAS\UI\URLBuilder;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\RequestInterface;

/**
 * TableGUI class for LTI consumer listing
 *
 * @author Jesús López <lopez@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesLTI
 */
class ilObjectConsumerTableGUI implements DataRetrieval
{
    protected ilLanguage $lng;
    protected Factory $ui_factory;
    protected \ILIAS\UI\Renderer $ui_renderer;
    private ServerRequestInterface|RequestInterface $request;
    protected \ILIAS\Data\Factory $data_factory;
    protected ilCtrlInterface $ctrl;
    protected WrapperFactory $wrapper;
    protected \ILIAS\Refinery\Factory $refinery;
    private array $records;
    private bool $editable = false;

    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->request = $DIC->http()->request();
        $this->data_factory = new \ILIAS\Data\Factory();
        $this->ctrl = $DIC->ctrl();
        $this->wrapper = $DIC->http()->wrapper();
        $this->refinery = $DIC->refinery();

        $this->records = $this->getRecords();
    }

    public function getColumns(): array
    {
        return [
            'active' => $this->ui_factory->table()->column()->statusIcon($this->lng->txt('active')),
            'title' => $this->ui_factory->table()->column()->text($this->lng->txt('title')),
            'description' => $this->ui_factory->table()->column()->text($this->lng->txt('description')),
            'prefix' => $this->ui_factory->table()->column()->text($this->lng->txt('prefix')),
            'language' => $this->ui_factory->table()->column()->text($this->lng->txt('in_use')),
            'objects' => $this->ui_factory->table()->column()->text($this->lng->txt('objects')),
            'role' => $this->ui_factory->table()->column()->text($this->lng->txt('role'))
        ];
    }

    private function getRecords(): array
    {
        $dataConnector = new ilLTIDataConnector();

        $consumer_data = $dataConnector->getGlobalToolConsumerSettings();

        $result = array();

        foreach ($consumer_data as $cons) {
            $result[] = array(
                "id" => $cons->getExtConsumerId(),
                "active" => $cons->getActive(),
                "title" => $cons->getTitle(),
                "description" => $cons->getDescription(),
                "prefix" => $cons->getPrefix(),
                "language" => $cons->getLanguage(),
                "role" => $cons->getRole(),
            );
        }

        return $result;
    }

    /**
     * @throws ilCtrlException
     */
    private function getActions(): array
    {
        if (!$this->isEditable()) {
            return [];
        }

        $df = new \ILIAS\Data\Factory();
        $here_uri = $df->uri($this->request->getUri()->__toString());
        $url_builder = new URLBuilder($here_uri);

        $query_params_namespace = ['lti_consumer_table'];
        list($url_builder, $id_token, $action_token) = $url_builder->acquireParameters(
            $query_params_namespace,
            "consumer_id",
            "action"
        );

        $query = $this->wrapper->query();
        if ($query->has($action_token->getName())) {
            $action = $query->retrieve($action_token->getName(), $this->refinery->to()->string());
            $ids = $query->retrieve($id_token->getName(), $this->refinery->custom()->transformation(fn($v) => $v));
            $id = $ids[0] ?? null;

            switch ($action) {
                case "edit":
                    $this->ctrl->setParameterByClass(ilObjLTIAdministrationGUI::class, 'cid', $id);
                    $this->ctrl->redirectByClass(ilObjLTIAdministrationGUI::class, 'editConsumer');
                    break;
                case "delete":
                    $this->ctrl->setParameterByClass(ilObjLTIAdministrationGUI::class, 'cid', $id);
                    $this->ctrl->redirectByClass(ilObjLTIAdministrationGUI::class, 'deleteLTIConsumer');
                    break;
                case "status":
                    $this->ctrl->setParameterByClass(ilObjLTIAdministrationGUI::class, 'cid', $id);
                    $this->ctrl->redirectByClass(ilObjLTIAdministrationGUI::class, 'changeStatusLTIConsumer');
                    break;
            }
        }

        return [
            $this->ui_factory->table()->action()->single(
                $this->lng->txt('edit'),
                $url_builder->withParameter($action_token, "edit"),
                $id_token
            ),
            $this->ui_factory->table()->action()->single(
                $this->lng->txt('delete'),
                $url_builder->withParameter($action_token, "delete"),
                $id_token
            ),
            $this->ui_factory->table()->action()->single(
                $this->lng->txt('activate') . " / " . $this->lng->txt('deactivate'),
                $url_builder->withParameter($action_token, "status"),
                $id_token
            )
        ];
    }

    /**
     * @throws ilObjectNotFoundException
     * @throws ilDatabaseException
     */
    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): Generator {
        $records = $this->applyOrdering($this->records, $order, $range);
        foreach ($records as $record) {
            $record["active"] = $this->ui_factory->symbol()->icon()->custom(
                $record["active"] ?
                    'assets/images/standard/icon_ok.svg' :
                    'assets/images/standard/icon_not_ok.svg',
                $record["active"] ? $this->lng->txt('active') : $this->lng->txt('inactive'),
                Icon::MEDIUM
            );

            $role = ilObjectFactory::getInstanceByObjId($record['role'], false);
            if ($role instanceof ilObjRole) {
                $record['role'] = $role->getTitle();
            } else {
                $record['role'] = '';
            }

            $record["objects"] = '';
            $obj_types = ilObjLTIAdministration::getActiveObjectTypes($record["id"]);
            if ($obj_types) {
                foreach ($obj_types as $obj_type) {
                    $record["objects"] .= $GLOBALS['DIC']->language()->txt('objs_' . $obj_type) . '<br/>';
                }
            }

            yield $row_builder->buildDataRow((string) $record["id"], $record);
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->records);
    }

    /**
     * @throws ilCtrlException
     */
    public function getHtml(): string
    {
        $table = $this->ui_factory->table()
            ->data($this, $this->lng->txt('lti_object_consumer'), $this->getColumns())
            ->withOrder(new Order('title', Order::ASC))
            ->withRange(new Range(0, 20))
            ->withActions($this->getActions())
            ->withRequest($this->request);

        return $this->ui_renderer->render($table);
    }

    public function isEditable(): bool
    {
        return $this->editable;
    }

    public function setEditable(bool $a_editable): void
    {
        $this->editable = $a_editable;
    }

    protected function applyOrdering(array $records, Order $order, ?Range $range = null): array
    {
        [$order_field, $order_direction] = $order->join(
            [],
            fn($ret, $key, $value) => [$key, $value]
        );

        usort($records, static function (array $left, array $right) use ($order_field): int {
            $left_val = $left[$order_field] ?? '';
            $right_val = $right[$order_field] ?? '';
            return ilStr::strCmp($left_val, $right_val);
        });

        if ($order_direction === Order::DESC) {
            $records = array_reverse($records);
        }

        if ($range !== null) {
            $records = array_slice($records, $range->getStart(), $range->getLength());
        }

        return $records;
    }
}
