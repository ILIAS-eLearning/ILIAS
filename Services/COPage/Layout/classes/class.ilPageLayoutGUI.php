<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Page.php");

/**
* Class ilPageLayoutGUI GUI class
*
* @author Hendrik Holtmann <holtmann@me.com>
* @version $Id$
*
* @ilCtrl_Calls ilPageLayoutGUI: ilPageEditorGUI, ilEditClipboardGUI
* @ilCtrl_Calls ilPageLayoutGUI: ilPublicUserProfileGUI, ilPageObjectGUI
*
*/
class ilPageLayoutGUI extends ilPageObjectGUI
{
    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilSetting
     */
    protected $settings;

    protected $layout_object = null;


    /**
    * Constructor
    */
    public function __construct($a_parent_type, $a_id = 0, $a_old_nr = 0, $a_prevent_get_id = false, $a_lang = "")
    {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->settings = $DIC->settings();
        $tpl = $DIC["tpl"];
    
        parent::__construct($a_parent_type, $a_id, $a_old_nr, $a_prevent_get_id, $a_lang);

        //associated object
        include_once("./Services/COPage/Layout/classes/class.ilPageLayout.php");

        $this->layout_object = new ilPageLayout($a_id);
        $this->layout_object->readObject();

        // content style
        include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
        $tpl->setCurrentBlock("ContentStyle");
        $tpl->setVariable(
            "LOCATION_CONTENT_STYLESHEET",
            ilObjStyleSheet::getContentStylePath($this->layout_object->getStyleId())
        );
        $tpl->parseCurrentBlock();
        
        $tpl->setCurrentBlock("SyntaxStyle");
        $tpl->setVariable(
            "LOCATION_SYNTAX_STYLESHEET",
            ilObjStyleSheet::getSyntaxStylePath()
        );
        $tpl->setVariable(
            "LOCATION_ADDITIONAL_STYLESHEET",
            ilObjStyleSheet::getPlaceHolderStylePath()
        );
        $tpl->parseCurrentBlock();
        
        $this->setStyleId($this->layout_object->getStyleId());
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        $ilCtrl = $this->ctrl;
        
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($next_class) {
            case 'ilmdeditorgui':
                return parent::executeCommand();
                break;

            case "ilpageobjectgui":
die("ilPageLayoutGUI forward to ilpageobjectgui error.");
                return;
                
            default:
                $html = parent::executeCommand();
                return $html;
        }
    }
    
    public function create()
    {
        $this->properties("insert");
    }

    /**
     * Edit page layout properties
     *
     * @param string $a_mode edit mode
     */
    public function properties($a_mode = "save", $a_form = null)
    {
        $ilTabs = $this->tabs;
    
        $ilTabs->setTabActive('properties');
        
        if (!$a_form) {
            $a_form = $this->initForm($a_mode);
        }
        
        $this->tpl->setContent($a_form->getHTML());
    }
    
    public function initForm($a_mode)
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilSetting = $this->settings;
        
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form_gui = new ilPropertyFormGUI();
        $form_gui->setFormAction($ilCtrl->getFormAction($this));
        $form_gui->setTitle($lng->txt("cont_ed_pglprop"));

        // title
        $title_input = new ilTextInputGUI($lng->txt("title"), "pgl_title");
        $title_input->setSize(50);
        $title_input->setMaxLength(128);
        $title_input->setValue($this->layout_object->title);
        $title_input->setTitle($lng->txt("title"));
        $title_input->setRequired(true);

        // description
        $desc_input = new ilTextAreaInputGUI($lng->txt("description"), "pgl_desc");
        $desc_input->setValue($this->layout_object->description);
        $desc_input->setRows(3);
        $desc_input->setCols(37);
        $desc_input->setTitle($lng->txt("description"));
        $desc_input->setRequired(false);
        
        // modules
        $mods = new ilCheckboxGroupInputGUI($this->lng->txt("modules"), "module");
        // $mods->setRequired(true);
        $mods->setValue($this->layout_object->getModules());
        foreach (ilPageLayout::getAvailableModules() as $mod_id => $mod_caption) {
            $mod = new ilCheckboxOption($mod_caption, $mod_id);
            $mods->addOption($mod);
        }

        $form_gui->addItem($title_input);
        $form_gui->addItem($desc_input);
        $form_gui->addItem($mods);

        // style
        $fixed_style = $ilSetting->get("fixed_content_style_id");
        $style_id = $this->layout_object->getStyleId();

        if ($fixed_style > 0) {
            $st = new ilNonEditableValueGUI($lng->txt("cont_current_style"));
            $st->setValue(ilObject::_lookupTitle($fixed_style) . " (" .
                $this->lng->txt("global_fixed") . ")");
            $form_gui->addItem($st);
        } else {
            include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
            $st_styles = ilObjStyleSheet::_getStandardStyles(true, false);
            $st_styles[0] = $this->lng->txt("default");
            ksort($st_styles);
            $style_sel = new ilSelectInputGUI($lng->txt("obj_sty"), "style_id");
            $style_sel->setOptions($st_styles);
            $style_sel->setValue($style_id);
            $form_gui->addItem($style_sel);
        }
                        
        $form_gui->addCommandButton("updateProperties", $lng->txt($a_mode));
        
        return $form_gui;
    }

    /**
     * Update properties
     */
    public function updateProperties()
    {
        $lng = $this->lng;
        
        $form = $this->initForm("save");
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            return $this->properties("save", $form);
        }
        
        $this->layout_object->setTitle($form->getInput('pgl_title'));
        $this->layout_object->setDescription($form->getInput('pgl_desc'));
        $this->layout_object->setStyleId($form->getInput('style_id'));
        $this->layout_object->setModules($form->getInput('module'));
        $this->layout_object->update();
        
        ilUtil::sendInfo($lng->txt("saved_successfully"));
        $this->properties();
    }
    
    /**
    * output tabs
    */
    public function setTabs($a_tabs = "")
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;

        $ilCtrl->setParameterByClass("ilpagelayoutgui", "obj_id", $this->obj->getId());
        $ilTabs->addTarget(
            "properties",
            $ilCtrl->getLinkTarget($this, "properties"),
            array("properties","", ""),
            "",
            ""
        );
        $tpl->setTitleIcon(ilUtil::getImagePath("icon_pg.svg"));
        $tpl->setTitle($this->layout_object->getTitle());
        $tpl->setDescription("");
        //	$tpl->setTitle(
        //		$lng->txt("sahs_page").": ".$this->node_object->getTitle());
    }
}
