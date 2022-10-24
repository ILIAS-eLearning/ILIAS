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

use ILIAS\Wiki\Export;
use ILIAS\GlobalScreen\ScreenContext\ContextServices;
use ILIAS\Wiki\Editing\EditingGUIRequest;

/**
 * @author Alexander Killing <killing@leifos.de>
 *
 * @ilCtrl_Calls ilObjWikiGUI: ilPermissionGUI, ilInfoScreenGUI, ilWikiPageGUI
 * @ilCtrl_IsCalledBy ilObjWikiGUI: ilRepositoryGUI, ilAdministrationGUI
 * @ilCtrl_Calls ilObjWikiGUI: ilPublicUserProfileGUI, ilObjectContentStyleSettingsGUI
 * @ilCtrl_Calls ilObjWikiGUI: ilExportGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjWikiGUI: ilRatingGUI, ilWikiPageTemplateGUI, ilWikiStatGUI
 * @ilCtrl_Calls ilObjWikiGUI: ilObjectMetaDataGUI
 * @ilCtrl_Calls ilObjWikiGUI: ilSettingsPermissionGUI
 * @ilCtrl_Calls ilObjWikiGUI: ilRepositoryObjectSearchGUI, ilObjectCopyGUI, ilObjNotificationSettingsGUI
 * @ilCtrl_Calls ilObjWikiGUI: ilLTIProviderObjectSettingGUI
 */
class ilObjWikiGUI extends ilObjectGUI
{
    protected \ILIAS\HTTP\Services $http;
    protected string $requested_page;
    protected ilPropertyFormGUI $form_gui;
    protected ilTabsGUI $tabs;
    protected ilHelpGUI $help;
    protected ilLogger $log;
    protected ContextServices $tool_context;
    protected \ILIAS\DI\UIServices $ui;
    protected bool $req_with_comments = false;
    protected EditingGUIRequest $edit_request;
    protected \ILIAS\Style\Content\GUIService $content_style_gui;
    protected \ILIAS\Style\Content\Object\ObjectFacade $content_style_domain;

    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference,
        bool $a_prepare_output = true
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->tabs = $DIC->tabs();
        $this->help = $DIC->help();
        $this->locator = $DIC["ilLocator"];
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $this->http = $DIC->http();

        $this->type = "wiki";

        $this->log = ilLoggerFactory::getLogger('wiki');

        $this->tool_context = $DIC->globalScreen()->tool()->context();
        $this->ui = $DIC->ui();

        $this->edit_request = $DIC
            ->wiki()
            ->internal()
            ->gui()
            ->editing()
            ->request();

        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
        $lng->loadLanguageModule("obj");
        $lng->loadLanguageModule("wiki");

        $this->requested_page = $this->edit_request->getPage();
        if ($this->requested_page !== "") {
            $ilCtrl->setParameter($this, "page", ilWikiUtil::makeUrlTitle($this->requested_page));
        }

        $this->req_with_comments = $this->edit_request->getWithComments();
        $cs = $DIC->contentStyle();
        $this->content_style_gui = $cs->gui();
        if (is_object($this->object)) {
            $this->content_style_domain = $cs->domain()->styleForRefId($this->object->getRefId());
        }
    }

    public function executeCommand(): void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $ilAccess = $this->access;

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->triggerAssignmentTool();

        $this->prepareOutput();

        // see ilWikiPageGUI::printViewOrderList()
        // printView() cannot be in ilWikiPageGUI because of stylesheet confusion
        if ($cmd === "printView") {
            $next_class = null;
        }

        switch ($next_class) {
            case "ilinfoscreengui":
                $this->checkPermission("visible");
                $this->addHeaderAction();
                $this->infoScreen();	// forwards command
                break;

            case 'ilpermissiongui':
                $this->addHeaderAction();
                $ilTabs->activateTab("perm_settings");
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case 'ilsettingspermissiongui':
                $this->checkPermission("write");
                $this->addHeaderAction();
                $ilTabs->activateTab("settings");
                $this->setSettingsSubTabs("permission_settings");
                $perm_gui = new ilSettingsPermissionGUI($this);
                $perm_gui->setPermissions(array("edit_wiki_navigation", "delete_wiki_pages", "activate_wiki_protection",
                    "wiki_html_export"));
                $perm_gui->setRoleRequiredPermissions(array("edit_content"));
                $perm_gui->setRoleProhibitedPermissions(array("write"));
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case 'ilwikipagegui':
                $this->checkPermission("read");
                $wpage_gui = ilWikiPageGUI::getGUIForTitle(
                    $this->object->getId(),
                    ilWikiUtil::makeDbTitle($this->requested_page),
                    $this->edit_request->getOldNr(),
                    $this->object->getRefId()
                );
                $wpage_gui->setStyleId($this->content_style_domain->getEffectiveStyleId());
                $this->setContentStyleSheet();
                if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()) &&
                    (
                        !$ilAccess->checkAccess("edit_content", "", $this->object->getRefId()) ||
                        $wpage_gui->getPageObject()->getBlocked()
                    )) {
                    $wpage_gui->setEnableEditing(false);
                }

                // alter title and description
