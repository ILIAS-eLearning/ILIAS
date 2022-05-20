<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject2GUI.php";
include_once('./Modules/WebResource/classes/class.ilParameterAppender.php');
require_once 'Services/LinkChecker/interfaces/interface.ilLinkCheckerGUIRowHandling.php';

/**
* Class ilObjLinkResourceGUI
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilObjLinkResourceGUI: ilObjectMetaDataGUI, ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjLinkResourceGUI: ilExportGUI, ilWorkspaceAccessGUI, ilCommonActionDispatcherGUI
* @ilCtrl_Calls ilObjLinkResourceGUI: ilPropertyFormGUI, ilInternalLinkGUI
*
*
* @ingroup ModulesWebResource
*/
class ilObjLinkResourceGUI extends ilObject2GUI implements ilLinkCheckerGUIRowHandling
{
    const VIEW_MODE_VIEW = 1;
    const VIEW_MODE_MANAGE = 2;
    const VIEW_MODE_SORT = 3;
    
    const LINK_MOD_CREATE = 1;
    const LINK_MOD_EDIT = 2;
    const LINK_MOD_ADD = 3;
    const LINK_MOD_SET_LIST = 4;
    const LINK_MOD_EDIT_LIST = 5;
    
    public function getType()
    {
        return "webr";
    }

    public function executeCommand()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $ilTabs = $DIC['ilTabs'];
        $ilErr = $DIC['ilErr'];
        $ilAccess = $DIC['ilAccess'];
        
        
        //if($this->ctrl->getTargetScript() == 'link_resources.php')
        if ($_GET["baseClass"] == 'ilLinkResourceHandlerGUI') {
            $_GET['view_mode'] = isset($_GET['switch_mode']) ? $_GET['switch_mode'] : $_GET['view_mode'];
            $ilCtrl->saveParameter($this, 'view_mode');
            $this->__prepareOutput();
        }
            
        $this->lng->loadLanguageModule("webr");

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        
        switch ($next_class) {
            case "ilinfoscreengui":
                $this->prepareOutput();
                $this->infoScreenForward();	// forwards command
                break;

            case 'ilobjectmetadatagui':
                $this->checkPermission('write'); // #18563
                $this->prepareOutput();
                $ilTabs->activateTab('id_meta_data');
                include_once 'Services/Object/classes/class.ilObjectMetaDataGUI.php';
                $md_gui = new ilObjectMetaDataGUI($this->object);
                $this->ctrl->forwardCommand($md_gui);
                break;
                
            case 'ilpermissiongui':
                $this->prepareOutput();
                $ilTabs->activateTab('id_permissions');
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $ret = &$this->ctrl->forwardCommand($perm_gui);
                break;
                
            case 'ilobjectcopygui':
                $this->prepareOutput();
                include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
                $cp = new ilObjectCopyGUI($this);
                $cp->setType('webr');
                $this->ctrl->forwardCommand($cp);
                break;
                
            case 'ilexportgui':
                $this->prepareOutput();
                $this->tabs_gui->setTabActive('export');
                include_once './Services/Export/classes/class.ilExportGUI.php';
                $exp = new ilExportGUI($this);
                $exp->addFormat('xml');
                $this->ctrl->forwardCommand($exp);
                break;
            
            case "ilcommonactiondispatchergui":
                $this->prepareOutput();
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;
            
            case "ilpropertyformgui":
                include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
                $this->initFormLink(self::LINK_MOD_EDIT);
                $this->ctrl->forwardCommand($this->form);
                break;
            
            case "ilinternallinkgui":
                $this->lng->loadLanguageModule("content");
                require_once("./Services/Link/classes/class.ilInternalLinkGUI.php");
                $link_gui = new ilInternalLinkGUI("RepositoryItem", 0);
                $link_gui->filterLinkType("PageObject");
                $link_gui->filterLinkType("GlossaryItem");
                $link_gui->filterLinkType("RepositoryItem");
                $link_gui->setFilterWhiteList(true);
                $ilCtrl->forwardCommand($link_gui);
                break;
            
            default:
                if (!$cmd) {
                    $this->ctrl->setCmd("view");
                }
                parent::executeCommand();
        }
        
