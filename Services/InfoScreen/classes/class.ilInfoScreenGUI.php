<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @defgroup ServicesInfoScreen Services/InfoScreen
 */

/**
* Class ilInfoScreenGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilInfoScreenGUI: ilNoteGUI, ilColumnGUI, ilPublicUserProfileGUI
* @ilCtrl_Calls ilInfoScreenGUI: ilCommonActionDispatcherGUI
*
* @ingroup ServicesInfoScreen
*/
class ilInfoScreenGUI
{
    /**
     * @var ilTabsGUI
     */
    protected $tabs_gui;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilObjUser
     */
    protected $user;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilSetting
     */
    protected $settings;

    public $lng;
    public $ctrl;
    public $gui_object;
    public $top_buttons = array();
    public $top_formbuttons = array();
    public $hiddenelements = array();
    public $table_class = "il_InfoScreen";
    public $open_form_tag = true;
    public $close_form_tag = true;

    /**
     * @var int|null
     */
    protected $contextRefId = null;

    /**
     * @var int|null
     */
    protected $contextObjId = null;

    /**
     * @var string|null
     */
    protected $contentObjType = null;

    /**
    * a form action parameter. if set a form is generated
    */
    public $form_action;

    /**
     * @var bool
     */
    protected $booking_enabled = false;

    /**
     * @var bool
     */
    protected $availability_enabled = true;


    /**
    * Constructor
    *
    * @param	object	$a_gui_object	GUI instance of related object
    * 									(ilCouseGUI, ilTestGUI, ...)
    */
    public function __construct($a_gui_object)
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
        $this->sec_nr = 0;
        $this->private_notes_enabled = false;
        $this->news_enabled = false;
        $this->feedback_enabled = false;
        $this->learning_progress_enabled = false;
        $this->form_action = "";
        $this->top_formbuttons = array();
        $this->hiddenelements = array();
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        $rbacsystem = $this->rbacsystem;
        $tpl = $this->tpl;
        $ilAccess = $this->access;
        
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
                include_once("./Services/User/classes/class.ilPublicUserProfileGUI.php");
                $user_profile = new ilPublicUserProfileGUI($_GET["user_id"]);
                $user_profile->setBackUrl($this->ctrl->getLinkTarget($this, "showSummary"));
                $html = $this->ctrl->forwardCommand($user_profile);
                $tpl->setContent($html);
                break;
            
            case "ilcommonactiondispatchergui":
                include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;
                
