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
use ILIAS\Blog\Export\BlogHtmlExport;
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
 */
class ilObjBlogGUI extends ilObject2GUI implements ilDesktopItemHandling
{
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
    protected int $apid = 0;
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
        $cs = $DIC->contentStyle();
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
        $this->apid = $req->getApId();
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
            $this->content_style_gui = $cs->gui();
            if (is_object($this->object)) {
                if ($this->id_type !== self::REPOSITORY_NODE_ID) {
                    $this->content_style_domain = $cs->domain()->styleForObjId($this->object->getId());
                } else {
                    $this->content_style_domain = $cs->domain()->styleForRefId($this->object->getRefId());
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
            $this->getAccessHandler(),
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

    public function getItems(): array
    {
        return $this->items;
    }

    protected function afterSave(ilObject $new_object): void
    {
        $ilCtrl = $this->ctrl;

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("object_added"), true);
        $ilCtrl->redirect($this, "");
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
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $lng = $this->lng;
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
                    $this->content_style_domain,
                    $this
                );
                $this->cmd_perm->forwardPermitted($this, $gui);
                break;

            case strtolower(\ILIAS\Blog\Presentation\PresentationGUI::class):
                $this->prepareOutput();
                $this->initHeaderAction(null, null, true);
                $gui = $this->gui->presentation()->presentationGUI(
                    $this,
                    $this->perm,
                    $this->content_style_domain,
                    $this->month,
                    $this->node_id,
                    $this->id_type,
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

    protected function createExportFileWithComments(): void
    {
        $this->buildExportFile(true);
        $this->prepareOutput();
        $this->tabs->activateTab("export");
        $this->ctrl->redirectByClass(ilExportGUI::class, ilExportGUI::CMD_LIST_EXPORT_FILES);
    }

    protected function createExportFile(): void
    {
        $this->buildExportFile();
        $this->prepareOutput();
        $this->tabs->activateTab("export");
        $this->ctrl->redirectByClass(ilExportGUI::class, ilExportGUI::CMD_LIST_EXPORT_FILES);
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


    /**
     * Filter blog postings by month, keyword or author
     */
    public function getListItems(
        bool $a_show_inactive = false
    ): array {
        return $this->getListItemsInternal($a_show_inactive);
    }

    protected function getListItemsInternal(
        bool $a_show_inactive = false
    ): array {
        return $this->domain->postingList($this->obj_id, $this->blog_settings, $a_show_inactive)
            ->getPostingsForView(
                $this->author ?? 0,
                $this->keyword ?? "",
                $this->month ?? ""
            );
    }

    /**
     * Build and deliver export file
     */
    public function export(
        bool $a_with_comments = false
    ): void {
        $export = $this->buildExportFile($a_with_comments);
        ilFileDelivery::deliverFileLegacy($export->getFilePath(), $this->object->getTitle() . ".zip", '', false, false, false);
        $export->delete();
    }


    // --- helper functions

    /**
     * Build fullscreen context
     */
    public function renderFullScreen(
        string $a_content,
        string $a_navigation
    ): void {
        $tpl = $this->tpl;
        $ilUser = $this->user;
        $ilTabs = $this->tabs;
        $ilLocator = $this->locator;

        $owner = $this->object->getOwner();

        $ilTabs->clearTargets();
        $tpl->setLocator();

        $back_caption = "";
        $back = "";

        // back (edit)
        if ($owner === $ilUser->getId()) {
            // from shared/deeplink
            if ($this->id_type === self::WORKSPACE_NODE_ID) {
                $back = "ilias.php?baseClass=ilDashboardGUI&cmd=jumpToWorkspace&wsp_id=" . $this->node_id;
            }
            // from editor (#10073)
            elseif ($this->perm->mayContribute()) {
                $this->ctrl->setParameter($this, "prvm", "");
                if ($this->blpg === 0) {
                    $back = $this->ctrl->getLinkTarget($this, "");
                } else {
                    $this->ctrl->setParameterByClass("ilblogpostinggui", "bmn", $this->month);
                    $this->ctrl->setParameterByClass("ilblogpostinggui", "blpg", $this->blpg);
                    $back = $this->ctrl->getLinkTargetByClass("ilblogpostinggui", "preview");
                }
                //$this->ctrl->setParameter($this, "prvm", $this->prvm);
            }

            $back_caption = $this->lng->txt("blog_back_to_blog_owner");
        }
        // back
        elseif ($ilUser->getId() && $ilUser->getId() !== ANONYMOUS_USER_ID) {
            // workspace (always shared)
            if ($this->id_type === self::WORKSPACE_NODE_ID) {
                $back = "ilias.php?baseClass=ilDashboardGUI&cmd=jumpToWorkspace&dsh=" . $owner;
            }
            // contributor
            elseif ($this->perm->mayContribute()) {
                $back = $this->ctrl->getLinkTarget($this, "");
                $back_caption = $this->lng->txt("blog_back_to_blog_owner");
            }
            // listgui / parent container
            else {
                $tree = $this->tree;
                $parent_id = $tree->getParentId($this->node_id);
                $back = ilLink::_getStaticLink($parent_id);
            }
        }

        $this->renderFullscreenHeader($tpl, $owner);

        // #13564
        $this->ctrl->setParameter($this, "bmn", "");
        //$tpl->setTitleUrl($this->ctrl->getLinkTarget($this, "preview"));
        $this->ctrl->setParameter($this, "bmn", $this->month);

        $this->setContentStyleSheet();

        // content
        $tpl->setContent($a_content);
        $tpl->setRightContent($a_navigation);
    }

    public function renderFullscreenHeader(
        ilGlobalTemplateInterface $a_tpl,
        int $a_user_id,
        bool $a_export = false
    ): void {
        $ilUser = $this->user;

        if (!$a_export) {
            ilChangeEvent::_recordReadEvent(
                $this->object->getType(),
                $this->node_id,
                $this->object->getId(),
                $ilUser->getId()
            );
        }

        // repository blogs are multi-author
        $name = "";
        if ($this->id_type !== self::REPOSITORY_NODE_ID) {
            $name = ilObjUser::_lookupName($a_user_id);
            $name = $name["lastname"] . ", " . $name["firstname"];
        }

        $ppic = "";
        if ($this->blog_settings?->getProfilePicture() && !$a_export) {
            // repository (multi-user)
            if ($this->id_type === self::REPOSITORY_NODE_ID) {
                // #15030
                if ($this->blpg > 0 && !$a_export) {
                    $post = new ilBlogPosting($this->blpg);
                    $author_id = $post->getAuthor();
                    if ($author_id) {
                        $ppic = $this->profile_gui->getPicturePath($author_id);
                        $name = $this->profile_gui->getNamePresentation($author_id);
                        //$name = $name["lastname"] . ", " . $name["firstname"];
                    }
                }
            }
            // workspace (author == owner)
            else {
                $ppic = ilObjUser::_getPersonalPicturePath($a_user_id, "xsmall", true);
                if ($a_export) {
                    $ppic = basename($ppic);
                }
            }
        } else {
            $ppic = ilUtil::getImagePath("standard/icon_blog.svg");
        }
        $a_tpl->resetHeaderBlock(false);
        $a_tpl->setTitleIcon($ppic);
        $a_tpl->setTitle($this->object->getTitle());
        if ($this->id_type === self::REPOSITORY_NODE_ID) {
            $a_tpl->setDescription($this->object->getDescription());
        } else {
            $a_tpl->setDescription($name);
        }
    }

    /**
     * Gather all blog postings
     */
    protected function buildPostingList(
        int $a_obj_id
    ): array {
        $posting_list = $this->domain->postingList($a_obj_id, $this->blog_settings);

        if ($this->author && !$posting_list->hasAuthorPostings($this->author)) {
            $this->author = null;
        }

        return $posting_list->getPostingsGroupedByMonth();
    }

    /**
     * Build posting month list
     */
    public function renderList(
        array $items,
        string $a_cmd = "preview",
        string $a_link_template = "",
        bool $a_show_inactive = false,
        string $a_export_directory = ""
    ): string {
        return $this->gui->posting()->postingList(
            $this,
            $this->perm,
            $this->month,
            $this->node_id,
            $this->id_type,
        )->render(
            $items,
            $a_cmd,
            $a_link_template,
            $a_show_inactive,
            $a_export_directory
        );
    }

    public function buildExportLink(
        string $a_template,
        string $a_type,
        string $a_id
    ): string {
        return $this->buildExportLinkInternal($a_template, $a_type, $a_id);
    }

    protected function buildExportLinkInternal(
        string $a_template,
        string $a_type,
        string $a_id
    ): string {
        $blog_export = new BlogHtmlExport(
            $this,
            $this->id_type === self::REPOSITORY_NODE_ID,
            "",
            ""
        );
        return $blog_export->buildExportLink($a_template, $a_type, $a_id, $this->getKeywords(false));
    }

    /**
     * Get keywords for single posting or complete blog
     */
    public function getKeywords(
        bool $a_show_inactive,
        ?int $a_posting_id = null
    ): array {
        $keywords = array();
        if ($a_posting_id) {
            foreach ($this->posting_manager->getKeywords($this->obj_id, $a_posting_id) as $keyword) {
                if (isset($keywords[$keyword])) {
                    $keywords[$keyword]++;
                } else {
                    $keywords[$keyword] = 1;
                }
            }
        } else {
            foreach ($this->items as $month => $items) {
                foreach ($items as $item) {
                    /** @var \ILIAS\Blog\Posting\Posting $item */
                    $item_id = $item->getId();
                    if ($a_show_inactive || ilBlogPosting::_lookupActive($item_id, "blp")) {
                        foreach ($this->posting_manager->getKeywords($this->obj_id, $item_id) as $keyword) {
                            if (isset($keywords[$keyword])) {
                                $keywords[$keyword]++;
                            } else {
                                $keywords[$keyword] = 1;
                            }
                        }
                    }
                }
            }
        }

        // #15881
        $tmp = array();
        foreach ($keywords as $keyword => $counter) {
            $tmp[] = array("keyword" => $keyword, "counter" => $counter);
        }
        $tmp = ilArrayUtil::sortArray($tmp, "keyword", "ASC");

        $keywords = array();
        foreach ($tmp as $item) {
            $keywords[(string) $item["keyword"]] = $item["counter"];
        }
        return $keywords;
    }

    /**
     * Build export file
     */
    public function buildExportFile(
        bool $a_include_comments = false,
        bool $print_version = false
    ): BlogHtmlExport {
        $type = "html";
        $format = explode("_", $this->blog_request->getFormat());
        if (($format[1] ?? "") === "comments" || $a_include_comments) {
            $a_include_comments = true;
            $type = "html_comments";
        }

        // create export file
        //ilExport::_createExportDirectory($this->object->getId(), $type, "blog");
        //$exp_dir = ilExport::_getExportDirectory($this->object->getId(), $type, "blog");

        $subdir = $this->object->getType() . "_" . $this->object->getId();
        if ($print_version) {
            $subdir .= "print";
        }

        $blog_export = new BlogHtmlExport(
            $this,
            $this->id_type === self::REPOSITORY_NODE_ID,
            "",
            $subdir
        );
        $blog_export->setPrintVersion($print_version);
        $blog_export->includeComments($a_include_comments);
        $blog_export->exportHTML();
        return $blog_export;
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

    /**
     * Filter inactive items from items list
     */
    public function checkPermissionBool(
        string $perm,
        string $cmd = "",
        string $type = "",
        ?int $ref_id = null
    ): bool {
        return parent::checkPermissionBool($perm, $cmd, $type, $ref_id);
    }

    public function filterInactivePostings(): void
    {
        foreach ($this->items as $month => $postings) {
            foreach ($postings as $id => $item) {
                if (!ilBlogPosting::_lookupActive($id, "blp")) {
                    unset($this->items[$month][$id]);
                } elseif ($this->blog_settings->getApproval() && !$item->isApproved()) {
                    unset($this->items[$month][$id]);
                }
            }
            if (!count($this->items[$month])) {
                unset($this->items[$month]);
            }
        }

        if ($this->items && !isset($this->items[$this->month])) {
            $keys = array_keys($this->items);
            $this->month = array_shift($keys);
        }
    }

    public function filterItemsByKeyword(
        array $a_items,
        string $a_keyword
    ): array {
        $res = [];
        foreach ($a_items as $month => $items) {
            foreach ($items as $item) {
                if (in_array(
                    $a_keyword,
                    $this->posting_manager->getKeywords($this->obj_id, $item->getId())
                )) {
                    $res[] = $item;
                }
            }
        }
        return $res;
    }

    protected function addLocatorItems(): void
    {
        $ilLocator = $this->locator;

        if (is_object($this->object)) {
            $ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "preview"), "", $this->node_id);
        }
    }

    public function approve(): void
    {
        if ($this->perm->canManage() && $this->apid > 0) {
            $post = new ilBlogPosting($this->apid);
            $post->setApproved(true);
            $post->setBlogNodeId($this->node_id, ($this->id_type == self::WORKSPACE_NODE_ID));
            $post->update(true, false, true, "new"); // #13434

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
        }

        $this->ctrl->redirectByClass(
            [
                ilObjBlogGUI::class,
                EditingGUI::class,
            ],
            ""
        );
    }


    public function deactivateAdmin(): void
    {
        if ($this->checkPermissionBool("write") && $this->apid > 0) {
            // ilBlogPostingGUI::deactivatePage()
            $post = new ilBlogPosting($this->apid);
            $post->setApproved(false);
            $post->setActive(false);
            $post->update(true, false, false);

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
        }

        $this->ctrl->redirect($this, "render");
    }


    ////
    //// Style related functions
    ////

    public function setContentStyleSheet(
        ?ilGlobalTemplateInterface $a_tpl = null
    ): void {
        $tpl = $this->tpl;

        if ($a_tpl) {
            $ctpl = $a_tpl;
        } else {
            $ctpl = $tpl;
        }

        $this->content_style_gui->addCss(
            $ctpl,
            $this->object->getRefId(),
            $this->object->getId()
        );
    }

    /**
     * Handle export choice
     */
    protected function exportWithComments(): void
    {
        $this->export(true);
    }

    ////
    //// Print
    ////

    public function getPrintView(): \ILIAS\Export\PrintProcessGUI
    {
        $style_sheet_id = $this->content_style_domain->getEffectiveStyleId();

        /** @var ilObjBlog $blog */
        $blog = $this->object;
        $provider = new \ILIAS\Blog\BlogPrintViewProviderGUI(
            $this->lng,
            $this->ctrl,
            $blog,
            $this->node_id,
            $this->access_handler,
            $style_sheet_id,
            $this->blog_request->getObjIds()
        );

        return new \ILIAS\Export\PrintProcessGUI(
            $provider,
            $this->http,
            $this->ui,
            $this->lng
        );
    }

    public function printViewSelection(): void
    {
        $view = $this->getPrintView();
        $view->sendForm();
    }

    public function printPostings(): void
    {
        $print_view = $this->getPrintView();
        $print_view->sendPrintView();
    }

    protected function forwardExport(): void
    {
        $this->ctrl->redirectByClass(ilExportGUI::class);
    }
}
