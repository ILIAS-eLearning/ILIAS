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

use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;

class ilDashboardSortationTableGUI extends ilTable2GUI
{
    private Renderer $uiRenderer;
    private Factory $uiFactory;
    private ilPDSelectedItemsBlockViewSettings $viewSettings;
    private ilDashboardSidePanelSettingsRepository $side_panel_settings;

    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;
        $this->uiFactory = $DIC->ui()->factory();
        $this->uiRenderer = $DIC->ui()->renderer();
        $this->viewSettings = new ilPDSelectedItemsBlockViewSettings($DIC->user());
        $this->side_panel_settings = new ilDashboardSidePanelSettingsRepository();
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->lng->loadLanguageModule('mme');
        $this->initColumns();
        $this->setFormAction($this->ctrl->getFormAction($this->parent_obj));
        $this->addCommandButton('saveSettings', $this->lng->txt('save'));
        $this->setRowTemplate(
            'tpl.dashboard_sortation_row.html',
            'components/ILIAS/Dashboard'
        );
        $this->setEnableNumInfo(false);
        $this->initData();
    }

    public function initColumns(): void
    {
        $this->addColumn($this->lng->txt('topitem_position'), '', '30px');
        $this->addColumn($this->lng->txt('topitem_block'));
        $this->addColumn($this->lng->txt('topitem_active'));
    }

    public function initData(): void
    {
        $data[] = [
            'position' => $this->uiRenderer->render(
                $this->uiFactory->divider()->horizontal()->withLabel($this->lng->txt('dash_main_panel'))
            ),
            'title' => '',
            'active_checkbox' => ''
        ];

        $position = 0;
        foreach ($this->viewSettings->getViewPositions() as $presentation_view) {
            $presentation_cb = new ilCheckboxInputGUI('', 'main_panel[enable][' . $presentation_view . ']');
            $presentation_cb->setChecked($this->viewSettings->isViewEnabled($presentation_view));
            $presentation_cb->setValue('1');
            $presentation_cb->setDisabled(
                $presentation_view === ilPDSelectedItemsBlockConstants::VIEW_RECOMMENDED_CONTENT
            );

            $position_input = new ilNumberInputGUI('', 'main_panel[position][' . $presentation_view . ']');
            $position_input->setSize(3);
            $position_input->setValue((string) (++$position * 10));

            $data[] = [
                'position' => $position_input->render(),
                'title' => $this->lng->txt('dash_enable_' . $this->viewSettings->getViewName($presentation_view)),
                'active_checkbox' => $presentation_cb->render()
            ];
        }

        $data[] = [
            'position' => $this->uiRenderer->render(
                $this->uiFactory->divider()->horizontal()->withLabel($this->lng->txt('dash_side_panel'))
            ),
            'title' => '',
            'active_checkbox' => ''
        ];

        $sp_fields = [];
        $position = 0;
        foreach ($this->side_panel_settings->getPositions() as $mod) {
            $side_panel_module_cb = new ilCheckboxInputGUI('', 'side_panel[enable][' . $mod . ']');
            $side_panel_module_cb->setChecked($this->side_panel_settings->isEnabled($mod));

            $position_input = new ilNumberInputGUI('', 'side_panel[position][' . $mod . ']');
            $position_input->setSize(3);
            $position_input->setValue((string) (++$position * 10));

            $data[] = [
                'position' => $position_input->render(),
                'title' => $this->lng->txt('dash_enable_' . $mod),
                'active_checkbox' => $side_panel_module_cb->render()
            ];
        }
        $this->setData($data);
    }
}
