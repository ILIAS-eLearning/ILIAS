<?php

declare(strict_types=1);

/**
 * Class ilObjLearningSequenceContentGUI
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilObjLearningSequenceContentGUI
{
    const CMD_MANAGE_CONTENT = "manageContent";
    const CMD_SAVE = "save";
    const CMD_DELETE = "delete";
    const CMD_CONFIRM_DELETE = "confirmDelete";
    const CMD_CANCEL = "cancel";

    const FIELD_ORDER = 'f_order';
    const FIELD_ONLINE = 'f_online';
    const FIELD_POSTCONDITION_TYPE = 'f_pct';

    public function __construct(
        ilObjLearningSequenceGUI $parent_gui,
        ilCtrl $ctrl,
        ilTemplate $tpl,
        ilLanguage $lng,
        ilAccess $access,
        ilConfirmationGUI $confirmation_gui,
        LSItemOnlineStatus $ls_item_online_status
    ) {
        $this->parent_gui = $parent_gui;
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->access = $access;
        $this->confirmation_gui = $confirmation_gui;
        $this->ls_item_online_status = $ls_item_online_status;
    }

    public function executeCommand()
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

    protected function manageContent()
    {
        // Adds a btn to the gui which allows adding possible objects.
        $this->parent_gui->showPossibleSubObjects();

        $data = $this->parent_gui->getObject()->getLSItems();
        $this->renderTable($data);
    }

    protected function renderTable(array $ls_items)
    {
        $table = new ilObjLearningSequenceContentTableGUI(
            $this,
            self::CMD_MANAGE_CONTENT,
            $this->ctrl,
            $this->lng,
            $this->access,
            new ilAdvancedSelectionListGUI(),
            $this->ls_item_online_status
        );

        $table->setData($ls_items);
        $table->addMultiCommand(self::CMD_CONFIRM_DELETE, $this->lng->txt("delete"));

        $table->addCommandButton(self::CMD_SAVE, $this->lng->txt("save"));

        $this->tpl->setContent($table->getHtml());
    }

    /**
     * Handle the confirmDelete command
     *
     * @return 	void
     */
    protected function confirmDelete()
    {
        $ref_ids = $_POST["id"];

        if (count($ref_ids) < 1) {
            ilUtil::sendInfo($this->lng->txt('no_entries_selected_for_delete'), true);
            $this->ctrl->redirect($this, self::CMD_MANAGE_CONTENT);
        }

        foreach ($ref_ids as $ref_id) {
            $obj = ilObjectFactory::getInstanceByRefId($ref_id);
            $this->confirmation_gui->addItem("id[]", $ref_id, $obj->getTitle());
        }

        $this->confirmation_gui->setFormAction($this->ctrl->getFormAction($this));
        $this->confirmation_gui->setHeaderText($this->lng->txt("delete_confirmation"));
        $this->confirmation_gui->setConfirm($this->lng->txt("confirm"), self::CMD_DELETE);
        $this->confirmation_gui->setCancel($this->lng->txt("cancel"), self::CMD_CANCEL);

        $this->tpl->setContent($this->confirmation_gui->getHTML());
    }

    protected function delete()
    {
        $ref_ids = $_POST["id"];

        $ref_ids = array_map(function ($i) {
            return (int) $i;
        }, $ref_ids);

        $this->parent_gui->getObject()->deletePostConditionsForSubObjects($ref_ids);

        ilUtil::sendSuccess($this->lng->txt('entries_deleted'), true);
        $this->ctrl->redirect($this, self::CMD_MANAGE_CONTENT);
    }


    public function getPossiblePostConditionsForType(string $type) : array
    {
        return $this->parent_gui->getObject()->getPossiblePostConditionsForType($type);
    }


    public function getFieldName(string $field_name, int $ref_id) : string
    {
        return implode('_', [$field_name, (string) $ref_id]);
    }

    protected function save()
    {
        $post = $_POST;
        $data = $this->parent_gui->getObject()->getLSItems();

        $updated = [];
        foreach ($data as $lsitem) {
            $ref_id = $lsitem->getRefId();
            $online = $this->getFieldName(self::FIELD_ONLINE, $ref_id);
            $order = $this->getFieldName(self::FIELD_ORDER, $ref_id);
            $condition_type = $this->getFieldName(self::FIELD_POSTCONDITION_TYPE, $ref_id);

            $condition = $lsitem->getPostCondition()
                ->withConditionOperator($post[$condition_type]);
            $updated[] = $lsitem
                ->withOnline((bool) $post[$online])
                ->withOrderNumber((int) $post[$order])
                ->withPostCondition($condition);
        }

        $this->parent_gui->getObject()->storeLSItems($updated);

        ilUtil::sendSuccess($this->lng->txt('entries_updated'), true);
        $this->ctrl->redirect($this, self::CMD_MANAGE_CONTENT);
    }
}
