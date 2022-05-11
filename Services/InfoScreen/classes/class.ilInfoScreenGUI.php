<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

use ILIAS\InfoScreen\StandardGUIRequest;

/**
 * Class ilInfoScreenGUI
 *
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ilInfoScreenGUI: ilNoteGUI, ilColumnGUI, ilPublicUserProfileGUI
 * @ilCtrl_Calls ilInfoScreenGUI: ilCommonActionDispatcherGUI
 */
class ilInfoScreenGUI
{
    protected ilTabsGUI $tabs_gui;
    protected ilRbacSystem $rbacsystem;
    protected ilGlobalPageTemplate $tpl;
    protected ilAccessHandler $access;
    protected ilObjUser $user;
    protected ilTree $tree;
    protected ilSetting $settings;
    public ilLanguage $lng;
    public ilCtrl $ctrl;
    public ?object $gui_object;
    public array $top_buttons = array();
    public array $top_formbuttons = array();
    public array $hiddenelements = array();
    public string $table_class = "il_InfoScreen";
    public bool $open_form_tag = true;
    public bool $close_form_tag = true;
    protected ?int $contextRefId = null;
    protected ?int $contextObjId = null;
    protected ?string $contentObjType = null;
    public string $form_action;
    protected bool $booking_enabled = false;
    protected bool $availability_enabled = true;
    protected bool $hidden = false;
    protected array $section = [];
    protected array $block_property = [];
    protected bool $news_editing = false;
    protected bool $show_hidden_toggle = false;
    protected int $sec_nr = 0;
    protected bool $private_notes_enabled = false;
    protected bool $news_enabled = false;
    protected bool $feedback_enabled = false;
    protected bool $learning_progress_enabled = false;
    protected StandardGUIRequest $request;


