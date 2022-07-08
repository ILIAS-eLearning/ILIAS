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

use ILIAS\MediaObjects\SubTitles\SubtitlesGUIRequest;

/**
 * Editing User Interface for MediaObjects within LMs (see ILIAS DTD)
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilObjMediaObjectGUI: ilObjectMetaDataGUI, ilImageMapEditorGUI, ilFileSystemGUI
 */
class ilObjMediaObjectGUI extends ilObjectGUI
{
    protected SubtitlesGUIRequest $sub_title_request;
    protected ilPropertyFormGUI $form_gui;
    protected int $height_preset = 0;
    protected int $width_preset = 0;
    protected string $back_title = "";
    protected ilErrorHandling $error;
    protected ilHelpGUI $help;
    protected ilTabsGUI $tabs;

    // $adv_ref_id - $adv_type - $adv_subtype:
    // Object, that defines the adv md records being used. Default is $this->object, but the
    // context may set another object (e.g. media pool for media objects)
    protected ?int $adv_ref_id = null;
    protected ?string $adv_type = null;
    protected ?string $adv_subtype = null;
    protected \ILIAS\MediaObjects\MediaType\MediaType $media_type;
    public string $header = "";
    public string $target_script = "";
    public bool $enabledmapareas = true;

    /**
     * @param mixed $data
     */
    public function __construct(
        $a_data,
        int $a_id = 0,
        bool $a_call_by_reference = false,
        bool $a_prepare_output = false
    ) {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->access = $DIC->access();
        $this->error = $DIC["ilErr"];
        $this->help = $DIC["ilHelp"];
        $this->tabs = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
        $this->user = $DIC->user();
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $this->media_type = new ILIAS\MediaObjects\MediaType\MediaType();

        $this->ctrl = $ilCtrl;
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
        $this->lng = $lng;
        $this->back_title = "";
        $this->type = "mob";

        $this->sub_title_request = $DIC->mediaObjects()
            ->internal()
            ->gui()
            ->subTitles()
            ->request();
        
        $lng->loadLanguageModule("mob");
    }

    /**
     * Set object, that defines the adv md records being used. Default is $this->object, but the
     * context may set another object (e.g. media pool for media objects)
     */
    public function setAdvMdRecordObject(
        int $a_adv_ref_id,
        string $a_adv_type,
        string $a_adv_subtype = "-"
    ) : void {
        $this->adv_ref_id = $a_adv_ref_id;
        $this->adv_type = $a_adv_type;
        $this->adv_subtype = $a_adv_subtype;
    }

    /**
     * Get adv md record type
     * @throws ilMediaObjectsException
     */
    public function getAdvMdRecordObject() : ?array
    {
        if ($this->adv_type == null) {
            throw new ilMediaObjectsException("Missing obj type (getAdvMdRecordObject)");
            // seems to be obsolete, since $this->obj_type is non-existent
            //return [$this->ref_id, $this->obj_type, $this->sub_type];
        }
        return [$this->adv_ref_id, $this->adv_type, $this->adv_subtype];
    }

    public function setHeader(
        string $a_title = ""
    ) : void {
        $this->header = $a_title;
    }

    public function getHeader() : string
    {
        return $this->header;
    }

    public function setEnabledMapAreas(
        bool $a_enabledmapareas
    ) : void {
        $this->enabledmapareas = $a_enabledmapareas;
    }

    public function getEnabledMapAreas() : bool
    {
        return $this->enabledmapareas;
    }
    
    /**
     * Set width preset (e.g. set from media pool)
     */
    public function setWidthPreset(int $a_val) : void
    {
        $this->width_preset = $a_val;
    }
    
    public function getWidthPreset() : int
    {
        return $this->width_preset;
    }

    /**
     * Set height preset (e.g. set from media pool)
     */
    public function setHeightPreset(int $a_val) : void
    {
        $this->height_preset = $a_val;
    }
    
    public function getHeightPreset() : int
    {
        return $this->height_preset;
    }

    public function getForm() : ilPropertyFormGUI
    {
        return $this->form_gui;
    }

    protected function assignObject() : void
    {
        if ($this->id != 0) {
            $this->object = new ilObjMediaObject($this->id);
        }
    }

    /**
     * @throws ilCtrlException
     */
    public function returnToContextObject() : void
    {
        $this->ctrl->returnToParent($this);
    }

    /**
     * @throws ilCtrlException
     */
    public function executeCommand() : void
    {
        $tpl = $this->tpl;

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $ret = "";

        switch ($next_class) {
            case 'ilobjectmetadatagui':
                $md_gui = new ilObjectMetaDataGUI(null, $this->object->getType(), $this->object->getId());
                // object is subtype, so we have to do it ourselves
                $md_gui->addMDObserver($this->object, 'MDUpdateListener', 'General');

                // set adv metadata record dobject
                if ($this->adv_type != "") {
                    $md_gui->setAdvMdRecordObject($this->adv_ref_id, $this->adv_type, $this->adv_subtype);
                }

                $this->ctrl->forwardCommand($md_gui);
                break;
                
            case "ilimagemapeditorgui":
                /** @var ilObjMediaObject $mob */
                $mob = $this->object;
                $image_map_edit = new ilImageMapEditorGUI($mob);
                $ret = $this->ctrl->forwardCommand($image_map_edit);
                $tpl->setContent($ret);
                $this->checkFixSize();
                break;
                
            case "ilfilesystemgui":
                $fs_gui = new ilFileSystemGUI(ilFileUtils::getWebspaceDir() . "/mobs/mm_" . $this->object->getId());
                $fs_gui->setAllowedSuffixes(ilObjMediaObject::getRestrictedFileTypes());
                $fs_gui->setForbiddenSuffixes(ilObjMediaObject::getForbiddenFileTypes());
                $fs_gui->activateLabels(true, $this->lng->txt("cont_purpose"));
                $fs_gui->setTableId("mobfs" . $this->object->getId());
                $fs_gui->labelFile(
                    $this->object->getMediaItem("Standard")->getLocation(),
                    $this->lng->txt("cont_std_view")
                );
                if ($this->object->hasFullscreenItem()) {
                    $fs_gui->labelFile(
                        $this->object->getMediaItem("Fullscreen")->getLocation(),
                        $this->lng->txt("cont_fullscreen")
                    );
                }
                $fs_gui->addCommand($this, "assignStandardObject", $this->lng->txt("cont_assign_std"));
                $fs_gui->addCommand($this, "assignFullscreenObject", $this->lng->txt("cont_assign_full"));
                ilObjMediaObject::renameExecutables(ilObjMediaObject::_getDirectory($this->object->getId()));	// see #20187
                $ret = $this->ctrl->forwardCommand($fs_gui);
                ilObjMediaObject::renameExecutables(ilObjMediaObject::_getDirectory($this->object->getId()));	// see #20187
                ilMediaSvgSanitizer::sanitizeDir(ilObjMediaObject::_getDirectory($this->object->getId()));	// see #20339
                break;


            default:
                /*
                if (isset($_POST["editImagemapForward"]) ||
                    isset($_POST["editImagemapForward_x"]) ||
                    isset($_POST["editImagemapForward_y"])) {
                    $cmd = "editImagemapForward";
                }*/
                $cmd .= "Object";
                $ret = $this->$cmd();
                break;
        }
    }

    public function setBackTitle(string $a_title) : void
    {
        $this->back_title = $a_title;
    }
    
