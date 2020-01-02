<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Block/classes/class.ilBlockGUI.php");
include_once './Services/PersonalDesktop/interfaces/interface.ilDesktopItemHandling.php';
require_once('./Services/Repository/classes/class.ilObjectPlugin.php');

/**
* BlockGUI class for Selected Items on Personal Desktop
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_IsCalledBy ilPDSelectedItemsBlockGUI: ilColumnGUI
* @ilCtrl_Calls ilPDSelectedItemsBlockGUI: ilCommonActionDispatcherGUI
*/
class ilPDSelectedItemsBlockGUI extends ilBlockGUI implements ilDesktopItemHandling
{
    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilSetting
     */
    protected $settings;

    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;

    /** @var string */
    public static $block_type = 'pditems';

    /** @var ilPDSelectedItemsBlockViewSettings */
    protected $viewSettings;

    /** @var ilPDSelectedItemsBlockViewGUI */
    protected $view;

    /** @var bool */
    protected $manage = false;

    /** @var string */
    protected $content = '';

    /** @var ilLanguage */
    protected $lng;

    /** @var ilCtrl */
    protected $ctrl;

    /** @var ilObjUser */
    protected $user;

    /**
     * ilPDSelectedItemsBlockGUI constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->rbacsystem = $DIC->rbac()->system();
        $this->settings = $DIC->settings();
        $this->obj_definition = $DIC["objDefinition"];
        $this->access = $DIC->access();

        parent::__construct();

        $this->lng  = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->user = $DIC->user();

        $this->lng->loadLanguageModule('pd');
        $this->lng->loadLanguageModule('cntr'); // #14158

        $this->setEnableNumInfo(false);
        $this->setLimit(99999);
        $this->setAvailableDetailLevels(3, 1);
        $this->allow_moving = false;

        $this->initViewSettings();
    }

    /**
     *
     */
    protected function initViewSettings()
    {
        require_once 'Services/PersonalDesktop/ItemsBlock/classes/class.ilPDSelectedItemsBlockViewSettings.php';
        $this->viewSettings = new ilPDSelectedItemsBlockViewSettings($this->user, (int) $_GET['view']);
        $this->viewSettings->parse();

        require_once 'Services/PersonalDesktop/ItemsBlock/classes/class.ilPDSelectedItemsBlockViewGUI.php';
        $this->view = ilPDSelectedItemsBlockViewGUI::bySettings($this->viewSettings);

        $_GET['view'] = $this->viewSettings->getCurrentView();
        $this->ctrl->saveParameter($this, 'view');
    }

    /**
     * @return ilPDSelectedItemsBlockViewSettings
     */
    public function getViewSettings()
    {
        return $this->viewSettings;
    }

    /**
     *
     */
    public function isManagedView()
    {
        return $this->manage;
    }

    /**
     * @inheritdoc
     */
    public function fillDetailRow()
    {
        //		$this->ctrl->setParameterByClass('ilpersonaldesktopgui', 'view', $this->viewSettings->getCurrentView());
        parent::fillDetailRow();
        //		$this->ctrl->setParameterByClass('ilpersonaldesktopgui', 'view', '');
    }

    /**
     * @inheritdoc
     */
    public function addToDeskObject()
    {
        include_once './Services/PersonalDesktop/classes/class.ilDesktopItemGUI.php';
        ilDesktopItemGUI::addToDesktop();
        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
        $this->ctrl->setParameterByClass('ilpersonaldesktopgui', 'view', $this->viewSettings->getCurrentView());
        $this->ctrl->redirectByClass('ilpersonaldesktopgui', 'show');
    }

    /**
     * @inheritdoc
     */
    public function removeFromDeskObject()
    {
        include_once './Services/PersonalDesktop/classes/class.ilDesktopItemGUI.php';
        ilDesktopItemGUI::removeFromDesktop();
        ilUtil::sendSuccess($this->lng->txt("removed_from_desktop"), true);
        $this->ctrl->setParameterByClass('ilpersonaldesktopgui', 'view', $this->viewSettings->getCurrentView());
        $this->ctrl->redirectByClass('ilpersonaldesktopgui', 'show');
    }

    /**
     * @inheritdoc
     */
    public function getBlockType() : string
    {
        return self::$block_type;
    }

    /**
     * @inheritdoc
     */
    public static function getScreenMode()
    {
        $cmd = $_GET['cmd'];
        if ($cmd == 'post') {
            $cmd = $_POST['cmd'];
            $cmd = array_shift(array_keys($cmd));
        }

        switch ($cmd) {
            case 'confirmRemove':
            case 'manage':
                return IL_SCREEN_FULL;

            default:
                return IL_SCREEN_SIDE;
        }
    }

