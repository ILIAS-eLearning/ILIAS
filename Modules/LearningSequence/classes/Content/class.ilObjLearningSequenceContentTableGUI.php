<?php

declare(strict_types=1);

/**
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilObjLearningSequenceContentTableGUI extends ilTable2GUI
{
    public function __construct(
        ilObjLearningSequenceContentGUI $parent_gui,
        string $cmd,
        ilCtrl $ctrl,
        ilLanguage $lng,
        ilAccess $access,
        ilAdvancedSelectionListGUI $advanced_selection_list_gui,
        LSItemOnlineStatus $ls_item_online_status
    ) {
        parent::__construct($parent_gui, $cmd);

        $this->parent_gui = $parent_gui;
        $this->ctrl = $ctrl;
        $this->lng = $lng;
        $this->access = $access;
        $this->advanced_selection_list_gui = $advanced_selection_list_gui;
        $this->ls_item_online_status = $ls_item_online_status;

        $this->setTitle($this->lng->txt("table_sequence_content"));
        $this->setRowTemplate("tpl.content_table.html", "Modules/LearningSequence");
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
        $this->setExternalSorting(true);
        $this->setEnableAllCommand(true);
        $this->setShowRowsSelector(false);
        $this->setTopCommands(true);
        $this->setEnableHeader(true);

        $this->addColumn("", "", "1", true);
        $this->addColumn("");
        $this->addColumn($this->lng->txt("table_position"));
        $this->addColumn($this->lng->txt("table_title"));
        $this->addColumn($this->lng->txt("table_online"));
        $this->addColumn($this->lng->txt("table_may_proceed"));
        $this->addColumn($this->lng->txt("table_actions"));
    }

    protected function fillRow($set)
    {
        $ni = new ilNumberInputGUI(
            "",
            $this->parent_gui->getFieldName($this->parent_gui::FIELD_ORDER, $set->getRefId())
        );
        $ni->setSize("3");
        $ni->setDisabled($this->set_is_used);
        $ni->setValue(($set->getOrderNumber() + 1) * 10);

        if ($this->ls_item_online_status->hasOnlineStatus($set->getRefId())) {
            $cb = new ilCheckboxInputGUI(
                "",
                $this->parent_gui->getFieldName($this->parent_gui::FIELD_ONLINE, $set->getRefId())
            );
            $cb->setChecked($set->isOnline());
        } else {
            $cb = new ilCheckboxInputGUI("", "");
            $cb->setChecked(true);
            $cb->setDisabled(true);
        }
        $this->tpl->setVariable("ONLINE", $cb->render());

        $si = new ilSelectInputGUI(
            "",
            $this->parent_gui->getFieldName($this->parent_gui::FIELD_POSTCONDITION_TYPE, $set->getRefId())
        );
        $options = $this->parent_gui->getPossiblePostConditionsForType($set->getType());

        $si->setOptions($options);
        $si->setValue($set->getPostCondition()->getConditionOperator());

        $action_items = $this->getActionMenuItems($set->getRefId(), $set->getType());
        $obj_link = $this->getEditLink($set->getRefId(), $set->getType(), $action_items);

        $title = $set->getTitle();
        $title = sprintf(
            '<a href="%s">%s</a>',
            $obj_link,
            $title
        );

        $this->tpl->setVariable("ID", $set->getRefId());
        $this->tpl->setVariable("IMAGE", $set->getIconPath());
        $this->tpl->setVariable("ORDER", $ni->render());
        $this->tpl->setVariable("TITLE", $title);
        $this->tpl->setVariable("POST_CONDITIONS", $si->render());
        $this->tpl->setVariable("ACTIONS", $this->getItemActionsMenu($set->getRefId(), $set->getType()));
        $this->tpl->setVariable("TYPE", $set->getType());
    }


    protected function getItemActionsMenu(int $ref_id, string $type) : string
    {
        $item_obj_id = $this->getObjIdFor($ref_id);
        $item_list_gui = $this->getListGuiFor($type);
        $item_list_gui->initItem($ref_id, $item_obj_id);

        $item_list_gui->enableCut(true);
        $item_list_gui->enableDelete(true);
        $item_list_gui->enableLink(true);
        $item_list_gui->enableTimings(false);

        $lso_gui = $this->parent_gui->parent_gui;
        $item_list_gui->setContainerObject($lso_gui);

        return $item_list_gui->getCommandsHTML();
    }

    protected function getActionMenuItems(int $ref_id, string $type) : array
    {
        $item_obj_id = $this->getObjIdFor($ref_id);
        $item_list_gui = $this->getListGuiFor($type);
        $item_list_gui->initItem($ref_id, $item_obj_id);
        return $item_list_gui->getCommands();
    }

    /**
    * @return string|null
    */
    protected function getEditLink(int $ref_id, string $type, array $action_items)
    {
        switch ($type) {
            case $this->ls_item_online_status::S_LEARNMODULE_IL:
            case $this->ls_item_online_status::S_LEARNMODULE_HTML:
            case $this->ls_item_online_status::S_SURVEY:
                $prop_for_type = 'properties';
                break;

            case $this->ls_item_online_status::S_SAHS:
            case $this->ls_item_online_status::S_CONTENTPAGE:
            case $this->ls_item_online_status::S_EXERCISE:
            case $this->ls_item_online_status::S_FILE:
                    $prop_for_type = 'edit';
                    break;

            case $this->ls_item_online_status::S_TEST:
                    $prop_for_type = 'ilObjTestSettingsGeneralGUI::showForm';
                    break;

            case $this->ls_item_online_status::S_IND_ASSESSMENT:
            default:
                return $this->getStdLink($ref_id, $type);
        }

        $props = array_filter(
            $action_items,
            function ($action_item) use ($prop_for_type) {
                //var_dump($action_item['cmd']);
                return $action_item['cmd'] === $prop_for_type;
            }
        );

        if (count($props) > 0) {
            return array_shift($props)['link'];
        }
        return null;
    }

    protected function getObjIdFor(int $ref_id) : int
    {
        return ilObject::_lookupObjId($ref_id);
    }

    protected function getListGuiFor(string $type) : ilObjectListGUI
    {
        return ilObjectListGUIFactory::_getListGUIByType($type);
    }

    protected function getStdLink(int $ref_id, string $type) : string
    {
        return ilLink::_getLink($ref_id, $type);
    }
}
