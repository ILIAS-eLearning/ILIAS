<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * BlockGUI class for Tasks on PD
 *
 * @author Alex Killing <alex.killing@gmx.de>
 *
 * @ilCtrl_IsCalledBy ilPDTasksBlockGUI: ilColumnGUI
 */
class ilPDTasksBlockGUI extends ilBlockGUI
{
    public static $block_type = "pdtasks";

    protected $tasks = [];

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
    public static function getScreenMode()
    {
        switch ($_GET["cmd"]) {
            default:
                return IL_SCREEN_SIDE;
                break;
        }
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
    public function fillDataSection()
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
    public function getListRowData()
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
    public function fillRow($a_set)
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
            
            if (0 === $a_set['url']) {
                $url = ilLink::_getStaticLink($a_set["ref_id"]);
            } else {
                $url = $a_set['url'];
            }
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

            if (0 === $a_set['url']) {
                $url = ilLink::_getStaticLink($a_set["wsp_id"]);
            } else {
                $url = $a_set['url'];
            }
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
    public function getOverview()
    {
        $lng = $this->lng;

        return '<div class="small">' . ((int) count($this->tasks)) . " " . $lng->txt("task_derived_tasks") . "</div>";
    }

    //
    // New rendering
    //

    protected $new_rendering = true;

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
    protected function getListItemForData(array $data) : ?\ILIAS\UI\Component\Item\Item
    {
        $factory = $this->ui->factory();
        $lng = $this->lng;

        $title = $data["title"];
        $props = [];

        if ($data["ref_id"] > 0) {
            $obj_id = ilObject::_lookupObjId($data["ref_id"]);
            $obj_type = ilObject::_lookupType($obj_id);
            if ($data['url'] == "") {
                $url = ilLink::_getStaticLink($data["ref_id"]);
            } else {
                $url = $data['url'];
            }
            $link = $url;
            $title = $factory->link()->standard($data["title"], $link);
            $props[$lng->txt("obj_" . $obj_type)] = ilObject::_lookupTitle($obj_id);
        }

        if ($data["wsp_id"] > 0) {
            $wst = new ilWorkspaceTree($this->user->getId());
            $obj_id = $wst->lookupObjectId($data["wsp_id"]);
            $obj_type = ilObject::_lookupType($obj_id);
            if ($data['url'] == "") {
                $url = ilLink::_getStaticLink($data["wsp_id"]);
            } else {
                $url = $data['url'];
            }
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
     *
     * @return string
     */
    public function getNoItemFoundContent() : string
    {
        return $this->lng->txt("task_no_task_items");
    }
}
