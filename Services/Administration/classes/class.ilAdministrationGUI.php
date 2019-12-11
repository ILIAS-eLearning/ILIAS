<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTableGUI.php");

/**
* Class ilAdministratioGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilAdministrationGUI: ilObjGroupGUI, ilObjFolderGUI, ilObjFileGUI, ilObjCourseGUI, ilCourseObjectivesGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjSAHSLearningModuleGUI, ilObjChatroomGUI, ilObjForumGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjLearningModuleGUI, ilObjGlossaryGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjQuestionPoolGUI, ilObjSurveyQuestionPoolGUI, ilObjTestGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjSurveyGUI, ilObjExerciseGUI, ilObjMediaPoolGUI, ilObjFileBasedLMGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjCategoryGUI, ilObjUserGUI, ilObjRoleGUI, ilObjUserFolderGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjLinkResourceGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjRoleTemplateGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjRootFolderGUI, ilObjSessionGUI, ilObjPortfolioTemplateGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjSystemFolderGUI, ilObjRoleFolderGUI, ilObjAuthSettingsGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjChatServerGUI, ilObjLanguageFolderGUI, ilObjMailGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjObjectFolderGUI, ilObjRecoveryFolderGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjSearchSettingsGUI, ilObjStyleSettingsGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjAssessmentFolderGUI, ilObjExternalToolsSettingsGUI, ilObjUserTrackingGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjAdvancedEditingGUI, ilObjPrivacySecurityGUI, ilObjNewsSettingsGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjPersonalDesktopSettingsGUI, ilObjMediaCastGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjLanguageExtGUI, ilObjMDSettingsGUI, ilObjComponentSettingsGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjCalendarSettingsGUI, ilObjSurveyAdministrationGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjCategoryReferenceGUI, ilObjCourseReferenceGUI, ilObjRemoteCourseGUI, ilObjGroupReferenceGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjForumAdministrationGUI, ilObjBlogGUI, ilObjPollGUI, ilObjDataCollectionGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjRemoteCategoryGUI, ilObjRemoteWikiGUI, ilObjRemoteLearningModuleGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjRemoteGlossaryGUI, ilObjRemoteFileGUI, ilObjRemoteGroupGUI, ilObjECSSettingsGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjCloudGUI, ilObjRepositorySettingsGUI, ilObjWebResourceAdministrationGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjCourseAdministrationGUI, ilObjGroupAdministrationGUI, ilObjExerciseAdministrationGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjTaxonomyAdministrationGUI, ilObjLoggingSettingsGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjBibliographicAdminGUI, ilObjBibliographicGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjStudyProgrammeAdminGUI, ilObjStudyProgrammeGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjBadgeAdministrationGUI, ilMemberExportSettingsGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjFileAccessSettingsGUI, ilPermissionGUI, ilObjRemoteTestGUI
*/
class ilAdministrationGUI
{
    /**
     * @var ilObjectDefinition
     */
    protected $objDefinition;

    /**
     * @var ilMainMenuGUI
     */
    protected $main_menu;

    /**
     * @var ilHelpGUI
     */
    protected $help;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
     * @var ilDB
     */
    protected $db;

    public $lng;
    public $tpl;
    public $tree;
    public $rbacsystem;
    public $cur_ref_id;
    public $cmd;
    public $mode;
    public $ctrl;

