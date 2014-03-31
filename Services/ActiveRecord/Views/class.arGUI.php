<?php
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Demo/TestRecord/class.arTestRecord.php');
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Views/Edit/class.arEditGUI.php');
require_once('./Services/PersonalDesktop/classes/class.ilPersonalDesktopGUI.php');
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Views/Index/class.arIndexTableGUI.php');
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Views/View/class.ActiveRecordViewGUI.php');

/**
 * Class arTestRecordGUI
 *
 * @author Timon Amstutz <timon.amstutz@bluewin.ch>
 */
class arTestRecordGUI
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
    public $access;

    /**
     * @var ActiveRecord
     */
    protected $ar;


    public function __construct(ActiveRecord $ar, arIndexTableGUI $indexTableGUI = null, arEditGUI $editGUI = null, ActiveRecordViewGUI $viewGUI =  null)
    {
        global $tpl, $ilCtrl, $ilAccess;

        $this->tpl      = $tpl;
        $this->ctrl     = $ilCtrl;
        $this->access   = $ilAccess;
        $this->ar       = $ar;

        if($indexTableGUI == null)
        {
            $this->indexTableGUI = new arIndexTableGUI($this, "index", new ActiveRecordList($this->ar));
        }
        else
        {
            $this->indexTableGUI = $indexTableGUI;
        }
        if($editGUI == null)
        {
            $this->editGUI = new arEditGUI($this, ActiveRecord::find($_GET[get_class($this->ar).'_id']));
        }
        else
        {
            $this->editGUI = $editGUI;
        }
    }


    public function executeCommand()
    {
        $cmd = $_GET['cmd'] ? $_GET['cmd'] : 'index';

        if ($cmd == 'configure')
        {
            $cmd = "index";
        }
        $this->$cmd();
    }


    public function index()
    {

        $this->tpl->setContent($table->getHTML());
    }


    public function edit()
    {

        $this->tpl->setContent($editGUI->getHTML());
    }

    /**
     * Configure screen
     */
    function view()
    {
        $this->tpl->setContent("view");
    }

    /**
     * Configure screen
     */
    function delete()
    {
        $this->tpl->setContent("delete");
    }
}

/**
 * Configure screen
 */
function edit()
{
    $form = new MessageRecordEditGUI($this);
    $this->tpl->setContent($form->getHTML());
}

public
function create()
{
    $form = new MessageRecordEditGUI($this, new MessageRecord());
    $this->save($form);

}

public
function update()
{
    $form = new MessageRecordEditGUI($this, MessageRecord::find($_GET['message_id']));
    $this->save($form);

}

public
function save(MessageRecordEditGUI $form)
{
    if ($form->saveObject())
    {
        ilUtil::sendSuccess($this->plugin_object->txt('success_edit'), true);
        $this->ctrl->redirect($this, "index");
    } else
    {
        $this->tpl->setContent($form->getHTML());
    }
}

?>

