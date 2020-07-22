<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('./Modules/Portfolio/classes/class.ilObjPortfolioBaseGUI.php');

/**
 * Portfolio template view gui class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ilCtrl_Calls ilObjPortfolioTemplateGUI: ilPortfolioTemplatePageGUI, ilPageObjectGUI, ilNoteGUI
 * @ilCtrl_Calls ilObjPortfolioTemplateGUI: ilObjectCopyGUI, ilInfoScreenGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjPortfolioTemplateGUI: ilPermissionGUI, ilExportGUI, ilObjStyleSheetGUI
 *
 * @ingroup ModulesPortfolio
 */
class ilObjPortfolioTemplateGUI extends ilObjPortfolioBaseGUI
{
    /**
     * @var ilNavigationHistory
     */
    protected $nav_history;

    /**
     * @var ilHelpGUI
     */
    protected $help;

    /**
     * @var ilTabsGUI
     */
    protected $tabs;


    /**
     * Constructor
     */
    public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        parent::__construct($a_id, $a_id_type, $a_parent_node_id);
        global $DIC;

        $this->nav_history = $DIC["ilNavigationHistory"];
        $this->help = $DIC["ilHelp"];
        $this->tabs = $DIC->tabs();
        $this->toolbar = $DIC->toolbar();
        $this->user = $DIC->user();
    }

    public function getType()
    {
        return "prtt";
    }
        
    public function executeCommand()
    {
        $ilNavigationHistory = $this->nav_history;
                
        // add entry to navigation history
        if (!$this->getCreationMode() &&
            $this->getAccessHandler()->checkAccess("read", "", $this->node_id)) {
            $link = $this->ctrl->getLinkTarget($this, "view");
            $ilNavigationHistory->addItem($this->node_id, $link, "prtt");
        }
        
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd("view");

        switch ($next_class) {
            case 'ilportfoliotemplatepagegui':
                $this->determinePageCall(); // has to be done before locator!
                $this->prepareOutput();
                $this->handlePageCall($cmd);
                break;
                
            case "ilnotegui":
                $this->preview();
                break;
            
            case "ilinfoscreengui":
                $this->prepareOutput();
                $this->addHeaderAction("view");
                $this->infoScreenForward();
                break;
            
            case "ilcommonactiondispatchergui":
                include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;
            
            case "ilpermissiongui":
                $this->prepareOutput();
                $this->tabs_gui->activateTab("id_permissions");
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;
            
            case "ilobjectcopygui":
                $this->prepareOutput();
                include_once "./Services/Object/classes/class.ilObjectCopyGUI.php";
                $cp = new ilObjectCopyGUI($this);
                $cp->setType("prtt");
                $this->ctrl->forwardCommand($cp);
                break;
            
            case 'ilexportgui':
                $this->prepareOutput();
                $this->tabs_gui->activateTab("export");
                include_once("./Services/Export/classes/class.ilExportGUI.php");
                $exp_gui = new ilExportGUI($this);
                $exp_gui->addFormat("xml");
                $this->ctrl->forwardCommand($exp_gui);
                break;
            
            case "ilobjstylesheetgui":
                include_once("./Services/Style/Content/classes/class.ilObjStyleSheetGUI.php");
                $this->ctrl->setReturn($this, "editStyleProperties");
                $style_gui = new ilObjStyleSheetGUI("", $this->object->getStyleSheetId(), false, false);
                $style_gui->omitLocator();
                if ($cmd == "create" || $_GET["new_type"] == "sty") {
                    $style_gui->setCreationMode(true);
                }

                if ($cmd == "confirmedDelete") {
                    $this->object->setStyleSheetId(0);
                    $this->object->update();
                }

                $ret = $this->ctrl->forwardCommand($style_gui);

                if ($cmd == "save" || $cmd == "copyStyle" || $cmd == "importStyle") {
                    $style_id = $ret;
                    $this->object->setStyleSheetId($style_id);
                    $this->object->update();
                    $this->ctrl->redirectByClass("ilobjstylesheetgui", "edit");
                }
                break;
            
            default:
                $this->addHeaderAction($cmd);
                return ilObject2GUI::executeCommand();
        }
    }
        
    protected function setTabs()
    {
        $ilHelp = $this->help;
        
        $ilHelp->setScreenIdComponent("prtt");
        
        if ($this->checkPermissionBool("write")) {
            $this->tabs_gui->addTab(
                "pages",
                $this->lng->txt("content"),
                $this->ctrl->getLinkTarget($this, "view")
            );
        }
            
        if ($this->checkPermissionBool("read")) {
            $this->tabs_gui->addTab(
                "id_info",
                $this->lng->txt("info_short"),
                $this->ctrl->getLinkTargetByClass(array("ilobjportfoliotemplategui", "ilinfoscreengui"), "showSummary")
            );
        }
                
        if ($this->checkPermissionBool("write")) {
            $this->tabs_gui->addTab(
                "settings",
                $this->lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, "edit")
            );
            
            $this->tabs_gui->addTab(
                "export",
                $this->lng->txt("export"),
                $this->ctrl->getLinkTargetByClass("ilexportgui", "")
            );
        }
            
        if ($this->checkPermissionBool("read")) {
            $this->tabs_gui->addNonTabbedLink(
                "preview",
                $this->lng->txt("user_profile_preview"),
                $this->ctrl->getLinkTarget($this, "preview")
            );
        }
        
        // will add permissions if needed
        ilObject2GUI::setTabs();
    }
    
    /**
    * this one is called from the info button in the repository
    * not very nice to set cmdClass/Cmd manually, if everything
    * works through ilCtrl in the future this may be changed
    */
    public function infoScreen()
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->infoScreenForward();
    }
    
    /**
    * show information screen
    */
    public function infoScreenForward()
    {
        $ilTabs = $this->tabs;
        $ilToolbar = $this->toolbar;
        
        $ilTabs->activateTab("id_info");

        $this->checkPermission("visible");
    
        if ($this->checkPermissionBool("read")) {
            $this->lng->loadLanguageModule("cntr");
            
            include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
            $button = ilLinkButton::getInstance();
            $button->setPrimary(true);
            $button->setCaption("prtf_create_portfolio_from_template");
            $button->setUrl($this->ctrl->getLinkTarget($this, "createfromtemplate"));
            $ilToolbar->addButtonInstance($button);
        }
                
        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
        $info = new ilInfoScreenGUI($this);

        $info->enablePrivateNotes();
        
        if ($this->checkPermissionBool("read")) {
            $info->enableNews();
        }

        // no news editing for files, just notifications
        $info->enableNewsEditing(false);
        if ($this->checkPermissionBool("write")) {
            $news_set = new ilSetting("news");
            $enable_internal_rss = $news_set->get("enable_rss_for_internal");
            
            if ($enable_internal_rss) {
                $info->setBlockProperty("news", "settings", true);
                $info->setBlockProperty("news", "public_notifications_option", true);
            }
        }
        
        // standard meta data
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());
        
        $this->ctrl->forwardCommand($info);
    }
    
    
    //
    // CREATE/EDIT
    //

    protected function initDidacticTemplate(ilPropertyFormGUI $a_form)
    {
        $ilUser = $this->user;
        
        include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";
        $all = ilObjPortfolio::getPortfoliosOfUser($ilUser->getId());
        if (sizeof($all)) {
            $opts = array("" => $this->lng->txt("please_select"));
            foreach ($all as $item) {
                $opts[$item["id"]] = $item["title"];
            }
            $prtf = new ilSelectInputGUI($this->lng->txt("prtf_create_template_from_portfolio"), "prtf");
            $prtf->setInfo($this->lng->txt("prtf_create_template_from_portfolio_info"));
            $prtf->setOptions($opts);
            $a_form->addItem($prtf);
        }
        
        // yeah, I know.
        return $a_form;
    }
    
    protected function afterSave(ilObject $a_new_object)
    {
        if ($_POST["prtf"]) {
            include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";
            $source = new ilObjPortfolio($_POST["prtf"], false);

            ilObjPortfolio::clonePagesAndSettings($source, $a_new_object);
        }

        ilUtil::sendSuccess($this->lng->txt("prtt_portfolio_created"), true);
        $this->ctrl->setParameter($this, "prt_id", $a_new_object->getId());
        $this->ctrl->redirect($this, "view");
    }
        
    protected function initEditCustomForm(ilPropertyFormGUI $a_form)
    {
        $obj_service = $this->object_service;
        // activation/availability
        
        include_once "Services/Object/classes/class.ilObjectActivation.php";
        $this->lng->loadLanguageModule('rep');
        
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('rep_activation_availability'));
        $a_form->addItem($section);
        
        // additional info only with multiple references
        $act_obj_info = $act_ref_info = "";
        if (sizeof(ilObject::_getAllReferences($this->object->getId())) > 1) {
            $act_obj_info = ' ' . $this->lng->txt('rep_activation_online_object_info');
            $act_ref_info = $this->lng->txt('rep_activation_access_ref_info');
        }
        
        $online = new ilCheckboxInputGUI($this->lng->txt('rep_activation_online'), 'online');
        $online->setInfo($this->lng->txt('prtt_activation_online_info') . $act_obj_info);
        $a_form->addItem($online);
                
        include_once "Services/Form/classes/class.ilDateDurationInputGUI.php";
        $dur = new ilDateDurationInputGUI($this->lng->txt("rep_visibility_until"), "access_period");
        $dur->setShowTime(true);
        $dur->setEndText($this->lng->txt('rep_activation_limited_end'));
        $a_form->addItem($dur);

        $visible = new ilCheckboxInputGUI($this->lng->txt('rep_activation_limited_visibility'), 'access_visiblity');
        $visible->setInfo($this->lng->txt('prtt_activation_limited_visibility_info'));
        $dur->addSubItem($visible);
        
        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('obj_presentation'));
        $a_form->addItem($section);

        // tile image
        $obj_service->commonSettings()->legacyForm($a_form, $this->object)->addTileImage();

    
        parent::initEditCustomForm($a_form);

        $tit = $a_form->getItemByPostVar("title");
        $tit->setInfo($this->lng->txt('prtt_title_info'));
    }
    
    protected function getEditFormCustomValues(array &$a_values)
    {
        $a_values["online"] = $this->object->isOnline();
        $a_values["access_period"]["start"] = $this->object->getActivationStartDate()
            ? new ilDateTime($this->object->getActivationStartDate(), IL_CAL_UNIX)
            : null;
        $a_values["access_period"]["end"] = $this->object->getActivationEndDate()
            ? new ilDateTime($this->object->getActivationEndDate(), IL_CAL_UNIX)
            : null;
        $a_values["access_visiblity"] = $this->object->getActivationVisibility();
        
        parent::getEditFormCustomValues($a_values);
    }
    
    public function updateCustom(ilPropertyFormGUI $a_form)
    {
        $obj_service = $this->object_service;

        $this->object->setOnline($a_form->getInput("online"));
        
        // activation
        $period = $a_form->getItemByPostVar("access_period");
        if ($period->getStart() && $period->getEnd()) {
            $this->object->setActivationLimited(true);
            $this->object->setActivationVisibility($a_form->getInput("access_visiblity"));
            $this->object->setActivationStartDate($period->getStart()->get(IL_CAL_UNIX));
            $this->object->setActivationEndDate($period->getEnd()->get(IL_CAL_UNIX));
        } else {
            $this->object->setActivationLimited(false);
        }

        parent::updateCustom($a_form);

        $obj_service->commonSettings()->legacyForm($a_form, $this->object)->saveTileImage();
    }
    
    
    //
    // PAGES
    //
    
    /**
     * Get portfolio template page instance
     *
     * @param int $a_page_id
     * @param int $a_portfolio_id
     * @return ilPortfolioTemplatePage
     */
    protected function getPageInstance($a_page_id = null, $a_portfolio_id = null)
    {
        if (!$a_portfolio_id && $this->object) {
            $a_portfolio_id = $this->object->getId();
        }
        include_once "Modules/Portfolio/classes/class.ilPortfolioTemplatePage.php";
        $page = new ilPortfolioTemplatePage($a_page_id);
        $page->setPortfolioId($a_portfolio_id);
        return $page;
    }
    
    /**
     * Get portfolio template page gui instance
     *
     * @param int $a_page_id
     * @return ilPortfolioTemplatePageGUI
     */
    protected function getPageGUIInstance($a_page_id)
    {
        include_once("Modules/Portfolio/classes/class.ilPortfolioTemplatePageGUI.php");
        $page_gui = new ilPortfolioTemplatePageGUI(
            $this->object->getId(),
            $a_page_id,
            0,
            $this->object->hasPublicComments()
        );
        $page_gui->setAdditional($this->getAdditional());
        return $page_gui;
    }
    
    public function getPageGUIClassName()
    {
        return "ilportfoliotemplatepagegui";
    }
    
    protected function initCopyPageFormOptions(ilPropertyFormGUI $a_form)
    {
        // always existing prtft
        $hi = new ilHiddenInputGUI("target");
        $hi->setValue("old");
        $a_form->addItem($hi);

        $options = array();
        $all = ilObjPortfolioTemplate::getAvailablePortfolioTemplates("write");
        foreach ($all as $id => $title) {
            $options[$id] = $title;
        }
        $prtf = new ilSelectInputGUI($this->lng->txt("obj_prtt"), "prtf");
        $prtf->setRequired(true);
        $prtf->setOptions($options);
        $a_form->addItem($prtf);
    }
    
    
    //
    // BLOG
    //
    
    /**
     * Init blog template page form
     *
     * @param string $a_mode
     * @return ilPropertyFormGUI
     */
    public function initBlogForm()
    {
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));

        $obj = new ilTextInputGUI($this->lng->txt("title"), "blog");
        $obj->setRequired(true);
        $form->addItem($obj);

        // save and cancel commands
        $form->setTitle($this->lng->txt("prtf_add_blog") . ": " .
            $this->object->getTitle());
        $form->addCommandButton("saveBlog", $this->lng->txt("save"));
        $form->addCommandButton("view", $this->lng->txt("cancel"));
        
        return $form;
    }
    
    /**
     * Create new portfolio blog template page
     */
    public function saveBlog()
    {
        $form = $this->initBlogForm();
        if ($form->checkInput() && $this->checkPermissionBool("write")) {
            $page = $this->getPageInstance();
            $page->setType(ilPortfolioTemplatePage::TYPE_BLOG_TEMPLATE);
            $page->setTitle($form->getInput("blog"));
            $page->create();

            ilUtil::sendSuccess($this->lng->txt("prtf_blog_page_created"), true);
            $this->ctrl->redirect($this, "view");
        }

        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt("back"),
            $this->ctrl->getLinkTarget($this, "view")
        );

        $form->setValuesByPost();
        $this->tpl->setContent($form->getHtml());
    }
    
    
    //
    // TRANSMOGRIFIER
    //
        
    public function preview($a_return = false, $a_content = false, $a_show_notes = true)
    {
        if (!$this->checkPermissionBool("write") &&
            $this->checkPermissionBool("read")) {
            $this->lng->loadLanguageModule("cntr");
            
            include_once "Services/UIComponent/Button/classes/class.ilLinkButton.php";
            $button = ilLinkButton::getInstance();
            $button->setPrimary(true);
            $button->setCaption("prtf_create_portfolio_from_template");
            $button->setUrl($this->ctrl->getLinkTarget($this, "createfromtemplate"));
            $this->tpl->setHeaderActionMenu($button->render());
        }
        
        return parent::preview($a_return, $a_content, $a_show_notes);
    }
    
    public function createFromTemplateOld()
    {
        $this->ctrl->setParameterByClass("ilobjportfoliogui", "prtt_pre", $this->object->getId());
        $this->ctrl->redirectByClass(array("ilpersonaldesktopgui", "ilportfoliorepositorygui", "ilobjportfoliogui"), "create");
    }

    public function createFromTemplate()
    {
        $this->ctrl->setParameterByClass("ilobjportfoliogui", "prtt_pre", $this->object->getId());
        $this->ctrl->redirectByClass(array("ilpersonaldesktopgui", "ilportfoliorepositorygui", "ilobjportfoliogui"), "createFromTemplateDirect");
    }

    public static function _goto($a_target)
    {
        $id = explode("_", $a_target);
        
        $_GET["baseClass"] = "ilRepositoryGUI";
        $_GET["ref_id"] = $id[0];
        $_GET["cmd"] = "preview";
    
        include("ilias.php");
        exit;
    }
}
