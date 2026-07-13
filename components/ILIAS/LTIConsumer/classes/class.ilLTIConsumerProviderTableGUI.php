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
use ILIAS\UI\Component\Input\Container\Filter\Standard as Filter;
use ILIAS\UI\Component\Symbol\Icon\Icon as IconAlias;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory;
use ILIAS\UI\Implementation\Component\Symbol\Icon\Icon;
use ILIAS\UI\URLBuilder;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Class ilLTIConsumerProviderTableGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package components\ILIAS/LTIConsumer
 */
class ilLTIConsumerProviderTableGUI implements DataRetrieval
{
    protected ilLanguage $lng;
    protected Factory $ui_factory;
    protected \ILIAS\UI\Renderer $ui_renderer;
    protected ilUIService $ui_service;
    private ServerRequestInterface|RequestInterface $request;
    protected \ILIAS\Data\Factory $data_factory;
    protected ilCtrlInterface $ctrl;
    protected WrapperFactory $wrapper;
    protected \ILIAS\Refinery\Factory $refinery;
    private array $records;
    public ?object $parent_obj;
    public ?string $parent_cmd;
    private bool $acceptProviderAsGlobal = false;
    private bool $resetProviderToUserScope = false;
    private bool $selectProviderForm = false;

    public function __construct(?object $a_parent_obj, ?string $a_parent_cmd)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ui_factory = $DIC->ui()->factory();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->ui_service = $DIC->uiService();
        $this->request = $DIC->http()->request();
        $this->data_factory = new \ILIAS\Data\Factory();
        $this->ctrl = $DIC->ctrl();
        $this->wrapper = $DIC->http()->wrapper();
        $this->refinery = $DIC->refinery();

