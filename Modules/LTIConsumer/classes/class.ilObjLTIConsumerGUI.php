<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilObjLTIConsumerGUI
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Modules/LTIConsumer
 *
 * @ilCtrl_Calls ilObjLTIConsumerGUI: ilObjectCopyGUI
 * @ilCtrl_Calls ilObjLTIConsumerGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjLTIConsumerGUI: ilPermissionGUI
 * @ilCtrl_Calls ilObjLTIConsumerGUI: ilObjectMetaDataGUI
 * @ilCtrl_Calls ilObjLTIConsumerGUI: ilInfoScreenGUI
 * @ilCtrl_Calls ilObjLTIConsumerGUI: ilLearningProgressGUI
 * @ilCtrl_Calls ilObjLTIConsumerGUI: ilLTIConsumerSettingsGUI
 * @ilCtrl_Calls ilObjLTIConsumerGUI: ilLTIConsumerXapiStatementsGUI
 * @ilCtrl_Calls ilObjLTIConsumerGUI: ilLTIConsumerScoringGUI
 * @ilCtrl_Calls ilObjLTIConsumerGUI: ilLTIConsumerContentGUI
 */
class ilObjLTIConsumerGUI extends ilObject2GUI
{
    const CFORM_CUSTOM_NEW = 99;
    
    const TAB_ID_INFO = 'tab_info';
    const TAB_ID_CONTENT = 'tab_content';
    const TAB_ID_SETTINGS = 'tab_settings';
    const TAB_ID_STATEMENTS = 'tab_statements';
    const TAB_ID_SCORING = 'tab_scoring';
    const TAB_ID_METADATA = 'tab_metadata';
    const TAB_ID_LEARNING_PROGRESS = 'learning_progress';
    const TAB_ID_PERMISSIONS = 'perm_settings';
    
    const DEFAULT_CMD = 'launch';
    
    /**
     * @var ilObjLTIConsumer
     */
    public $object;
    
    /**
     * @var ilLTIConsumerAccess
     */
    protected $ltiAccess;
    
    public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        parent::__construct($a_id, $a_id_type, $a_parent_node_id);
        
        if ($this->object instanceof ilObjLTIConsumer) {
            $this->ltiAccess = new ilLTIConsumerAccess($this->object);
        }
        
