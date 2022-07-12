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

/**
 * TableGUI class for system styles
 */
class ilSystemStylesTableGUI extends ilTable2GUI
{
    protected bool $with_actions = false;
    protected bool $management_enabled = false;
    protected bool $read_documentation = true;

    public function __construct(object $a_parent_obj, string $a_parent_cmd = '')
    {
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->getStyles();

        $this->setLimit(9999);
        $this->setTitle($this->lng->txt('manage_system_styles'));
        $this->addColumn($this->lng->txt(''));
        $this->addColumn($this->lng->txt('style_name'), 'style_name');
        $this->addColumn($this->lng->txt('skin_name'), 'skin_name');
        $this->addColumn($this->lng->txt('sty_substyle_of'));
        $this->addColumn($this->lng->txt('scope'));
        $this->addColumn($this->lng->txt('default'));
        $this->addColumn($this->lng->txt('active'));
        $this->addColumn($this->lng->txt('users'), 'users');
        $this->addColumn($this->lng->txt('version'));
        $this->setRowTemplate('tpl.sys_styles_row.html', 'Services/Style/System');
        $this->setEnableHeader(true);
    }

    /**
     * @param           $management_enabled
     * @param bool|true $read_documentation
     */
    public function addActions($management_enabled, bool $read_documentation = true)
    {
        $this->setManagementEnabled($management_enabled);
        $this->setReadDocumentation($read_documentation);

        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject()));
        $this->addCommandButton('saveStyleSettings', $this->lng->txt('save'));
        $this->setRowTemplate('tpl.sys_styles_row_with_actions.html', 'Services/Style/System');

        if ($read_documentation || $management_enabled) {
            $this->setWithActions(true);

            $this->addColumn($this->lng->txt('actions'));
        }
        if ($management_enabled) {
            $this->addMultiCommand('deleteStyles', $this->lng->txt('delete'));
        }
    }

    /**
     *
     */
    public function getStyles()
    {
        // get all user assigned styles
        $all_user_styles = ilObjUser::_getAllUserAssignedStyles();

        // output 'other' row for all users, that are not assigned to
        // any existing style
        $users_missing_styles = 0;
        foreach ($all_user_styles as $skin_style_id) {
            $style_arr = explode(':', $skin_style_id);
            if (!ilStyleDefinition::styleExists($style_arr[1])) {
                $users_missing_styles += ilObjUser::_getNumberOfUsersForStyle($style_arr[0], $style_arr[1]);
            }
        }
        $all_styles = ilStyleDefinition::getAllSkinStyles();
        if ($users_missing_styles > 0) {
            $all_styles['other'] =
                [
                    'title' => $this->lng->txt('other'),
                    'id' => 'other',
                    'template_id' => '',
                    'skin_id' => 'other',
                    'style_id' => '',
                    'skin_name' => 'other',
                    'style_name' => '',
                    'users' => $users_missing_styles,
                    'version' => '-'
                ];
        }

        $this->setData($all_styles);
    }

    /**
     * @param array $a_set
     * @noinspection PhpIfWithCommonPartsInspection
     */
    protected function fillRow(array $a_set) : void
    {
        global $DIC;

        $this->tpl->setVariable('STYLE_NAME', $a_set['style_name']);
        $this->tpl->setVariable('SKIN_NAME', $a_set['skin_name']);
        $is_substyle = $a_set['substyle_of'] != '';

        if (!$is_substyle) {
            $this->tpl->setVariable('USERS', $a_set['users']);
        } else {
            $this->tpl->setVariable('USERS', '-');
        }

        if ($a_set['id'] != 'other') {
            $this->tpl->setCurrentBlock('default_input');

            if (!$is_substyle) {
                $this->tpl->setVariable('DEFAULT_ID', $a_set['id']);
                if (ilSystemStyleSettings::getCurrentDefaultSkin() == $a_set['skin_id'] &&
                    ilSystemStyleSettings::getCurrentDefaultStyle() == $a_set['style_id']
                ) {
                    $this->tpl->setVariable('CHECKED_DEFAULT', " checked='checked' ");
                } else {
                    $this->tpl->setVariable('CHECKED_DEFAULT');
                }
                $this->tpl->parseCurrentBlock();
            }

            $this->tpl->setCurrentBlock('active_input');
            $this->tpl->setVariable('ACTIVE_ID', $a_set['id']);

            if ($is_substyle) {
                $this->tpl->setVariable('DISABLED_ACTIVE', 'disabled');

                if (ilSystemStyleSettings::_lookupActivatedStyle($a_set['skin_id'], $a_set['substyle_of'])) {
                    $this->tpl->setVariable('CHECKED_ACTIVE', " checked='checked' ");
                } else {
                    $this->tpl->setVariable('CHECKED_ACTIVE');
                }
            } elseif (ilSystemStyleSettings::_lookupActivatedStyle($a_set['skin_id'], $a_set['style_id'])) {
                $this->tpl->setVariable('CHECKED_ACTIVE', " checked='checked' ");
            } else {
                $this->tpl->setVariable('CHECKED_ACTIVE');
            }

            $this->tpl->parseCurrentBlock();
        }

        if ($is_substyle) {
            $this->tpl->setVariable('SUB_STYLE_OF', $a_set['substyle_of_name']);

            $assignments = ilSystemStyleSettings::getSubStyleCategoryAssignments(
                $a_set['skin_id'],
                $a_set['substyle_of'],
                $a_set['style_id']
            );

            $categories = [];

            foreach ($assignments as $assignment) {
                $category_title = ilObject::_lookupTitle(ilObject::_lookupObjId((int) $assignment['ref_id']));
                if ($category_title) {
                    $categories[] = $category_title;
                }
            }

            $listing = $DIC->ui()->factory()->listing()->unordered($categories);
            $this->tpl->setVariable(
                'CATEGORIES',
                $this->lng->txt('local') . $DIC->ui()->renderer()->render($listing)
            );
        } else {
            $this->tpl->setVariable('SUB_STYLE_OF');
            $this->tpl->setVariable('CATEGORIES', $this->lng->txt('global'));
        }

        $this->tpl->setVariable('VERSION', $a_set['version']);

        if ($this->isWithActions()) {
            /** @noinspection PhpIfWithCommonPartsInspection */
            if ($a_set['skin_id'] == 'other') {
                $this->tpl->setCurrentBlock('actions');
                $this->tpl->setVariable('ACTIONS');
                $this->tpl->parseCurrentBlock();
            } else {
                $action_list = new ilAdvancedSelectionListGUI();
                $action_list->setId('id_action_list_' . $a_set['id']);
                $action_list->setListTitle($this->lng->txt('actions'));

                if ($this->isReadDocumentation()) {
                    $DIC->ctrl()->setParameterByClass('ilSystemStyleDocumentationGUI', 'skin_id', $a_set['skin_id']);
                    $DIC->ctrl()->setParameterByClass('ilSystemStyleDocumentationGUI', 'style_id', $a_set['style_id']);
                    $action_list->addItem(
                        $this->lng->txt('open_documentation'),
                        'documentation',
                        $this->ctrl->getLinkTargetByClass('ilSystemStyleDocumentationGUI', 'entries')
                    );
                }

                if ($this->isManagementEnabled()) {
                    $this->ctrl->setParameterByClass('ilSystemStyleSettingsGUI', 'skin_id', $a_set['skin_id']);
                    $this->ctrl->setParameterByClass('ilSystemStyleSettingsGUI', 'style_id', $a_set['style_id']);

                    $this->ctrl->setParameterByClass('ilSystemStyleOverviewGUI', 'skin_id', $a_set['skin_id']);
                    $this->ctrl->setParameterByClass('ilSystemStyleOverviewGUI', 'style_id', $a_set['style_id']);

                    $config = new ilSystemStyleConfig();
                    if ($a_set['skin_id'] != $config->getDefaultSkinId()) {
                        $this->addManagementActionsToList($action_list);
                        $this->addMultiActions($a_set['id']);
                    }
                    if (!$is_substyle && $a_set['skin_id'] != 'default') {
                        $action_list->addItem(
                            $this->lng->txt('export'),
                            'export',
                            $this->ctrl->getLinkTargetByClass('ilSystemStyleOverviewGUI', 'export')
                        );
                    }
                }

                $this->tpl->setCurrentBlock('actions');
                $this->tpl->setVariable('ACTIONS', $action_list->getHTML());
                $this->tpl->parseCurrentBlock();
            }
        }
    }

    protected function addManagementActionsToList(ilAdvancedSelectionListGUI $action_list)
    {
        $action_list->addItem(
            $this->lng->txt('edit'),
            'edit',
            $this->ctrl->getLinkTargetByClass('ilSystemStyleSettingsGUI')
        );
        $action_list->addItem(
            $this->lng->txt('delete'),
            'delete',
            $this->ctrl->getLinkTargetByClass('ilSystemStyleOverviewGUI', 'deleteStyle')
        );
    }

    protected function addMultiActions($id)
    {
        $this->tpl->setCurrentBlock('multi_actions');
        $this->tpl->setVariable('MULTI_ACTIONS_ID', $id);
        $this->tpl->parseCurrentBlock();
    }

    public function isWithActions() : bool
    {
        return $this->with_actions;
    }

    public function setWithActions(bool $with_actions) : void
    {
        $this->with_actions = $with_actions;
    }

    public function isManagementEnabled() : bool
    {
        return $this->management_enabled;
    }

    public function setManagementEnabled(bool $management_enabled)
    {
        $this->management_enabled = $management_enabled;
    }

    public function isReadDocumentation() : bool
    {
        return $this->read_documentation;
    }

    public function setReadDocumentation(bool $read_documentation)
    {
        $this->read_documentation = $read_documentation;
    }
}