    /**
     * @inheritdoc
     */
    protected function isRepositoryObject() : bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getHTML()
    {
        global $DIC;

        $DIC->database()->useSlave(true);

        // workaround to show details row
        $this->setData(array('dummy'));

        require_once 'Services/Object/classes/class.ilObjectListGUI.php';
        ilObjectListGUI::prepareJSLinks(
            '',
            $this->ctrl->getLinkTargetByClass(array('ilcommonactiondispatchergui', 'ilnotegui'), '', '', true, false),
            $this->ctrl->getLinkTargetByClass(array('ilcommonactiondispatchergui', 'iltagginggui'), '', '', true, false)
        );

        $DIC['ilHelp']->setDefaultScreenId(ilHelpGUI::ID_PART_SCREEN, $this->view->getScreenId());
        $this->setTitle($this->view->getTitle());
        $this->setContent($this->getViewBlockHtml());

        if ($this->getContent() == '') {
            $this->setEnableDetailRow(false);
        }

        //		$this->ctrl->clearParametersByClass('ilpersonaldesktopgui');
        $this->ctrl->clearParameters($this);

        $DIC->database()->useSlave(false);

        return parent::getHTML();
    }

    // Overwritten from ilBlockGUI as there seems to be no other possibility to
    // not show Commands in the HEADER(!!!!) of a block in the VIEW_MY_STUDYPROGRAMME
    // case... Sigh.
    public function getFooterLinks()
    {
        if ($this->viewSettings->isStudyProgrammeViewActive()) {
            return array();
        }

        return parent::getFooterLinks();
    }
    
    /**
     *
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass();
        $cmd        = $this->ctrl->getCmd('getHTML');

        switch ($next_class) {
            case 'ilcommonactiondispatchergui':
                include_once('Services/Object/classes/class.ilCommonActionDispatcherGUI.php');
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;
                
            default:
                if (method_exists($this, $cmd)) {
                    return $this->$cmd();
                } else {
                    return $this->{$cmd . 'Object'}();
                }
        }
    }

    /**
     * @return string
     */
    protected function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $a_content
     */
    protected function setContent($a_content)
    {
        $this->content = $a_content;
    }

    /**
     * @inheritdoc
     */
    public function fillDataSection()
    {
        if ($this->getContent() == '') {
            $this->setDataSection($this->view->getIntroductionHtml());
        } else {
            $this->tpl->setVariable('BLOCK_ROW', $this->getContent());
        }
    }


    /**
     * @inheritdoc
     */
    public function fillFooter()
    {
        $this->setFooterLinks();
        $this->fillFooterLinks();
        $this->tpl->setVariable('FCOLSPAN', $this->getColSpan());
        if ($this->tpl->blockExists('block_footer')) {
            $this->tpl->setCurrentBlock('block_footer');
            $this->tpl->parseCurrentBlock();
        }
    }

    /**
     *
     */
    protected function setFooterLinks()
    {
        if ($this->getContent() == '') {
            $this->setEnableNumInfo(false);
            return '';
        }

        if ($this->manage) {
            return '';
        }

        $this->addFooterLink(
            $this->lng->txt("pd_sort_by_type"),
            $this->ctrl->getLinkTarget($this, "orderPDItemsByType"),
            $this->ctrl->getLinkTarget($this, "orderPDItemsByType", "", true),
            "block_" . $this->getBlockType() . "_" . $this->block_id,
            false,
            false,
            $this->viewSettings->isSortedByType()
        );

        $this->addFooterLink(
            $this->lng->txt("pd_sort_by_location"),
            $this->ctrl->getLinkTarget($this, "orderPDItemsByLocation"),
            $this->ctrl->getLinkTarget($this, "orderPDItemsByLocation", "", true),
            "block_" . $this->getBlockType() . "_" . $this->block_id,
            false,
            false,
            $this->viewSettings->isSortedByLocation()
        );

        if ($this->viewSettings->isMembershipsViewActive()) {
            $this->addFooterLink(
                $this->lng->txt("pd_sort_by_start_date"),
                $this->ctrl->getLinkTarget($this, "orderPDItemsByStartDate"),
                $this->ctrl->getLinkTarget($this, "orderPDItemsByStartDate", "", true),
                "block_" . $this->getBlockType() . "_" . $this->block_id,
                false,
                false,
                $this->viewSettings->isSortedByStartDate()
            );
        }

        $this->addFooterLink(
            $this->viewSettings->isSelectedItemsViewActive() ?
            $this->lng->txt("pd_remove_multiple") :
            $this->lng->txt("pd_unsubscribe_multiple_memberships"),
            $this->ctrl->getLinkTarget($this, "manage"),
            null,
            "block_" . $this->getBlockType() . "_" . $this->block_id
        );
    }

