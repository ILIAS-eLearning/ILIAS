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

use ILIAS\COPage\Layout\AdministrationGUIRequest;
use ILIAS\DI\UIServices;

/**
 * Administration for page layouts
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilPageLayoutAdministrationGUI: ilPageLayoutGUI
 */
class ilPageLayoutAdministrationGUI
{
    protected AdministrationGUIRequest $admin_request;
    protected ?int $pg_id = null;
    protected ilContentStyleSettings $settings;
    protected ilTabsGUI $tabs;
    protected ilPageLayoutPage $pg_content;
    protected ilCtrl $ctrl;
    protected ilRbacSystem $rbacsystem;
    protected ilToolbarGUI $toolbar;
    protected ilLanguage $lng;
    protected ilGlobalTemplateInterface $tpl;
    protected ILIAS\DI\Container $DIC;
    protected int $ref_id;

    public function __construct()
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->toolbar = $DIC->toolbar();
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->tabs = $DIC->tabs();

        $this->settings = new ilContentStyleSettings();
        $this->admin_request = $DIC
            ->copage()
            ->internal()
            ->gui()
            ->layout()
            ->adminRequest();
        $this->ref_id = $this->admin_request->getRefId();
    }

    public function executeCommand(): void
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
                $this->tabs->setBackTarget(
                    $this->lng->txt("page_layouts"),
                    $this->ctrl->getLinkTarget($this, "listLayouts")
                );

                $this->ctrl->setReturn($this, "listLayouts");
                if ($this->pg_id != null) {
                    $layout_gui = new ilPageLayoutGUI("stys", $this->pg_id);
                } else {
                    $layout_gui = new ilPageLayoutGUI(
                        "stys",
                        $this->admin_request->getObjId()
                    );
                }
                $layout_gui->setTabs();
                $layout_gui->setEditPreview(true);
                $this->ctrl->saveParameter($this, "obj_id");
                $ret = $this->ctrl->forwardCommand($layout_gui);
                if ($ret != "") {
                    $this->tpl->setContent($ret);
                }
                break;

            default:
                if (in_array($cmd, array("listLayouts", "editPg", "addPageLayout", "cancelCreate", "createPg", "exportLayout",
                    "activate", "deactivate", "importPageLayoutForm", "deletePgl", "cancelDeletePg",
                    "confirmedDeletePg", "importPageLayout"))) {
                    $this->$cmd();
                } else {
                    die("Unknown command " . $cmd);
                }
        }
    }

    /**
     * Check permission
     * @throws ilObjectException
     */
    public function checkPermission(
        string $a_perm,
        bool $a_throw_exc = true
    ): bool {
        if (!$this->rbacsystem->checkAccess($a_perm, $this->ref_id)) {
            if ($a_throw_exc) {
                throw new ilObjectException($this->lng->txt("permission_denied"));
            }
            return false;
        }
        return true;
    }

    public function listLayouts(): void
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

        $pglayout_table = new ilPageLayoutTableGUI($this, "listLayouts");
        $oa_tpl->setVariable("PGLAYOUT_TABLE", $pglayout_table->getHTML());
        $this->tpl->setContent($oa_tpl->get());
    }

    public function activate(
        bool $a_activate = true
    ): void {
        $ids = $this->admin_request->getLayoutIds();
        if (count($ids) == 0) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("no_checkbox"), true);
        } else {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("sty_opt_saved"), true);
            foreach ($ids as $item) {
                $pg_layout = new ilPageLayout($item);
                $pg_layout->activate($a_activate);
            }
        }
        $this->ctrl->redirect($this, "listLayouts");
    }

    public function deactivate(): void
    {
        $this->activate(false);
    }

    /**
     * display deletion confirmation screen
     */
    public function deletePgl(): void
    {
        $ids = $this->admin_request->getLayoutIds();
        if (count($ids) == 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "listLayouts");
        }

        unset($this->data);

        // display confirmation message
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt("info_delete_sure"));
        $cgui->setCancel($this->lng->txt("cancel"), "cancelDeletePg");
        $cgui->setConfirm($this->lng->txt("confirm"), "confirmedDeletePg");

        foreach ($ids as $id) {
            $pg_obj = new ilPageLayout($id);
            $pg_obj->readObject();

            $caption = $pg_obj->getTitle();

            $cgui->addItem("pglayout[]", $id, $caption);
        }

        $this->tpl->setContent($cgui->getHTML());
    }

    /**
     * cancel deletion of Page Layout
     */
    public function cancelDeletePg(): void
    {
        $this->tpl->setOnScreenMessage('info', $this->lng->txt("msg_cancel"), true);
        $this->ctrl->redirect($this, "listLayouts");
    }

    /**
     * conform deletion of Page Layout
     */
    public function confirmedDeletePg(): void
    {
        $ids = $this->admin_request->getLayoutIds();
        foreach ($ids as $id) {
            $pg_obj = new ilPageLayout($id);
            $pg_obj->delete();
        }

        $this->ctrl->redirect($this, "listLayouts");
    }

    public function addPageLayout(ilPropertyFormGUI $a_form = null): void
    {
        if (!$a_form) {
            $a_form = $this->initAddPageLayoutForm();
        }
        $this->tpl->setContent($a_form->getHTML());
    }

    public function initAddPageLayoutForm(): ilPropertyFormGUI
    {
        $this->lng->loadLanguageModule("content");

        $form_gui = new ilPropertyFormGUI();
        $form_gui->setFormAction($this->ctrl->getFormAction($this));
        $form_gui->setTitle($this->lng->txt("sty_create_pgl"));

        $title_input = new ilTextInputGUI($this->lng->txt("title"), "pgl_title");
        $title_input->setSize(50);
        $title_input->setMaxLength(128);
        //$title_input->setValue($this->layout_object->title);
        $title_input->setTitle($this->lng->txt("title"));
        $title_input->setRequired(true);

        $desc_input = new ilTextAreaInputGUI($this->lng->txt("description"), "pgl_desc");
        //$desc_input->setValue($this->layout_object->description);
        $desc_input->setRows(3);
        $desc_input->setCols(37);


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
        $form_gui->addItem($mods);
        $form_gui->addItem($ttype_input);


        $form_gui->addCommandButton("createPg", $this->lng->txt("save"));
        $form_gui->addCommandButton("cancelCreate", $this->lng->txt("cancel"));

        return $form_gui;
    }


    public function createPg(): void
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
        $pg_object->setModules($form_gui->getInput('module'));
        $pg_object->update();

        //create Page
        //if (!is_object($pg_content)) {
        $this->pg_content = new ilPageLayoutPage();
        //}

        $this->pg_content->setId($pg_object->getId());

        $tmpl = $form_gui->getInput('pgl_template');
        if ($tmpl != "-1") {
            $layout_obj = new ilPageLayout($tmpl);
            $this->pg_content->setXMLContent($layout_obj->getXMLContent());
        }
        $this->pg_content->create(false);

        $this->ctrl->setParameterByClass("ilpagelayoutgui", "obj_id", $pg_object->getId());
        $this->ctrl->redirectByClass("ilpagelayoutgui", "edit");
    }

    public function cancelCreate(): void
    {
        $this->listLayouts();
    }

    public function editPg(): void
    {
        $this->checkPermission("sty_write_page_layout");

        $this->ctrl->setCmdClass("ilpagelayoutgui");
        $this->ctrl->setCmd("edit");
        $this->executeCommand();
    }


    /**
     * Export page layout template object
     */
    public function exportLayout(): void
    {
        $exp = new ilExport();

        $tmpdir = ilFileUtils::ilTempnam();
        ilFileUtils::makeDir($tmpdir);

        $succ = $exp->exportEntity(
            "pgtp",
            $this->admin_request->getLayoutId(),
            "4.2.0",
            "Services/COPage",
            "Title",
            $tmpdir
        );

        if (is_file($succ["directory"] . "/" . $succ["file"])) {
            ilFileDelivery::deliverFileLegacy(
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
            //unlink($succ["directory"]);
        }
    }

    /**
     * Import page layout
     */
    public function importPageLayoutForm(): void
    {
        $form = $this->initPageLayoutImportForm();
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * Init page layout import form.
     */
    public function initPageLayoutImportForm(): ilPropertyFormGUI
    {
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
    public function importPageLayout(): void
    {
        $form = $this->initPageLayoutImportForm();
        if ($form->checkInput()) {
            ilPageLayout::import($_FILES["file"]["name"], $_FILES["file"]["tmp_name"]);
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("sty_imported_layout"), true);
            $this->ctrl->redirect($this, "listLayouts");
        } else {
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());
        }
    }
}
