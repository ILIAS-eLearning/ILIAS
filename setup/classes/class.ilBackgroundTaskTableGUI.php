<?php
use ILIAS\BackgroundTasks\Implementation\Bucket\State;
use ILIAS\BackgroundTasks\Implementation\Persistence\BucketContainer;

require_once("./Services/Table/classes/class.ilTable2GUI.php");
require_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");

/**
 * Class ilBackgroundTaskTableGUI
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 */
class ilBackgroundTaskTableGUI extends ilTable2GUI
{

    /**
     * @var ilSetup
     */
    protected $setup;


    /**
     * ilBackgroundTaskTableGUI constructor.
     *
     * @param ilSetup $setup
     */
    public function __construct($setup)
    {
        global $lng;

        parent::__construct(null, "");
        $this->setTitle($lng->txt("background_tasks"));
        $this->setLimit(9999);
        $this->setup = $setup;

        $this->addColumn($this->lng->txt("name"), "");
        $this->addColumn($this->lng->txt("id"), "");
        $this->addColumn($this->lng->txt("bt_available"), "");
        $this->addColumn($this->lng->txt("running_tasks"), "");
        $this->addColumn($this->lng->txt("waiting_tasks"), "");
        $this->addColumn($this->lng->txt("actions"), "");

        $this->setDefaultOrderField("name");
        $this->setDefaultOrderDirection("asc");

        $this->setEnableHeader(true);
        $this->setFormAction("setup.php?cmd=gateway");
        $this->setRowTemplate("tpl.bt_list_row.html", "setup");
        $this->disable("footer");
        $this->setEnableTitle(true);

        $this->getClients();

        //		$this->addMultiCommand("changedefault", $lng->txt("set_default_client"));
    }


    /**
     *
     */
    public function getClients()
    {
        global $lng;

        $clients = array();
        $clientlist = new ilClientList($this->setup->db_connections);
        $list = $clientlist->getClients();

        foreach ($list as $key => $client) {
            $client->provideGlobalDB();

            $client_name = ($client->getName()) ? $client->getName() : "&lt;" . $lng->txt("no_client_name") . "&gt;";

            if ($this->BTAvailable()) {
                $running_tasks = $this->getRunningTasksForDB();
                $waiting_tasks = $this->getWaitingTasksForDB();
                $bt_available = true;
            } else {
                $running_tasks = "-";
                $waiting_tasks = "-";
                $bt_available = false;
            }
            // visible data part
            $clients[] = array(
                "name"          => $client_name,
                "desc"          => $client->getDescription(),
                "id"            => $key,
                "running_tasks" => $running_tasks,
                "waiting_tasks" => $waiting_tasks,
                "bt_available"  => $bt_available

            );
        }

        $this->setData($clients);
    }

    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        global $lng;

        $this->tpl->setVariable("NAME", $a_set["name"]);
        $this->tpl->setVariable("DESC", $a_set["desc"]);
        $this->tpl->setVariable("ID", $a_set["id"]);
        $this->tpl->setVariable("RUNNING_TASKS", $a_set["running_tasks"]);
        $this->tpl->setVariable("WAITING_TASKS", $a_set["waiting_tasks"]);
        $this->tpl->setVariable("BT_AVAILABLE", $a_set["bt_available"] ? $this->lng->txt("yes") : $this->lng->txt("no"));

        $adv = new ilAdvancedSelectionListGUI();
        if ($a_set["bt_available"]) {
            $adv->addItem($this->lng->txt("kill_waiting_tasks"), "", "setup.php?cmd=kill_waiting_tasks&client_id=" . $a_set["id"]);
        }

        $this->tpl->setVariable("ACTIONS", $adv->getHTML());
    }

    public function kill_waiting_tasks()
    {
        echo "hi";
        exit;
    }


    private function getRunningTasksForDB()
    {
        return BucketContainer::where(["state" => State::RUNNING])->count();
    }


    private function getWaitingTasksForDB()
    {
        return BucketContainer::where(["state" => State::SCHEDULED])->count();
    }


    private function BTAvailable()
    {
        try {
            BucketContainer::where("TRUE")->first();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
