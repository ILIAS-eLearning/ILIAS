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

use ILIAS\GlobalScreen\ScreenContext\ContextServices;
use ILIAS\Blog\BlogGUIContext;
use ILIAS\Blog\Settings\SettingsGUI;
use ILIAS\Blog\Permission\PermissionManager;
use ILIAS\Blog\InternalDomainService;
use ILIAS\Blog\InternalGUIService;
use ILIAS\Blog\Contributor\ContributorGUI;
use ILIAS\Blog\Editing\EditingGUI;

/**
 * @ilCtrl_Calls ilObjBlogGUI: ilWorkspaceAccessGUI
 * @ilCtrl_Calls ilObjBlogGUI: ilInfoScreenGUI, ilNoteGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjBlogGUI: ilPermissionGUI, ilObjectCopyGUI
 * @ilCtrl_Calls ilObjBlogGUI: ilExportGUI, ilObjectContentStyleSettingsGUI, ilBlogExerciseGUI, ilObjNotificationSettingsGUI
 * @ilCtrl_Calls ilObjBlogGUI: ilObjectMetaDataGUI
 * @ilCtrl_Calls ilObjBlogGUI: ILIAS\Blog\Settings\SettingsGUI
 * @ilCtrl_Calls ilObjBlogGUI: ILIAS\Blog\Settings\BlockSettingsGUI
 * @ilCtrl_Calls ilObjBlogGUI: ILIAS\Blog\Contributor\ContributorGUI
 * @ilCtrl_Calls ilObjBlogGUI: ILIAS\Blog\Editing\EditingGUI
 * @ilCtrl_Calls ilObjBlogGUI: ILIAS\Blog\Presentation\PresentationGUI
 * @ilCtrl_Calls ilObjBlogGUI: ILIAS\Blog\Export\ExportGUI
 */
class ilObjBlogGUI extends ilObject2GUI implements ilDesktopItemHandling
{
    protected \ILIAS\Style\Content\Service $cs;
    protected \ILIAS\Blog\Permission\BlogCmdPermission $cmd_perm;
    protected PermissionManager $perm;
    protected InternalDomainService $domain;
    protected InternalGUIService $gui;

    protected ilTabsGUI $tabs;
    protected ilNavigationHistory $nav_history;
    protected BlogGUIContext $blog_context;

    protected ContextServices $tool_context;
    protected \ILIAS\Style\Content\GUIService $content_style_gui;
    protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain;

    public function __construct(
        int $a_id = 0,
        int $a_id_type = self::REPOSITORY_NODE_ID,
        int $a_parent_node_id = 0
    ) {
        global $DIC;
        // other services
        $this->cs = $DIC->contentStyle();
        $this->tool_context = $DIC->globalScreen()->tool()->context();

        // internal service
        $service = $DIC->blog()->internal();

        $this->domain = $domain = $service->domain();
        $this->gui = $gui = $service->gui();

        $this->settings = $domain->settings();
        $this->user = $domain->user();
        $this->tree = $domain->repositoryTree();
        $this->lng = $domain->lng();

        $this->tabs = $gui->tabs();
        $this->toolbar = $gui->toolbar();
        $this->locator = $gui->locator();

        $this->nav_history = $DIC["ilNavigationHistory"];
        $this->ctrl = $gui->ctrl();

        $blog_request = $gui->standardRequest();
        $month = $blog_request->getMonth();
        $author = $blog_request->getAuthor();

        parent::__construct($a_id, $a_id_type, $a_parent_node_id);

        $blog_page = $blog_request->getBlogPage();
        if ($blog_page > 0 &&
            $domain->posting()->lookupBlogId($blog_page) !== $this->object->getId()) {
            throw new ilException("Posting ID does not match blog.");
        }

        if ($this->object) {
            $this->content_style_gui = $this->cs->gui();
            if (is_object($this->object)) {
                if ($this->id_type !== self::REPOSITORY_NODE_ID) {
                    $this->content_style_domain = $this->cs->domain()->styleForObjId($this->object->getId());
                } else {
                    $this->content_style_domain = $this->cs->domain()->styleForRefId($this->object->getRefId());
                }
            }

            $view_state = $domain->postingList($this->object->getId())
                ->prepareViewState($month, $author);
            $month = $view_state->getMonth();
            $author = $view_state->getAuthor();

            $this->ctrl->setParameter($this, "bmn", $month);
        }

        $this->lng->loadLanguageModule("blog");

        $owner = $this->object?->getOwner() ?? 0;
        $this->perm = $domain->perm(
            $this->node_id,
            $this->id_type,
            $this->user->getId(),
            $owner
        );
        $this->cmd_perm = $gui->cmdPerm($this->perm);
        $this->blog_context = $gui->blogContext(
            $this->node_id,
            $this->id_type,
            $this->object?->getId(),
            $month,
            $author,
            $this->perm,
            $this->call_by_reference
        );
    }