            default:
                return $this->$cmd();
                break;
        }
        return true;
    }

    /**
     * Set table class
     *
     * @param	string	table class
     */
    public function setTableClass($a_val)
    {
        $this->table_class = $a_val;
    }
    
    /**
     * Get table class
     *
     * @return	string	table class
     */
    public function getTableClass()
    {
        return $this->table_class;
    }
    
    /**
    * enable notes
    */
    public function enablePrivateNotes($a_enable = true)
    {
        $this->private_notes_enabled = $a_enable;
    }

    /**
    * enable learning progress
    */
    public function enableLearningProgress($a_enable = true)
    {
        $this->learning_progress_enabled = $a_enable;
    }

    /**
    * enable availability
    */
    public function enableAvailability($a_enable = true)
    {
        $this->availability_enabled = $a_enable;
    }

    /**
     * booking info
     * @param bool $a_enable
     */
    public function enableBookingInfo($a_enable = true)
    {
        $this->booking_enabled = $a_enable;
    }


    /**
    * enable feedback
    */
    public function enableFeedback($a_enable = true)
    {
        $this->feedback_enabled = $a_enable;
    }

    /**
    * enable news
    */
    public function enableNews($a_enable = true)
    {
        $this->news_enabled = $a_enable;
    }

    /**
    * enable news editing
    */
    public function enableNewsEditing($a_enable = true)
    {
        $this->news_editing = $a_enable;
    }

    /**
    * This function is supposed to be used for block type specific
    * properties, that should be passed to ilBlockGUI->setProperty
    *
    * @param	string	$a_property		property name
    * @param	string	$a_value		property value
    */
    public function setBlockProperty($a_block_type, $a_property, $a_value)
    {
        $this->block_property[$a_block_type][$a_property] = $a_value;
    }
    
    public function getAllBlockProperties()
    {
        return $this->block_property;
    }

    /**
    * add a new section
    */
    public function addSection($a_title)
    {
        $this->sec_nr++;
        $this->section[$this->sec_nr]["title"] = $a_title;
        $this->section[$this->sec_nr]["hidden"] = (bool) $this->hidden;
    }

    /**
    * set a form action
    */
    public function setFormAction($a_form_action)
    {
        $this->form_action = $a_form_action;
    }

    /**
    * remove form action
    */
    public function removeFormAction()
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
    public function addProperty($a_name, $a_value, $a_link = "")
    {
        $this->section[$this->sec_nr]["properties"][] =
            array("name" => $a_name, "value" => $a_value,
                "link" => $a_link);
    }

    /**
    * add a property to current section
    */
    public function addPropertyCheckbox($a_name, $a_checkbox_name, $a_checkbox_value, $a_checkbox_label = "", $a_checkbox_checked = false)
    {
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
    * add a property to current section
    */
    public function addPropertyTextinput($a_name, $a_input_name, $a_input_value = "", $a_input_size = "", $direct_button_command = "", $direct_button_label = "", $direct_button_primary = false)
    {
        $input = "<span class=\"form-inline\"><input class=\"form-control\" type=\"text\" name=\"$a_input_name\" id=\"$a_input_name\"";
        if (strlen($a_input_value)) {
            $input .= " value=\"" . ilUtil::prepareFormOutput($a_input_value) . "\"";
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
    * add a property to current section
    */
    public function addButton($a_title, $a_link, $a_frame = "", $a_position = "top", $a_primary = false)
    {
        if ($a_position == "top") {
            $this->top_buttons[] =
                array("title" => $a_title,"link" => $a_link,"target" => $a_frame,"primary" => $a_primary);
        }
    }

    /**
    * add a form button to the info screen
    * the form buttons are only valid if a form action is set
    */
    public function addFormButton($a_command, $a_title, $a_position = "top")
    {
        if ($a_position == "top") {
            array_push(
                $this->top_formbuttons,
                array("command" => $a_command, "title" => $a_title)
            );
        }
    }

    public function addHiddenElement($a_name, $a_value)
    {
        array_push($this->hiddenelements, array("name" => $a_name, "value" => $a_value));
    }

    /**
    * add standard meta data sections
    */
    public function addMetaDataSections($a_rep_obj_id, $a_obj_id, $a_type)
    {
        $lng = $this->lng;

        $lng->loadLanguageModule("meta");

        include_once("./Services/MetaData/classes/class.ilMD.php");
        $md = new ilMD($a_rep_obj_id, $a_obj_id, $a_type);

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
            $langs = array();
            foreach ($ids = $md_gen->getLanguageIds() as $id) {
                $md_lan = $md_gen->getLanguage($id);
                if ($md_lan->getLanguageCode() != "") {
                    $langs[] = $lng->txt("meta_l_" . $md_lan->getLanguageCode());
                }
            }
            $langs = implode(", ", $langs);

            // keywords
            $keywords = array();
            foreach ($ids = $md_gen->getKeywordIds() as $id) {
                $md_key = $md_gen->getKeyword($id);
                $keywords[] = $md_key->getKeyword();
            }
            $keywords = implode(", ", $keywords);
        }

        // authors
        if (is_object($lifecycle = $md->getLifecycle())) {
            $sep = $author = "";
            foreach (($ids = $lifecycle->getContributeIds()) as $con_id) {
                $md_con = $lifecycle->getContribute($con_id);
                if ($md_con->getRole() == "Author") {
                    foreach ($ent_ids = $md_con->getEntityIds() as $ent_id) {
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
            include_once('Services/MetaData/classes/class.ilMDUtils.php');
            $copyright = ilMDUtils::_parseCopyright($rights->getDescription());
        } else {
            $copyright = ilMDUtils::_getDefaultCopyright();
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
    * add standard object section
    */
    public function addObjectSections()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        $ilAccess = $this->access;
        $tree = $this->tree;

        // ressource bookings
        if ($this->booking_enabled) {
            $booking_adapter = new ilBookingInfoScreenAdapter($this);
            $booking_adapter->add();
        }

        $this->addSection($lng->txt("additional_info"));
        $a_obj = $this->gui_object->object;
                
        // links to the object
        if (is_object($a_obj)) {
            // permanent link
            $type = $a_obj->getType();
            $ref_id = $a_obj->getRefId();
            
            if ($ref_id) {
                include_once 'Services/WebServices/ECS/classes/class.ilECSServerSettings.php';
                if (ilECSServerSettings::getInstance()->activeServerExists()) {
                    $this->addProperty(
                        $lng->txt("object_id"),
                        $a_obj->getId()
                    );
                }

                include_once 'Services/PermanentLink/classes/class.ilPermanentLinkGUI.php';
                $pm = new ilPermanentLinkGUI($type, $ref_id);
                $pm->setIncludePermanentLinkText(false);
                $pm->setAlignCenter(false);
                $this->addProperty(
                    $lng->txt("perma_link"),
                    $pm->getHTML(),
                    ""
                );
            
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

        if ($ilAccess->checkAccess("write", "", $ref_id) ||
            $ilAccess->checkAccess("edit_permissions", "", $ref_id)) {

            // creation date
            $this->addProperty(
                $lng->txt("create_date"),
                ilDatePresentation::formatDate(new ilDateTime($a_obj->getCreateDate(), IL_CAL_DATETIME))
            );

            // owner
            if ($ilUser->getId() != ANONYMOUS_USER_ID and $a_obj->getOwner()) {
                include_once './Services/Object/classes/class.ilObjectFactory.php';
                include_once './Services/User/classes/class.ilObjUser.php';

                if (ilObjUser::userExists(array($a_obj->getOwner()))) {
                    $ownerObj = ilObjectFactory::getInstanceByObjId($a_obj->getOwner(), false);
                } else {
                    $ownerObj = ilObjectFactory::getInstanceByObjId(6, false);
                }

                if (!is_object($ownerObj) || $ownerObj->getType() != "usr") {        // root user deleted
                    $this->addProperty($lng->txt("owner"), $lng->txt("no_owner"));
                } elseif ($ownerObj->hasPublicProfile()) {
                    $ilCtrl->setParameterByClass("ilpublicuserprofilegui", "user_id", $ownerObj->getId());
                    $this->addProperty($lng->txt("owner"), $ownerObj->getPublicName(),
                        $ilCtrl->getLinkTargetByClass("ilpublicuserprofilegui", "getHTML"));
                } else {
                    $this->addProperty($lng->txt("owner"), $ownerObj->getPublicName());
                }
            }

            // change event
            require_once 'Services/Tracking/classes/class.ilChangeEvent.php';
            if (ilChangeEvent::_isActive()) {
                if ($ilUser->getId() != ANONYMOUS_USER_ID) {
                    $readEvents = ilChangeEvent::_lookupReadEvents($a_obj->getId());
                    $count_users = 0;
                    $count_members = 0;
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
                        $this->addProperty($this->lng->txt("accesscount_registered_users"), $count_users);
                    }
                }
            }
            // END ChangeEvent: Display change event info
        }

        // WebDAV: Display locking information
        require_once('Services/WebDAV/classes/class.ilDAVActivationChecker.php');
        if (ilDAVActivationChecker::_isActive()) {
            if ($ilUser->getId() != ANONYMOUS_USER_ID) {
                require_once 'Services/WebDAV/classes/lock/class.ilWebDAVLockBackend.php';
                $webdav_lock_backend = new ilWebDAVLockBackend();

                // Show lock info
                if ($ilUser->getId() != ANONYMOUS_USER_ID) {
                    if ($lock = $webdav_lock_backend->getLocksOnObjectId($this->gui_object->object->getId())) {
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
    * show summary page
    */
    public function showSummary()
    {
        $tpl = $this->tpl;
        $ilAccess = $this->access;

        $tpl->setContent($this->getCenterColumnHTML());
        $tpl->setRightContent($this->getRightColumnHTML());
    }


    /**
    * Display center column
    */
    public function getCenterColumnHTML()
    {
        $ilCtrl = $this->ctrl;
        
        include_once("Services/Block/classes/class.ilColumnGUI.php");
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
    * Display right column
    */
    public function getRightColumnHTML()
    {
        $ilUser = $this->user;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        include_once("Services/Block/classes/class.ilColumnGUI.php");
        $column_gui = new ilColumnGUI("info", IL_COL_RIGHT);
        $this->setColumnSettings($column_gui);

        if ($ilCtrl->getNextClass() == "ilcolumngui" &&
            $column_gui->getCmdSide() == IL_COL_RIGHT &&
            $column_gui->getScreenMode() == IL_SCREEN_SIDE) {
            $html = $ilCtrl->forwardCommand($column_gui);
        } else {
            if (!$ilCtrl->isAsynch()) {
                if ($this->news_enabled) {
                    $html = $ilCtrl->getHTML($column_gui);
                }
            }
        }

        return $html;
    }

    /**
    * Set column settings.
    */
    public function setColumnSettings($column_gui)
    {
        $lng = $this->lng;
        $ilAccess = $this->access;

        $column_gui->setEnableEdit($this->news_editing);
        $column_gui->setRepositoryMode(true);
        $column_gui->setAllBlockProperties($this->getAllBlockProperties());
    }
    
    public function setOpenFormTag($a_val)
    {
        $this->open_form_tag = $a_val;
    }

    public function setCloseFormTag($a_val)
    {
        $this->close_form_tag = $a_val;
    }

    /**
    * get html
    */
    public function getHTML()
    {
        $lng = $this->lng;
        $ilSetting = $this->settings;
        $tree = $this->tree;
        $ilAccess = $this->access;
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        
        $tpl = new ilTemplate("tpl.infoscreen.html", true, true, "Services/InfoScreen");

        // other class handles form action (@todo: this is not implemented/tested)
        if ($this->form_action == "") {
            $this->setFormAction($ilCtrl->getFormAction($this));
        }
        
        require_once 'Services/jQuery/classes/class.iljQueryUtil.php';
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
            $tpl->addBlockfile("TOP_BUTTONS", "top_buttons", "tpl.buttons.html");

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
            $tpl->addBlockfile("TOP_FORMBUTTONS", "top_submitbuttons", "tpl.submitbuttons.html", "Services/InfoScreen");

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
        if ($this->learning_progress_enabled and $html = $this->showLearningProgress($tpl)) {
            $tpl->setCurrentBlock("learning_progress");
            $tpl->setVariable("LP_TABLE", $html);
            $tpl->parseCurrentBlock();
        }

        // notes section
        if ($this->private_notes_enabled && !$ilSetting->get('disable_notes')) {
            $html = $this->showNotesSection();
            $tpl->setCurrentBlock("notes");
            $tpl->setVariable("NOTES", $html);
            $tpl->parseCurrentBlock();
        }

        // tagging
        if (is_object($this->gui_object->object)) {
            $tags_set = new ilSetting("tags");
            if ($tags_set->get("enable") && $ilUser->getId() != ANONYMOUS_USER_ID) {
                $this->addTagging();
            }
        }

        if (is_object($this->gui_object->object)) {
            $this->addObjectSections();
        }

        // render all sections
        for ($i = 1; $i <= $this->sec_nr; $i++) {
            if (is_array($this->section[$i]["properties"])) {
                // section properties
                foreach ($this->section[$i]["properties"] as $property) {
                    if ($property["name"] != "") {
                        if ($property["link"] == "") {
                            $tpl->setCurrentBlock("pv");
                            $tpl->setVariable("TXT_PROPERTY_VALUE", $property["value"]);
                            $tpl->parseCurrentBlock();
                        } else {
                            $tpl->setCurrentBlock("lpv");
                            $tpl->setVariable("TXT_PROPERTY_LVALUE", $property["value"]);
                            $tpl->setVariable("LINK_PROPERTY_VALUE", $property["link"]);
                            $tpl->parseCurrentBlock();
                        }
                        $tpl->setCurrentBlock("property_row");
                        $tpl->setVariable("TXT_PROPERTY", $property["name"]);
                        $tpl->parseCurrentBlock();
                    } else {
                        $tpl->setCurrentBlock("property_full_row");
                        $tpl->setVariable("TXT_PROPERTY_FULL_VALUE", $property["value"]);
                        $tpl->parseCurrentBlock();
                    }
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

    /**
     * @return int|null
     */
    public function getContextRefId() : int
    {
        if ($this->contextRefId !== null) {
            return $this->contextRefId;
        }

        return $this->gui_object->object->getRefId();
    }

    /**
     * @param int|null $contextRefId
     */
    public function setContextRefId(int $contextRefId)
    {
        $this->contextRefId = $contextRefId;
    }

    /**
     * @return int|null
     */
    public function getContextObjId() : int
    {
        if ($this->contextObjId !== null) {
            return $this->contextObjId;
        }

        return $this->gui_object->object->getId();
    }

    /**
     * @param int|null $contextObjId
     */
    public function setContextObjId(int $contextObjId)
    {
        $this->contextObjId = $contextObjId;
    }

    /**
     * @return null|string
     */
    public function getContentObjType() : string
    {
        if ($this->contentObjType !== null) {
            return $this->contentObjType;
        }

        return $this->gui_object->object->getType();
    }

    /**
     * @param null|string $contentObjType
     */
    public function setContentObjType(string $contentObjType)
    {
        $this->contentObjType = $contentObjType;
    }

    public function showLearningProgress($a_tpl)
    {
        $ilUser = $this->user;
        $rbacsystem = $this->rbacsystem;

        if (!$rbacsystem->checkAccess('read', $this->getContextRefId())) {
            return false;
        }
        if ($ilUser->getId() == ANONYMOUS_USER_ID) {
            return false;
        }

        include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
        if (!ilObjUserTracking::_enabledLearningProgress()) {
            return false;
        }
            
        include_once './Services/Object/classes/class.ilObjectLP.php';
        $olp = ilObjectLP::getInstance($this->getContextObjId());
        if ($olp->getCurrentMode() != ilLPObjSettings::LP_MODE_MANUAL) {
            return false;
        }

        include_once 'Services/Tracking/classes/class.ilLPMarks.php';

        $this->lng->loadLanguageModule('trac');

        // section header
        //		$a_tpl->setCurrentBlock("header_row");
        $a_tpl->setVariable(
            "TXT_SECTION",
            $this->lng->txt('learning_progress')
        );
        $a_tpl->parseCurrentBlock();
        // $a_tpl->touchBlock("row");

        // status
        $i_tpl = new ilTemplate("tpl.lp_edit_manual_info_page.html", true, true, "Services/Tracking");
        $i_tpl->setVariable("INFO_EDITED", $this->lng->txt("trac_info_edited"));
        $i_tpl->setVariable("SELECT_STATUS", ilUtil::formSelect(
            (int) ilLPMarks::_hasCompleted(
                $ilUser->getId(),
                $this->getContextObjId()
            ),
            'lp_edit',
            array(0 => $this->lng->txt('trac_not_completed'),
                      1 => $this->lng->txt('trac_completed')),
            false,
            true
        ));
        $i_tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
        $a_tpl->setCurrentBlock("pv");
        $a_tpl->setVariable("TXT_PROPERTY_VALUE", $i_tpl->get());
        $a_tpl->parseCurrentBlock();
        $a_tpl->setCurrentBlock("property_row");
        $a_tpl->setVariable("TXT_PROPERTY", $this->lng->txt('trac_status'));
        $a_tpl->parseCurrentBlock();
        // $a_tpl->touchBlock("row");


        // More infos for lm's
        if ($this->getContentObjType() == 'lm' ||
            $this->getContentObjType() == 'htlm') {
            $a_tpl->setCurrentBlock("pv");

            include_once 'Services/Tracking/classes/class.ilLearningProgress.php';
            $progress = ilLearningProgress::_getProgress($ilUser->getId(), $this->getContextObjId());
            if ($progress['access_time']) {
                $a_tpl->setVariable(
                    "TXT_PROPERTY_VALUE",
                    ilDatePresentation::formatDate(new ilDateTime($progress['access_time'], IL_CAL_UNIX))
                );
            } else {
                $a_tpl->setVariable(
                    "TXT_PROPERTY_VALUE",
                    $this->lng->txt('trac_not_accessed')
                );
            }

            $a_tpl->parseCurrentBlock();
            $a_tpl->setCurrentBlock("property_row");
            $a_tpl->setVariable("TXT_PROPERTY", $this->lng->txt('trac_last_access'));
            $a_tpl->parseCurrentBlock();
            // $a_tpl->touchBlock("row");

            // tags of all users
            $a_tpl->setCurrentBlock("pv");
            $a_tpl->setVariable(
                "TXT_PROPERTY_VALUE",
                (int) $progress['visits']
            );
            $a_tpl->parseCurrentBlock();
            $a_tpl->setCurrentBlock("property_row");
            $a_tpl->setVariable("TXT_PROPERTY", $this->lng->txt('trac_visits'));
            $a_tpl->parseCurrentBlock();
            // $a_tpl->touchBlock("row");


            if ($this->getContentObjType() == 'lm') {
                // tags of all users
                $a_tpl->setCurrentBlock("pv");
                $a_tpl->setVariable(
                    "TXT_PROPERTY_VALUE",
                    ilDatePresentation::secondsToString($progress['spent_seconds'])
                );
                $a_tpl->parseCurrentBlock();
                $a_tpl->setCurrentBlock("property_row");
                $a_tpl->setVariable("TXT_PROPERTY", $this->lng->txt('trac_spent_time'));
                $a_tpl->parseCurrentBlock();
                // $a_tpl->touchBlock("row");
            }
        }
        
        // #10493
        $a_tpl->touchBlock("row");
    }

    public function saveProgress($redirect = true)
    {
        $ilUser = $this->user;

        include_once 'Services/Tracking/classes/class.ilLPMarks.php';

        $lp_marks = new ilLPMarks($this->getContextObjId(), $ilUser->getId());
        $lp_marks->setCompleted((bool) $_POST['lp_edit']);
        $lp_marks->update();

        require_once 'Services/Tracking/classes/class.ilLPStatusWrapper.php';
        ilLPStatusWrapper::_updateStatus($this->getContextObjId(), $ilUser->getId());

        $this->lng->loadLanguageModule('trac');
        ilUtil::sendSuccess($this->lng->txt('trac_updated_status'), true);

        if ($redirect) {
            $this->ctrl->redirect($this, ""); // #14993
        }
    }


    /**
    * show notes section
    */
    public function showNotesSection()
    {
        $ilAccess = $this->access;
        $ilSetting = $this->settings;
        
        $next_class = $this->ctrl->getNextClass($this);
        include_once("Services/Notes/classes/class.ilNoteGUI.php");
        $notes_gui = new ilNoteGUI(
            $this->gui_object->object->getId(),
            0,
            $this->gui_object->object->getType()
        );
        
        // global switch
        if ($ilSetting->get("disable_comments")) {
            $notes_gui->enablePublicNotes(false);
        } else {
            $ref_id = $this->gui_object->object->getRefId();
            $has_write = $ilAccess->checkAccess("write", "", $ref_id);
            
            if ($has_write && $ilSetting->get("comments_del_tutor", 1)) {
                $notes_gui->enablePublicNotesDeletion(true);
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
            $html = $notes_gui->getNotesHTML();
        }

        return $html;
    }

    /**
     * show LDAP role group mapping info
     *
     * @access public
     * @param string section name. Leave empty to place this info string inside a section
     *
     */
    public function showLDAPRoleGroupMappingInfo($a_section = '')
    {
        if (strlen($a_section)) {
            $this->addSection($a_section);
        }
        include_once('Services/LDAP/classes/class.ilLDAPRoleGroupMapping.php');
        $ldap_mapping = ilLDAPRoleGroupMapping::_getInstance();
        if ($infos = $ldap_mapping->getInfoStrings($this->gui_object->object->getId())) {
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
        return true;
    }

    public function setTabs()
    {
        $tpl = $this->tpl;

        $this->getTabs($this->tabs_gui);
    }

    /**
    * get tabs
    */
    public function getTabs(&$tabs_gui)
    {
        $rbacsystem = $this->rbacsystem;
        $ilUser = $this->user;
        $ilAccess = $this->access;

        $next_class = $this->ctrl->getNextClass($this);
        $force_active = ($next_class == "ilnotegui")
            ? true
            : false;

        $tabs_gui->addSubTabTarget(
            'summary',
            $this->ctrl->getLinkTarget($this, "showSummary"),
            array("showSummary", ""),
            get_class($this),
            "",
            $force_active
        );
    }

    
    /**
    * Add tagging
    */
    public function addTagging()
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        
        $lng->loadLanguageModule("tagging");
        $tags_set = new ilSetting("tags");

        include_once("Services/Tagging/classes/class.ilTaggingGUI.php");
        $tagging_gui = new ilTaggingGUI();
        $tagging_gui->setObject(
            $this->gui_object->object->getId(),
            $this->gui_object->object->getType()
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
    
    public function saveTags()
    {
        include_once("Services/Tagging/classes/class.ilTaggingGUI.php");
        $tagging_gui = new ilTaggingGUI();
        $tagging_gui->setObject(
            $this->gui_object->object->getId(),
            $this->gui_object->object->getType()
        );
        $tagging_gui->saveInput();
        
        ilUtil::sendSuccess($this->lng->txt('msg_obj_modified'), true);
        $this->ctrl->redirect($this, ""); // #14993
        
        // return $this->showSummary();
    }

    public function hideFurtherSections($a_add_toggle = true)
    {
        $this->hidden = true;
        $this->show_hidden_toggle = (bool) $a_add_toggle;
    }

    public function getHiddenToggleButton()
    {
        $lng = $this->lng;
        
        return "<a onClick=\"toggleSections(this, '" . $lng->txt("show_hidden_sections") . "', '" . $lng->txt("hide_visible_sections") . "'); return false;\" href=\"#\">" . $lng->txt("show_hidden_sections") . "</a>";
    }


    /**
     * Add preconditions
     */
    protected function addAvailability()
    {
        if (!is_object($this->gui_object) || !is_object($this->gui_object->object)) {
            return;
        }

        $obj = $this->gui_object->object;
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
    protected function addPreconditions()
    {
        if (!is_object($this->gui_object) || !is_object($this->gui_object->object)) {
            return;
        }

        $obj = $this->gui_object->object;
        if ($obj->getRefId() <= 0) {
            return;
        }

        $conditions = ilConditionHandler::_getEffectiveConditionsOfTarget($obj->getRefId(), $obj->getId());

        if (sizeof($conditions)) {
            for ($i = 0; $i < count($conditions); $i++) {
                $conditions[$i]['title'] = ilObject::_lookupTitle($conditions[$i]['trigger_obj_id']);
            }
            $conditions = ilUtil::sortArray($conditions, 'title', 'DESC');

            // Show obligatory and optional preconditions seperated
            $this->addPreconditionSection($obj, $conditions, true);
            $this->addPreconditionSection($obj, $conditions, false);
        }
    }

    protected function addPreconditionSection($obj, $conditions, $obligatory = true)
    {
        $lng = $this->lng;
        $tree = $this->tree;

        $num_required = ilConditionHandler::calculateEffectiveRequiredTriggers($obj->getRefId(), $obj->getId());
        $num_optional_required =
            $num_required - count($conditions) + count(ilConditionHandler::getEffectiveOptionalConditionsOfTarget($obj->getRefId(), $obj->getId()));

        // Check if all conditions are fullfilled
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
                    return true;
                }
            }
        }

        $properties = [];

        foreach ($conditions as $condition) {
            if (!in_array($condition['id'], $visible_conditions)) {
                continue;
            }

            $missing_cond_exist = true;

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
     * Add access period property
     */
    public function addAccessPeriodProperty() : void
    {
        $a_obj = $this->gui_object->object;

        $this->lng->loadLanguageModule("rep");
        $this->lng->loadLanguageModule("crs");

        // links to the object
        if (is_object($a_obj)) {
            $act = new ilObjectActivation();
            $act->read($a_obj->getRefId());
            if ($act->getTimingType() == ilObjectActivation::TIMINGS_ACTIVATION)
            {
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