    /**
     * @param ilTemplate $tpl
     * @param ilPDSelectedItemsBlockGroup[] $grouped_items
     * @param bool $show_header
     * @return bool
     */
    protected function renderGroupedItems(ilTemplate $tpl, array $grouped_items, $show_header = false)
    {
        /** @var $rbacsystem ilRbacSystem */
        $rbacsystem = $this->rbacsystem;

        if (0 == count($grouped_items)) {
            return false;
        }

        $output = false;

        require_once 'Services/Object/classes/class.ilObjectActivation.php';
        require_once 'Services/PersonalDesktop/ItemsBlock/classes/class.ilPDSelectedItemsBlockListGUIFactory.php';
        $list_factory = new ilPDSelectedItemsBlockListGUIFactory($this);

        foreach ($grouped_items as $group) {
            $item_html = array();

            foreach ($group->getItems() as $item) {
                try {
                    $item_list_gui = $list_factory->byType($item['type']);
                    ilObjectActivation::addListGUIActivationProperty($item_list_gui, $item);

                    // #15232
                    if ($this->manage) {
                        if ($this->view->mayRemoveItem((int) $item['ref_id'])) {
                            $item_list_gui->enableCheckbox(true);
                        } else {
                            $item_list_gui->enableCheckbox(false);
                        }
                    }

                    $html = $item_list_gui->getListItemHTML($item['ref_id'], $item['obj_id'], $item['title'], $item['description']);
                    if ($html != '') {
                        $item_html[] = array(
                            'html'                 => $html,
                            'item_ref_id'          => $item['ref_id'],
                            'item_obj_id'          => $item['obj_id'],
                            'parent_ref'           => $item['parent_ref'],
                            'type'                 => $item['type'],
                            'item_icon_image_type' => $item_list_gui->getIconImageType()
                        );
                    }
                } catch (ilException $e) {
                    continue;
                }
            }

            if (0 == count($item_html)) {
                continue;
            }

            if ($show_header) {
                $this->addSectionHeader($tpl, $group);
                $this->resetRowType() ;
            }

            foreach ($item_html as $item) {
                $this->addStandardRow(
                    $tpl,
                    $item['html'],
                    $item['item_ref_id'],
                    $item['item_obj_id'],
                    $item['item_icon_image_type'],
                    'th_' . md5($group->getLabel())
                );
                $output = true;
            }
        }

        return $output;
    }