//				$tpl->setTitle($wpage_gui->getPageObject()->getTitle());
//				$tpl->setDescription($this->object->getTitle());
                if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
                    $wpage_gui->activateMetaDataEditor($this->object, "wpg", $wpage_gui->getId());
                }

                $ret = $this->ctrl->forwardCommand($wpage_gui);
                if ($ret != "") {
                    $tpl->setContent($ret);
                }
                break;

            case 'ilobjectcopygui':
                $cp = new ilObjectCopyGUI($this);
                $cp->setType('wiki');
                $this->ctrl->forwardCommand($cp);
                break;

            case 'ilpublicuserprofilegui':
                $profile_gui = new ilPublicUserProfileGUI(
                    $this->edit_request->getUserId()
                );
                $ret = $this->ctrl->forwardCommand($profile_gui);
                $tpl->setContent($ret);
                break;

            case "ilobjectcontentstylesettingsgui":
                $this->checkPermission("write");
                $this->addHeaderAction();
                $ilTabs->activateTab("settings");
                $this->setSettingsSubTabs("style");

                $settings_gui = $this->content_style_gui
                    ->objectSettingsGUIForRefId(
                        null,
                        $this->object->getRefId()
                    );
                $this->ctrl->forwardCommand($settings_gui);
                break;

            case "ilexportgui":
                $this->addHeaderAction();
                $ilTabs->activateTab("export");
                $exp_gui = new ilExportGUI($this);
                $exp_gui->addFormat("xml");
                $exp_gui->addFormat("html", "", $this, "exportHTML");
                if ($this->object->isCommentsExportPossible()) {
                    $exp_gui->addFormat("html_comments", "HTML (" . $this->lng->txt("wiki_incl_comments") . ")", $this, "exportHTML");
                }
                $this->ctrl->forwardCommand($exp_gui);
                break;

            case "ilcommonactiondispatchergui":
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;

            case "ilratinggui":
                // for rating category editing
                $this->checkPermission("write");
                $this->addHeaderAction();
                $ilTabs->activateTab("settings");
                $this->setSettingsSubTabs("rating_categories");
                $gui = new ilRatingGUI();
                $gui->setObject($this->object->getId(), $this->object->getType());
                $gui->setExportCallback(array($this, "getSubObjectTitle"), $this->lng->txt("page"));
                $this->ctrl->forwardCommand($gui);
                break;

            case "ilwikistatgui":
                $this->checkPermission("statistics_read");

                $this->addHeaderAction();
                $ilTabs->activateTab("statistics");
                $gui = new ilWikiStatGUI($this->object->getId());
                $this->ctrl->forwardCommand($gui);
                break;

            case "ilwikipagetemplategui":
                $this->checkPermission("write");
                $this->addHeaderAction();
                $ilTabs->activateTab("settings");
                $this->setSettingsSubTabs("page_templates");
                $wptgui = new ilWikiPageTemplateGUI($this);
                $this->ctrl->forwardCommand($wptgui);
                break;

            case 'ilobjectmetadatagui':
                $this->checkPermission("write");
                $this->addHeaderAction();
                $ilTabs->activateTab("advmd");
                $md_gui = new ilObjectMetaDataGUI($this->object, "wpg");
                $this->ctrl->forwardCommand($md_gui);
                break;

            case 'ilrepositoryobjectsearchgui':
                $this->addHeaderAction();
                $this->setSideBlock();
                $ilTabs->setTabActive("wiki_search_results");
                $ilCtrl->setReturn($this, 'view');
                $search_gui = new ilRepositoryObjectSearchGUI(
                    $this->object->getRefId(),
                    $this,
                    'view'
                );
                $ilCtrl->forwardCommand($search_gui);
                break;

            case 'ilobjnotificationsettingsgui':
                $this->addHeaderAction();
                $ilTabs->activateTab("settings");
                $this->setSettingsSubTabs("notifications");
                $gui = new ilObjNotificationSettingsGUI($this->object->getRefId());
                $this->ctrl->forwardCommand($gui);
                break;

            case 'illtiproviderobjectsettinggui':
                $this->addHeaderAction();
                $ilTabs->activateTab("settings");
                $this->setSettingsSubTabs("lti_provider");
                $lti_gui = new ilLTIProviderObjectSettingGUI($this->object->getRefId());
                $lti_gui->setCustomRolesForSelection($GLOBALS['DIC']->rbac()->review()->getLocalRoles($this->object->getRefId()));
                $lti_gui->offerLTIRolesForSelection(false);
                $this->ctrl->forwardCommand($lti_gui);
                break;

            default:
                $this->addHeaderAction();
                if (!$cmd) {
                    $cmd = "infoScreen";
                }
                $cmd .= "Object";
                if ($cmd !== "cancelObject") {
                    if ($cmd !== "infoScreenObject") {
                        if (!in_array($cmd, array("createObject", "saveObject", "importFileObject"))) {
                            $this->checkPermission("read");
                        }
                    } else {
                        $this->checkPermission("visible");
                    }
                }
                $this->$cmd();
                break;
        }
    }

    public function viewObject(): void
    {
        $this->checkPermission("read");
        $this->gotoStartPageObject();
    }

    protected function initCreationForms(string $new_type): array
    {
        $this->initSettingsForm("create");
        $this->getSettingsFormValues("create");

        $forms = array(self::CFORM_NEW => $this->form_gui,
                self::CFORM_IMPORT => $this->initImportForm($new_type),
                self::CFORM_CLONE => $this->fillCloneTemplate(null, $new_type));

        return $forms;
    }

    public function saveObject(): void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;

        if (!$this->checkPermissionBool("create", "", "wiki", $this->requested_ref_id)) {
            throw new ilPermissionException($this->lng->txt("permission_denied"));
        }

        $this->initSettingsForm("create");
        if ($this->form_gui->checkInput()) {
            if (!ilObjWiki::checkShortTitleAvailability($this->form_gui->getInput("shorttitle"))) {
                $short_item = $this->form_gui->getItemByPostVar("shorttitle");
                $short_item->setAlert($lng->txt("wiki_short_title_already_in_use"));
            } else {
                parent::saveObject();
                return;
            }
        }

        $this->form_gui->setValuesByPost();
        $tpl->setContent($this->form_gui->getHTML());
    }

    protected function afterSave(ilObject $new_object): void
    {
        $ilSetting = $this->settings;

        $new_object->setTitle($this->form_gui->getInput("title"));
        $new_object->setDescription($this->form_gui->getInput("description"));
        $new_object->setIntroduction($this->form_gui->getInput("intro"));
        $new_object->setStartPage($this->form_gui->getInput("startpage"));
        $new_object->setShortTitle((string) $this->form_gui->getInput("shorttitle"));
        $new_object->setRating($this->form_gui->getInput("rating"));
        // $new_object->setRatingAsBlock($this->form_gui->getInput("rating_side"));
        $new_object->setRatingForNewPages($this->form_gui->getInput("rating_new"));
        $new_object->setRatingCategories($this->form_gui->getInput("rating_ext"));

        $new_object->setRatingOverall($this->form_gui->getInput("rating_overall"));
        $new_object->setPageToc($this->form_gui->getInput("page_toc"));



        if (!$ilSetting->get("disable_comments")) {
            $new_object->setPublicNotes($this->form_gui->getInput("public_notes"));
        }
        $new_object->setOnline($this->form_gui->getInput("online"));
        $new_object->update();

        // always send a message
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("object_added"), true);
        ilUtil::redirect(self::getGotoLink($new_object->getRefId()));
    }

    /**
     * this one is called from the info button in the repository
     * @throws ilObjectException
     * @throws ilPermissionException
     */
    public function infoScreenObject(): void
    {
        $this->checkPermission("visible");
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->infoScreen();
    }

    public function infoScreen(): void
    {
        $ilAccess = $this->access;
        $ilUser = $this->user;
        $ilTabs = $this->tabs;
        $lng = $this->lng;

        $ilTabs->activateTab("info_short");

        if (!$ilAccess->checkAccess("visible", "", $this->object->getRefId())) {
            throw new ilPermissionException($this->lng->txt("permission_denied"));
        }

        $info = new ilInfoScreenGUI($this);
        $info->enablePrivateNotes();
        if (trim($this->object->getIntroduction()) !== "") {
            $info->addSection($lng->txt("wiki_introduction"));
            $info->addProperty("", nl2br($this->object->getIntroduction()));
        }

        // feedback from tutor; mark, status, comment
        $lpcomment = ilLPMarks::_lookupComment($ilUser->getId(), $this->object->getId());
        $mark = ilLPMarks::_lookupMark($ilUser->getId(), $this->object->getId());
        $status = ilWikiContributor::_lookupStatus($this->object->getId(), $ilUser->getId());
        if ($lpcomment !== "" || $mark !== "" || (int) $status !== ilWikiContributor::STATUS_NOT_GRADED) {
            $info->addSection($this->lng->txt("wiki_feedback_from_tutor"));
            if ($lpcomment !== "") {
                $info->addProperty(
                    $this->lng->txt("wiki_comment"),
                    $lpcomment
                );
            }
            if ($mark !== "") {
                $info->addProperty(
                    $this->lng->txt("wiki_mark"),
                    $mark
                );
            }

            if ((int) $status === ilWikiContributor::STATUS_PASSED) {
                $info->addProperty(
                    $this->lng->txt("status"),
                    $this->lng->txt("wiki_passed")
                );
            }
            if ((int) $status === ilWikiContributor::STATUS_FAILED) {
                $info->addProperty(
                    $this->lng->txt("status"),
                    $this->lng->txt("wiki_failed")
                );
            }
        }

        if ($ilAccess->checkAccess("read", "", $this->object->getRefId())) {
            $info->addButton($lng->txt("wiki_start_page"), self::getGotoLink($this->object->getRefId()));
        }

        // general information
        $this->lng->loadLanguageModule("meta");
        $this->lng->loadLanguageModule("wiki");

        // forward the command
        $this->ctrl->forwardCommand($info);
    }

    public function gotoStartPageObject(): void
    {
        ilUtil::redirect(self::getGotoLink($this->object->getRefId()));
    }

    public function addPageTabs(): void
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter(
            $this,
            "wpg_id",
            ilWikiPage::getPageIdForTitle($this->object->getId(), ilWikiUtil::makeDbTitle($this->requested_page))
        );
        $ilCtrl->setParameter($this, "page", ilWikiUtil::makeUrlTitle($this->requested_page));
        $ilTabs->addTarget(
            "wiki_what_links_here",
            $this->ctrl->getLinkTargetByClass(
                "ilwikipagegui",
                "whatLinksHere"
            ),
            "whatLinksHere"
        );
        $ilTabs->addTarget(
            "wiki_print_view",
            $this->ctrl->getLinkTargetByClass(
                "ilwikipagegui",
                "printViewSelection"
            ),
            "printViewSelection"
        );
    }

    public function addPagesSubTabs(): void
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;

        $ilTabs->activateTab("wiki_pages");

        $ilCtrl->setParameter(
            $this,
            "wpg_id",
            ilWikiPage::getPageIdForTitle(
                $this->object->getId(),
                ilWikiUtil::makeDbTitle($this->requested_page)
            )
        );
        $ilCtrl->setParameter($this, "page", ilWikiUtil::makeUrlTitle($this->requested_page));
        $ilTabs->addSubTabTarget(
            "wiki_all_pages",
            $this->ctrl->getLinkTarget($this, "allPages"),
            "allPages"
        );
        $ilTabs->addSubTabTarget(
            "wiki_recent_changes",
            $this->ctrl->getLinkTarget($this, "recentChanges"),
            "recentChanges"
        );
        $ilTabs->addSubTabTarget(
            "wiki_new_pages",
            $this->ctrl->getLinkTarget($this, "newPages"),
            "newPages"
        );
        $ilTabs->addSubTabTarget(
            "wiki_popular_pages",
            $this->ctrl->getLinkTarget($this, "popularPages"),
            "popularPages"
        );
        $ilTabs->addSubTabTarget(
            "wiki_orphaned_pages",
            $this->ctrl->getLinkTarget($this, "orphanedPages"),
            "orphanedPages"
        );
    }

    protected function getTabs(): void
    {
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        $lng = $this->lng;
        $ilHelp = $this->help;

        $ilHelp->setScreenIdComponent("wiki");

        // wiki tabs
        if (in_array(strtolower($ilCtrl->getCmdClass()), array("", "ilobjectcontentstylesettingsgui", "ilobjwikigui",
            "ilinfoscreengui", "ilpermissiongui", "ilexportgui", "ilratingcategorygui", "ilobjnotificationsettingsgui", "iltaxmdgui",
            "ilwikistatgui", "ilwikipagetemplategui", "iladvancedmdsettingsgui", "ilsettingspermissiongui", 'ilrepositoryobjectsearchgui'
            ), true) || $ilCtrl->getNextClass() === "ilpermissiongui") {
            if ($this->requested_page !== "") {
                $this->tabs_gui->setBackTarget(
                    $lng->txt("wiki_last_visited_page"),
                    self::getGotoLink(
                        $this->requested_ref_id,
                        ilWikiUtil::makeDbTitle($this->requested_page)
                    )
                );
            }

            // pages
            if ($ilAccess->checkAccess('read', "", $this->object->getRefId())) {
                $this->tabs_gui->addTab(
                    "wiki_pages",
                    $lng->txt("wiki_pages"),
                    $this->ctrl->getLinkTarget($this, "allPages")
                );
            }

            // info screen
            if ($ilAccess->checkAccess('visible', "", $this->object->getRefId())) {
                $this->tabs_gui->addTab(
                    "info_short",
                    $lng->txt("info_short"),
                    $this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary")
                );
            }

            // settings
            if ($ilAccess->checkAccess('write', "", $this->object->getRefId())) {
                $this->tabs_gui->addTab(
                    "settings",
                    $lng->txt("settings"),
                    $this->ctrl->getLinkTarget($this, "editSettings")
                );

                // metadata
                $mdgui = new ilObjectMetaDataGUI($this->object, "wpg");
                $mdtab = $mdgui->getTab();
                if ($mdtab) {
                    $this->tabs_gui->addTab(
                        "advmd",
                        $this->lng->txt("meta_data"),
                        $mdtab
                    );
                }
            }

            // contributors
            if ($ilAccess->checkAccess('write', "", $this->object->getRefId())) {
                $this->tabs_gui->addTab(
                    "wiki_contributors",
                    $lng->txt("wiki_contributors"),
                    $this->ctrl->getLinkTarget($this, "listContributors")
                );
            }

            // statistics
            if ($ilAccess->checkAccess('statistics_read', "", $this->object->getRefId())) {
                $this->tabs_gui->addTab(
                    "statistics",
                    $lng->txt("statistics"),
                    $this->ctrl->getLinkTargetByClass("ilWikiStatGUI", "initial")
                );
            }

            if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
                $this->tabs_gui->addTab(
                    "export",
                    $lng->txt("export"),
                    $this->ctrl->getLinkTargetByClass("ilexportgui", "")
                );
            }

            // edit permissions
            if ($ilAccess->checkAccess('edit_permission', "", $this->object->getRefId())) {
                $this->tabs_gui->addTab(
                    "perm_settings",
                    $lng->txt("perm_settings"),
                    $this->ctrl->getLinkTargetByClass("ilpermissiongui", "perm")
                );
            }
        }
    }

    public function setSettingsSubTabs(string $a_active): void
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilAccess = $this->access;

        if (in_array(
            $a_active,
            array("general_settings", "style", "imp_pages", "rating_categories",
            "page_templates", "advmd", "permission_settings", "notifications", "lti_provider")
        )) {
            if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
                // general properties
                $ilTabs->addSubTab(
                    "general_settings",
                    $lng->txt("wiki_general_settings"),
                    $ilCtrl->getLinkTarget($this, 'editSettings')
                );

                // permission settings
                $ilTabs->addSubTab(
                    "permission_settings",
                    $lng->txt("obj_permission_settings"),
                    $this->ctrl->getLinkTargetByClass("ilsettingspermissiongui", "")
                );

                // style properties
                $ilTabs->addSubTab(
                    "style",
                    $lng->txt("wiki_style"),
                    $ilCtrl->getLinkTargetByClass("ilObjectContentStyleSettingsGUI", "")
                );
            }

            if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
                // important pages
                $ilTabs->addSubTab(
                    "imp_pages",
                    $lng->txt("wiki_navigation"),
                    $ilCtrl->getLinkTarget($this, 'editImportantPages')
                );
            }

            if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
                // page templates
                $ilTabs->addSubTab(
                    "page_templates",
                    $lng->txt("wiki_page_templates"),
                    $ilCtrl->getLinkTargetByClass("ilwikipagetemplategui", "")
                );

                // rating categories
                if ($this->object->getRating() && $this->object->getRatingCategories()) {
                    $lng->loadLanguageModule("rating");
                    $ilTabs->addSubTab(
                        "rating_categories",
                        $lng->txt("rating_categories"),
                        $ilCtrl->getLinkTargetByClass(array('ilratinggui', 'ilratingcategorygui'), '')
                    );
                }

                $ilTabs->addSubTab(
                    'notifications',
                    $lng->txt("notifications"),
                    $ilCtrl->getLinkTargetByClass("ilobjnotificationsettingsgui", '')
                );
            }

            // LTI Provider
            $lti_settings = new ilLTIProviderObjectSettingGUI($this->object->getRefId());
            if ($lti_settings->hasSettingsAccess()) {
                $ilTabs->addSubTabTarget(
                    'lti_provider',
                    $this->ctrl->getLinkTargetByClass(ilLTIProviderObjectSettingGUI::class)
                );
            }

            $ilTabs->activateSubTab($a_active);
        }
    }

    public function editSettingsObject(): void
    {
        $tpl = $this->tpl;

        $this->checkPermission("write");

        $this->setSettingsSubTabs("general_settings");

        $this->initSettingsForm();
        $this->getSettingsFormValues();

        // Edit ecs export settings
        $ecs = new ilECSWikiSettings($this->object);
        $ecs->addSettingsToForm($this->form_gui, 'wiki');

        $tpl->setContent($this->form_gui->getHTML());
        $this->setSideBlock();
    }

    public function initSettingsForm(string $a_mode = "edit"): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilTabs = $this->tabs;
        $ilSetting = $this->settings;
        $obj_service = $this->object_service;

        $lng->loadLanguageModule("wiki");
        $ilTabs->activateTab("settings");

        $this->form_gui = new ilPropertyFormGUI();

        // Title
        $tit = new ilTextInputGUI($lng->txt("title"), "title");
        $tit->setRequired(true);
        $this->form_gui->addItem($tit);

        // Description
        $des = new ilTextAreaInputGUI($lng->txt("description"), "description");
        $this->form_gui->addItem($des);

        // Introduction
        $intro = new ilTextAreaInputGUI($lng->txt("wiki_introduction"), "intro");
        $intro->setCols(40);
        $intro->setRows(4);
        $this->form_gui->addItem($intro);

        // Start Page
        $options = [];
        if ($a_mode === "edit") {
            $pages = ilWikiPage::getAllWikiPages($this->object->getId());
            foreach ($pages as $p) {
                $options[$p["id"]] = ilStr::shortenTextExtended($p["title"], 60, true);
            }
            $si = new ilSelectInputGUI($lng->txt("wiki_start_page"), "startpage_id");
            $si->setOptions($options);
            $this->form_gui->addItem($si);
        } else {
            $sp = new ilTextInputGUI($lng->txt("wiki_start_page"), "startpage");
            if ($a_mode === "edit") {
                $sp->setInfo($lng->txt("wiki_start_page_info"));
            }
            $sp->setMaxLength(200);
            $sp->setRequired(true);
            $this->form_gui->addItem($sp);
        }

        // Online
        $online = new ilCheckboxInputGUI($lng->txt("online"), "online");
        $this->form_gui->addItem($online);


        // rating

        $lng->loadLanguageModule('rating');
        $rate = new ilCheckboxInputGUI($lng->txt('rating_activate_rating'), 'rating_overall');
        $rate->setInfo($lng->txt('rating_activate_rating_info'));
        $this->form_gui->addItem($rate);

        $rating = new ilCheckboxInputGUI($lng->txt("wiki_activate_rating"), "rating");
        $this->form_gui->addItem($rating);

        /* always active
        $side = new ilCheckboxInputGUI($lng->txt("wiki_activate_sideblock_rating"), "rating_side");
        $rating->addSubItem($side);
        */

        $new = new ilCheckboxInputGUI($lng->txt("wiki_activate_new_page_rating"), "rating_new");
        $rating->addSubItem($new);

        $extended = new ilCheckboxInputGUI($lng->txt("wiki_activate_extended_rating"), "rating_ext");
        $rating->addSubItem($extended);


        // public comments
        if (!$ilSetting->get("disable_comments")) {
            $comments = new ilCheckboxInputGUI($lng->txt("wiki_public_comments"), "public_notes");
            $this->form_gui->addItem($comments);
        }

        // important pages
        //		$imp_pages = new ilCheckboxInputGUI($lng->txt("wiki_important_pages"), "imp_pages");
        //		$this->form_gui->addItem($imp_pages);

        // page toc
        $page_toc = new ilCheckboxInputGUI($lng->txt("wiki_page_toc"), "page_toc");
        $page_toc->setInfo($lng->txt("wiki_page_toc_info"));
        $this->form_gui->addItem($page_toc);

        if ($a_mode === "edit") {
            // advanced metadata auto-linking
            if (count(ilAdvancedMDRecord::_getSelectedRecordsByObject("wiki", $this->object->getRefId(), "wpg")) > 0) {
                $link_md = new ilCheckboxInputGUI($lng->txt("wiki_link_md_values"), "link_md_values");
                $link_md->setInfo($lng->txt("wiki_link_md_values_info"));
                $this->form_gui->addItem($link_md);
            }


            $section = new ilFormSectionHeaderGUI();
            $section->setTitle($this->lng->txt('obj_presentation'));
            $this->form_gui->addItem($section);

            // tile image
            $obj_service->commonSettings()->legacyForm($this->form_gui, $this->object)->addTileImage();


            // additional features
            $feat = new ilFormSectionHeaderGUI();
            $feat->setTitle($this->lng->txt('obj_features'));
            $this->form_gui->addItem($feat);

            ilObjectServiceSettingsGUI::initServiceSettingsForm(
                $this->object->getId(),
                $this->form_gui,
                array(
                        ilObjectServiceSettingsGUI::CUSTOM_METADATA
                    )
            );
        }

        // :TODO: sorting

        // Form action and save button
        $this->form_gui->setTitleIcon(ilUtil::getImagePath("icon_wiki.svg"));
        if ($a_mode !== "create") {
            $this->form_gui->setTitle($lng->txt("wiki_settings"));
            $this->form_gui->addCommandButton("saveSettings", $lng->txt("save"));
        } else {
            $this->form_gui->setTitle($lng->txt("wiki_new"));
            $this->form_gui->addCommandButton("save", $lng->txt("wiki_add"));
            $this->form_gui->addCommandButton("cancel", $lng->txt("cancel"));
        }

        // set values
        if ($a_mode === "create") {
            $ilCtrl->setParameter($this, "new_type", "wiki");
        }

        $this->form_gui->setFormAction($ilCtrl->getFormAction($this, "saveSettings"));
    }

    public function getSettingsFormValues(string $a_mode = "edit"): void
    {
        // set values
        if ($a_mode === "create") {
            $values["rating_new"] = true;

            $values["rating_overall"] = ilObject::hasAutoRating("wiki", $this->requested_ref_id);
        } else {
            $values["online"] = $this->object->getOnline();
            $values["title"] = $this->object->getTitle();
            //$values["startpage"] = $this->object->getStartPage();
            $values["startpage_id"] = ilWikiPage::_getPageIdForWikiTitle($this->object->getId(), $this->object->getStartPage());
            $values["shorttitle"] = $this->object->getShortTitle();
            $values["description"] = $this->object->getLongDescription();
            $values["rating_overall"] = $this->object->getRatingOverall();
            $values["rating"] = $this->object->getRating();
            $values["rating_new"] = $this->object->getRatingForNewPages();
            $values["rating_ext"] = $this->object->getRatingCategories();
            $values["public_notes"] = $this->object->getPublicNotes();
            $values["intro"] = $this->object->getIntroduction();
            $values["page_toc"] = $this->object->getPageToc();
            $values["link_md_values"] = $this->object->getLinkMetadataValues();

            // only set given values (because of adv. metadata)
        }
        $this->form_gui->setValuesByArray($values, true);
    }


    public function saveSettingsObject(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $ilSetting = $this->settings;
        $obj_service = $this->object_service;

        $this->checkPermission("write");

        $this->initSettingsForm();

        if ($this->form_gui->checkInput()) {
            if (!ilObjWiki::checkShortTitleAvailability($this->form_gui->getInput("shorttitle")) &&
                $this->form_gui->getInput("shorttitle") !== $this->object->getShortTitle()) {
                $short_item = $this->form_gui->getItemByPostVar("shorttitle");
                $short_item->setAlert($lng->txt("wiki_short_title_already_in_use"));
            } else {
                $this->object->setTitle($this->form_gui->getInput("title"));
                $this->object->setDescription($this->form_gui->getInput("description"));
                $this->object->setOnline($this->form_gui->getInput("online"));
                $this->object->setStartPage(ilWikiPage::lookupTitle($this->form_gui->getInput("startpage_id")));
                $this->object->setShortTitle((string) $this->form_gui->getInput("shorttitle"));
                $this->object->setRatingOverall($this->form_gui->getInput("rating_overall"));
                $this->object->setRating($this->form_gui->getInput("rating"));
                // $this->object->setRatingAsBlock($this->form_gui->getInput("rating_side"));
                $this->object->setRatingForNewPages($this->form_gui->getInput("rating_new"));
                $this->object->setRatingCategories($this->form_gui->getInput("rating_ext"));

                if (!$ilSetting->get("disable_comments")) {
                    $this->object->setPublicNotes($this->form_gui->getInput("public_notes"));
                }
                $this->object->setIntroduction($this->form_gui->getInput("intro"));
                $this->object->setPageToc($this->form_gui->getInput("page_toc"));
                $this->object->setLinkMetadataValues($this->form_gui->getInput("link_md_values"));
                $this->object->update();

                // tile image
                $obj_service->commonSettings()->legacyForm($this->form_gui, $this->object)->saveTileImage();

                ilObjectServiceSettingsGUI::updateServiceSettingsForm(
                    $this->object->getId(),
                    $this->form_gui,
                    array(
                        ilObjectServiceSettingsGUI::CUSTOM_METADATA
                    )
                );

                // Update ecs export settings
                $ecs = new ilECSWikiSettings($this->object);
                if ($ecs->handleSettingsUpdate()) {
                    $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
                    $ilCtrl->redirect($this, "editSettings");
                }
            }
        }

        $this->form_gui->setValuesByPost();
        $this->tpl->setContent($this->form_gui->getHTML());
    }

    public function listContributorsObject(): void
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;

        $this->checkPermission("write");
        $ilTabs->activateTab("wiki_contributors");

        $table_gui = new ilWikiContributorsTableGUI(
            $this,
            "listContributors",
            $this->object->getId()
        );

        $tpl->setContent($table_gui->getHTML());

        $this->setSideBlock();
    }

    public function saveGradingObject(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $this->checkPermission("write");

        $users = $this->edit_request->getUserIds();
        $marks = $this->edit_request->getMarks();
        $comments = $this->edit_request->getComments();
        $status = $this->edit_request->getStatus();

        $saved = false;
        foreach ($users as $user_id) {
            if ($user_id != "") {
                $marks_obj = new ilLPMarks($this->object->getId(), $user_id);
                $new_mark = ilUtil::stripSlashes($marks[$user_id]);
                $new_comment = ilUtil::stripSlashes($comments[$user_id] ?? "");
                $new_status = ilUtil::stripSlashes($status[$user_id]);

                if ($marks_obj->getMark() !== $new_mark ||
                    $marks_obj->getComment() !== $new_comment ||
                    (int) ilWikiContributor::_lookupStatus($this->object->getId(), $user_id) !== (int) $new_status) {
                    ilWikiContributor::_writeStatus($this->object->getId(), $user_id, $new_status);
                    $marks_obj->setMark($new_mark);
                    $marks_obj->setComment($new_comment);
                    $marks_obj->update();
                    $saved = true;
                }
            }
        }
        if ($saved) {
            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
        }

        $ilCtrl->redirect($this, "listContributors");
    }

    // add wiki to locator
    protected function addLocatorItems(): void
    {
        $ilLocator = $this->locator;

        if (is_object($this->object)) {
            $ilLocator->addItem(
                $this->object->getTitle(),
                self::getGotoLink($this->object->getRefId()),
                "",
                $this->requested_ref_id
            );
        }
    }

    public static function _goto(string $a_target): void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $ilAccess = $DIC->access();
        $lng = $DIC->language();
        $ctrl = $DIC->ctrl();

        $i = strpos($a_target, "_");
        $a_page = "";
        if ($i > 0) {
            $a_page = substr($a_target, $i + 1);
            $a_target = substr($a_target, 0, $i);
        }

        if ($a_target === "wpage") {
            $a_page_arr = explode("_", $a_page);
            $wpg_id = (int) $a_page_arr[0];
            $ref_id = (int) ($a_page_arr[1] ?? 0);
            $w_id = ilWikiPage::lookupWikiId($wpg_id);
            if ($ref_id > 0) {
                $refs = array($ref_id);
            } else {
                $refs = ilObject::_getAllReferences($w_id);
            }
            foreach ($refs as $r) {
                if ($ilAccess->checkAccess("read", "", $r)) {
                    $a_target = $r;
                    $a_page = ilWikiPage::lookupTitle($wpg_id);
                }
            }
        }

        if ($ilAccess->checkAccess("read", "", $a_target)) {
            $ctrl->setParameterByClass(
                "ilobjwikigui",
                "page",
                ilWikiUtil::makeUrlTitle($a_page)
            );
            $ctrl->setParameterByClass(
                "ilwikihandlergui",
                "ref_id",
                $a_target
            );
            if ($a_page != "") {
                $ctrl->redirectByClass(
                    ["ilwikihandlergui", "ilobjwikigui"],
                    "viewPage"
                );
            } else {
                $ctrl->redirectByClass(
                    ["ilwikihandlergui"],
                    "view"
                );
            }
        } elseif ($ilAccess->checkAccess("visible", "", $a_target)) {
            ilObjectGUI::_gotoRepositoryNode($a_target, "infoScreen");
        } elseif ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
            $main_tpl->setOnScreenMessage('failure', sprintf(
                $lng->txt("msg_no_perm_read_item"),
                ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
            ), true);
            ilObjectGUI::_gotoRepositoryRoot();
        }

        throw new ilPermissionException($lng->txt("permission_denied"));
    }

    public static function getGotoLink(
        int $a_ref_id,
        string $a_page = ""
    ): string {
        if ($a_page === "") {
            $a_page = ilObjWiki::_lookupStartPage(ilObject::_lookupObjId($a_ref_id));
        }

        $goto = "goto.php?target=wiki_" . $a_ref_id . "_" .
            ilWikiUtil::makeUrlTitle($a_page);

        return $goto;
    }

    public function viewPageObject(): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $ilAccess = $this->access;

        $this->checkPermission("read");

        $ilTabs->clearTargets();
        $tpl->setHeaderActionMenu("");

        $page = ($this->requested_page !== "")
            ? $this->requested_page
            : $this->object->getStartPage();

        if (!ilWikiPage::exists($this->object->getId(), $page)) {
            $page = $this->object->getStartPage();
        }

        if (!ilWikiPage::exists($this->object->getId(), $page)) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("wiki_no_start_page"), true);
            $ilCtrl->redirect($this, "infoScreen");
            return;
        }

        // page exists, show it !
        $ilCtrl->setParameter($this, "page", ilWikiUtil::makeUrlTitle($page));

        $wpage_gui = ilWikiPageGUI::getGUIForTitle(
            $this->object->getId(),
            ilWikiUtil::makeDbTitle($page),
            0,
            $this->object->getRefId()
        );
        $wpage_gui->setStyleId($this->content_style_domain->getEffectiveStyleId());

        $this->setContentStyleSheet();

        //$wpage_gui->setSideBlock();
        $ilCtrl->setCmdClass("ilwikipagegui");
        $ilCtrl->setCmd("preview");
        if (!$ilAccess->checkAccess("write", "", $this->object->getRefId()) &&
            (
                !$ilAccess->checkAccess("edit_content", "", $this->object->getRefId()) ||
                $wpage_gui->getPageObject()->getBlocked()
            )) {
            $wpage_gui->setEnableEditing(false);
        }

        // alter title and description
        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            $wpage_gui->activateMetaDataEditor($this->object, "wpg", $wpage_gui->getId());
        }

        $html = $ilCtrl->forwardCommand($wpage_gui);

        $tpl->setContent($html);
    }

    public function allPagesObject(): void
    {
        $tpl = $this->tpl;

        $this->checkPermission("read");

        $this->addPagesSubTabs();

        $table_gui = new ilWikiPagesTableGUI(
            $this,
            "allPages",
            $this->object->getId(),
            IL_WIKI_ALL_PAGES
        );

        $this->setSideBlock();
        $tpl->setContent($table_gui->getHTML());
    }

    /**
     * @throws ilObjectException
     */
    public function popularPagesObject(): void
    {
        $tpl = $this->tpl;

        $this->checkPermission("read");

        $this->addPagesSubTabs();

        $table_gui = new ilWikiPagesTableGUI(
            $this,
            "popularPages",
            $this->object->getId(),
            IL_WIKI_POPULAR_PAGES
        );

        $this->setSideBlock();
        $tpl->setContent($table_gui->getHTML());
    }

    /**
     * @throws ilObjectException
     */
    public function orphanedPagesObject(): void
    {
        $tpl = $this->tpl;

        $this->checkPermission("read");

        $this->addPagesSubTabs();

        $table_gui = new ilWikiPagesTableGUI(
            $this,
            "orphanedPages",
            $this->object->getId(),
            IL_WIKI_ORPHANED_PAGES
        );

        $this->setSideBlock();
        $tpl->setContent($table_gui->getHTML());
    }

    /**
     * @param string $a_page page title
     */
    public function gotoPageObject(
        string $a_page = ""
    ): void {
        $ilCtrl = $this->ctrl;

        if ($a_page === "") {
            $a_page = $this->requested_page;
        }

        if (ilWikiPage::_wikiPageExists(
            $this->object->getId(),
            ilWikiUtil::makeDbTitle($a_page)
        )) {
            // to do: get rid of this redirect
            ilUtil::redirect(self::getGotoLink($this->object->getRefId(), $a_page));
        } else {
            if (!$this->access->checkAccess("edit_content", "", $this->object->getRefId())) {
                $this->tpl->setOnScreenMessage("failure", $this->lng->txt("no_permission"), true);
                ilUtil::redirect(ilObjWikiGUI::getGotoLink($this->object->getRefId(), $this->edit_request->getFromPage()));
            }
            if (!$this->object->getTemplateSelectionOnCreation()) {
                // check length
                if (ilStr::strLen(ilWikiUtil::makeDbTitle($a_page)) > 200) {
                    $this->tpl->setOnScreenMessage(
                        'failure',
                        $this->lng->txt("wiki_page_title_too_long") . " (" . $a_page . ")",
                        true
                    );
                    $ilCtrl->setParameterByClass(
                        "ilwikipagegui",
                        "page",
                        ilWikiUtil::makeUrlTitle($this->edit_request->getFromPage())
                    );
                    $ilCtrl->redirectByClass("ilwikipagegui", "preview");
                }
                $this->object->createWikiPage($a_page);

                // redirect to newly created page
                $ilCtrl->setParameterByClass("ilwikipagegui", "page", ilWikiUtil::makeUrlTitle(($a_page)));
                $ilCtrl->redirectByClass("ilwikipagegui", "edit");
            } else {
                $ilCtrl->setParameter($this, "page", ilWikiUtil::makeUrlTitle($this->requested_page));
                $ilCtrl->setParameter(
                    $this,
                    "from_page",
                    ilWikiUtil::makeUrlTitle($this->edit_request->getFromPage())
                );
                $ilCtrl->redirect($this, "showTemplateSelection");
            }
        }
    }

    public function randomPageObject(): void
    {
        $this->checkPermission("read");

        $page = ilWikiPage::getRandomPage($this->object->getId());
        $this->gotoPageObject($page);
    }

    public function recentChangesObject(): void
    {
        $tpl = $this->tpl;

        $this->checkPermission("read");

        $this->addPagesSubTabs();

        $table_gui = new ilWikiRecentChangesTableGUI(
            $this,
            "recentChanges",
            $this->object->getId()
        );

        $this->setSideBlock();
        $tpl->setContent($table_gui->getHTML());
    }

    public function setSideBlock(int $a_wpg_id = 0): void
    {
        self::renderSideBlock($a_wpg_id, $this->object->getRefId());
    }

    public static function renderSideBlock(
        int $a_wpg_id,
        int $a_wiki_ref_id,
        ?ilWikiPage $a_wp = null
    ): void {
        global $DIC;

        $tpl = $DIC["tpl"];
        $lng = $DIC->language();
        $ilAccess = $DIC->access();
        $ilCtrl = $DIC->ctrl();

        $tpl->addJavaScript("./Modules/Wiki/js/WikiPres.js");

        // setting asynch to false fixes #0019457, since otherwise ilBlockGUI would act on asynch and output html when side blocks
        // being processed during the export. This is a flaw in ilCtrl and/or ilBlockGUI.
        $tpl->addOnLoadCode("il.Wiki.Pres.init('" . $ilCtrl->getLinkTargetByClass("ilobjwikigui", "", "", false, false) . "');");

        if ($a_wpg_id > 0 && !$a_wp) {
            $a_wp = new ilWikiPage($a_wpg_id);
        }

        // search block
        $rcontent = ilRepositoryObjectSearchGUI::getSearchBlockHTML($lng->txt('wiki_search'));


        // quick navigation
        if ($a_wpg_id > 0) {
            // rating
            $wiki_id = ilObject::_lookupObjId($a_wiki_ref_id);
            if (ilObjWiki::_lookupRating($wiki_id) &&
                // ilObjWiki::_lookupRatingAsBlock($wiki_id) &&
                $a_wp->getRating()) {
                $rgui = new ilRatingGUI();
                $rgui->setObject($wiki_id, "wiki", $a_wpg_id, "wpg");
                $rgui->enableCategories(ilObjWiki::_lookupRatingCategories($wiki_id));
                $rgui->setYourRatingText("#");
                $rcontent .= $rgui->getBlockHTML($lng->txt("wiki_rate_page"));
            }

            // advanced metadata
            if (!ilWikiPage::lookupAdvancedMetadataHidden($a_wpg_id)) {
                $cmd = null;
                if ($ilAccess->checkAccess("write", "", $a_wiki_ref_id) ||
                    $ilAccess->checkAccess("edit_page_meta", "", $a_wiki_ref_id)) {
                    $cmd = array(
                        "edit" => $ilCtrl->getLinkTargetByClass("ilwikipagegui", "editAdvancedMetaData"),
                        "hide" => $ilCtrl->getLinkTargetByClass("ilwikipagegui", "hideAdvancedMetaData")
                    );
                }
                $wiki = new ilObjWiki($a_wiki_ref_id);
                $callback = $wiki->getLinkMetadataValues()
                    ? array($wiki, "decorateAdvMDValue")
                    : null;
                $mdgui = new ilObjectMetaDataGUI($wiki, "wpg", $a_wpg_id);
                $rcontent .= $mdgui->getBlockHTML($cmd, $callback); // #17291
            }
        }

        // important pages
        $imp_pages_block = new ilWikiImportantPagesBlockGUI();
        $rcontent .= $imp_pages_block->getHTML();

        // wiki functions block
        if ($a_wpg_id > 0) {
            $wiki_functions_block = new ilWikiFunctionsBlockGUI();
            $wiki_functions_block->setPageObject($a_wp);
            $rcontent .= $wiki_functions_block->getHTML();
        }

        $tpl->setRightContent($rcontent);
    }

    public function newPagesObject(): void
    {
        $tpl = $this->tpl;

        $this->checkPermission("read");

        $this->addPagesSubTabs();

        $table_gui = new ilWikiPagesTableGUI(
            $this,
            "newPages",
            $this->object->getId(),
            IL_WIKI_NEW_PAGES
        );

        $this->setSideBlock();
        $tpl->setContent($table_gui->getHTML());
    }

    protected function getPrintPageIds(): array
    {
        $page_ids = [];
        $ordering = $this->edit_request->getPrintOrdering();

        // multiple ordered page ids
        if (count($ordering) > 0) {
            asort($ordering);
            $page_ids = array_keys($ordering);
        }
        // single page
        elseif ($this->edit_request->getWikiPageId()) {
            $page_ids = array($this->edit_request->getWikiPageId());
        }
        return $page_ids;
    }

    public function getPrintView(bool $export = false): \ILIAS\Export\PrintProcessGUI
    {
        $page_ids = $export
            ? null
            : $this->getPrintPageIds();
        $provider = new \ILIAS\Wiki\WikiPrintViewProviderGUI(
            $this->lng,
            $this->ctrl,
            $this->object->getRefId(),
            $page_ids
        );

        return new \ILIAS\Export\PrintProcessGUI(
            $provider,
            $this->http,
            $this->ui,
            $this->lng
        );
    }

    public function printViewObject(): void
    {
        $print_view = $this->getPrintView();
        $print_view->sendPrintView();
    }

    public function performSearchObject(): void
    {
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $this->checkPermission("read");

        $ilTabs->setTabActive("wiki_search_results");

        if ($this->edit_request->getSearchTerm() === "") {
            $this->tpl->setOnScreenMessage('failure', $lng->txt("wiki_please_enter_search_term"), true);
            $ilCtrl->redirectByClass("ilwikipagegui", "preview");
        }

        $search_results = ilObjWiki::_performSearch(
            $this->object->getId(),
            $this->edit_request->getSearchTerm()
        );
        $table_gui = new ilWikiSearchResultsTableGUI(
            $this,
            "performSearch",
            $this->object->getId(),
            $search_results,
            $this->edit_request->getSearchTerm()
        );

        $this->setSideBlock();
        $tpl->setContent($table_gui->getHTML());
    }

    public function setContentStyleSheet(): void
    {
        $tpl = $this->tpl;

        if ($tpl == null) {
            $tpl = $this->tpl;
        }

        $this->content_style_gui->addCss($tpl, $this->object->getRefId());
        $tpl->addCss(ilObjStyleSheet::getSyntaxStylePath());
    }


    //
    // Important pages
    //

    public function editImportantPagesObject(): void
    {
        $tpl = $this->tpl;
        $ilToolbar = $this->toolbar;
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $this->checkPermission("edit_wiki_navigation");

        $this->tpl->setOnScreenMessage('info', $lng->txt("wiki_navigation_info"));

        $ipages = ilObjWiki::_lookupImportantPagesList($this->object->getId());
        $ipages_ids = array();
        foreach ($ipages as $i) {
            $ipages_ids[] = $i["page_id"];
        }

        // list pages
        $pages = ilWikiPage::getAllWikiPages($this->object->getId());
        $options = array("" => $lng->txt("please_select"));
        foreach ($pages as $p) {
            if (!in_array($p["id"], $ipages_ids)) {
                $options[$p["id"]] = ilStr::shortenTextExtended($p["title"], 60, true);
            }
        }
        if (count($options) > 0) {
            $si = new ilSelectInputGUI($lng->txt("wiki_pages"), "imp_page_id");
            $si->setOptions($options);
            $si->setInfo($lng->txt(""));
            $ilToolbar->addInputItem($si);
            $ilToolbar->setFormAction($ilCtrl->getFormAction($this));
            $ilToolbar->addFormButton($lng->txt("add"), "addImportantPage");
        }


        $ilTabs->activateTab("settings");
        $this->setSettingsSubTabs("imp_pages");

        $imp_table = new ilImportantPagesTableGUI($this, "editImportantPages");

        $tpl->setContent($imp_table->getHTML());
    }

    public function addImportantPageObject(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $this->checkPermission("edit_wiki_navigation");

        $imp_page_id = $this->edit_request->getImportantPageId();
        if ($imp_page_id > 0) {
            $this->object->addImportantPage($imp_page_id);
            $this->tpl->setOnScreenMessage('success', $lng->txt("wiki_imp_page_added"), true);
        }
        $ilCtrl->redirect($this, "editImportantPages");
    }

    public function confirmRemoveImportantPagesObject(): void
    {
        $ilCtrl = $this->ctrl;
        $tpl = $this->tpl;
        $lng = $this->lng;

        $imp_page_ids = $this->edit_request->getImportantPageIds();
        if (count($imp_page_ids) === 0) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("no_checkbox"), true);
            $ilCtrl->redirect($this, "editImportantPages");
        } else {
            $cgui = new ilConfirmationGUI();
            $cgui->setFormAction($ilCtrl->getFormAction($this));
            $cgui->setHeaderText($lng->txt("wiki_sure_remove_imp_pages"));
            $cgui->setCancel($lng->txt("cancel"), "editImportantPages");
            $cgui->setConfirm($lng->txt("remove"), "removeImportantPages");

            foreach ($imp_page_ids as $i) {
                $cgui->addItem("imp_page_id[]", $i, ilWikiPage::lookupTitle((int) $i));
            }

            $tpl->setContent($cgui->getHTML());
        }
    }

    public function removeImportantPagesObject(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $this->checkPermission("edit_wiki_navigation");

        $imp_page_ids = $this->edit_request->getImportantPageIds();
        foreach ($imp_page_ids as $i) {
            $this->object->removeImportantPage((int) $i);
        }
        $this->tpl->setOnScreenMessage('success', $lng->txt("wiki_removed_imp_pages"), true);
        $ilCtrl->redirect($this, "editImportantPages");
    }

    public function saveOrderingAndIndentObject(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $this->checkPermission("edit_wiki_navigation");

        $ordering = $this->edit_request->getImportantPageOrdering();
        $indentation = $this->edit_request->getImportantPageIndentation();
        $this->object->saveOrderingAndIndentation($ordering, $indentation);
        $this->tpl->setOnScreenMessage('success', $lng->txt("wiki_ordering_and_indent_saved"), true);
        $ilCtrl->redirect($this, "editImportantPages");
    }

    public function setAsStartPageObject(): void
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;

        $this->checkPermission("edit_wiki_navigation");

        $imp_page_ids = $this->edit_request->getImportantPageIds();
        if (count($imp_page_ids) !== 1) {
            $this->tpl->setOnScreenMessage('info', $lng->txt("wiki_select_one_item"), true);
        } else {
            $this->object->removeImportantPage($imp_page_ids[0]);
            $this->object->setStartPage(ilWikiPage::lookupTitle($imp_page_ids[0]));
            $this->object->update();
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
        }
        $ilCtrl->redirect($this, "editImportantPages");
    }


    /**
     * Create html package
     * @throws ilTemplateException
     * @throws ilWikiExportException
     */
    public function exportHTML(): void
    {
        /** @var ilObjWiki $wiki */
        $wiki = $this->object;
        $cont_exp = new Export\WikiHtmlExport($wiki);

        $format = explode("_", $this->edit_request->getFormat());
        if ($format[1] === "comments") {
            $cont_exp->setMode(Export\WikiHtmlExport::MODE_COMMENTS);
        }

        $cont_exp->buildExportFile();
    }

    /**
     * Get title for wiki page (used in ilNotesGUI)
     */
    public static function lookupSubObjectTitle(
        int $a_wiki_id,
        string $a_page_id
    ): string {
        $page = new ilWikiPage($a_page_id);
        if ($page->getWikiId() === $a_wiki_id) {
            return $page->getTitle();
        }
        return "";
    }

    /**
     * Used for rating export
     */
    public function getSubObjectTitle(
        int $a_id,
        string $a_type
    ): string {
        return ilWikiPage::lookupTitle($a_id);
    }

    public function showTemplateSelectionObject(): void
    {
        $lng = $this->lng;
        $tpl = $this->tpl;
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameterByClass(
            "ilobjwikigui",
            "from_page",
            ilWikiUtil::makeUrlTitle($this->edit_request->getFromPage())
        );
        $ilTabs->clearTargets();
        $this->tpl->setOnScreenMessage('info', $lng->txt("wiki_page_not_exist_select_templ"));

        $form = $this->initTemplateSelectionForm();
        $tpl->setContent($form->getHTML());
    }

    public function initTemplateSelectionForm(): ilPropertyFormGUI
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form = new ilPropertyFormGUI();

        // page name
        $hi = new ilHiddenInputGUI("page");
        $hi->setValue($this->requested_page);
        $form->addItem($hi);

        // page template
        $radg = new ilRadioGroupInputGUI($lng->txt("wiki_page_template"), "page_templ");
        $radg->setRequired(true);

        if ($this->object->getEmptyPageTemplate()) {
            $op1 = new ilRadioOption($lng->txt("wiki_empty_page"), 0);
            $radg->addOption($op1);
        }

        $wt = new ilWikiPageTemplate($this->object->getId());
        $ts = $wt->getAllInfo(ilWikiPageTemplate::TYPE_NEW_PAGES);
        foreach ($ts as $t) {
            $op = new ilRadioOption($t["title"], $t["wpage_id"]);
            $radg->addOption($op);
        }

        $form->addItem($radg);

        // save and cancel commands
        $form->addCommandButton("createPageUsingTemplate", $lng->txt("wiki_create_page"));
        $form->addCommandButton("cancelCreationPageUsingTemplate", $lng->txt("cancel"));

        $form->setTitle($lng->txt("wiki_new_page") . ": " . $this->requested_page);
        $form->setFormAction($ilCtrl->getFormAction($this));

        return $form;
    }

    public function createPageUsingTemplateObject(): void
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $form = $this->initTemplateSelectionForm();
        if ($form->checkInput()) {
            $a_page = $this->edit_request->getPage();
            $this->object->createWikiPage(
                $a_page,
                $this->edit_request->getPageTemplateId()
            );

            // redirect to newly created page
            $ilCtrl->setParameterByClass("ilwikipagegui", "page", ilWikiUtil::makeUrlTitle(($a_page)));
            $ilCtrl->redirectByClass("ilwikipagegui", "edit");

            $this->tpl->setOnScreenMessage('success', $lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "");
        } else {
            $form->setValuesByPost();
            $tpl->setContent($form->getHTML());
        }
    }

    public function cancelCreationPageUsingTemplateObject(): void
    {
        $ilCtrl = $this->ctrl;

        // redirect to newly created page
        $ilCtrl->setParameterByClass(
            "ilwikipagegui",
            "page",
            ilWikiUtil::makeUrlTitle($this->edit_request->getFromPage())
        );
        $ilCtrl->redirectByClass("ilwikipagegui", "preview");
    }

    protected function checkPermissionBool(string $perm, string $cmd = "", string $type = "", ?int $ref_id = null): bool
    {
        if ($perm === "create") {
            return parent::checkPermissionBool($perm, $cmd, $type, $ref_id);
        } else {
            if (!$ref_id) {
                $ref_id = $this->object->getRefId();
            }
            return ilWikiPerm::check($perm, $ref_id, $cmd);
        }
    }


    //
    // User HTML Export
    //

    /**
     * Export html (as user)
     */
    public function initUserHTMLExportObject(): void
    {
        $this->log->debug("init: " . $this->req_with_comments);
        $this->checkPermission("wiki_html_export");
        $this->object->initUserHTMLExport($this->req_with_comments);
    }

    /**
     * Export html (as user)
     */
    public function startUserHTMLExportObject(): void
    {
        $this->log->debug("start: " . $this->req_with_comments);
        $this->checkPermission("wiki_html_export");
        $this->object->startUserHTMLExport($this->req_with_comments);
    }

    /**
     * Get user html export progress
     */
    public function getUserHTMLExportProgressObject(): void
    {
        $this->log->debug("get progress: " . $this->req_with_comments);
        $this->checkPermission("wiki_html_export");
        $p = $this->object->getUserHTMLExportProgress($this->req_with_comments);

        $pb = ilProgressBar::getInstance();
        $pb->setCurrent($p["progress"]);

        $r = new stdClass();
        $r->progressBar = $pb->render();
        $r->status = $p["status"];
        $this->log->debug("status: " . $r->status);
        echo(json_encode($r, JSON_THROW_ON_ERROR));
        exit;
    }

    public function downloadUserHTMLExportObject(): void
    {
        $this->log->debug("download");
        $this->checkPermission("wiki_html_export");
        $this->object->deliverUserHTMLExport();
    }

    public function downloadUserHTMLExportWithCommentsObject(): void
    {
        $this->log->debug("download");
        $this->checkPermission("wiki_html_export");
        $this->object->deliverUserHTMLExport(true);
    }

    protected function triggerAssignmentTool(): void
    {
        if (!is_object($this->object)) {
            return;
        }
        $ass_info = ilExcRepoObjAssignment::getInstance()->getAssignmentInfoOfObj(
            $this->object->getRefId(),
            $this->user->getId()
        );
        if (count($ass_info) > 0) {
            $ass_ids = array_map(static function ($i): int {
                return $i->getId();
            }, $ass_info);
            $this->tool_context->current()->addAdditionalData(ilExerciseGSToolProvider::SHOW_EXC_ASSIGNMENT_INFO, true);
            $this->tool_context->current()->addAdditionalData(ilExerciseGSToolProvider::EXC_ASS_IDS, $ass_ids);
            $this->tool_context->current()->addAdditionalData(
                ilExerciseGSToolProvider::EXC_ASS_BUTTONS,
                $this->getAssignmentButtons()
            );
        }
    }

    /**
     * Get assignment buttons
     */
    protected function getAssignmentButtons(): array
    {
        $ilCtrl = $this->ctrl;
        $ui = $this->ui;
        $lng = $this->lng;

        $ass_info = ilExcRepoObjAssignment::getInstance()->getAssignmentInfoOfObj(
            $this->object->getRefId(),
            $this->user->getId()
        );
        $buttons = [];
        foreach ($ass_info as $i) {	// should be only one
            $ass = new ilExAssignment($i->getId());
            $times_up = $ass->afterDeadlineStrict();

            // submit button
            if (!$times_up) {
                $ilCtrl->setParameterByClass("ilwikipagegui", "ass", $ass->getId());
                $submit_link = $ilCtrl->getLinkTargetByClass("ilwikipagegui", "finalizeAssignment");
                $ilCtrl->setParameterByClass("ilwikipagegui", "ass", "");

                $buttons[$i->getId()][] = $ui->factory()->button()->primary($lng->txt("wiki_finalize_wiki"), $submit_link);
            }

            // submitted files
            $submission = new ilExSubmission($ass, $this->user->getId());
            if ($submission->hasSubmitted()) {
                $submitted = $submission->getSelectedObject();
                if ($submitted["ts"] != "") {
                    $ilCtrl->setParameterByClass("ilwikipagegui", "ass", $ass->getId());
                }
                $dl_link = $ilCtrl->getLinkTargetByClass("ilwikipagegui", "downloadExcSubFile");
                $ilCtrl->setParameterByClass("ilwikipagegui", "ass", "");
                $buttons[$i->getId()][] = $ui->factory()->button()->standard($lng->txt("wiki_download_submission"), $dl_link);
            }
        }
        return $buttons;
    }
}
