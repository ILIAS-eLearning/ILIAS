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

namespace ILIAS\LearningModule\Table;

use ILIAS\UI\Component\Table\Column\Column;
use ILIAS\UI\Component\Table\Table;
use ILIAS\UI\URLBuilder;
use ILIAS\Repository\BaseGUIRequest;
use ILIAS\UI\URLBuilderToken;

class TableAdapterGUI
{
    use BaseGUIRequest;

    protected const STANDARD = 0;
    protected const SINGLE = 1;
    protected const MULTI = 2;
    protected string $order_cmd = "";
    protected string $last_action_key;
    protected URLBuilderToken $row_id_token;
    protected URLBuilderToken $action_parameter_token;
    protected URLBuilder $url_builder;
    protected \ILIAS\Data\Factory $df;

    protected \ilLanguage $lng;
    protected ?Table $table = null;
    protected string $last_key;
    protected \ilCtrlInterface $ctrl;
    protected \ILIAS\DI\UIServices $ui;
    protected array $columns = [];
    protected array $actions = [];

    public function __construct(
        protected string $id,
        protected string $title,
        protected RetrievalInterface $retrieval,
        protected object $parent_gui,
        protected string $parent_cmd = "tableCommand",
        protected string $namespace = ""
    ) {
        global $DIC;
        $this->ui = $DIC->ui();
        $this->ctrl = $DIC->ctrl();
        $this->http = $DIC->http();
        $this->lng = $DIC->language();
        $this->refinery = $DIC->refinery();
        $this->df = new \ILIAS\Data\Factory();
        $this->initRequest($this->http, $this->refinery);
        if ($namespace === "") {
            $this->namespace = $id;
        }

        $form_action = $this->df->uri(
            ILIAS_HTTP_PATH . '/' .
            $this->ctrl->getLinkTarget($this->parent_gui, $this->parent_cmd)
        );
        $this->url_builder = new URLBuilder($form_action);
        [$this->url_builder, $this->action_parameter_token, $this->row_id_token] =
            $this->url_builder->acquireParameters(
                [$this->namespace], // namespace
                "table_action", //this is the actions's parameter name
                "ids"   //this is the parameter name to be used for row-ids
            );

    }

    public function ordering(
        string $order_cmd
    ): self {
        $this->order_cmd = $order_cmd;
        return $this;
    }

    public function textColumn(
        string $key,
        string $title,
        bool $sortable = false
    ): self {
        $column = $this->ui->factory()->table()->column()->text($title)->withIsSortable($sortable);
        $this->addColumn($key, $column);
        return $this;
    }

    public function iconColumn(
        string $key,
        string $title,
        bool $sortable = false
    ): self {
        $column = $this->ui->factory()->table()->column()->statusIcon($title);
        $this->addColumn($key, $column);
        return $this;
    }

    public function singleAction(
        string $action,
        string $title,
        bool $async = false
    ): self {
        $this->addAction(self::SINGLE, $action, $title, $async);
        return $this;
    }

    public function redirect(
        array $class_path,
        string $cmd = "",
        string $id_param = ""
    ): self {
        $act = $this->actions[$this->last_action_key] ?? false;
        if ($act && $act["type"] === self::SINGLE) {
            $act["redirect_class_path"] = $class_path;
            $act["redirect_cmd"] = $cmd;
            $act["redirect_id_param"] = $id_param;
            $this->actions[$this->last_action_key] = $act;
        }
        return $this;
    }

    public function standardAction(
        string $action,
        string $title
    ): self {
        $this->addAction(self::STANDARD, $action, $title);
        return $this;
    }

    public function multiAction(
        string $action,
        string $title
    ): self {
        $this->addAction(self::MULTI, $action, $title);
        return $this;
    }

    protected function addAction(int $type, string $action, string $title, bool $async = false): void
    {
        $this->actions[$action] = [
            "type" => $type,
            "action" => $action,
            "title" => $title,
            "async" => $async
        ];
        $this->last_action_key = $action;
    }

    protected function addColumn(string $key, Column $column): void
    {
        if ($key === "") {
            throw new \ilException("Missing Input Key: " . $key);
        }
        if (isset($this->columns[$key])) {
            throw new \ilException("Duplicate Input Key: " . $key);
        }
        $this->columns[$key] = $column;
        $this->last_key = $key;
    }