    /**
    * Constructor
    * @access	public
    */
    public function __construct()
    {
        global $DIC;

        $this->main_menu = $DIC["ilMainMenu"];
        $this->help = $DIC["ilHelp"];
        $this->error = $DIC["ilErr"];
        $this->db = $DIC->database();
        $lng = $DIC->language();
        $tpl = $DIC["tpl"];
        $tree = $DIC->repositoryTree();
        $rbacsystem = $DIC->rbac()->system();
        $objDefinition = $DIC["objDefinition"];
        $ilCtrl = $DIC->ctrl();
        $ilMainMenu = $DIC["ilMainMenu"];

        $this->lng = $lng;
        $this->lng->loadLanguageModule('administration');
        $this->tpl = $tpl;
        $this->tree = $tree;
        $this->rbacsystem = $rbacsystem;
        $this->objDefinition = $objDefinition;
        $this->ctrl = $ilCtrl;

        $ilMainMenu->setActive("administration");
        
        $this->creation_mode = false;

        $this->ctrl->saveParameter($this, array("ref_id", "admin_mode"));
        
        if ($_GET["admin_mode"] != "repository") {
            $_GET["admin_mode"] = "settings";
        }
        
        if (!ilUtil::isAPICall()) {
            $this->ctrl->setReturn($this, "");
        }

        // determine current ref id and mode
        if (!empty($_GET["ref_id"]) && $tree->isInTree($_GET["ref_id"])) {
            $this->cur_ref_id = $_GET["ref_id"];
        } else {
            //$this->cur_ref_id = $this->tree->getRootId();
            $_POST = array();
            if ($_GET["cmd"] != "getDropDown") {
                $_GET["cmd"] = "";
            }
        }
    }

    
    /**
    * execute command
    */
    public function executeCommand()
    {
        $rbacsystem = $this->rbacsystem;
        $objDefinition = $this->objDefinition;
        $ilHelp = $this->help;
        $ilErr = $this->error;
        $ilDB = $this->db;
        
        // permission checks
        include_once './Services/MainMenu/classes/class.ilMainMenuGUI.php';
        if (!$rbacsystem->checkAccess("visible", SYSTEM_FOLDER_ID) &&
                !$rbacsystem->checkAccess("read", SYSTEM_FOLDER_ID)) {
            $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->WARNING);
        }
        
        // check creation mode
        // determined by "new_type" parameter
        $new_type = $_POST["new_type"]
            ? $_POST["new_type"]
            : $_GET["new_type"];
        if ($new_type != "") {
            $this->creation_mode = true;
        }
    
        // determine next class
        if ($this->creation_mode) {
            $obj_type = $new_type;
            $class_name = $this->objDefinition->getClassName($obj_type);
            $next_class = strtolower("ilObj" . $class_name . "GUI");
            $this->ctrl->setCmdClass($next_class);
        }
        // set next_class directly for page translations
        // (no cmdNode is given in translation link)
        elseif ($this->ctrl->getCmdClass() == "ilobjlanguageextgui") {
            $next_class = "ilobjlanguageextgui";
        } else {
            $next_class = $this->ctrl->getNextClass($this);
        }

        if ((
            $next_class == "iladministrationgui" || $next_class == ""
        ) && ($this->ctrl->getCmd() == "return")) {
            // get GUI of current object
            $obj_type = ilObject::_lookupType($this->cur_ref_id, true);
            $class_name = $this->objDefinition->getClassName($obj_type);
            $next_class = strtolower("ilObj" . $class_name . "GUI");
            $this->ctrl->setCmdClass($next_class);
            $this->ctrl->setCmd("view");
        }

        $cmd = $this->ctrl->getCmd("forward");