    /**
    * get selected item block
    */
    protected function getViewBlockHtml()
    {
        $tpl = $this->newBlockTemplate();

        $this->renderGroupedItems(
            $tpl,
            $this->view->getItemGroups(),
            ($this->getCurrentDetailLevel() >= $this->view->getMinimumDetailLevelForSection())
        );

        if ($this->manage && $this->view->supportsSelectAll()) {
            // #11355 - see ContainerContentGUI::renderSelectAllBlock()
            $tpl->setCurrentBlock('select_all_row');
            $tpl->setVariable('CHECKBOXNAME', 'ilToolbarSelectAll');
            $tpl->setVariable('SEL_ALL_PARENT', 'ilToolbar');
            $tpl->setVariable('SEL_ALL_CB_NAME', 'id');
            $tpl->setVariable('TXT_SELECT_ALL', $this->lng->txt('select_all'));
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    protected function resetRowType()
    {
        $this->cur_row_type = "";
    }
    
    /**
    * returns a new list block template
    *
    * @access	private
    * @return	object		block template
    */
    public function newBlockTemplate()
    {
        $tpl = new ilTemplate("tpl.pd_list_block.html", true, true, "Services/PersonalDesktop");
        $this->cur_row_type = "";
        return $tpl;
    }

    /**
     * @param ilTemplate                  $a_tpl
     * @param ilPDSelectedItemsBlockGroup $group
     */
    protected function addSectionHeader(ilTemplate $a_tpl, ilPDSelectedItemsBlockGroup $group)
    {
        if ($group->hasIcon()) {
            $a_tpl->setCurrentBlock('container_header_row_image');
            $a_tpl->setVariable('HEADER_IMG', $group->getIconPath());
            $a_tpl->setVariable('HEADER_ALT', $group->getLabel());
        } else {
            $a_tpl->setCurrentBlock('container_header_row');
        }

        $a_tpl->setVariable('BLOCK_HEADER_CONTENT', $group->getLabel());
        $a_tpl->setVariable('BLOCK_HEADER_ID', 'th_' . md5($group->getLabel()));
        $a_tpl->parseCurrentBlock();

        $a_tpl->touchBlock('container_row');

        $this->resetRowType();
    }

    /**
    * adds a standard row to a block template
    *
    * @param	object		$a_tpl		block template
    * @param	string		$a_html		html code
    * @access	private
    */
    public function addStandardRow(
        &$a_tpl,
        $a_html,
        $a_item_ref_id = "",
        $a_item_obj_id = "",
        $a_image_type = "",
        $a_related_header = ""
    ) {
        $ilSetting = $this->settings;
        
        $this->cur_row_type = ($this->cur_row_type == "row_type_1")
        ? "row_type_2"
        : "row_type_1";
        $a_tpl->touchBlock($this->cur_row_type);
        
        if ($a_image_type != "") {
            if (!is_array($a_image_type) && !in_array($a_image_type, array("lm", "htlm", "sahs"))) {
                $icon = ilUtil::getImagePath("icon_" . $a_image_type . ".svg");
                $title = $this->lng->txt("obj_" . $a_image_type);
            } else {
                $icon = ilUtil::getImagePath("icon_lm.svg");
                $title = $this->lng->txt("learning_module");
            }

            if ($ilSetting->get('custom_icons')) {
                global $DIC;
                /** @var \ilObjectCustomIconFactory  $customIconFactory */
                $customIconFactory = $DIC['object.customicons.factory'];
                $customIcon        = $customIconFactory->getByObjId($a_item_obj_id, $a_image_type);

                if ($customIcon->exists()) {
                    $icon = $customIcon->getFullPath();
                }
            }

            $a_tpl->setCurrentBlock("block_row_image");
            $a_tpl->setVariable("ROW_IMG", $icon);
            $a_tpl->setVariable("ROW_ALT", $title);
            $a_tpl->parseCurrentBlock();
        } else {
            $a_tpl->setVariable("ROW_NBSP", "&nbsp;");
        }
        $a_tpl->setCurrentBlock("container_standard_row");
        $a_tpl->setVariable("BLOCK_ROW_CONTENT", $a_html);
        $rel_headers = ($a_related_header != "")
        ? "th_selected_items " . $a_related_header
        : "th_selected_items";
        $a_tpl->setVariable("BLOCK_ROW_HEADERS", $rel_headers);
        $a_tpl->parseCurrentBlock();
        $a_tpl->touchBlock("container_row");
    }

    /**
     * @param string $sort_type
     */
    protected function changeSortMode($sort_type)
    {
        $this->user->writePref('pd_order_items', $sort_type);
        $this->initViewSettings();

        if ($this->ctrl->isAsynch()) {
            echo $this->getHTML();
            exit;
        }

        $this->ctrl->setParameterByClass('ilpersonaldesktopgui', 'view', $this->viewSettings->getCurrentView());
        $this->ctrl->redirectByClass('ilpersonaldesktopgui', 'show');
    }

    /**
     * Sort desktop items by location
     */
    public function orderPDItemsByLocation()
    {
        $this->changeSortMode($this->viewSettings->getSortByLocationMode());
    }
    
    /**
     * Sort desktop items by Type
     */
    public function orderPDItemsByType()
    {
        $this->changeSortMode($this->viewSettings->getSortByTypeMode());
    }

    /**
     * Sort desktop items by start date
     */
    public function orderPDItemsByStartDate()
    {
        $this->changeSortMode($this->viewSettings->getSortByStartDateMode());
    }

    public function manageObject()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        $this->manage = true;
        $this->setAvailableDetailLevels(1, 1);
        
        $top_tb = new ilToolbarGUI();
        $top_tb->setFormAction($ilCtrl->getFormAction($this));
        $top_tb->setLeadingImage(ilUtil::getImagePath("arrow_upright.svg"), $lng->txt("actions"));

        $button = ilSubmitButton::getInstance();
        if ($this->viewSettings->isSelectedItemsViewActive()) {
            $button->setCaption("remove");
        } else {
            $button->setCaption("pd_unsubscribe_memberships");
        }
        $button->setCommand("confirmRemove");
        $top_tb->addStickyItem($button);

        $button2 = ilSubmitButton::getInstance();
        $button2->setCaption("cancel");
        $button2->setCommand("getHTML");
        $top_tb->addStickyItem($button2);

        $top_tb->setCloseFormTag(false);

        $bot_tb = new ilToolbarGUI();
        $bot_tb->setLeadingImage(ilUtil::getImagePath("arrow_downright.svg"), $lng->txt("actions"));
        $bot_tb->addStickyItem($button);
        $bot_tb->addStickyItem($button2);
        $bot_tb->setOpenFormTag(false);
        return $top_tb->getHTML() . $this->getHTML() . $bot_tb->getHTML();
    }
    
    public function confirmRemoveObject()
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter($this, 'view', $this->viewSettings->getCurrentView());
        if (!sizeof($_POST["id"])) {
            ilUtil::sendFailure($this->lng->txt("select_one"), true);
            $ilCtrl->redirect($this, "manage");
        }
        
        if ($this->viewSettings->isSelectedItemsViewActive()) {
            $question = $this->lng->txt("pd_info_delete_sure_remove");
            $cmd = "confirmedRemove";
        } else {
            $question = $this->lng->txt("pd_info_delete_sure_unsubscribe");
            $cmd = "confirmedUnsubscribe";
        }
        
        include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
        $cgui = new ilConfirmationGUI();
        $cgui->setHeaderText($question);

        $cgui->setFormAction($ilCtrl->getFormAction($this));
        $cgui->setCancel($this->lng->txt("cancel"), "manage");
        $cgui->setConfirm($this->lng->txt("confirm"), $cmd);

        foreach ($_POST["id"] as $ref_id) {
            $obj_id = ilObject::_lookupObjectId($ref_id);
            $title = ilObject::_lookupTitle($obj_id);
            $type = ilObject::_lookupType($obj_id);
            
            $cgui->addItem(
                "ref_id[]",
                $ref_id,
                $title,
                ilObject::_getIcon($obj_id, "small", $type),
                $this->lng->txt("icon") . " " . $this->lng->txt("obj_" . $type)
            );
        }
        
        return $cgui->getHTML();
    }
        
