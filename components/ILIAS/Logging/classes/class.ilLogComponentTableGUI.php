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
 * Component logger with individual log levels by component id
 */
class ilLogComponentTableGUI extends ilTable2GUI
{
    protected ilComponentRepository $component_repo;
    protected ?ilLoggingDBSettings $settings = null;
    protected bool $editable = true;

    public function __construct(object $a_parent_obj, string $a_parent_cmd = "")
    {
        global $DIC;
        $this->component_repo = $DIC["component.repository"];

        $this->setId('il_log_component');
        parent::__construct($a_parent_obj, $a_parent_cmd);
    }

    /**
     * Set ediatable (write permission granted)
     */
    public function setEditable(bool $a_status): void
    {
        $this->editable = $a_status;
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
        $this->settings = ilLoggingDBSettings::getInstance();

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
     * Get settings
     */
    public function getSettings(): ilLoggingDBSettings
    {
        return $this->settings;
    }

    /**
     * Parse table
     */
    public function parse(): void
    {
        $components = ilLogComponentLevels::getInstance()->getLogComponents();
        $rows = array();
        foreach ($components as $component) {
            $row['id'] = $component->getComponentId();
            if ($component->getComponentId() == 'log_root') {
                $row['component'] = 'Root';
                $row['component_sortable'] = '_' . $row['component'];
            } else {
                if ($this->component_repo->hasComponentId(
                    $component->getComponentId()
                )) {
                    $row['component'] = $this->component_repo->getComponentById(
                        $component->getComponentId()
                    )->getName();
                } else {
                    $row['component'] = "Unknown (" . $component->getComponentId() . ")";
                }
                $row['component_sortable'] = $row['component'];
            }
            $row['level'] = (int) $component->getLevel();
            $rows[] = $row;
        }
        $this->setMaxCount(count($rows));
        $this->setData($rows);
    }

    /**
     * @inheritDoc
     */
    protected function fillRow(array $a_set): void
    {
        $this->tpl->setVariable('CNAME', $a_set['component']);
        if ($a_set['id'] == 'log_root') {
            $this->tpl->setVariable('TXT_DESC', $this->lng->txt('log_component_root_desc'));
        }

        $default_option_value = ilLoggingDBSettings::getInstance()->getLevel();
        $array_options = ilLogLevel::getLevelOptions();
        $default_option = array( 0 => $this->lng->txt('default') . " (" . $array_options[$default_option_value] . ")");
        $array_options = $default_option + $array_options;

        $levels = new ilSelectInputGUI('', 'level[' . $a_set['id'] . ']');
        $levels->setOptions($array_options);
        $levels->setValue($a_set['level']);
        $this->tpl->setVariable('C_SELECT_LEVEL', $levels->render());
    }
}
