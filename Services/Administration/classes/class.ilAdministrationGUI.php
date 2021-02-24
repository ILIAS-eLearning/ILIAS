<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilAdministratioGUI
*
* @author Alex Killing <alex.killing@gmx.de>
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
* @ilCtrl_Calls ilAdministrationGUI: ilObjMediaCastGUI
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
* @ilCtrl_Calls ilAdministrationGUI: ilObjFileAccessSettingsGUI, ilPermissionGUI, ilObjRemoteTestGUI, ilPropertyFormGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjCmiXapiAdministrationGUI, ilObjCmiXapiGUI, ilObjLTIConsumerGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjLearningSequenceAdminGUI
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
     * @var \ilLogger|null
     */
    private $logger = null;

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

        $this->logger = $DIC->logger()->adm();

        $context = $DIC->globalScreen()->tool()->context();
        $context->claim()->administration();

        $ilMainMenu->setActive("administration");
        
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
        if (!$rbacsystem->checkAccess("visible", SYSTEM_FOLDER_ID) &&
                !$rbacsystem->checkAccess("read", SYSTEM_FOLDER_ID)) {
            $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->WARNING);
        }

        // check creation mode
        // determined by "new_type" parameter
        $new_type = empty($_REQUEST['new_type']) ? '' : $_REQUEST['new_type'];
        if ($new_type) {
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
        if ($this->ctrl->getCmdClass() == "ilobjlanguageextgui") {
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
                    $dbupdate = new ilDBUpdate($ilDB);
                    if (!$dbupdate->getDBVersionStatus()) {
                        ilUtil::sendFailure($this->lng->txt("db_need_update"));
                    } elseif ($dbupdate->hotfixAvailable()) {
                        ilUtil::sendFailure($this->lng->txt("db_need_hotfix"));
                    }
                    
                    $class_path = $this->ctrl->lookupClassPath($next_class);
                    require_once $class_path;
                    // get gui class instance
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
                    $tabs_out = true;
                    $ilHelp->setScreenIdComponent(ilObject::_lookupType($this->cur_ref_id, true));
                    $this->showTree();
                        
                    $this->ctrl->setReturn($this, "return");
                    $ret = $this->ctrl->forwardCommand($this->gui_obj);
                    $html = $this->gui_obj->getHTML();

                    if ($html != "") {
                        $this->tpl->setVariable("OBJECTS", $html);
                    }
                    $this->tpl->printToStdout();
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
        global $DIC;

        if ($_GET["admin_mode"] != "repository") {
            return;
        }

        $DIC->globalScreen()->tool()->context()->current()->addAdditionalData(ilAdminGSToolProvider::SHOW_ADMIN_TREE, true);

        $exp = new ilAdministrationExplorerGUI($this, "showTree");
        $exp->handleCommand();
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