        if (!$this->getCreationMode()) {
            // Fill meta header tags
            include_once('Services/MetaData/classes/class.ilMDUtils.php');
            ilMDUtils::_fillHTMLMetaTags($this->object->getId(), $this->object->getId(), 'webr');
            
            $this->addHeaderAction();
        }
        return true;
    }
    
    protected function initCreateForm($a_new_type)
    {
        $this->initFormLink(self::LINK_MOD_CREATE);
        return $this->form;
    }

    /**
     * Save new object
     * @access	public
     */
    public function save()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        $this->initFormLink(self::LINK_MOD_CREATE);
        $valid = $this->form->checkInput();
        if ($this->checkLinkInput(self::LINK_MOD_CREATE, $valid, 0, 0)
            && $this->form->getInput('tar_mode_type') == 'single') {
            // Save new object
            parent::save();
        } elseif ($valid && $this->form->getInput('tar_mode_type') == 'list') {
            $this->initList(self::LINK_MOD_CREATE, 0);
            parent::save();
        } else {
            // Data incomplete or invalid
            ilUtil::sendFailure($this->lng->txt('err_check_input'));
            $this->form->setValuesByPost();
            $this->tpl->setContent($this->form->getHTML());
        }
    }

    protected function afterSave(ilObject $a_new_object)
    {
        if ($this->form->getInput('tar_mode_type') == 'single') {
            // Save link
            $this->link->setLinkResourceId($a_new_object->getId());
            $link_id = $this->link->add();
            $this->link->updateValid(true);

            ilUtil::sendSuccess($this->lng->txt('webr_link_added'));
        }

        if ($this->form->getInput('tar_mode_type') == 'list') {
            // Save list
            $this->list->setListResourceId($a_new_object->getId());
            $this->list->add();

            ilUtil::sendSuccess($this->lng->txt('webr_list_added'));
        }
        
        // personal workspace
        if ($this->id_type == self::WORKSPACE_NODE_ID) {
            $this->ctrl->redirect($this, "editLinks");
        }
        // repository
        else {
            ilUtil::redirect("ilias.php?baseClass=ilLinkResourceHandlerGUI&ref_id=" .
                $a_new_object->getRefId() . "&cmd=switchViewMode&switch_mode=2");
        }
    }
    
    /**
     * Edit settings
     * Title, Description, Sorting
     * @return
     */
    protected function settings()
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];
    
        $this->checkPermission('write');
        $ilTabs->activateTab('id_settings');
        
        $this->initFormSettings();
        $this->tpl->setContent($this->form->getHTML());
    }
    
    /**
     * Save container settings
     * @return
     */
    protected function saveSettings()
    {
        global $DIC;

        $obj_service = $this->object_service;
        $ilTabs = $DIC['ilTabs'];
        
        $this->checkPermission('write');
        $ilTabs->activateTab('id_settings');
        
        $this->initFormSettings();
        $valid = $this->form->checkInput();
        if ($valid) {
            // update list
            $this->initList(self::LINK_MOD_EDIT_LIST, $this->object->getId());
            $this->list->update();

            // update object
            $this->object->setTitle($this->form->getInput('title'));
            $this->object->setDescription($this->form->getInput('desc'));
            $this->object->update();
            
            // update sorting
            include_once './Services/Container/classes/class.ilContainerSortingSettings.php';
            $sort = new ilContainerSortingSettings($this->object->getId());
            $sort->setSortMode($this->form->getInput('sor'));
            $sort->update();

            // tile image
            $obj_service->commonSettings()->legacyForm($this->form, $this->object)->saveTileImage();

            
            ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
            $this->ctrl->redirect($this, 'settings');
        }
        
        $this->form->setValuesByPost();
        ilUtil::sendFailure($this->lng->txt('err_check_input'));
        $this->tpl->setContent($this->form->getHTML());
    }
    
    
    /**
     * Show settings form
     * @return
     */
    protected function initFormSettings()
    {
        $obj_service = $this->object_service;

        include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this, 'saveSettings'));

        if (ilLinkResourceList::checkListStatus($this->object->getId())) {
            $this->form->setTitle($this->lng->txt('webr_edit_settings'));

            // Title
            $tit = new ilTextInputGUI($this->lng->txt('webr_list_title'), 'title');
            $tit->setValue($this->object->getTitle());
            $tit->setRequired(true);
            $tit->setSize(40);
            $tit->setMaxLength(127);
            $this->form->addItem($tit);

            // Description
            $des = new ilTextAreaInputGUI($this->lng->txt('webr_list_desc'), 'desc');
            $des->setValue($this->object->getDescription());
            $des->setCols(40);
            $des->setRows(3);
            $this->form->addItem($des);

            $section = new ilFormSectionHeaderGUI();
            $section->setTitle($this->lng->txt('obj_presentation'));
            $this->form->addItem($section);

            // tile image
            $obj_service->commonSettings()->legacyForm($this->form, $this->object)->addTileImage();

            // Sorting
            include_once './Services/Container/classes/class.ilContainerSortingSettings.php';
            include_once './Services/Container/classes/class.ilContainer.php';

            $sor = new ilRadioGroupInputGUI($this->lng->txt('webr_sorting'), 'sor');
            $sor->setRequired(true);
            include_once './Services/Container/classes/class.ilContainerSortingSettings.php';
            $sor->setValue(ilContainerSortingSettings::_lookupSortMode($this->object->getId()));

            $opt = new ilRadioOption(
                $this->lng->txt('webr_sort_title'),
                ilContainer::SORT_TITLE
            );
            $sor->addOption($opt);

            $opm = new ilRadioOption(
                $this->lng->txt('webr_sort_manual'),
                ilContainer::SORT_MANUAL
            );
            $sor->addOption($opm);
            $this->form->addItem($sor);
        } else {
            $this->form->setTitle($this->lng->txt('obj_presentation'));

            // hidden title
            $tit = new ilHiddenInputGUI('title');
            $tit->setValue($this->object->getTitle());
            $this->form->addItem($tit);

            // hidden description
            $des = new ilHiddenInputGUI('desc');
            $des->setValue($this->object->getDescription());
            $this->form->addItem($des);

            // tile image
            $obj_service->commonSettings()->legacyForm($this->form, $this->object)->addTileImage();
        }

        $this->form->addCommandButton('saveSettings', $this->lng->txt('save'));
        $this->form->addCommandButton('view', $this->lng->txt('cancel'));
    }
    

    /**
     * Edit a single link
     * @return
     */
    public function editLink()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        
        $this->checkPermission('write');
        $this->activateTabs('content', 'id_content_view');
        
        if (!(int) $_GET['link_id']) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $ilCtrl->redirect($this, 'view');
        }
        
        $this->initFormLink(self::LINK_MOD_EDIT);
        $this->setValuesFromLink((int) $_GET['link_id']);
        $this->tpl->setContent($this->form->getHTML());
    }
    
    /**
     * Save after editing
     * @return
     */
    public function updateLink()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        $this->initFormLink(self::LINK_MOD_EDIT);
        $valid = $this->form->checkInput();
        if ($this->checkLinkInput(self::LINK_MOD_EDIT, $valid, $this->object->getId(), (int) $_REQUEST['link_id'])) {
            $this->link->setLinkId((int) $_REQUEST['link_id']);
            $this->link->update();
            if (ilParameterAppender::_isEnabled() and is_object($this->dynamic)) {
                $this->dynamic->add((int) $_REQUEST['link_id']);
            }
            
            if (!ilLinkResourceList::checkListStatus($this->object->getId())) {
                $this->object->setTitle($this->form->getInput('title'));
                $this->object->setDescription($this->form->getInput('desc'));
                $this->object->update();
            }
            
            ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
            $ilCtrl->redirect($this, 'view');
        }
        ilUtil::sendFailure($this->lng->txt('err_check_input'));
        $this->form->setValuesByPost();
        $this->tpl->setContent($this->form->getHTML());
    }

    /**
     * Get form to transform a single weblink to a weblink list
     */
    public function getLinkToListModal()
    {
        global $DIC;

        $f = $DIC->ui()->factory();
        $r = $DIC->ui()->renderer();

        // check if form was already set
        if ($this->form == null) {
            $this->initFormLink(self::LINK_MOD_SET_LIST);
        }

        $form_id = 'form_' . $this->form->getId();

        $submit = $f->button()->primary($this->lng->txt('save'), '#')
            ->withOnLoadCode(function ($id) use ($form_id) {
                return "$('#{$id}').click(function() { $('#{$form_id}').submit(); return false; });";
            });
        $info = $f->messageBox()->info($this->lng->txt('webr_new_list_info'));

        $modal = $f->modal()->roundtrip(
            $this->lng->txt('webr_new_list'),
            $f->legacy($r->render($info) . $this->form->getHTML())
            )
            ->withActionButtons([$submit]);

        // modal triggers its show signal on load if form validation failed
        if (isset($_POST['sbmt']) && $_POST['sbmt'] == 'submit') {
            $modal = $modal->withOnLoad($modal->getShowSignal());
        }

        return $modal;
    }

    /**
     * Save form data
     */
    public function saveLinkList()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        $this->checkPermission('write');

        $this->initFormLink(self::LINK_MOD_SET_LIST);
        $valid = $this->form->checkInput();
        if ($valid) {
            // Save list data
            $this->object->setTitle($this->form->getInput('lti'));
            $this->object->setDescription($this->form->getInput('tde'));
            $this->object->update();

            // Save Link List
            $this->initList(self::LINK_MOD_SET_LIST, $this->object->getId());
            $this->list->add();
            ilUtil::sendSuccess($this->lng->txt('webr_list_set'), true);
            $ilCtrl->redirect($this, 'view');
        }

        // Error handling
        ilUtil::sendFailure($this->lng->txt('err_check_input'), true);
        $this->form->setValuesByPost();
        $this->view();
    }
    
    /**
     * Add an additional link
     * @return
     */
    public function addLink()
    {
        $this->checkPermission('write');
        $this->activateTabs('content', 'id_content_view');
    
        $this->initFormLink(self::LINK_MOD_ADD);
        $this->tpl->setContent($this->form->getHTML());
    }
    
    /**
     * Save form data
     * @return
     */
    public function saveAddLink()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        
        $this->checkPermission('write');
    
        $this->initFormLink(self::LINK_MOD_ADD);
        $valid = $this->form->checkInput();
        if ($this->checkLinkInput(self::LINK_MOD_ADD, $valid, $this->object->getId(), 0)) {
            // Save Link
            $link_id = $this->link->add();
            $this->link->updateValid(true);
            
            // Dynamic parameters
            if (ilParameterAppender::_isEnabled() and is_object($this->dynamic)) {
                $this->dynamic->add($link_id);
            }
            ilUtil::sendSuccess($this->lng->txt('webr_link_added'), true);
            $ilCtrl->redirect($this, 'view');
        }
        // Error handling
        ilUtil::sendFailure($this->lng->txt('err_check_input'));
        $this->form->setValuesByPost();
        
        $this->activateTabs('content', 'id_content_view');
        $this->tpl->setContent($this->form->getHTML());
    }
    
    /**
     * Delete a dynamic parameter
     * @return
     */
    protected function deleteParameter()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        $this->checkPermission('write');
        
        $this->ctrl->setParameter($this, 'link_id', (int) $_GET['link_id']);
        
        if (!isset($_GET['param_id'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $ilCtrl->redirect($this, 'view');
        }

        include_once './Modules/WebResource/classes/class.ilParameterAppender.php';
        $param = new ilParameterAppender($this->object->getId());
        $param->delete((int) $_GET['param_id']);
        
        ilUtil::sendSuccess($this->lng->txt('links_parameter_deleted'), true);
        $ilCtrl->redirect($this, 'editLinks');
    }
    
    protected function deleteParameterForm()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        $this->checkPermission('write');
        
        if (!isset($_GET['param_id'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $ilCtrl->redirect($this, 'view');
        }

        include_once './Modules/WebResource/classes/class.ilParameterAppender.php';
        $param = new ilParameterAppender($this->object->getId());
        $param->delete((int) $_GET['param_id']);
        
        ilUtil::sendSuccess($this->lng->txt('links_parameter_deleted'), true);
        $ilCtrl->redirect($this, 'view');
    }
    
    
    /**
     * Update all visible links
     * @return
     */
    protected function updateLinks()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        
        $this->checkPermission('write');
        $this->activateTabs('content', '');
        
        if (!is_array($_POST['ids'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $ilCtrl->redirect($this, 'view');
        }
        
        // Validate
        $invalid = array();
        foreach ($_POST['ids'] as $link_id) {
            $data = $_POST['links'][$link_id];
    
            // handle internal links
            if ($_POST['tar_' . $link_id . '_ajax_type'] &&
                $_POST['tar_' . $link_id . '_ajax_id']) {
                $data['tar'] = $_POST['links'][$link_id]['tar'] =
                    $_POST['tar_' . $link_id . '_ajax_type'] . '|' .
                    $_POST['tar_' . $link_id . '_ajax_id'];
            }
            
            
            if (!strlen($data['title'])) {
                $invalid[] = $link_id;
                continue;
            }
            if (!strlen($data['tar'])) {
                $invalid[] = $link_id;
                continue;
            }
            if ($data['nam'] and !$data['val']) {
                $invalid[] = $link_id;
                continue;
            }
            if (!$data['nam'] and $data['val']) {
                $invalid[] = $link_id;
                continue;
            }
        }
        
        if (count($invalid)) {
            ilUtil::sendFailure($this->lng->txt('err_check_input'));
            $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.webr_manage.html', 'Modules/WebResource');
            
            include_once './Modules/WebResource/classes/class.ilWebResourceEditableLinkTableGUI.php';
            $table = new ilWebResourceEditableLinkTableGUI($this, 'view');
            $table->setInvalidLinks($invalid);
            $table->parseSelectedLinks($_POST['ids']);
            $table->updateFromPost();
            $this->tpl->setVariable('TABLE_LINKS', $table->getHTML());
            return false;
        }
        
        include_once './Modules/WebResource/classes/class.ilLinkResourceItems.php';
        $links = new ilLinkResourceItems($this->object->getId());
        
        // Save Settings
        include_once './Services/Form/classes/class.ilFormPropertyGUI.php';
        include_once './Services/Form/classes/class.ilLinkInputGUI.php';
        foreach ($_POST['ids'] as $link_id) {
            $data = $_POST['links'][$link_id];
            
            $orig = ilLinkResourceItems::lookupItem($this->object->getId(), $link_id);
            
            $links->setLinkId($link_id);
            $links->setTitle(ilUtil::stripSlashes($data['title']));
            $links->setDescription(ilUtil::stripSlashes($data['desc']));
            $links->setTarget(str_replace('"', '', ilUtil::stripSlashes($data['tar'])));
            $links->setActiveStatus((int) $data['act']);
            $links->setDisableCheckStatus((int) $data['che']);
            $links->setLastCheckDate($orig['last_check']);
            $links->setValidStatus((int) $data['vali']);
            $links->setInternal(ilLinkInputGUI::isInternalLink($data['tar']));
            $links->update();
            
            if (strlen($data['nam']) and $data['val']) {
                $param = new ilParameterAppender($this->object->getId());
                $param->setName(ilUtil::stripSlashes($data['nam']));
                $param->setValue((int) $data['val']);
                $param->add($link_id);
            }

            if (!ilLinkResourceList::checkListStatus($this->object->getId())) {
                $this->object->setTitle(ilUtil::stripSlashes($data['title']));
                $this->object->setDescription(ilUtil::stripSlashes($data['desc']));
                $this->object->update();
            }
            
            // TODO: Dynamic parameters
        }
            
        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $ilCtrl->redirect($this, 'view');
    }
    
    /**
     * Set form values from link
     * @param object $a_link_id
     * @return
     */
    protected function setValuesFromLink($a_link_id)
    {
        include_once './Modules/WebResource/classes/class.ilLinkResourceItems.php';
        $link = new ilLinkResourceItems($this->object->getId());
        
        $values = $link->getItem($a_link_id);
        
        if (ilParameterAppender::_isEnabled()) {
        }
        
        $this->form->setValuesByArray(
            array(
                'title' => $values['title'],
                'tar' => $values['target'],
                'desc' => $values['description'],
                'act' => (int) $values['active'],
                'che' => (int) $values['disable_check'],
                'vali' => (int) $values['valid']
            )
        );
    }

    /**
     * Init a new link list
     *
     * @param int $a_mode
     * @param int $a_webr_id
     */
    protected function initList(int $a_mode, int $a_webr_id = 0)
    {
        if ($a_mode == self::LINK_MOD_CREATE || $a_mode == self::LINK_MOD_EDIT_LIST) {
            $this->list = new ilLinkResourceList($a_webr_id);
            $this->list->setTitle($this->form->getInput('title'));
            $this->list->setDescription($this->form->getInput('desc'));
        }

        if ($a_mode == self::LINK_MOD_SET_LIST) {
            $this->list = new ilLinkResourceList($a_webr_id);
            $this->list->setTitle($this->form->getInput('lti'));
            $this->list->setDescription($this->form->getInput('tde'));
        }
    }
    
    
    /**
     * Check input after creating a new link
     * @param object $a_mode
     * @param bool $a_valid
     * @param object $a_webr_id [optional]
     * @param object $a_link_id [optional]
     * @return
     */
    protected function checkLinkInput($a_mode, $a_valid, $a_webr_id = 0, $a_link_id = 0)
    {
        $valid = $a_valid;
        
        $link_input = $this->form->getInput('tar');
        
        include_once './Modules/WebResource/classes/class.ilLinkResourceItems.php';
        $this->link = new ilLinkResourceItems($a_webr_id);
        $this->link->setTarget(str_replace('"', '', ilUtil::stripSlashes($link_input)));
        $this->link->setTitle($this->form->getInput('title'));
        $this->link->setDescription($this->form->getInput('desc'));
        $this->link->setDisableCheckStatus($this->form->getInput('che'));
        $this->link->setInternal(ilLinkInputGUI::isInternalLink($link_input));
        
        if ($a_mode == self::LINK_MOD_CREATE) {
            $this->link->setActiveStatus(true);
        } else {
            $this->link->setActiveStatus($this->form->getInput('act'));
        }
        
        if ($a_mode == self::LINK_MOD_EDIT) {
            $this->link->setValidStatus($this->form->getInput('vali'));
        } else {
            $this->link->setValidStatus(true);
        }
        
        if (!ilParameterAppender::_isEnabled()) {
            return $valid;
        }
        
        $this->dynamic = new ilParameterAppender($a_webr_id);
        $this->dynamic->setName($this->form->getInput('nam'));
        $this->dynamic->setValue($this->form->getInput('val'));
        if (!$this->dynamic->validate()) {
            switch ($this->dynamic->getErrorCode()) {
                case LINKS_ERR_NO_NAME:
                    $this->form->getItemByPostVar('nam')->setAlert($this->lng->txt('links_no_name_given'));
                    return false;
                    
                case LINKS_ERR_NO_VALUE:
                    $this->form->getItemByPostVar('val')->setAlert($this->lng->txt('links_no_value_given'));
                    return false;
                    
                case LINKS_ERR_NO_NAME_VALUE:
                    // Nothing entered => no error
                    return $valid;
            }
            $this->dynamic = null;
        }
        return $valid;
    }

    
    /**
     * Show create/edit single link
     * @param int form mode
     * @return
     */
    protected function initFormLink($a_mode)
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];
    
        $ilTabs->activateTab("id_content");
        
        include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
        $this->form = new ilPropertyFormGUI();
        
        switch ($a_mode) {
            case self::LINK_MOD_CREATE:
                // Header
                $this->ctrl->setParameter($this, 'new_type', 'webr');
                $this->form->setTitle($this->lng->txt('webr_new_link'));
                $this->form->setTableWidth('600px');

                // Buttons
                $this->form->addCommandButton('save', $this->lng->txt('webr_add'));
                $this->form->addCommandButton('cancel', $this->lng->txt('cancel'));
                break;
                
            case self::LINK_MOD_ADD:
                // Header
                $this->form->setTitle($this->lng->txt('webr_new_link'));

                // Buttons
                $this->form->addCommandButton('saveAddLink', $this->lng->txt('webr_add'));
                $this->form->addCommandButton('view', $this->lng->txt('cancel'));
                break;

            case self::LINK_MOD_EDIT:
                // Header
                $this->ctrl->setParameter($this, 'link_id', (int) $_REQUEST['link_id']);
                $this->form->setTitle($this->lng->txt('webr_edit'));
                
                // Buttons
                $this->form->addCommandButton('updateLink', $this->lng->txt('save'));
                $this->form->addCommandButton('view', $this->lng->txt('cancel'));
                break;
        }
        
        if ($a_mode == self::LINK_MOD_SET_LIST) {
            $this->form->setValuesByPost();
            $this->form->setFormAction($this->ctrl->getFormAction($this, 'saveLinkList'));
            $this->form->setId(uniqid('form'));

            // List Title
            $title = new ilTextInputGUI($this->lng->txt('webr_list_title'), 'lti');
            $title->setRequired(true);
            $title->setSize(40);
            $title->setMaxLength(127);
            $this->form->addItem($title);
            
            // List Description
            $desc = new ilTextAreaInputGUI($this->lng->txt('webr_list_desc'), 'tde');
            $desc->setRows(3);
            $desc->setCols(40);
            $this->form->addItem($desc);

            $item = new ilHiddenInputGUI('sbmt');
            $item->setValue('submit');
            $this->form->addItem($item);
        }


        else {
            $this->form->setFormAction($this->ctrl->getFormAction($this));

            // Target
            /*
            $tar = new ilTextInputGUI($this->lng->txt('webr_link_target'),'tar');
            $tar->setValue("http://");

            $tar->setSize(40);
            $tar->setMaxLength(500);
            */
            include_once 'Services/Form/classes/class.ilLinkInputGUI.php';
            $tar = new ilLinkInputGUI($this->lng->txt('type'), 'tar'); // lng var
            if ($a_mode == self::LINK_MOD_CREATE) {
                $tar->setAllowedLinkTypes(ilLinkInputGUI::LIST);
            }
            $tar->setInternalLinkFilterTypes(
                array(
                    "PageObject",
                    "GlossaryItem",
                    "RepositoryItem",
                    'WikiPage'
                )
            );
            $tar->setExternalLinkMaxLength(1000);
            $tar->setInternalLinkFilterTypes(array("PageObject", "GlossaryItem", "RepositoryItem"));
            $tar->setRequired(true);
            $this->form->addItem($tar);

            // Title
            $tit = new ilTextInputGUI($this->lng->txt('webr_link_title'), 'title');
            $tit->setRequired(true);
            $tit->setSize(40);
            $tit->setMaxLength(127);
            $this->form->addItem($tit);

            // Description
            $des = new ilTextAreaInputGUI($this->lng->txt('description'), 'desc');
            $des->setRows(3);
            $des->setCols(40);
            $this->form->addItem($des);


            if ($a_mode != self::LINK_MOD_CREATE) {
                // Active
                $act = new ilCheckboxInputGUI($this->lng->txt('active'), 'act');
                $act->setChecked(true);
                $act->setValue(1);
                $this->form->addItem($act);

                // Check
                $che = new ilCheckboxInputGUI($this->lng->txt('webr_disable_check'), 'che');
                $che->setValue(1);
                $this->form->addItem($che);
            }

            // Valid
            if ($a_mode == self::LINK_MOD_EDIT) {
                $val = new ilCheckboxInputGUI($this->lng->txt('valid'), 'vali');
                $this->form->addItem($val);
            }

            if (ilParameterAppender::_isEnabled() && $a_mode != self::LINK_MOD_CREATE) {
                $dyn = new ilNonEditableValueGUI($this->lng->txt('links_dyn_parameter'));
                $dyn->setInfo($this->lng->txt('links_dynamic_info'));


                if (count($links = ilParameterAppender::_getParams((int)$_GET['link_id']))) {
                    $ex = new ilCustomInputGUI($this->lng->txt('links_existing_params'), 'ex');
                    $dyn->addSubItem($ex);

                    foreach ($links as $id => $link) {
                        $p = new ilCustomInputGUI();

                        $ptpl = new ilTemplate('tpl.link_dyn_param_edit.html', true, true, 'Modules/WebResource');
                        $ptpl->setVariable('INFO_TXT', ilParameterAppender::parameterToInfo($link['name'], $link['value']));
                        $this->ctrl->setParameter($this, 'param_id', $id);
                        $ptpl->setVariable('LINK_DEL', $this->ctrl->getLinkTarget($this, 'deleteParameterForm'));
                        $ptpl->setVariable('LINK_TXT', $this->lng->txt('delete'));
                        $p->setHtml($ptpl->get());
                        $dyn->addSubItem($p);
                    }
                }

                // Existing parameters

                // New parameter
                if ($a_mode != self::LINK_MOD_CREATE) {
                    #$new = new ilCustomInputGUI($this->lng->txt('links_add_param'),'');
                    #$dyn->addSubItem($new);
                }

                // Dynyamic name
                $nam = new ilTextInputGUI($this->lng->txt('links_name'), 'nam');
                $nam->setSize(12);
                $nam->setMaxLength(128);
                $dyn->addSubItem($nam);

                // Dynamic value
                $val = new ilSelectInputGUI($this->lng->txt('links_value'), 'val');
                $val->setOptions(ilParameterAppender::_getOptionSelect());
                $val->setValue(0);
                $dyn->addSubItem($val);

                $this->form->addItem($dyn);
            }
        }
    }
    
    /**
     * Switch between "View" "Manage" and "Sort"
     * @return
     */
    protected function switchViewMode()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        
        $_REQUEST['view_mode'] = $_GET['view_mode'] = (int) $_GET['switch_mode'];
        $this->view();
    }
    
    /**
     * Start with manage mode
     * @return
     */
    protected function editLinks()
    {
        $_GET['switch_mode'] = self::VIEW_MODE_MANAGE;
        $this->switchViewMode();
    }
    

    /**
     * View object
     * @return
     */
    public function view()
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];
        $ilTabs = $DIC['ilTabs'];
        
        $ilTabs->activateTab("id_content");
        
        $this->checkPermission('read');
        
        if (strtolower($_GET["baseClass"]) == "iladministrationgui") {
            parent::view();
            return true;
        } else {
            switch ((int) $_REQUEST['view_mode']) {
                case self::VIEW_MODE_MANAGE:
                    $this->manage();
                    break;
                    
                case self::VIEW_MODE_SORT:
                    // #14638
                    include_once './Services/Container/classes/class.ilContainerSortingSettings.php';
                    if (ilContainerSortingSettings::_lookupSortMode($this->object->getId()) == ilContainer::SORT_MANUAL) {
                        $this->sort();
                        break;
                    }
                    // fallthrough
                
                    // no break
                default:
                    $this->showLinks();
                    break;
            }
        }
        $GLOBALS['DIC']['tpl']->setPermanentLink($this->object->getType(), $this->object->getRefId());
        return true;
    }
    
    /**
     * Manage links
     * @return
     */
    protected function manage()
    {
        $this->checkPermission('write');
        $this->activateTabs('content', 'id_content_manage');
        
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.webr_manage.html', 'Modules/WebResource');
        $this->showToolbar('ACTION_BUTTONS');
        
        include_once './Modules/WebResource/classes/class.ilWebResourceEditableLinkTableGUI.php';
        $table = new ilWebResourceEditableLinkTableGUI($this, 'view');
        $table->parse();
        
        include_once './Services/Link/classes/class.ilInternalLinkGUI.php';
        $js = ilInternalLinkGUI::getInitHTML("");
        
        $this->tpl->addJavaScript("Modules/WebResource/js/intLink.js");
        $this->tpl->addJavascript("Services/Form/js/Form.js");

        $this->tpl->setVariable('TABLE_LINKS', $table->getHTML() . $js);
    }
    
    /**
     * Show all active links
     * @return
     */
    protected function showLinks()
    {
        $this->checkPermission('read');
        $this->activateTabs('content', 'id_content_view');
        
        include_once './Modules/WebResource/classes/class.ilWebResourceLinkTableGUI.php';
        $table = new ilWebResourceLinkTableGUI($this, 'showLinks');
        $table->parse();
        
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.webr_view.html', 'Modules/WebResource');
        $this->showToolbar('ACTION_BUTTONS');
        $this->tpl->setVariable('LINK_TABLE', $table->getHTML());
    }
    
    /**
     * Sort web links
     * @return
     */
    protected function sort()
    {
        $this->checkPermission('write');
        $this->activateTabs('content', 'id_content_ordering');
        
        include_once './Modules/WebResource/classes/class.ilWebResourceLinkTableGUI.php';
        $table = new ilWebResourceLinkTableGUI($this, 'sort', true);
        $table->parse();
        
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.webr_view.html', 'Modules/WebResource');
        $this->showToolbar('ACTION_BUTTONS');
        $this->tpl->setVariable('LINK_TABLE', $table->getHTML());
    }
    
    /**
     * Save nmanual sorting
     * @return
     */
    protected function saveSorting()
    {
        $this->checkPermission('write');
        
        include_once './Services/Container/classes/class.ilContainerSorting.php';
        $sort = ilContainerSorting::_getInstance($this->object->getId());
        $sort->savePost((array) $_POST['position']);
        
        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->view();
    }
    
    
    /**
     * Show toolbar
     * @param string $a_tpl_var Name of template variable
     * @return
     */
    protected function showToolbar($a_tpl_var)
    {
        global $DIC;

        include_once './Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
        $tool = new ilToolbarGUI();
        $tool->setFormAction($this->ctrl->getFormAction($this));

        $f = $DIC->ui()->factory();
        $r = $DIC->ui()->renderer();

        if (
            ilLinkResourceList::checkListStatus($this->object->getId()) &&
            $this->checkPermissionBool('write')
        ) {
            $tool->addButton(
                $this->lng->txt('webr_add'),
                $this->ctrl->getLinkTarget($this, 'addLink')
            );
        }
        elseif ($this->checkPermissionBool('write')) {
            $modal = $this->getLinkToListModal();
            $button = $f->button()->standard($this->lng->txt('webr_set_to_list'), '#')
                ->withOnClick($modal->getShowSignal());

            $this->tpl->setVariable("MODAL", $r->render([$modal]));
            $tool->addComponent($button);
        }

        $download_button = $f->button()->standard(
            $this->lng->txt('export_html'),
            $this->ctrl->getLinkTarget($this, 'exportHTML')
        );
        $tool->addComponent($download_button);
        $this->tpl->setVariable($a_tpl_var, $tool->getHTML());
        return;
    }
    
    /**
     * Show delete confirmation screen
     * @return
     */
    protected function confirmDeleteLink()
    {
        $this->checkPermission('write');
        $this->activateTabs('content', 'id_content_view');
        
        $link_ids = array();

        if (is_array($_POST['link_ids'])) {
            $link_ids = $_POST['link_ids'];
        } elseif (isset($_GET['link_id'])) {
            $link_ids = array($_GET['link_id']);
        }

        if (!count($link_ids) > 0) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->view();
            return false;
        }
        
        include_once './Modules/WebResource/classes/class.ilLinkResourceItems.php';
        $links = new ilLinkResourceItems($this->object->getId());
        
        include_once 'Services/Utilities/classes/class.ilConfirmationGUI.php';
        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this, 'view'));
        $confirm->setHeaderText($this->lng->txt('webr_sure_delete_items'));
        $confirm->setConfirm($this->lng->txt('delete'), 'deleteLinks');
        $confirm->setCancel($this->lng->txt('cancel'), 'view');
        
        foreach ($link_ids as $link_id) {
            $link = $links->getItem($link_id);
            $confirm->addItem('link_ids[]', $link_id, $link['title']);
        }
        $this->tpl->setContent($confirm->getHTML());
    }
    
    /**
     * Delete links
     * @return
     */
    protected function deleteLinks()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        
        $this->checkPermission('write');
        
        include_once './Modules/WebResource/classes/class.ilLinkResourceItems.php';
        $links = new ilLinkResourceItems($this->object->getId());
        
        foreach ($_POST['link_ids'] as $link_id) {
            $links->delete($link_id);
        }
        ilUtil::sendSuccess($this->lng->txt('webr_deleted_items'), true);
        $ilCtrl->redirect($this, 'view');
    }
    
    /**
     * Deactivate links
     * @return
     */
    protected function deactivateLink()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        
        $this->checkPermission('write');
        
        include_once './Modules/WebResource/classes/class.ilLinkResourceItems.php';
        $links = new ilLinkResourceItems($this->object->getId());

        if (!$_GET['link_id']) {
            ilUtil::sendFailure($this->lng->txt('select_one'), true);
            $ilCtrl->redirect($this, 'view');
        }
        
        $links->setLinkId((int) $_GET['link_id']);
        $links->updateActive(false);
        
        ilUtil::sendSuccess($this->lng->txt('webr_inactive_success'), true);
        $ilCtrl->redirect($this, 'view');
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
        global $DIC;

        $ilTabs = $DIC['ilTabs'];

        if (!$this->checkPermissionBool('visible')) {
            $this->checkPermission('read');
        }
        $ilTabs->activateTab('id_info');

        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
        $info = new ilInfoScreenGUI($this);
        
        $info->enablePrivateNotes();
        
        // standard meta data
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());
        
        if ($this->id_type == self::WORKSPACE_NODE_ID) {
            $info->addProperty($this->lng->txt("perma_link"), $this->getPermanentLinkWidget());
        }
        
        // forward the command
        $this->ctrl->forwardCommand($info);
    }


    public function history()
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];
        
        $this->checkPermission('write');
        $ilTabs->activateTab('id_history');

        include_once("./Services/History/classes/class.ilHistoryTableGUI.php");
        $hist_gui = new ilHistoryTableGUI($this, "history", $this->object->getId(), $this->object->getType);
        $hist_gui->initTable();
        $this->tpl->setContent($hist_gui->getHTML());
    }
    
    /**
     *
     * @see		ilLinkCheckerGUIRowHandling::formatInvalidLinkArray()
     * @param	array Unformatted array
     * @return	array Formatted array
     * @access	public
     *
     */
    public function formatInvalidLinkArray(array $row)
    {
        $this->object->items_obj->readItem($row['page_id']);
        $row['title'] = $this->object->items_obj->getTitle();
    
        require_once 'Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php';
        $actions = new ilAdvancedSelectionListGUI();
        $actions->setSelectionHeaderClass('small');
        $actions->setItemLinkClass('xsmall');
        $actions->setListTitle($this->lng->txt('actions'));
        $actions->setId($row['page_id']);
        $this->ctrl->setParameter($this, 'link_id', $row['page_id']);
        $actions->addItem(
            $this->lng->txt('edit'),
            '',
            $this->ctrl->getLinkTarget($this, 'editLink')
        );
        $this->ctrl->clearParameters($this);
        $row['action_html'] = $actions->getHTML();
        
        return $row;
    }

    /**
     * Show link validation
     * @return
     */
    protected function linkChecker()
    {
        global $DIC;

        $ilias = $DIC['ilias'];
        $ilUser = $DIC['ilUser'];
        $tpl = $DIC['tpl'];
        $ilTabs = $DIC['ilTabs'];
        
        $this->checkPermission('write');
        $ilTabs->activateTab('id_link_check');

        $this->__initLinkChecker();
        $this->object->initLinkResourceItemsObject();
        
        require_once './Services/LinkChecker/classes/class.ilLinkCheckerTableGUI.php';
        
        $toolbar = new ilToolbarGUI();

        if ((bool) $ilias->getSetting('cron_web_resource_check')) {
            include_once './Services/LinkChecker/classes/class.ilLinkCheckNotify.php';
            include_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
            
            $chb = new ilCheckboxInputGUI($this->lng->txt('link_check_message_a'), 'link_check_message');
            $chb->setValue(1);
            $chb->setChecked((bool) ilLinkCheckNotify::_getNotifyStatus($ilUser->getId(), $this->object->getId()));
            $chb->setOptionTitle($this->lng->txt('link_check_message_b'));
            
            $toolbar->addInputItem($chb);
            $toolbar->addFormButton($this->lng->txt('save'), 'saveLinkCheck');
            $toolbar->setFormAction($this->ctrl->getLinkTarget($this, 'saveLinkCheck'));
        }

        $tgui = new ilLinkCheckerTableGUI($this, 'linkChecker');
        $tgui->setLinkChecker($this->link_checker_obj)
             ->setRowHandler($this)
             ->setRefreshButton($this->lng->txt('refresh'), 'refreshLinkCheck');

        return $tpl->setContent($tgui->prepareHTML()->getHTML() . $toolbar->getHTML());
    }
    
    public function saveLinkCheck()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilUser = $DIC['ilUser'];

        include_once './Services/LinkChecker/classes/class.ilLinkCheckNotify.php';

        $link_check_notify = new ilLinkCheckNotify($ilDB);
        $link_check_notify->setUserId($ilUser->getId());
        $link_check_notify->setObjId($this->object->getId());

        if ($_POST['link_check_message']) {
            ilUtil::sendSuccess($this->lng->txt('link_check_message_enabled'));
            $link_check_notify->addNotifier();
        } else {
            ilUtil::sendSuccess($this->lng->txt('link_check_message_disabled'));
            $link_check_notify->deleteNotifier();
        }
        $this->linkChecker();

        return true;
    }
        


    public function refreshLinkCheck()
    {
        $this->__initLinkChecker();
        $this->object->initLinkResourceItemsObject();

        // Set all link to valid. After check invalid links will be set to invalid
        $this->object->items_obj->updateValidByCheck();
        
        foreach ($this->link_checker_obj->checkWebResourceLinks() as $invalid) {
            $this->object->items_obj->readItem($invalid['page_id']);
            $this->object->items_obj->setActiveStatus(false);
            $this->object->items_obj->setValidStatus(false);
            $this->object->items_obj->update(false);
        }
        
        $this->object->items_obj->updateLastCheck();
        ilUtil::sendSuccess($this->lng->txt('link_checker_refreshed'));

        $this->linkChecker();

        return true;
    }

    public function __initLinkChecker()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        include_once './Services/LinkChecker/classes/class.ilLinkChecker.php';

        $this->link_checker_obj = new ilLinkChecker($ilDB, false);
        $this->link_checker_obj->setObjId($this->object->getId());

        return true;
    }
    
    
    /**
     * Activate tab and subtabs
     * @param string $a_active_tab
     * @param string $a_active_subtab [optional]
     * @return
     */
    protected function activateTabs($a_active_tab, $a_active_subtab = '')
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $ilTabs = $DIC['ilTabs'];
        $lng = $DIC['lng'];
        
        switch ($a_active_tab) {
            case 'content':
                if ($this->checkPermissionBool('write')) {
                    $this->lng->loadLanguageModule('cntr');
                    
                    $this->ctrl->setParameter($this, 'switch_mode', self::VIEW_MODE_VIEW);
                    $ilTabs->addSubTab(
                        'id_content_view',
                        $lng->txt('view'),
                        $this->ctrl->getLinkTarget($this, 'switchViewMode')
                    );
                    $this->ctrl->setParameter($this, 'switch_mode', self::VIEW_MODE_MANAGE);
                    $ilTabs->addSubTab(
                        'id_content_manage',
                        $lng->txt('cntr_manage'),
                        $this->ctrl->getLinkTarget($this, 'switchViewMode')
                    );
                    include_once './Modules/WebResource/classes/class.ilLinkResourceItems.php';
                    include_once './Services/Container/classes/class.ilContainerSortingSettings.php';
                    include_once './Services/Container/classes/class.ilContainer.php';
                    if ((ilLinkResourceItems::lookupNumberOfLinks($this->object->getId()) > 1)
                        and ilContainerSortingSettings::_lookupSortMode($this->object->getId()) == ilContainer::SORT_MANUAL) {
                        $this->ctrl->setParameter($this, 'switch_mode', self::VIEW_MODE_SORT);
                        $ilTabs->addSubTab(
                            'id_content_ordering',
                            $this->lng->txt('cntr_ordering'),
                            $this->ctrl->getLinkTarget($this, 'switchViewMode')
                        );
                    }
                    
                    $ilCtrl->clearParameters($this);
                    $ilTabs->activateSubTab($a_active_subtab);
                }
        }
        
        $ilTabs->activateTab('id_content');
    }
    
    
    /**
    * get tabs
    * @access	public
    */
    public function setTabs()
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];
        $lng = $DIC['lng'];
        $ilHelp = $DIC['ilHelp'];
        
        $ilHelp->setScreenIdComponent("webr");
        
        if ($this->checkPermissionBool('read')) {
            $ilTabs->addTab(
                "id_content",
                $lng->txt("content"),
                $this->ctrl->getLinkTarget($this, "view")
            );
        }
        
        if (
            $this->checkPermissionBool('visible') ||
            $this->checkPermissionBool('read')
        ) {
            $ilTabs->addTab(
                "id_info",
                $lng->txt("info_short"),
                $this->ctrl->getLinkTarget($this, "infoScreen")
            );
        }
        
        if ($this->checkPermissionBool('write') and !$this->getCreationMode()) {
            $ilTabs->addTab(
                "id_settings",
                $lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, "settings")
            );
        }

        if ($this->checkPermissionBool('write')) {
            $ilTabs->addTab(
                "id_history",
                $lng->txt("history"),
                $this->ctrl->getLinkTarget($this, "history")
            );
        }

        if ($this->checkPermissionBool('write')) {
            // Check if pear library is available
            $ilTabs->addTab(
                "id_link_check",
                $lng->txt("link_check"),
                $this->ctrl->getLinkTarget($this, "linkChecker")
            );
        }

        if ($this->checkPermissionBool('write')) {
            include_once "Services/Object/classes/class.ilObjectMetaDataGUI.php";
            $mdgui = new ilObjectMetaDataGUI($this->object);
            $mdtab = $mdgui->getTab();
            if ($mdtab) {
                $ilTabs->addTab(
                    "id_meta_data",
                    $lng->txt("meta_data"),
                    $mdtab
                );
            }
        }

        if ($this->checkPermissionBool('write')) {
            $ilTabs->addTab(
                'export',
                $this->lng->txt('export'),
                $this->ctrl->getLinkTargetByClass('ilexportgui', '')
            );
        }

        // will add permission tab if needed
        parent::setTabs();
    }

    // PRIVATE
    public function __prepareOutput()
    {
        // output objects
        // $this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
        // $this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

        $this->tpl->setLocator();

        // output message
        if ($this->message) {
            ilUtil::sendInfo($this->message);
        }

        // display infopanel if something happened
        ilUtil::infoPanel();
        ;
    }

    public function addLocatorItems()
    {
        global $DIC;

        $ilLocator = $DIC['ilLocator'];

        if (is_object($this->object)) {
            $ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this), "", $this->object->getRefId(), "webr");
        }
    }

    protected function handleSubItemLinks($a_target)
    {
        // #15647 - handle internal links
        include_once "Services/Form/classes/class.ilFormPropertyGUI.php";
        include_once "Services/Form/classes/class.ilLinkInputGUI.php";

        if (ilLinkInputGUI::isInternalLink($a_target)) {
            include_once("./Services/Link/classes/class.ilLink.php");
            
            // #10612
            $parts = explode("|", $a_target);

            if ($parts[0] == 'wpage') {
                return ilLink::_getStaticLink(
                    0,
                    'wiki',
                    true,
                    '&target=wiki_wpage_' . $parts[1]
                );
            }
            
            if ($parts[0] == "term") {
                // #16894
                return ilLink::_getStaticLink(
                    0,
                    "git",
                    true,
                    "&target=git_" . $parts[1]
                );
            }
            
            if ($parts[0] == "page") {
                $parts[0] = "pg";
            }
            
            $a_target = ilLink::_getStaticLink($parts[1], $parts[0]);
        }
        
        return $a_target;
    }
    
    public function callDirectLink()
    {
        $obj_id = $this->object->getId();

        include_once './Modules/WebResource/classes/class.ilLinkResourceItems.php';
        if (ilLinkResourceItems::_isSingular($obj_id)) {
            $url = ilLinkResourceItems::_getFirstLink($obj_id);
            if ($url["target"]) {
                $url["target"] = $this->handleSubItemLinks($url["target"]);

                include_once './Modules/WebResource/classes/class.ilParameterAppender.php';
                if (ilParameterAppender::_isEnabled()) {
                    $url = ilParameterAppender::_append($url);
                }

                $this->redirectToLink($this->ref_id, $obj_id, $url["target"]);
            }
        }
    }
    
    public function callLink()
    {
        if ($_REQUEST["link_id"]) {
            $obj_id = $this->object->getId();
            
            include_once './Modules/WebResource/classes/class.ilLinkResourceItems.php';
            $items = new ilLinkResourceItems($obj_id);
            $item = $items->getItem($_REQUEST["link_id"]);
            if ($item["target"]) {
                $item["target"] = $this->handleSubItemLinks($item["target"]);
                
                include_once './Modules/WebResource/classes/class.ilParameterAppender.php';
                if (ilParameterAppender::_isEnabled()) {
                    $item = ilParameterAppender::_append($item);
                }
                ilLoggerFactory::getLogger('webr')->debug('Redirecting to: ' . $item['target']);
                $this->redirectToLink($this->ref_id, $obj_id, $item["target"]);
            }
        }
    }
    
    protected function redirectToLink($a_ref_id, $a_obj_id, $a_url)
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        
        if ($a_url) {
            require_once('Services/Tracking/classes/class.ilChangeEvent.php');
            ilChangeEvent::_recordReadEvent(
                "webr",
                $a_ref_id,
                $a_obj_id,
                $ilUser->getId()
            );
            
            ilUtil::redirect($a_url);
        }
    }
        
    public function exportHTML()
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        
        $tpl = new ilTemplate("tpl.export_html.html", true, true, "Modules/WebResource");
        
        include_once './Modules/WebResource/classes/class.ilLinkResourceItems.php';
        $items = new ilLinkResourceItems($this->object->getId());
        foreach ($items->getAllItems() as $item) {
            if (!$item["active"]) {
                continue;
            }
            
            $target = $this->handleSubItemLinks($item["target"]);
            
            $tpl->setCurrentBlock("link_bl");
            $tpl->setVariable("LINK_URL", $target);
            $tpl->setVariable("LINK_TITLE", $item["title"]);
            $tpl->setVariable("LINK_DESC", $item["description"]);
            $tpl->setVariable("LINK_CREATE", $item["create_date"]);
            $tpl->setVariable("LINK_UPDATE", $item["last_update"]);
            $tpl->parseCurrentBlock();
        }
        
        $tpl->setVariable("CREATE_DATE", $this->object->getCreateDate());
        $tpl->setVariable("LAST_UPDATE", $this->object->getLastUpdateDate());
        $tpl->setVariable("TXT_TITLE", $this->object->getTitle());
        $tpl->setVariable("TXT_DESC", $this->object->getLongDescription());
        
        $tpl->setVariable("INST_ID", ($ilSetting->get('short_inst_name') != "")
            ? $ilSetting->get('short_inst_name')
            : "ILIAS");
        
        ilUtil::deliverData($tpl->get(), "bookmarks.html");
    }

    public static function _goto($a_target, $a_additional = null)
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $lng = $DIC['lng'];
                
        if ($a_additional && substr($a_additional, -3) == "wsp") {
            $_GET["baseClass"] = "ilsharedresourceGUI";
            $_GET["wsp_id"] = $a_target;
            include("ilias.php");
            exit;
        }

        // Will be replaced in future releases by ilAccess::checkAccess()
        if ($ilAccess->checkAccess("read", "", $a_target)) {
            ilUtil::redirect("ilias.php?baseClass=ilLinkResourceHandlerGUI&ref_id=$a_target");
        } else {
            // to do: force flat view
            if ($ilAccess->checkAccess("visible", "", $a_target)) {
                ilUtil::redirect("ilias.php?baseClass=ilLinkResourceHandlerGUI&ref_id=" . $a_target . "&cmd=infoScreen");
            } else {
                if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
                    ilUtil::sendFailure(sprintf(
                        $lng->txt("msg_no_perm_read_item"),
                        ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
                    ), true);
                    ilObjectGUI::_gotoRepositoryRoot();
                }
            }
        }

        $ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
    }
} // END class.ilObjLinkResource
