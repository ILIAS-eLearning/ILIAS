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

use ILIAS\Administration\AdminGUIRequest;

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
* @ilCtrl_Calls ilAdministrationGUI: ilObjLanguageFolderGUI, ilObjMailGUI
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
* @ilCtrl_Calls ilAdministrationGUI: ilObjLearningSequenceAdminGUI, ilObjContentPageAdministrationGUI, ilObjPDFGenerationGUI
*/
class ilAdministrationGUI implements ilCtrlBaseClassInterface
{
    protected ilObjectDefinition $objDefinition;
    protected ilHelpGUI $help;
    protected ilDBInterface $db;
    public ilLanguage $lng;
    public ilGlobalTemplateInterface $tpl;
    public ilTree $tree;
    public ilRbacSystem $rbacsystem;
    public int $cur_ref_id;
    public string $cmd;
    public ilCtrl $ctrl;
    protected string $admin_mode = "";
    protected bool $creation_mode = false;
    protected int $requested_obj_id = 0;
    protected AdminGUIRequest $request;
    protected ilObjectGUI $gui_obj;

    public function __construct()
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->help = $DIC["ilHelp"];
        $this->db = $DIC->database();
        $lng = $DIC->language();
        $tpl = $DIC->ui()->mainTemplate();
        $tree = $DIC->repositoryTree();
        $rbacsystem = $DIC->rbac()->system();
        $objDefinition = $DIC["objDefinition"];
        $ilCtrl = $DIC->ctrl();

        $this->lng = $lng;
        $this->lng->loadLanguageModule('administration');
        $this->tpl = $tpl;
        $this->tree = $tree;
        $this->rbacsystem = $rbacsystem;
        $this->objDefinition = $objDefinition;
        $this->ctrl = $ilCtrl;

        $context = $DIC->globalScreen()->tool()->context();
        $context->claim()->administration();

        $this->request = new AdminGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

        $this->ctrl->saveParameter($this, array("ref_id", "admin_mode"));

        $this->admin_mode = $this->request->getAdminMode();
        if ($this->admin_mode !== ilObjectGUI::ADMIN_MODE_REPOSITORY) {
            $this->admin_mode = ilObjectGUI::ADMIN_MODE_SETTINGS;
        }

        $this->ctrl->setReturn($this, "");

        // determine current ref id and mode
        $ref_id = $this->request->getRefId();
        if ($tree->isInTree($ref_id)) {
            $this->cur_ref_id = $ref_id;
        } else {
            throw new ilPermissionException("Invalid ref id.");
        }

