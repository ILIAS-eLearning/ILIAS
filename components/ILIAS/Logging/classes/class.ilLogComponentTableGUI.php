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

use ILIAS\Logging\Config\Basic\ConfigInterface as BasicConfig;
use ILIAS\Logging\Config\ByComponent\RepositoryInterface as ComponentConfigRepo;
use ILIAS\Logging\ILIASLogLevel;

/**
 * Component logger with individual log levels by component id
 */
class ilLogComponentTableGUI extends ilTable2GUI
{
    public function __construct(
        protected bool $editable,
        protected ilComponentRepository $component_repo,
        protected BasicConfig $basic_log_config,
        protected ComponentConfigRepo $component_config_repo,
        object $a_parent_obj,
        string $a_parent_cmd = ""
    ) {
        $this->setId('il_log_component');
        parent::__construct($a_parent_obj, $a_parent_cmd);
    }

    /**
     * Check if ediatable (write permission granted)
     */
    public function isEditable(): bool
    {
        return $this->editable;
    }

    /**
     * init table
     */
    public function init(): void
    {
        $this->setFormAction($this->ctrl->getFormAction($this->getParentObject()));

        $this->setRowTemplate('tpl.log_component_row.html', 'components/ILIAS/Logging');
        $this->addColumn($this->lng->txt('log_component_col_component'), 'component_sortable');
        $this->addColumn($this->lng->txt('log_component_col_level'), 'level');

        $this->setDefaultOrderField('component_sortable');

        if ($this->isEditable()) {
            $this->addCommandButton('saveComponentLevels', $this->lng->txt('save'));
            $this->addCommandButton('resetComponentLevels', $this->lng->txt('log_component_btn_reset'));
        }

        $this->setShowRowsSelector(false);
        $this->setLimit(500);
    }

    /**
     * Parse table
     */
    public function parse(): void
    {
        $levels_by_component = $this->component_config_repo->getAllLevelsForComponents();

        $rows = [];
        foreach ($this->component_repo->getComponents() as $id => $component) {
            $row = [];
            $row['id'] = $id;
            $row['component'] = $row['component_sortable'] = $component->getName();
            $row['level'] = $levels_by_component[$id]?->value ?? 0;
            unset($levels_by_component[$id]);
            $rows[] = $row;
        }
        foreach ($this->component_repo->getPlugins() as $id => $plugin) {
            $row = [];
            $row['id'] = $id;
            $row['component'] = $row['component_sortable'] = $plugin->getName();
            $row['level'] = $levels_by_component[$id]?->value ?? 0;
            unset($levels_by_component[$id]);
            $rows[] = $row;
        }
        foreach ($levels_by_component as $id => $level) {
            $row = [];
            $row['id'] = $id;
            $row['component'] = $row['component_sortable'] = sprintf(
                $this->lng->txt('log_component_unknown'),
                $id
            );
            $row['level'] = $level->value;
            unset($levels_by_component[$id]);
            $rows[] = $row;
        }
        $this->setMaxCount(count($rows));
        $this->setData($rows);
    }

    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('CNAME', $a_set['component']);

        $default_label = sprintf(
            $this->lng->txt('log_level_default'),
            $this->presentableLogLevel($this->basic_log_config->defaultLevel())
        );
        $options = [0 => $default_label];
        foreach (ILIASLogLevel::cases() as $level) {
            $options[$level->value] = $this->presentableLogLevel($level);
        }

        $levels = new ilSelectInputGUI('', 'level[' . $a_set['id'] . ']');
        $levels->setOptions($options);
        $levels->setValue($a_set['level']);
        $this->tpl->setVariable('C_SELECT_LEVEL', $levels->render());
    }

    protected function presentableLogLevel(ILIASLogLevel $level): string
    {
        return match ($level) {
            ILIASLogLevel::DEBUG => $this->lng->txt('log_level_debug'),
            ILIASLogLevel::INFO => $this->lng->txt('log_level_info'),
            ILIASLogLevel::NOTICE => $this->lng->txt('log_level_notice'),
            ILIASLogLevel::WARNING => $this->lng->txt('log_level_warning'),
            ILIASLogLevel::ERROR => $this->lng->txt('log_level_error'),
            ILIASLogLevel::CRITICAL => $this->lng->txt('log_level_critical'),
            ILIASLogLevel::ALERT => $this->lng->txt('log_level_alert'),
            ILIASLogLevel::EMERGENCY => $this->lng->txt('log_level_emergency'),
            ILIASLogLevel::OFF => $this->lng->txt('log_level_off')
        };
    }
}
