<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPCPlaceHolder.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
* Class ilPCPlaceHolderGUI
*
* User Interface for Place Holder Management
*
* @author Hendrik Holtmann <holtmann@me.com>
* @version $Id: class.ilPCListGUI.php 17506 2008-09-24 13:48:46Z akill $
*
* @ilCtrl_Calls ilPCPlaceHolderGUI: ilPCMediaObjectGUI
*
* @ingroup ServicesCOPage
*/
class ilPCPlaceHolderGUI extends ilPageContentGUI
{
    public $pg_obj;
    public $content_obj;
    public $hier_id;
    public $pc_id;
    protected $styleid;
    
    const TYPE_TEXT = "Text";
    const TYPE_QUESTION = "Question";
    const TYPE_MEDIA = "Media";
    const TYPE_VERIFICATION = "Verification";
    
    /**
    * Constructor
    * @access	public
    */
    public function __construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id = "")
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->pg_obj = $a_pg_obj;
        $this->content_obj = $a_content_obj;
        $this->hier_id = $a_hier_id;
        $this->pc_id = $a_pc_id;
        
        parent::__construct($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
    }
        
    /**
    * execute command
    */
    public function executeCommand()
    {
        // get next class that processes or forwards current command
        $next_class = $this->ctrl->getNextClass($this);
        // get current command
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            case 'ilpcmediaobjectgui':  //special handling
                include_once("./Services/COPage/classes/class.ilPCMediaObjectGUI.php");
                $media_gui = new ilPCMediaObjectGUI($this->pg_obj, $this->content_obj, $this->hier_id, $this->pc_id);
                $ret = $this->ctrl->forwardCommand($media_gui);
                break;
                
            default:
                $ret = $this->$cmd();
                break;
        }
        
        return $ret;
    }
    
    /**
    * Handle Insert
    */
    protected function insert()
    {
        $this->propertyGUI("create", self::TYPE_TEXT, "100px", "insert");
    }
    
    /**
    * create new table in dom and update page in db
    */
    protected function create()
    {
        if ($_POST["plach_height"]=="" ||
            !preg_match("/[0-9]+/", $_POST["plach_height"])) {
            return $this->insert();
        }
        
        $this->content_obj = new ilPCPlaceHolder($this->getPage());
        $this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
        $this->content_obj->setHeight($_POST["plach_height"] . "px");
        $this->content_obj->setContentClass($_POST['plach_type']);
        $this->updated = $this->pg_obj->update();
        if ($this->updated === true) {
            $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
        } else {
            $this->insert();
        }
    }
    
    /**
    * Handle Editing
    */
    public function edit()
    {
        if ($this->getPageConfig()->getEnablePCType("PlaceHolder")) {
            $this->edit_object();
        } else {
            $this->forward_edit();
        }
    }
    
    /**
    * Set Style Id.
    *
    * @param	int	$a_styleid	Style Id
    */
    public function setStyleId($a_styleid)
    {
        $this->styleid = $a_styleid;
    }

    /**
    * Get Style Id.
    *
    * @return	int	Style Id
    */
    public function getStyleId()
    {
        return $this->styleid;
    }

    /**
    * Handle Editing Private Methods
    */
    protected function edit_object()
    {
        $this->propertyGUI(
            "saveProperties",
            $this->content_obj->getContentClass(),
            $this->content_obj->getHeight(),
            "save"
        );
    }
        
    protected function forward_edit()
    {
        switch ($this->content_obj->getContentClass()) {
            case self::TYPE_MEDIA:
                include_once("./Services/COPage/classes/class.ilPCMediaObjectGUI.php");
                $this->ctrl->setCmdClass("ilpcmediaobjectgui");
                $this->ctrl->setCmd("insert");
                $media_gui = new ilPCMediaObjectGUI($this->pg_obj, null);
                $this->ctrl->forwardCommand($media_gui);
                break;
            
            case self::TYPE_TEXT:
                $this->textCOSelectionGUI();
                break;
            
            case self::TYPE_QUESTION:
                include_once("./Services/COPage/classes/class.ilPCQuestionGUI.php");
                $this->ctrl->setCmdClass("ilpcquestiongui");
                $this->ctrl->setCmd("insert");
                $question_gui = new ilPCQuestionGUI($this->pg_obj, $this->content_obj, $this->hier_id, $this->pc_id);
                $question_gui->setSelfAssessmentMode(true);
                $this->ctrl->forwardCommand($question_gui);
                break;
            
            case self::TYPE_VERIFICATION:
                include_once("./Services/COPage/classes/class.ilPCVerificationGUI.php");
                $this->ctrl->setCmdClass("ilpcverificationgui");
                $this->ctrl->setCmd("insert");
                $cert_gui = new ilPCVerificationGUI($this->pg_obj, $this->content_obj, $this->hier_id, $this->pc_id);
                $this->ctrl->forwardCommand($cert_gui);
                break;
            
            default:
                break;
        }
    }
    
    
    /**
    * save placeholder properties in db and return to page edit screen
    */
    protected function saveProperties()
    {
        if ($_POST["plach_height"]=="" ||
            !preg_match("/[0-9]+/", $_POST["plach_height"])) {
            return $this->edit_object();
        }
            
        $this->content_obj->setContentClass($_POST['plach_type']);
        $this->content_obj->setHeight($_POST["plach_height"] . "px");
        
        $this->updated = $this->pg_obj->update();
        if ($this->updated === true) {
            $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
        } else {
            $this->pg_obj->addHierIDs();
            $this->edit();
        }
    }
    
    /**
    * Object Property GUI
    */
    protected function propertyGUI($a_action, $a_type, $a_height, $a_mode)
    {
        $lng = $this->lng;
        
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form_gui = new ilPropertyFormGUI();
        $this->form_gui->setFormAction($this->ctrl->getFormAction($this));
        $this->form_gui->setTitle($lng->txt("cont_ed_plachprop"));

        $ttype_input = new ilRadioGroupInputGUI($lng->txt("type"), "plach_type");
        $type_captions = $this->getTypeCaptions();
        foreach ($this->getAvailableTypes($a_type) as $type) {
            $ttype_input->addOption(new ilRadioOption($type_captions[$type], $type));
        }
        $ttype_input->setRequired(true);
        $this->form_gui->addItem($ttype_input);
        
        $theight_input = new ilTextInputGUI($lng->txt("height"), "plach_height");
        $theight_input->setSize(4);
        $theight_input->setMaxLength(3);
        $theight_input->setTitle($lng->txt("height") . " (px)");
        $theight_input->setRequired(true);
        $this->form_gui->addItem($theight_input);
        
        $theight_input->setValue(preg_replace("/px/", "", $a_height));
        $ttype_input->setValue($a_type);
        
        $this->form_gui->addCommandButton($a_action, $lng->txt($a_mode));
        $this->form_gui->addCommandButton("cancelCreate", $lng->txt("cancel"));
        $this->tpl->setContent($this->form_gui->getHTML());
    }
    
    /**
    * Text Item Selection
    */
    protected function textCOSelectionGUI()
    {
        $lng = $this->lng;
    
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form_gui = new ilPropertyFormGUI();
        $this->form_gui->setFormAction($this->ctrl->getFormAction($this));
        $this->form_gui->setTitle($lng->txt("cont_ed_select_pctext"));

        // Select Question Type
        $ttype_input = new ilRadioGroupInputGUI($lng->txt("cont_ed_textitem"), "pctext_type");
        $ttype_input->addOption(new ilRadioOption($lng->txt("cont_ed_par"), 0));
        $ttype_input->addOption(new ilRadioOption($lng->txt("cont_ed_dtable"), 1));
        $ttype_input->addOption(new ilRadioOption($lng->txt("cont_ed_atable"), 2));
        $ttype_input->addOption(new ilRadioOption($lng->txt("cont_ed_list"), 3));
        $ttype_input->addOption(new ilRadioOption($lng->txt("cont_ed_flist"), 4));
        $ttype_input->addOption(new ilRadioOption($lng->txt("cont_tabs"), 5));
        $this->form_gui->addItem($ttype_input);
        
        $this->form_gui->addCommandButton("insertPCText", $lng->txt("insert"));
        $this->form_gui->addCommandButton("cancelCreate", $lng->txt("cancel"));
        $this->tpl->setContent($this->form_gui->getHTML());
    }
    
    /**
    * Forwards Text Item Selection to GUI classes
    */
    protected function insertPCText()
    {
        switch ($_POST['pctext_type']) {
            case 0:  //Paragraph / Text
                
                // js editing? -> redirect to js page editor
                // if ($ilSetting->get("enable_js_edit", 1) && ilPageEditorGUI::_doJSEditing())
                if (ilPageEditorGUI::_doJSEditing()) {
                    $ret_class = $this->ctrl->getReturnClass($this);
                    $this->ctrl->setParameterByClass($ret_class, "pl_hier_id", $this->hier_id);
                    $this->ctrl->setParameterByClass($ret_class, "pl_pc_id", $this->pc_id);
                    $this->ctrl->redirectByClass(
                        $ret_class,
                        "insertJSAtPlaceholder"
                    );
                }

                include_once("./Services/COPage/classes/class.ilPCParagraphGUI.php");
                $this->ctrl->setCmdClass("ilpcparagraphgui");
                $this->ctrl->setCmd("insert");
                $paragraph_gui = new ilPCParagraphGUI($this->pg_obj, $this->content_obj, $this->hier_id, $this->pc_id);
                $paragraph_gui->setStyleId($this->getStyleId());
                $paragraph_gui->setPageConfig($this->getPageConfig());
                $this->ctrl->forwardCommand($paragraph_gui);
                break;
                
            case 1:  //DataTable
                include_once("./Services/COPage/classes/class.ilPCDataTableGUI.php");
                $this->ctrl->setCmdClass("ilpcdatatablegui");
                $this->ctrl->setCmd("insert");
                $dtable_gui = new ilPCDataTableGUI($this->pg_obj, $this->content_obj, $this->hier_id, $this->pc_id);
                $this->ctrl->forwardCommand($dtable_gui);
                break;
                
            case 2:  //Advanced Table
                include_once("./Services/COPage/classes/class.ilPCTableGUI.php");
                $this->ctrl->setCmdClass("ilpctablegui");
                $this->ctrl->setCmd("insert");
                $atable_gui = new ilPCTableGUI($this->pg_obj, $this->content_obj, $this->hier_id, $this->pc_id);
                $this->ctrl->forwardCommand($atable_gui);
                break;
                
            case 3:  //Advanced List
                include_once("./Services/COPage/classes/class.ilPCListGUI.php");
                $this->ctrl->setCmdClass("ilpclistgui");
                $this->ctrl->setCmd("insert");
                $list_gui = new ilPCListGUI($this->pg_obj, $this->content_obj, $this->hier_id, $this->pc_id);
                $this->ctrl->forwardCommand($list_gui);
                break;
                
            case 4:  //File List
                include_once("./Services/COPage/classes/class.ilPCFileListGUI.php");
                $this->ctrl->setCmdClass("ilpcfilelistgui");
                $this->ctrl->setCmd("insert");
                $file_list_gui = new ilPCFileListGUI($this->pg_obj, $this->content_obj, $this->hier_id, $this->pc_id);
                $file_list_gui->setStyleId($this->getStyleId());
                $this->ctrl->forwardCommand($file_list_gui);
                break;
                
            case 5:  //Tabs
                include_once("./Services/COPage/classes/class.ilPCTabsGUI.php");
                $this->ctrl->setCmdClass("ilpctabsgui");
                $this->ctrl->setCmd("insert");
                $tabs_gui = new ilPCTabsGUI($this->pg_obj, $this->content_obj, $this->hier_id, $this->pc_id);
                $tabs_gui->setStyleId($this->getStyleId());
                $this->ctrl->forwardCommand($tabs_gui);
                break;
                
            default:
                break;
        }
    }
    
    /**
     * Cancel
     */
    public function cancel()
    {
        $this->ctrl->returnToParent($this, "jump" . $this->hier_id);
    }
        
    protected function getAvailableTypes($a_selected_type = "")
    {
        // custom config?
        if (method_exists($this->getPageConfig(), "getAvailablePlaceholderTypes")) {
            $types = $this->getPageConfig()->getAvailablePlaceholderTypes();
        } else {
            $types = array(self::TYPE_TEXT, self::TYPE_MEDIA, self::TYPE_QUESTION);
        }

        include_once("./Services/Certificate/classes/class.ilCertificate.php");
        if (!ilCertificate::isActive()) {
            // we remove type verification if certificates are deactivated and this
            // is not the currently selected value
            if (($key = array_search(self::TYPE_VERIFICATION, $types)) !== false &&
                self::TYPE_VERIFICATION != $a_selected_type) {
                unset($types[$key]);
            }
        }
        return $types;
    }
    
    protected function getTypeCaptions()
    {
        $lng = $this->lng;
        
        return array(
                self::TYPE_TEXT => $lng->txt("cont_ed_plachtext"),
                self::TYPE_MEDIA => $lng->txt("cont_ed_plachmedia"),
                self::TYPE_QUESTION => $lng->txt("cont_ed_plachquestion"),
                self::TYPE_VERIFICATION => $lng->txt("cont_ed_plachverification")
            );
    }
}
