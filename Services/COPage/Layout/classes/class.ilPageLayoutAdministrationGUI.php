<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Administration for page layouts
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ilCtrl_Calls ilPageLayoutAdministrationGUI: ilPageLayoutGUI
 * @ingroup ServicesCOPage
 */
class ilPageLayoutAdministrationGUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilToolbarGUI
     */
    protected $toolbar;

    /**
     * @var ilLanguage
     */
    protected $lng;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ILIAS\DI\Container
     */
    protected $DIC;

    /**
     * @var int
     */
    protected $ref_id;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $this->dic = $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->toolbar = $DIC->toolbar();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->ref_id = (int) $_GET["ref_id"];
        $this->tabs = $DIC["ilTabs"];


        include_once("./Services/Style/Content/classes/class.ilContentStyleSettings.php");
        $this->settings = new ilContentStyleSettings();
    }

    /**
     * Execute command
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("listLayouts");

        if ($cmd == "listLayouts") {
            $this->checkPermission("read");
        } else {
            $this->checkPermission("sty_write_page_layout");
        }

        switch ($next_class) {
            case 'ilpagelayoutgui':
                $this->tabs->clearTargets();
                include_once("./Services/COPage/Layout/classes/class.ilPageLayoutGUI.php");
//				$this->tpl->getStandardTemplate();

                $this->tabs->setBackTarget(
                    $this->lng->txt("page_layouts"),
                    $this->ctrl->getLinkTarget($this, "listLayouts")
                );

                $this->ctrl->setReturn($this, "listLayouts");
                if ($this->pg_id != null) {
                    $layout_gui = new ilPageLayoutGUI("stys", $this->pg_id);
                } else {
                    $layout_gui = new ilPageLayoutGUI("stys", $_GET["obj_id"]);
                }
                $layout_gui->setTabs();
                $layout_gui->setEditPreview(true);
                $this->ctrl->saveParameter($this, "obj_id");
                $ret = $this->ctrl->forwardCommand($layout_gui);
                $this->tpl->setContent($ret);
                break;

            default:
                if (in_array($cmd, array("listLayouts", "editPg", "addPageLayout", "cancelCreate", "createPg", "exportLayout",
                    "savePageLayoutTypes", "activate", "deactivate", "importPageLayoutForm", "deletePgl", "cancelDeletePg",
                    "confirmedDeletePg", "importPageLayout"))) {
                    $this->$cmd();
                } else {
                    die("Unknown command " . $cmd);
                }
        }
    }

    /**
     * Check permission
     *
     * @param string $a_perm permission(s)
     * @return bool
     * @throws ilObjectException
     */
    public function checkPermission($a_perm, $a_throw_exc = true)
    {
        if (!$this->rbacsystem->checkAccess($a_perm, $this->ref_id)) {
            if ($a_throw_exc) {
                include_once "Services/Object/exceptions/class.ilObjectException.php";
                throw new ilObjectException($this->lng->txt("permission_denied"));
            }
            return false;
        }
        return true;
    }

    /**
     * view list of page layouts
     */
    public function listLayouts()
    {
        // show toolbar, if write permission is given
        if ($this->checkPermission("sty_write_page_layout", false)) {
            $this->toolbar->addButton(
                $this->lng->txt("sty_add_pgl"),
                $this->ctrl->getLinkTarget($this, "addPageLayout")
            );
            $this->toolbar->addButton(
                $this->lng->txt("sty_import_page_layout"),
                $this->ctrl->getLinkTarget($this, "importPageLayoutForm")
            );
        }

        $oa_tpl = new ilTemplate("tpl.stys_pglayout.html", true, true, "Services/COPage/Layout");

        include_once("./Services/COPage/Layout/classes/class.ilPageLayoutTableGUI.php");
        $pglayout_table = new ilPageLayoutTableGUI($this, "listLayouts");
        $oa_tpl->setVariable("PGLAYOUT_TABLE", $pglayout_table->getHTML());
        $this->tpl->setContent($oa_tpl->get());
    }

    /**
     * Activate layout
     *
     * @param bool $a_activate
     */
    public function activate($a_activate = true)
    {
        if (!isset($_POST["pglayout"])) {
            ilUtil::sendInfo($this->lng->txt("no_checkbox"), true);
        } else {
            ilUtil::sendSuccess($this->lng->txt("sty_opt_saved"), true);
            foreach ($_POST["pglayout"] as $item) {
                $pg_layout = new ilPageLayout($item);
                $pg_layout->activate($a_activate);
            }
        }
        $this->ctrl->redirect($this, "listLayouts");
    }

    /**
     * Deactivate layout
     */
    public function deactivate()
    {
        $this->activate(false);
    }

    /**
     * display deletion confirmation screen
     */
    public function deletePgl()
    {
        if (!isset($_POST["pglayout"])) {
            ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "listLayouts");
        }

        unset($this->data);

        // display confirmation message
        include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt("info_delete_sure"));
        $cgui->setCancel($this->lng->txt("cancel"), "cancelDeletePg");
        $cgui->setConfirm($this->lng->txt("confirm"), "confirmedDeletePg");

        foreach ($_POST["pglayout"] as $id) {
            $pg_obj = new ilPageLayout($id);
            $pg_obj->readObject();

            $caption = ilUtil::getImageTagByType("stys", $this->tpl->tplPath) .
                " " . $pg_obj->getTitle();

            $cgui->addItem("pglayout[]", $id, $caption);
        }

        $this->tpl->setContent($cgui->getHTML());
    }

    /**
     * cancel deletion of Page Layout
     */
    public function cancelDeletePg()
    {
        ilUtil::sendInfo($this->lng->txt("msg_cancel"), true);
        $this->ctrl->redirect($this, "listLayouts");
    }

    /**
     * conform deletion of Page Layout
     */
    public function confirmedDeletePg()
    {
        foreach ($_POST["pglayout"] as $id) {
            $pg_obj = new ilPageLayout($id);
            $pg_obj->delete();
        }

        $this->ctrl->redirect($this, "listLayouts");
    }

    /**
     * @param null|ilPropertyFormGUI $a_form
     */
    public function addPageLayout($a_form = null)
    {
        if (!$a_form) {
            $a_form = $this->initAddPageLayoutForm();
        }

        $this->tpl->setContent($a_form->getHTML());
    }

    /**
     * @return ilPropertyFormGUI
     */
    public function initAddPageLayoutForm()
    {
        $this->lng->loadLanguageModule("content");

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form_gui = new ilPropertyFormGUI();
        $form_gui->setFormAction($this->ctrl->getFormAction($this));
        $form_gui->setTitle($this->lng->txt("sty_create_pgl"));

        $title_input = new ilTextInputGUI($this->lng->txt("title"), "pgl_title");
        $title_input->setSize(50);
        $title_input->setMaxLength(128);
        $title_input->setValue($this->layout_object->title);
        $title_input->setTitle($this->lng->txt("title"));
        $title_input->setRequired(true);

        $desc_input = new ilTextAreaInputGUI($this->lng->txt("description"), "pgl_desc");
        $desc_input->setValue($this->layout_object->description);
        $desc_input->setRows(3);
        $desc_input->setCols(37);

        // special page?
        $options = array(
            "0" => $this->lng->txt("cont_layout_template"),
            "1" => $this->lng->txt("cont_special_page"),
        );
        $si = new ilSelectInputGUI($this->lng->txt("type"), "special_page");
        $si->setOptions($options);

        // modules
        $mods = new ilCheckboxGroupInputGUI($this->lng->txt("modules"), "module");
        // $mods->setRequired(true);
        foreach (ilPageLayout::getAvailableModules() as $mod_id => $mod_caption) {
            $mod = new ilCheckboxOption($mod_caption, $mod_id);
            $mods->addOption($mod);
        }

        $ttype_input = new ilSelectInputGUI($this->lng->txt("sty_based_on"), "pgl_template");

        $arr_templates = ilPageLayout::getLayouts();
        $arr_templates1 = ilPageLayout::getLayouts(false, true);
        foreach ($arr_templates1 as $v) {
            $arr_templates[] = $v;
        }

        $options = array();
        $options['-1'] = $this->lng->txt("none");

        foreach ($arr_templates as $templ) {
            $templ->readObject();
            $key = $templ->getId();
            $value = $templ->getTitle();
            $options[$key] = $value;
        }

        $ttype_input->setOptions($options);
        $ttype_input->setValue(-1);
        $ttype_input->setRequired(true);

        $desc_input->setTitle($this->lng->txt("description"));
        $desc_input->setRequired(false);

        $form_gui->addItem($title_input);
        $form_gui->addItem($desc_input);
        $form_gui->addItem($si);
        $form_gui->addItem($mods);
        $form_gui->addItem($ttype_input);


        $form_gui->addCommandButton("createPg", $this->lng->txt("save"));
        $form_gui->addCommandButton("cancelCreate", $this->lng->txt("cancel"));

        return $form_gui;
    }


    public function createPg()
    {
        $form_gui = $this->initAddPageLayoutForm();
        if (!$form_gui->checkInput()) {
            $form_gui->setValuesByPost();
            $this->addPageLayout($form_gui);
            return;
        }

        //create Page-Layout-Object first
        $pg_object = new ilPageLayout();
        $pg_object->setTitle($form_gui->getInput('pgl_title'));
        $pg_object->setDescription($form_gui->getInput('pgl_desc'));
        $pg_object->setSpecialPage($form_gui->getInput('special_page'));
        $pg_object->setModules($form_gui->getInput('module'));
        $pg_object->update();

        include_once("./Services/COPage/Layout/classes/class.ilPageLayoutPage.php");

        //create Page
        if (!is_object($pg_content)) {
            $this->pg_content = new ilPageLayoutPage();
        }

        $this->pg_content->setId($pg_object->getId());

        $tmpl = $form_gui->getInput('pgl_template');
        if ($tmpl != "-1") {
            $layout_obj = new ilPageLayout($tmpl);
            $this->pg_content->setXMLContent($layout_obj->getXMLContent());
            $this->pg_content->create(false);
        } else {
            $this->pg_content->create(false);
        }

        $this->ctrl->setParameterByClass("ilpagelayoutgui", "obj_id", $pg_object->getId());
        $this->ctrl->redirectByClass("ilpagelayoutgui", "edit");
    }

    /**
     * Cancel creation
     */
    public function cancelCreate()
    {
        $this->listLayouts();
    }

    /**
     * Edit page
     */
    public function editPg()
    {
        $this->checkPermission("sty_write_page_layout");

        $this->ctrl->setCmdClass("ilpagelayoutgui");
        $this->ctrl->setCmd("edit");
        $this->executeCommand();
    }

    /**
     * Save page layout types
     */
    public function savePageLayoutTypes()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        include_once("./Services/COPage/Layout/classes/class.ilPageLayout.php");

        if (is_array($_POST["type"])) {
            foreach ($_POST["type"] as $id => $t) {
                if ($id > 0) {
                    $l = new ilPageLayout($id);
                    $l->readObject();
                    $l->setSpecialPage($t);
                    if (is_array($_POST["module"][$id])) {
                        $l->setModules(array_keys($_POST["module"][$id]));
                    } else {
                        $l->setModules();
                    }
                    $l->update();
                }
            }

            ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"));
        }

        $this->ctrl->redirect($this, "listLayouts");
    }


    /**
     * Export page layout template object
     */
    public function exportLayout()
    {
        include_once("./Services/Export/classes/class.ilExport.php");
        $exp = new ilExport();

        $tmpdir = ilUtil::ilTempnam();
        ilUtil::makeDir($tmpdir);

        $succ = $exp->exportEntity(
            "pgtp",
            (int) $_GET["layout_id"],
            "4.2.0",
            "Services/COPage",
            "Title",
            $tmpdir
        );

        if ($succ["success"]) {
            ilUtil::deliverFile(
                $succ["directory"] . "/" . $succ["file"],
                $succ["file"],
                "",
                false,
                false,
                false
            );
        }
        if (is_file($succ["directory"] . "/" . $succ["file"])) {
            unlink($succ["directory"] . "/" . $succ["file"]);
        }
        if (is_dir($succ["directory"])) {
            unlink($succ["directory"]);
        }
    }

    /**
     * Import page layout
     */
    public function importPageLayoutForm()
    {
        $form = $this->initPageLayoutImportForm();
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Init page layout import form.
     */
    public function initPageLayoutImportForm()
    {
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();

        // template file
        $fi = new ilFileInputGUI($this->lng->txt("file"), "file");
        $fi->setSuffixes(array("zip"));
        $fi->setRequired(true);
        $form->addItem($fi);

        $form->addCommandButton("importPageLayout", $this->lng->txt("import"));
        $form->addCommandButton("listLayouts", $this->lng->txt("cancel"));

        $form->setTitle($this->lng->txt("sty_import_page_layout"));
        $form->setFormAction($this->ctrl->getFormAction($this));

        return $form;
    }

    /**
     * Import page layout
     */
    public function importPageLayout()
    {
        $form = $this->initPageLayoutImportForm();
        if ($form->checkInput()) {
            include_once("./Services/COPage/Layout/classes/class.ilPageLayout.php");
            $pg = ilPageLayout::import($_FILES["file"]["name"], $_FILES["file"]["tmp_name"]);
            if ($pg > 0) {
                ilUtil::sendSuccess($this->lng->txt("sty_imported_layout"), true);
            }
            $this->ctrl->redirect($this, "listLayouts");
        } else {
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHtml());
        }
    }
}
