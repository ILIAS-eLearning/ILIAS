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

class ilDclSwitcher
{
    protected ilToolbarGUI $toolbar;
    protected \ILIAS\UI\Factory $ui_factory;
    protected ilLanguage $lng;
    protected ilCtrl $ctrl;

    public function __construct(ilToolbarGUI $toolbar, \ILIAS\UI\Factory $ui_factory, ilCtrl $ctrl, ilLanguage $lng)
    {
        $this->toolbar = $toolbar;
        $this->ui_factory = $ui_factory;
        $this->ctrl = $ctrl;
        $this->lng = $lng;
    }

    /**
     * @param ilDclTable[]  $tables
     */
    public function addTableSwitcherToToolbar(array $tables, string $target_class, string $target_cmd, int $table_id = 0): void
    {
        $this->ctrl->clearParameterByClass($target_class, 'tableview_id');
        $links = [];
        $current = '';
        foreach ($tables as $table) {
            $title = $table->getTitle();
            if ($table->getId() == $table_id) {
                $current = $title;
            }
            $title = ($current === $title ? '✓ ' : '⠀ ') . $title;
            $this->ctrl->setParameterByClass($target_class, 'table_id', $table->getId());
            $links[] = $this->ui_factory->link()->standard($title, $this->ctrl->getLinkTargetByClass($target_class, $target_cmd));
        }
        $this->ctrl->clearParameterByClass($target_class, 'table_id');

        $this->addSwitcherToToolbar($links, $this->lng->txt('dcl_table') . ': ' . $current);
    }

    /**
     * @param ilDclTableView[]  $views
     */
    public function addViewSwitcherToToolbar(array $views, int $table_id, string $target_class, string $target_cmd, int $tableview_id = 0): void
    {
        $this->ctrl->setParameterByClass($target_class, 'table_id', $table_id);
        $links = [];
        $current = '';
        foreach ($views as $view) {
            $title = $view->getTitle();
            if ($view->getId() == $tableview_id) {
                $current = $title;
            }
            $title = ($current === $title ? '✓⠀' : '⠀⠀') . $title;
            $this->ctrl->setParameterByClass($target_class, 'tableview_id', $view->getId());
            $links[] = $this->ui_factory->link()->standard($title, $this->ctrl->getLinkTargetByClass($target_class, $target_cmd));
        }
        $this->ctrl->clearParameterByClass($target_class, 'tableview_id');
        $this->ctrl->clearParameterByClass($target_class, 'table_id');

        $this->addSwitcherToToolbar($links, $this->lng->txt('dcl_tableview') . ': ' . $current);
    }

    /**
     * @param \ILIAS\UI\Component\Link\Standard[] $links
     * @param string                            $label
     * @return void
     */
    protected function addSwitcherToToolbar(array $links, string $label): void
    {
        if (count($links) > 1) {
            $this->toolbar->addComponent(
                $this->ui_factory->dropdown()->standard($links)->withLabel($label)
            );
        }
    }
}
