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
     * @param string $target_class
     * @param string $target_cmd
     * @return void
     * @throws ilCtrlException
     */
    public function addTableSwitcherToToolbar(array $tables, string $target_class, string $target_cmd): void
    {
        $links = [];

        $this->ctrl->clearParameterByClass(ilObjDataCollectionGUI::class, "tableview_id");
        $this->ctrl->clearParameterByClass($target_class, "tableview_id");

        foreach ($tables as $table) {
            $this->ctrl->setParameterByClass($target_class, "table_id", $table->getId());
            $links[] = $this->ui_factory->link()->standard($table->getTitle(), $this->ctrl->getLinkTargetByClass($target_class, $target_cmd));
        }
        $this->ctrl->clearParameterByClass($target_class, "table_id");

        $this->addSwitcherToToolbar($links, $this->lng->txt('dcl_switch_table'));
    }

    /**
     * @param ilDclTableView[]  $views
     * @param int    $table_id
     * @param string $target_class
     * @param string $target_cmd
     * @return void
     * @throws ilCtrlException
     */
    public function addViewSwitcherToToolbar(array $views, int $table_id, string $target_class, string $target_cmd): void
    {
        $links = [];
        $this->ctrl->setParameterByClass($target_class, "table_id", $table_id);
        foreach ($views as $view) {
            $this->ctrl->setParameterByClass($target_class, "tableview_id", $view->getId());
            $links[] = $this->ui_factory->link()->standard($view->getTitle(), $this->ctrl->getLinkTargetByClass($target_class, $target_cmd));
        }
        $this->addSwitcherToToolbar($links, $this->lng->txt('dcl_switch_view'));
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
