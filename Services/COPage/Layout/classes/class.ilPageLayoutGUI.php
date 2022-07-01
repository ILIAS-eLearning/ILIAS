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

use ILIAS\UI\Component\Input\Field\Radio;

/**
 * Class ilPageLayoutGUI GUI class
 *
 * @author Hendrik Holtmann <holtmann@me.com>
 * @ilCtrl_Calls ilPageLayoutGUI: ilPageEditorGUI, ilEditClipboardGUI
 * @ilCtrl_Calls ilPageLayoutGUI: ilPublicUserProfileGUI, ilPageObjectGUI
 */
class ilPageLayoutGUI extends ilPageObjectGUI
{
    protected ilTabsGUI $tabs;
    protected ilSetting $settings;
    protected ?ilPageLayout $layout_object = null;

    public function __construct(
        string $a_parent_type,
        int $a_id = 0,
        int $a_old_nr = 0,
        bool $a_prevent_get_id = false,
        string $a_lang = ""
    ) {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->ctrl = $DIC->ctrl();
        $this->tabs = $DIC->tabs();
        $this->lng = $DIC->language();
        $this->settings = $DIC->settings();
        $tpl = $DIC["tpl"];
    
        parent::__construct($a_parent_type, $a_id, $a_old_nr, $a_prevent_get_id, $a_lang);

        //associated object
        $this->layout_object = new ilPageLayout($a_id);
        $this->layout_object->readObject();

        // content style
        $tpl->setCurrentBlock("ContentStyle");
        $tpl->setVariable(
            "LOCATION_CONTENT_STYLESHEET",
            ilObjStyleSheet::getContentStylePath(0)
        );
        $tpl->parseCurrentBlock();
        
        $tpl->addCss(ilObjStyleSheet::getPlaceHolderStylePath());
        $tpl->addCss(ilObjStyleSheet::getSyntaxStylePath());

//        $this->setStyleId($this->layout_object->getStyleId());
    }

    public function executeCommand() : string
    {
        $next_class = $this->ctrl->getNextClass($this);

        switch ($next_class) {
            case 'ilmdeditorgui':
                return parent::executeCommand();

            default:
                $html = parent::executeCommand();
                return $html;
        }
    }
    
    public function create() : void
    {
        $this->properties("insert");
    }

    /**
     * Edit page layout properties
     */
    public function properties(
        string $a_mode = "save",
        ilPropertyFormGUI $a_form = null
    ) : void {
        $ilTabs = $this->tabs;
    
        $ilTabs->setTabActive('properties');
        
        if (!$a_form) {
            $a_form = $this->initForm($a_mode);
        }
        $this->tpl->setContent($a_form->getHTML());
    }
    
    public function initForm(string $a_mode) : ilPropertyFormGUI
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilSetting = $this->settings;
        
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

        $form_gui->addCommandButton("updateProperties", $lng->txt($a_mode));
        
        return $form_gui;
    }

    public function updateProperties() : void
    {
        $lng = $this->lng;
        
        $form = $this->initForm("save");
        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->properties("save", $form);
            return;
        }
        
        $this->layout_object->setTitle($form->getInput('pgl_title'));
        $this->layout_object->setDescription($form->getInput('pgl_desc'));
        $this->layout_object->setModules($form->getInput('module'));
        $this->layout_object->update();
        
        $this->tpl->setOnScreenMessage('info', $lng->txt("saved_successfully"));
        $this->properties();
    }
    
    /**
     * output tabs
     */
    public function setTabs(ilTabsGUI $a_tabs = null) : void
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;

        $ilCtrl->setParameterByClass("ilpagelayoutgui", "obj_id", $this->obj->getId());
        $ilTabs->addTab(
            "properties",
            $this->lng->txt("settings"),
            $ilCtrl->getLinkTarget($this, "properties")
        );
        $tpl->setTitleIcon(ilUtil::getImagePath("icon_pg.svg"));
        $tpl->setTitle($this->layout_object->getTitle());
        $tpl->setDescription("");
    }

    /**
     * Get template selection radio
     */
    public static function getTemplateSelection(string $module) : ?Radio
    {
        global $DIC;
        $ui = $DIC->ui();
        $f = $ui->factory();
        $lng = $DIC->language();
        $arr_templates = ilPageLayout::activeLayouts($module);
        if (count($arr_templates) == 0) {
            return null;
        }
        $radio = $f->input()->field()->radio($lng->txt("cont_page_template"), "");
        $first = 0;
        /** @var ilPageLayout $templ */
        foreach ($arr_templates as $templ) {
            if ($first == 0) {
                $first = $templ->getId();
            }
            $templ->readObject();
            $radio = $radio->withOption($templ->getId(), $templ->getPreview(), $templ->getTitle());
        }
        $radio = $radio->withValue($first);
        return $radio;
    }

    public function finishEditing() : void
    {
        $this->ctrl->redirectByClass("ilpagelayoutadministrationgui", "listLayouts");
    }
}