        $this->parent_obj = $a_parent_obj;
        $this->parent_cmd = $a_parent_cmd;
    }

    public function enableAcceptProviderAsGlobal(): void
    {
        $this->acceptProviderAsGlobal = true;
    }

    public function enableResetProviderToUserScope(): void
    {
        $this->resetProviderToUserScope = true;
    }

    public function enableSelectProviderForm(): void
    {
        $this->selectProviderForm = true;
    }

    /**
     * @throws ilObjectNotFoundException
     * @throws ilCtrlException
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
            $record["icon"] = $record["icon"] ?? "lti";
            $record["icon"] = $this->ui_factory->symbol()->icon()->standard($record["icon"], $record["icon"], IconAlias::SMALL);

            if ($this->selectProviderForm) {
                $this->ctrl->setParameter($this->parent_obj, 'provider_id', $record['id']);
                $record["title"] = $this->ui_factory->link()->standard($record['title'], $this->ctrl->getLinkTarget($this->parent_obj, "save"));
            } else {
                $this->ctrl->setParameter($this->parent_obj, 'provider_id', $record['id']);
                $record["title"] = $this->ui_factory->link()->standard($record['title'], $this->ctrl->getLinkTarget($this->parent_obj, ilLTIConsumerAdministrationGUI::CMD_SHOW_GLOBAL_PROVIDER_FORM));
            }

            $record["category"] = $this->getCategoryTranslation($record['category']);

            $record["outcome"] = $this->getHasOutcomeFormatted($record['outcome']);
            $record["internal"] = $this->getIsInternalFormatted(!$record['external']);
            $record["with_key"] = $this->getIsWithKeyFormatted(!$record['provider_key_customizable']);

            $record["availability"] = $this->getAvailabilityLabel($record);
            $record["own_provider"] = $this->getOwnProviderLabel($record);
            $record["provider_creator"] = $this->getProviderCreatorLabel($record);

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

    public function setData(array $data): void
    {
        $this->records = $data;
    }

    protected function applyOrdering(array $records, Order $order, ?Range $range = null): array
    {
        [$order_field, $order_direction] = $order->join(
            [],
            fn($ret, $key, $value) => [$key, $value]
        );

        $order_field = (string) $order_field;
        $sortable_records = array_map(function (array $record) use ($order_field): array {
            return [
                'sort_key' => $this->getSortableValue($record, $order_field),
                'record' => $record,
            ];
        }, $records);

        usort($sortable_records, static function (array $left, array $right): int {
            return ilStr::strCmp($left['sort_key'], $right['sort_key']);
        });

        $records = array_column($sortable_records, 'record');

        if ($order_direction === Order::DESC) {
            $records = array_reverse($records);
        }

        if ($range !== null) {
            $records = array_slice($records, $range->getStart(), $range->getLength());
        }

        return $records;
    }

    protected function getSortableValue(array $record, string $order_field): string
    {
        return match ($order_field) {
            'category' => $this->getCategoryTranslation((string) ($record['category'] ?? '')),
            'outcome' => $this->getHasOutcomeFormatted((bool) ($record['outcome'] ?? false)),
            'internal' => $this->getIsInternalFormatted(!(bool) ($record['external'] ?? false)),
            'with_key' => $this->getIsWithKeyFormatted(!(bool) ($record['provider_key_customizable'] ?? false)),
            'availability' => $this->getAvailabilityLabel($record),
            'own_provider' => $this->getOwnProviderLabel($record),
            'provider_creator' => $this->getProviderCreatorLabel($record),
            default => (string) ($record[$order_field] ?? ''),
        };
    }

    /**
     * @throws ilCtrlException
     */
    public function getHTML(bool $hasWriteAccess = false): string
    {
        $table = $this->ui_factory->table()
            ->data($this, $this->lng->txt('tbl_provider_header'), $this->getColumns())
            ->withOrder(new Order('title', Order::ASC))
            ->withRange(new Range(0, 20))
            ->withRequest($this->request);

        if ($hasWriteAccess) {
            $table = $table->withActions($this->getActions());
        }

        return $this->ui_renderer->render($table);
    }

    private function getColumns(): array
    {
        return [
            'icon' => $this->ui_factory->table()->column()->statusIcon($this->lng->txt('icon')),
            'title' => $this->ui_factory->table()->column()->link($this->lng->txt('title')),
            'description' => $this->ui_factory->table()->column()->text($this->lng->txt('tbl_lti_prov_description')),
            'category' => $this->ui_factory->table()->column()->text($this->lng->txt('tbl_lti_prov_category'))->withIsOptional(true),
            'keywords' => $this->ui_factory->table()->column()->text($this->lng->txt('tbl_lti_prov_keywords')),
            'outcome' => $this->ui_factory->table()->column()->text($this->lng->txt('tbl_lti_prov_outcome'))->withIsOptional(true),
            'internal' => $this->ui_factory->table()->column()->text($this->lng->txt('tbl_lti_prov_internal'))->withIsOptional(true),
            'with_key' => $this->ui_factory->table()->column()->text($this->lng->txt('tbl_lti_prov_with_key')),
            'availability' => $this->ui_factory->table()->column()->text($this->lng->txt('tbl_lti_prov_availability')),
            'own_provider' => $this->ui_factory->table()->column()->text($this->lng->txt('tbl_lti_prov_own_provider'))->withIsOptional(true),
            'provider_creator' => $this->ui_factory->table()->column()->text($this->lng->txt('tbl_lti_prov_provider_creator'))->withIsOptional(true),
            'usages_untrashed' => $this->ui_factory->table()->column()->text($this->lng->txt('tbl_lti_prov_usages_untrashed')),
            'usages_trashed' => $this->ui_factory->table()->column()->text($this->lng->txt('tbl_lti_prov_usages_trashed'))->withIsOptional(true),
        ];
    }

    /**
     * @throws ilCtrlException
     */
    private function getActions(): array
    {
        $df = new \ILIAS\Data\Factory();
        $here_uri = $df->uri($this->request->getUri()->__toString());
        $url_builder = new URLBuilder($here_uri);

        $query_params_namespace = ['provider', 'table'];
        list($url_builder, $id_token, $action_token) = $url_builder->acquireParameters(
            $query_params_namespace,
            "provider_id",
            "action"
        );

        $query = $this->wrapper->query();
        if ($query->has($action_token->getName())) {
            $action = $query->retrieve($action_token->getName(), $this->refinery->to()->string());
            $ids = $query->retrieve($id_token->getName(), $this->refinery->custom()->transformation(fn($v) => $v));

            switch ($action) {
                case "edit":
                    $id = $ids[0] ?? null;
                    $this->ctrl->setParameter($this->parent_obj, 'provider_id', $id);
                    $this->ctrl->redirect($this->parent_obj, ilLTIConsumerAdministrationGUI::CMD_SHOW_USER_PROVIDER_FORM);
                    break;
                case "delete_global":
                    if (count($ids) > 1) {
                        $this->ctrl->setParameter($this->parent_obj, 'provider_ids', implode(",", $ids));
                        $this->ctrl->redirect($this->parent_obj, ilLTIConsumerAdministrationGUI::CMD_DELETE_GLOBAL_PROVIDER_MULTI);
                    } else {
                        $id = $ids[0] ?? null;
                        $this->ctrl->setParameter($this->parent_obj, 'provider_id', $id);
                        $this->ctrl->redirect($this->parent_obj, ilLTIConsumerAdministrationGUI::CMD_DELETE_GLOBAL_PROVIDER);
                    }
                    break;
                case "delete_user":
                    if (count($ids) > 1) {
                        $this->ctrl->setParameter($this->parent_obj, 'provider_ids', implode(",", $ids));
                        $this->ctrl->redirect($this->parent_obj, ilLTIConsumerAdministrationGUI::CMD_DELETE_USER_PROVIDER_MULTI);
                    } else {
                        $id = $ids[0] ?? null;
                        $this->ctrl->setParameter($this->parent_obj, 'provider_id', $id);
                        $this->ctrl->redirect($this->parent_obj, ilLTIConsumerAdministrationGUI::CMD_DELETE_USER_PROVIDER);
                    }
                    break;
                case "global":
                    if (count($ids) > 1) {
                        $this->ctrl->setParameter($this->parent_obj, 'provider_ids', implode(",", $ids));
                        $this->ctrl->redirect($this->parent_obj, ilLTIConsumerAdministrationGUI::CMD_ACCEPT_PROVIDER_AS_GLOBAL_MULTI);
                    } else {
                        $id = $ids[0] ?? null;
                        $this->ctrl->setParameter($this->parent_obj, 'provider_id', $id);
                        $this->ctrl->redirect($this->parent_obj, ilLTIConsumerAdministrationGUI::CMD_ACCEPT_PROVIDER_AS_GLOBAL);
                    }
                    break;
                case "reset":
                    if (count($ids) > 1) {
                        $this->ctrl->setParameter($this->parent_obj, 'provider_ids', implode(",", $ids));
                        $this->ctrl->redirect($this->parent_obj, ilLTIConsumerAdministrationGUI::CMD_RESET_PROVIDER_TO_USER_SCOPE_MULTI);
                    } else {
                        $id = $ids[0] ?? null;
                        $this->ctrl->setParameter($this->parent_obj, 'provider_id', $id);
                        $this->ctrl->redirect($this->parent_obj, ilLTIConsumerAdministrationGUI::CMD_RESET_PROVIDER_TO_USER_SCOPE);
                    }
                    break;
            }
        }


        $actions = [
            "edit" => $this->ui_factory->table()->action()->single(
                $this->lng->txt('lti_action_edit_provider'),
                $url_builder->withParameter($action_token, "edit"),
                $id_token
            ),
        ];

        if ($this->acceptProviderAsGlobal) {
            $actions["global"] = $this->ui_factory->table()->action()->standard(
                $this->lng->txt('lti_action_accept_provider_as_global'),
                $url_builder->withParameter($action_token, "global"),
                $id_token
            );
            $actions["delete_user"] = $this->ui_factory->table()->action()->standard(
                $this->lng->txt('lti_delete_provider'),
                $url_builder->withParameter($action_token, "delete_user"),
                $id_token
            );
        }

        if ($this->resetProviderToUserScope) {
            $actions["reset"] = $this->ui_factory->table()->action()->standard(
                $this->lng->txt('lti_action_reset_provider_to_user_scope'),
                $url_builder->withParameter($action_token, "reset"),
                $id_token
            );
            $actions["delete_global"] = $this->ui_factory->table()->action()->standard(
                $this->lng->txt('lti_delete_provider'),
                $url_builder->withParameter($action_token, "delete_global"),
                $id_token
            );
        }

        return $actions;
    }

    protected function getHasOutcomeFormatted(bool $hasOutcome): string
    {
        global $DIC;

        return $hasOutcome ? $DIC->language()->txt('yes') : '';
    }

    protected function getIsInternalFormatted(bool $isInternal): string
    {
        global $DIC;

        return $isInternal ? $DIC->language()->txt('yes') : '';
    }

    protected function getIsWithKeyFormatted(bool $isWithKey): string
    {
        global $DIC;

        return $isWithKey ? $DIC->language()->txt('yes') : '';
    }

    protected function getCategoryTranslation(string $category): string
    {
        $categories = ilLTIConsumeProvider::getCategoriesSelectOptions();
        return $categories[$category];
    }

    protected function getAvailabilityLabel(array $data): string
    {
        global $DIC;

        return match ($data['availability']) {
            ilLTIConsumeProvider::AVAILABILITY_CREATE => $DIC->language()->txt('lti_con_prov_availability_create'),
            ilLTIConsumeProvider::AVAILABILITY_EXISTING => $DIC->language()->txt('lti_con_prov_availability_existing'),
            ilLTIConsumeProvider::AVAILABILITY_NONE => $DIC->language()->txt('lti_con_prov_availability_non'),
            default => '',
        };
    }

    protected function getOwnProviderLabel(array $data): string
    {
        global $DIC;

        if ($data['creator'] == $DIC->user()->getId()) {
            return $DIC->language()->txt('yes');
        }

        return '';
    }

    /**
     * @throws ilObjectNotFoundException
     * @throws ilDatabaseException
     */
    protected function getProviderCreatorLabel(array $data): string
    {
        global $DIC;

        if ($data['creator']) {
            /* @var ilObjUser $user */
            $user = ilObjectFactory::getInstanceByObjId($data['creator'], false);

            if ($user) {
                return $user->getFullname();
            }

            return $DIC->language()->txt('deleted_user');
        }

        return '';
    }

    /**
     * @throws ilCtrlException
     */
    public function getFilter(): Filter
    {
        $filter_inputs = [
            'title' => $this->ui_factory->input()->field()->text($this->lng->txt("title")),
            'keywords' => $this->ui_factory->input()->field()->text($this->lng->txt("tbl_lti_prov_keywords")),
            'outcome' => $this->ui_factory->input()->field()->select($this->lng->txt("tbl_lti_prov_outcome"), [
                'yes' => $this->lng->txt('yes'),
                'no' => $this->lng->txt('no'),
            ]),
            'internal' => $this->ui_factory->input()->field()->select($this->lng->txt("tbl_lti_prov_internal"), [
                'yes' => $this->lng->txt('yes'),
                'no' => $this->lng->txt('no'),
            ]),
            'with_key' => $this->ui_factory->input()->field()->select($this->lng->txt("tbl_lti_prov_with_key"), [
                'yes' => $this->lng->txt('yes'),
                'no' => $this->lng->txt('no'),
            ]),
            'category' => $this->ui_factory->input()->field()->select($this->lng->txt("tbl_lti_prov_category"), ilLTIConsumeProvider::getCategoriesSelectOptions()),
        ];

        $active = array_fill(0, count($filter_inputs), true);

        return $this->ui_service->filter()->standard(
            'lti_consumer_provider_table',
            $this->ctrl->getLinkTarget($this->parent_obj, $this->parent_cmd),
            $filter_inputs,
            $active,
            true
        );
    }
}