    public function createObject() : void
    {
        $tpl = $this->tpl;
        $ilHelp = $this->help;
        
        $ilHelp->setScreenId("create");
        $this->initForm();
        $tpl->setContent($this->form_gui->getHTML());
    }

    public function initForm(string $a_mode = "create") : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $add_str = "";

        $std_item = null;
        $full_item = null;
        if ($a_mode == "edit") {
            $std_item = $this->object->getMediaItem("Standard");
        }

        $this->form_gui = new ilPropertyFormGUI();
        
        // standard view resource
        $title = new ilTextInputGUI($lng->txt("title"), "standard_title");
        $title->setSize(40);
        $title->setMaxLength(120);
        $this->form_gui->addItem($title);
        $radio_prop = new ilRadioGroupInputGUI($lng->txt("cont_resource"), "standard_type");
        $op1 = new ilRadioOption($lng->txt("cont_file"), "File");
        $up = new ilFileInputGUI("", "standard_file");
        $up->setSuffixes(ilObjMediaObject::getRestrictedFileTypes());
        $up->setForbiddenSuffixes(ilObjMediaObject::getForbiddenFileTypes());
        $up->setInfo("");
        if ($a_mode == "create" || $std_item->getLocationType() != "LocalFile") {
            $up->setRequired(true);
        }
        $op1->addSubItem($up);
        $radio_prop->addOption($op1);
        $op2 = new ilRadioOption($lng->txt("url"), "Reference");
        $ref = new ilUriInputGUI("", "standard_reference");
        $ref->setInfo($lng->txt("cont_ref_helptext"));
        $ref->setRequired(true);
        $op2->addSubItem($ref);
        $radio_prop->addOption($op2);
        $radio_prop->setValue("File");
        $this->form_gui->addItem($radio_prop);
        
        // standard format
        if ($a_mode == "edit") {
            $format = new ilNonEditableValueGUI($lng->txt("cont_format"), "standard_format");
            $format->setValue($std_item->getFormat());
            $this->form_gui->addItem($format);
        }
        
        // standard size
        $radio_size = new ilRadioGroupInputGUI($lng->txt("size"), "standard_size");
        if ($a_mode == "edit") {
            if ($orig_size = $std_item->getOriginalSize()) {
                $add_str = " (" . ($orig_size["width"] ?? "") . " x " . ($orig_size["height"] ?? "") . ")";
            }
            $op1 = new ilRadioOption($lng->txt("cont_resource_size") . $add_str, "original");
            $op1->setInfo($lng->txt("cont_resource_size_info"));
            $op2 = new ilRadioOption($lng->txt("cont_custom_size"), "selected");
        } else {
            $op1 = new ilRadioOption($lng->txt("cont_orig_size"), "original");
            $op1->setInfo($lng->txt("cont_resource_size_info"));
            $op2 = new ilRadioOption($lng->txt("cont_adjust_size"), "selected");
        }
        $radio_size->addOption($op1);
        
        // width height
        $width_height = new ilWidthHeightInputGUI($lng->txt("cont_width") .
                " / " . $lng->txt("cont_height"), "standard_width_height");
        $width_height->setConstrainProportions(true);
        $op2->addSubItem($width_height);
            
        // resize image
        if ($a_mode == "edit") {
            $std_item = $this->object->getMediaItem("Standard");
            if (is_int(strpos($std_item->getFormat(), "image"))
                    && $std_item->getLocationType() == "LocalFile") {
                $resize = new ilCheckboxInputGUI($lng->txt("cont_resize_img"), "standard_resize");
                $op2->addSubItem($resize);
            }
        }
            
        $radio_size->setValue("original");
        if ($a_mode == "create" && ($this->getHeightPreset() > 0 || $this->getWidthPreset() > 0)) {
            $radio_size->setValue("selected");
            $width_height->setWidth($this->getWidthPreset());
            $width_height->setHeight($this->getHeightPreset());
        }
        $radio_size->addOption($op2);
        $this->form_gui->addItem($radio_size);
        
        // standard caption
        $caption = new ilTextAreaInputGUI($lng->txt("cont_caption"), "standard_caption");
        $caption->setCols(30);
        $caption->setRows(2);
        $this->form_gui->addItem($caption);

        /*$caption = new ilTextInputGUI($lng->txt("cont_caption"), "standard_caption");
        $caption->setSize(40);
        $caption->setMaxLength(200);
        $this->form_gui->addItem($caption);*/
        
        // text representation (alt text)
        if ($a_mode == "edit" && $this->media_type->usesAltTextProperty($std_item->getFormat())) {
            $ta = new ilTextAreaInputGUI($lng->txt("text_repr"), "text_representation");
            $ta->setCols(30);
            $ta->setRows(2);
            $ta->setInfo($lng->txt("text_repr_info"));
            $this->form_gui->addItem($ta);
        }

        // standard parameters
        if ($a_mode == "edit" &&
            $this->media_type->usesParameterProperty($std_item->getFormat())) {
            if ($this->media_type->usesAutoStartParameterOnly(
                $std_item->getLocation(),
                $std_item->getFormat()
            )) {	// autostart
                /*$auto = new ilCheckboxInputGUI($lng->txt("cont_autostart"), "standard_autostart");
                $this->form_gui->addItem($auto);*/
            } else {							// parameters
                $par = new ilTextAreaInputGUI($lng->txt("cont_parameter"), "standard_parameters");
                $par->setRows(5);
                $par->setCols(50);
                $this->form_gui->addItem($par);
            }
        }

        if ($a_mode == "edit") {
            $full_item = $this->object->getMediaItem("Fullscreen");
        }
        
        // fullscreen view resource
        $fs_sec = new ilFormSectionHeaderGUI();
        $fs_sec->setTitle($lng->txt("cont_fullscreen"));
        $this->form_gui->addItem($fs_sec);
        
        $radio_prop2 = new ilRadioGroupInputGUI($lng->txt("cont_resource"), "full_type");
        $op1 = new ilRadioOption($lng->txt("cont_none"), "None");
        $radio_prop2->addOption($op1);
        $op4 = new ilRadioOption($lng->txt("cont_use_same_resource_as_above"), "Standard");
        $radio_prop2->addOption($op4);
        $op2 = new ilRadioOption($lng->txt("cont_file"), "File");
        $up = new ilFileInputGUI("", "full_file");
        $up->setSuffixes(ilObjMediaObject::getRestrictedFileTypes());
        $up->setForbiddenSuffixes(ilObjMediaObject::getForbiddenFileTypes());
        $up->setInfo("");
        if ($a_mode == "create" || !$full_item || $full_item->getLocationType() != "LocalFile") {
            $up->setRequired(true);
        }
        $op2->addSubItem($up);
        $radio_prop2->addOption($op2);
        $op3 = new ilRadioOption($lng->txt("url"), "Reference");
        $ref = new ilUriInputGUI("", "full_reference");
        $ref->setInfo($lng->txt("cont_ref_helptext"));
        $ref->setRequired(true);
        $op3->addSubItem($ref);
        $radio_prop2->addOption($op3);
        $radio_prop2->setValue("None");
        $this->form_gui->addItem($radio_prop2);

        // fullscreen format
        if ($a_mode == "edit") {
            if ($this->object->hasFullscreenItem()) {
                $format = new ilNonEditableValueGUI($lng->txt("cont_format"), "full_format");
                $format->setValue($full_item->getFormat());
                $this->form_gui->addItem($format);
            }
        }
        
