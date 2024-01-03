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
    protected \ILIAS\DI\UIServices $ui;
    protected array $modals = [];

    public function __construct(object $a_parent_obj, string $a_parent_cmd = '')
    {
        global $DIC;
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->ui = $DIC->ui();
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
        $this->setRowTemplate('tpl.sys_styles_row.html', 'components/ILIAS/Style/System');
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
        $this->setRowTemplate('tpl.sys_styles_row_with_actions.html', 'components/ILIAS/Style/System');

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
                    'style_id' => 'other',
                    'skin_name' => 'other',
                    'style_name' => 'other',
                    'users' => $users_missing_styles,
                    'version' => '-',
                    'substyle_of' => ''
                ];
        }

        $this->setData($all_styles);
    }

    /**
     * @param array $a_set
     * @noinspection PhpIfWithCommonPartsInspection
     */
    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('STYLE_NAME', $a_set['style_name']);
        $this->tpl->setVariable('SKIN_NAME', $a_set['skin_name']);
        $is_substyle = isset($a_set['substyle_of']) && $a_set['substyle_of'] != '';

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

            $listing = $this->ui->factory()->listing()->unordered($categories);
            $this->tpl->setVariable(
                'CATEGORIES',
                $this->lng->txt('local') . $this->ui->renderer()->render($listing)
            );
        } else {
            $this->tpl->setVariable('SUB_STYLE_OF');
            $this->tpl->setVariable('CATEGORIES', $this->lng->txt('global'));
        }

        $this->tpl->setVariable('VERSION', $a_set['version']);

        if ($this->isWithActions()) {
            $action_items = [];

            /** @noinspection PhpIfWithCommonPartsInspection */

            if ($this->isReadDocumentation() && $a_set['skin_id'] != 'other') {
                $this->ctrl->setParameterByClass(ilSystemStyleDocumentationGUI::class, 'skin_id', $a_set['skin_id']);
                $this->ctrl->setParameterByClass(ilSystemStyleDocumentationGUI::class, 'style_id', $a_set['style_id']);
                $action_items[] = $this->ui->factory()->link()->standard(
                    $this->lng->txt('open_documentation'),
                    $this->ctrl->getLinkTargetByClass('ilSystemStyleDocumentationGUI', 'entries')
                );
            }

            if ($this->isManagementEnabled() && $a_set['skin_id'] != 'other') {
                $this->ctrl->setParameterByClass(ilSystemStyleConfigGUI::class, 'skin_id', $a_set['skin_id']);
                $this->ctrl->setParameterByClass(ilSystemStyleConfigGUI::class, 'style_id', $a_set['style_id']);

                $this->ctrl->setParameterByClass(ilSystemStyleConfigGUI::class, 'skin_id', $a_set['skin_id']);
                $this->ctrl->setParameterByClass(ilSystemStyleConfigGUI::class, 'style_id', $a_set['style_id']);

                $config = new ilSystemStyleConfig();
                if ($a_set['skin_id'] != $config->getDefaultSkinId()) {
                    $action_items = $this->addManagementActionsToList($action_items);
                    $this->addMultiActions($a_set['id']);
                }

                if (!$is_substyle && $a_set['skin_id'] != 'default') {
                    $action_items[] = $this->ui->factory()->link()->standard(
                        $this->lng->txt('export'),
                        $this->ctrl->getLinkTargetByClass(ilSystemStyleOverviewGUI::class, 'export')
                    );
                }
            }

            if (!$is_substyle) {
                $this->ctrl->setParameterByClass(ilSystemStyleOverviewGUI::class, 'old_skin_id', $a_set['skin_id']);
                $this->ctrl->setParameterByClass(ilSystemStyleOverviewGUI::class, 'old_style_id', $a_set['style_id']);

                $assignment_modal = $this->parent_obj->getAssignmentCreationModal($a_set['style_name']);

                if($assignment_modal) {
                    $this->modals[] = $assignment_modal;

                    $action_items[] = $this->ui->factory()->button()->shy(
                        $this->lng->txt('change_assignment'),
                        "#"
                    )->withOnClick($assignment_modal->getShowSignal());
                }
            }

            $this->tpl->setCurrentBlock('actions');
            $action_dropdown = $this->ui->factory()->dropdown()->standard($action_items)->withLabel(
                $this->lng->txt('actions')
            );
            $this->tpl->setVariable('ACTIONS', $this->ui->renderer()->render($action_dropdown));
            $this->tpl->parseCurrentBlock();
        }
    }

    public function getModalsHtml()
    {
        return $this->ui->renderer()->render($this->modals);
    }

    protected function addManagementActionsToList(array $action_items): array
    {
        $action_items[] = $this->ui->factory()->link()->standard(
            $this->lng->txt('edit'),
            $this->ctrl->getLinkTargetByClass('ilsystemstyleconfiggui')
        );
        $action_items[] = $this->ui->factory()->link()->standard(
            $this->lng->txt('delete'),
            $this->ctrl->getLinkTargetByClass('ilSystemStyleOverviewGUI', 'deleteStyle')
        );
        return $action_items;
    }

    protected function addMultiActions($id)
    {
        $this->tpl->setCurrentBlock('multi_actions');
        $this->tpl->setVariable('MULTI_ACTIONS_ID', $id);
        $this->tpl->parseCurrentBlock();
    }

    public function isWithActions(): bool
    {
        return $this->with_actions;
    }

    public function setWithActions(bool $with_actions): void
    {
        $this->with_actions = $with_actions;
    }

    public function isManagementEnabled(): bool
    {
        return $this->management_enabled;
    }

    public function setManagementEnabled(bool $management_enabled)
    {
        $this->management_enabled = $management_enabled;
    }

    public function isReadDocumentation(): bool
    {
        return $this->read_documentation;
    }

    public function setReadDocumentation(bool $read_documentation)
    {
        $this->read_documentation = $read_documentation;
    }
}
