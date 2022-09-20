<?php

declare(strict_types=1);

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

class ilObjLearningSequenceContentTableGUI extends ilTable2GUI
{
    protected ilObjLearningSequenceContentGUI $parent_gui;
    protected ilObjLearningSequenceGUI $container_gui;
    protected string $cmd;
    protected ilAccess $access;
    protected ilAdvancedSelectionListGUI $advanced_selection_list_gui;
    protected LSItemOnlineStatus $ls_item_online_status;

    public function __construct(
        ilObjLearningSequenceContentGUI $parent_gui,
        ilObjLearningSequenceGUI $container_gui,
        string $cmd,
        ilCtrl $ctrl,
        ilLanguage $lng,
        ilAccess $access,
        ilAdvancedSelectionListGUI $advanced_selection_list_gui,
        LSItemOnlineStatus $ls_item_online_status
    ) {
        parent::__construct($parent_gui, $cmd);

        $this->parent_gui = $parent_gui;
        $this->container_gui = $container_gui;
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

        $this->setLimit(9999);
    }

    protected function fillRow(array $a_set): void
    {
        /** @var LSItem $a_set */
        $a_set = $a_set[0];

        $ni = new ilNumberInputGUI(
            "",
            $this->parent_gui->getFieldName($this->parent_gui::FIELD_ORDER, $a_set->getRefId())
        );
        $ni->setSize(3);
        $ni->setValue((string) (($a_set->getOrderNumber() + 1) * 10));

        if ($this->ls_item_online_status->hasOnlineStatus($a_set->getRefId())) {
            $cb = new ilCheckboxInputGUI(
                "",
                $this->parent_gui->getFieldName($this->parent_gui::FIELD_ONLINE, $a_set->getRefId())
            );
            $cb->setChecked($a_set->isOnline());
        } else {
            $cb = new ilCheckboxInputGUI("", "");
            $cb->setChecked(true);
            $cb->setDisabled(true);
        }
        $this->tpl->setVariable("ONLINE", $cb->render());

        $si = new ilSelectInputGUI(
            "",
            $this->parent_gui->getFieldName($this->parent_gui::FIELD_POSTCONDITION_TYPE, $a_set->getRefId())
        );
        $options = $this->parent_gui->getPossiblePostConditionsForType($a_set->getType());

        $si->setOptions($options);
        $si->setValue($a_set->getPostCondition()->getConditionOperator());

        $action_items = $this->getActionMenuItems($a_set->getRefId(), $a_set->getType());
        $obj_link = $this->getEditLink($a_set->getRefId(), $a_set->getType(), $action_items);

        $title = $a_set->getTitle();
        $title = sprintf(
            '<a href="%s">%s</a>',
            $obj_link,
            $title
        );

        $this->tpl->setVariable("ID", $a_set->getRefId());
        $this->tpl->setVariable("IMAGE", $a_set->getIconPath());
        $this->tpl->setVariable("ORDER", $ni->render());
        $this->tpl->setVariable("TITLE", $title);
        $this->tpl->setVariable("POST_CONDITIONS", $si->render());
        $this->tpl->setVariable("ACTIONS", $this->getItemActionsMenu($a_set->getRefId(), $a_set->getType()));
        $this->tpl->setVariable("TYPE", $a_set->getType());
    }

    protected function getItemActionsMenu(int $ref_id, string $type): string
    {
        $item_obj_id = $this->getObjIdFor($ref_id);
        $item_list_gui = $this->getListGuiFor($type);
        $item_list_gui->initItem($ref_id, $item_obj_id, $type);

        $item_list_gui->enableCut(true);
        $item_list_gui->enableDelete(true);
        $item_list_gui->enableLink(true);
        $item_list_gui->enableTimings(false);

        $item_list_gui->setContainerObject($this->container_gui);

        return $item_list_gui->getCommandsHTML();
    }

    /**
     * @return	array	array of command arrays including
     *					"permission" => permission name
     *					"cmd" => command
     *					"link" => command link url
     *					"frame" => command link frame
     *					"lang_var" => language variable of command
     *					"granted" => true/false: command granted or not
     *					"access_info" => access info object (to do: implementation)
     */
    protected function getActionMenuItems(int $ref_id, string $type): array
    {
        $item_obj_id = $this->getObjIdFor($ref_id);
        $item_list_gui = $this->getListGuiFor($type);
        $item_list_gui->initItem($ref_id, $item_obj_id, $type);
        return $item_list_gui->getCommands();
    }

    protected function getEditLink(int $ref_id, string $type, array $action_items): ?string
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
            fn ($action_item) => $action_item['cmd'] === $prop_for_type
        );

        if ($props !== []) {
            return array_shift($props)['link'];
        }
        return null;
    }

    protected function getObjIdFor(int $ref_id): int
    {
        return ilObject::_lookupObjId($ref_id);
    }

    protected function getListGuiFor(string $type): ilObjectListGUI
    {
        return ilObjectListGUIFactory::_getListGUIByType($type);
    }

    protected function getStdLink(int $ref_id, string $type): string
    {
        return ilLink::_getLink($ref_id, $type);
    }
}
