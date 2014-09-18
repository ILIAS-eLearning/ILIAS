<?php
require_once("./Services/Object/classes/class.ilObjectGUI.php");
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Modules/Bibliographic/classes/Admin/class.ilObjBibliographicAdminTableGUI.php');
require_once('./Modules/Bibliographic/classes/Admin/class.ilBibliographicSetting.php');
require_once('./Services/Form/classes/class.ilTextInputGUI.php');


/**
 * Bibliographic Administration Settings.
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 *
 *
 * @ingroup ModulesBibliographic
 */
class ilObjBibliographicAdminAttributeOrderGUI {

    /**
     * @var ilObjBibliographicAdminGUI
     */
    protected $parent_gui;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * Constructor
     *
     * @param ilObjBibliographicAdminGUI $parent_gui
     */
    public function __construct($parent_gui){
        global $ilCtrl, $lng;
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        $this->parent_gui = $parent_gui;

    }

    /**
     * Execute command
     *
     * @access public
     *
     */
    public function executeCommand()
    {
        global $ilCtrl;
        $cmd = $ilCtrl->getCmd();

        switch($cmd)
        {
            case 'view':
                $this->view();
                break;
            case 'save':
                $this->save();
                break;
        }
    }

    public function view() {
        $a_form = $this->initForm();
        $this->parent_gui->tpl->setContent($a_form->getHTML());
        return true;
    }

    /**
     * Init settings property form
     *
     * @access protected
     */
    protected function initForm()
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt('bibl_admin_settings'));

        $bibl_set = new ilSetting("bibl");

        $bibtex_order = new ilTextInputGUI($this->lng->txt("attr_order_bibtex"), 'bib_ord');
        $bibtex_order->setValue($bibl_set->get("bib_ord"), true);
        $bibtex_order->setInfo($this->lng->txt("attr_order_bibtex_info"));
        $form->addItem($bibtex_order);

        $ris_order = new ilTextInputGUI($this->lng->txt("attr_order_ris"), 'ris_ord');
        $ris_order->setValue($bibl_set->get("ris_ord"), true);
        $ris_order->setInfo($this->lng->txt("attr_order_ris_info"));
        $form->addItem($ris_order);

        $form->addCommandButton('save', $this->lng->txt("save"));

        return $form;
    }


    /**
     * Save settings in Form
     *
     */
    public function save()
    {
        $form = $this->initForm();
        if($form->checkInput())
        {
            $bibl_set = new ilSetting("bibl");
            $bibl_set->set("bib_ord", $form->getInput("bib_ord"));
            $bibl_set->set("ris_ord", $form->getInput("ris_ord"));


            ilUtil::sendSuccess($this->lng->txt("settings_saved"),true);
            $this->ctrl->redirect($this, "view");
        }

        $form->setValuesByPost();
        $this->editSettings($form);
    }
}
?>