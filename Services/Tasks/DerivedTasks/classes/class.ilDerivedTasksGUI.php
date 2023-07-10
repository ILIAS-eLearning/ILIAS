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

use ILIAS\DI\Container;
use ILIAS\DI\UIServices;

/**
 * Derived tasks list
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilDerivedTasksGUI implements ilCtrlBaseClassInterface
{
    protected ?Container $dic;
    protected ilCtrl $ctrl;
    protected ilGlobalTemplateInterface $main_tpl;
    protected ilTaskService $task;
    protected ilObjUser $user;
    protected UIServices $ui;
    protected ilLanguage $lng;
    protected ilHelpGUI $help;

    /**
     * Constructor
     * @param Container|null $dic
     */
    public function __construct(Container $di_container = null)
    {
        global $DIC;

        if (is_null($di_container)) {
            $di_container = $DIC;
        }
        $this->dic = $di_container;
        $this->ctrl = $DIC->ctrl();
        $this->main_tpl = $DIC->ui()->mainTemplate();
        $this->task = $DIC->task();
        $this->user = $DIC->user();
        $this->ui = $DIC->ui();
        $this->lng = $DIC->language();
        $this->help = $DIC->help();

        $this->help->setScreenIdComponent('task');
        $this->lng->loadLanguageModule("task");
    }

    /**
     * Execute command
     */
    public function executeCommand(): void
    {
        $ctrl = $this->ctrl;
        $main_tpl = $this->main_tpl;
        $main_tpl->loadStandardTemplate();

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("show");

        if ($cmd == "show") {
            $this->$cmd();
        }
        $main_tpl->printToStdout();
    }

    /**
     * Show list of tasks
     */
    protected function show(): void
    {
        $ui = $this->ui;
        $lng = $this->lng;
        $main_tpl = $this->main_tpl;

        $main_tpl->setTitle($lng->txt("task_derived_tasks"));
        $this->help->setScreenId('derived_tasks');

        $f = $ui->factory();
        $renderer = $ui->renderer();

        $collector = $this->task->derived()->factory()->collector();

        $entries = $collector->getEntries($this->user->getId());

        $list_items_with_deadline = [];
        $list_items_without_deadline = [];

        // item groups from tasks
        foreach ($entries as $i) {
            $props = [];

            $title = $i->getTitle();
            $link = '';

            if ($i->getRefId() > 0) {
                $obj_id = ilObject::_lookupObjId($i->getRefId());
                $obj_type = ilObject::_lookupType($obj_id);
                $props[$lng->txt("obj_" . $obj_type)] = ilObject::_lookupTitle($obj_id);

                $link = ilLink::_getStaticLink($i->getRefId());
            }

            if ($i->getWspId() > 0) {
                $wst = new ilWorkspaceTree($this->user->getId());
                $obj_id = $wst->lookupObjectId($i->getWspId());
                $obj_type = ilObject::_lookupType($obj_id);
                $props[$lng->txt("obj_" . $obj_type)] = ilObject::_lookupTitle($obj_id);
            }

            if (strlen($i->getUrl()) > 0) {
                $link = $i->getUrl();
            }

            if (strlen($link) > 0) {
                $title = $f->button()->shy($title, $link);
            }

            if ($i->getStartingTime() > 0) {
                $start = new ilDateTime($i->getStartingTime(), IL_CAL_UNIX);
                $props[$lng->txt("task_start")] = ilDatePresentation::formatDate($start);
            }
            if ($i->getDeadline() > 0) {
                $end = new ilDateTime($i->getDeadline(), IL_CAL_UNIX);
                $props[$lng->txt("task_deadline")] = ilDatePresentation::formatDate($end);
            }
            $item = $f->item()->standard($title)->withProperties($props);
            if ($i->getDeadline() > 0) {
                $list_items_with_deadline[] = $item;
            } else {
                $list_items_without_deadline[] = $item;
            }
        }

        // output list panel or info message
        if (count($list_items_with_deadline) > 0 || count($list_items_without_deadline) > 0) {
            $panels = [];

            if (count($list_items_with_deadline) > 0) {
                $panels[] = $f->panel()->listing()->standard(
                    $lng->txt("task_tasks_with_deadline"),
                    [$f->item()->group("", $list_items_with_deadline)]
                );
            }
            if (count($list_items_without_deadline) > 0) {
                $panels[] = $f->panel()->listing()->standard(
                    $lng->txt("task_tasks_without_deadline"),
                    [$f->item()->group("", $list_items_without_deadline)]
                );
            }


            $main_tpl->setContent($renderer->render($panels));
        } else {
            $this->main_tpl->setOnScreenMessage('info', $lng->txt("task_no_tasks"));
        }
    }
}
