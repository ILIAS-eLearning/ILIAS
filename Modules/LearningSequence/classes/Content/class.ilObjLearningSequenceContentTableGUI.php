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

class ilObjLearningSequenceContentTableGUI extends ilTable2GUI
{
    protected bool $lp_globally_enabled;

    public function __construct(
        protected ilObjLearningSequenceContentGUI $parent_gui,
        protected ilObjLearningSequenceGUI $container_gui,
        protected string $cmd,
        ilCtrl $ctrl, //table2gui
        ilLanguage $lng, //tablegui
        protected ilAccess $access,
        protected ILIAS\UI\Factory $ui_factory,
        protected ILIAS\UI\Renderer $ui_renderer,
        protected LSItemOnlineStatus $ls_item_online_status,
        protected string $alert_icon
    ) {
        parent::__construct($parent_gui, $cmd);

        $this->lp_globally_enabled = ilObjUserTracking::_enabledLearningProgress();

        $this->lng->loadLanguageModule('trac');

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
        if ($this->lp_globally_enabled) {
            $this->addColumn($this->lng->txt("table_lp_settings"));
        }
        $this->addColumn($this->lng->txt("table_actions"));

        $this->setLimit(9999);
    }

    protected function fillRow(array $a_set): void
    {
        /** @var LSItem $ls_item */
        $ls_item = $a_set[0];

        $ni = new ilNumberInputGUI(
            "",
            $this->parent_gui->getFieldName($this->parent_gui::FIELD_ORDER, $ls_item->getRefId())
        );
        $ni->setSize(3);
        $ni->setValue((string) (($ls_item->getOrderNumber() + 1) * 10));

        $cb = new ilCheckboxInputGUI(
            "",
            $this->parent_gui->getFieldName($this->parent_gui::FIELD_ONLINE, $ls_item->getRefId())
        );
        $cb->setChecked($ls_item->isOnline());
        if (!$this->ls_item_online_status->hasChangeableOnlineStatus($ls_item->getRefId())) {
            $cb->setDisabled(true);
        }
        $this->tpl->setVariable("ONLINE", $cb->render());

        $si = new ilSelectInputGUI(
            "",
            $this->parent_gui->getFieldName($this->parent_gui::FIELD_POSTCONDITION_TYPE, $ls_item->getRefId())
        );
        $options = $this->parent_gui->getPossiblePostConditionsForType($ls_item->getType());

        $si->setOptions($options);
        $si->setValue($ls_item->getPostCondition()->getConditionOperator());

        $action_items = $this->getActionMenuItems($ls_item->getRefId(), $ls_item->getType());
        $obj_link = $this->getEditLink($ls_item->getRefId(), $ls_item->getType(), $action_items);

        $title = $ls_item->getTitle();
        $title = sprintf(
            '<a href="%s">%s</a>',
            $obj_link,
            $title
        );

        $this->tpl->setVariable("ID", $ls_item->getRefId());

        $this->tpl->setVariable(
            "IMAGE",
            $this->ui_renderer->render(
                $this->ui_factory->symbol()->icon()->custom(
                    $ls_item->getIconPath(),
                    $ls_item->getType()
                )->withSize('medium')
            )
        );

        $this->tpl->setVariable("ORDER", $ni->render());
        $this->tpl->setVariable("TITLE", $title);
        $this->tpl->setVariable("POST_CONDITIONS", $si->render());

        if ($this->lp_globally_enabled) {
            $lp_setting = $ls_item->getPostCondition()->getConditionOperator() === ilLSPostCondition::OPERATOR_LP ?
                $this->getLPSettingsRepresentation($ls_item) : $this->lng->txt("lp_not_relevant_post_cond");
            $this->tpl->setVariable("LP_SETTINGS", $lp_setting);
        }

        $this->tpl->setVariable("ACTIONS", $this->getItemActionsMenu($ls_item->getRefId(), $ls_item->getType()));
        $this->tpl->setVariable("TYPE", $ls_item->getType());
    }

    protected function getLPSettingsRepresentation(LSItem $ls_item): string
    {
        $mode = $ls_item->getLPMode();
        $setting = ilLPObjSettings::_mode2Text($mode);
        if (
            $ls_item->getPostCondition()->getConditionOperator() === ilLSPostCondition::OPERATOR_LP
            && $mode === 0
        ) {
            $setting = $this->alert_icon . $setting;
        }
        return $setting;
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
                $prop_for_type = 'ilObjTestSettingsMainGUI::showForm';
                break;

            case $this->ls_item_online_status::S_IND_ASSESSMENT:
            default:
                return $this->getStdLink($ref_id, $type);
        }

        $props = array_filter(
            $action_items,
            fn($action_item) => $action_item['cmd'] === $prop_for_type
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
