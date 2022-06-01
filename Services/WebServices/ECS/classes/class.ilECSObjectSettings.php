<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
* Handles object exports to ECS
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
*/
abstract class ilECSObjectSettings
{
    protected \ilObject $content_obj; // [ilObj]
    
    private ilLogger $logger;
    private ilLanguage $lng;
    private ilTree $tree;
    private ilRbacAdmin $rbacAdmin;
    
    public const MAIL_SENDER = 6;
    private \ilGlobalTemplateInterface $main_tpl;
    
    /**
     * Constructor
     *
     * @param ilObject $a_content_object
     */
    public function __construct(ilObject $a_content_object)
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $this->lng = $DIC->language();
        $this->logger = $DIC->logger()->obj();
        $this->tree = $DIC->repositoryTree();
        $this->rbacAdmin = $DIC->rbac()->admin();

        $this->content_obj = $a_content_object;
    }
    
    /**
     * Get settings handler for repository object
     */
    public static function getInstanceByObject(ilObject $a_content_obj) : ?ilECSObjectSettings
    {
        switch ($a_content_obj->getType()) {
            case 'crs':
                return new ilECSCourseSettings($a_content_obj);
                
            case 'cat':
                return new ilECSCategorySettings($a_content_obj);
                
            case 'file':
                return new ilECSFileSettings($a_content_obj);
                
            case 'glo':
                return new ilECSGlossarySettings($a_content_obj);
                
            case 'grp':
                return new ilECSGroupSettings($a_content_obj);
                
            case 'lm':
                return new ilECSLearningModuleSettings($a_content_obj);
                
            case 'wiki':
                return new ilECSWikiSettings($a_content_obj);
        }
        return null;
    }
    
    /**
     * Get content object
     * @return ilObject
     */
    public function getContentObject() : \ilObject
    {
        return $this->content_obj;
    }
    
    /**
     * Get ECS resource identifier, e.g. "/campusconnect/courselinks"
     */
    abstract protected function getECSObjectType() : string;

    /**
     * Is ECS (for current object) active?
     */
    protected function isActive() : bool
    {
        if (ilECSServerSettings::getInstance()->activeServerExists()) {
            // imported objects cannot be exported => why not
            #if(!ilECSImportManager::getInstance()->lookupServerId($this->content_obj->getId()))
            {
                return true;
            }
        }
                        
        return false;
    }

    /**
     * Fill ECS export settings "multiple servers"
     *
     * to be used in ilObject->initEditForm()
     */
    public function addSettingsToForm(ilPropertyFormGUI $a_form, $a_type) : bool
    {
        $this->logger->debug('Show ecs settings.');
        if (!$this->isActive()) {
            $this->logger->debug('Object type is not active. => no settings.');
            return false;
        }
        
        $obj_id = $this->content_obj->getId();

        // Return if no participant is enabled for export and the current object is not released
        if (!$this->getContentObject()->withReferences()) {
            $this->logger->debug('Called withot references. => no settings.');
            return true;
        }
        $exportManager = ilECSExportManager::getInstance();
        $exportableParticipants = (new ilECSParticipantSettingsRepository())->getExportableParticipants($a_type);
        if (!$exportableParticipants && !$exportManager->_isExported($obj_id)) {
            $this->logger->debug('Object type is not exportable. => no settings.');
            return true;
        }
        if (
            $this->tree->checkForParentType($this->tree->getParentId($this->getContentObject()->getRefId()), 'crs', false) ||
            $this->tree->checkForParentType($this->tree->getParentId($this->getContentObject()->getRefId()), 'grp', false)
        ) {
            $this->logger->debug('Parent crs/grp in path. => no settings.');
            return true;
        }

        $this->lng->loadLanguageModule('ecs');

        // show ecs property form section
        $ecs = new ilFormSectionHeaderGUI();
        $ecs->setTitle($this->lng->txt('ecs_' . $a_type . '_export'));
        $a_form->addItem($ecs);


        // release or not
        $exp = new ilRadioGroupInputGUI($this->lng->txt('ecs_' . $a_type . '_export_obj_settings'), 'ecs_export');
        $exp->setRequired(true);
        $exp->setValue($exportManager->_isExported($obj_id) ? "1" : "0");
        $off = new ilRadioOption($this->lng->txt('ecs_' . $a_type . '_export_disabled'), "0");
        $exp->addOption($off);
        $on = new ilRadioOption($this->lng->txt('ecs_' . $a_type . '_export_enabled'), "1");
        $exp->addOption($on);
        $a_form->addItem($exp);

        // Show all exportable participants
        $publish_for = new ilCheckboxGroupInputGUI($this->lng->txt('ecs_publish_for'), 'ecs_sid');

        // @TODO: Active checkboxes for recipients
        //$publish_for->setValue((array) $members);

        // Read receivers
        $receivers = array();
        foreach ($exportManager->getExportServerIds($obj_id) as $sid) {
            $exp = new ilECSExport($sid, $obj_id);
                        
            $participants = null;
            $details = ilECSEContentDetails::getInstanceFromServer(
                $sid,
                $exp->getEContentId(),
                $this->getECSObjectType()
            );
            if ($details instanceof ilECSEContentDetails) {
                $participants = $details->getReceivers();
            }
            if ($participants) {
                foreach ($participants as $mid) {
                    $receivers[] = $sid . '_' . $mid;
                }
            }
        }
        $publish_for->setValue($receivers);

        foreach ($exportableParticipants as $pInfo) {
            $partSetting = new ilECSParticipantSetting($pInfo['sid'], $pInfo['mid']);

            $com = new ilCheckboxOption(
                $partSetting->getCommunityName() . ': ' . $partSetting->getTitle(),
                'sid_mid'
            );
            $com->setValue($pInfo['sid'] . '_' . $pInfo['mid']);
            $publish_for->addOption($com);
        }
        $on->addSubItem($publish_for);
        return true;
    }
    
    /**
     * Update ECS Export Settings
     *
     * Processes post data from addSettingstoForm()
     * to be used in ilObject->update() AFTER object data has been updated
     *
     * @return bool
     */
    public function handleSettingsUpdate() : bool
    {
        if (!$this->isActive()) {
            return true;
        }
        
        // Parse post data
        $mids = array();
        foreach ((array) $_POST['ecs_sid'] as $sid_mid) {
            $tmp = explode('_', $sid_mid);
            $mids[$tmp[0]][] = $tmp[1];
        }

        try {
            // Update for each server
            foreach ((new ilECSParticipantSettingsRepository())->getServersContaingExports() as $server_id) {
                $server = ilECSSetting::getInstanceByServerId($server_id);
                if ($server->isEnabled()) {
                    // Export
                    $export = true;
                    if (!$_POST['ecs_export']) {
                        $export = false;
                    }
                    if (
                        !isset($mids[$server_id]) ||
                        !is_array($mids[$server_id]) ||
                        !count($mids[$server_id])) {
                        $export = false;
                    }
                    $this->handleSettingsForServer(
                        $server,
                        $export,
                        $mids[$server_id] ?? []
                    );
                }
            }
        } catch (ilECSConnectorException $exc) {
            $this->main_tpl->setOnScreenMessage('failure', 'Error exporting to ECS server: ' . $exc->getMessage());
            return false;
        }
        return true;
    }
    
    /**
     * Save ECS settings (add- update- deleteResource)
     *
     * @param array array of participant mids
     * @throws ilECSConnectorException
     */
    protected function handleSettingsForServer(ilECSSetting $a_server, bool $a_export, array $a_mids) : void
    {
        $export_settings = new ilECSExport($a_server->getServerId(), $this->content_obj->getId());

        // already exported?
        if ($export_settings->isExported()) {
            // still exportable: update ecs
            if ($a_export) {
                $this->doUpdate($a_server, $export_settings, $a_mids);
            }
            // not exportable anymore
            else {
                $this->doDelete($a_server, $export_settings);
            }
        }
        // not exported yet
        elseif ($a_export) {
            $this->doAdd($a_server, $export_settings, $a_mids);
        }
    }
    
    /**
     * Update ECS Content
     *
     * to be used AFTER metadata-/content-updates
     */
    public function handleContentUpdate() : bool
    {
        if (!$this->isActive()) {
            return true;
        }
        $exportManager = ilECSExportManager::getInstance();
        $export_servers = $exportManager->getExportServerIds($this->content_obj->getId());
        foreach ($export_servers as $server_id) {
            $server = ilECSSetting::getInstanceByServerId($server_id);
            if ($server->isEnabled()) {
                try {
                    $export_settings = new ilECSExport($server_id, $this->content_obj->getId());
                    
                    // already exported, update ecs
                    if ($export_settings->isExported()) {
                        $this->doUpdate($server, $export_settings);
                    }
                } catch (ilECSConnectorException $exc) {
                    $this->logger->warning(__METHOD__ . ': Cannot handle ECS content update. ' . $exc->getMessage());
                    return false;
                }
            }
        }
        return true;
    }
    
    /**
     * Add resource to ECS
     */
    protected function doAdd(ilECSSetting $a_server, ilECSExport $a_export_settings, array $a_mids) : void
    {
        $this->logger->info(__METHOD__ . ': Starting ECS add resource...');

        $json = $this->buildJson($a_server);

        $connector = new ilECSConnector($a_server);
        $connector->addHeader(ilECSConnector::HEADER_MEMBERSHIPS, implode(',', $a_mids));
        $econtent_id = $connector->addResource(
            $this->getECSObjectType(),
            json_encode($json, JSON_THROW_ON_ERROR)
        );

        // status changed
        $a_export_settings->setExported(true);
        $a_export_settings->setEContentId($econtent_id);
        $a_export_settings->save();
        
        $this->handlePermissionUpdate($a_server);

        // Send mail
        $this->sendNewContentNotification($a_server, $econtent_id);
    }
    
    /**
     * Update ECS resource
     *
     * @param ilECSSetting $a_server
     * @param ilECSExport $a_export_settings
     * @param array $a_mids
     * @throws ilECSConnectorException
     */
    protected function doUpdate(ilECSSetting $a_server, ilECSExport $a_export_settings, array $a_mids = null) : void
    {
        $econtent_id = $a_export_settings->getEContentId();
        if (!$econtent_id) {
            $this->logger->warning(__METHOD__ . ': Missing eid. Aborting.');
            throw new ilECSConnectorException('Missing ECS content ID. Aborting.');
        }
        $connector = new ilECSConnector($a_server);
        
        if (!$a_mids) {
            $a_mids = $this->getParticipants($a_server->getServerId(), $econtent_id);
        }
        $this->logger->info(__METHOD__ . ': Start updating ECS content - ' . print_r($a_mids, true));
        $connector->addHeader(ilECSConnector::HEADER_MEMBERSHIPS, implode(',', (array) $a_mids));

        $json = $this->buildJson($a_server);
        $connector->updateResource(
            $this->getECSObjectType(),
            $econtent_id,
            json_encode($json, JSON_THROW_ON_ERROR)
        );
        
        $this->handlePermissionUpdate($a_server);
    }
    
    /**
     * Delete ECS resource
     *
     * as it is called from self::_handleDelete() it has to be public...
     *
     * @throws ilECSConnectorException
     */
    public function doDelete(ilECSSetting $a_server, ilECSExport $a_export_settings) : void
    {
        // already exported?
        if ($a_export_settings->isExported()) {
            $econtent_id = $a_export_settings->getEContentId();
            if (!$econtent_id) {
                $this->logger->warning(__METHOD__ . ': Missing eid. Aborting.');
                throw new ilECSConnectorException('Missing ECS content ID. Aborting.');
            }
            $connector = new ilECSConnector($a_server);

            $this->logger->info(__METHOD__ . ': Start deleting ECS content...');
            $connector->deleteResource(
                $this->getECSObjectType(),
                $econtent_id
            );

            // status changed
            $a_export_settings->setExported(false);
            $a_export_settings->save();
        }
    }
    
    /**
     * handle delete
     * Objects that are moved to the trash call ECS-Remove
     *
     * @see ilRepUtil
     */
    public static function _handleDelete(array $a_subbtree_nodes) : void
    {
        // active?
        if (!ilECSServerSettings::getInstance()->activeServerExists()) {
            return;
        }
        $exportManager = ilECSExportManager::getInstance();
        $exported = $exportManager->getExportedIds();
        foreach ($a_subbtree_nodes as $node) {
            if (in_array(
                $node['obj_id'],
                $exported,
                true
            ) && $content_obj = ilObjectFactory::getInstanceByRefId($node['child'], false)) {
                $settings = self::getInstanceByObject($content_obj);

                // Read export server ids
                foreach ($exportManager->getExportServerIds($node['obj_id']) as $sid) {
                    $server = ilECSSetting::getInstanceByServerId($sid);
                    $export_settings = new ilECSExport($sid, $content_obj->getId());
                    if ($settings) {
                        $settings->doDelete($server, $export_settings);
                    }
                }
            }
        }
    }
    
    /**
     * Get participants for server and ecs resource
     */
    protected function getParticipants(int $a_server_id, int $a_econtent_id) : array
    {
        $receivers = array();
        foreach ((array) $a_server_id as $sid) {
            $participants = null;
            $details = ilECSEContentDetails::getInstanceFromServer($sid, $a_econtent_id, $this->getECSObjectType());
            if ($details instanceof ilECSEContentDetails) {
                $participants = $details->getReceivers();
            }
            if ($participants) {
                foreach ($participants as $mid) {
                    $receivers[] = $mid;
                }
            }
        }
        return $receivers;
    }
    
    /**
     * send notifications about new EContent
     */
    protected function sendNewContentNotification(ilECSSetting $a_server, $a_econtent_id) : bool
    {
        if (!count($rcps = $a_server->getApprovalRecipients())) {
            return true;
        }

        $lang = ilLanguageFactory::_getLanguage();
        $lang->loadLanguageModule('ecs');

        // @TODO: read mail
        $mail = new ilMail(self::MAIL_SENDER);
        $message = $lang->txt('ecs_export_created_body_a') . "\n\n";
        $message .= $lang->txt('title') . ': ' . $this->content_obj->getTitle() . "\n";
        if (($desc = $this->content_obj->getDescription()) !== '') {
            $message .= $lang->txt('desc') . ': ' . $desc . "\n";
        }

        // Participant info
        $message .= ("\n" . $lang->txt('ecs_published_for'));
            
        try {
            $found = false;
            
            $receivers = null;
            $details = ilECSEContentDetails::getInstanceFromServer(
                $a_server->getServerId(),
                $a_econtent_id,
                $this->getECSObjectType()
            );
            if ($details instanceof ilECSEContentDetails) {
                $receivers = $details->getReceivers();
            }
            if ($receivers) {
                foreach ($receivers as $member) {
                    $found = true;
                    
                    $part = ilECSCommunityReader::getInstanceByServerId($a_server->getServerId())->getParticipantByMID($member);
                    
                    $message .= ("\n\n" . $part->getParticipantName() . "\n");
                    $message .= ($part->getDescription());
                }
            }
            if ($found) {
                $message .= "\n\n";
            } else {
                $message .= (' ' . $lang->txt('ecs_not_published') . "\n\n");
            }
        } catch (ilECSConnectorException $e) {
            $this->logger->warning(__METHOD__ . ': Cannot read approvements.');
            return false;
        }
        
        $href = ilLink::_getStaticLink($this->content_obj->getRefId(), 'crs', true);
        $message .= $lang->txt("perma_link") . ': ' . $href . "\n\n";
        $message .= ilMail::_getAutoGeneratedMessageString();
        
        $mail->enqueue(
            $a_server->getApprovalRecipientsAsString(),
            '',
            '',
            $lang->txt('ecs_new_approval_subject'),
            $message,
            array()
        );
        
        return true;
    }
    
    /**
     * Handle permission update
     */
    protected function handlePermissionUpdate(ilECSSetting $server) : void
    {
        if (
            ($this->content_obj->getType() === 'crs') ||
            ($this->content_obj->getType() === 'grp')
        ) {
            $this->logger->info(__METHOD__ . ': Permission update for courses/groups');
            $this->rbacAdmin->grantPermission(
                $server->getGlobalRole(),
                ilRbacReview::_getOperationIdsByName(array('join','visible')),
                $this->content_obj->getRefId()
            );
        }
    }
    
    /**
     * Build core json structure
     */
    protected function getJsonCore(string $a_etype) : object
    {
        $json = new stdClass();
        $json->lang = 'en_EN'; // :TODO: obsolet?
        $json->id = 'il_' . IL_INST_ID . '_' . $this->getContentObject()->getType() . '_' . $this->getContentObject()->getId();
        $json->etype = $a_etype;
        $json->title = $this->content_obj->getTitle();
        $json->abstract = $this->content_obj->getLongDescription();
        
        $json->url = ilLink::_getLink($this->content_obj->getRefId(), $this->content_obj->getType());
        
        return $json;
    }
    
    /**
     * Add advanced metadata to json (export)
     */
    protected function addMetadataToJson(object $a_json, ilECSSetting $a_server, array $a_definition) : void
    {
        $mappings = ilECSDataMappingSettings::getInstanceByServerId($a_server->getServerId());
        
        $values = ilECSUtils::getAdvancedMDValuesForObjId($this->content_obj->getId());

        foreach ($a_definition as $id => $type) {
            if (is_array($type)) {
                [$type , $target] = $type;
            } else {
                $target = $id;
            }
        
            if ($field = $mappings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_EXPORT, $id)) {
                $value = $values[$field] ?? '';
                
                switch ($type) {
                    case ilECSUtils::TYPE_ARRAY:
                        $a_json->{$target} = explode(',', $value);
                        break;
                    
                    case ilECSUtils::TYPE_INT:
                        $a_json->{$target} = (int) $value;
                        break;
                    
                    case ilECSUtils::TYPE_STRING:
                        $a_json->{$target} = (string) $value;
                        break;
                    
                    case ilECSUtils::TYPE_TIMEPLACE:
                        if (!isset($a_json->{$target})) {
                            $a_json->{$target} = new ilECSTimePlace();
                        }
                        $a_json->{$target}->{'set' . ucfirst($id)}($value);
                        break;
                }
            }
        }
    }
    
    /**
     * Build resource-specific json
     *
     * @return mixed
     */
    abstract protected function buildJson(ilECSSetting $a_server);
}
