<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilObjCmiXapiGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/CmiXapi
 *
 * @ilCtrl_Calls ilObjCmiXapiGUI: ilObjectCopyGUI
 * @ilCtrl_Calls ilObjCmiXapiGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjCmiXapiGUI: ilObjectMetaDataGUI
 * @ilCtrl_Calls ilObjCmiXapiGUI: ilPermissionGUI
 * @ilCtrl_Calls ilObjCmiXapiGUI: ilInfoScreenGUI
 * @ilCtrl_Calls ilObjCmiXapiGUI: ilLearningProgressGUI
 * @ilCtrl_Calls ilObjCmiXapiGUI: ilCmiXapiRegistrationGUI
 * @ilCtrl_Calls ilObjCmiXapiGUI: ilCmiXapiLaunchGUI
 * @ilCtrl_Calls ilObjCmiXapiGUI: ilCmiXapiSettingsGUI
 * @ilCtrl_Calls ilObjCmiXapiGUI: ilCmiXapiStatementsGUI
 * @ilCtrl_Calls ilObjCmiXapiGUI: ilCmiXapiScoringGUI
 * @ilCtrl_Calls ilObjCmiXapiGUI: ilCmiXapiExportGUI
 */
class ilObjCmiXapiGUI extends ilObject2GUI
{
    const TAB_ID_INFO = 'tab_info';
    const TAB_ID_SETTINGS = 'tab_settings';
    const TAB_ID_STATEMENTS = 'tab_statements';
    const TAB_ID_SCORING = 'tab_scoring';
    const TAB_ID_LEARNING_PROGRESS = 'learning_progress';
    const TAB_ID_METADATA = 'tab_metadata';
    const TAB_ID_EXPORT = 'tab_export';
    const TAB_ID_PERMISSIONS = 'perm_settings';
    
    const CMD_INFO_SCREEN = 'infoScreen';
    const CMD_FETCH_XAPI_STATEMENTS = 'fetchXapiStatements';
    
    const DEFAULT_CMD = self::CMD_INFO_SCREEN;

    const NEW_OBJ_TITLE = "";
    
    /**
     * @var ilObjCmiXapi
     */
    public $object;
    
    /**
     * @var ilCmiXapiAccess
     */
    public $cmixAccess;
    
    public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        parent::__construct($a_id, $a_id_type, $a_parent_node_id);
        
        if ($this->object instanceof ilObjCmiXapi) {
            $this->cmixAccess = ilCmiXapiAccess::getInstance($this->object);
        }
        