    protected function getColumnForKey(string $key): Column
    {
        if (!isset($this->columns[$key])) {
            throw new \ilException("Unknown Key: " . $key);
        }
        return $this->columns[$key];
    }

    protected function getLastColumn(): ?Column
    {
        return $this->columns[$this->last_key] ?? null;
    }

    protected function replaceLastColumn(Column $column): void
    {
        if ($this->last_key !== "") {
            $this->columns[$this->last_key] = $column;
        }
    }

    public function getItemIds(): array
    {
        $ids = $this->intArray($this->row_id_token->getName());
        if (count($ids) > 0) {
            return $ids;           // from table multi action
        }
        $ids = $this->intArray("interruptive_items");   // from confirmation
        if (count($ids) > 0) {
            return $ids;
        }
        return [];
    }

    public function handleCommand(): void
    {
        $action = $this->str($this->action_parameter_token->getName());
        if ($action !== "") {
            if ($this->actions[$action]["type"] === self::SINGLE) {
                $id = $this->getItemIds()[0];
                if ($this->actions[$action]["redirect_class_path"] ?? false) {
                    $path = $this->actions[$action]["redirect_class_path"];
                    if ($this->actions[$action]["redirect_id_param"] ?? false) {
                        $this->ctrl->setParameterByClass(
                            $path[count($path) - 1],
                            $this->actions[$action]["redirect_id_param"],
                            $id
                        );
                    }
                    $cmd = $this->actions[$action]["redirect_cmd"] ?? $action;
                    $this->ctrl->redirectByClass($this->actions[$action]["redirect_class_path"], $cmd);
                }
                $this->parent_gui->$action($id);
            } else {
                $this->parent_gui->$action($this->getItemIds());
            }
        }
    }

    protected function getTable(): Table
    {
        $a = $this->ui->factory()->table()->action();

        if (is_null($this->table)) {
            $columns = [];
            foreach ($this->columns as $key => $column) {
                $columns[$key] = $column;
            }
            $actions = [];
            foreach ($this->actions as $act) {
                switch ($act["type"]) {
                    case self::SINGLE:
                        $actions[$act["action"]] = $a->single(
                            $act["title"],
                            $this->url_builder->withParameter($this->action_parameter_token, $act["action"]),
                            $this->row_id_token
                        );
                        break;
                    case self::STANDARD:
                        $actions[$act["action"]] = $a->standard(
                            $act["title"],
                            $this->url_builder->withParameter($this->action_parameter_token, $act["action"]),
                            $this->row_id_token
                        );
                        break;
                    case self::MULTI:
                        $actions[$act["action"]] = $a->multi(
                            $act["title"],
                            $this->url_builder->withParameter($this->action_parameter_token, $act["action"]),
                            $this->row_id_token
                        );
                        break;
                }
                if ($act["async"]) {
                    $actions[$act["action"]] = $actions[$act["action"]]->withAsync(true);
                }
            }
            if ($this->order_cmd !== "") {
                $uri = $this->df->uri(
                    ILIAS_HTTP_PATH . '/' .
                    $this->ctrl->getLinkTarget($this->parent_gui, $this->order_cmd)
                );
                $table_retrieval = new OrderingBinding($this->retrieval);
                $this->table = $this
                    ->ui
                    ->factory()
                    ->table()
                    ->ordering($this->title, $columns, $table_retrieval, $uri)
                    ->withId($this->id)
                    ->withActions($actions)
                    ->withRequest($this->http->request());
            } else {
                $table_retrieval = new TableRetrieval($this->retrieval);
                $this->table = $this
                    ->ui
                    ->factory()
                    ->table()
                    ->data($this->title, $columns, $table_retrieval)
                    ->withId($this->id)
                    ->withActions($actions)
                    ->withRequest($this->http->request());
            }
        }
        return $this->table;
    }

    public function getData(): ?array
    {
        return $this->getTable()->getData();
    }

    public function render(): string
    {
        $html = $this->ui->renderer()->render($this->getTable());
        return $html;
    }
}