        //echo "<br>cmd:$cmd:nextclass:$next_class:-".$_GET["cmdClass"]."-".$_GET["cmd"]."-";
        switch ($next_class) {
            default:
            
                // forward all other classes to gui commands
                if ($next_class != "" && $next_class != "iladministrationgui") {
                    // check db update
                    include_once("./Services/Database/classes/class.ilDBUpdate.php");
                    $dbupdate = new ilDBUpdate($ilDB);
                    if (!$dbupdate->getDBVersionStatus()) {
                        ilUtil::sendFailure($this->lng->txt("db_need_update"));
                    } elseif ($dbupdate->hotfixAvailable()) {
                        ilUtil::sendFailure($this->lng->txt("db_need_hotfix"));
                    }
                    
                    $class_path = $this->ctrl->lookupClassPath($next_class);
                    // get gui class instance
                    include_once($class_path);
                    $class_name = $this->ctrl->getClassForClasspath($class_path);
                    if (($next_class == "ilobjrolegui" || $next_class == "ilobjusergui"
                        || $next_class == "ilobjroletemplategui")) {
                        if ($_GET["obj_id"] != "") {
                            $this->gui_obj = new $class_name("", $_GET["obj_id"], false, false);
                            $this->gui_obj->setCreationMode(false);
                        } else {
                            $this->gui_obj = new $class_name("", $this->cur_ref_id, true, false);
                            $this->gui_obj->setCreationMode(true);
                        }
                    } else {
                        if ($objDefinition->isPlugin(ilObject::_lookupType($this->cur_ref_id, true))) {
                            $this->gui_obj = new $class_name($this->cur_ref_id);
                        } else {
                            if (!$this->creation_mode) {
                                if (is_subclass_of($class_name, "ilObject2GUI")) {
                                    $this->gui_obj = new $class_name($this->cur_ref_id, ilObject2GUI::REPOSITORY_NODE_ID);
                                } else {
                                    $this->gui_obj = new $class_name("", $this->cur_ref_id, true, false);
                                }
                            } else {
                                if (is_subclass_of($class_name, "ilObject2GUI")) {
                                    $this->gui_obj = new $class_name(null, ilObject2GUI::REPOSITORY_NODE_ID, $this->cur_ref_id);
                                } else {
                                    $this->gui_obj = new $class_name("", 0, true, false);
                                }
                            }
                        }
                        $this->gui_obj->setCreationMode($this->creation_mode);
                    }
                    $tabs_out = ($new_type == "")
                        ? true
                        : false;

                    // set standard screen id
                    //					if (strtolower($next_class) == strtolower($this->ctrl->getCmdClass()) ||
                    //						"ilpermissiongui" == strtolower($this->ctrl->getCmdClass()))
                    //					{
                    $ilHelp->setScreenIdComponent(ilObject::_lookupType($this->cur_ref_id, true));
                    //					}
                    $this->showTree();
                        
                    $this->ctrl->setReturn($this, "return");
                    $ret = $this->ctrl->forwardCommand($this->gui_obj);
                    $html = $this->gui_obj->getHTML();

                    if ($html != "") {
                        $this->tpl->setVariable("OBJECTS", $html);
                    }
                    $this->tpl->show();
                } else {	//
                    $cmd = $this->ctrl->getCmd("forward");
                    $this->$cmd();
                }
                break;
        }
    }

    /**
     * Forward to class/command
     */
    public function forward()
    {
        $ilErr = $this->error;
        
        if ($_GET["admin_mode"] != "repository") {	// settings
            if ($_GET["ref_id"] == USER_FOLDER_ID) {
                $this->ctrl->setParameter($this, "ref_id", USER_FOLDER_ID);
                $this->ctrl->setParameterByClass("iladministrationgui", "admin_mode", "settings");
                if (((int) $_GET["jmpToUser"]) > 0 && ilObject::_lookupType((int) $_GET["jmpToUser"]) == "usr") {
                    $this->ctrl->setParameterByClass(
                        "ilobjuserfoldergui",
                        "jmpToUser",
                        (int) $_GET["jmpToUser"]
                    );
                    $this->ctrl->redirectByClass("ilobjuserfoldergui", "jumpToUser");
                } else {
                    $this->ctrl->redirectByClass("ilobjuserfoldergui", "view");
                }
            } else {
                $this->ctrl->setParameter($this, "ref_id", SYSTEM_FOLDER_ID);
                $this->ctrl->setParameterByClass("iladministrationgui", "admin_mode", "settings");

                if ($_GET['fr']) {
                    // Security check: We do only allow relative urls
                    $url_parts = parse_url(base64_decode(rawurldecode($_GET['fr'])));
                    if ($url_parts['http'] || $url_parts['host']) {
                        $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->MESSAGE);
                    }
                    
                    $fs_gui->setMainFrameSource(
                        base64_decode(rawurldecode($_GET['fr']))
                    );
                    ilUtil::redirect(ILIAS_HTTP_PATH . '/' . base64_decode(rawurldecode($_GET['fr'])));
                } else {
                    $fs_gui->setMainFrameSource(
                        $this->ctrl->getLinkTargetByClass("ilobjsystemfoldergui", "view")
                    );
                    $this->ctrl->redirectByClass("ilobjsystemfoldergui", "view");
                }
            }
        } else {
            $this->ctrl->setParameter($this, "ref_id", ROOT_FOLDER_ID);
            $this->ctrl->setParameterByClass("iladministrationgui", "admin_mode", "repository");
            $this->ctrl->redirectByClass("ilobjrootfoldergui", "view");
        }
    }

    /**
    * display tree view
    */
    public function showTree()
    {
        $tpl = $this->tpl;
        $tree = $this->tree;
        $lng = $this->lng;
        
        if ($_GET["admin_mode"] != "repository") {
            return;
        }
        
        include_once("./Services/Administration/classes/class.ilAdministrationExplorerGUI.php");
        $exp = new ilAdministrationExplorerGUI($this, "showTree");
        if (!$exp->handleCommand()) {
            $tpl->setLeftNavContent($exp->getHTML());
        }
    }
    
    /**
     * Special jump to plugin slot after ilCtrl has been reloaded
     */
    public function jumpToPluginSlot()
    {
        $ilCtrl = $this->ctrl;
        
        $ilCtrl->setParameterByClass("ilobjcomponentsettingsgui", "ctype", $_GET["ctype"]);
        $ilCtrl->setParameterByClass("ilobjcomponentsettingsgui", "cname", $_GET["cname"]);
        $ilCtrl->setParameterByClass("ilobjcomponentsettingsgui", "slot_id", $_GET["slot_id"]);
        
        if ($_GET["plugin_id"]) {
            $ilCtrl->setParameter($this, "plugin_id", $_GET["plugin_id"]);
            $ilCtrl->redirectByClass("ilobjcomponentsettingsgui", "showPlugin");
        } else {
            $ilCtrl->redirectByClass("ilobjcomponentsettingsgui", "listPlugins");
        }
    }

    /**
     * Get drop down
     */
    public function getDropDown()
    {
        $tree = $this->tree;
        $rbacsystem = $this->rbacsystem;
        $lng = $this->lng;

        $objects = $tree->getChilds(SYSTEM_FOLDER_ID);

        foreach ($objects as $object) {
            $new_objects[$object["title"] . ":" . $object["child"]]
                = $object;
            // have to set it manually as translation type of main node cannot be "sys" as this type is a orgu itself.
            if ($object["type"] == "orgu") {
                $new_objects[$object["title"] . ":" . $object["child"]]["title"] = $lng->txt("objs_orgu");
            }
        }

        // add entry for switching to repository admin
        // note: please see showChilds methods which prevents infinite look
        $new_objects[$lng->txt("repository_admin") . ":" . ROOT_FOLDER_ID] =
            array(
            "tree" => 1,
            "child" => ROOT_FOLDER_ID,
            "ref_id" => ROOT_FOLDER_ID,
            "depth" => 3,
            "type" => "root",
            "title" => $lng->txt("repository_admin"),
            "description" => $lng->txt("repository_admin_desc"),
            "desc" => $lng->txt("repository_admin_desc"),
            );

        $new_objects[$lng->txt("general_settings") . ":" . SYSTEM_FOLDER_ID] =
            array(
            "tree" => 1,
            "child" => SYSTEM_FOLDER_ID,
            "ref_id" => SYSTEM_FOLDER_ID,
            "depth" => 2,
            "type" => "adm",
            "title" => $lng->txt("general_settings"),
            );
        ksort($new_objects);

        // determine items to show
        $items = array();
        foreach ($new_objects as $c) {
            // check visibility
            if ($tree->getParentId($c["ref_id"]) == ROOT_FOLDER_ID && $c["type"] != "adm" &&
                $_GET["admin_mode"] != "repository") {
                continue;
            }
            // these objects may exist due to test cases that didnt clear
            // data properly
            if ($c["type"] == "" || $c["type"] == "objf" ||
                $c["type"] == "xxx") {
                continue;
            }
            $accessible = $rbacsystem->checkAccess('visible,read', $c["ref_id"]);
            if (!$accessible) {
                continue;
            }
            if ($c["ref_id"] == ROOT_FOLDER_ID &&
                !$rbacsystem->checkAccess('write', $c["ref_id"])) {
                continue;
            }
            if ($c["type"] == "rolf" && $c["ref_id"] != ROLE_FOLDER_ID) {
                continue;
            }
            $items[] = $c;
        }

        $titems = array();
        foreach ($items as $i) {
            $titems[$i["type"]] = $i;
        }
        
        // admin menu layout
        $layout = array(
            1 => array(
                "basic" =>
                    array("adm", "mme", "stys", "adve", "lngf", "hlps", "accs", "cmps", "extt", "wfe"),
                "user_administration" =>
                    array("usrf", 'tos', "rolf", "orgu", "auth", "ps"),
                "learning_outcomes" =>
                    array("skmg", "bdga", "cert", "trac")
                ),
            2 => array(
                "user_services" =>
                    array("pdts", "prfa", "nwss", "awra", "cadm", "cals", "mail"),
                "content_services" =>
                    array("seas", "mds", "tags", "taxs", 'ecss', "ltis", "otpl", "pdfg"),
                "maintenance" =>
                    array('logs', 'sysc', "recf", "root")
                ),
            3 => array(
                "container" =>
                    array("reps", "crss", "grps", "prgs"),
                "content_objects" =>
                    array("bibs", "blga", "chta", "excs", "facs", "frma",
                        "lrss", "mcts", "mobs", "svyf", "assf", "wbrs", "wiks")
                )
            );
        
        // now get all items and groups that are accessible
        $groups = array();
        for ($i = 1; $i <= 3; $i++) {
            $groups[$i] = array();
            foreach ($layout[$i] as $group => $entries) {
                $groups[$i][$group] = array();
                $entries_since_last_sep = false;
                foreach ($entries as $e) {
                    if ($e == "---" || $titems[$e]["type"] != "") {
                        if ($e == "---" && $entries_since_last_sep) {
                            $groups[$i][$group][] = $e;
                            $entries_since_last_sep = false;
                        } elseif ($e != "---") {
                            $groups[$i][$group][] = $e;
                            $entries_since_last_sep = true;
                        }
                    }
                }
            }
        }
        
        include_once("./Services/UIComponent/GroupedList/classes/class.ilGroupedListGUI.php");
        $gl = new ilGroupedListGUI();
        $gl->setAsDropDown(true);

        for ($i = 1; $i <= 3; $i++) {
            if ($i > 1) {
                $gl->nextColumn();
            }
            foreach ($groups[$i] as $group => $entries) {
                if (count($entries) > 0) {
                    $gl->addGroupHeader($lng->txt("adm_" . $group));
                        
                    foreach ($entries as $e) {
                        if ($e == "---") {
                            $gl->addSeparator();
                        } else {
                            $path = ilObject::_getIcon("", "tiny", $titems[$e]["type"]);
                            $icon = ($path != "")
                                ? ilUtil::img($path) . " "
                                : "";
                                
                            if ($_GET["admin_mode"] == "settings" && $titems[$e]["ref_id"] == ROOT_FOLDER_ID) {
                                $gl->addEntry(
                                    $icon . $titems[$e]["title"],
                                    "ilias.php?baseClass=ilAdministrationGUI&ref_id=" .
                                    $titems[$e]["ref_id"] . "&admin_mode=repository",
                                    "_top",
                                    "",
                                    "",
                                    "mm_adm_rep",
                                    ilHelp::getMainMenuTooltip("mm_adm_rep"),
                                    "bottom center",
                                    "top center",
                                    false
                                );
                            } else {
                                $gl->addEntry(
                                    $icon . $titems[$e]["title"],
                                    "ilias.php?baseClass=ilAdministrationGUI&ref_id=" .
                                        $titems[$e]["ref_id"] . "&cmd=jump",
                                    "_top",
                                    "",
                                    "",
                                    "mm_adm_" . $titems[$e]["type"],
                                    ilHelp::getMainMenuTooltip("mm_adm_" . $titems[$e]["type"]),
                                    "bottom center",
                                    "top center",
                                    false
                                );
                            }
                        }
                    }
                }
            }
        }

        echo $gl->getHTML();
        exit;
    }

    /**
     * Jump to node
     */
    public function jump()
    {
        $ilCtrl = $this->ctrl;
        $objDefinition = $this->objDefinition;

        $ref_id = (int) $_GET["ref_id"];
        $obj_id = ilObject::_lookupObjId($ref_id);
        $obj_type = ilObject::_lookupType($obj_id);
        $class_name = $objDefinition->getClassName($obj_type);
        $class = strtolower("ilObj" . $class_name . "GUI");
        $ilCtrl->setParameterByClass($class, "ref_id", $ref_id);
        $ilCtrl->redirectByClass($class, "view");
    }
}
