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

declare(strict_types=1);

use ILIAS\Administration\AdminGUIRequest;
use ILIAS\GlobalScreen\Services as GlobalScreen;

/**
* Class ilAdministrationGUI
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
* @ilCtrl_Calls ilAdministrationGUI: ilObjTestFolderGUI, ilObjExternalToolsSettingsGUI, ilObjUserTrackingGUI
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
* @ilCtrl_Calls ilAdministrationGUI: ilObjLearningSequenceAdminGUI, ilObjContentPageAdministrationGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjIndividualAssessmentGUI
* @ilCtrl_Calls ilAdministrationGUI: ilLPProgressTableGUI
*/
class ilAdministrationGUI implements ilCtrlBaseClassInterface
{
    private const array COMMANDS = ['forward', 'jump', 'jumpToPluginSlot', 'showTree'];

    private readonly ilObjectDefinition $obj_definition;
    private readonly ilHelpGUI $help;
    private readonly ilLanguage $lng;
    private readonly ilGlobalTemplateInterface $tpl;
    private readonly ilTree $tree;
    private readonly ilAccessHandler $access;
    private readonly ilRbacReview $rbac_review;
    private readonly ilObjUser $user;
    private readonly ilCtrl $ctrl;
    private readonly AdminGUIRequest $request;
    private readonly GlobalScreen $global_screen;
    private readonly ilLogger $logger;

    private int $cur_ref_id;
    private string $admin_mode = "";
    private int $requested_obj_id = 0;
    private ilObjectGUI $gui_obj;

    public function __construct()
    {
        global $DIC;

        $this->help = $DIC["ilHelp"];
        $this->logger = $DIC->logger()->root();
        $this->lng = $DIC->language();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->tree = $DIC->repositoryTree();
        $this->access = $DIC->access();
        $this->user = $DIC->user();
        $this->rbac_review = $DIC->rbac()->review();
        $this->obj_definition = $DIC["objDefinition"];
        $this->ctrl = $DIC->ctrl();
        $this->global_screen = $DIC->globalScreen();

        $this->lng->loadLanguageModule('administration');
        $this->lng->loadLanguageModule('benchmark');

        $context = $this->global_screen->tool()->context();
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
        if ($this->tree->isInTree($ref_id)) {
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
        // check the basic permission
        // - admin nodes and their childs (e.g. org units) must have read permission to be called
        // - admin mode for repository and trash is only available to the global admin role
        $has_access = false;
        if ($this->cur_ref_id === SYSTEM_FOLDER_ID || $this->tree->isGrandChild(SYSTEM_FOLDER_ID, $this->cur_ref_id)) {
            $has_access = $this->access->checkAccess('read', '', $this->cur_ref_id);
        } else {
            $has_access = $this->rbac_review->isAssigned($this->user->getId(), SYSTEM_ROLE_ID);
        }
        if (!$has_access) {
            $this->logger->log($this->lng->txt('permission_denied'), ilLogLevel::INFO);
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'), true);
            $this->ctrl->redirectToURL(
                ilUserUtil::getStartingPointAsUrl()
            );
        }

        // check creation mode
        // determined by "new_type" parameter
        // e.g. creation of a new role, user org unit, talk template
        $new_type = $this->request->getNewType();
        if ($new_type) {
            $creation_mode = true;
            $obj_type = $new_type;
            $class_name = $this->obj_definition->getClassName($obj_type);
            $next_class = strtolower("ilObj" . $class_name . "GUI");
        } else {
            $creation_mode = false;
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
            $class_name = $this->obj_definition->getClassName($obj_type);
            $next_class = strtolower("ilObj" . $class_name . "GUI");

            // #47446: redirect to remove the "return" command which is not implemented by all GUIs
            $this->ctrl->redirectByClass($next_class);
        }

        // forward all other classes to gui commands
        if ($next_class != "" && $next_class !== "iladministrationgui") {
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
                if ($this->obj_definition->isPlugin(ilObject::_lookupType($this->cur_ref_id, true))) {
                    $this->gui_obj = new $class_name($this->cur_ref_id);
                } elseif (!$creation_mode) {
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
                $this->gui_obj->setCreationMode($creation_mode);
            }
            $this->gui_obj->setAdminMode($this->admin_mode);
            $this->help->setScreenIdComponent(ilObject::_lookupType($this->cur_ref_id, true));
            $this->showTree();

            $this->ctrl->setReturn($this, "return");
            $ret = $this->ctrl->forwardCommand($this->gui_obj);
            $html = $this->gui_obj->getHTML();

            if ($html != "") {
                $this->tpl->setVariable("OBJECTS", $html);
            }
            $this->tpl->printToStdout();

        } else {
            // local command
            $cmd = $this->ctrl->getCmd("forward");
            if (in_array($cmd, self::COMMANDS)) {
                $this->$cmd();
            }
        }
    }

    /**
     * Redirect in special cases
     * - Administration mode of the repository
     * - Direct jump to user editing
     *
     * @throws ilCtrlException
     * @throws ilPermissionException
     */
    private function forward(): void
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
            }
        } else {
            $this->ctrl->setParameter($this, "ref_id", ROOT_FOLDER_ID);
            $this->ctrl->setParameterByClass("iladministrationgui", "admin_mode", "repository");
            $this->ctrl->redirectByClass("ilobjrootfoldergui", "view");
        }
    }

    /**
     * Show the repository tree in the slate
     * This command is used by ilAdministrationExplorerGUI the for administration mode of the repository
     */
    private function showTree(): void
    {
        if ($this->admin_mode !== "repository") {
            return;
        }

        $this->global_screen->tool()->context()->current()->addAdditionalData(ilAdminGSToolProvider::SHOW_ADMIN_TREE, true);

        $exp = new ilAdministrationExplorerGUI(self::class, "showTree");
        $exp->handleCommand();
    }

    /**
     * Special jump to plugin slot after ilCtrl has been reloaded
     * Ths command is used by ilObjComponentSettingsGUI for plugin updates
     */
    private function jumpToPluginSlot(): void
    {
        $this->ctrl->redirectByClass("ilobjcomponentsettingsgui", "listPlugins");
    }

    /**
     * Jump to the GUI of an administration node
     * This command is used by AdministrationMainBarProvider for the nodes in the admin menu
     */
    private function jump(): void
    {
        $ref_id = $this->request->getRefId();
        $obj_id = ilObject::_lookupObjId($ref_id);
        $obj_type = ilObject::_lookupType($obj_id);
        $class_name = $this->obj_definition->getClassName($obj_type);
        $class = strtolower("ilObj" . $class_name . "GUI");
        $this->ctrl->setParameterByClass($class, "ref_id", $ref_id);
        $this->ctrl->redirectByClass($class, "view");
    }
}
