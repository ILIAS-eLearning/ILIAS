<?php
include_once("./Services/Component/classes/class.ilPluginConfigGUI.php");
include_once('./Services/ActiveRecord/class.ActiveRecordList.php');
include_once('./Services/ActiveRecord/Views/Index/class.arIndexTableGUI.php');
include_once('./Services/UICore/classes/class.ilTemplate.php');

/**
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version 2.0.7
 *
 */
class arGUI
{

    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilTemplate
     */
    protected $tpl;
    /**
     * @var ilAccessHandler
     */
    protected $access;
    /**
     * @ar ilLanguage
     */
    protected $lng;
    /**
     * @var ilPlugin
     */
    protected $plugin_object = null;
    /**
     * @var string
     */
    protected $record_type = "";
    /**
     * @var ActiveRecord
     */
    protected $ar;
    /**
     * @var string
     */
    protected $lng_prefix = "";


    /**
     * @param          $record_type
     * @param ilPlugin $plugin_object
     */
    public function __construct($record_type, ilPlugin $plugin_object = null)
    {
        global $DIC;
        $tpl = $DIC['tpl'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilAccess = $DIC['ilAccess'];
        $lng = $DIC['lng'];

        $this->lng = $lng;

        if ($plugin_object) {
            $this->setLngPrefix($plugin_object->getPrefix());
            $plugin_object->loadLanguageModule();
        }

        $this->tpl = $tpl;
        $this->ctrl = $ilCtrl;
        $this->access = $ilAccess;
        $this->plugin_object = $plugin_object;
        $this->record_type = $record_type;
        $this->ar = new $record_type();
    }


    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case "edit":
            case "update":
            case "view":
            case "delete":
                $this->$cmd(arIndexTableGUI::domid_decode($_GET['ar_id']));
                break;
            case "multiAction":
                $action_name = $_POST["index_table_multi_action_2"];
                $this->multiAction($action_name);
                break;
            default:
                $this->$cmd();
                break;
        }
    }


    /**
     * @param arIndexTableGUI $table_gui
     */
    public function index(arIndexTableGUI $table_gui = null)
    {
        if (!$table_gui) {
            $index_table_gui_class = $this->record_type . "IndexTableGUI";
            /**
             * @var arIndexTableGUI $table_gui
             */
            $table_gui = new $index_table_gui_class($this, "index", new ActiveRecordList($this->ar));
        }
        $this->tpl->setContent($table_gui->getHTML());
    }


    public function applyFilter()
    {
        $index_table_gui_class = $this->record_type . "IndexTableGUI";
        /**
         * @var arIndexTableGUI $table_gui
         */
        $table_gui = new $index_table_gui_class($this, "index", new ActiveRecordList($this->ar));
        $table_gui->applyFilter();
        $this->index();
    }


    public function resetFilter()
    {
        $index_table_gui_class = $this->record_type . "IndexTableGUI";
        /**
         * @var arIndexTableGUI $table_gui
         */
        $table_gui = new $index_table_gui_class($this, "index", new ActiveRecordList($this->ar));
        $table_gui->resetFilter();
        $this->index();
    }


    /**
     * @param string $action_name
     */
    public function multiAction($action_name = "")
    {
        $ids = array();
        if ($_POST['id']) {
            foreach ($_POST['id'] as $id) {
                $ids[] = arIndexTableGUI::domid_decode($id);
            }
        }

        if (empty($ids)) {
            ilUtil::sendFailure($this->txt("no_checkbox", false), true);
            $this->ctrl->redirect($this, "index");
        }

        switch ($action_name) {
            case "delete":
                $this->deleteMultiple($ids);
                break;
            default:
                $this->customMultiAction($action_name, $ids);
                break;
        }
    }


    /**
     * @param string $action_name
     * @param null   $ids
     */
    public function customMultiAction($action_name = "", $ids = null)
    {
    }


    /**
     * Configure screen
     */
    public function edit($id)
    {
        $edit_gui_class = $this->record_type . "EditGUI";
        /**
         * @var arEditGUI $edit_gui
         */
        $edit_gui = new $edit_gui_class($this, $this->ar->find($id));
        $this->tpl->setContent($edit_gui->getHTML());
    }


    public function add()
    {
        $edit_gui_class = $this->record_type . "EditGUI";
        /**
         * @var arEditGUI $edit_gui
         */
        $edit_gui = new $edit_gui_class($this, $this->ar);
        $this->tpl->setContent($edit_gui->getHTML());
    }


    public function create()
    {
        $edit_gui_class = $this->record_type . "EditGUI";
        /**
         * @var arEditGUI $edit_gui
         */
        $edit_gui = new $edit_gui_class($this, $this->ar);
        $this->save($edit_gui);
    }


    /**
     * @param $id
     */
    public function update($id)
    {
        $edit_gui_class = $this->record_type . "EditGUI";
        /**
         * @var arEditGUI $edit_gui
         */
        $edit_gui = new $edit_gui_class($this, $this->ar->find($id));
        $this->save($edit_gui);
    }


    /**
     * @param arEditGUI $edit_gui
     */
    public function save(arEditGUI $edit_gui)
    {
        if ($edit_gui->saveObject()) {
            ilUtil::sendSuccess($this->getRecordCreatedMessage());
            $this->ctrl->redirect($this, "index");
        } else {
            $this->tpl->setContent($edit_gui->getHTML());
        }
    }


    /**
     * @return string
     */
    public function getRecordCreatedMessage()
    {
        return $this->txt(('record_created'), true);
    }


    /**
     * @param $id
     */
    public function view($id)
    {
        $display_gui_class = $this->record_type . "DisplayGUI";
        /**
         * @var arDisplayGUI $display_gui
         */
        $display_gui = new $display_gui_class($this, $this->ar->find($id));
        $this->tpl->setContent($display_gui->getHtml());
    }


    /**
     * @param $id
     */
    public function delete($id)
    {
        $this->deleteMultiple(array( $id ));
    }


    /**
     * @param $ids []
     */
    public function deleteMultiple($ids = null)
    {
        $delete_gui_class = $this->record_type . "DeleteGUI";
        /**
         * @var arDeleteGUI $delete_gui
         */
        $delete_gui = new $delete_gui_class($this, "delete", new ActiveRecordList($this->ar), "delete", $ids);
        if (count($ids) == 1) {
            ilUtil::sendQuestion($this->getDeleteRecordConfirmationMessage());
        } else {
            ilUtil::sendQuestion($this->getDeleteRecordsConfirmationMessage());
        }
        $this->tpl->setContent($delete_gui->getHTML());
    }


    /**
     * @return string
     */
    public function getDeleteRecordsConfirmationMessage()
    {
        return $this->txt(('delete_records_confirmation'), true);
    }


    /**
     * @return string
     */
    public function getDeleteRecordConfirmationMessage()
    {
        return $this->txt(('delete_record_confirmation'), true);
    }


    public function deleteItems()
    {
        $nr_ids = $_POST['nr_ids'];
        for ($i = 0; $i < $nr_ids; $i++) {
            $id = $_POST['delete_id_' . $i];
            $record = $this->ar->find($id);
            $record->delete();
        }
        if ($i == 1) {
            ilUtil::sendSuccess($this->getDeleteRecordMessage(), true);
        } else {
            ilUtil::sendSuccess($this->getDeleteRecordsMessage(), true);
        }

        $this->ctrl->redirect($this, "index");
    }


    /**
     * @return string
     */
    public function getDeleteRecordsMessage()
    {
        return $this->txt(('records_deleted'), true);
    }


    /**
     * @return string
     */
    public function getDeleteRecordMessage()
    {
        return $this->txt(('record_deleted'), true);
    }


    /**
     * @param string $lng_prefix
     */
    public function setLngPrefix($lng_prefix)
    {
        $this->lng_prefix = $lng_prefix;
    }


    /**
     * @return string
     */
    public function getLngPrefix()
    {
        return $this->lng_prefix;
    }


    /**
     * @param      $txt
     * @param bool $plugin_txt
     *
     * @return string
     */
    public function txt($txt, $plugin_txt = true)
    {
        if ($this->getLngPrefix() != "" && $plugin_txt) {
            return $this->lng->txt($this->getLngPrefix() . "_" . $txt, $this->getLngPrefix());
        } else {
            return $this->lng->txt($txt);
        }
    }
}
