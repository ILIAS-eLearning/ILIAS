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
*
* @ingroup ServicesWebServicesECS
*/
abstract class ilECSObjectSettings
{
    protected \ilObject $content_obj; // [ilObj]
    
    /**
     * @var ilLogger
     */
    private ilLogger $logger;
    private ilLanguage $lng;
    private ilTree $tree;
    private ilRbacAdmin $rbacAdmin;
    
    const MAIL_SENDER = 6;
    
    /**
     * Constructor
     *
     * @param ilObject $a_content_object
     */
    public function __construct(ilObject $a_content_object)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->logger = $DIC->logger()->obj();
        $this->tree = $DIC->repositoryTree();
        $this->rbacAdmin = $DIC->rbac()->admin();

        $this->content_obj = $a_content_object;
    }
    
    /**
     * Get settings handler for repository object
     *
     * @param ilObject $a_content_obj
     * @return ilECSObjectSettings
     */
    public static function getInstanceByObject(ilObject $a_content_obj)
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
    }
    
    /**
     * Get content object
     * @return ilObject
     */
    public function getContentObject()
    {
        return $this->content_obj;
    }
    
    /**
     * Get ECS resource identifier, e.g. "/campusconnect/courselinks"
     *
     * @return string
     */
    abstract protected function getECSObjectType();
    
    /**
     * Is ECS (for current object) active?
     *
     * @return boolean
     */
    protected function isActive()
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
     *
     * @param ilPropertyFormGUI $a_form
     */
    public function addSettingsToForm(ilPropertyFormGUI $a_form, $a_type)
    {
        $this->logger->debug('Show ecs settings.');
        if (!$this->isActive($a_type)) {
            $this->logger->debug('Object type is not active. => no settings.');
            return;
        }
        
        $obj_id = $this->content_obj->getId();

        // Return if no participant is enabled for export and the current object is not released
        if (!$this->getContentObject()->withReferences()) {
            $this->logger->debug('Called withot references. => no settings.');
            return true;
        }
        $exportManager = ilECSExportManager::getInstance();
        $exportableParticipants = (new ilECSParticipantSettingsRepository())->getExportableParticipants($a_type);
        if (!$exportableParticipants and !$exportManager->_isExported($obj_id)) {
            $this->logger->debug('Object type is not exportable. => no settings.');
            return true;
        }
        if (
            $this->tree->checkForParentType($this->tree->getParentId($this->getContentObject()->getRefId()), 'crs', false) or
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
        $exp->setValue($exportManager->_isExported($obj_id) ? 1 : 0);
        $off = new ilRadioOption($this->lng->txt('ecs_' . $a_type . '_export_disabled'), 0);
        $exp->addOption($off);
        $on = new ilRadioOption($this->lng->txt('ecs_' . $a_type . '_export_enabled'), 1);
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
            $details = ilECSEContentDetails::getInstance(
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

            $com = new ilCheckboxInputGUI(
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
    public function handleSettingsUpdate()
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
                        isset($mids[$server_id]) ? $mids[$server_id] : []
                    );
                }
            }
        } catch (ilECSConnectorException $exc) {
            ilUtil::sendFailure('Error exporting to ECS server: ' . $exc->getMessage());
            return false;
        }
        return true;
    }
    
    /**
     * Save ECS settings (add- update- deleteResource)
     *
     * @param ilECSSetting $a_server
     * @param bool $a_export
     * @param array array of participant mids
     * @throws ilECSConnectorException
     */
    protected function handleSettingsForServer(ilECSSetting $a_server, $a_export, $a_mids)
    {
        try {
            $export_settings = new ilECSExport($a_server->getServerId(), $this->content_obj->getId());

            // already exported?
            if ($export_settings->isExported()) {
                // still exportable: update ecs
                if ((bool) $a_export) {
                    $this->doUpdate($a_server, $export_settings, $a_mids);
                }
                // not exportable anymore
                else {
                    $this->doDelete($a_server, $export_settings);
                }
            }
            // not exported yet
            else {
                // now to be exported
                if ($a_export) {
                    $this->doAdd($a_server, $export_settings, $a_mids);
                }
                // was not and will not be exported
                else {
                }
            }
        } catch (ilECSConnectorException $exc) {
            throw $exc;
        }
    }
    
    /**
     * Update ECS Content
     *
     * to be used AFTER metadata-/content-updates
     *
     * @return bool
     */
    public function handleContentUpdate()
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
                    // not exported yet, nothing to do
                    else {
                    }
                } catch (ilECSConnectorException $exc) {
                    $this->logger->warn(__METHOD__ . ': Cannot handle ECS content update. ' . $exc->getMessage());
                    return false;
                }
            }
        }
        return true;
    }
    
    /**
     * Add resource to ECS
     *
     * @param ilECSSetting $a_server
     * @param ilECSExport $a_export_settings
     * @param array $a_mids
     */
    protected function doAdd(ilECSSetting $a_server, ilECSExport $a_export_settings, array $a_mids)
    {
        $this->logger->info(__METHOD__ . ': Starting ECS add resource...');

        $json = $this->buildJson($a_server);

        $connector = new ilECSConnector($a_server);
        $connector->addHeader(ilECSConnector::HEADER_MEMBERSHIPS, implode(',', $a_mids));
        $econtent_id = $connector->addResource(
            $this->getECSObjectType(),
            json_encode($json)
        );

        // status changed
        $a_export_settings->setExported(true);
        $a_export_settings->setEContentId($econtent_id);
        $a_export_settings->save();
        
        $this->handlePermissionUpdate($a_server, true);

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
    protected function doUpdate(ilECSSetting $a_server, ilECSExport $a_export_settings, array $a_mids = null)
    {
        $econtent_id = $a_export_settings->getEContentId();
        if (!$econtent_id) {
            $this->logger->warn(__METHOD__ . ': Missing eid. Aborting.');
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
            json_encode($json)
        );
        
        $this->handlePermissionUpdate($a_server, true);
    }
    
    /**
     * Delete ECS resource
     *
     * as it is called from self::_handleDelete() it has to be public...
     *
     * @param type $a_server_id
     * @throws ilECSConnectorException
     */
    public function doDelete(ilECSSetting $a_server, ilECSExport $a_export_settings)
    {
        // already exported?
        if ($a_export_settings->isExported()) {
            $econtent_id = $a_export_settings->getEContentId();
            if (!$econtent_id) {
                $this->logger->warn(__METHOD__ . ': Missing eid. Aborting.');
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
     * @param array $a_subbtree_nodes
     */
    public static function _handleDelete(array $a_subbtree_nodes)
    {
        // active?
        if (!ilECSServerSettings::getInstance()->activeServerExists()) {
            return;
        }
        $exportManager = ilECSExportManager::getInstance();
        $exported = $exportManager->getExportedIds();
        foreach ($a_subbtree_nodes as $node) {
            if (in_array($node['obj_id'], $exported)) {
                if ($content_obj = ilObjectFactory::getInstanceByRefId($node['child'], false)) {
                    $settings = self::getInstanceByObject($content_obj);
                    
                    // Read export server ids
                    foreach ($exportManager->getExportServerIds($node['obj_id']) as $sid) {
                        $server = ilECSSetting::getInstanceByServerId($sid);
                        $export_settings = new ilECSExport($sid, $content_obj->getId());
                        $settings->doDelete($server, $export_settings);
                    }
                }
            }
        }
    }
    
    /**
     * Get participants for server and ecs resource
     *
     * @param int $a_server_id
     * @param int $a_econtent_id
     * @return array
     */
    protected function getParticipants($a_server_id, $a_econtent_id)
    {
        $receivers = array();
        foreach ((array) $a_server_id as $sid) {
            $participants = null;
            $details = ilECSEContentDetails::getInstance($sid, $a_econtent_id, $this->getECSObjectType());
            if ($details instanceof ilECSEContentDetails) {
                $participants = $details->getReceivers();
            }
            if ($participants) {
                foreach ($participants as $mid) {
                    $receivers[] = $mid;
                }
            }
        }
        return (array) $receivers;
    }
    
    /**
     * send notifications about new EContent
     *
     * @return bool
     */
    protected function sendNewContentNotification(ilECSSetting $a_server, $a_econtent_id)
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
        if (strlen($desc = $this->content_obj->getDescription())) {
            $message .= $lang->txt('desc') . ': ' . $desc . "\n";
        }

        // Participant info
        $message .= ("\n" . $lang->txt('ecs_published_for'));
            
        try {
            $found = false;
            
            $receivers = null;
            $details = ilECSEContentDetails::getInstance(
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
            $this->logger->warn(__METHOD__ . ': Cannot read approvements.');
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
     * @param ilECSSetting $server
     */
    protected function handlePermissionUpdate(ilECSSetting $server)
    {
        if (
            ($this->content_obj->getType() == 'crs') ||
            ($this->content_obj->getType() == 'grp')
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
     *
     * @param string $a_etype
     * @return object
     */
    protected function getJsonCore($a_etype)
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
     *
     * @param object $a_json
     * @param ilECSSetting $a_server
     * @param array $a_definition
     */
    protected function addMetadataToJson(&$a_json, ilECSSetting $a_server, array $a_definition)
    {
        $mappings = ilECSDataMappingSettings::getInstanceByServerId($a_server->getServerId());
        
        $values = ilECSUtils::getAdvancedMDValuesForObjId($this->content_obj->getId());

        foreach ($a_definition as $id => $type) {
            if (is_array($type)) {
                $target = $type[1];
                $type = $type[0];
            } else {
                $target = $id;
            }
        
            if ($field = $mappings->getMappingByECSName(ilECSDataMappingSetting::MAPPING_EXPORT, $id)) {
                $value = isset($values[$field]) ? $values[$field] : '';
                
                switch ($type) {
                    case ilECSUtils::TYPE_ARRAY:
                        $a_json->$target = explode(',', $value);
                        break;
                    
                    case ilECSUtils::TYPE_INT:
                        $a_json->$target = (int) $value;
                        break;
                    
                    case ilECSUtils::TYPE_STRING:
                        $a_json->$target = (string) $value;
                        break;
                    
                    case ilECSUtils::TYPE_TIMEPLACE:
                        if (!isset($a_json->$target)) {
                            $a_json->$target = new ilECSTimePlace();
                        }
                        $a_json->$target->{'set' . ucfirst($id)}($value);
                        break;
                }
            }
        }
    }
    
    /**
     * Build resource-specific json
     *
     * @param ilECSSetting $a_server
     * @return object
     */
    abstract protected function buildJson(ilECSSetting $a_server);
}