        // fullscreen size
        $radio_size = new ilRadioGroupInputGUI($lng->txt("size"), "full_size");
        if ($a_mode == "edit") {
            $add_str = "";
            if ($this->object->hasFullscreenItem() && ($orig_size = $full_item->getOriginalSize())) {
                $add_str = " (" . ($orig_size["width"] ?? "") . " x " . ($orig_size["height"] ?? "") . ")";
            }
            $op1 = new ilRadioOption($lng->txt("cont_resource_size") . $add_str, "original");
            $op1->setInfo($lng->txt("cont_resource_size_info"));
            $op2 = new ilRadioOption($lng->txt("cont_custom_size"), "selected");
        } else {
            $op1 = new ilRadioOption($lng->txt("cont_orig_size"), "original");
            $op1->setInfo($lng->txt("cont_resource_size_info"));
            $op2 = new ilRadioOption($lng->txt("cont_adjust_size"), "selected");
        }
        $radio_size->addOption($op1);
        
        // width/height
        $width_height = new ilWidthHeightInputGUI($lng->txt("cont_width") .
                " / " . $lng->txt("cont_height"), "full_width_height");
        $width_height->setConstrainProportions(true);
        $op2->addSubItem($width_height);
            
        // resize image
        if ($a_mode == "edit") {
            $full_item = $this->object->getMediaItem("Fullscreen");
            if ($this->object->hasFullscreenItem() &&
                    is_int(strpos($full_item->getFormat(), "image")) &&
                    $full_item->getLocationType() == "LocalFile") {
                $resize = new ilCheckboxInputGUI(
                    $lng->txt("cont_resize_img"),
                    "full_resize"
                );
                $op2->addSubItem($resize);
            }
        }

        $radio_size->setValue("original");
        $radio_size->addOption($op2);
        $this->form_gui->addItem($radio_size);
        
        // fullscreen caption
        $caption = new ilTextAreaInputGUI($lng->txt("cont_caption"), "full_caption");
        $caption->setCols(30);
        $caption->setRows(2);
        $this->form_gui->addItem($caption);

        /*$caption = new ilTextInputGUI($lng->txt("cont_caption"), "full_caption");
        $caption->setSize(40);
        $caption->setMaxLength(200);
        $this->form_gui->addItem($caption);*/
        
        // text representation (alt text)
        if ($a_mode == "edit" && $this->object->hasFullscreenItem() && $this->media_type->usesAltTextProperty($std_item->getFormat())) {
            $ta = new ilTextAreaInputGUI($lng->txt("text_repr"), "full_text_representation");
            $ta->setCols(30);
            $ta->setRows(2);
            $ta->setInfo($lng->txt("text_repr_info"));
            $this->form_gui->addItem($ta);
        }

        
        // fullscreen parameters
        if ($a_mode == "edit" && $this->object->hasFullscreenItem() &&
            $this->media_type->usesParameterProperty($full_item->getFormat())) {
            if ($this->media_type->usesAutoStartParameterOnly(
                $full_item->getLocation(),
                $full_item->getFormat()
            )) {
                /*$auto = new ilCheckboxInputGUI($lng->txt("cont_autostart"), "full_autostart");
                $this->form_gui->addItem($auto);*/
            } else {
                $par = new ilTextAreaInputGUI($lng->txt("cont_parameter"), "full_parameters");
                $par->setRows(5);
                $par->setCols(50);
                $this->form_gui->addItem($par);
            }
        }


