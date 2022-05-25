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

use ILIAS\GlobalScreen\ScreenContext\ContextServices;
use ILIAS\Repository\StandardGUIRequest;

/**
 * Class ilRepositoryGUI
 *
 * @author Alexander Killing <killing@leifos.de>
 *
 * @ilCtrl_Calls ilRepositoryGUI: ilObjGroupGUI, ilObjFolderGUI, ilObjFileGUI, ilObjCourseGUI, ilCourseObjectivesGUI
 * @ilCtrl_Calls ilRepositoryGUI: ilObjSAHSLearningModuleGUI, ilObjChatroomGUI, ilObjForumGUI
 * @ilCtrl_Calls ilRepositoryGUI: ilObjLearningModuleGUI, ilObjGlossaryGUI
 * @ilCtrl_Calls ilRepositoryGUI: ilObjQuestionPoolGUI, ilObjSurveyQuestionPoolGUI, ilObjTestGUI
 * @ilCtrl_Calls ilRepositoryGUI: ilObjSurveyGUI, ilObjExerciseGUI, ilObjMediaPoolGUI, ilObjFileBasedLMGUI
 * @ilCtrl_Calls ilRepositoryGUI: ilObjCategoryGUI, ilObjRoleGUI, ilObjBlogGUI
 * @ilCtrl_Calls ilRepositoryGUI: ilObjLinkResourceGUI
 * @ilCtrl_Calls ilRepositoryGUI: ilObjRootFolderGUI, ilObjMediaCastGUI, ilObjRemoteCourseGUI, ilObjSessionGUI
 * @ilCtrl_Calls ilRepositoryGUI: ilObjCourseReferenceGUI, ilObjCategoryReferenceGUI, ilObjDataCollectionGUI, ilObjGroupReferenceGUI, ilObjStudyProgrammeReferenceGUI
 * @ilCtrl_Calls ilRepositoryGUI: ilObjPollGUI, ilObjRemoteCategoryGUI, ilObjRemoteWikiGUI, ilObjRemoteLearningModuleGUI
 * @ilCtrl_Calls ilRepositoryGUI: ilObjRemoteGlossaryGUI, ilObjRemoteFileGUI, ilObjRemoteGroupGUI
 * @ilCtrl_Calls ilRepositoryGUI: ilObjRemoteTestGUI, ilObjCloudGUI, ilObjPortfolioTemplateGUI
 * @ilCtrl_Calls ilRepositoryGUI: ilObjStudyProgrammeGUI
 * @ilCtrl_Calls ilRepositoryGUI: ilObjIndividualAssessmentGUI
 * @ilCtrl_Calls ilRepositoryGUI: ilObjLTIConsumerGUI
 * @ilCtrl_Calls ilRepositoryGUI: ilObjCmiXapiGUI
 * @ilCtrl_Calls ilRepositoryGUI: ilPermissionGUI
 *
 */
class ilRepositoryGUI implements ilCtrlBaseClassInterface
{
    protected ilObjectDefinition $objDefinition;
    protected ilLogger $log;
    protected ilObjUser $user;
    protected ilSetting $settings;
    protected ilHelpGUI $help;
    protected ilErrorHandling $error;
    protected ilAccessHandler $access;
    protected ContextServices $tool_context;
    public ilLanguage $lng;
    public ilGlobalTemplateInterface $tpl;
    public ilTree $tree;
    public ilRbacSystem $rbacsystem;
    public int $cur_ref_id;
    public string $cmd;
    public string $mode = "";
    public ilCtrl $ctrl;
    private \ILIAS\HTTP\Services $http;
    protected StandardGUIRequest $request;
    protected bool $creation_mode;
    protected ilObjectGUI $gui_obj;

    public function __construct()
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->log = $DIC["ilLog"];
        $this->user = $DIC->user();
        $this->settings = $DIC->settings();
        $this->help = $DIC["ilHelp"];
        $this->error = $DIC["ilErr"];
        $this->access = $DIC->access();
        $lng = $DIC->language();
        $tpl = $DIC->ui()->mainTemplate();
        $tree = $DIC->repositoryTree();
        $rbacsystem = $DIC->rbac()->system();
        $objDefinition = $DIC["objDefinition"];
        $ilCtrl = $DIC->ctrl();
        $this->tool_context = $DIC->globalScreen()->tool()->context();
        $this->http = $DIC->http();

        $this->lng = $lng;
        $this->tpl = $tpl;
        $this->tree = $tree;
        $this->rbacsystem = $rbacsystem;
        $this->objDefinition = $objDefinition;

        $this->ctrl = $ilCtrl;

        $this->creation_mode = false;

        $this->ctrl->saveParameter($this, ["ref_id"]);
        $this->ctrl->setReturn($this, "");

        $this->request = $DIC->repository()->internal()->gui()->standardRequest();