        $this->requested_obj_id = $this->request->getObjId();
    }


    /**
     * @throws ilCtrlException
     * @throws ilPermissionException
     */
    public function executeCommand(): void
    {
        $rbacsystem = $this->rbacsystem;
        $objDefinition = $this->objDefinition;
        $ilHelp = $this->help;
        $ilDB = $this->db;

        // permission checks
        if (!$rbacsystem->checkAccess("visible", SYSTEM_FOLDER_ID) &&
                !$rbacsystem->checkAccess("read", SYSTEM_FOLDER_ID)) {
            throw new ilPermissionException($this->lng->txt('permission_denied'));
        }

        // check creation mode
        // determined by "new_type" parameter
        $new_type = $this->request->getNewType();
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
        if ($this->ctrl->getCmdClass() === "ilobjlanguageextgui") {
            $next_class = "ilobjlanguageextgui";
        } else {
            $next_class = $this->ctrl->getNextClass($this);
        }

        if ((
            $next_class === "iladministrationgui" || $next_class == ""
        ) && ($this->ctrl->getCmd() === "return")) {
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
                if ($next_class != "" && $next_class !== "iladministrationgui") {
                    // check db update
                    $dbupdate = new ilDBUpdate($ilDB);
                    if (!$dbupdate->getDBVersionStatus()) {
                        $this->tpl->setOnScreenMessage('failure', $this->lng->txt("db_need_update"));
                    } elseif ($dbupdate->hotfixAvailable()) {
                        $this->tpl->setOnScreenMessage('failure', $this->lng->txt("db_need_hotfix"));
                    }

                    $class_path = $this->ctrl->lookupClassPath($next_class);
                    if (is_file($class_path)) {
                        require_once $class_path;   // note: org unit plugins still need the require
                    }
                    // get gui class instance
                    $class_name = $this->ctrl->getClassForClasspath($class_path);
                    if (($next_class === "ilobjrolegui" || $next_class === "ilobjusergui"
                        || $next_class === "ilobjroletemplategui")) {
                        if ($this->requested_obj_id > 0) {
                            $this->gui_obj = new $class_name(null, $this->requested_obj_id, false, false);
                            $this->gui_obj->setCreationMode(false);
                        } else {
                            $this->gui_obj = new $class_name(null, $this->cur_ref_id, true, false);
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
                                    $this->gui_obj = new $class_name(null, $this->cur_ref_id, true, false);
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
                    $this->gui_obj->setAdminMode($this->admin_mode);
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
     * @throws ilCtrlException
     * @throws ilPermissionException
     */
    public function forward(): void
    {
        if ($this->admin_mode !== "repository") {	// settings
            if ($this->request->getRefId() == USER_FOLDER_ID) {
                $this->ctrl->setParameter($this, "ref_id", USER_FOLDER_ID);
                $this->ctrl->setParameterByClass("iladministrationgui", "admin_mode", "settings");
                if (ilObject::_lookupType($this->request->getJumpToUserId()) === "usr") {
                    $this->ctrl->setParameterByClass(
                        "ilobjuserfoldergui",
                        "jmpToUser",
                        $this->request->getJumpToUserId()
                    );
                    $this->ctrl->redirectByClass("ilobjuserfoldergui", "jumpToUser");
                } else {
                    $this->ctrl->redirectByClass("ilobjuserfoldergui", "view");
                }
            } else {
                // this code should not be necessary anymore...
                throw new ilPermissionException("Missing AdmiGUI parameter.");

                /*
                $this->ctrl->setParameter($this, "ref_id", SYSTEM_FOLDER_ID);
                $this->ctrl->setParameterByClass("iladministrationgui", "admin_mode", "settings");


                if ($_GET['fr']) {
                    // Security check: We do only allow relative urls
                    $url_parts = parse_url(base64_decode(rawurldecode($_GET['fr'])));
                    if ($url_parts['http'] || $url_parts['host']) {
                        throw new ilPermissionException($this->lng->txt('permission_denied'));
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
                }*/
            }
        } else {
            $this->ctrl->setParameter($this, "ref_id", ROOT_FOLDER_ID);
            $this->ctrl->setParameterByClass("iladministrationgui", "admin_mode", "repository");
            $this->ctrl->redirectByClass("ilobjrootfoldergui", "view");
        }
    }

    public function showTree(): void
    {
        global $DIC;

        if ($this->admin_mode !== "repository") {
            return;
        }

        $DIC->globalScreen()->tool()->context()->current()->addAdditionalData(ilAdminGSToolProvider::SHOW_ADMIN_TREE, true);

        $exp = new ilAdministrationExplorerGUI(self::class, "showTree");
        $exp->handleCommand();
    }

    // Special jump to plugin slot after ilCtrl has been reloaded
    public function jumpToPluginSlot(): void
    {
        $ilCtrl = $this->ctrl;
        $ilCtrl->redirectByClass("ilobjcomponentsettingsgui", "listPlugins");
    }

    // Jump to node
    public function jump(): void
    {
        $ilCtrl = $this->ctrl;
        $objDefinition = $this->objDefinition;

        $ref_id = $this->request->getRefId();
        $obj_id = ilObject::_lookupObjId($ref_id);
        $obj_type = ilObject::_lookupType($obj_id);
        $class_name = $objDefinition->getClassName($obj_type);
        $class = strtolower("ilObj" . $class_name . "GUI");
        $ilCtrl->setParameterByClass($class, "ref_id", $ref_id);
        $ilCtrl->redirectByClass($class, "view");
    }
}