    public function confirmedRemove()
    {
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;
        
        if (!sizeof($_POST["ref_id"])) {
            $ilCtrl->redirect($this, "manage");
        }
        
        foreach ($_POST["ref_id"] as $ref_id) {
            $type = ilObject::_lookupType($ref_id, true);
            ilObjUser::_dropDesktopItem($ilUser->getId(), $ref_id, $type);
        }
        
        // #12909
        ilUtil::sendSuccess($this->lng->txt("pd_remove_multi_confirm"), true);
        $ilCtrl->setParameterByClass('ilpersonaldesktopgui', 'view', $this->viewSettings->getCurrentView());
        $ilCtrl->redirectByClass("ilpersonaldesktopgui", "show");
    }
    
    public function confirmedUnsubscribe()
    {
        $ilCtrl = $this->ctrl;
        $ilAccess = $this->access;
        $ilUser = $this->user;
        
        if (!sizeof($_POST["ref_id"])) {
            $ilCtrl->redirect($this, "manage");
        }
        
        foreach ($_POST["ref_id"] as $ref_id) {
            if ($ilAccess->checkAccess("leave", "", $ref_id)) {
                switch (ilObject::_lookupType($ref_id, true)) {
                    case "crs":
                        // see ilObjCourseGUI:performUnsubscribeObject()
                        include_once "Modules/Course/classes/class.ilCourseParticipants.php";
                        $members = new ilCourseParticipants(ilObject::_lookupObjId($ref_id));
                        $members->delete($ilUser->getId());
                        
                        $members->sendUnsubscribeNotificationToAdmins($ilUser->getId());
                        $members->sendNotification(
                            $members->NOTIFY_UNSUBSCRIBE,
                            $ilUser->getId()
                        );
                        break;
                    
                    case "grp":
                        // see ilObjGroupGUI:performUnsubscribeObject()
                        include_once "Modules/Group/classes/class.ilGroupParticipants.php";
                        $members = new ilGroupParticipants(ilObject::_lookupObjId($ref_id));
                        $members->delete($ilUser->getId());
                        
                        include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
                        $members->sendNotification(
                            ilGroupMembershipMailNotification::TYPE_UNSUBSCRIBE_MEMBER,
                            $ilUser->getId()
                        );
                        $members->sendNotification(
                            ilGroupMembershipMailNotification::TYPE_NOTIFICATION_UNSUBSCRIBE,
                            $ilUser->getId()
                        );
                        break;
                    
                    default:
                        // do nothing
                        continue 2;
                }
        
                include_once './Modules/Forum/classes/class.ilForumNotification.php';
                ilForumNotification::checkForumsExistsDelete($ref_id, $ilUser->getId());
            }
        }
        
        
        ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
        $ilCtrl->setParameterByClass('ilpersonaldesktopgui', 'view', $this->viewSettings->getCurrentView());
        $ilCtrl->redirectByClass("ilpersonaldesktopgui", "show");
    }
}