    public function getType(): string
    {
        return "blog";
    }

    protected function afterSave(ilObject $new_object): void
    {
        $ilCtrl = $this->ctrl;

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("object_added"), true);
        $ilCtrl->redirect($this, "edit");
    }


    public function edit(): void
    {
        $this->ctrl->redirectByClass(SettingsGUI::class, "");
    }

    protected function setTabs(): void
    {
        $this->gui->navigation()->navigationGUI($this->blog_context)->setTabs();
        parent::setTabs();
    }

    public function executeCommand(): void
    {
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        $ilNavigationHistory = $this->nav_history;

        $next_class = $ilCtrl->getNextClass($this);

        if ($next_class !== "ilexportgui") {
            $this->triggerAssignmentTool();
        }

        // add entry to navigation history
        if (($this->id_type === self::REPOSITORY_NODE_ID) && !$this->getCreationMode() &&
            $this->getAccessHandler()->checkAccess("read", "", $this->node_id)) {
            // see #22067
            $link = $ilCtrl->getLinkTargetByClass([
                ilRepositoryGUI::class,
                ilObjBlogGUI::class,
                \ILIAS\Blog\Presentation\PresentationGUI::class
            ], "preview");
            $ilNavigationHistory->addItem($this->node_id, $link, "blog");
        }
        switch ($next_class) {
            case "ilinfoscreengui":
                $this->prepareOutput();
                $this->infoScreenForward();
                break;

            case "ilnotegui":
                $this->preview();
                break;

            case "ilcommonactiondispatchergui":
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $gui->enableCommentsSettings(false);
                $this->prepareOutput();
                $this->cmd_perm->forwardPermitted($this, $gui);
                break;

            case "ilpermissiongui":
                $this->prepareOutput();
                $ilTabs->activateTab("id_permissions");
                $perm_gui = new ilPermissionGUI($this);
                $this->cmd_perm->forwardPermitted($this, $perm_gui);
                break;

            case "ilobjectcopygui":
                $this->prepareOutput();
                $cp = new ilObjectCopyGUI($this);
                $cp->setType("blog");
                $this->cmd_perm->forwardPermitted($this, $cp);
                break;

            case strtolower(\ilRepositorySearchGUI::class):
                $this->checkPermission("write");
                $this->prepareOutput();
                $ilTabs->activateTab("contributors");
                $this->cmd_perm->forwardPermitted($this, $this->gui->contributor()->contributorGUI($this->node_id, $this->object));
                break;

            case 'ilexportgui':
                $this->prepareOutput();
                $this->tabs->activateTab("export");
                $exp_gui = new ilExportGUI($this);
                $this->cmd_perm->forwardPermitted($this, $exp_gui);
                break;

            case "ilobjectcontentstylesettingsgui":
                $this->checkPermission("write");
                $this->prepareOutput();
                $this->addHeaderAction();
                $ilTabs->activateTab("settings");
                $this->gui->navigation()->navigationGUI($this->blog_context)
                    ->setSettingsSubTabs("style");


                if ($this->id_type === self::REPOSITORY_NODE_ID) {
                    $settings_gui = $this->content_style_gui
                        ->objectSettingsGUIForRefId(
                            null,
                            $this->object->getRefId()
                        );
                } else {
                    $settings_gui = $this->content_style_gui
                        ->objectSettingsGUIForObjId(
                            null,
                            $this->object->getId()
                        );
                }
                $this->cmd_perm->forwardPermitted($this, $settings_gui);
                break;


            case "ilblogexercisegui":
                $this->ctrl->setReturn($this, "render");
                $gui = $this->gui->exercise()->ilBlogExerciseGUI($this->node_id);
                $this->cmd_perm->forwardPermitted($this, $gui);
                break;

            case 'ilobjnotificationsettingsgui':
                $this->prepareOutput();
                $ilTabs->activateTab("settings");
                $this->gui->navigation()->navigationGUI($this->blog_context)
                    ->setSettingsSubTabs("notifications");
                $gui = new ilObjNotificationSettingsGUI($this->object->getRefId());
                $this->cmd_perm->forwardPermitted($this, $gui);
                break;

            case strtolower(ilObjectMetaDataGUI::class):
                $this->checkPermission("write");
                $this->prepareOutput();
                $ilTabs->activateTab("meta_data");
                $gui = new ilObjectMetaDataGUI($this->object, null, null, $this->call_by_reference);
                $this->cmd_perm->forwardPermitted($this, $gui);
                break;

            case strtolower(SettingsGUI::class):
                $this->checkPermission("write");
                $this->prepareOutput();
                $ilTabs->activateTab("settings");
                $this->gui->navigation()->navigationGUI($this->blog_context)
                    ->setSettingsSubTabs("properties");
                $gui = $this->gui->settings()->settingsGUI(
                    $this->obj_id,
                    $this->id_type === self::REPOSITORY_NODE_ID
                );
                $this->cmd_perm->forwardPermitted($this, $gui);
                break;

            case strtolower(\ILIAS\Blog\Editing\EditingGUI::class):
                $this->prepareOutput();
                $this->addHeaderAction();
                $gui = $this->gui->editing()->editingGUI(
                    $this->blog_context,
                    $this->cs
                );
                $this->cmd_perm->forwardPermitted($this, $gui);
                break;

            case strtolower(\ILIAS\Blog\Presentation\PresentationGUI::class):
                $this->prepareOutput();
                $this->initHeaderAction(null, null, true);
                $gui = $this->gui->presentation()->presentationGUI(
                    $this->blog_context,
                    $this->content_style_domain,
                    function () {
                        $this->addPresentationHeaderAction();
                    }
                );
                $this->cmd_perm->forwardPermitted($this, $gui);
                break;

            case strtolower(ContributorGUI::class):
                $this->checkPermission("write");
                $this->prepareOutput();
                $ilTabs->activateTab("contributors");
                $gui = $this->gui->contributor()->contributorGUI(
                    $this->node_id,
                    $this->object
                );
                $this->cmd_perm->forwardPermitted($this, $gui);
                break;

            case strtolower(\ILIAS\Blog\Settings\BlockSettingsGUI::class):
                $this->checkPermission("write");
                $this->prepareOutput();
                $ilTabs->activateTab("settings");
                $this->gui->navigation()->navigationGUI($this->blog_context)
                    ->setSettingsSubTabs("side_blocks");
                $gui = $this->gui->settings()->blockSettingsGUI(
                    $this->obj_id,
                    $this->id_type === self::REPOSITORY_NODE_ID
                );
                $this->cmd_perm->forwardPermitted($this, $gui);
                break;

            case strtolower(\ILIAS\Blog\Export\ExportGUI::class):
                $this->prepareOutput();
                $gui = $this->gui->export()->exportGUI(
                    $this->node_id,
                    $this->object->getOwner(),
                    $this->id_type === self::REPOSITORY_NODE_ID
                );
                $this->cmd_perm->forwardPermitted($this, $gui);
                break;

            case "ilworkspaceaccessgui":
                $this->checkPermission("write");
                parent::executeCommand();
                break;

            default:
                // desktop item handling, must be toggled before header action
                $cmd = $ilCtrl->getCmd();
                //$this->addHeaderActionForCommand($cmd);
                $this->prepareOutput();
                if ($this->cmd_perm->classImplementsMethodDirectly(get_class($this), $cmd)) {
                    $cmd = $this->cmd_perm->getPermittedCommand();
                }
                $this->$cmd();
        }
    }

    protected function triggerAssignmentTool(): void
    {
        $be = $this->domain->exercise($this->node_id);
        $be_gui = $this->gui->exercise()->ilBlogExerciseGUI($this->node_id);
        $assignments = $be->getAssignmentsOfBlog();
        if (count($assignments) > 0) {
            $ass_ids = array_map(static function ($i) {
                return $i["ass_id"];
            }, $assignments);
            $this->tool_context->current()->addAdditionalData(ilExerciseGSToolProvider::SHOW_EXC_ASSIGNMENT_INFO, true);
            $this->tool_context->current()->addAdditionalData(ilExerciseGSToolProvider::EXC_ASS_IDS, $ass_ids);
            $this->tool_context->current()->addAdditionalData(
                ilExerciseGSToolProvider::EXC_ASS_BUTTONS,
                $be_gui->getActionButtons()
            );
        }
    }

    /**
     * this one is called from the info button in the repository
     */
    public function infoScreen(): void
    {
        $this->ctrl->redirectByClass(ilInfoScreenGUI::class, "showSummary");
    }

    public function infoScreenForward(): void
    {
        $ilTabs = $this->tabs;

        $ilTabs->activateTab("id_info");

        $this->checkPermission("visible");

        $info = new ilInfoScreenGUI($this);

        if ($this->id_type !== self::WORKSPACE_NODE_ID) {
            $info->enablePrivateNotes();
        }

        if ($this->checkPermissionBool("read")) {
            $info->enableNews();
        }

        // no news editing for files, just notifications
        $info->enableNewsEditing(false);
        if ($this->checkPermissionBool("write")) {
            $news_set = new ilSetting("news");
            $enable_internal_rss = $news_set->get("enable_rss_for_internal");

            if ($enable_internal_rss) {
                $info->setBlockProperty("news", "settings", "1");
                $info->setBlockProperty("news", "public_notifications_option", "1");
            }
        }

        // standard meta data
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());

        $this->cmd_perm->forwardPermitted($this, $info);
    }

    // --- helper functions

    public function addPresentationHeaderAction(): void
    {
        $this->insertHeaderAction($this->initHeaderAction(null, null, true));
    }

    protected function initHeaderAction(
        ?string $sub_type = null,
        ?int $sub_id = null,
        bool $presenation = false
    ): ?ilObjectListGUI {
        if (!$this->obj_id) {
            return null;
        }
        $sub_type = $sub_id = null;
        if ($this->blog_context->getRequest()->getBlogPage() > 0) {
            $sub_type = "blp";
            $sub_id = $this->blog_context->getRequest()->getBlogPage();
        }

        $lg = parent::initHeaderAction($sub_type, $sub_id);
        if (!$lg) {
            return null;
        }
        return $this->gui->navigation()->navigationGUI($this->blog_context)
            ->prepareHeaderAction($lg, $presenation);
    }

    /**
     * Get title for blog posting (used in ilNotesGUI)
     */
    public static function lookupSubObjectTitle(
        int $a_blog_id,
        int $a_posting_id
    ): string {
        // page might be deleted, so setting halt on errors to false
        $post = new ilBlogPosting($a_posting_id);
        if ($post->getBlogId() === $a_blog_id) {
            return $post->getTitle();
        }
        return "";
    }

    public function checkPermissionBool(
        string $perm,
        string $cmd = "",
        string $type = "",
        ?int $ref_id = null
    ): bool {
        return parent::checkPermissionBool($perm, $cmd, $type, $ref_id);
    }

    protected function addLocatorItems(): void
    {
        $this->gui->navigation()->navigationGUI($this->blog_context)
            ->addLocatorItems();
    }


    protected function forwardExport(): void
    {
        $this->ctrl->redirectByClass(ilExportGUI::class);
    }
}