    public function __construct(?object $a_gui_object = null)
    {
        global $DIC;

        $this->rbacsystem = $DIC->rbac()->system();
        $this->tpl = $DIC["tpl"];
        $this->access = $DIC->access();
        $this->user = $DIC->user();
        $this->tree = $DIC->repositoryTree();
        $this->settings = $DIC->settings();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $ilTabs = $DIC->tabs();

        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->tabs_gui = $ilTabs;
        $this->gui_object = $a_gui_object;
        $this->form_action = "";
        $this->top_formbuttons = array();
        $this->hiddenelements = array();
        $this->request = new StandardGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );
    }

    /**
     * @throws ilCtrlException
     * @throws ilDatabaseException
     * @throws ilDateTimeException
     * @throws ilObjectNotFoundException
     */
    public function executeCommand() : void
    {
        $tpl = $this->tpl;

        $next_class = $this->ctrl->getNextClass($this);

        $cmd = $this->ctrl->getCmd("showSummary");
        $this->ctrl->setReturn($this, "showSummary");
        
        $this->setTabs();

        switch ($next_class) {
            case "ilnotegui":
                if ($this->ctrl->isAsynch()) {
                    $this->showNotesSection();
                } else {
                    $this->showSummary();    // forwards command
                }
                break;

            case "ilcolumngui":
                $this->showSummary();
                break;

            case "ilpublicuserprofilegui":
                $user_profile = new ilPublicUserProfileGUI($this->request->getUserId());
                $user_profile->setBackUrl($this->ctrl->getLinkTarget($this, "showSummary"));
                $html = $this->ctrl->forwardCommand($user_profile);
                $tpl->setContent($html);
                break;
            
            case "ilcommonactiondispatchergui":
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;
                
            default:
                $this->$cmd();
                break;
        }
    }

    public function setTableClass(string $a_val) : void
    {
        $this->table_class = $a_val;
    }
    
    public function getTableClass() : string
    {
        return $this->table_class;
    }
    
    public function enablePrivateNotes(bool $a_enable = true) : void
    {
        $this->private_notes_enabled = $a_enable;
    }

    public function enableLearningProgress(bool $a_enable = true) : void
    {
        $this->learning_progress_enabled = $a_enable;
    }

    public function enableAvailability(bool $a_enable = true) : void
    {
        $this->availability_enabled = $a_enable;
    }

    public function enableBookingInfo(bool $a_enable = true) : void
    {
        $this->booking_enabled = $a_enable;
    }


    public function enableFeedback(bool $a_enable = true) : void
    {
        $this->feedback_enabled = $a_enable;
    }

    public function enableNews(bool $a_enable = true) : void
    {
        $this->news_enabled = $a_enable;
    }

    public function enableNewsEditing(bool $a_enable = true) : void
    {
        $this->news_editing = $a_enable;
    }

    /**
    * This function is supposed to be used for block type specific
    * properties, that should be passed to ilBlockGUI->setProperty
    */
    public function setBlockProperty(string $a_block_type, string $a_property, string $a_value) : void
    {
        $this->block_property[$a_block_type][$a_property] = $a_value;
    }
    
    public function getAllBlockProperties() : array
    {
        return $this->block_property;
    }

    public function addSection(string $a_title) : void
    {
        $this->sec_nr++;
        $this->section[$this->sec_nr]["title"] = $a_title;
        $this->section[$this->sec_nr]["hidden"] = $this->hidden;
    }

    public function setFormAction(string $a_form_action) : void
    {
        $this->form_action = $a_form_action;
    }

    public function removeFormAction() : void
    {
        $this->form_action = "";
    }

    /**
    * add a property to current section
    *
    * @param	string	$a_name		property name string
    * @param	string	$a_value	property value
    * @param	string	$a_link		link (will link the property value string)
    */
    public function addProperty(string $a_name, string $a_value, string $a_link = "") : void
    {
        $this->section[$this->sec_nr]["properties"][] =
            array("name" => $a_name, "value" => $a_value,
                "link" => $a_link);
    }

    /**
     * @deprecated
     */
    public function addPropertyCheckbox(
        string $a_name,
        string $a_checkbox_name,
        string $a_checkbox_value,
        string $a_checkbox_label = "",
        bool $a_checkbox_checked = false
    ) : void {
        $checkbox = "<input type=\"checkbox\" name=\"$a_checkbox_name\" value=\"$a_checkbox_value\" id=\"$a_checkbox_name$a_checkbox_value\"";
        if ($a_checkbox_checked) {
            $checkbox .= " checked=\"checked\"";
        }
        $checkbox .= " />";
        if (strlen($a_checkbox_label)) {
            $checkbox .= "&nbsp;<label for=\"$a_checkbox_name$a_checkbox_value\">$a_checkbox_label</label>";
        }
        $this->section[$this->sec_nr]["properties"][] =
            array("name" => $a_name, "value" => $checkbox);
    }

    /**
     * @deprecated
     */
    public function addPropertyTextinput(
        string $a_name,
        string $a_input_name,
        string $a_input_value = "",
        string $a_input_size = "",
        string $direct_button_command = "",
        string $direct_button_label = "",
        bool $direct_button_primary = false
    ) : void {
        $input = "<span class=\"form-inline\"><input class=\"form-control\" type=\"text\" name=\"$a_input_name\" id=\"$a_input_name\"";
        if (strlen($a_input_value)) {
            $input .= " value=\"" . ilLegacyFormElementsUtil::prepareFormOutput($a_input_value) . "\"";
        }
        if (strlen($a_input_size)) {
            $input .= " size=\"" . $a_input_size . "\"";
        }
        $input .= " />";
        if (strlen($direct_button_command) && strlen($direct_button_label)) {
            $css = "";
            if ($direct_button_primary) {
                $css = " btn-primary";
            }
            $input .= " <input type=\"submit\" class=\"btn btn-default" . $css . "\" name=\"cmd[$direct_button_command]\" value=\"$direct_button_label\" />";
        }
        $input .= "</span>";
        $this->section[$this->sec_nr]["properties"][] =
            array("name" => "<label for=\"$a_input_name\">$a_name</label>", "value" => $input);
    }

    /**
     * @inheritDoc
     */
    public function addButton(
        string $a_title,
        string $a_link,
        string $a_frame = "",
        string $a_position = "top",
        bool $a_primary = false
    ) : void {
        if ($a_position == "top") {
            $this->top_buttons[] =
                array("title" => $a_title,"link" => $a_link,"target" => $a_frame,"primary" => $a_primary);
        }
    }

    /**
     * add a form button to the info screen
     * the form buttons are only valid if a form action is set
     */
    public function addFormButton(
        string $a_command,
        string $a_title,
        string $a_position = "top"
    ) : void {
        if ($a_position == "top") {
            $this->top_formbuttons[] = array("command" => $a_command, "title" => $a_title);
        }
    }

    public function addHiddenElement(string $a_name, string $a_value) : void
    {
        $this->hiddenelements[] = array("name" => $a_name, "value" => $a_value);
    }

    public function addMetaDataSections(int $a_rep_obj_id, int $a_obj_id, string $a_type) : void
    {
        $lng = $this->lng;

        $lng->loadLanguageModule("meta");

        $md = new ilMD($a_rep_obj_id, $a_obj_id, $a_type);
        $description = "";
        $langs = '';
        $keywords = "";
        if ($md_gen = $md->getGeneral()) {
            // get first descrption
            // The description is shown on the top of the page.
            // Thus it is not necessary to show it again.
            foreach ($md_gen->getDescriptionIds() as $id) {
                $md_des = $md_gen->getDescription($id);
                $description = $md_des->getDescription();
                break;
            }

            // get language(s)
            $language_arr = [];
            foreach ($md_gen->getLanguageIds() as $id) {
                $md_lan = $md_gen->getLanguage($id);
                if ($md_lan->getLanguageCode() != "") {
                    $language_arr[] = $lng->txt("meta_l_" . $md_lan->getLanguageCode());
                }
            }
            $langs = implode(", ", $language_arr);

            // keywords
            $keyword_arr = [];
            foreach ($md_gen->getKeywordIds() as $id) {
                $md_key = $md_gen->getKeyword($id);
                $keyword_arr[] = $md_key->getKeyword();
            }
            $keywords = implode(", ", $keyword_arr);
        }

        // authors
        $author = "";
        if (is_object($lifecycle = $md->getLifecycle())) {
            $sep = "";
            foreach (($lifecycle->getContributeIds()) as $con_id) {
                $md_con = $lifecycle->getContribute($con_id);
                if ($md_con->getRole() == "Author") {
                    foreach ($md_con->getEntityIds() as $ent_id) {
                        $md_ent = $md_con->getEntity($ent_id);
                        $author = $author . $sep . $md_ent->getEntity();
                        $sep = ", ";
                    }
                }
            }
        }

        // copyright
        $copyright = "";
        if (is_object($rights = $md->getRights())) {
            $copyright = ilMDUtils::_parseCopyright($rights->getDescription());
        }

        // learning time
        #if(is_object($educational = $md->getEducational()))
        #{
        #	$learning_time = $educational->getTypicalLearningTime();
        #}
        $learning_time = "";
        if (is_object($educational = $md->getEducational())) {
            if ($seconds = $educational->getTypicalLearningTimeSeconds()) {
                $learning_time = ilDatePresentation::secondsToString($seconds);
            }
        }


        // output

        // description
        if ($description != "") {
            $this->addSection($lng->txt("description"));
            $this->addProperty("", nl2br($description));
        }

        // general section
        $this->addSection($lng->txt("meta_general"));
        if ($langs != "") {	// language
            $this->addProperty(
                $lng->txt("language"),
                $langs
            );
        }
        if ($keywords != "") {	// keywords
            $this->addProperty(
                $lng->txt("keywords"),
                $keywords
            );
        }
        if ($author != "") {		// author
            $this->addProperty(
                $lng->txt("author"),
                $author
            );
        }
        if ($copyright != "") {		// copyright
            $this->addProperty(
                $lng->txt("meta_copyright"),
                $copyright
            );
        }
        if ($learning_time != "") {		// typical learning time
            $this->addProperty(
                $lng->txt("meta_typical_learning_time"),
                $learning_time
            );
        }
    }

    /**
     * @throws ilDatabaseException
     * @throws ilDateTimeException
     * @throws ilObjectNotFoundException
     */
    public function addObjectSections() : void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $ilAccess = $this->access;
        $tree = $this->tree;

        // resource bookings
        if ($this->booking_enabled) {
            $booking_adapter = new ilBookingInfoScreenAdapter($this);
            $booking_adapter->add();
        }

        $this->addSection($lng->txt("additional_info"));
        $a_obj = $this->gui_object->getObject();
                
        // links to the object
        if (is_object($a_obj)) {
            // permanent link
            $type = $a_obj->getType();
            $ref_id = $a_obj->getRefId();
            
            if ($ref_id) {
                if (ilECSServerSettings::getInstance()->activeServerExists()) {
                    $this->addProperty(
                        $lng->txt("object_id"),
                        $a_obj->getId()
                    );
                }

                $this->tpl->setPermanentLink($type, $ref_id);

                // links to resource
                if ($ilAccess->checkAccess("write", "", $ref_id) ||
                    $ilAccess->checkAccess("edit_permissions", "", $ref_id)) {
                    $obj_id = $a_obj->getId();
                    $rs = ilObject::_getAllReferences($obj_id);
                    $refs = array();
                    foreach ($rs as $r) {
                        if ($tree->isInTree($r)) {
                            $refs[] = $r;
                        }
                    }
                    if (count($refs) > 1) {
                        $links = $sep = "";
                        foreach ($refs as $r) {
                            $cont_loc = new ilLocatorGUI();
                            $cont_loc->addContextItems($r, true);
                            $links .= $sep . $cont_loc->getHTML();
                            $sep = "<br />";
                        }

                        $this->addProperty(
                            $lng->txt("res_links"),
                            '<div class="small">' . $links . '</div>'
                        );
                    }
                }
            }
        }
                
                
        // creation date
        if ($ilAccess->checkAccess("edit_permissions", "", $ref_id)) {
            $this->addProperty(
                $lng->txt("create_date"),
                ilDatePresentation::formatDate(new ilDateTime($a_obj->getCreateDate(), IL_CAL_DATETIME))
            );

            // owner
            if ($ilUser->getId() != ANONYMOUS_USER_ID and $a_obj->getOwner()) {
                if (ilObjUser::userExists(array($a_obj->getOwner()))) {
                    /** @var  $ownerObj ilObjUser */
                    $ownerObj = ilObjectFactory::getInstanceByObjId($a_obj->getOwner(), false);
                } else {
                    $ownerObj = ilObjectFactory::getInstanceByObjId(6, false);
                }

                if (!is_object($ownerObj) || $ownerObj->getType() != "usr") {        // root user deleted
                    $this->addProperty($lng->txt("owner"), $lng->txt("no_owner"));
                } elseif ($ownerObj->hasPublicProfile()) {
                    $ilCtrl->setParameterByClass("ilpublicuserprofilegui", "user_id", $ownerObj->getId());
                    $this->addProperty(
                        $lng->txt("owner"),
                        $ownerObj->getPublicName(),
                        $ilCtrl->getLinkTargetByClass("ilpublicuserprofilegui", "getHTML")
                    );
                } else {
                    $this->addProperty($lng->txt("owner"), $ownerObj->getPublicName());
                }
            }
        }

        // change event
        if (ilChangeEvent::_isActive()) {
            if ($ilUser->getId() != ANONYMOUS_USER_ID) {
                $readEvents = ilChangeEvent::_lookupReadEvents($a_obj->getId());
                $count_users = 0;
                $count_user_reads = 0;
                $count_anonymous_reads = 0;
                foreach ($readEvents as $evt) {
                    if ($evt['usr_id'] == ANONYMOUS_USER_ID) {
                        $count_anonymous_reads += $evt['read_count'];
                    } else {
                        $count_user_reads += $evt['read_count'];
                        $count_users++;
                        /* to do: if ($evt['user_id'] is member of $this->getRefId())
                        {
                            $count_members++;
                        }*/
                    }
                }
                if ($count_anonymous_reads > 0) {
                    $this->addProperty($this->lng->txt("readcount_anonymous_users"), $count_anonymous_reads);
                }
                if ($count_user_reads > 0) {
                    $this->addProperty($this->lng->txt("readcount_users"), $count_user_reads);
                }
                if ($count_users > 0) {
                    $this->addProperty($this->lng->txt("accesscount_registered_users"), (string) $count_users);
                }
            }
        }
        // END ChangeEvent: Display change event info

        // WebDAV: Display locking information
        if (ilDAVActivationChecker::_isActive()) {
            if ($ilUser->getId() != ANONYMOUS_USER_ID) {
                $webdav_dic = new ilWebDAVDIC();
                $webdav_dic->initWithoutDIC();
                $webdav_lock_backend = $webdav_dic->locksbackend();
                // Show lock info
                if ($ilUser->getId() != ANONYMOUS_USER_ID) {
                    if ($lock = $webdav_lock_backend->getLocksOnObjectId($this->gui_object->getObject()->getId())) {
                        /** @var ilWebDAVLockObject $lock */
                        $lock_user = new ilObjUser($lock->getIliasOwner());
                        $this->addProperty(
                            $this->lng->txt("in_use_by"),
                            $lock_user->getPublicName(),
                            "./ilias.php?user=" . $lock_user->getId() . '&cmd=showUserProfile&cmdClass=ildashboardgui&cmdNode=1&baseClass=ilDashboardGUI'
                        );
                    }
                }
            }
        }
    }
    // END ChangeEvent: Display standard object info

    /**
     * @throws ilCtrlException
     * @throws ilDatabaseException
     * @throws ilDateTimeException
     * @throws ilObjectNotFoundException
     */
    public function showSummary() : void
    {
        $tpl = $this->tpl;

        $tpl->setContent($this->getCenterColumnHTML());
        $tpl->setRightContent($this->getRightColumnHTML());
    }


    /**
     * @return string
     * @throws ilCtrlException
     * @throws ilDatabaseException
     * @throws ilDateTimeException
     * @throws ilObjectNotFoundException
     */
    public function getCenterColumnHTML() : string
    {
        $ilCtrl = $this->ctrl;
        $html = "";
        $column_gui = new ilColumnGUI("info", IL_COL_CENTER);
        $this->setColumnSettings($column_gui);

        if (!$ilCtrl->isAsynch()) {
            if ($column_gui->getScreenMode() != IL_SCREEN_SIDE) {
                // right column wants center
                if ($column_gui->getCmdSide() == IL_COL_RIGHT) {
                    $column_gui = new ilColumnGUI("info", IL_COL_RIGHT);
                    $this->setColumnSettings($column_gui);
                    $html = $ilCtrl->forwardCommand($column_gui);
                }
                // left column wants center
                if ($column_gui->getCmdSide() == IL_COL_LEFT) {
                    $column_gui = new ilColumnGUI("info", IL_COL_LEFT);
                    $this->setColumnSettings($column_gui);
                    $html = $ilCtrl->forwardCommand($column_gui);
                }
            } else {
                $html = $this->getHTML();
            }
        }
        
        return $html;
    }

    /**
     * @return string
     * @throws ilCtrlException
     */
    public function getRightColumnHTML() : string
    {
        $ilCtrl = $this->ctrl;

        $html = "";
        $column_gui = new ilColumnGUI("info", IL_COL_RIGHT);
        $this->setColumnSettings($column_gui);

        if ($ilCtrl->getNextClass() == "ilcolumngui" &&
            $column_gui->getCmdSide() == IL_COL_RIGHT &&
            $column_gui->getScreenMode() == IL_SCREEN_SIDE) {
            $html = $ilCtrl->forwardCommand($column_gui);
        } elseif (!$ilCtrl->isAsynch() && $this->news_enabled) {
            $html = $ilCtrl->getHTML($column_gui);
        }

        return $html;
    }

    /**
    * Set column settings.
    */
    public function setColumnSettings(ilColumnGUI $column_gui) : void
    {
        $column_gui->setEnableEdit($this->news_editing);
        $column_gui->setRepositoryMode(true);
        $column_gui->setAllBlockProperties($this->getAllBlockProperties());
    }
    
    public function setOpenFormTag(bool $a_val) : void
    {
        $this->open_form_tag = $a_val;
    }

    public function setCloseFormTag(bool $a_val) : void
    {
        $this->close_form_tag = $a_val;
    }

    /**
     * @return string
     * @throws ilCtrlException
     * @throws ilDatabaseException
     * @throws ilDateTimeException
     * @throws ilObjectNotFoundException
     */
    public function getHTML() : string
    {
        $lng = $this->lng;
        $ilSetting = $this->settings;
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        
        $tpl = new ilTemplate("tpl.infoscreen.html", true, true, "Services/InfoScreen");

        // other class handles form action (@todo: this is not implemented/tested)
        if ($this->form_action == "") {
            $this->setFormAction($ilCtrl->getFormAction($this));
        }
        
        iljQueryUtil::initjQuery();

        if ($this->hidden) {
            $tpl->touchBlock("hidden_js");
            if ($this->show_hidden_toggle) {
                $this->addButton($lng->txt("show_hidden_sections"), "JavaScript:toggleSections(this, '" . $lng->txt("show_hidden_sections") . "', '" . $lng->txt("hide_visible_sections") . "');");
            }
        }
        
        
        // DEPRECATED - use ilToolbarGUI
        
        // add top buttons
        if (count($this->top_buttons) > 0) {
            $tpl->addBlockFile("TOP_BUTTONS", "top_buttons", "tpl.buttons.html");

            foreach ($this->top_buttons as $button) {
                // view button
                $tpl->setCurrentBlock("btn_cell");
                $tpl->setVariable("BTN_LINK", $button["link"]);
                $tpl->setVariable("BTN_TARGET", $button["target"]);
                $tpl->setVariable("BTN_TXT", $button["title"]);
                if ($button["primary"]) {
                    $tpl->setVariable("BTN_CLASS", " btn-primary");
                }
                $tpl->parseCurrentBlock();
            }
        }

        // add top formbuttons
        if ((count($this->top_formbuttons) > 0) && (strlen($this->form_action) > 0)) {
            $tpl->addBlockFile("TOP_FORMBUTTONS", "top_submitbuttons", "tpl.submitbuttons.html", "Services/InfoScreen");

            foreach ($this->top_formbuttons as $button) {
                // view button
                $tpl->setCurrentBlock("btn_submit_cell");
                $tpl->setVariable("BTN_COMMAND", $button["command"]);
                $tpl->setVariable("BTN_NAME", $button["title"]);
                $tpl->parseCurrentBlock();
            }
        }

        // add form action
        if (strlen($this->form_action) > 0) {
            if ($this->open_form_tag) {
                $tpl->setCurrentBlock("formtop");
                $tpl->setVariable("FORMACTION", $this->form_action);
                $tpl->parseCurrentBlock();
            }
            
            if ($this->close_form_tag) {
                $tpl->touchBlock("formbottom");
            }
        }

        if (count($this->hiddenelements)) {
            foreach ($this->hiddenelements as $hidden) {
                $tpl->setCurrentBlock("hidden_element");
                $tpl->setVariable("HIDDEN_NAME", $hidden["name"]);
                $tpl->setVariable("HIDDEN_VALUE", $hidden["value"]);
                $tpl->parseCurrentBlock();
            }
        }

        if ($this->availability_enabled) {
            $this->addAvailability();
        }

        $this->addPreconditions();

        // learning progress
        if ($this->learning_progress_enabled) {
            $this->showLearningProgress($tpl);
        }

        // notes section
        if ($this->private_notes_enabled && !$ilSetting->get('disable_notes')) {
            $html = $this->showNotesSection();
            $tpl->setCurrentBlock("notes");
            $tpl->setVariable("NOTES", $html);
            $tpl->parseCurrentBlock();
        }

        // tagging
        if (
            isset($this->gui_object) &&
            method_exists($this->gui_object, "getObject") &&
            is_object($this->gui_object->getObject())
        ) {
            $tags_set = new ilSetting("tags");
            if ($tags_set->get("enable") && $ilUser->getId() != ANONYMOUS_USER_ID) {
                $this->addTagging();
            }

            $this->addObjectSections();
        }

        // render all sections
        for ($i = 1; $i <= $this->sec_nr; $i++) {
            if (isset($this->section[$i]["properties"])) {
                // section properties
                foreach ($this->section[$i]["properties"] as $property) {
                    if ($property["name"] != "") {
                        if ($property["link"] == "") {
                            $tpl->setCurrentBlock("pv");
                            $tpl->setVariable("TXT_PROPERTY_VALUE", $property["value"]);
                        } else {
                            $tpl->setCurrentBlock("lpv");
                            $tpl->setVariable("TXT_PROPERTY_LVALUE", $property["value"]);
                            $tpl->setVariable("LINK_PROPERTY_VALUE", $property["link"]);
                        }
                        $tpl->parseCurrentBlock();
                        $tpl->setCurrentBlock("property_row");
                        $tpl->setVariable("TXT_PROPERTY", $property["name"]);
                    } else {
                        $tpl->setCurrentBlock("property_full_row");
                        $tpl->setVariable("TXT_PROPERTY_FULL_VALUE", $property["value"]);
                    }
                    $tpl->parseCurrentBlock();
                }

                // section header
                if ($this->section[$i]["hidden"]) {
                    $tpl->setVariable("SECTION_HIDDEN", " style=\"display:none;\"");
                    $tpl->setVariable("SECTION_ID", "hidable_" . $i);
                } else {
                    $tpl->setVariable("SECTION_ID", $i);
                }
                $tpl->setVariable("TCLASS", $this->getTableClass());
                $tpl->setVariable("TXT_SECTION", $this->section[$i]["title"]);
                $tpl->setCurrentBlock("row");
                $tpl->parseCurrentBlock();
            }
        }

        return $tpl->get();
    }

    public function getContextRefId() : int
    {
        if ($this->contextRefId !== null) {
            return $this->contextRefId;
        }

        return $this->gui_object->getObject()->getRefId();
    }

    public function setContextRefId(int $contextRefId) : void
    {
        $this->contextRefId = $contextRefId;
    }

    public function getContextObjId() : int
    {
        if ($this->contextObjId !== null) {
            return $this->contextObjId;
        }

        return $this->gui_object->getObject()->getId();
    }

    public function setContextObjId(int $contextObjId) : void
    {
        $this->contextObjId = $contextObjId;
    }

    public function getContentObjType() : string
    {
        if ($this->contentObjType !== null) {
            return $this->contentObjType;
        }

        return $this->gui_object->getObject()->getType();
    }

    public function setContentObjType(string $contentObjType) : void
    {
        $this->contentObjType = $contentObjType;
    }

    /**
     * @param ilTemplate $a_tpl
     * @throws ilDateTimeException
     */
    public function showLearningProgress(ilTemplate $a_tpl) : void
    {
        return;

        $ilUser = $this->user;
        $rbacsystem = $this->rbacsystem;

        if (!$rbacsystem->checkAccess('read', $this->getContextRefId())) {
            return;
        }
        if ($ilUser->getId() == ANONYMOUS_USER_ID) {
            return;
        }

        if (!ilObjUserTracking::_enabledLearningProgress()) {
            return;
        }
            
        $olp = ilObjectLP::getInstance($this->getContextObjId());
        if ($olp->getCurrentMode() != ilLPObjSettings::LP_MODE_MANUAL) {
            return;
        }

        $this->lng->loadLanguageModule('trac');

        // section header
        //		$a_tpl->setCurrentBlock("header_row");
        $a_tpl->setVariable(
            "TXT_SECTION",
            $this->lng->txt('learning_progress')
        );
        $a_tpl->parseCurrentBlock();
    }

    public function saveProgress(bool $redirect = true) : void
    {
        $ilUser = $this->user;

        $lp_marks = new ilLPMarks($this->getContextObjId(), $ilUser->getId());
        $lp_marks->setCompleted((bool) $this->request->getLPEdit());
        $lp_marks->update();

        ilLPStatusWrapper::_updateStatus($this->getContextObjId(), $ilUser->getId());

        $this->lng->loadLanguageModule('trac');
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('trac_updated_status'), true);

        if ($redirect) {
            $this->ctrl->redirect($this, ""); // #14993
        }
    }


    /**
     * @return string
     * @throws ilCtrlException
     */
    public function showNotesSection() : string
    {
        global $DIC;

        $ilAccess = $this->access;
        $ilSetting = $this->settings;
        $DIC->notes()->gui()->initJavascript();

        $next_class = $this->ctrl->getNextClass($this);
        $notes_gui = new ilNoteGUI(
            $this->gui_object->getObject()->getId(),
            0,
            $this->gui_object->getObject()->getType()
        );
        $notes_gui->setUseObjectTitleHeader(false);
        
        // global switch
        if ($ilSetting->get("disable_comments")) {
            $notes_gui->enablePublicNotes(false);
        } else {
            $ref_id = $this->gui_object->getObject()->getRefId();
            $has_write = $ilAccess->checkAccess("write", "", $ref_id);
            
            if ($has_write && $ilSetting->get("comments_del_tutor", "1")) {
                $notes_gui->enablePublicNotesDeletion();
            }
            
            /* should probably be discussed further
            for now this will only work properly with comments settings
            (see ilNoteGUI constructor)
            */
            if ($has_write ||
                $ilAccess->checkAccess("edit_permissions", "", $ref_id)) {
                $notes_gui->enableCommentsSettings();
            }
        }

        /* moved to action menu
        $notes_gui->enablePrivateNotes();
        */

        if ($next_class == "ilnotegui") {
            $html = $this->ctrl->forwardCommand($notes_gui);
        } else {
            $html = $notes_gui->getCommentsHTML();
        }

        return $html;
    }

    /**
     * @param string $a_section section name. Leave empty to place this info string inside a section
     */
    public function showLDAPRoleGroupMappingInfo(string $a_section = '') : void
    {
        if (strlen($a_section)) {
            $this->addSection($a_section);
        }
        $ldap_mapping = ilLDAPRoleGroupMapping::_getInstance();
        if ($infos = $ldap_mapping->getInfoStrings($this->gui_object->getObject()->getId())) {
            $info_combined = '<div style="color:green;">';
            $counter = 0;
            foreach ($infos as $info_string) {
                if ($counter++) {
                    $info_combined .= '<br />';
                }
                $info_combined .= $info_string;
            }
            $info_combined .= '</div>';
            $this->addProperty($this->lng->txt('applications'), $info_combined);
        }
    }

    public function setTabs() : void
    {
        $this->getTabs($this->tabs_gui);
    }

    public function getTabs(ilTabsGUI $tabs_gui) : void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $force_active = ($next_class == "ilnotegui");

        $tabs_gui->addSubTabTarget(
            'summary',
            $this->ctrl->getLinkTarget($this, "showSummary"),
            array("showSummary", ""),
            get_class($this),
            "",
            $force_active
        );
    }

    
    public function addTagging() : void
    {
        $lng = $this->lng;

        $lng->loadLanguageModule("tagging");
        $tags_set = new ilSetting("tags");

        $tagging_gui = new ilTaggingGUI();
        $tagging_gui->setObject(
            $this->gui_object->getObject()->getId(),
            $this->gui_object->getObject()->getType()
        );
        
        $this->addSection($lng->txt("tagging_tags"));
        
        if ($tags_set->get("enable_all_users")) {
            $this->addProperty(
                $lng->txt("tagging_all_users"),
                $tagging_gui->getAllUserTagsForObjectHTML()
            );
        }
        
        $this->addProperty(
            $lng->txt("tagging_my_tags"),
            $tagging_gui->getTaggingInputHTML()
        );
    }
    
    public function saveTags() : void
    {
        $tagging_gui = new ilTaggingGUI();
        $tagging_gui->setObject(
            $this->gui_object->getObject()->getId(),
            $this->gui_object->getObject()->getType()
        );
        $tagging_gui->saveInput();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
        $this->ctrl->redirect($this, ""); // #14993
    }

    public function hideFurtherSections(bool $a_add_toggle = true) : void
    {
        $this->hidden = true;
        $this->show_hidden_toggle = $a_add_toggle;
    }

    public function getHiddenToggleButton() : string
    {
        $lng = $this->lng;
        
        return "<a onClick=\"toggleSections(this, '" . $lng->txt("show_hidden_sections") . "', '" . $lng->txt("hide_visible_sections") . "'); return false;\" href=\"#\">" . $lng->txt("show_hidden_sections") . "</a>";
    }


    /**
     * @throws ilDatabaseException
     * @throws ilDateTimeException
     */
    protected function addAvailability() : void
    {
        if (
            !is_object($this->gui_object) ||
            !method_exists($this->gui_object, "getObject") ||
            !is_object($this->gui_object->getObject())
        ) {
            return;
        }

        $obj = $this->gui_object->getObject();
        if ($obj->getRefId() <= 0) {
            return;
        }

        $act = new ilObjectActivation();
        $act->read($obj->getRefId());
        if ($act->getTimingType() == ilObjectActivation::TIMINGS_ACTIVATION) {
            $this->lng->loadLanguageModule("rep");
            $this->addSection($this->lng->txt("rep_activation_availability"));
            $this->addAccessPeriodProperty();
        }
    }

    /**
     * Add preconditions
     */
    protected function addPreconditions() : void
    {
        if (
            !is_object($this->gui_object) ||
            !method_exists($this->gui_object, "getObject") ||
            !is_object($this->gui_object->getObject())
        ) {
            return;
        }

        $obj = $this->gui_object->getObject();
        if ($obj->getRefId() <= 0) {
            return;
        }

        $conditions = ilConditionHandler::_getEffectiveConditionsOfTarget($obj->getRefId(), $obj->getId());

        if (sizeof($conditions)) {
            for ($i = 0; $i < count($conditions); $i++) {
                $conditions[$i]['title'] = ilObject::_lookupTitle($conditions[$i]['trigger_obj_id']);
            }
            $conditions = ilArrayUtil::sortArray($conditions, 'title', 'DESC');

            // Show obligatory and optional preconditions seperated
            $this->addPreconditionSection($obj, $conditions, true);
            $this->addPreconditionSection($obj, $conditions, false);
        }
    }

    protected function addPreconditionSection(
        ilObject $obj,
        array $conditions,
        bool $obligatory = true
    ) : void {
        $lng = $this->lng;
        $tree = $this->tree;

        $num_required = ilConditionHandler::calculateEffectiveRequiredTriggers($obj->getRefId(), $obj->getId());
        $num_optional_required =
            $num_required - count($conditions) + count(ilConditionHandler::getEffectiveOptionalConditionsOfTarget($obj->getRefId(), $obj->getId()));

        // Check if all conditions are fulfilled
        $visible_conditions = array();
        $passed_optional = 0;
        foreach ($conditions as $condition) {
            if ($obligatory and !$condition['obligatory']) {
                continue;
            }
            if (!$obligatory and $condition['obligatory']) {
                continue;
            }

            if ($tree->isDeleted($condition['trigger_ref_id'])) {
                continue;
            }

            $ok = ilConditionHandler::_checkCondition($condition) and
            !ilMemberViewSettings::getInstance()->isActive();

            if (!$ok) {
                $visible_conditions[] = $condition['id'];
            }

            if (!$obligatory and $ok) {
                ++$passed_optional;
                // optional passed
                if ($passed_optional >= $num_optional_required) {
                    return;
                }
            }
        }

        $properties = [];

        foreach ($conditions as $condition) {
            if (!in_array($condition['id'], $visible_conditions)) {
                continue;
            }

            $properties[] = [
                "condition" => ilConditionHandlerGUI::translateOperator(
                    $condition['trigger_obj_id'],
                    $condition['operator']
                ) . ' ' . $condition['value'],
                "title" => ilObject::_lookupTitle($condition['trigger_obj_id']),
                "link" => ilLink::_getLink($condition['trigger_ref_id'])
            ];
        }

        if (count($properties) > 0) {
            if ($obligatory) {
                $this->addSection($lng->txt("preconditions_obligatory_hint"));
            } else {
                $this->addSection(sprintf($lng->txt("preconditions_optional_hint"), $num_optional_required));
            }

            foreach ($properties as $p) {
                $this->addProperty(
                    $p["condition"],
                    "<a href='" . $p["link"] . "'>" . $p["title"] . "</a>"
                );
            }
        }
    }

    /**
     * @throws ilDatabaseException
     * @throws ilDateTimeException
     */
    public function addAccessPeriodProperty() : void
    {
        $a_obj = $this->gui_object->getObject();

        $this->lng->loadLanguageModule("rep");
        $this->lng->loadLanguageModule("crs");

        // links to the object
        if (is_object($a_obj)) {
            $act = new ilObjectActivation();
            $act->read($a_obj->getRefId());
            if ($act->getTimingType() == ilObjectActivation::TIMINGS_ACTIVATION) {
                $this->addProperty(
                    $this->lng->txt('rep_activation_access'),
                    ilDatePresentation::formatPeriod(
                        new ilDateTime($act->getTimingStart(), IL_CAL_UNIX),
                        new ilDateTime($act->getTimingEnd(), IL_CAL_UNIX)
                    )
                );
            } else {
                $this->addProperty(
                    $this->lng->txt('rep_activation_access'),
                    $this->lng->txt('crs_visibility_limitless')
                );
            }
        }
    }
}