        if ($a_mode == "edit") {
            $this->form_gui->setTitle($lng->txt("cont_edit_mob"));
            $this->form_gui->addCommandButton("saveProperties", $lng->txt("save"));
        } else {
            $this->form_gui->setTitle($lng->txt("cont_insert_mob"));
            $this->form_gui->addCommandButton("save", $lng->txt("save"));
            $this->form_gui->addCommandButton("cancel", $lng->txt("cancel"));
        }
        $this->form_gui->setFormAction($ilCtrl->getFormAction($this));
    }

    /**
     * Check fix size (for map editing hint)
     */
    protected function checkFixSize() : void
    {
        $std_item = $this->object->getMediaItem("Standard");
        if ($std_item->getWidth() == "" || $std_item->getHeight() == "") {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("mob_no_fixed_size_map_editing"));
        }
    }

    
    /**
     * Get values for form
     */
    public function getValues() : void
    {
        $values = array();
        
        $values["standard_title"] = $this->object->getTitle();
        
        $std_item = $this->object->getMediaItem("Standard");
        if ($std_item->getLocationType() == "LocalFile") {
            $values["standard_type"] = "File";
            $values["standard_file"] = $std_item->getLocation();
        } else {
            $values["standard_type"] = "Reference";
            $values["standard_reference"] = $std_item->getLocation();
        }
        $values["standard_format"] = $std_item->getFormat();
        $values["standard_width_height"]["width"] = $std_item->getWidth();
        $values["standard_width_height"]["height"] = $std_item->getHeight();
        $values["standard_width_height"]["constr_prop"] = true;
        
        $values["standard_size"] = "selected";

        $orig_size = $std_item->getOriginalSize();
        if ($std_item->getWidth() == "" && $std_item->getHeight() == "") {
            $values["standard_size"] = "original";
            $values["standard_width_height"]["width"] = $orig_size["width"] ?? "";
            $values["standard_width_height"]["height"] = $orig_size["height"] ?? "";
        }

        $values["standard_caption"] = $std_item->getCaption();
        $values["text_representation"] = $std_item->getTextRepresentation();
        if ($this->media_type->usesAutoStartParameterOnly(
            $std_item->getLocation(),
            $std_item->getFormat()
        )) {
            /*$par = $std_item->getParameters();
            if ($par["autostart"]) {
                $values["standard_autostart"] = true;
            }*/
        } else {
            $values["standard_parameters"] = $std_item->getParameterString();
        }
        
        $values["full_type"] = "None";
        $values["full_size"] = "original";
        if ($this->object->hasFullscreenItem()) {
            $full_item = $this->object->getMediaItem("Fullscreen");
            if ($full_item->getLocationType() == "LocalFile") {
                $values["full_type"] = "File";
                $values["full_file"] = $full_item->getLocation();
            } else {
                $values["full_type"] = "Reference";
                $values["full_reference"] = $full_item->getLocation();
            }
            $values["full_format"] = $full_item->getFormat();
            $values["full_width_height"]["width"] = $full_item->getWidth();
            $values["full_width_height"]["height"] = $full_item->getHeight();
            $values["full_width_height"]["constr_prop"] = true;

            $values["full_size"] = "selected";
    
            $orig_size = $full_item->getOriginalSize();
            if ($full_item->getWidth() == "" &&
                $full_item->getHeight() == "") {
                $values["full_size"] = "original";
                $values["full_width_height"]["width"] = $orig_size["width"] ?? "";
                $values["full_width_height"]["height"] = $orig_size["height"] ?? "";
            }
            $values["full_caption"] = $full_item->getCaption();
            if ($this->media_type->usesAutoStartParameterOnly(
                $full_item->getLocation(),
                $full_item->getFormat()
            )) {
                /*$par = $full_item->getParameters();
                if ($par["autostart"]) {
                    $values["full_autostart"] = true;
                }*/
            } else {
                $values["full_parameters"] = $full_item->getParameterString();
            }
            $values["full_text_representation"] = $full_item->getTextRepresentation();
        }
        
        $this->form_gui->setValuesByArray($values);
    }

    /**
     * create new media object
     */
    public function saveObject() : void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $this->initForm();
        if ($this->form_gui->checkInput()) {
            $this->object = new ilObjMediaObject();
            $this->setObjectPerCreationForm($this->object);
            $this->tpl->setOnScreenMessage('success', $lng->txt("saved_media_object"), true);
        } else {
            $this->form_gui->setValuesByPost();
            $tpl->setContent($this->form_gui->getHTML());
        }
    }
    
    public function checkFormInput() : bool
    {
        if (!$this->form_gui->checkInput()) {
            $this->form_gui->setValuesByPost();
            return false;
        }
        return true;
    }
    
    
    /**
     * Set media object values from creation form
     */
    public function setObjectPerCreationForm(
        ilObjMediaObject $a_mob
    ) : void {
        $form = $this->form_gui;

        $location = "";
        $file_name = "";
        $file = "";
        $type = "";

        // determinte title and format
        if (trim($form->getInput("standard_title")) != "") {
            $title = trim($form->getInput("standard_title"));
        } else {
            if ($form->getInput("standard_type") == "File") {
                $title = $_FILES['standard_file']['name'];
            } else {
                $title = $form->getInput("standard_reference");
            }
        }

        $a_mob->setTitle($title);
        $a_mob->setDescription("");
        $a_mob->create();

        // determine and create mob directory, move uploaded file to directory
        //$mob_dir = ilFileUtils::getWebspaceDir()."/mobs/mm_".$a_mob->getId();
        $a_mob->createDirectory();
        $mob_dir = ilObjMediaObject::_getDirectory($a_mob->getId());

        $media_item = new ilMediaItem();
        $a_mob->addMediaItem($media_item);
        $media_item->setPurpose("Standard");

        if ($form->getInput("standard_type") == "File") {
            $file_name = ilObjMediaObject::fixFilename($_FILES['standard_file']['name']);
            $file = $mob_dir . "/" . $file_name;
            ilFileUtils::moveUploadedFile(
                $_FILES['standard_file']['tmp_name'],
                $file_name,
                $file
            );

            // get mime type
            $format = ilObjMediaObject::getMimeType($file);
            $location = $file_name;

            // resize standard images
            if ($form->getInput("standard_size") != "original" &&
                is_int(strpos($format, "image"))) {
                $wh_input = $form->getInput("standard_width_height");

                $location = ilObjMediaObject::_resizeImage(
                    $file,
                    (int) $wh_input["width"],
                    (int) $wh_input["height"],
                    (boolean) $wh_input["constr_prop"]
                );
            }

            // set real meta and object data
            $media_item->setFormat($format);
            $media_item->setLocation($location);
            $media_item->setLocationType("LocalFile");
            $a_mob->generatePreviewPic(320, 240);
        } else {	// standard type: reference
            $format = ilObjMediaObject::getMimeType($form->getInput("standard_reference"), true);
            $media_item->setFormat($format);
            $media_item->setLocation(ilUtil::secureLink($form->getInput("standard_reference")));
            $media_item->setLocationType("Reference");

            try {
                $a_mob->getExternalMetadata();
            } catch (Exception $e) {
            }
        }
        $a_mob->setDescription($format);

        // determine width and height of known image types
        $wh_input = $form->getInput("standard_width_height");
        $wh = ilObjMediaObject::_determineWidthHeight(
            $format,
            $form->getInput("standard_type"),
            $mob_dir . "/" . $location,
            $media_item->getLocation(),
            $wh_input["constr_prop"],
            ($form->getInput("standard_size") == "original"),
            ($wh_input["width"] == "") ? null : (int) $wh_input["width"],
            ($wh_input["height"] == "") ? null : (int) $wh_input["height"]
        );
        $media_item->setWidth($wh["width"]);
        $media_item->setHeight($wh["height"]);
        if ($wh["info"] != "") {
            $this->tpl->setOnScreenMessage('info', $wh["info"], true);
        }

        if ($form->getInput("standard_caption") != "") {
            $media_item->setCaption($form->getInput("standard_caption"));
        }


        $media_item->setHAlign("Left");

        // fullscreen view
        if ($form->getInput("full_type") != "None") {
            $media_item2 = new ilMediaItem();
            $a_mob->addMediaItem($media_item2);
            $media_item2->setPurpose("Fullscreen");

            // move file / set format and location
            if ($form->getInput("full_type") == "File") {
                $format = $location = "";
                if ($_FILES['full_file']['name'] != "") {
                    $full_file_name = ilObjMediaObject::fixFilename($_FILES['full_file']['name']);
                    $file = $mob_dir . "/" . $full_file_name;
                    ilFileUtils::moveUploadedFile(
                        $_FILES['full_file']['tmp_name'],
                        $full_file_name,
                        $file
                    );
                    $format = ilObjMediaObject::getMimeType($file);
                    $location = $full_file_name;
                }
            } elseif ($form->getInput("full_type") == "Standard" && $form->getInput("standard_type") == "File") {
                $location = $file_name;
            }
            
            // resize file
            if ($form->getInput("full_type") == "File" ||
                ($form->getInput("full_type") == "Standard" && $form->getInput("standard_type") == "File")) {
                if (($form->getInput("full_size") != "original" &&
                        is_int(strpos($format, "image")))
                    ) {
                    $full_wh_input = $form->getInput("full_width_height");
                    $location = ilObjMediaObject::_resizeImage(
                        $file,
                        (int) $full_wh_input["width"],
                        (int) $full_wh_input["height"],
                        (boolean) $full_wh_input["constr_prop"]
                    );
                }
    
                $media_item2->setFormat($format);
                $media_item2->setLocation($location);
                $media_item2->setLocationType("LocalFile");
                $type = "File";
            }
            
            if ($form->getInput("full_type") == "Reference") {
                $format = $location = "";
                if ($form->getInput("full_reference") != "") {
                    $format = ilObjMediaObject::getMimeType($form->getInput("full_reference"), true);
                    $location = ilUtil::stripSlashes($form->getInput("full_reference"));
                }
            }
            
            if ($form->getInput("full_type") == "Reference" ||
                ($form->getInput("full_type") == "Standard" && $form->getInput("standard_type") == "Reference")) {
                $media_item2->setFormat($format);
                $media_item2->setLocation($location);
                $media_item2->setLocationType("Reference");
                $type = "Reference";
            }

            // determine width and height of known image types
            $wh_input = $form->getInput("full_width_height");
            $wh = ilObjMediaObject::_determineWidthHeight(
                $format,
                $type,
                $mob_dir . "/" . $location,
                $media_item2->getLocation(),
                $full_wh_input["constr_prop"],
                ($form->getInput("full_size") == "original"),
                ($wh_input["width"] == "") ? null : (int) $wh_input["width"],
                ($wh_input["height"] == "") ? null : (int) $wh_input["height"]
            );

            $media_item2->setWidth($wh["width"]);
            $media_item2->setHeight($wh["height"]);

            if ($form->getInput("full_caption") != "") {
                $media_item2->setCaption($form->getInput("full_caption"));
            }
        }
    
        ilObjMediaObject::renameExecutables($mob_dir);
        ilMediaSvgSanitizer::sanitizeDir($mob_dir);	// see #20339
        $a_mob->update();
    }
    
    
    /**
     * Cancel saving
     * @throws ilCtrlException
     */
    public function cancelObject() : void
    {
        $this->ctrl->returnToParent($this);
    }

    public function editObject() : void
    {
        $tpl = $this->tpl;
        
        $this->setPropertiesSubTabs("general");

        $this->initForm("edit");
        $this->getValues();
        $tpl->setContent($this->form_gui->getHTML());
    }


    /**
     * resize images to specified size
     */
    public function resizeImagesObject() : void
    {
        // directory
        $mob_dir = ilObjMediaObject::_getDirectory($this->object->getId());

        // standard item
        $std_item = $this->object->getMediaItem("Standard");
        if ($std_item->getLocationType() == "LocalFile" &&
            is_int(strpos($std_item->getFormat(), "image"))
            ) {
            $file = $mob_dir . "/" . $std_item->getLocation();
            $location = ilObjMediaObject::_resizeImage(
                $file,
                $std_item->getWidth(),
                $std_item->getHeight()
            );
            $std_item->setLocation($location);
            $std_item->update();
        }

        // fullscreen item
        if ($this->object->hasFullscreenItem()) {
            $full_item = $this->object->getMediaItem("Fullscreen");
            if ($full_item->getLocationType() == "LocalFile" &&
                is_int(strpos($full_item->getFormat(), "image"))
                ) {
                $file = $mob_dir . "/" . $full_item->getLocation();
                $location = ilObjMediaObject::_resizeImage(
                    $file,
                    $full_item->getWidth(),
                    $full_item->getHeight()
                );
                $full_item->setLocation($location);
                $full_item->update();
            }
        }

        $this->ctrl->redirect($this, "edit");
    }


    /**
     * set original size of standard file
     */
    public function getStandardSizeObject() : void
    {
        $std_item = $this->object->getMediaItem("Standard");
        $mob_dir = ilObjMediaObject::_getDirectory($this->object->getId());

        if ($std_item->getLocationType() == "LocalFile") {
            $file = $mob_dir . "/" . $std_item->getLocation();

            $size = ilMediaImageUtil::getImageSize($file);

            $std_item->setWidth($size[0]);
            $std_item->setHeight($size[1]);
            $this->object->update();
        }
        $this->ctrl->redirect($this, "edit");
    }


    /**
     * set original size of fullscreen file
     */
    public function getFullscreenSizeObject() : void
    {
        $full_item = $this->object->getMediaItem("Fullscreen");
        $mob_dir = ilObjMediaObject::_getDirectory($this->object->getId());

        if ($full_item->getLocationType() == "LocalFile") {
            $file = $mob_dir . "/" . $full_item->getLocation();
            $size = ilMediaImageUtil::getImageSize($file);
            $full_item->setWidth($size[0]);
            $full_item->setHeight($size[1]);
            $this->object->update();
        }
        $this->ctrl->redirect($this, "edit");
    }

    public function savePropertiesObject() : void
    {
        $lng = $this->lng;
        $tpl = $this->tpl;
        $file = "";
        $type = "";
        
        $this->initForm("edit");
        $form = $this->form_gui;

        if ($form->checkInput()) {
            $title = trim($form->getInput("standard_title"));
            $this->object->setTitle($title);
            
            $std_item = $this->object->getMediaItem("Standard");
            $location = $std_item->getLocation();
            $format = $std_item->getFormat();
            if ($form->getInput("standard_type") == "Reference") {
                $format = ilObjMediaObject::getMimeType($form->getInput("standard_reference"), true);
                $std_item->setFormat($format);
                $std_item->setLocation(ilUtil::secureLink($form->getInput("standard_reference")));
                $std_item->setLocationType("Reference");
            }
            $mob_dir = ilObjMediaObject::_getDirectory($this->object->getId());
            if ($form->getInput("standard_type") == "File") {
                $resize = false;
                if ($_FILES['standard_file']['name'] != "") {
                    $file_name = ilObjMediaObject::fixFilename($_FILES['standard_file']['name']);
                    $file = $mob_dir . "/" . $file_name;
                    ilFileUtils::moveUploadedFile(
                        $_FILES['standard_file']['tmp_name'],
                        $file_name,
                        $file
                    );
    
                    // get mime type
                    $format = ilObjMediaObject::getMimeType($file);
                    $location = $file_name;
                    
                    $resize = true;
                } elseif ($form->getInput("standard_resize")) {
                    $file = $mob_dir . "/" . $location;
                    $resize = true;
                }
                
                // resize
                if ($resize) {
                    if ($form->getInput("standard_size") != "original" &&
                        is_int(strpos($format, "image"))) {
                        $wh_input = $form->getInput("standard_width_height");
                        $location = ilObjMediaObject::_resizeImage(
                            $file,
                            (int) $wh_input["width"],
                            (int) $wh_input["height"],
                            (boolean) $wh_input["constr_prop"]
                        );
                    }
                    $std_item->setFormat($format);
                    $std_item->setLocation($location);
                }
                
                $std_item->setLocationType("LocalFile");
            }
            $this->object->setDescription($format);
            // determine width and height of known image types
            $wh_input = $form->getInput("standard_width_height");
            $wh = ilObjMediaObject::_determineWidthHeight(
                $format,
                $form->getInput("standard_type"),
                $mob_dir . "/" . $location,
                $std_item->getLocation(),
                $wh_input["constr_prop"],
                ($form->getInput("standard_size") == "original"),
                ($wh_input["width"] == "") ? null : (int) $wh_input["width"],
                ($wh_input["height"] == "") ? null : (int) $wh_input["height"]
            );
            if ($wh["info"] != "") {
                $this->tpl->setOnScreenMessage('info', $wh["info"], true);
            }
            $std_item->setWidth($wh["width"]);
            $std_item->setHeight($wh["height"]);

            // set caption
            $std_item->setCaption($form->getInput("standard_caption"));
            
            // text representation
            $std_item->setTextRepresentation($form->getInput("text_representation"));
            
            // set parameters
            if ($this->media_type->usesParameterProperty($std_item->getFormat())) {
                if ($this->media_type->usesAutoStartParameterOnly(
                    $std_item->getLocation(),
                    $std_item->getFormat()
                )) {
                    /*
                    if ($_POST["standard_autostart"]) {	// save only autostart flag
                        $std_item->setParameters('autostart="true"');
                    } else {
                        $std_item->setParameters("");
                    }*/
                } else {
                    $std_item->setParameters(utf8_decode($form->getInput("standard_parameters")));
                }
            }
    
            // "None" selected
            if ($form->getInput("full_type") == "None") {
                if ($this->object->hasFullscreenItem()) {		// delete existing
                    $this->object->removeMediaItem("Fullscreen");
                }
            } else {	// Not "None" -> we need one
                if ($this->object->hasFullscreenItem()) {	// take existing one
                    $full_item = $this->object->getMediaItem("Fullscreen");
                } else {		// create one
                    $full_item = new ilMediaItem();
                    $this->object->addMediaItem($full_item);
                    $full_item->setPurpose("Fullscreen");
                }
                $location = $full_item->getLocation();
                $format = $full_item->getFormat();
                if ($form->getInput("full_type") == "Reference") {
                    $format = ilObjMediaObject::getMimeType($form->getInput("full_reference"), true);
                    $full_item->setFormat($format);
                    $full_item->setLocationType("Reference");
                    $location = ilUtil::secureLink($form->getInput("full_reference"));
                    $type = "Reference";
                    $full_item->setLocation($location);
                }
                $mob_dir = ilObjMediaObject::_getDirectory($this->object->getId());
                if ($form->getInput("full_type") == "File") {
                    $resize = false;
                    if ($_FILES['full_file']['name'] != "") {
                        $full_file_name = ilObjMediaObject::fixFilename($_FILES['full_file']['name']);
                        $file = $mob_dir . "/" . $full_file_name;
                        ilFileUtils::moveUploadedFile(
                            $_FILES['full_file']['tmp_name'],
                            $full_file_name,
                            $file
                        );
    
                        $format = ilObjMediaObject::getMimeType($file);
                        $location = $full_file_name;
                        
                        $resize = true;
                    } elseif ($form->getInput("full_resize")) {
                        $file = $mob_dir . "/" . $location;
                        $resize = true;
                    }
                    
                    // resize
                    if ($resize) {
                        if ($form->getInput("full_size") != "original" &&
                            is_int(strpos($format, "image"))) {
                            $wh_input = $form->getInput("full_width_height");
                            $location = ilObjMediaObject::_resizeImage(
                                $file,
                                (int) $wh_input["width"],
                                (int) $wh_input["height"],
                                (boolean) $wh_input["constr_prop"]
                            );
                        }
                        $full_item->setFormat($format);
                        $full_item->setLocation($location);
                    }

                    $full_item->setLocationType("LocalFile");
                    $type = "File";
                }
                if ($form->getInput("full_type") == "Standard") {
                    $format = $std_item->getFormat();
                    $location = $std_item->getLocation();
                    $full_item->setLocationType($std_item->getLocationType());
                    $full_item->setFormat($format);
                    $full_item->setLocation($location);
                    $type = $std_item->getLocationType();
                    if ($type == "LocalFile") {
                        $type = "File";
                    }
                    // resize image
                    //echo "-".$_POST["full_size"]."-".is_int(strpos($format, "image"))."-".$full_item->getLocationType()."-";
                    if ($form->getInput("full_size") != "original" &&
                        is_int(strpos($format, "image")) &&
                        $full_item->getLocationType() == "LocalFile") {
                        $file = $mob_dir . "/" . $location;
                        $wh_input = $form->getInput("full_width_height");
                        $location = ilObjMediaObject::_resizeImage(
                            $file,
                            (int) $wh_input["width"],
                            (int) $wh_input["height"],
                            (boolean) $wh_input["constr_prop"]
                        );
                    }
                }
                
                // determine width and height of known image types
                $wh_input = $form->getInput("full_width_height");
                $wh = ilObjMediaObject::_determineWidthHeight(
                    $format,
                    $type,
                    $mob_dir . "/" . $location,
                    $full_item->getLocation(),
                    $wh_input["constr_prop"],
                    ($form->getInput("full_size") == "original"),
                    ($wh_input["width"] == "") ? null : (int) $wh_input["width"],
                    ($wh_input["height"] == "") ? null : (int) $wh_input["height"]
                );
                if ($wh["info"] != "") {
                    $this->tpl->setOnScreenMessage('info', $wh["info"], true);
                }

                $full_item->setWidth($wh["width"]);
                $full_item->setHeight($wh["height"]);
                $full_item->setLocation($location);
                
                $full_item->setCaption($form->getInput("full_caption"));
                
                // text representation
                $full_item->setTextRepresentation($form->getInput("full_text_representation"));

                
                // set parameters
                if ($this->media_type->usesParameterProperty($std_item->getFormat())) {
                    if ($this->media_type->usesAutoStartParameterOnly(
                        $std_item->getLocation(),
                        $std_item->getFormat()
                    )) {
                        /*
                        if ($_POST["full_autostart"]) {	// save only autostart flag
                            $full_item->setParameters('autostart="true"');
                        } else {
                            $full_item->setParameters("");
                        }*/
                    } else {
                        $full_item->setParameters(utf8_decode($form->getInput("full_parameters")));
                    }
                }
            }

            ilObjMediaObject::renameExecutables(ilObjMediaObject::_getDirectory($this->object->getId()));
            ilMediaSvgSanitizer::sanitizeDir(ilObjMediaObject::_getDirectory($this->object->getId()));	// see #20339

            $this->object->update();
            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this, "edit");
        } else {
            $this->form_gui->setValuesByPost();
            $tpl->setContent($this->form_gui->getHTML());
        }
    }

    /**
     * assign file to standard view
     */
    public function assignStandardObject(
        string $a_file
    ) : void {
        // determine directory
        $cur_subdir = dirname($a_file);
        $mob_dir = ilFileUtils::getWebspaceDir() . "/mobs/mm_" . $this->object->getId();
        $cur_dir = (!empty($cur_subdir))
            ? $mob_dir . "/" . $cur_subdir
            : $mob_dir;
        $file = $cur_dir . "/" . basename($a_file);
        $location = $a_file;

        if (!is_file($file)) {
            $this->ilias->raiseError($this->lng->txt("cont_select_file"), $this->ilias->error_obj->MESSAGE);
        }

        $std_item = $this->object->getMediaItem("Standard");
        $std_item->setLocationType("LocalFile");
        $std_item->setLocation($location);
        $format = ilObjMediaObject::getMimeType($file);
        $std_item->setFormat($format);
        $this->object->update();
        //		$this->ctrl->saveParameter($this, "cdir");
        $this->ctrl->redirectByClass("ilfilesystemgui", "listFiles");
    }


    /**
     * assign file to fullscreen view
     */
    public function assignFullscreenObject(
        string $a_file
    ) : void {
        // determine directory
        $cur_subdir = dirname($a_file);
        $mob_dir = ilFileUtils::getWebspaceDir() . "/mobs/mm_" . $this->object->getId();
        $cur_dir = (!empty($cur_subdir))
            ? $mob_dir . "/" . $cur_subdir
            : $mob_dir;
        $file = $cur_dir . "/" . basename($a_file);
        $location = $a_file;

        if (!is_file($file)) {
            $this->ilias->raiseError($this->lng->txt("cont_select_file"), $this->ilias->error_obj->MESSAGE);
        }

        if (!$this->object->hasFullscreenItem()) {	// create new fullscreen item
            $std_item = $this->object->getMediaItem("Standard");
            $mob_dir = ilFileUtils::getWebspaceDir() . "/mobs/mm_" . $this->object->getId();
            $file = $mob_dir . "/" . $location;
            $full_item = new ilMediaItem();
            $full_item->setMobId($std_item->getMobId());
            $full_item->setLocation($location);
            $full_item->setLocationType("LocalFile");
            $full_item->setFormat(ilObjMediaObject::getMimeType($file));
            $full_item->setPurpose("Fullscreen");
            $this->object->addMediaItem($full_item);
        } else {	// alter existing fullscreen item
            $full_item = $this->object->getMediaItem("Fullscreen");

            $full_item->setLocationType("LocalFile");
            $full_item->setLocation($location);
            $format = ilObjMediaObject::getMimeType($file);
            $full_item->setFormat($format);
        }
        $this->object->update();
        //		$this->ctrl->saveParameter($this, "cdir");
        $this->ctrl->redirectByClass("ilfilesystemgui", "listFiles");
    }


    /**
     * remove fullscreen view
     */
    public function removeFullscreenObject() : void
    {
        $this->object->removeMediaItem("Fullscreen");
        $this->object->update();

        $this->ctrl->redirect($this, "edit");
    }

    /**
     * add fullscreen view
     */
    public function addFullscreenObject() : void
    {
        if (!$this->object->hasFullscreenItem()) {
            $std_item = $this->object->getMediaItem("Standard");
            $full_item = new ilMediaItem();
            $full_item->setMobId($std_item->getMobId());
            $full_item->setLocation($std_item->getLocation());
            $full_item->setLocationType($std_item->getLocationType());
            $full_item->setFormat($std_item->getFormat());
            $full_item->setWidth($std_item->getWidth());
            $full_item->setHeight($std_item->getHeight());
            $full_item->setCaption($std_item->getCaption());
            $full_item->setTextRepresentation($std_item->getTextRepresentation());
            $full_item->setPurpose("Fullscreen");
            $this->object->addMediaItem($full_item);

            $this->object->update();
        }

        $this->ctrl->redirect($this, "edit");
    }

    /**
     * Show all media object usages (incl history)
     */
    public function showAllUsagesObject() : void
    {
        $this->showUsagesObject(true);
    }
    
    
    /**
     * show all usages of mob
     */
    public function showUsagesObject(
        bool $a_all = false
    ) : void {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $ilTabs->addSubTab(
            "current_usages",
            $lng->txt("cont_current_usages"),
            $ilCtrl->getLinkTarget($this, "showUsages")
        );
        
        $ilTabs->addSubTab(
            "all_usages",
            $lng->txt("cont_all_usages"),
            $ilCtrl->getLinkTarget($this, "showAllUsages")
        );
        
        if ($a_all) {
            $ilTabs->activateSubTab("all_usages");
            $cmd = "showAllUsages";
        } else {
            $ilTabs->activateSubTab("current_usages");
            $cmd = "showUsages";
        }

        /** @var ilObjMediaObject $mob */
        $mob = $this->object;
        $usages_table = new ilMediaObjectUsagesTableGUI(
            $this,
            $cmd,
            $mob,
            $a_all
        );
        $tpl->setContent($usages_table->getHTML());
    }

    /**
     * get media info as html
     */
    public static function _getMediaInfoHTML(
        ilObjMediaObject $a_mob
    ) : string {
        global $DIC;

        $lng = $DIC->language();

        $tpl = new ilTemplate("tpl.media_info.html", true, true, "Services/MediaObjects");
        $types = array("Standard", "Fullscreen");
        foreach ($types as $type) {
            if ($type == "Fullscreen" && !$a_mob->hasFullscreenItem()) {
                continue;
            }

            $med = $a_mob->getMediaItem($type);
            if (!$med) {
                return "";
            }

            $tpl->setCurrentBlock("media_info");
            if ($type == "Standard") {
                $tpl->setVariable("TXT_PURPOSE", $lng->txt("cont_std_view"));
            } else {
                $tpl->setVariable("TXT_PURPOSE", $lng->txt("cont_fullscreen"));
            }
            $tpl->setVariable("TXT_TYPE", $lng->txt("cont_" . strtolower($med->getLocationType())));
            $tpl->setVariable("VAL_LOCATION", $med->getLocation());
            if ($med->getLocationType() == "LocalFile") {
                $file = ilObjMediaObject::_getDirectory($med->getMobId()) . "/" . $med->getLocation();
                if (is_file($file)) {
                    $size = filesize($file);
                } else {
                    $size = 0;
                }
                $tpl->setVariable("VAL_FILE_SIZE", " ($size " . $lng->txt("bytes") . ")");
            }
            $tpl->setVariable("TXT_FORMAT", $lng->txt("cont_format"));
            $tpl->setVariable("VAL_FORMAT", $med->getFormat());
            if ($med->getWidth() != "" && $med->getHeight() != "") {
                $tpl->setCurrentBlock("size");
                $tpl->setVariable("TXT_SIZE", $lng->txt("size"));
                $tpl->setVariable("VAL_SIZE", $med->getWidth() . "x" . $med->getHeight());
                $tpl->parseCurrentBlock();
            }

            // original size
            if ($orig_size = $med->getOriginalSize()) {
                if (($orig_size["width"] ?? "") !== $med->getWidth() ||
                    ($orig_size["height"] ?? "") !== $med->getHeight()) {
                    $tpl->setCurrentBlock("orig_size");
                    $tpl->setVariable("TXT_ORIG_SIZE", $lng->txt("cont_orig_size"));
                    $tpl->setVariable("ORIG_WIDTH", $orig_size["width"]);
                    $tpl->setVariable("ORIG_HEIGHT", $orig_size["height"]);
                    $tpl->parseCurrentBlock();
                }
            }

            // output caption
            if (strlen($med->getCaption())) {
                $tpl->setCurrentBlock('additional_info');
                $tpl->setVariable('ADD_INFO', $lng->txt('cont_caption') . ': ' . $med->getCaption());
                $tpl->parseCurrentBlock();
            }

            // output keywords
            if ($type == "Standard") {
                if (count($kws = ilMDKeyword::lookupKeywords(0, $med->getMobId()))) {
                    $tpl->setCurrentBlock('additional_info');
                    $tpl->setVariable('ADD_INFO', $lng->txt('keywords') . ': ' . implode(', ', $kws));
                    $tpl->parseCurrentBlock();
                }
            }

            $tpl->setCurrentBlock("media_info");
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    /**
     * set admin tabs
     */
    public function setTabs() : void
    {
        // catch feedback message
        $this->getTabs();

        //$this->tpl->clearHeader();
        if (is_object($this->object) && strtolower(get_class($this->object)) == "ilobjmediaobject") {
            $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_mob.svg"));
            $this->tpl->setTitle($this->object->getTitle());
        } else {
            //$title = $this->object->getTitle();
            $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_mob.svg"));
            $this->tpl->setTitle($this->lng->txt("cont_create_mob"));
        }
    }

    public function getTabs() : void
    {
        $ilHelp = $this->help;

        $ilHelp->setScreenIdComponent("mob");
        
        if (is_object($this->object) && strtolower(get_class($this->object)) == "ilobjmediaobject"
            && $this->object->getId() > 0) {
            // object properties
            $this->tabs_gui->addTarget(
                "cont_mob_def_prop",
                $this->ctrl->getLinkTarget($this, "edit"),
                "edit",
                get_class($this)
            );
            
            $st_item = $this->object->getMediaItem("Standard");

            // link areas
            
            if (is_object($st_item) && $this->getEnabledMapAreas()) {
                $format = $st_item->getFormat();
                if (substr($format, 0, 5) == "image" && !is_int(strpos($format, "svg"))) {
                    $this->tabs_gui->addTarget(
                        "cont_def_map_areas",
                        $this->ctrl->getLinkTargetByClass(
                            array("ilobjmediaobjectgui", "ilimagemapeditorgui"),
                            "editMapAreas"
                        ),
                        "editMapAreas",
                        "ilimagemapeditorgui"
                    );
                }
            }

            // object usages
            $this->tabs_gui->addTarget(
                "cont_mob_usages",
                $this->ctrl->getLinkTarget($this, "showUsages"),
                "showUsages",
                get_class($this)
            );

            // object files
            $std_item = $this->object->getMediaItem("Standard");
            $full_item = $this->object->getMediaItem("Fullscreen");
            $mset = new ilSetting("mobs");
            if ($mset->get("file_manager_always") ||
                ($this->media_type->usesParameterProperty($std_item->getFormat()) ||
                    (is_object($full_item) && $this->media_type->usesParameterProperty($full_item->getFormat())))
            ) {
                $this->tabs_gui->addTarget(
                    "cont_files",
                    $this->ctrl->getLinkTargetByClass(
                        array("ilobjmediaobjectgui", "ilfilesystemgui"),
                        "listFiles"
                    ),
                    "",
                    "ilfilesystemgui"
                );
            }

            $mdgui = new ilObjectMetaDataGUI(null, $this->object->getType(), $this->object->getId());
            $mdtab = $mdgui->getTab("ilobjmediaobjectgui");
            if ($mdtab) {
                $this->tabs_gui->addTarget(
                    "meta_data",
                    $mdtab,
                    "",
                    "ilmdeditorgui"
                );
            }
        }

        // back to upper context
        if ($this->back_title != "") {
            $this->tabs_gui->setBackTarget(
                $this->back_title,
                $this->ctrl->getParentReturn($this)
            );
        }
    }
    
    /**
     * Include media object presentation JS
     */
    public static function includePresentationJS(
        ilGlobalTemplateInterface $a_tpl = null
    ) : void {
        global $DIC;

        $tpl = $DIC["tpl"];

        if ($a_tpl == null) {
            $a_tpl = $tpl;
        }
        
        iljQueryUtil::initjQuery($a_tpl);
        $a_tpl->addJavaScript(iljQueryUtil::getLocalMaphilightPath());
        $a_tpl->addJavaScript("./Services/COPage/js/ilCOPagePres.js");
        
        ilPlayerUtil::initMediaElementJs($a_tpl);
    }
    
    public function setPropertiesSubTabs(
        string $a_active
    ) : void {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $ilTabs->activateTab("cont_mob_def_prop");
        
        $ilTabs->addSubTab(
            "general",
            $lng->txt("mob_general"),
            $ilCtrl->getLinkTarget($this, "edit")
        );
        
        if ($this->object->getMediaItem("Standard")->getFormat() == "video/webm" ||
            $this->object->getMediaItem("Standard")->getFormat() == "video/mp4") {
            $ilTabs->addSubTab(
                "subtitles",
                $lng->txt("mob_subtitles"),
                $ilCtrl->getLinkTarget($this, "listSubtitleFiles")
            );
        }
        
        $ilTabs->activateSubTab($a_active);
    }
    
    public function listSubtitleFilesObject() : void
    {
        $ilToolbar = $this->toolbar;
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilUser = $this->user;
        
        $this->setPropertiesSubTabs("subtitles");
        
        // upload file
        $ilToolbar->setFormAction($ilCtrl->getFormAction($this), true);
        $fi = new ilFileInputGUI($lng->txt("mob_subtitle_file") . " (.srt)", "subtitle_file");
        $fi->setSuffixes(array("srt"));
        $ilToolbar->addInputItem($fi, true);
        
        // language
        $options = ilMDLanguageItem::_getLanguages();
        $si = new ilSelectInputGUI($this->lng->txt("mob_language"), "language");
        $si->setOptions($options);
        $si->setValue($ilUser->getLanguage());
        $ilToolbar->addInputItem($si, true);

        $ilToolbar->addFormButton($lng->txt("upload"), "uploadSubtitleFile");

        $ilToolbar->addSeparator();
        $ilToolbar->addFormButton($lng->txt("mob_upload_multi_srt"), "uploadMultipleSubtitleFileForm");

        /** @var ilObjMediaObject $mob */
        $mob = $this->object;
        $tab = new ilMobSubtitleTableGUI($this, "listSubtitleFiles", $mob);
            
        $tpl->setContent($tab->getHTML());
    }
    
    public function uploadSubtitleFileObject() : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        if ($this->object->uploadSrtFile(
            $_FILES["subtitle_file"]["tmp_name"],
            $this->sub_title_request->getLanguage()
        )) {
            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        }
        $ilCtrl->redirect($this, "listSubtitleFiles");
    }
    
    /**
     * Confirm srt file deletion
     */
    public function confirmSrtDeletionObject() : void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;
            
        $lng->loadLanguageModule("meta");

        $srts = $this->sub_title_request->getSrtFiles();
        if (count($srts) == 0) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "listSubtitleFiles");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("mob_really_delete_srt"));
            $cgui->setCancel($lng->txt("cancel"), "listSubtitleFiles");
            $cgui->setConfirm($lng->txt("delete"), "deleteSrtFiles");
            
            foreach ($srts as $i) {
                $cgui->addItem("srt[]", $i, "subtitle_" . $i . ".srt (" . $lng->txt("meta_l_" . $i) . ")");
            }
            
            $tpl->setContent($cgui->getHTML());
        }
    }
    
    /**
     * Delete srt files
     */
    public function deleteSrtFilesObject() : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $srts = $this->sub_title_request->getSrtFiles();
        foreach ($srts as $i) {
            if (strlen($i) == 2 && !is_int(strpos($i, "."))) {
                $this->object->removeAdditionalFile("srt/subtitle_" . $i . ".srt");
            }
        }
        $this->tpl->setOnScreenMessage('success', $lng->txt("mob_srt_files_deleted"), true);
        $ilCtrl->redirect($this, "listSubtitleFiles");
    }

    public function uploadMultipleSubtitleFileFormObject() : void
    {
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->tpl->setOnScreenMessage('info', $lng->txt("mob_upload_multi_srt_howto"));

        $this->setPropertiesSubTabs("subtitles");

        // upload file
        $ilToolbar->setFormAction($ilCtrl->getFormAction($this), true);
        $fi = new ilFileInputGUI($lng->txt("mob_subtitle_file") . " (.zip)", "subtitle_file");
        $fi->setSuffixes(array("zip"));
        $ilToolbar->addInputItem($fi, true);

        $ilToolbar->addFormButton($lng->txt("upload"), "uploadMultipleSubtitleFile");
    }

    public function uploadMultipleSubtitleFileObject() : void
    {
        try {
            $this->object->uploadMultipleSubtitleFile(ilArrayUtil::stripSlashesArray($_FILES["subtitle_file"]));
            $this->ctrl->redirect($this, "showMultiSubtitleConfirmationTable");
        } catch (ilMediaObjectsException $e) {
            $this->tpl->setOnScreenMessage('failure', $e->getMessage(), true);
            $this->ctrl->redirect($this, "uploadMultipleSubtitleFileForm");
        }
    }

    /**
     * List of srt files in zip file
     */
    public function showMultiSubtitleConfirmationTableObject() : void
    {
        $tpl = $this->tpl;

        $this->setPropertiesSubTabs("subtitles");

        $tab = new ilMultiSrtConfirmationTable2GUI($this, "showMultiSubtitleConfirmationTable");
        $tpl->setContent($tab->getHTML());
    }

    /**
     * Cancel Multi Feedback
     */
    public function cancelMultiSrtObject() : void
    {
        $this->object->clearMultiSrtDirectory();
        $this->ctrl->redirect($this, "listSubtitleFiles");
    }

    /**
     * Save selected srt files as new srt files
     */
    public function saveMultiSrtObject() : void
    {
        $ilCtrl = $this->ctrl;
        $srt_files = $this->object->getMultiSrtFiles();
        $files = $this->sub_title_request->getFiles();
        foreach ($files as $f) {
            foreach ($srt_files as $srt_file) {
                if ($f == $srt_file["filename"]) {
                    $this->object->uploadSrtFile($this->object->getMultiSrtUploadDir() . "/" . $srt_file["filename"], $srt_file["lang"], "rename");
                }
            }
        }
        $this->object->clearMultiSrtDirectory();
        $ilCtrl->redirect($this, "listSubtitleFiles");
    }
}