        $DIC->language()->loadLanguageModule("lti");
        $DIC->language()->loadLanguageModule("rep");
    }
    
    public function getType()
    {
        return 'lti';
    }
    
    public function initCreationForms($a_new_type)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $forms = array(
            self::CFORM_NEW => $this->initCreateForm($a_new_type)
        );
        
        if (ilLTIConsumerAccess::hasCustomProviderCreationAccess()) {
            $forms[self::CFORM_CUSTOM_NEW] = $this->initCustomCreateForm($a_new_type);
        }
        
        //$forms[self::CFORM_IMPORT] = $this->initImportForm($a_new_type), // no import yet
        $forms[self::CFORM_CLONE] = $this->fillCloneTemplate(null, $a_new_type);
        
        return $forms;
    }
    
    public function initCreateForm($a_new_type)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $form = $this->buildProviderSelectionForm($a_new_type);
        
        $globalProviderList = new ilLTIConsumeProviderList();
        $globalProviderList->setAvailabilityFilter(ilLTIConsumeProvider::AVAILABILITY_CREATE);
        $globalProviderList->setScopeFilter(ilLTIConsumeProviderList::SCOPE_GLOBAL);
        
        $userProviderList = new ilLTIConsumeProviderList();
        $userProviderList->setAvailabilityFilter(ilLTIConsumeProvider::AVAILABILITY_CREATE);
        $userProviderList->setScopeFilter(ilLTIConsumeProviderList::SCOPE_USER);
        $userProviderList->setCreatorFilter($DIC->user()->getId());
        
        if ($form->getFilter('title')) {
            $globalProviderList->setTitleFilter($form->getFilter('title'));
            $userProviderList->setTitleFilter($form->getFilter('title'));
        }
        
        if ($form->getFilter('category')) {
            $globalProviderList->setCategoryFilter($form->getFilter('category'));
            $userProviderList->setCategoryFilter($form->getFilter('category'));
        }
        
        if ($form->getFilter('keyword')) {
            $globalProviderList->setKeywordFilter($form->getFilter('keyword'));
            $userProviderList->setKeywordFilter($form->getFilter('keyword'));
        }
        
        if ($form->getFilter('outcome')) {
            $globalProviderList->setHasOutcomeFilter(true);
            $userProviderList->setHasOutcomeFilter(true);
        }
        
        if ($form->getFilter('internal')) {
            $globalProviderList->setIsExternalFilter(false);
            $userProviderList->setIsExternalFilter(false);
        }
        
        if ($form->getFilter('with_key')) {
            $globalProviderList->setIsProviderKeyCustomizableFilter(false);
            $userProviderList->setIsProviderKeyCustomizableFilter(false);
        }

        $globalProviderList->load();
        $userProviderList->load();
        
        $form->setData(array_merge(
            $globalProviderList->getTableData(),
            $userProviderList->getTableData()
        ));
        
        return $form;
    }
    
    public function initCustomCreateForm($a_new_type)
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $provider = new ilLTIConsumeProvider();
        
        $form = new ilLTIConsumeProviderFormGUI($provider);
        
        $form->initForm($this->ctrl->getFormAction($this, "save"), '', '');
        
        $form->clearCommandButtons();
        $form->addCommandButton("saveCustom", $this->lng->txt($a_new_type . "_add_own_provider"));
        $form->addCommandButton("cancel", $this->lng->txt("cancel"));
        
        $form->setTitle($DIC->language()->txt($a_new_type . '_custom_new'));
        
        return $form;
    }
    
    protected function buildProviderSelectionForm($a_new_type)
    {
        return new ilLTIConsumerProviderSelectionFormTableGUI(
            $a_new_type,
            $this,
            'create',
            'applyProviderFilter',
            'resetProviderFilter'
        );
    }
    
    protected function applyProviderFilter()
    {
        $form = $this->buildProviderSelectionForm('');
        $form->applyFilter();
        $this->createObject();
    }
    
    protected function resetProviderFilter()
    {
        $form = $this->buildProviderSelectionForm('');
        $form->resetFilter();
        $this->createObject();
    }
    
    protected function createNewObject($newType, $title, $description)
    {
        $classname = "ilObj" . $this->objDefinition->getClassName($newType);

        $newObj = new $classname();
        $newObj->setType($newType);
        $newObj->setTitle($title);
        $newObj->setDescription($description);
        $newObj->create();
        
        $this->putObjectInTree($newObj);
        
        return $newObj;
    }
    
    public function saveCustom()
    {
        if (!ilLTIConsumerAccess::hasCustomProviderCreationAccess()) {
            throw new ilLtiConsumerException('permission denied!');
        }
        
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        $new_type = $_REQUEST["new_type"];
        $DIC->ctrl()->setParameter($this, "new_type", $new_type);
        
        $DIC->language()->loadLanguageModule($new_type);
        
        $form = $this->initCustomCreateForm($new_type);
        
        if ($form->checkInput()) {
            $DIC->ctrl()->setParameter($this, "new_type", "");
            
            // create object
            $newObj = $this->createNewObject(
                $new_type,
                $form->getInput('title'),
                $form->getInput('desc')
            );
            
            // apply didactic template?
            $dtpl = $this->getDidacticTemplateVar("dtpl");
            if ($dtpl) {
                $newObj->applyDidacticTemplate($dtpl);
            }
            
            // auto rating
            $this->handleAutoRating($newObj);

            $this->afterSave($newObj);

            return;
        }
        
        $form->setValuesByPost();
        
        $DIC->ui()->mainTemplate()->setContent($form->getHtml());
    }
    
    public function afterSave(ilObject $newObject)
    {
        /* @var ilObjLTIConsumer $newObject */
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        if (isset($_GET['provider_id']) && (int) $_GET['provider_id']) {
            $newObject->setProviderId((int) $_GET['provider_id']);
            $newObject->initProvider();
            $newObject->save();
            
            $newObject->setTitle($newObject->getProvider()->getTitle());
            $newObject->setMasteryScore($newObject->getProvider()->getMasteryScore());
            $newObject->update();
            
            $this->initMetadata($newObject);
            
            $DIC->ctrl()->redirectByClass(ilLTIConsumerSettingsGUI::class);
        }
        
        if (!ilLTIConsumerAccess::hasCustomProviderCreationAccess()) {
            throw new ilLtiConsumerException('permission denied!');
        }
        
        $form = $this->initCustomCreateForm($newObject->getType());
        
        if ($form->checkInput()) {
            $provider = new ilLTIConsumeProvider();
            $form->initProvider($provider);
            $provider->setAvailability(ilLTIConsumeProvider::AVAILABILITY_CREATE);
            $provider->setIsGlobal(false);
            $provider->setCreator($DIC->user()->getId());
            $provider->save();
            
            $newObject->setProviderId($provider->getId());
            $newObject->setProvider($provider);
            $newObject->save();
            
            $newObject->setTitle($provider->getTitle());
            $newObject->setMasteryScore($newObject->getProvider()->getMasteryScore());
            $newObject->update();
            
            $this->initMetadata($newObject);
            
            $DIC->ctrl()->redirectByClass(ilObjLTIConsumerGUI::class);
        }
        
        throw new ilLtiConsumerException(
            'form validation seems to not have worked in ilObjLTIConsumer::saveCustom()!'
        );
    }
    
    public function initMetadata(ilObjLTIConsumer $object)
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
        
        $keywords = $object->getProvider()->getKeywordsArray();
        ilMDKeyword::updateKeywords($generalMetadata, $keywords);
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
                [ilObjLTIConsumerGUI::class, ilLTIConsumerSettingsGUI::class],
                ilLTIConsumerSettingsGUI::CMD_DELIVER_CERTIFICATE
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
            $ctrl->setParameterByClass(ilObjLTIConsumerGUI::class, 'ref_id', $id);
            if (isset($request->getQueryParams()['gotolp'])) {
                $ctrl->setParameterByClass(ilObjLTIConsumerGUI::class, 'gotolp', 1);
            }
            $ctrl->redirectByClass([ilRepositoryGUI::class, ilObjLTIConsumerGUI::class]);
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
    
    /**
     * @throws ilCtrlException
     */
    public function executeCommand()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        
        // TODO: general access checks (!)

        if (!ilLTIConsumerContentGUI::isEmbeddedLaunchRequest()) {
            $this->prepareOutput();
            $this->addHeaderAction();
        }
        
        if (!$this->creation_mode) {
            $this->trackObjectReadEvent();
            
            if ($this->object->getProvider()->hasProviderIcon()) {
                $DIC->ui()->mainTemplate()->setTitleIcon(
                    $this->object->getProvider()->getProviderIcon()->getAbsoluteFilePath(),
                    'Icon ' . $this->object->getProvider()->getTitle()
                );
            }
            
            $link = ilLink::_getLink($this->object->getRefId(), $this->object->getType());
            $navigationHistory = $DIC['ilNavigationHistory']; /* @var ilNavigationHistory $navigationHistory */
            $navigationHistory->addItem($this->object->getRefId(), $link, $this->object->getType());
        }
        
        switch ($DIC->ctrl()->getNextClass()) {
            case strtolower(ilObjectCopyGUI::class):
                
                $gui = new ilObjectCopyGUI($this);
                $gui->setType($this->getType());
                $DIC->ctrl()->forwardCommand($gui);
                break;

            case strtolower(ilCommonActionDispatcherGUI::class):
                
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $DIC->ctrl()->forwardCommand($gui);
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
                
            case strtolower(ilLTIConsumerSettingsGUI::class):
                
                $DIC->tabs()->activateTab(self::TAB_ID_SETTINGS);
                
                $gui = new ilLTIConsumerSettingsGUI($this->object, $this->ltiAccess);
                $DIC->ctrl()->forwardCommand($gui);
                break;
            
            case strtolower(ilLTIConsumerXapiStatementsGUI::class):
                
                $DIC->tabs()->activateTab(self::TAB_ID_STATEMENTS);
                
                $gui = new ilLTIConsumerXapiStatementsGUI($this->object);
                $DIC->ctrl()->forwardCommand($gui);
                
                break;
            
            case strtolower(ilLTIConsumerScoringGUI::class):

                $DIC->tabs()->activateTab(self::TAB_ID_SCORING);

                $gui = new ilLTIConsumerScoringGUI($this->object);
                $DIC->ctrl()->forwardCommand($gui);

                break;
            
            case strtolower(ilLTIConsumerContentGUI::class):

                $DIC->tabs()->activateTab(self::TAB_ID_CONTENT);

                $gui = new ilLTIConsumerContentGUI($this->object);
                $DIC->ctrl()->forwardCommand($gui);

                break;

            case strtolower(ilInfoScreenGUI::class):

                $DIC->tabs()->activateTab(self::TAB_ID_INFO);
                $this->infoScreen();

                break;

            default:
                $command = $DIC->ctrl()->getCmd(self::DEFAULT_CMD);
                $this->{$command}();
        }
    }
    
    public function setTabs()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $DIC->language()->loadLanguageModule('lti');

        $DIC->help()->setScreenIdComponent("lti");

        if (!$this->object->getOfflineStatus() &&
            $this->object->getProvider()->getAvailability() != ilLTIConsumeProvider::AVAILABILITY_NONE
        ) {
            $DIC->tabs()->addTab(
                self::TAB_ID_CONTENT,
                $DIC->language()->txt(self::TAB_ID_CONTENT),
                $DIC->ctrl()->getLinkTargetByClass(ilLTIConsumerContentGUI::class)
            );
        }

        $DIC->tabs()->addTab(
            self::TAB_ID_INFO,
            $DIC->language()->txt(self::TAB_ID_INFO),
            $this->ctrl->getLinkTargetByClass(ilInfoScreenGUI::class)
        );


        if ($this->ltiAccess->hasWriteAccess()) {
            $DIC->tabs()->addTab(
                self::TAB_ID_SETTINGS,
                $DIC->language()->txt(self::TAB_ID_SETTINGS),
                $DIC->ctrl()->getLinkTargetByClass(ilLTIConsumerSettingsGUI::class)
            );
        }
        
        if ($this->ltiAccess->hasStatementsAccess()) {
            $DIC->tabs()->addTab(
                self::TAB_ID_STATEMENTS,
                $DIC->language()->txt(self::TAB_ID_STATEMENTS),
                $DIC->ctrl()->getLinkTargetByClass(ilLTIConsumerXapiStatementsGUI::class)
            );
        }
        
        if ($this->ltiAccess->hasHighscoreAccess()) {
            $DIC->language()->loadLanguageModule('lti');
            $DIC->tabs()->addTab(
                self::TAB_ID_SCORING,
                $DIC->language()->txt(self::TAB_ID_SCORING),
                $DIC->ctrl()->getLinkTargetByClass(ilLTIConsumerScoringGUI::class)
            );
        }
        
        if ($this->ltiAccess->hasLearningProgressAccess() && $this->object->getProvider()->getHasOutcome()) {
            $DIC->tabs()->addTab(
                self::TAB_ID_LEARNING_PROGRESS,
                $DIC->language()->txt(self::TAB_ID_LEARNING_PROGRESS),
                $DIC->ctrl()->getLinkTargetByClass(ilLearningProgressGUI::class)
            );
        }
        
        if ($this->ltiAccess->hasWriteAccess()) {
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
        
        if ($this->ltiAccess->hasEditPermissionsAccess()) {
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
        
        $aggregateEndPointUrl = str_replace(
            'data/xAPI',
            'api/statements/aggregate',
            $this->object->getProvider()->getXapiLaunchUrl() // should be named endpoint not launch url
        );
        
        $linkBuilder = new ilCmiXapiHighscoreReportLinkBuilder(
            $this->object,
            $aggregateEndPointUrl,
            $filter
        );
        
        $basicAuth = ilCmiXapiLrsType::buildBasicAuth(
            $this->object->getProvider()->getXapiLaunchKey(),
            $this->object->getProvider()->getXapiLaunchSecret()
        );
        
        $request = new ilCmiXapiHighscoreReportRequest(
            $basicAuth,
            $linkBuilder
        );
        
        try {
            $report = $request->queryReport($this->object);
            
            $DIC->ui()->mainTemplate()->setContent(
                $report->getResponseDebug()
            );
            
            //ilUtil::sendSuccess('Object ID: '.$this->object->getId());
            ilUtil::sendInfo($linkBuilder->getPipelineDebug());
            ilUtil::sendQuestion('<pre>' . print_r($report->getTableData(), 1) . '</pre>');
        } catch (Exception $e) {
            ilUtil::sendFailure($e->getMessage());
        }
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
        
        ilLPStatusWrapper::_updateStatus($this->object->getId(), $DIC->user()->getId());
    }

    protected function launch() : void
    {
        /** @var ilObjLTIConsumer $obj */
        $obj = $this->object;
        $this->tabs_gui->activateTab(self::TAB_ID_CONTENT);
        $gui = new ilLTIConsumerContentGUI($obj);
        $this->ctrl->forwardCommand($gui);
    }
    
    protected function infoScreen()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $DIC->tabs()->activateTab(self::TAB_ID_INFO);
        
        $DIC->ctrl()->setCmd("showSummary");
        $DIC->ctrl()->setCmdClass("ilinfoscreengui");
        $this->infoScreenForward();
    }
    
    protected function infoScreenForward()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $ilErr = $DIC['ilErr']; /* @var ilErrorHandling $ilErr */
        
        if (!$this->checkPermissionBool("visible") && !$this->checkPermissionBool("read")) {
            $ilErr->raiseError($DIC->language()->txt("msg_no_perm_read"));
        }
        
        $this->handleAvailablityMessage();

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
        
        // standard meta data
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());
        
        if (DEVMODE) {
            // Development Info
            $info->addSection('DEVMODE Info');
            $info->addProperty('Local Object ID', $this->object->getId());
            $info->addProperty('Current User ID', $DIC->user()->getId());
        }

        require_once('Services/Tracking/classes/class.ilLPObjSettings.php');
        if ($this->object->getProvider()->getHasOutcome() && ilLPObjSettings::_lookupDBMode($this->object->getId()) != 0) {
            $info->addSection($DIC->language()->txt("lti_info_learning_progress_section"));
            $info->addProperty(
                $DIC->language()->txt("mastery_score"),
                ($this->object->getMasteryScorePercent()) . ' %'
            );
        }

        // LTI Ressource Info about privacy
        $info->addSection($DIC->language()->txt("lti_info_privacy_section"));

        $info->addProperty(
            $DIC->language()->txt("lti_con_prov_url"),
            $this->object->getProvider()->getProviderUrl()
        );

        $info->addProperty(
            $DIC->language()->txt("conf_privacy_name"),
            $DIC->language()->txt('conf_privacy_name_' . ilObjCmiXapiGUI::getPrivacyNameString($this->object->getProvider()->getPrivacyName()))
        );

        $info->addProperty(
            $DIC->language()->txt("conf_privacy_ident"),
            $DIC->language()->txt('conf_privacy_ident_' . ilObjCmiXapiGUI::getPrivacyIdentString($this->object->getProvider()->getPrivacyIdent()))
        );
        if ($this->object->getProvider()->isExternalProvider()) {
            $info->addProperty(
                $DIC->language()->txt("lti_info_external_provider_label"),
                $DIC->language()->txt('lti_info_external_provider_info')
            );
        }

        if ($this->object->getProvider()->getUseXapi()) {
            $info->addProperty(
                $DIC->language()->txt("lti_con_prov_xapi_launch_url"),
                $this->object->getProvider()->getXapiLaunchUrl()
            );
        }

        // FINISHED INFO SCREEN, NOW FORWARD
        $this->ctrl->forwardCommand($info);
    }

    protected function handleAvailablityMessage()
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        if ($this->object->getProvider()->getProviderUrl() == '') {
            ilUtil::sendFailure($DIC->language()->txt('lti_provider_not_set_msg'));
        } elseif ($this->object->getProvider()->getAvailability() == ilLTIConsumeProvider::AVAILABILITY_NONE) {
            ilUtil::sendFailure($DIC->language()->txt('lti_provider_not_avail_msg'));
        }
    }
}
