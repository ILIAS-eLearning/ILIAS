<?php

declare(strict_types=1);

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

use ILIAS\Style\Content\Access;
use ILIAS\Style\Content;

/**
 * Class ilObjStyleSheetGUI
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilObjStyleSheetGUI: ilExportGUI, ilStyleCharacteristicGUI, ilContentStyleImageGUI
 */
class ilObjStyleSheetGUI extends ilObjectGUI
{
    protected ilPropertyFormGUI $form_gui;
    protected ilPropertyFormGUI $form;
    protected Content\StandardGUIRequest $style_request;
    protected Content\InternalService $service;
    protected ilHelpGUI $help;
    protected ilObjectDefinition $obj_definition;
    public string $cmd_update;
    public string $cmd_new_par;
    public string $cmd_delete;
    protected bool $enable_write = false;
    protected string $super_type;
    protected Access\StyleAccessManager $access_manager;
    protected Content\CharacteristicManager $characteristic_manager;
    protected Content\ColorManager $color_manager;
    protected Content\ImageManager $image_manager;
    protected Content\InternalGUIService $gui_service;


    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference
    ) {
        global $DIC;

        parent::__construct($a_data, $a_id, $a_call_by_reference, false);

        $this->service = $DIC->contentStyle()
            ->internal();
        $this->gui_service = $this->service->gui();
        $this->help = $this->gui_service->help();
        $this->lng->loadLanguageModule("style");
        $this->ctrl->saveParameter($this, array("tag", "style_type", "temp_type"));
        $this->style_request = $DIC->contentStyle()
            ->internal()
            ->gui()
            ->standardRequest();

        $this->super_type = "";
        if ($this->style_request->getStyleType() != "") {
            $this->super_type = ilObjStyleSheet::_getStyleSuperTypeForType(
                $this->style_request->getStyleType()
            );
        }
        $this->type = "sty";

        $ref_id = (is_object($this->object))
            ? $this->object->getRefId()
            : 0;
        $style_id = (is_object($this->object))
            ? $this->object->getId()
            : 0;

        $this->access_manager = $this->service->domain()->access(
            $ref_id,
            $this->user->getId()
        );
        $this->characteristic_manager = $this->service->domain()->characteristic(
            $style_id,
            $this->access_manager
        );
        $this->color_manager = $this->service->domain()->color(
            $style_id,
            $this->access_manager
        );
        $this->image_manager = $this->service->domain()->image(
            $style_id,
            $this->access_manager
        );
    }

    /**
     * Enable writing
     */
    public function enableWrite(bool $a_write): void
    {
        $this->access_manager->enableWrite($a_write);
    }

    public function executeCommand(): void
    {
        $tabs = $this->gui_service->tabs();
        $ctrl = $this->gui_service->ctrl();

        $next_class = $ctrl->getNextClass($this);
        $cmd = $ctrl->getCmd("edit");

        // #9440/#9489: prepareOutput will fail if not set properly
        if (!$this->object || $cmd == "create") {
            $this->setCreationMode();
        }

        switch ($next_class) {

            case "ilexportgui":
                $this->prepareOutput();
                $exp_gui = new ilExportGUI($this);
                $exp_gui->addFormat("xml");
                $ctrl->forwardCommand($exp_gui);
                break;

            case "ilstylecharacteristicgui":
                $this->prepareOutput();
                $this->includeCSS();
                $tabs->activateTab("sty_style_chars");
                $gui = $this->gui_service->characteristic()->ilStyleCharacteristicGUI(
                    $this->getStyleSheet(),
                    $this->super_type,
                    $this->access_manager,
                    $this->characteristic_manager,
                    $this->image_manager
                );
                $ctrl->forwardCommand($gui);
                break;

            case "ilcontentstyleimagegui":
                $this->prepareOutput();
                $this->includeCSS();
                $tabs->activateTab("sty_images");
                $gui = $this->gui_service->image()->ilContentStyleImageGUI(
                    $this->access_manager,
                    $this->image_manager
                );
                $ctrl->forwardCommand($gui);
                break;

            default:
                $this->prepareOutput();
                $cmd .= "Object";
                $this->$cmd();
                break;
        }
    }

    protected function getStyleSheet(): ilObjStyleSheet
    {
        /** @var ilObjStyleSheet $obj */
        $obj = $this->object;
        return $obj;
    }

    public function viewObject(): void
    {
        $this->editObject();
    }

    public function createObject(): void
    {
        $tpl = $this->gui_service->ui()->mainTemplate();

        $ilHelp = $this->help;

        $forms = array();


        $ilHelp->setScreenIdComponent("sty");
        $ilHelp->setDefaultScreenId(ilHelpGUI::ID_PART_SCREEN, "create");

        $forms[] = $this->getCreateForm();
        $forms[] = $this->getImportForm();
        $forms[] = $this->getCloneForm();

        $tpl->setContent($this->getCreationFormsHTML($forms));
    }

    protected function getCreateForm(): ilPropertyFormGUI
    {
        $ctrl = $this->ctrl;
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("sty_create_new_stylesheet"));

        // title
        $ti = new ilTextInputGUI($this->lng->txt("title"), "style_title");
        $ti->setMaxLength(128);
        $ti->setSize(40);
        $ti->setRequired(true);
        $form->addItem($ti);

        // description
        $ta = new ilTextAreaInputGUI($this->lng->txt("description"), "style_description");
        $ta->setCols(40);
        $ta->setRows(2);
        $form->addItem($ta);

        $form->addCommandButton("save", $this->lng->txt("save"));
        $form->addCommandButton("cancel", $this->lng->txt("cancel"));
        return $form;
    }

    protected function getImportForm(): ilPropertyFormGUI
    {
        $ctrl = $this->ctrl;
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("sty_import_stylesheet"));

        // title
        $ti = new ilFileInputGUI($this->lng->txt("import_file"), "importfile");
        $ti->setRequired(true);
        $form->addItem($ti);

        $form->addCommandButton("importStyle", $this->lng->txt("import"));
        $form->addCommandButton("cancel", $this->lng->txt("cancel"));
        return $form;
    }

    protected function getCloneForm(): ilPropertyFormGUI
    {
        $ctrl = $this->ctrl;
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("sty_copy_other_stylesheet"));

        // source
        $ti = new ilSelectInputGUI($this->lng->txt("sty_source"), "source_style");
        $ti->setRequired(true);
        $ti->setOptions(ilObjStyleSheet::_getClonableContentStyles());
        $form->addItem($ti);

        $form->addCommandButton("copyStyle", $this->lng->txt("copy"));
        $form->addCommandButton("cancel", $this->lng->txt("cancel"));
        return $form;
    }

    public function includeCSS(): void
    {
        $tpl = $this->gui_service->ui()->mainTemplate();
        $tpl->addCss(ilObjStyleSheet::getContentStylePath($this->object->getId()));
    }


    /**
     * Edit -> List characteristics
     */
    public function editObject(): void
    {
        $ctrl = $this->gui_service->ctrl();
        $ctrl->redirectByClass("ilStyleCharacteristicGUI", "listCharacteristics");
    }

    public function propertiesObject(): void
    {
        $tpl = $this->gui_service->ui()->mainTemplate();
        $tpl->addCss(ilObjStyleSheet::getContentStylePath($this->object->getId()));

        $this->initPropertiesForm();
        $this->getPropertiesValues();
        $tpl->setContent($this->form->getHTML());
    }

    /**
     * Get current values for properties from
     */
    public function getPropertiesValues(): void
    {
        $values = array();

        $values["style_title"] = $this->object->getTitle();
        $values["style_description"] = $this->object->getDescription();
        $values["disable_auto_margins"] = (int) $this->object->lookupStyleSetting("disable_auto_margins");

        $this->form->setValuesByArray($values);
    }

    /**
     * FORM: Init properties form.
     */
    public function initPropertiesForm(
        string $a_mode = "edit"
    ): void {
        $ctrl = $this->gui_service->ctrl();
        $lng = $this->lng;

        $this->form = new ilPropertyFormGUI();

        // title
        $ti = new ilTextInputGUI($this->lng->txt("title"), "style_title");
        $ti->setMaxLength(128);
        $ti->setSize(40);
        $ti->setRequired(true);
        $this->form->addItem($ti);

        // description
        $ta = new ilTextAreaInputGUI($this->lng->txt("description"), "style_description");
        //$ta->setCols();
        //$ta->setRows();
        $this->form->addItem($ta);

        // disable automatic margins for left/right alignment
        $cb = new ilCheckboxInputGUI($this->lng->txt("sty_disable_auto_margins"), "disable_auto_margins");
        $cb->setInfo($this->lng->txt("sty_disable_auto_margins_info"));
        $this->form->addItem($cb);

        // save and cancel commands

        if ($a_mode == "create") {
            $this->form->addCommandButton("save", $lng->txt("save"));
            $this->form->addCommandButton("cancelSave", $lng->txt("cancel"));
        } else {
            if ($this->access_manager->checkWrite()) {
                $this->form->addCommandButton("update", $lng->txt("save"));
            }
        }

        $this->form->setTitle($lng->txt("edit_stylesheet"));
        $this->form->setFormAction($ctrl->getFormAction($this));
    }

    public function updateObject(): void
    {
        $lng = $this->lng;
        $ctrl = $this->gui_service->ctrl();
        $tpl = $this->gui_service->ui()->mainTemplate();

        $this->initPropertiesForm("edit");
        if ($this->form->checkInput()) {
            $this->object->setTitle($this->form->getInput("style_title"));
            $this->object->setDescription($this->form->getInput("style_description"));
            $this->object->writeStyleSetting(
                "disable_auto_margins",
                $this->form->getInput("disable_auto_margins")
            );
            $this->object->update();
            $this->tpl->setOnScreenMessage('info', $lng->txt("msg_obj_modified"), true);
            $ctrl->redirect($this, "properties");
        } else {
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHTML());
        }
    }

    /**
     * Switch media query
     */
    public function switchMQueryObject(): void
    {
        $ctrl = $this->gui_service->ctrl();
        $ctrl->setParameter($this, "mq_id", $this->style_request->getMediaQueryId());
        $ctrl->redirectByClass("ilstylecharacteristicgui", "editTagStyle");
    }

    public function exportStyleObject(): void
    {
        $exp = new ilExport();
        $r = $exp->exportObject($this->object->getType(), $this->object->getId());

        ilFileDelivery::deliverFileLegacy($r["directory"] . "/" . $r["file"], $r["file"], '', false, true);
    }

    /**
     * display deletion confirmation screen
     */
    public function deleteObject($a_error = false): void
    {
        $tpl = $this->gui_service->ui()->mainTemplate();
        $ctrl = $this->gui_service->ctrl();

        // display confirmation message
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt("info_delete_sure"));
        $cgui->setCancel($this->lng->txt("cancel"), "cancelDelete");
        $cgui->setConfirm($this->lng->txt("confirm"), "confirmedDelete");

        $caption = ilObject::_lookupTitle($this->object->getId());

        $cgui->addItem("id[]", "", $caption);

        $tpl->setContent($cgui->getHTML());
    }

    public function cancelDeleteObject(): void
    {
        $this->gui_service->ctrl()->returnToParent($this);
    }

    /**
     * delete selected style objects
     */
    public function confirmedDeleteObject(): void
    {
        $this->object->delete();
        $this->gui_service->ctrl()->returnToParent($this);
    }

    public function saveObject(): void
    {
        $ctrl = $this->gui_service->ctrl();

        $form = $this->getCreateForm();
        $form->checkInput();

        if (!trim($form->getInput("style_title"))) {
            $ctrl->redirect($this, "create");
        }

        // copy from default style or ... see #11330
        $default_style = $this->settings->get("default_content_style_id");
        if (ilObject::_lookupType((int) $default_style) == "sty") {
            $style_obj = ilObjectFactory::getInstanceByObjId((int) $default_style);
            $new_id = $style_obj->ilClone();
            $newObj = new ilObjStyleSheet($new_id);
        } else {
            // ... import from basic zip file
            $imp = new ilImport();
            $style_id = $imp->importObject(
                null,
                ilObjStyleSheet::getBasicZipPath(),
                "style.zip",
                "sty",
                "Services/Style",
                true
            );

            $newObj = new ilObjStyleSheet($style_id);
        }

        $newObj->setTitle($form->getInput("style_title"));
        $newObj->setDescription($form->getInput("style_description"));
        $newObj->update();
        $this->object = $newObj;

        ilObjStyleSheet::_addMissingStyleClassesToStyle($newObj->getId());

        // assign style to style sheet folder,
        // if parent is style sheet folder
        if ($this->requested_ref_id > 0) {
            $fold = ilObjectFactory::getInstanceByRefId($this->requested_ref_id);
            if ($fold->getType() == "stys") {
                $cont_style_settings = new ilContentStyleSettings();
                $cont_style_settings->addStyle($newObj->getId());
                $cont_style_settings->update();

                ilObjStyleSheet::_writeStandard($newObj->getId(), true);
                $ctrl->returnToParent($this);
            }
        }
    }

    public function copyStyleObject(): int
    {
        $ctrl = $this->gui_service->ctrl();

        $form = $this->getCloneForm();
        $form->checkInput();
        $new_id = 0;

        if ($form->getInput("source_style") > 0) {
            $style_obj = ilObjectFactory::getInstanceByObjId((int) $form->getInput("source_style"));
            $new_id = $style_obj->ilClone();
        }

        // assign style to style sheet folder,
        // if parent is style sheet folder
        if ($this->requested_ref_id > 0) {
            $fold = ilObjectFactory::getInstanceByRefId($this->requested_ref_id);
            if ($fold->getType() == "stys") {
                $cont_style_settings = new ilContentStyleSettings();
                $cont_style_settings->addStyle($new_id);
                $cont_style_settings->update();
                ilObjStyleSheet::_writeStandard($new_id, true);
                $ctrl->returnToParent($this);
            }
        }
        $this->object = new ilObjStyleSheet($new_id);

        return $new_id;
    }

    /**
     * Import style sheet
     */
    public function importStyleObject(): int
    {
        $newObj = null;
        // check file
        $source = $_FILES["importfile"]["tmp_name"];
        if (($source == 'none') || (!$source)) {
            $this->ilias->raiseError("No file selected!", $this->ilias->error_obj->MESSAGE);
        }

        // check correct file type
        $info = pathinfo($_FILES["importfile"]["name"]);
        if (strtolower($info["extension"]) != "zip" && strtolower($info["extension"]) != "xml") {
            $this->ilias->raiseError("File must be a zip or xml file!", $this->ilias->error_obj->MESSAGE);
        }

        // new import
        $fname = explode("_", $_FILES["importfile"]["name"]);
        if (strtolower($info["extension"]) == "zip" && $fname[4] == "sty") {
            $imp = new ilImport();
            $new_id = $imp->importObject(
                null,
                $_FILES["importfile"]["tmp_name"],
                $_FILES["importfile"]["name"],
                "sty"
            );
            if ($new_id > 0) {
                $newObj = ilObjectFactory::getInstanceByObjId($new_id);
            }
        } else {	// old import
            $newObj = new ilObjStyleSheet();
            $newObj->import($_FILES["importfile"]);
        }

        // assign style to style sheet folder,
        // if parent is style sheet folder
        if ($this->requested_ref_id > 0) {
            $fold = ilObjectFactory::getInstanceByRefId($this->requested_ref_id);
            if ($fold->getType() == "stys") {
                $cont_style_settings = new ilContentStyleSettings();
                $cont_style_settings->addStyle($newObj->getId());
                $cont_style_settings->update();
                ilObjStyleSheet::_writeStandard($newObj->getId(), true);
                $this->ctrl->returnToParent($this);
            }
        }
        $this->object = $newObj;
        return $newObj->getId();
    }


    public function cancelObject(): void
    {
        $lng = $this->lng;

        $this->tpl->setOnScreenMessage('info', $lng->txt("msg_cancel"), true);
        $this->ctrl->returnToParent($this);
    }

    public function getAdminTabs(): void
    {
        $this->getTabs();
    }

    protected function getTabs(): void
    {
        $lng = $this->lng;
        $ilHelp = $this->help;
        $tabs = $this->gui_service->tabs();
        $ilHelp->setScreenIdComponent("sty");
        // back to upper context
        $tabs->setBackTarget(
            $lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "returnToUpperContext")
        );

        // style classes
        $tabs->addTarget(
            "sty_style_chars",
            $this->ctrl->getLinkTarget($this, "edit"),
            array("edit", ""),
            get_class($this)
        );

        // colors
        $tabs->addTarget(
            "sty_colors",
            $this->ctrl->getLinkTarget($this, "listColors"),
            "listColors",
            get_class($this)
        );

        // images
        $tabs->addTarget(
            "sty_images",
            $this->ctrl->getLinkTargetByClass("ilContentStyleImageGUI", ""),
            "",
            "ilContentStyleImageGUI"
        );

        // media queries
        $tabs->addTarget(
            "sty_media_queries",
            $this->ctrl->getLinkTarget($this, "listMediaQueries"),
            "listMediaQueries",
            get_class($this)
        );


        // table templates
        $tabs->addTarget(
            "sty_templates",
            $this->ctrl->getLinkTarget($this, "listTemplates"),
            "listTemplates",
            get_class($this)
        );

        // settings
        $tabs->addTarget(
            "settings",
            $this->ctrl->getLinkTarget($this, "properties"),
            "properties",
            get_class($this)
        );

        // export
        $tabs->addTarget(
            "export",
            $this->ctrl->getLinkTargetByClass("ilexportgui", ""),
            "",
            "ilexportgui"
        );
    }

    public function setTemplatesSubTabs(): void
    {
        $ilTabs = $this->gui_service->tabs();
        $ilCtrl = $this->ctrl;

        $types = ilObjStyleSheet::_getTemplateClassTypes();

        foreach ($types as $t => $c) {
            $ilCtrl->setParameter($this, "temp_type", $t);
            $ilTabs->addSubTabTarget(
                "sty_" . $t . "_templates",
                $this->ctrl->getLinkTarget($this, "listTemplates"),
                array("listTemplates", ""),
                get_class($this)
            );
        }

        $ilCtrl->setParameter($this, "temp_type", $this->style_request->getTempType());
    }

    /**
     * should be overwritten to add object specific items
     * (repository items are preloaded)
     */
    protected function addAdminLocatorItems(bool $do_not_add_object = false): void
    {
        $ilLocator = $this->gui_service->locator();

        if ($this->style_request->getAdminMode() == "settings") {	// system settings
            parent::addAdminLocatorItems(true);

            $ilLocator->addItem(
                $this->lng->txt("obj_stys"),
                $this->ctrl->getLinkTargetByClass("ilobjstylesettingsgui", "")
            );

            $ilLocator->addItem(
                $this->lng->txt("content_styles"),
                $this->ctrl->getLinkTargetByClass("ilcontentstylesettingsgui", "")
            );

            if ($this->style_request->getObjId() > 0) {
                $ilLocator->addItem(
                    $this->object->getTitle(),
                    $this->ctrl->getLinkTarget($this, "edit")
                );
            }
        }
    }

    /**
     * Get style example HTML
     */
    public static function getStyleExampleHTML(
        string $a_type,
        string $a_class
    ): string {
        global $DIC;

        $lng = $DIC->language();

        $c = explode(":", $a_class);
        $a_class = $c[0];

        $ex_tpl = new ilTemplate("tpl.style_example.html", true, true, "Services/Style/Content");

        if ($ex_tpl->blockExists("Example_" . $a_type)) {
            $ex_tpl->setCurrentBlock("Example_" . $a_type);
        } else {
            $ex_tpl->setCurrentBlock("Example_default");
        }
        $ex_tpl->setVariable("EX_CLASS", "ilc_" . $a_type . "_" . $a_class);
        $ex_tpl->setVariable("EX_TEXT", "ABC abc 123");
        if (in_array($a_type, array("media_cont", "qimg"))) {
            //
        }
        if (in_array($a_type, array("table", "table_caption"))) {
            $ex_tpl->setVariable("TXT_CAPTION", $lng->txt("sty_caption"));
        }
        if (in_array($a_class, array("OrderListItemHorizontal", "OrderListHorizontal"))) {
            $ex_tpl->setVariable("HOR", "Horizontal");
        }
        $ex_tpl->parseCurrentBlock();

        return $ex_tpl->get();
    }


    //
    // Color management
    //

    /**
     * List colors of style
     */
    public function listColorsObject(): void
    {
        $tpl = $this->gui_service->ui()->mainTemplate();
        $ilToolbar = $this->gui_service->toolbar();
        $ilCtrl = $this->ctrl;

        if ($this->access_manager->checkWrite()) {
            $ilToolbar->addButton(
                $this->lng->txt("sty_add_color"),
                $ilCtrl->getLinkTarget($this, "addColor")
            );
        }

        $table_gui = new ilStyleColorTableGUI(
            $this,
            "listColors",
            $this->getStyleSheet(),
            $this->access_manager
        );
        $tpl->setContent($table_gui->getHTML());
    }

    public function addColorObject(): void
    {
        $tpl = $this->gui_service->ui()->mainTemplate();

        $this->initColorForm();
        $tpl->setContent($this->form_gui->getHTML());
    }

    public function editColorObject(): void
    {
        $tpl = $this->gui_service->ui()->mainTemplate();
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter($this, "c_name", $this->style_request->getColorName());
        $this->initColorForm("edit");
        $this->getColorFormValues();
        $tpl->setContent($this->form_gui->getHTML());
    }


    public function initColorForm(
        string $a_mode = "create"
    ): void {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->form_gui = new ilPropertyFormGUI();

        $this->form_gui->setTitle($lng->txt("sty_add_color"));

        // name
        $name_input = new ilRegExpInputGUI($lng->txt("sty_color_name"), "color_name");
        $name_input->setPattern("/^[a-zA-Z]+[a-zA-Z0-9]*$/");
        $name_input->setNoMatchMessage($lng->txt("sty_msg_color_must_only_include") . " A-Z, a-z, 1-9");
        $name_input->setRequired(true);
        $name_input->setSize(15);
        $name_input->setMaxLength(15);
        $this->form_gui->addItem($name_input);

        // code
        $color_input = new ilColorPickerInputGUI($lng->txt("sty_color_code"), "color_code");
        $color_input->setRequired(true);
        $color_input->setDefaultColor("");
        $this->form_gui->addItem($color_input);

        if ($a_mode == "create") {
            $this->form_gui->addCommandButton("saveColor", $lng->txt("save"));
        } else {
            $this->form_gui->addCommandButton("updateColor", $lng->txt("save"));
        }
        $this->form_gui->addCommandButton("cancelColorSaving", $lng->txt("cancel"));
        $this->form_gui->setFormAction($ilCtrl->getFormAction($this));
    }

    /**
     * Set values for color editing
     */
    public function getColorFormValues(): void
    {
        $c_name = $this->style_request->getColorName();
        if ($c_name != "") {
            $values["color_name"] = $c_name;
            $values["color_code"] = $this->object->getColorCodeForName($c_name);
            $this->form_gui->setValuesByArray($values);
        }
    }

    /**
     * Cancel color saving
     */
    public function cancelColorSavingObject(): void
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->redirect($this, "listColors");
    }

    /**
     * Save color
     */
    public function saveColorObject(): void
    {
        $tpl = $this->gui_service->ui()->mainTemplate();
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $this->initColorForm();
        $this->form_gui->checkInput();

        if ($this->form_gui->checkInput()) {
            if ($this->color_manager->colorExists($this->form_gui->getInput("color_name"))) {
                $col_input = $this->form_gui->getItemByPostVar("color_name");
                $col_input->setAlert($lng->txt("sty_color_already_exists"));
            } else {
                $this->color_manager->addColor(
                    $this->form_gui->getInput("color_name"),
                    $this->form_gui->getInput("color_code")
                );
                $ilCtrl->redirect($this, "listColors");
            }
        }
        $this->form_gui->setValuesByPost();
        $tpl->setContent($this->form_gui->getHTML());
    }

    /**
     * Update color
     */
    public function updateColorObject(): void
    {
        $tpl = $this->gui_service->ui()->mainTemplate();
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $this->initColorForm("edit");

        $c_name = $this->style_request->getColorName();
        if ($this->form_gui->checkInput()) {
            if ($this->color_manager->colorExists($this->form_gui->getInput("color_name")) &&
                $this->form_gui->getInput("color_name") != $c_name) {
                $col_input = $this->form_gui->getItemByPostVar("color_name");
                $col_input->setAlert($lng->txt("sty_color_already_exists"));
            } else {
                $this->color_manager->updateColor(
                    $c_name,
                    $this->form_gui->getInput("color_name"),
                    $this->form_gui->getInput("color_code")
                );
                $ilCtrl->redirect($this, "listColors");
            }
        }
        $ilCtrl->setParameter($this, "c_name", $c_name);
        $this->form_gui->setValuesByPost();
        $tpl->setContent($this->form_gui->getHTML());
    }

    /**
     * Delete color confirmation
     */
    public function deleteColorConfirmationObject(): void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->gui_service->ui()->mainTemplate();
        $lng = $this->lng;

        $colors = $this->style_request->getColors();
        if (count($colors) == 0) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listColors");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("sty_confirm_color_deletion"));
            $cgui->setCancel($lng->txt("cancel"), "cancelColorDeletion");
            $cgui->setConfirm($lng->txt("delete"), "deleteColor");

            foreach ($colors as $c) {
                $cgui->addItem("color[]", ilLegacyFormElementsUtil::prepareFormOutput($c), $c);
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Cancel color deletion
     */
    public function cancelColorDeletionObject(): void
    {
        $ilCtrl = $this->ctrl;
        $ilCtrl->redirect($this, "listColors");
    }

    public function deleteColorObject(): void
    {
        $ilCtrl = $this->ctrl;

        $colors = $this->style_request->getColors();
        foreach ($colors as $c) {
            $this->object->removeColor($c);
        }

        $ilCtrl->redirect($this, "listColors");
    }

    //
    // Media query management
    //

    public function listMediaQueriesObject(): void
    {
        $tpl = $this->gui_service->ui()->mainTemplate();
        $ilToolbar = $this->gui_service->toolbar();
        $ilCtrl = $this->ctrl;

        if ($this->access_manager->checkWrite()) {
            $ilToolbar->addButton(
                $this->lng->txt("sty_add_media_query"),
                $ilCtrl->getLinkTarget($this, "addMediaQuery")
            );
        }

        $table_gui = new ilStyleMediaQueryTableGUI(
            $this,
            "listMediaQueries",
            $this->getStyleSheet(),
            $this->access_manager
        );
        $tpl->setContent($table_gui->getHTML());
    }

    public function addMediaQueryObject(): void
    {
        $tpl = $this->gui_service->ui()->mainTemplate();

        $this->initMediaQueryForm();
        $tpl->setContent($this->form_gui->getHTML());
    }

    public function editMediaQueryObject(): void
    {
        $tpl = $this->gui_service->ui()->mainTemplate();
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter($this, "mq_id", $this->style_request->getMediaQueryId());
        $this->initMediaQueryForm("edit");
        $this->getMediaQueryFormValues();
        $tpl->setContent($this->form_gui->getHTML());
    }


    /**
     * Init media query form
     */
    public function initMediaQueryForm(
        string $a_mode = "create"
    ): void {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->form_gui = new ilPropertyFormGUI();

        $this->form_gui->setTitle($lng->txt("sty_add_media_query"));

        // media query
        $ti = new ilTextInputGUI("@media", "mquery");
        $ti->setMaxLength(2000);
        $ti->setInfo($lng->txt("sty_add_media_query_info"));
        $this->form_gui->addItem($ti);


        if ($a_mode == "create") {
            $this->form_gui->addCommandButton("saveMediaQuery", $lng->txt("save"));
        } else {
            $this->form_gui->addCommandButton("updateMediaQuery", $lng->txt("save"));
        }
        $this->form_gui->addCommandButton("listMediaQueries", $lng->txt("cancel"));
        $this->form_gui->setFormAction($ilCtrl->getFormAction($this));
    }

    /**
     * Set values for media query editing
     */
    public function getMediaQueryFormValues(): void
    {
        $values = [];
        if ($this->style_request->getMediaQueryId() > 0) {
            foreach ($this->object->getMediaQueries() as $mq) {
                if ($mq["id"] == $this->style_request->getMediaQueryId()) {
                    $values["mquery"] = $mq["mquery"];
                }
            }
            $this->form_gui->setValuesByArray($values);
        }
    }

    /**
     * Save media query
     */
    public function saveMediaQueryObject(): void
    {
        $tpl = $this->gui_service->ui()->mainTemplate();
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $this->initMediaQueryForm();

        if ($this->form_gui->checkInput()) {
            $this->object->addMediaQuery($this->form_gui->getInput("mquery"));
            $ilCtrl->redirect($this, "listMediaQueries");
        }
        $this->form_gui->setValuesByPost();
        $tpl->setContent($this->form_gui->getHTML());
    }

    public function updateMediaQueryObject(): void
    {
        $tpl = $this->gui_service->ui()->mainTemplate();
        $ilCtrl = $this->ctrl;

        $this->initMediaQueryForm("edit");

        if ($this->form_gui->checkInput()) {
            $this->object->updateMediaQuery(
                $this->style_request->getMediaQueryId(),
                $this->form_gui->getInput("mquery")
            );
            $ilCtrl->redirect($this, "listMediaQueries");
        }
        $ilCtrl->setParameter($this, "mq_id", $this->style_request->getMediaQueryId());
        $this->form_gui->setValuesByPost();
        $tpl->setContent($this->form_gui->getHTML());
    }

    public function deleteMediaQueryConfirmationObject(): void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->gui_service->ui()->mainTemplate();
        $lng = $this->lng;

        $mq_ids = $this->style_request->getMediaQueryIds();
        if (count($mq_ids) == 0) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listMediaQueries");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("sty_sure_del_mqueries"));
            $cgui->setCancel($lng->txt("cancel"), "listMediaQueries");
            $cgui->setConfirm($lng->txt("delete"), "deleteMediaQueries");

            foreach ($mq_ids as $i) {
                $mq = $this->object->getMediaQueryForId($i);
                $cgui->addItem("mq_id[]", $i, $mq["mquery"]);
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    public function deleteMediaQueriesObject(): void
    {
        $ilCtrl = $this->ctrl;

        $mq_ids = $this->style_request->getMediaQueryIds();
        if ($this->access_manager->checkWrite()) {
            foreach ($mq_ids as $id) {
                $this->object->deleteMediaQuery($id);
            }
        }
        $ilCtrl->redirect($this, "listMediaQueries");
    }

    public function saveMediaQueryOrderObject(): void
    {
        $ilCtrl = $this->ctrl;

        $order = $this->style_request->getOrder();
        if (count($order) > 0) {
            $this->getStyleSheet()->saveMediaQueryOrder($order);
        }
        $ilCtrl->redirect($this, "listMediaQueries");
    }


    //
    // Templates management
    //

    /**
     * List templates
     */
    public function listTemplatesObject(): void
    {
        $tpl = $this->gui_service->ui()->mainTemplate();
        $ilTabs = $this->gui_service->tabs();
        $ilCtrl = $this->ctrl;
        $ilToolbar = $this->gui_service->toolbar();

        $ctype = $this->style_request->getTempType();
        if ($ctype == "") {
            $ctype = "table";
            $ilCtrl->setParameter($this, "temp_type", $ctype);
        }

        $this->setTemplatesSubTabs();
        $ilTabs->setSubTabActive("sty_" . $ctype . "_templates");

        // action commands
        if ($this->access_manager->checkWrite()) {
            if ($ctype == "table") {
                $ilToolbar->addButton(
                    $this->lng->txt("sty_generate_template"),
                    $ilCtrl->getLinkTarget($this, "generateTemplate")
                );
            }
            $ilToolbar->addButton(
                $this->lng->txt("sty_add_template"),
                $ilCtrl->getLinkTarget($this, "addTemplate")
            );
        }



        $this->includeCSS();
        $table_gui = new ilTableTemplatesTableGUI(
            $ctype,
            $this,
            "listTemplates",
            $this->getStyleSheet(),
            $this->access_manager
        );
        $tpl->setContent($table_gui->getHTML());
    }

    public function addTemplateObject(): void
    {
        $tpl = $this->gui_service->ui()->mainTemplate();

        $this->initTemplateForm();
        $tpl->setContent($this->form_gui->getHTML());
    }

    public function editTemplateObject(): void
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter(
            $this,
            "t_id",
            $this->style_request->getTemplateId()
        );
        $this->initTemplateForm("edit");
        $this->getTemplateFormValues();

        $this->displayTemplateEditForm();
    }

    public function getTemplatePreview(
        string $a_type,
        int $a_t_id,
        bool $a_small_mode = false
    ): string {
        return $this->_getTemplatePreview(
            $this->getStyleSheet(),
            $a_type,
            $a_t_id,
            $a_small_mode
        );
    }

    /**
     * Get table template preview
     */
    public static function _getTemplatePreview(
        ilObjStyleSheet $a_style,
        string $a_type,
        int $a_t_id,
        bool $a_small_mode = false
    ): string {
        global $DIC;

        $lng = $DIC->language();
        $p_content = "";

        $kr = $kc = 7;
        if ($a_small_mode) {
            $kr = 6;
            $kc = 5;
        }

        $ts = $a_style->getTemplate($a_t_id);
        $t = $ts["classes"];

        // preview
        if ($a_type == "table") {
            $p_content = '<PageContent><Table DataTable="y"';
            $t["row_head"] = $t["row_head"] ?? "";
            $t["row_foot"] = $t["row_foot"] ?? "";
            $t["col_head"] = $t["col_head"] ?? "";
            $t["col_foot"] = $t["col_foot"] ?? "";
            if ($t["row_head"] != "") {
                $p_content .= ' HeaderRows="1"';
            }
            if ($t["row_foot"] != "") {
                $p_content .= ' FooterRows="1"';
            }
            if ($t["col_head"] != "") {
                $p_content .= ' HeaderCols="1"';
            }
            if ($t["col_foot"] != "") {
                $p_content .= ' FooterCols="1"';
            }
            $p_content .= ' Template="' . $a_style->lookupTemplateName($a_t_id) . '">';
            if (!$a_small_mode) {
                $p_content .= '<Caption>' . $lng->txt("sty_caption") . '</Caption>';
            }
            for ($i = 1; $i <= $kr; $i++) {
                $p_content .= '<TableRow>';
                for ($j = 1; $j <= $kc; $j++) {
                    if ($a_small_mode) {
                        $cell = '&lt;div style="height:2px;"&gt;&lt;/div&gt;';
                    } else {
                        $cell = 'xxx';
                    }
                    $p_content .= '<TableData><PageContent><Paragraph Characteristic="TableContent">' . $cell . '</Paragraph></PageContent></TableData>';
                }
                $p_content .= '</TableRow>';
            }
            $p_content .= '</Table></PageContent>';
        }

        if ($a_type == "vaccordion" || $a_type == "haccordion" || $a_type == "carousel") {
            ilAccordionGUI::addCss();

            if ($a_small_mode) {
                $c = '&amp;nbsp;';
                $h = '&amp;nbsp;';
            } else {
                $c = 'xxx';
                $h = 'head';
            }
            if ($a_type == "vaccordion") {
                $p_content = '<PageContent><Tabs HorizontalAlign="Left" Type="VerticalAccordion" ';
                if ($a_small_mode) {
                    $p_content .= ' ContentWidth="70"';
                }
            } elseif ($a_type == "haccordion") {
                $p_content = '<PageContent><Tabs Type="HorizontalAccordion"';
                $p_content .= ' ContentHeight="40"';
                if ($a_small_mode) {
                    $p_content .= ' ContentWidth="70"';
                    $c = '&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;';
                }
            } elseif ($a_type == "carousel") {
                $p_content = '<PageContent><Tabs HorizontalAlign="Left" Type="Carousel" ';
                if ($a_small_mode) {
                    $p_content .= ' ContentWidth="70"';
                }
            }


            $p_content .= ' Template="' . $a_style->lookupTemplateName($a_t_id) . '">';
            $p_content .= '<Tab><PageContent><Paragraph>' . $c . '</Paragraph></PageContent>';
            $p_content .= '<TabCaption>' . $h . '</TabCaption>';
            $p_content .= '</Tab>';
            $p_content .= '</Tabs></PageContent>';
        }
        //echo htmlentities($p_content);
        $txml = $a_style->getTemplateXML();
        //echo htmlentities($txml); exit;
        $p_content .= $txml;
        $r_content = ilPCTableGUI::_renderTable($p_content, "");

        // fix carousel template visibility
        if ($a_type == "carousel") {
            $r_content .= "<style>.owl-carousel{ display:block !important; }</style>";
        }

        //echo htmlentities($r_content); exit;
        return $r_content;
    }

    /**
     * Init table template form
     */
    public function initTemplateForm(string $a_mode = "create"): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->form_gui = new ilPropertyFormGUI();

        if ($a_mode == "create") {
            $this->form_gui->setTitle($lng->txt("sty_add_template"));
        } else {
            $this->form_gui->setTitle($lng->txt("sty_edit_template"));
        }

        // name
        $name_input = new ilRegExpInputGUI($lng->txt("sty_template_name"), "name");
        $name_input->setPattern("/^[a-zA-Z]+[a-zA-Z0-9]*$/");
        $name_input->setNoMatchMessage($lng->txt("sty_msg_color_must_only_include") . " A-Z, a-z, 1-9");
        $name_input->setRequired(true);
        $name_input->setSize(30);
        $name_input->setMaxLength(30);
        $this->form_gui->addItem($name_input);

        // template style classes
        $scs = ilObjStyleSheet::_getTemplateClassTypes(
            $this->style_request->getTempType()
        );
        foreach ($scs as $sc => $st) {
            $sc_input = new ilSelectInputGUI($lng->txt("sty_" . $sc . "_class"), $sc . "_class");
            $chars = $this->object->getCharacteristics($st);
            $options = array("" => "");
            foreach ($chars as $char) {
                $options[$char] = $char;
            }
            $sc_input->setOptions($options);
            $this->form_gui->addItem($sc_input);
        }

        if ($a_mode == "create") {
            $this->form_gui->addCommandButton("saveTemplate", $lng->txt("save"));
        } else {
            $this->form_gui->addCommandButton("refreshTemplate", $lng->txt("save_refresh"));
            $this->form_gui->addCommandButton("updateTemplate", $lng->txt("save_return"));
        }
        $this->form_gui->addCommandButton("cancelTemplateSaving", $lng->txt("cancel"));
        $this->form_gui->setFormAction($ilCtrl->getFormAction($this));
    }

    /**
     * Cancel color saving
     */
    public function cancelTemplateSavingObject(): void
    {
        $ilCtrl = $this->ctrl;
        $ilCtrl->redirect($this, "listTemplates");
    }


    /**
     * Save table template
     */
    public function saveTemplateObject(): void
    {
        $tpl = $this->gui_service->ui()->mainTemplate();
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $this->initTemplateForm();
        $temp_type = $this->style_request->getTempType();

        if ($this->form_gui->checkInput()) {
            if ($this->object->templateExists($this->form_gui->getInput("name"))) {
                $name_input = $this->form_gui->getItemByPostVar("name");
                $name_input->setAlert($lng->txt("sty_table_template_already_exists"));
            } else {
                $classes = array();
                foreach (ilObjStyleSheet::_getTemplateClassTypes($temp_type) as $tct => $ct) {
                    $classes[$tct] = $this->form_gui->getInput($tct . "_class");
                }
                $t_id = $this->object->addTemplate(
                    $temp_type,
                    $this->form_gui->getInput("name"),
                    $classes
                );
                $this->object->writeTemplatePreview(
                    $t_id,
                    $this->getTemplatePreview($temp_type, $t_id, true)
                );
                $ilCtrl->redirect($this, "listTemplates");
            }
        }
        $this->form_gui->setValuesByPost();
        $tpl->setContent($this->form_gui->getHTML());
    }

    public function updateTemplateObject(
        bool $a_refresh = false
    ): void {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $ilCtrl->setParameter($this, "t_id", $this->style_request->getTemplateId());
        $this->initTemplateForm("edit");
        $temp_type = $this->style_request->getTempType();

        $t_id = $this->style_request->getTemplateId();
        if ($this->form_gui->checkInput()) {
            if ($this->object->templateExists($this->form_gui->getInput("name")) &&
                $this->form_gui->getInput("name") != ilObjStyleSheet::_lookupTemplateName($t_id)) {
                $name_input = $this->form_gui->getItemByPostVar("name");
                $name_input->setAlert($lng->txt("sty_template_already_exists"));
            } else {
                $classes = array();
                foreach (ilObjStyleSheet::_getTemplateClassTypes($temp_type) as $tct => $ct) {
                    $classes[$tct] = $this->form_gui->getInput($tct . "_class");
                }

                $this->object->updateTemplate(
                    $t_id,
                    $this->form_gui->getInput("name"),
                    $classes
                );
                $this->object->writeTemplatePreview(
                    $t_id,
                    $this->getTemplatePreview($temp_type, $t_id, true)
                );
                if (!$a_refresh) {
                    $ilCtrl->redirect($this, "listTemplates");
                }
            }
        }

        $this->form_gui->setValuesByPost();
        $this->displayTemplateEditForm();
    }

    public function displayTemplateEditForm(): void
    {
        $tpl = $this->gui_service->ui()->mainTemplate();

        $a_tpl = new ilTemplate(
            "tpl.template_edit.html",
            true,
            true,
            "Services/Style/Content"
        );
        $this->includeCSS();
        $a_tpl->setVariable("FORM", $this->form_gui->getHTML());
        $a_tpl->setVariable("PREVIEW", $this->getTemplatePreview(
            $this->style_request->getTempType(),
            $this->style_request->getTemplateId()
        ));
        $tpl->setContent($a_tpl->get());
    }

    /**
     * Refresh table template
     */
    public function refreshTemplateObject(): void
    {
        $this->updateTemplateObject(true);
    }

    /**
     * Set values for table template editing
     */
    public function getTemplateFormValues(): void
    {
        $t_id = $this->style_request->getTemplateId();
        if ($t_id > 0) {
            $t = $this->object->getTemplate($t_id);

            $values["name"] = $t["name"];
            $scs = ilObjStyleSheet::_getTemplateClassTypes(
                $this->style_request->getTempType()
            );
            foreach ($scs as $k => $type) {
                $values[$k . "_class"] = $t["classes"][$k];
            }
            $this->form_gui->setValuesByArray($values);
        }
    }

    /**
     * Delete table template confirmation
     */
    public function deleteTemplateConfirmationObject(): void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->gui_service->ui()->mainTemplate();
        $lng = $this->lng;

        $tids = $this->style_request->getTemplateIds();
        if (count($tids) == 0) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listTemplates");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("sty_confirm_template_deletion"));
            $cgui->setCancel($lng->txt("cancel"), "cancelTemplateDeletion");
            $cgui->setConfirm($lng->txt("sty_del_template"), "deleteTemplate");

            foreach ($tids as $tid) {
                $classes = $this->object->getTemplateClasses($tid);
                $cl_str = "";
                $listed = array();
                foreach ($classes as $cl) {
                    if ($cl != "" && !$listed[$cl]) {
                        $cl_str .= '<div>- ' .
                            $cl . "</div>";
                        $listed[$cl] = true;
                    }
                }
                if ($cl_str != "") {
                    $cl_str = '<div style="padding-left:30px;" class="small">' .
                        "<div><i>" . $lng->txt("sty_style_class") . "</i></div>" . $cl_str . "</div>";
                }
                $cgui->addItem("tid[]", $tid, $this->object->lookupTemplateName($tid) . $cl_str);
            }

            $cgui->addButton($lng->txt("sty_del_template_keep_classes"), "deleteTemplateKeepClasses");

            $tpl->setContent($cgui->getHTML());
        }
    }

    /**
     * Cancel table template deletion
     */
    public function cancelTemplateDeletionObject(): void
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->redirect($this, "listTemplates");
    }

    public function deleteTemplateKeepClassesObject(): void
    {
        $ilCtrl = $this->ctrl;

        $tids = $this->style_request->getTemplateIds();
        foreach ($tids as $tid) {
            $this->object->removeTemplate($tid);
        }

        $ilCtrl->redirect($this, "listTemplates");
    }

    /**
     * Delete template
     * @throws Content\ContentStyleNoPermissionException
     * @throws Content\ContentStyleNoPermissionException
     * @throws ilCtrlException
     */
    public function deleteTemplateObject(): void
    {
        $ilCtrl = $this->ctrl;

        $tids = $this->style_request->getTemplateIds();
        foreach ($tids as $tid) {
            $cls = $this->object->getTemplateClasses($tid);
            foreach ($cls as $k => $cls2) {
                $ty = $this->object->determineTemplateStyleClassType(
                    $this->style_request->getTempType(),
                    $k
                );
                $this->characteristic_manager->deleteCharacteristic($ty, $cls2);
            }
            $this->object->removeTemplate($tid);
        }

        $ilCtrl->redirect($this, "listTemplates");
    }

    public function generateTemplateObject(): void
    {
        $tpl = $this->gui_service->ui()->mainTemplate();

        $this->initTemplateGenerationForm();
        $tpl->setContent($this->form_gui->getHTML());
    }

    /**
     * Init table template generation form
     */
    public function initTemplateGenerationForm(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->form_gui = new ilPropertyFormGUI();

        $this->form_gui->setTitle($lng->txt("sty_generate_template"));

        // name
        $name_input = new ilRegExpInputGUI($lng->txt("sty_template_name"), "name");
        $name_input->setPattern("/^[a-zA-Z]+[a-zA-Z0-9]*$/");
        $name_input->setNoMatchMessage($lng->txt("sty_msg_color_must_only_include") . " A-Z, a-z, 1-9");
        $name_input->setRequired(true);
        $name_input->setSize(30);
        $name_input->setMaxLength(30);
        $this->form_gui->addItem($name_input);

        // basic layout
        $bl_input = new ilSelectInputGUI($lng->txt("sty_template_layout"), "layout");
        $options = array(
            "coloredZebra" => $lng->txt("sty_table_template_colored_zebra"),
            "bwZebra" => $lng->txt("sty_table_template_bw_zebra"),
            "noZebra" => $lng->txt("sty_table_template_no_zebra")
            );
        $bl_input->setOptions($options);
        $this->form_gui->addItem($bl_input);

        // top bottom padding
        $num_input = new ilNumericStyleValueInputGUI($lng->txt("sty_top_bottom_padding"), "tb_padding");
        $num_input->setAllowPercentage(false);
        $num_input->setValue("3px");
        $this->form_gui->addItem($num_input);

        // left right padding
        $num_input = new ilNumericStyleValueInputGUI($lng->txt("sty_left_right_padding"), "lr_padding");
        $num_input->setAllowPercentage(false);
        $num_input->setValue("10px");
        $this->form_gui->addItem($num_input);

        // base color
        $bc_input = new ilSelectInputGUI($lng->txt("sty_base_color"), "base_color");
        $cs = $this->object->getColors();
        $options = array();
        foreach ($cs as $c) {
            $options[$c["name"]] = $c["name"];
        }
        $bc_input->setOptions($options);
        $this->form_gui->addItem($bc_input);

        // Lightness Settings
        $lss = array("border" => 90, "header_text" => 70, "header_bg" => 0,
            "cell1_text" => -60, "cell1_bg" => 90, "cell2_text" => -60, "cell2_bg" => 75);
        foreach ($lss as $ls => $v) {
            $l_input = new ilNumberInputGUI($lng->txt("sty_lightness_" . $ls), "lightness_" . $ls);
            $l_input->setMaxValue(100);
            $l_input->setMinValue(-100);
            $l_input->setValue((string) $v);
            $l_input->setSize(4);
            $l_input->setMaxLength(4);
            $this->form_gui->addItem($l_input);
        }

        $this->form_gui->addCommandButton("templateGeneration", $lng->txt("generate"));
        $this->form_gui->addCommandButton("cancelTemplateSaving", $lng->txt("cancel"));
        $this->form_gui->setFormAction($ilCtrl->getFormAction($this));
    }

    /**
     * Table template generation
     */
    public function templateGenerationObject(): void
    {
        $tpl = $this->gui_service->ui()->mainTemplate();
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $this->initTemplateGenerationForm();

        if ($this->form_gui->checkInput()) {
            if ($this->object->templateExists($this->form_gui->getInput("name"))) {
                $name_input = $this->form_gui->getItemByPostVar("name");
                $name_input->setAlert($lng->txt("sty_table_template_already_exists"));
            } else {
                // -> move to application class!

                // cell classes
                $cells = array("H" => "header", "C1" => "cell1", "C2" => "cell2");
                $tb_p = $this->form_gui->getItemByPostVar("tb_padding");
                $tb_padding = $tb_p->getValue();
                $lr_p = $this->form_gui->getItemByPostVar("lr_padding");
                $lr_padding = $lr_p->getValue();
                $cell_color = $this->form_gui->getInput("base_color");

                // use mid gray as cell color for bw zebra
                if ($this->form_gui->getInput("layout") == "bwZebra") {
                    $cell_color = "MidGray";
                    if (!$this->color_manager->colorExists($cell_color)) {
                        $this->color_manager->addColor($cell_color, "7F7F7F");
                    }
                    $this->color_manager->updateColor($cell_color, $cell_color, "7F7F7F");
                }

                foreach ($cells as $k => $cell) {
                    $cell_class[$k] = $this->form_gui->getInput("name") . $k;
                    if (!$this->object->characteristicExists($cell_class[$k], "table_cell")) {
                        $this->characteristic_manager->addCharacteristic("table_cell", $cell_class[$k], true);
                    }
                    if ($this->form_gui->getInput("layout") == "bwZebra" && $k == "H") {
                        $this->characteristic_manager->replaceParameter(
                            "td",
                            $cell_class[$k],
                            "color",
                            "!" . $this->form_gui->getInput("base_color") . "(" . $this->form_gui->getInput("lightness_" . $cell . "_text") . ")",
                            "table_cell"
                        );
                        $this->characteristic_manager->replaceParameter(
                            "td",
                            $cell_class[$k],
                            "background-color",
                            "!" . $this->form_gui->getInput("base_color") . "(" . $this->form_gui->getInput("lightness_" . $cell . "_bg") . ")",
                            "table_cell"
                        );
                    } else {
                        $this->characteristic_manager->replaceParameter(
                            "td",
                            $cell_class[$k],
                            "color",
                            "!" . $cell_color . "(" . $this->form_gui->getInput("lightness_" . $cell . "_text") . ")",
                            "table_cell"
                        );
                        $this->characteristic_manager->replaceParameter(
                            "td",
                            $cell_class[$k],
                            "background-color",
                            "!" . $cell_color . "(" . $this->form_gui->getInput("lightness_" . $cell . "_bg") . ")",
                            "table_cell"
                        );
                    }
                    $this->characteristic_manager->replaceParameter(
                        "td",
                        $cell_class[$k],
                        "padding-top",
                        $tb_padding,
                        "table_cell"
                    );
                    $this->characteristic_manager->replaceParameter(
                        "td",
                        $cell_class[$k],
                        "padding-bottom",
                        $tb_padding,
                        "table_cell"
                    );
                    $this->characteristic_manager->replaceParameter(
                        "td",
                        $cell_class[$k],
                        "padding-left",
                        $lr_padding,
                        "table_cell"
                    );
                    $this->characteristic_manager->replaceParameter(
                        "td",
                        $cell_class[$k],
                        "padding-right",
                        $lr_padding,
                        "table_cell"
                    );
                    $this->characteristic_manager->replaceParameter(
                        "td",
                        $cell_class[$k],
                        "border-width",
                        "1px",
                        "table_cell"
                    );
                    $this->characteristic_manager->replaceParameter(
                        "td",
                        $cell_class[$k],
                        "border-style",
                        "solid",
                        "table_cell"
                    );
                    $this->characteristic_manager->replaceParameter(
                        "td",
                        $cell_class[$k],
                        "border-color",
                        "!" . $cell_color . "(" . $this->form_gui->getInput("lightness_border") . ")",
                        "table_cell"
                    );
                    $this->characteristic_manager->replaceParameter(
                        "td",
                        $cell_class[$k],
                        "font-weight",
                        "normal",
                        "table_cell"
                    );
                }

                // table class
                $classes["table"] = $this->form_gui->getInput("name") . "T";
                if (!$this->object->characteristicExists($classes["table"], "table")) {
                    $this->characteristic_manager->addCharacteristic("table", $classes["table"], true);
                }
                $this->characteristic_manager->replaceParameter(
                    "table",
                    $classes["table"],
                    "caption-side",
                    "bottom",
                    "table"
                );
                $this->characteristic_manager->replaceParameter(
                    "table",
                    $classes["table"],
                    "border-collapse",
                    "collapse",
                    "table"
                );
                $this->characteristic_manager->replaceParameter(
                    "table",
                    $classes["table"],
                    "margin-top",
                    "5px",
                    "table"
                );
                $this->characteristic_manager->replaceParameter(
                    "table",
                    $classes["table"],
                    "margin-bottom",
                    "5px",
                    "table"
                );
                if ($this->form_gui->getInput("layout") == "bwZebra") {
                    $this->characteristic_manager->replaceParameter(
                        "table",
                        $classes["table"],
                        "border-bottom-color",
                        "!" . $this->form_gui->getInput("base_color"),
                        "table"
                    );
                    $this->characteristic_manager->replaceParameter(
                        "table",
                        $classes["table"],
                        "border-bottom-style",
                        "solid",
                        "table"
                    );
                    $this->characteristic_manager->replaceParameter(
                        "table",
                        $classes["table"],
                        "border-bottom-width",
                        "3px",
                        "table"
                    );
                    $sb = array("left", "right", "top");
                    foreach ($sb as $b) {
                        $this->characteristic_manager->replaceParameter(
                            "table",
                            $classes["table"],
                            "border-" . $b . "-width",
                            "0px",
                            "table"
                        );
                    }
                }

                switch ($this->form_gui->getInput("layout")) {
                    case "bwZebra":
                    case "coloredZebra":
                        $classes["row_head"] = $cell_class["H"];
                        $classes["odd_row"] = $cell_class["C1"];
                        $classes["even_row"] = $cell_class["C2"];
                        break;

                    case "noZebra":
                        $classes["row_head"] = $cell_class["H"];
                        $classes["odd_row"] = $cell_class["C1"];
                        $classes["even_row"] = $cell_class["C1"];
                        $classes["col_head"] = $cell_class["C2"];
                        break;
                }


                $t_id = $this->object->addTemplate(
                    $this->style_request->getTempType(),
                    $this->form_gui->getInput("name"),
                    $classes
                );
                $this->object->writeTemplatePreview(
                    $t_id,
                    $this->getTemplatePreview(
                        $this->style_request->getTempType(),
                        $t_id,
                        true
                    )
                );
                $ilCtrl->redirect($this, "listTemplates");
            }
        }
        $this->form_gui->setValuesByPost();
        $tpl->setContent($this->form_gui->getHTML());
    }

    public function returnToUpperContextObject(): void
    {
        $ilCtrl = $this->ctrl;
        $ilCtrl->returnToParent($this);
    }
}
