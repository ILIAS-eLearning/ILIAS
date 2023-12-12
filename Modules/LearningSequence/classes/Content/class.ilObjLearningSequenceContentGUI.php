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

use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;
use ILIAS\Refinery\Factory;

class ilObjLearningSequenceContentGUI
{
    public const CMD_MANAGE_CONTENT = "manageContent";
    public const CMD_SAVE = "save";
    public const CMD_DELETE = "delete";
    public const CMD_CONFIRM_DELETE = "confirmDelete";
    public const CMD_CANCEL = "cancel";

    public const FIELD_ORDER = 'f_order';
    public const FIELD_ONLINE = 'f_online';
    public const FIELD_POSTCONDITION_TYPE = 'f_pct';

    public function __construct(
        protected ilObjLearningSequenceGUI $parent_gui,
        protected ilCtrl $ctrl,
        protected ilGlobalTemplateInterface $tpl,
        protected ilLanguage $lng,
        protected ilAccess $access,
        protected ilConfirmationGUI $confirmation_gui,
        protected LSItemOnlineStatus $ls_item_online_status,
        protected ArrayBasedRequestWrapper $post_wrapper,
        protected Factory $refinery,
        protected ILIAS\UI\Factory $ui_factory,
        protected ILIAS\UI\Renderer $ui_renderer
    ) {
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();

        switch ($cmd) {
            case self::CMD_CANCEL:
                $this->ctrl->redirect($this, self::CMD_MANAGE_CONTENT);
                break;
            case self::CMD_MANAGE_CONTENT:
            case self::CMD_SAVE:
            case self::CMD_DELETE:
            case self::CMD_CONFIRM_DELETE:
                $this->$cmd();
                break;
            default:
                throw new ilException("ilObjLearningSequenceContentGUI: Command not supported: $cmd");
        }
    }

    protected function manageContent(): void
    {
        // Adds a btn to the gui which allows adding possible objects.
        $this->parent_gui->showPossibleSubObjects();

        $data = $this->parent_gui->getObject()->getLSItems();
        // Sadly, ilTable2 only wants an array for fillRow, so we need to wrap this...
        $data = array_map(fn($s) => [$s], $data);
        $this->renderTable($data);
    }

    protected function renderTable(array $ls_items): void
    {
        $alert_icon = $this->ui_renderer->render(
            $this->ui_factory->symbol()->icon()
                ->custom(ilUtil::getImagePath("standard/icon_alert.svg"), $this->lng->txt("warning"))
                ->withSize('small')
        );
        $table = new ilObjLearningSequenceContentTableGUI(
            $this,
            $this->parent_gui,
            self::CMD_MANAGE_CONTENT,
            $this->ctrl,
            $this->lng,
            $this->access,
            $this->ui_factory,
            $this->ui_renderer,
            $this->ls_item_online_status,
            $alert_icon
        );

        $table->setData($ls_items);
        $table->addMultiCommand(self::CMD_CONFIRM_DELETE, $this->lng->txt("delete"));

        $table->addCommandButton(self::CMD_SAVE, $this->lng->txt("save"));

        $this->tpl->setContent($table->getHtml());
    }

    /**
     * Handle the confirmDelete command
     */
    protected function confirmDelete(): void
    {
        $this->parent_gui->deleteObject();
    }

    protected function delete(): void
    {
        $ref_ids = $this->post_wrapper->retrieve(
            "id",
            $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
        );

        $this->parent_gui->getObject()->deletePostConditionsForSubObjects($ref_ids);

        $this->tpl->setOnScreenMessage("success", $this->lng->txt('entries_deleted'), true);
        $this->ctrl->redirect($this, self::CMD_MANAGE_CONTENT);
    }

    /**
     * @return array<"value" => "option_text">
     */
    public function getPossiblePostConditionsForType(string $type): array
    {
        return $this->parent_gui->getObject()->getPossiblePostConditionsForType($type);
    }


    public function getFieldName(string $field_name, int $ref_id): string
    {
        return implode('_', [$field_name, (string) $ref_id]);
    }

    protected function save(): void
    {
        $data = $this->parent_gui->getObject()->getLSItems();
        $r = $this->refinery;

        $updated = [];
        foreach ($data as $lsitem) {
            $ref_id = $lsitem->getRefId();
            $online = $this->getFieldName(self::FIELD_ONLINE, $ref_id);
            $order = $this->getFieldName(self::FIELD_ORDER, $ref_id);
            $condition_type = $this->getFieldName(self::FIELD_POSTCONDITION_TYPE, $ref_id);

            $condition_type = $this->post_wrapper->retrieve($condition_type, $r->kindlyTo()->string());
            $online = $this->post_wrapper->retrieve($online, $r->byTrying([$r->kindlyTo()->bool(), $r->always(false)]));
            $order = $this->post_wrapper->retrieve(
                $order,
                $r->in()->series([
                    $r->kindlyTo()->string(),
                    $r->custom()->transformation(fn($v) => ltrim($v, '0')),
                    $r->kindlyTo()->int()
                ])
            );

            $condition = $lsitem->getPostCondition()
                ->withConditionOperator($condition_type);
            $updated[] = $lsitem
                ->withOnline($online)
                ->withOrderNumber($order)
                ->withPostCondition($condition);
        }

        $this->parent_gui->getObject()->storeLSItems($updated);
        $this->tpl->setOnScreenMessage("success", $this->lng->txt('entries_updated'), true);
        $this->ctrl->redirect($this, self::CMD_MANAGE_CONTENT);
    }
}