        // determine current ref id and mode
        $this->cur_ref_id = $this->request->getRefId();
        if (!$tree->isInTree($this->cur_ref_id)) {
            $this->redirectToRoot();
        }
    }

    protected function redirectToRoot() : void
    {
        $ctrl = $this->ctrl;
        $ctrl->setParameterByClass(
            self::class,
            "ref_id",
            $this->tree->getRootId()
        );

        $ctrl->redirectByClass(self::class, "");
    }

    public function executeCommand() : void
    {
        $rbacsystem = $this->rbacsystem;
        $lng = $this->lng;
        $ilHelp = $this->help;
        $ilErr = $this->error;

        if (
            ($this->user->isAnonymous() || !($this->user->getId() >= 1)) &&
            !ilPublicSectionSettings::getInstance()->isEnabledForDomain(
                $this->http->request()->getServerParams()['SERVER_NAME']
            )
        ) {
            $this->ctrl->redirectToURL('./login.php?cmd=force_login');
        }

        $this->tool_context->claim()->repository();

        // check creation mode
        // determined by "new_type" parameter
        $new_type = $this->request->getNewType();

        if ($new_type !== "" && $new_type !== "sty") {
            $this->creation_mode = true;
            $ilHelp->setScreenIdComponent($new_type);
            $ilHelp->setDefaultScreenId(ilHelpGUI::ID_PART_SCREEN, "create");
        }

        $cmd = $this->ctrl->getCmd();

        // determine next class
        if ($this->creation_mode) {
            $obj_type = $new_type;
            $class_name = $this->objDefinition->getClassName($obj_type);
            if (strtolower($class_name) !== "user") {
                $next_class = strtolower("ilObj" . $class_name . "GUI");
            } else {
                $next_class = $this->ctrl->getNextClass();
            }
            // Only set the fixed cmdClass if the next class is different to
            // the GUI class of the new object.
            // An example:
            // Copy Category uses this call structure:
            // RespositoryGUI -> CategoryGUI -> ilObjectCopyGUI
            // Without this fix, the cmdClass ilObjectCopyGUI would never be reached

            ilLoggerFactory::getLogger('obj')->debug($this->ctrl->getNextClass() . ' <-> ' . $class_name);

            if ($this->ctrl->getNextClass() !== strtolower('ilObj' . $class_name . 'GUI')) {
                $this->ctrl->setCmdClass($next_class);
            }
        } elseif ((($next_class = $this->ctrl->getNextClass($this)) == "")
            || ($next_class === "ilrepositorygui" && $this->ctrl->getCmd() === "return")) {
            // get GUI of current object
            $obj_type = ilObject::_lookupType($this->cur_ref_id, true);
            $class_name = $this->objDefinition->getClassName($obj_type);
            $next_class = strtolower("ilObj" . $class_name . "GUI");

            $this->ctrl->setCmdClass($next_class);
            if ($this->ctrl->getCmd() === "return") {
                //$this->ctrl->setCmd(null);    // this does not work anymore
                $this->ctrl->redirectByClass($next_class, "");
            }
        }

        // commands that are always handled by repository gui
        // to do: move to container
        if ($cmd === "showRepTree") {
            $next_class = "";
        }

        switch ($next_class) {
            // forward asynchronous file uploads to the upload handler.
            // possible via dropzones in list guis or global template
            // sections like title.
            case strtolower(ilObjFileUploadHandlerGUI::class):
                $this->ctrl->forwardCommand(new ilObjFileUploadHandlerGUI());
                break;

            default:
                // forward all other classes to gui commands
                if ($next_class !== null && $next_class !== "" && $next_class !== "ilrepositorygui") {
                    $class_path = $this->ctrl->lookupClassPath($next_class);
                    // get gui class instance
                    //require_once($class_path);
                    $class_name = $this->ctrl->getClassForClasspath($class_path);
                    if (!$this->creation_mode) {
                        if (is_subclass_of($class_name, "ilObject2GUI")) {
                            $this->gui_obj = new $class_name($this->cur_ref_id, ilObject2GUI::REPOSITORY_NODE_ID);
                        } else {
                            $this->gui_obj = new $class_name("", $this->cur_ref_id, true, false);
                        }
                    } elseif (is_subclass_of($class_name, "ilObject2GUI")) {
                        $this->gui_obj = new $class_name(0, ilObject2GUI::REPOSITORY_NODE_ID, $this->cur_ref_id);
                    } else {
                        $this->gui_obj = new $class_name("", 0, true, false);
                    }
                    $this->gui_obj->setCreationMode($this->creation_mode);
                    $this->ctrl->setReturn($this, "return");

                    $this->show();
                } else {	//
                    $cmd = (string) $this->ctrl->getCmd("");

                    // check read access for category
                    if ($cmd !== "showRepTree" && $this->cur_ref_id > 0 && !$rbacsystem->checkAccess("read", $this->cur_ref_id)) {
                        $ilErr->raiseError($lng->txt("permission_denied"), $ilErr->MESSAGE);
                        $this->tpl->printToStdout();
                    } else {
                        $this->cmd = $cmd;
                        $this->$cmd();
                    }
                }
                break;
        }
    }

    public function show() : void
    {
        // normal command processing
        $this->ctrl->forwardCommand($this->gui_obj);
        $this->tpl->setVariable("OBJECTS", $this->gui_obj->getHTML());
        $this->tpl->printToStdout();
    }

    public function showRepTree() : void
    {
        $exp = new ilRepositoryExplorerGUI($this, "showRepTree");
        // root node should be skipped, see #26787
        $exp->setSkipRootNode(true);
        $exp->handleCommand();
    }
}