        $DIC->language()->loadLanguageModule("cmix");
    }
    
    public function getType()
    {
        return 'cmix';
    }
    
    protected function initCreateForm($a_new_type)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setTarget("_top");
        $form->setFormAction($this->ctrl->getFormAction($this, "save"));
        $form->setTitle($this->lng->txt($a_new_type . "_new"));
        
        $form = $this->initDidacticTemplate($form);

        $title = new ilHiddenInputGUI('title', 'title');
        $title->setValue(self::NEW_OBJ_TITLE);
        $form->addItem($title);

        $type = new ilRadioGroupInputGUI('Type', 'content_type');
        $type->setRequired(true);
        
        $typeLearningModule = new ilRadioOption($this->lng->txt('cmix_add_cmi5_lm'), ilObjCmiXapi::CONT_TYPE_CMI5);
        $typeLearningModule->setInfo($this->lng->txt('cmix_add_cmi5_lm_info'));
        $type->addOption($typeLearningModule);
        
        $typeGenericModule = new ilRadioOption($this->lng->txt('cmix_add_xapi_standard_object'), ilObjCmiXapi::CONT_TYPE_GENERIC);
        $typeGenericModule->setInfo($this->lng->txt('cmix_add_xapi_standard_object_info'));
        $type->addOption($typeGenericModule);

        $form->addItem($type);
        
        $item = new ilRadioGroupInputGUI($this->lng->txt('cmix_add_lrs_type'), 'lrs_type_id');
        $item->setRequired(true);
        $types = ilCmiXapiLrsTypeList::getTypesData(false, ilCmiXapiLrsType::AVAILABILITY_CREATE);
        foreach ($types as $type) {
            $option = new ilRadioOption($type['title'], $type['type_id'], $type['description']);
            $item->addOption($option);
        }
        #$item->setValue($this->object->typedef->getTypeId());
        $form->addItem($item);

        $source = new ilRadioGroupInputGUI($this->lng->txt('cmix_add_source'), 'source_type');
        $source->setRequired(true);
        
        $srcRemoteContent = new ilRadioOption($this->lng->txt('cmix_add_source_url'), 'resource');
        $srcRemoteContent->setInfo($this->lng->txt('cmix_add_source_url_info'));
        $source->addOption($srcRemoteContent);
        
        $srcUploadContent = new ilRadioOption($this->lng->txt('cmix_add_source_local_dir'), 'upload');
        $srcUploadContent->setInfo($this->lng->txt('cmix_add_source_local_dir_info'));
        $source->addOption($srcUploadContent);
        
        $srcUpload = new ilFileInputGUI($this->lng->txt("select_file"), "uploadfile");
        $srcUpload->setAllowDeletion(false);
        $srcUpload->setSuffixes(['zip', 'xml']);
        $srcUpload->setRequired(true);
        $srcUploadContent->addSubItem($srcUpload);
        
        if (ilUploadFiles::_getUploadDirectory()) {
            $srcServerContent = new ilRadioOption($this->lng->txt('cmix_add_source_upload_dir'), 'server');
            $srcServerContent->setInfo($this->lng->txt('cmix_add_source_upload_dir_info'));
            $source->addOption($srcServerContent);
            
            $options = ['' => $this->lng->txt('cmix_add_source_upload_select')];
            
            foreach (ilUploadFiles::_getUploadFiles() as $file) {
                $options[$file] = $file;
            }
            
            $srcSelect = new ilSelectInputGUI($this->lng->txt("select_file"), "serverfile");
            $srcSelect->setOptions($options);
            $srcServerContent->addSubItem($srcSelect);
        }
        
        $srcExternalApp = new ilRadioOption($this->lng->txt('cmix_add_source_external_app'), 'external');
        $srcExternalApp->setInfo($this->lng->txt('cmix_add_source_external_app_info'));
        $source->addOption($srcExternalApp);
        
        $form->addItem($source);
        
        $form->addCommandButton("save", $this->lng->txt($a_new_type . "_add"));
        $form->addCommandButton("cancel", $this->lng->txt("cancel"));
        
        return $form;
    }
    
    protected function afterSave(ilObject $newObject)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        /* @var ilObjCmiXapi $newObject */
        $form = $this->initCreateForm($newObject->getType());
        
        if ($form->checkInput()) {
            $newObject->setContentType($form->getInput('content_type'));
            
            $newObject->setLrsTypeId($form->getInput('lrs_type_id'));
            $newObject->initLrsType();
            
            $newObject->setPrivacyIdent($newObject->getLrsType()->getPrivacyIdent());
            $newObject->setPrivacyName($newObject->getLrsType()->getPrivacyName());
            
            switch ($form->getInput('source_type')) {
                case 'resource': // remote resource

                    $newObject->setTitle($form->getInput('title'));
                    $newObject->setSourceType(ilObjCmiXapi::SRC_TYPE_REMOTE);
                    break;
                    
                case 'upload': // upload from local client

                    try {
                        $uploadImporter = new ilCmiXapiContentUploadImporter($newObject);
                        $uploadImporter->importFormUpload($form->getItemByPostVar('uploadfile'));
                        
                        $newObject->setSourceType(ilObjCmiXapi::SRC_TYPE_LOCAL);
                    } catch (ilCmiXapiInvalidUploadContentException $e) {
                        $form->getItemByPostVar('uploadfile')->setAlert($e->getMessage());
                        ilUtil::sendFailure('something went wrong!', true);
                        $DIC->ctrl()->redirectByClass(self::class, 'create');
                    }
                    
                    break;
                    
                case 'server': // from upload directory
                    
                    if (!ilUploadFiles::_getUploadDirectory()) {
                        throw new ilCmiXapiException('access denied!');
                    }
                    
                    $serverFile = $form->getInput('serverfile');
                    
                    if (!ilUploadFiles::_checkUploadFile($serverFile)) {
                        throw new ilCmiXapiException($DIC->language()->txt('upload_error_file_not_found'));
                    }
                    
                    $uploadImporter = new ilCmiXapiContentUploadImporter($newObject);
                    
                    $uploadImporter->importServerFile(implode(DIRECTORY_SEPARATOR, [
                        ilUploadFiles::_getUploadDirectory(), $serverFile
                    ]));
                    
                    $newObject->setSourceType(ilObjCmiXapi::SRC_TYPE_LOCAL);
                    
                    break;
                    
                case 'external':
                    
                    $newObject->setSourceType(ilObjCmiXapi::SRC_TYPE_EXTERNAL);
                    $newObject->setBypassProxyEnabled(true);
                    break;
            }
            
            $newObject->save();
            
            $this->initMetadata($newObject);
            
            $DIC->ctrl()->redirectByClass(ilCmiXapiSettingsGUI::class);
        }
        
        throw new ilCmiXapiException('invalid creation form submit!');
    }
    
    public function initMetadata(ilObjCmiXapi $object)
    {
        $metadata = new ilMD($object->getId(), $object->getId(), $object->getType());
        
        $generalMetadata = $metadata->getGeneral();
        
        if (!$generalMetadata) {
            $generalMetadata = $metadata->addGeneral();
        }
        
        $generalMetadata->setTitle($object->getTitle());
        $generalMetadata->save();
        
        $id = $generalMetadata->addIdentifier();
        $id->setCatalog('ILIAS');
        $id->setEntry('il__' . $object->getType() . '_' . $object->getId());
        $id->save();
    }
    
    protected function initHeaderAction($a_sub_type = null, $a_sub_id = null)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $return = parent::initHeaderAction($a_sub_type, $a_sub_id);
        
        if ($this->creation_mode) {
            return $return;
        }

        $validator = new ilCertificateDownloadValidator();
        if ($validator->isCertificateDownloadable((int) $DIC->user()->getId(), (int) $this->object->getId())) {
            $certLink = $DIC->ctrl()->getLinkTargetByClass(
                [ilObjCmiXapiGUI::class, ilCmiXapiSettingsGUI::class],
                ilCmiXapiSettingsGUI::CMD_DELIVER_CERTIFICATE
            );
            
            $DIC->language()->loadLanguageModule('certificate');
            
            $return->addCustomCommand($certLink, 'download_certificate');
            
            $return->addHeaderIcon(
                'cert_icon',
                ilUtil::getImagePath('icon_cert.svg'),
                $DIC->language()->txt('download_certificate'),
                null,
                null,
                $certLink
            );
        }
        
        return $return;
    }
    
    public static function _goto($a_target)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $err = $DIC['ilErr']; /* @var ilErrorHandling $err */
        $ctrl = $DIC->ctrl();
        $request = $DIC->http()->request();
        $access = $DIC->access();
        $lng = $DIC->language();

        $targetParameters = explode('_', $a_target);
        $id = (int) $targetParameters[0];

        if ($id <= 0) {
            $err->raiseError($lng->txt('msg_no_perm_read'), $err->FATAL);
        }

        if ($access->checkAccess('read', '', $id)) {
            $ctrl->setTargetScript('ilias.php');
            $ctrl->initBaseClass(ilRepositoryGUI::class);
            $ctrl->setParameterByClass(ilObjCmiXapiGUI::class, 'ref_id', $id);
            if (isset($request->getQueryParams()['gotolp'])) {
                $ctrl->setParameterByClass(ilObjCmiXapiGUI::class, 'gotolp', 1);
            }
            $ctrl->redirectByClass([ilRepositoryGUI::class, ilObjCmiXapiGUI::class]);
        } elseif ($access->checkAccess('visible', '', $id)) {
            ilObjectGUI::_gotoRepositoryNode($id, 'infoScreen');
        } elseif ($access->checkAccess('read', '', ROOT_FOLDER_ID)) {
            ilUtil::sendInfo(
                sprintf(
                    $DIC->language()->txt('msg_no_perm_read_item'),
                    ilObject::_lookupTitle(ilObject::_lookupObjId($id))
                ),
                true
            );

            ilObjectGUI::_gotoRepositoryRoot();
        }

        $err->raiseError($DIC->language()->txt("msg_no_perm_read_lm"), $err->FATAL);
    }
    
    public function executeCommand()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        // TODO: access checks (!)
        
        if (!$this->creation_mode) {
            $link = ilLink::_getLink($this->object->getRefId(), $this->object->getType());
            $navigationHistory = $DIC['ilNavigationHistory']; /* @var ilNavigationHistory $navigationHistory */
            $navigationHistory->addItem($this->object->getRefId(), $link, $this->object->getType());
            $this->trackObjectReadEvent();
        }
        
        $this->prepareOutput();
        $this->addHeaderAction();
        
        
        switch ($DIC->ctrl()->getNextClass()) {
            case strtolower(ilObjectCopyGUI::class):
                
                $gui = new ilObjectCopyGUI($this);
                $gui->setType($this->getType());
                $DIC->ctrl()->forwardCommand($gui);
                
                break;

            case strtolower(ilCommonActionDispatcherGUI::class):
                
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                
                break;

            case strtolower(ilLearningProgressGUI::class):
                
                $DIC->tabs()->activateTab(self::TAB_ID_LEARNING_PROGRESS);
                
                $gui = new ilLearningProgressGUI(
                    ilLearningProgressGUI::LP_CONTEXT_REPOSITORY,
                    $this->object->getRefId()
                );
                
                $DIC->ctrl()->forwardCommand($gui);
                
                break;
            
            case strtolower(ilObjectMetaDataGUI::class):
                
                $DIC->tabs()->activateTab(self::TAB_ID_METADATA);
                
                $gui = new ilObjectMetaDataGUI($this->object);
                $DIC->ctrl()->forwardCommand($gui);
                break;

            case strtolower(ilPermissionGUI::class):
                
                $DIC->tabs()->activateTab(self::TAB_ID_PERMISSIONS);
                
                $gui = new ilPermissionGUI($this);
                $DIC->ctrl()->forwardCommand($gui);
                
                break;
                
            case strtolower(ilCmiXapiSettingsGUI::class):
                
                $DIC->tabs()->activateTab(self::TAB_ID_SETTINGS);
                
                $gui = new ilCmiXapiSettingsGUI($this->object);
                $DIC->ctrl()->forwardCommand($gui);
                
                break;
            
            case strtolower(ilCmiXapiStatementsGUI::class):
                
                $DIC->tabs()->activateTab(self::TAB_ID_STATEMENTS);
                
                $gui = new ilCmiXapiStatementsGUI($this->object);
                $DIC->ctrl()->forwardCommand($gui);
                
                break;

            case strtolower(ilCmiXapiScoringGUI::class):

                $DIC->tabs()->activateTab(self::TAB_ID_SCORING);

                $gui = new ilCmiXapiScoringGUI($this->object);
                $DIC->ctrl()->forwardCommand($gui);

                break;

            case strtolower(ilCmiXapiExportGUI::class):
                
                $DIC->tabs()->activateTab(self::TAB_ID_EXPORT);
                
                $gui = new ilCmiXapiExportGUI($this);
                $DIC->ctrl()->forwardCommand($gui);

                break;
            
            case strtolower(ilCmiXapiRegistrationGUI::class):
                
                $DIC->tabs()->activateTab(self::TAB_ID_INFO);
                
                $gui = new ilCmiXapiRegistrationGUI($this->object);
                $DIC->ctrl()->forwardCommand($gui);
                
                break;
            
            case strtolower(ilCmiXapiLaunchGUI::class):
                
                $gui = new ilCmiXapiLaunchGUI($this->object);
                $DIC->ctrl()->forwardCommand($gui);
                
                break;

            default:
                
                $command = $DIC->ctrl()->getCmd(self::DEFAULT_CMD);
                $this->{$command}();
        }
    }
    
    protected function setTabs()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $DIC->tabs()->addTab(
            self::TAB_ID_INFO,
            $DIC->language()->txt(self::TAB_ID_INFO),
            $DIC->ctrl()->getLinkTargetByClass(self::class)
        );
        
        if ($this->cmixAccess->hasWriteAccess()) {
            $DIC->tabs()->addTab(
                self::TAB_ID_SETTINGS,
                $DIC->language()->txt(self::TAB_ID_SETTINGS),
                $DIC->ctrl()->getLinkTargetByClass(ilCmiXapiSettingsGUI::class)
            );
        }
        
        if ($this->cmixAccess->hasStatementsAccess()) {
            $DIC->tabs()->addTab(
                self::TAB_ID_STATEMENTS,
                $DIC->language()->txt(self::TAB_ID_STATEMENTS),
                $DIC->ctrl()->getLinkTargetByClass(ilCmiXapiStatementsGUI::class)
            );
        }

        if ($this->cmixAccess->hasHighscoreAccess()) {
            $DIC->tabs()->addTab(
                self::TAB_ID_SCORING,
                $DIC->language()->txt(self::TAB_ID_SCORING),
                $DIC->ctrl()->getLinkTargetByClass(ilCmiXapiScoringGUI::class)
            );
        }
        
        if ($this->cmixAccess->hasLearningProgressAccess()) {
            $DIC->tabs()->addTab(
                self::TAB_ID_LEARNING_PROGRESS,
                $DIC->language()->txt(self::TAB_ID_LEARNING_PROGRESS),
                $DIC->ctrl()->getLinkTargetByClass(ilLearningProgressGUI::class)
            );
        }
        
        if ($this->cmixAccess->hasWriteAccess()) {
            $gui = new ilObjectMetaDataGUI($this->object);
            $link = $gui->getTab();
            
            if (strlen($link)) {
                $DIC->tabs()->addTab(
                    self::TAB_ID_METADATA,
                    $DIC->language()->txt('meta_data'),
                    $link
                );
            }
        }
        
        if ($this->cmixAccess->hasWriteAccess()) {
            $DIC->tabs()->addTab(
                self::TAB_ID_EXPORT,
                $DIC->language()->txt(self::TAB_ID_EXPORT),
                $DIC->ctrl()->getLinkTargetByClass(ilCmiXapiExportGUI::class)
            );
        }
        
        if ($this->cmixAccess->hasEditPermissionsAccess()) {
            $DIC->tabs()->addTab(
                self::TAB_ID_PERMISSIONS,
                $DIC->language()->txt(self::TAB_ID_PERMISSIONS),
                $DIC->ctrl()->getLinkTargetByClass(ilPermissionGUI::class, 'perm')
            );
        }
        
        if (defined('DEVMODE') && DEVMODE) {
            $DIC->tabs()->addTab(
                'debug',
                'DEBUG',
                $DIC->ctrl()->getLinkTarget($this, 'debug')
            );
        }
    }
    
    protected function debug()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $DIC->tabs()->activateTab('debug');
        
        $filter = new ilCmiXapiStatementsReportFilter();
        $filter->setActivityId($this->object->getActivityId());
        
        $linkBuilder = new ilCmiXapiHighscoreReportLinkBuilder(
            $this->object->getId(),
            $this->object->getLrsType()->getLrsEndpointStatementsAggregationLink(),
            $filter
        );
        
        $request = new ilCmiXapiHighscoreReportRequest(
            $this->object->getLrsType()->getBasicAuth(),
            $linkBuilder
        );
        
        try {
            $report = $request->queryReport($this->object->getId());
            
            $DIC->ui()->mainTemplate()->setContent(
                $report->getResponseDebug()
            );
        } catch (Exception $e) {
            ilUtil::sendFailure($e->getMessage());
        }
        //ilUtil::sendSuccess('Object ID: '.$this->object->getId());
        ilUtil::sendInfo($linkBuilder->getPipelineDebug());
        ilUtil::sendQuestion('<pre>' . print_r($report->getTableData(), 1) . '</pre>');
    }
    
    public function addLocatorItems()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $locator = $DIC['ilLocator']; /* @var ilLocatorGUI $locator */
        $locator->addItem(
            $this->object->getTitle(),
            $this->ctrl->getLinkTarget($this, self::DEFAULT_CMD),
            "",
            $_GET["ref_id"]
        );
    }
    
    protected function trackObjectReadEvent()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        ilChangeEvent::_recordReadEvent(
            $this->object->getType(),
            $this->object->getRefId(),
            $this->object->getId(),
            $DIC->user()->getId()
        );
    }
    
    public function infoScreen()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $DIC->tabs()->activateTab(self::TAB_ID_INFO);
        
        $DIC->ctrl()->setCmd("showSummary");
        $DIC->ctrl()->setCmdClass("ilinfoscreengui");
        $this->infoScreenForward();
    }
    
    public function infoScreenForward()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $ilErr = $DIC['ilErr']; /* @var ilErrorHandling $ilErr */
        
        if (!$this->checkPermissionBool("visible") && !$this->checkPermissionBool("read")) {
            $ilErr->raiseError($DIC->language()->txt("msg_no_perm_read"));
        }
        
        $this->handleAvailablityMessage();
        $this->initInfoScreenToolbar();
        
        $info = new ilInfoScreenGUI($this);
        
        $info->enablePrivateNotes();
        
        if ($this->checkPermissionBool("read")) {
            $info->enableNews();
        }
        
        $info->enableNewsEditing(false);

        if ($this->checkPermissionBool("write")) {
            $news_set = new ilSetting("news");
            $enable_internal_rss = $news_set->get("enable_rss_for_internal");
            
            if ($enable_internal_rss) {
                $info->setBlockProperty("news", "settings", true);
                $info->setBlockProperty("news", "public_notifications_option", true);
            }
        }
        
        if (DEVMODE) {
            // Development Info
            $info->addSection('DEVMODE Info');
            $info->addProperty('Local Object ID', $this->object->getId());
            $info->addProperty('Current User ID', $DIC->user()->getId());
        }
        
        // standard meta data
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());
        
        // Info about privacy
        if ($this->object->isSourceTypeExternal()) {
            $info->addSection($DIC->language()->txt("cmix_info_privacy_section"));
        } else {
            $info->addSection($DIC->language()->txt("cmix_info_privacy_section_launch"));
        }

        $info->addProperty($DIC->language()->txt('cmix_lrs_type'), $this->object->getLrsType()->getTitle());

        if ($this->object->isSourceTypeExternal()) {
            $cmixUser = new ilCmiXapiUser($this->object->getId(), $DIC->user()->getId(), $this->object->getPrivacyIdent());
            if ($cmixUser->getUsrIdent()) {
                $info->addProperty(
                    $DIC->language()->txt("conf_user_registered_mail"),
                    $cmixUser->getUsrIdent()
                );
            }
        } else {
            $info->addProperty(
                $DIC->language()->txt("conf_privacy_name"),
                $DIC->language()->txt('conf_privacy_name_' . self::getPrivacyNameString($this->object->getPrivacyName()))
            );
            
            $info->addProperty(
                $DIC->language()->txt("conf_privacy_ident"),
                $DIC->language()->txt('conf_privacy_ident_' . self::getPrivacyIdentString($this->object->getPrivacyIdent()))
            );
        }

        if ($this->object->getLrsType()->getExternalLrs()) {
            $info->addProperty(
                $DIC->language()->txt("cmix_info_external_lrs_label"),
                $DIC->language()->txt('cmix_info_external_lrs_info')
            );
        }

        if (strlen($this->object->getLrsType()->getPrivacyCommentDefault())) {
            $info->addProperty(
                $DIC->language()->txt("cmix_indication_to_user"),
                nl2br($this->object->getLrsType()->getPrivacyCommentDefault())
            );
        }
        
        // FINISHED INFO SCREEN, NOW FORWARD
        
        $this->ctrl->forwardCommand($info);
    }
    
    protected function initInfoScreenToolbar()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        if (!$this->object->getOfflineStatus() && $this->object->getLrsType()->isAvailable()) {
            // TODO : check if this is the correct query
            // p.e. switched to another privacyIdent before: user exists but not with the new privacyIdent
            // re_check for isSourceTypeExternal
            //$cmixUserExists = ilCmiXapiUser::exists($this->object->getId(), $DIC->user()->getId());
            
            if ($this->object->isSourceTypeExternal()) {
                $extCmiUserExists = ilCmiXapiUser::exists($this->object->getId(), $DIC->user()->getId());
                $registerButton = ilLinkButton::getInstance();
                
                if ($extCmiUserExists) {
                    $registerButton->setCaption('change_registration');
                } else {
                    $registerButton->setPrimary(true);
                    $registerButton->setCaption('create_registration');
                }
                
                $registerButton->setUrl($DIC->ctrl()->getLinkTargetByClass(
                    ilCmiXapiRegistrationGUI::class
                ));
                
                $DIC->toolbar()->addButtonInstance($registerButton);
            } else {
                $launchButton = ilLinkButton::getInstance();
                $launchButton->setPrimary(true);
                $launchButton->setCaption('launch');
                
                if ($this->object->getLaunchMethod() == ilObjCmiXapi::LAUNCH_METHOD_NEW_WIN) {
                    $launchButton->setTarget('_blank');
                }
                
                $launchButton->setUrl($DIC->ctrl()->getLinkTargetByClass(
                    ilCmiXapiLaunchGUI::class
                ));
                
                $DIC->toolbar()->addButtonInstance($launchButton);
            }
            
            /**
             * beware: ilCmiXapiUser::exists($this->object->getId(),$DIC->user()->getId());
             * this is not a valid query because if you switched privacyIdent mode before you will get 
             * an existing user without launched data like proxySuccess
             */
            $cmiUserExists = ilCmiXapiUser::exists($this->object->getId(),$DIC->user()->getId(),$this->object->getPrivacyIdent());

            if ($cmiUserExists) {
                $cmixUser = new ilCmiXapiUser($this->object->getId(), $DIC->user()->getId(), $this->object->getPrivacyIdent());
                
                if ($this->isFetchXapiStatementsRequired($cmixUser)) {
                    $fetchButton = ilLinkButton::getInstance();
                    $fetchButton->setCaption('fetch_xapi_statements');
                    
                    $fetchButton->setUrl($DIC->ctrl()->getLinkTarget(
                        $this,
                        self::CMD_FETCH_XAPI_STATEMENTS
                    ));
                    
                    $DIC->toolbar()->addButtonInstance($fetchButton);
                    
                    $this->sendLastFetchInfo($cmixUser);
                }
            }
        }
    }
    
    protected function handleAvailablityMessage()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        if ($this->object->getLrsType()->getAvailability() == ilCmiXapiLrsType::AVAILABILITY_NONE) {
            ilUtil::sendFailure($DIC->language()->txt('cmix_lrstype_not_avail_msg'));
        }
    }
    
    protected function isFetchXapiStatementsRequired(ilCmiXapiUser $cmixUser)
    {
        global $DIC;
        if ($this->object->getLaunchMode() != ilObjCmiXapi::LAUNCH_MODE_NORMAL) {
            return false;
        }
        
        if ($this->object->isBypassProxyEnabled()) {
            return true;
        }
        
        if (!$cmixUser->hasProxySuccess()) {
            return true;
        }
        
        return false;
    }
    
    protected function sendLastFetchInfo(ilCmiXapiUser $cmixUser)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        if (!$cmixUser->getFetchUntil()->get(IL_CAL_UNIX)) {
            $info = $DIC->language()->txt('xapi_statements_not_fetched_yet');
        } else {
            $info = $DIC->language()->txt('xapi_statements_last_fetch_date') . ' ' . ilDatePresentation::formatDate(
                $cmixUser->getFetchUntil()
            );
        }
        
        ilUtil::sendInfo($info);
    }
    
    protected function fetchXapiStatements()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $logger = ilLoggerFactory::getLogger($this->object->getType());
        
        if ($this->object->getLaunchMode() != ilObjCmiXapi::LAUNCH_MODE_NORMAL) {
            throw new ilCmiXapiException('access denied!');
        }
        
        $cmixUser = new ilCmiXapiUser($this->object->getId(), $DIC->user()->getId(), $this->object->getPrivacyIdent());
        
        $fetchedUntil = $cmixUser->getFetchUntil();
        $now = new ilCmiXapiDateTime(time(), IL_CAL_UNIX);
        
        $report = $this->getXapiStatementsReport($fetchedUntil, $now);
        
        if ($report->hasStatements()) {
            $evaluation = new ilXapiStatementEvaluation($logger, $this->object);
            $evaluation->evaluateReport($report);
            
            //$logger->debug('update lp for object (' . $this->object->getId() . ')');
            //ilLPStatusWrapper::_updateStatus($this->object->getId(), $DIC->user()->getId());
        }
        
        $cmixUser->setFetchUntil($now);
        $cmixUser->save();
        
        ilUtil::sendSuccess($DIC->language()->txt('xapi_statements_fetched_successfully'), true);
        $DIC->ctrl()->redirect($this, self::CMD_INFO_SCREEN);
    }
    
    protected function getXapiStatementsReport(ilCmiXapiDateTime $since, ilCmiXapiDateTime $until)
    {
        $filter = $this->buildReportFilter($since, $until);
        
        $linkBuilder = new ilCmiXapiStatementsReportLinkBuilder(
            $this->object->getId(),
            $this->object->getLrsType()->getLrsEndpointStatementsAggregationLink(),
            $filter
        );
        
        $request = new ilCmiXapiStatementsReportRequest(
            $this->object->getLrsType()->getBasicAuth(),
            $linkBuilder
        );
        
        return $request->queryReport($this->object->getId());
    }
    
    protected function buildReportFilter(ilCmiXapiDateTime $since, ilCmiXapiDateTime $until)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $filter = new ilCmiXapiStatementsReportFilter();
        
        $filter->setActor(new ilCmiXapiUser($this->object->getId(), $DIC->user()->getId()));
        $filter->setActivityId($this->object->getActivityId());
        
        $filter->setStartDate($since);
        $filter->setEndDate($until);
        
        $start = $filter->getStartDate()->get(IL_CAL_DATETIME);
        $end = $filter->getEndDate()->get(IL_CAL_DATETIME);
        ilLoggerFactory::getLogger($this->object->getType())->debug("use filter from ($start) until ($end)");
        
        return $filter;
    }
    
    public static function getPrivacyIdentString(int $ident)
    {
        switch ($ident) {
            case 0:
                return "il_uuid_user_id";
            case 1:
                return "il_uuid_ext_account";
            case 2:
                return "il_uuid_login";
            case 3:
                return "real_email";
            case 4:
                return "il_uuid_random";
        }
        return '';
    }

    public static function getPrivacyNameString(int $ident)
    {
        switch ($ident) {
            case 0:
                return "none";
            case 1:
                return "firstname";
            case 2:
                return "lastname";
            case 3:
                return "fullname";
        }
        return '';
    }
}
