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

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Table of the platforms that may launch ILIAS as an LTI tool (LTI 1.1 and LTI Advantage).
 * Shows the columns and actions of the former table.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTIAdministrationPlatformTable implements DataRetrieval
{
    private const string ACTION_EDIT = "edit";
    private const string ACTION_ACTIVATE = "activate";
    private const string ACTION_DEACTIVATE = "deactivate";
    private const string ACTION_CONFIRM_DELETE = "confirm_delete";
    private const string ACTION_DELETE = "delete";

    private URLBuilder $url_builder;
    private URLBuilderToken $action_token;
    private URLBuilderToken $id_token;

    public function __construct(
        private readonly ilDBInterface $db,
        private readonly ilLanguage $lng,
        private readonly Factory $ui_factory,
        private readonly Renderer $ui_renderer,
        private readonly ilGlobalTemplateInterface $tpl,
        private readonly ilCtrlInterface $ctrl,
        private readonly ServerRequestInterface $request,
        private readonly bool $writable
    ) {
        $this->url_builder = new URLBuilder(new DataFactory()->uri((string) $this->request->getUri()));
        [$this->url_builder, $this->action_token, $this->id_token] = $this->url_builder->acquireParameters(
            ["lti", "platform"],
            "action",
            "ids"
        );
    }

    /**
     * Executes the table action of the current request, if any, and redirects to the given commands.
     * @throws ilCtrlException
     */
    public function handleAction(object $gui, string $return_cmd, string $edit_cmd): void
    {
        $query = $this->request->getQueryParams();
        $action = $query[$this->action_token->getName()] ?? null;
        if ($action === null || !$this->writable) {
            return;
        }
        $ids = array_filter(array_map("intval", (array) ($query[$this->id_token->getName()] ?? [])));
        if ($ids === []) {
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($gui, $return_cmd);
        }

        switch ($action) {
            case self::ACTION_EDIT:
                $this->ctrl->setParameter($gui, "cid", reset($ids));
                $this->ctrl->redirect($gui, $edit_cmd);
                break;

            case self::ACTION_ACTIVATE:
            case self::ACTION_DEACTIVATE:
                $activate = $action === self::ACTION_ACTIVATE;
                $this->db->manipulate(
                    "UPDATE lti_ext_consumer SET active = " . $this->db->quote((int) $activate, "integer")
                    . " WHERE " . $this->db->in("id", $ids, false, "integer")
                );
                $this->tpl->setOnScreenMessage(
                    "success",
                    $this->lng->txt($activate ? "lti_consumer_set_active" : "lti_consumer_set_inactive"),
                    true
                );
                break;

            case self::ACTION_CONFIRM_DELETE:
                $this->showDeleteModal($ids);
                exit();

            case self::ACTION_DELETE:
                // the released objects go with the platform
                $this->db->manipulate("DELETE FROM lti_ext_consumer WHERE " . $this->db->in("id", $ids, false, "integer"));
                $this->db->manipulate("DELETE FROM lti_ext_consumer_otype WHERE " . $this->db->in("consumer_id", $ids, false, "integer"));
                $this->db->manipulate("DELETE FROM lti2_consumer WHERE " . $this->db->in("ext_consumer_id", $ids, false, "integer"));
                $this->tpl->setOnScreenMessage("success", $this->lng->txt("lti_consumer_deleted"), true);
                break;
        }
        $this->ctrl->redirect($gui, $return_cmd);
    }

    public function getTable(): DataTable
    {
        $column = $this->ui_factory->table()->column();
        $icon = $this->ui_factory->symbol()->icon();

        $table = $this->ui_factory->table()->data($this, $this->lng->txt("lti_object_consumer"), [
            "active" => $column->boolean(
                $this->lng->txt("active"),
                $icon->custom("assets/images/standard/icon_ok.svg", $this->lng->txt("active")),
                $icon->custom("assets/images/standard/icon_not_ok.svg", $this->lng->txt("inactive"))
            ),
            "title" => $column->text($this->lng->txt("title")),
            "description" => $column->text($this->lng->txt("description"))->withIsOptional(true),
            "prefix" => $column->text($this->lng->txt("prefix"))->withIsOptional(true),
            "language" => $column->text($this->lng->txt("user_language"))->withIsOptional(true),
            "objects" => $column->listing($this->lng->txt("objects"))->withIsOptional(true),
            "role" => $column->text($this->lng->txt("role"))->withIsOptional(true),
            "version" => $column->text($this->lng->txt("lti_con_version"))->withIsOptional(true),
        ])
            ->withId("lti_provider_platform_table")
            ->withOrder(new Order("title", Order::ASC))
            ->withRange(new Range(0, 20))
            ->withRequest($this->request);

        return $this->writable ? $table->withActions($this->getActions()) : $table;
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): Generator {
        [$order_field, $order_direction] = $order->join([], fn($ret, $key, $value) => [$key, $value]);
        $order_columns = [
            "active" => "c.active",
            "title" => "c.title",
            "description" => "c.description",
            "prefix" => "c.prefix",
            "language" => "c.user_language",
            "role" => "role_title",
            "version" => "advantage",
        ];
        $order_by = ($order_columns[$order_field] ?? "c.title") . ($order_direction === Order::DESC ? " DESC" : " ASC");

        $this->db->setLimit($range->getLength(), $range->getStart());
        $result = $this->db->query(
            "SELECT c.id, c.active, c.title, c.description, c.prefix, c.user_language, r.title role_title,"
            // LTI Advantage registrations are stored per platform, or per released object in older installations
            . " (SELECT COUNT(*) FROM lti2_consumer l WHERE l.ext_consumer_id = c.id AND l.lti_version = "
            . $this->db->quote(ilLTIAdministrationPlatformForm::VERSION_ADVANTAGE, "text") . ") advantage"
            . " FROM lti_ext_consumer c LEFT JOIN object_data r ON r.obj_id = c.role AND r.type = 'role'"
            . " ORDER BY " . $order_by
        );
        while ($row = $this->db->fetchAssoc($result)) {
            yield $row_builder->buildDataRow((string) $row["id"], [
                "active" => (bool) $row["active"],
                "title" => htmlspecialchars((string) $row["title"]),
                "description" => htmlspecialchars((string) $row["description"]),
                "prefix" => htmlspecialchars((string) $row["prefix"]),
                "language" => (string) $row["user_language"],
                "objects" => $this->ui_factory->listing()->unordered($this->getObjectTypes((int) $row["id"])),
                "role" => (string) $row["role_title"],
                "version" => $this->lng->txt(
                    $row["advantage"] > 0 ? "lti_version_advantage" : "lti_version_1p1_deprecated"
                ),
            ]);
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return (int) $this->db->fetchAssoc($this->db->query("SELECT COUNT(*) cnt FROM lti_ext_consumer"))["cnt"];
    }

    /**
     * @return array
     */
    private function getActions(): array
    {
        $action = $this->ui_factory->table()->action();
        $url = fn(string $name) => $this->url_builder->withParameter($this->action_token, $name);

        return [
            self::ACTION_EDIT => $action->single($this->lng->txt("edit"), $url(self::ACTION_EDIT), $this->id_token),
            self::ACTION_ACTIVATE => $action->standard($this->lng->txt("activate"), $url(self::ACTION_ACTIVATE), $this->id_token),
            self::ACTION_DEACTIVATE => $action->standard($this->lng->txt("deactivate"), $url(self::ACTION_DEACTIVATE), $this->id_token),
            self::ACTION_CONFIRM_DELETE => $action->standard(
                $this->lng->txt("delete"),
                $url(self::ACTION_CONFIRM_DELETE),
                $this->id_token
            )->withAsync(),
        ];
    }

    /**
     * @param int $platform_id
     * @return array
     */
    private function getObjectTypes(int $platform_id): array
    {
        $types = [];
        $result = $this->db->query(
            "SELECT object_type FROM lti_ext_consumer_otype WHERE consumer_id = " . $this->db->quote($platform_id, "integer")
        );
        while ($row = $this->db->fetchAssoc($result)) {
            $types[] = $this->lng->txt("objs_" . $row["object_type"]);
        }

        return $types;
    }

    /**
     * @param array $ids
     */
    private function showDeleteModal(array $ids): void
    {
        $items = [];
        $result = $this->db->query("SELECT id, title FROM lti_ext_consumer WHERE " . $this->db->in("id", $ids, false, "integer"));
        while ($row = $this->db->fetchAssoc($result)) {
            $items[] = $this->ui_factory->modal()->interruptiveItem()->standard((string) $row["id"], (string) $row["title"]);
        }
        $delete_url = $this->url_builder
            ->withParameter($this->action_token, self::ACTION_DELETE)
            ->withParameter($this->id_token, array_map("strval", $ids))
            ->buildURI();

        echo $this->ui_renderer->renderAsync(
            $this->ui_factory->modal()->interruptive(
                $this->lng->txt("confirm"),
                $this->lng->txt("info_delete_sure"),
                (string) $delete_url
            )->withAffectedItems($items)
        );
    }
}
