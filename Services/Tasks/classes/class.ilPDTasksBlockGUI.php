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

use ILIAS\UI\Component\Item\Item;

/**
 * BlockGUI class for Tasks on PD
 *
 * @author Alexander Killing <killing@leifos.de>
 *
 * @ilCtrl_IsCalledBy ilPDTasksBlockGUI: ilColumnGUI
 */
class ilPDTasksBlockGUI extends ilBlockGUI
{
    public static string $block_type = "pdtasks";

    protected array $tasks = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->user = $DIC->user();
        $lng = $DIC->language();

        parent::__construct();

        $this->setLimit(5);
        $lng->loadLanguageModule("task");
        $this->setTitle($lng->txt("task_derived_tasks"));

        $this->setPresentation(self::PRES_SEC_LIST);
    }

    /**
     * @inheritdoc
     */
    public function getBlockType() : string
    {
        return self::$block_type;
    }

    /**
     * @inheritdoc
     */
    protected function isRepositoryObject() : bool
    {
        return false;
    }

    /**
     * Get Screen Mode for current command.
     */
    public static function getScreenMode() : string
    {
        return IL_SCREEN_SIDE;
    }

    /**
     * execute command
     */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;

        $cmd = $ilCtrl->getCmd("getHTML");

        return $this->$cmd();
    }

    /**
     * Fill data section
     */
    public function fillDataSection() : void
    {
        global $DIC;
        $collector = $DIC->task()->derived()->factory()->collector();

        $this->tasks = $collector->getEntries($this->user->getId());

        if (count($this->tasks) > 0) {
            $this->setRowTemplate("tpl.pd_tasks.html", "Services/Tasks");
            $this->getListRowData();
            parent::fillDataSection();
        } else {
            $this->setEnableNumInfo(false);
            $this->setDataSection($this->getOverview());
        }
    }


    /**
     * Get list data.
     */
    public function getListRowData() : void
    {
        $data = [];

        /** @var ilDerivedTask $task */
        foreach ($this->tasks as $task) {
            $data[] = array(
                "title" => $task->getTitle(),
                "url" => $task->getUrl(),
                "ref_id" => $task->getRefId(),
                "wsp_id" => $task->getWspId(),
                "deadline" => $task->getDeadline(),
                "starting_time" => $task->getStartingTime()
            );
        }

        $this->setData($data);
    }

    /**
     * get flat list for personal desktop
     */
    public function fillRow(array $a_set) : void
    {
        global $DIC;

        $factory = $DIC->ui()->factory();
        $renderer = $DIC->ui()->renderer();
        $lng = $this->lng;

        $info_screen = new ilInfoScreenGUI($this);
        $info_screen->setFormAction("#");
        $info_screen->addSection($lng->txt(""));
        //$toolbar = new ilToolbarGUI();

        $info_screen->addProperty(
            $lng->txt("task_task"),
            $a_set["title"]
        );

        if ($a_set["ref_id"] > 0) {
            $obj_id = ilObject::_lookupObjId($a_set["ref_id"]);
            $obj_type = ilObject::_lookupType($obj_id);
            
            $url = 0 === $a_set['url'] ? ilLink::_getStaticLink($a_set["ref_id"]) : $a_set['url'];
            $link = $factory->button()->shy(ilObject::_lookupTitle($obj_id), $url);

            $info_screen->addProperty(
                $lng->txt("obj_" . $obj_type),
                $renderer->render($link)
            );
        }

        if ($a_set["wsp_id"] > 0) {
            $wst = new ilWorkspaceTree($this->user->getId());
            $obj_id = $wst->lookupObjectId($a_set["wsp_id"]);
            $obj_type = ilObject::_lookupType($obj_id);

            $url = 0 === $a_set['url'] ? ilLink::_getStaticLink($a_set["wsp_id"]) : $a_set['url'];
            $link = $factory->button()->shy(ilObject::_lookupTitle($obj_id), $url);

            $info_screen->addProperty(
                $lng->txt("obj_" . $obj_type),
                $renderer->render($link)
            );
        }

        if ($a_set["starting_time"] > 0) {
            $start = new ilDateTime($a_set["starting_time"], IL_CAL_UNIX);
            $info_screen->addProperty(
                $lng->txt("task_start"),
                ilDatePresentation::formatDate($start)
            );
        }

        if ($a_set["deadline"] > 0) {
            $end = new ilDateTime($a_set["deadline"], IL_CAL_UNIX);
            $info_screen->addProperty(
                $lng->txt("task_deadline"),
                ilDatePresentation::formatDate($end)
            );
        }

        $modal = $factory->modal()->roundtrip(
            $lng->txt("task_details"),
            $factory->legacy($info_screen->getHTML())
        )
            ->withCancelButtonLabel("close");
        $button1 = $factory->button()->shy($a_set["title"], '#')
            ->withOnClick($modal->getShowSignal());

        $this->tpl->setVariable("TITLE", $renderer->render([$button1, $modal]));
    }

    /**
     * Get overview.
     */
    public function getOverview() : string
    {
        $lng = $this->lng;

        return '<div class="small">' . (count($this->tasks)) . " " . $lng->txt("task_derived_tasks") . "</div>";
    }

    //
    // New rendering
    //

    protected bool $new_rendering = true;

    /**
     * @inheritdoc
     */
    public function getHTMLNew() : string
    {
        global $DIC;
        $collector = $DIC->task()->derived()->factory()->collector();

        $this->tasks = $collector->getEntries($this->user->getId());

        $this->getListRowData();

        return parent::getHTMLNew();
    }

    /**
     * @inheritdoc
     */
    protected function getListItemForData(array $data) : ?Item
    {
        $factory = $this->ui->factory();
        $lng = $this->lng;

        $title = $data["title"];
        $props = [];

        if ($data["ref_id"] > 0) {
            $obj_id = ilObject::_lookupObjId($data["ref_id"]);
            $obj_type = ilObject::_lookupType($obj_id);
            $url = $data['url'] == "" ? ilLink::_getStaticLink($data["ref_id"]) : $data['url'];
            $link = $url;
            $title = $factory->link()->standard($data["title"], $link);
            $props[$lng->txt("obj_" . $obj_type)] = ilObject::_lookupTitle($obj_id);
        }

        if ($data["wsp_id"] > 0) {
            $wst = new ilWorkspaceTree($this->user->getId());
            $obj_id = $wst->lookupObjectId($data["wsp_id"]);
            $obj_type = ilObject::_lookupType($obj_id);
            $url = $data['url'] == "" ? ilLink::_getStaticLink($data["wsp_id"]) : $data['url'];
            $link = $url;
            $title = $factory->link()->standard($data["title"], $link);
            $props[$lng->txt("obj_" . $obj_type)] = ilObject::_lookupTitle($obj_id);
        }

        if ($data["starting_time"] > 0) {
            $start = new ilDateTime($data["starting_time"], IL_CAL_UNIX);
            $props[$lng->txt("task_start")] = ilDatePresentation::formatDate($start);
        }

        $factory = $this->ui->factory();
        $item = $factory->item()->standard($title)
                        ->withProperties($props);

        if ($data["deadline"] > 0) {
            $end = new ilDateTime($data["deadline"], IL_CAL_UNIX);
            //$props[$lng->txt("task_deadline")] =
            //    ilDatePresentation::formatDate($end);
            $item = $item->withDescription(ilDatePresentation::formatDate($end));
        }


        return $item;
    }

    /**
     * No item entry
     */
    public function getNoItemFoundContent() : string
    {
        return $this->lng->txt("task_no_task_items");
    }
}
