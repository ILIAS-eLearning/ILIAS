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
use ILIAS\Blog\StandardGUIRequest;
use ILIAS\Blog\Settings\SettingsGUI;
use ILIAS\Blog\Permission\PermissionManager;
use ILIAS\Blog\InternalDomainService;
use ILIAS\Blog\InternalGUIService;
use ILIAS\Repository\Profile\ProfileAdapter;
use ILIAS\Repository\Profile\ProfileGUI;
use ILIAS\Blog\Settings\Settings;
use ILIAS\Blog\ReadingTime\ReadingTimeManager;
use ILIAS\Blog\Posting\PostingManager;
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
    protected PostingManager $posting_manager;
    protected \ILIAS\Blog\Permission\BlogCmdPermission $cmd_perm;
    protected ?Settings $blog_settings = null;
    protected ProfileGUI $profile_gui;
    protected ProfileAdapter $profile;
    protected PermissionManager $perm;
    protected InternalDomainService $domain;
    protected InternalGUIService $gui;
    protected string $rendered_content = "";
    protected \ILIAS\Notes\Service $notes;
    protected \ILIAS\Blog\ReadingTime\BlogSettingsGUI $reading_time_gui;
    protected ReadingTimeManager $reading_time_manager;

    protected StandardGUIRequest $blog_request;
    protected ilHelpGUI $help;
    protected ilTabsGUI $tabs;
    protected ilNavigationHistory $nav_history;
    protected ilRbacAdmin $rbacadmin;

    protected string $month = "";
    protected array $items = [];
    protected string $keyword = "";
    protected ?int $author = null;
    protected bool $month_default = false;
    protected int $blpg = 0;
    protected int $old_nr = 0;
    protected int $ppage = 0;
    protected int $user_page = 0;
    protected int $ntf = 0;
    protected string $new_type = "";
    protected ContextServices $tool_context;
    protected \ILIAS\DI\UIServices $ui;
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
        $this->notes = $DIC->notes();

        // internal service
        $service = $DIC->blog()->internal();

        $this->domain = $domain = $service->domain();
        $this->gui = $gui = $service->gui();

        $this->settings = $domain->settings();
        $this->user = $domain->user();
        $this->tree = $domain->repositoryTree();
        $this->rbac_review = $domain->rbac()->review();
        $this->rbacadmin = $domain->rbac()->admin();
        $this->lng = $domain->lng();
        $this->posting_manager = $domain->posting();

        $gui = $service->gui();
        $this->gui = $gui;
        $this->help = $gui->help();
        $this->tabs = $gui->tabs();
        $this->toolbar = $gui->toolbar();
        $this->ui = $gui->ui();
        $this->locator = $gui->locator();

        $this->nav_history = $DIC["ilNavigationHistory"];
        $this->ctrl = $gui->ctrl();

        $this->blog_request = $gui->standardRequest();

        $req = $this->blog_request;
        $this->blpg = $req->getBlogPage();
        $this->old_nr = $req->getOldNr();
        $this->ppage = $req->getPPage();
        $this->user_page = $req->getUserPage();
        $this->new_type = $req->getNewType();
        $this->month = $req->getMonth();
        $this->keyword = $req->getKeyword();
        $this->author = $req->getAuthor();

        parent::__construct($a_id, $a_id_type, $a_parent_node_id);

        $blog_page = $this->blog_request->getBlogPage();
        if ($blog_page > 0 &&
            $this->posting_manager->lookupBlogId($blog_page) !== $this->object->getId()) {
            throw new ilException("Posting ID does not match blog.");
        }

        $blog_id = 0;
        if ($this->object) {
            $this->content_style_gui = $this->cs->gui();
            if (is_object($this->object)) {
                if ($this->id_type !== self::REPOSITORY_NODE_ID) {
                    $this->content_style_domain = $this->cs->domain()->styleForObjId($this->object->getId());
                } else {
                    $this->content_style_domain = $this->cs->domain()->styleForRefId($this->object->getRefId());
                }
                $this->blog_settings =
                    $domain->blogSettings()->getByObjId($this->object->getId());
            }

            // gather postings by month
            $this->items = $this->buildPostingList($this->object->getId());
            if ($this->items) {
                // current month (if none given or empty)
                if (!$this->month || !isset($this->items[$this->month]) || $this->items[$this->month] === []) {
                    $m = array_keys($this->items);
                    $this->month = array_shift($m);
                    $this->month_default = true;
                }
            }

            $this->ctrl->setParameter($this, "bmn", $this->month);
            $blog_id = $this->object->getId();
        }

        $this->lng->loadLanguageModule("blog");

        $this->reading_time_gui = $gui->readingTime()->settingsGUI($blog_id);
        $this->reading_time_manager = $domain->readingTime();
        $this->notes = $DIC->notes();
        $owner = $this->object?->getOwner() ?? 0;
        $this->perm = $domain->perm(
            $this->node_id,
            $this->id_type,
            $this->user->getId(),
            $owner
        );
        $this->profile = $domain->profile();
        $this->profile_gui = $gui->profile();
        $this->cmd_perm = $gui->cmdPerm($this->perm);
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

    protected function setSettingsSubTabs(string $a_active): void
    {
        $tree = $this->tree;
        $access = $this->access;

        // general properties
        $this->tabs_gui->addSubTab(
            "properties",
            $this->lng->txt("general"),
            $this->ctrl->getLinkTarget($this, 'edit')
        );

        $this->tabs_gui->addSubTab(
            "side_blocks",
            $this->lng->txt("blog_side_blocks"),
            $this->ctrl->getLinkTargetByClass(
                [\ILIAS\Blog\Settings\BlockSettingsGUI::class],
                ""
            )
        );

        $this->tabs_gui->addSubTab(
            "style",
            $this->lng->txt("obj_sty"),
            $this->ctrl->getLinkTargetByClass("ilobjectcontentstylesettingsgui", "")
        );

        // notification settings for blogs in courses and groups
        if ($this->id_type === self::REPOSITORY_NODE_ID) {
            $grp_ref_id = $tree->checkForParentType($this->object->getRefId(), 'grp');
            $crs_ref_id = $tree->checkForParentType($this->object->getRefId(), 'crs');

            if ($grp_ref_id > 0 || $crs_ref_id > 0) {
                if ($access->checkAccess('write', '', $this->ref_id)) {
                    $this->tabs_gui->addSubTab(
                        'notifications',
                        $this->lng->txt("notifications"),
                        $this->ctrl->getLinkTargetByClass("ilobjnotificationsettingsgui", '')
                    );
                }
            }
        }

        $this->tabs_gui->activateSubTab($a_active);
    }

    public function edit(): void
    {
        $this->ctrl->redirectByClass(SettingsGUI::class, "");
    }

    protected function setTabs(): void
    {
        $lng = $this->lng;
        $ilHelp = $this->help;

        if ($this->id_type === self::WORKSPACE_NODE_ID) {
            $this->ctrl->setParameter($this, "wsp_id", $this->node_id);
        }

        $ilHelp->setScreenIdComponent("blog");

        if ($this->perm->mayContribute()) {
            $this->ctrl->setParameterByClass(self::class, "bmn", null);
            $this->tabs_gui->addTab(
                "content",
                $lng->txt("content"),
                $this->ctrl->getLinkTargetByClass(
                    EditingGUI::class,
                    ""
                )
            );
        }
        if ($this->checkPermissionBool("read")) {
            $this->tabs_gui->addTab(
                "id_info",
                $lng->txt("info_short"),
                $this->ctrl->getLinkTargetByClass(array("ilobjbloggui", "ilinfoscreengui"), "showSummary")
            );
        }

        if ($this->checkPermissionBool("write")) {
            $this->tabs_gui->addTab(
                "settings",
                $lng->txt("settings"),
                $this->ctrl->getLinkTargetByClass(
                    [SettingsGUI::class],
                    ""
                )
            );

            if ($this->id_type === self::REPOSITORY_NODE_ID) {
                $this->tabs_gui->addTab(
                    "contributors",
                    $lng->txt("blog_contributors"),
                    $this->ctrl->getLinkTargetByClass(ContributorGUI::class, "contributors")
                );
            }

            if ($this->id_type === self::REPOSITORY_NODE_ID) {
                $mdgui = new ilObjectMetaDataGUI($this->object, null, null, $this->call_by_reference);
                $mdtab = $mdgui->getTab();
                if ($mdtab) {
                    $this->tabs_gui->addTab(
                        "meta_data",
                        $this->lng->txt("meta_data"),
                        $mdtab
                    );
                }
                $this->tabs_gui->addTab(
                    "export",
                    $lng->txt("export"),
                    $this->ctrl->getLinkTargetByClass("ilexportgui", "")
                );
            }
        }

        if ($this->perm->mayContribute()) {
            $this->tabs_gui->addNonTabbedLink(
                "preview",
                $lng->txt("blog_preview"),
                $this->ctrl->getLinkTargetByClass(
                    [
                        self::class,
                        \ILIAS\Blog\Presentation\PresentationGUI::class
                    ],
                    "preview"
                )
            );
        }
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
                $this->setSettingsSubTabs("style");


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
                $this->setSettingsSubTabs("notifications");
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
                $this->setSettingsSubTabs("properties");
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
                    $this->node_id,
                    $this->id_type,
                    $this->perm,
                    $this->month,
                    $this->cs,
                    $this
                );
                $this->cmd_perm->forwardPermitted($this, $gui);
                break;

            case strtolower(\ILIAS\Blog\Presentation\PresentationGUI::class):
                $this->prepareOutput();
                $this->initHeaderAction(null, null, true);
                $gui = $this->gui->presentation()->presentationGUI(
                    $this->perm,
                    $this->content_style_domain,
                    $this->month,
                    $this->object->getOwner(),
                    $this->node_id,
                    $this->id_type,
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
                $this->setSettingsSubTabs("side_blocks");
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

    /**
     * Gather all blog postings
     */
    protected function buildPostingList(
        int $a_obj_id
    ): array {
        $posting_list = $this->domain->postingList($a_obj_id);

        if ($this->author && !$posting_list->hasAuthorPostings($this->author)) {
            $this->author = null;
        }

        return $posting_list->getPostingsGroupedByMonth();
    }

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
        if ($this->blpg > 0) {
            $sub_type = "blp";
            $sub_id = $this->blpg;
        }

        $lg = parent::initHeaderAction($sub_type, $sub_id);
        if (!$lg) {
            return null;
        }
        $lg->enableComments(false);
        $lg->enableNotes(false);
        if (!$presenation) {
            return $lg;
        }
        return $this->gui->navigation()->presentationHeader(
            $this->object,
            $this->perm,
        )->get($lg, $this->blpg);
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
        $ilLocator = $this->locator;

        if (is_object($this->object)) {
            $ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "preview"), "", $this->node_id);
        }
    }


    protected function forwardExport(): void
    {
        $this->ctrl->redirectByClass(ilExportGUI::class);
    }
}
