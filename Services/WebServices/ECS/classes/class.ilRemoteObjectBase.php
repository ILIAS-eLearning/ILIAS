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
 * Remote object app base class
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
abstract class ilRemoteObjectBase extends ilObject2
{
    protected ?string $local_information = null;
    protected ?string $remote_link = null;
    protected ?string $organization = null;
    protected ?int $mid = null;
    protected string $auth_hash = '';
    
    protected string $realm_plain = '';
    
    private ilLogger $logger;

    public const MAIL_SENDER = 6;
    public const OBJECT_OWNER = 6;

    public function __construct(int $a_id = 0, bool $a_call_by_reference = true)
    {
        global $DIC;

        parent::__construct($a_id, $a_call_by_reference);

        $this->logger = $DIC->logger()->wsrv();
    }
    
    /**
     * Get instance by ilECSEvent(QueueReader) type
     *
     * @return ?ilRemoteObjectBase
     */
    public static function getInstanceByEventType(int $a_type)
    {
        switch ($a_type) {
            case ilECSEventQueueReader::TYPE_REMOTE_COURSE:
                return new ilObjRemoteCourse();
                
            case ilECSEventQueueReader::TYPE_REMOTE_CATEGORY:
                return new ilObjRemoteCategory();
                
            case ilECSEventQueueReader::TYPE_REMOTE_FILE:
                return new ilObjRemoteFile();
                
            case ilECSEventQueueReader::TYPE_REMOTE_GLOSSARY:
                return new ilObjRemoteGlossary();
                
            case ilECSEventQueueReader::TYPE_REMOTE_GROUP:
                return new ilObjRemoteGroup();
                
            case ilECSEventQueueReader::TYPE_REMOTE_LEARNING_MODULE:
                return new ilObjRemoteLearningModule();
                
            case ilECSEventQueueReader::TYPE_REMOTE_WIKI:
                return new ilObjRemoteWiki();
                
            case ilECSEventQueueReader::TYPE_REMOTE_TEST:
                return new ilObjRemoteTest();

            default:
                return null;
        }
    }
    
    protected function beforeCreate() : bool
    {
        $this->setOwner(self::OBJECT_OWNER);
        return parent::beforeCreate();
    }
    
    /**
     * Get db table name
     */
    abstract protected function getTableName() : string;
    
    /**
     * Get ECS resource identifier, e.g. "/campusconnect/courselinks"
     */
    abstract protected function getECSObjectType() : string;
    

    /**
     * Get realm plain
     */
    public function getRealmPlain() : string
    {
        return $this->realm_plain;
    }

    /**
     * set organization
     */
    public function setOrganization(string $a_organization) : void
    {
        $this->organization = $a_organization;
    }
    
    /**
     * get organization
     */
    public function getOrganization() : string
    {
        return $this->organization;
    }
    
    /**
     * get local information
     */
    public function getLocalInformation() : string
    {
        return $this->local_information;
    }
    
    /**
     * set local information
     *
     * @param string $a_info
     */
    public function setLocalInformation(string $a_info) : void
    {
        $this->local_information = $a_info;
    }
    
    /**
     * get mid
     */
    public function getMID() : int
    {
        return $this->mid;
    }
    
    /**
     * set mid
     *
     * @param int $a_mid mid
     */
    public function setMID(int $a_mid) : void
    {
        $this->mid = $a_mid;
    }
    
    /**
     * set remote link
     *
     * @param string $a_link link to original object
     */
    public function setRemoteLink(string $a_link) : void
    {
        $this->remote_link = $a_link;
    }
    
    /**
     * get remote link
     */
    public function getRemoteLink() : string
    {
        return $this->remote_link;
    }
    
    /**
     * get full remote link
     * Including ecs generated hash and auth mode
     *
     * @throws ilECSConnectorException
     */
    public function getFullRemoteLink() : string
    {
        $server_id = ilECSImportManager::getInstance()->lookupServerId($this->getId());
        $server = ilECSSetting::getInstanceByServerId($server_id);
        $setting = ilECSParticipantSetting::getInstance($server_id, $this->mid);
        $user = new ilECSUser($this->user);
        $ecs_user_data = $user->toGET($setting);
        $this->logger->info(__METHOD__ . ': Using ecs user data ' . $ecs_user_data);
        
        // check token mechanism enabled
        $part = new ilECSParticipantSetting($server_id, $this->getMID());
        if (!$part->isTokenEnabled()) {
            return $this->getRemoteLink();
        }

        $auth_hash = $this->createAuthResource($this->getRemoteLink() . $user->toREALM());
        $ecs_url_hash = 'ecs_hash_url=' . urlencode($server->getServerURI() . '/sys/auths/' . $auth_hash);
        
        if (strpos($this->getRemoteLink(), '?')) {
            $link = $this->getRemoteLink() . '&ecs_hash=' . $auth_hash . $ecs_user_data . '&' . $ecs_url_hash;
        } else {
            $link = $this->getRemoteLink() . '?ecs_hash=' . $auth_hash . $ecs_user_data . '&' . $ecs_url_hash;
        }
        $this->logger->info(__METHOD__ . ': ECS full link: ' . $link);
        return $link;
    }
    
    /**
     * create authentication resource on ecs server
     */
    public function createAuthResource(string $a_plain_realm)
    {
        try {
            $server_id = ilECSImportManager::getInstance()->lookupServerId($this->getId());
            $import_info = new ilECSImport($server_id, $this->getId());
            
            $connector = new ilECSConnector(ilECSSetting::getInstanceByServerId($server_id));
            $auth = new ilECSAuth();
            $auth->setPid($import_info->getMID());
            //TODO URL is deprecated
            $auth->setUrl($this->getRemoteLink());
            $realm = sha1($a_plain_realm);
            $this->logger->info(__METHOD__ . ': Using realm ' . $a_plain_realm);
            $auth->setRealm($realm);
            $this->logger->info(__METHOD__ . ' Mid is ' . $this->getMID());
            //TODO remove @
            $this->auth_hash = $connector->addAuth(json_encode($auth, JSON_THROW_ON_ERROR), $this->getMID());
            return $this->auth_hash;
        } catch (ilECSConnectorException $exc) {
            $this->logger->info(__METHOD__ . ': Caught error from ECS Auth resource: ' . $exc->getMessage());
            return false;
        }
    }

    protected function doCreate(bool $clone_mode = false) : void
    {
        $fields = array(
            "obj_id" => array("integer", $this->getId()),
            "local_information" => array("text", ""),
            "remote_link" => array("text", ""),
            "mid" => array("integer", 0),
            "organization" => array("text", "")
        );
        
        $this->doCreateCustomFields($fields);
    
        $this->db->insert($this->getTableName(), $fields);
    }
    
    /**
     * Add custom fields to db insert
     */
    protected function doCreateCustomFields(array &$a_fields) : void
    {
    }

    /**
     * Update remote object
     */
    protected function doUpdate() : void
    {
        $fields = array(
            "local_information" => array("text", $this->getLocalInformation()),
            "remote_link" => array("text", $this->getRemoteLink()),
            "mid" => array("integer", $this->getMID()),
            "organization" => array("text", $this->getOrganization())
        );
        
        $this->doUpdateCustomFields($fields);
        
        $where = array("obj_id" => array("integer", $this->getId()));
        
        $this->db->update($this->getTableName(), $fields, $where);
    }
    
    /**
     * Add custom fields to db update
     */
    protected function doUpdateCustomFields(array &$a_fields) : void
    {
    }

    /**
     * Delete remote object
     */
    protected function doDelete() : void
    {
        //put here your module specific stuff
        ilECSImportManager::getInstance()->_deleteByObjId($this->getId());
        
        $query = "DELETE FROM " . $this->getTableName() .
            " WHERE obj_id = " . $this->db->quote($this->getId(), 'integer') . " ";
        $this->db->manipulate($query);
    }

    protected function doRead() : void
    {
        $query = "SELECT * FROM " . $this->getTableName() .
            " WHERE obj_id = " . $this->db->quote($this->getId(), 'integer') . " ";
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setLocalInformation($row->local_information);
            $this->setRemoteLink($row->remote_link);
            $this->setMID((int) $row->mid);
            $this->setOrganization($row->organization);
            
            $this->doReadCustomFields($row);
        }
    }
    
    /**
     * Read custom fields from db row
     */
    protected function doReadCustomFields(object $a_row) : void
    {
    }
    
    /**
     * create remote object from ECSContent object
     *
     * @param ilECSSetting $a_server
     * @param object $a_ecs_content object with object settings
     * @param int $a_owner
     */
    public function createFromECSEContent(ilECSSetting $a_server, object $a_ecs_content, int $a_owner) : void
    {
        $this->create();
        $this->log->info("Done calling create, creating reference");
        // won't work for personal workspace
        $this->createReference();
        $this->log->info("Done creating reference, setting permissions");
        $this->setPermissions($a_server->getImportId());
        $this->log->info("Done setting permissions, putting object in tree");

        $matchable_content = ilECSUtils::getMatchableContent(
            $this->getECSObjectType(),
            $a_server->getServerId(),
            $a_ecs_content,
            $a_owner
        );
        
        $this->putInTree(ilECSCategoryMapping::getMatchingCategory(
            $a_server->getServerId(),
            $matchable_content
        ));
        $this->log->info("Done putting object in tree, updateing object");
        
        $this->updateFromECSContent($a_server, $a_ecs_content, $a_owner);
    }
    
    /**
     * update remote object settings from ecs content
     *
     * @param object $a_ecs_content object with object settings
     */
    public function updateFromECSContent(ilECSSetting $a_server, object $a_ecs_content, int $a_owner) : void
    {
        $this->logger->info('updateFromECSContent: ' . print_r($a_ecs_content, true));
        
        // Get organisation for owner (ObjectListGUI performance)
        $organisation = null;
        if ($a_owner) {
            $organisation = ilECSCommunityReader::getInstanceByServerId($a_server->getServerId())
                ->getParticipantNameByMid($a_owner);
            $this->logger->info('found organisation: ' . $organisation);
        }
        
        $this->setMID($a_owner); // obsolete?
        $this->setOrganization($organisation);
        $this->setTitle($a_ecs_content->title);
        $this->setDescription($a_ecs_content->abstract);
        $this->setRemoteLink($a_ecs_content->url);
                    
        $this->logger->info('updateCustomFromECSContent');
        $this->updateCustomFromECSContent($a_server, $a_ecs_content);

        // we are updating late so custom values can be set
        
        $this->logger->info('ilObject->update()');
        $this->update();
        
        $matchable_content = ilECSUtils::getMatchableContent(
            $this->getECSObjectType(),
            $a_server->getServerId(),
            $a_ecs_content,
            $a_owner
        );
                        
        // rule-based category mapping
        ilECSCategoryMapping::handleUpdate(
            $this->getId(),
            $a_server->getServerId(),
            $matchable_content
        );
    }
    
    /**
     * Add advanced metadata to json (export)
     *
     */
    protected function importMetadataFromJson(object $a_json, ilECSSetting $a_server, array $a_definition, int $a_mapping_mode) : void
    {
        $this->logger->info("importing metadata from json: " . print_r($a_json, true));
        
        $mappings = ilECSDataMappingSettings::getInstanceByServerId($a_server->getServerId());
        $values_records = ilAdvancedMDValues::getInstancesForObjectId($this->getId(), $this->getType());
        foreach ($values_records as $values_record) {
            // this correctly binds group and definitions
            $values_record->read();
        }
        
        $do_save = false;
        
        foreach ($a_definition as $id => $type) {
            if (is_array($type)) {
                [$type, $target] = $type;
            } else {
                $target = $id;
            }
        
            $timePlace = null;
            if ($field = $mappings->getMappingByECSName($a_mapping_mode, $id)) {
                // find element in records
                $adv_md_def = null;
                foreach ($values_records as $values_record) {
                    $adv_md_defs = $values_record->getDefinitions();
                    if (isset($adv_md_defs[$field])) {
                        $adv_md_def = $adv_md_defs[$field];
                        break;
                    }
                }
                if (!$adv_md_def) {
                    continue;
                }
                
                $raw_value = $a_json->{$target};
                        
                if ($type === ilECSUtils::TYPE_TIMEPLACE) {
                    if (!is_object($timePlace)) {
                        $timePlace = new ilECSTimePlace();
                        if (is_object($raw_value)) {
                            $timePlace->loadFromJSON($raw_value);
                        }
                    }
                    $raw_value = $timePlace;
                }
                
                if ($adv_md_def->importFromECS($type, $raw_value, $id)) {
                    $do_save = true;
                }
            }
        }
        
        if ($do_save) {
            foreach ($values_records as $values_record) {
                $additional = array();
                foreach ($values_record->getADTGroup()->getElements() as $element_id => $element) {
                    if (!$element->isNull()) {
                        $additional[$element_id] = array("disabled" => array("integer", 1));
                    }
                }
                $values_record->write($additional);
            }
        }
    }
    
    /**
     * update remote object settings from ecs content
     *
     * @param ilECSSetting $a_server
     * @param object $a_ecs_content object with object settings
     */
    protected function updateCustomFromECSContent(ilECSSetting $a_server, $ecs_content) : void
    {
    }
    
    /**
     * Is remote object from same installation?
     *
     * @return boolean
     */
    public function isLocalObject() : bool
    {
        if (ilECSExportManager::getInstance()->_isRemote(
            ilECSImportManager::getInstance()->lookupServerId($this->getId()),
            (int) ilECSImportManager::getInstance()->_lookupEContentId($this->getId())
        )) {
            return false;
        }
        return true;
    }
    
    /**
     * Handle creation
     *
     * called by ilTaskScheduler
     */
    public function handleCreate(ilECSSetting $a_server, int $a_econtent_id, array $a_mids) : bool
    {
        return $this->handleUpdate($a_server, $a_econtent_id, $a_mids);
    }
    
    /**
     * Handle update event
     *
     * called by ilTaskScheduler
     */
    public function handleUpdate(ilECSSetting $a_server, int $a_econtent_id, array $a_mids) : bool
    {
        // get content details
        $details = ilECSEContentDetails::getInstanceFromServer(
            $a_server->getServerId(),
            $a_econtent_id,
            $this->getECSObjectType()
        );
        if (!$details instanceof ilECSEContentDetails) {
            $this->handleDelete($a_server, $a_econtent_id);
            $this->logger->info(__METHOD__ . ': Handling delete of deprecated remote object. DONE');
            return true;
        }
        
        $this->logger->info(__METHOD__ . ': Receivers are ' . print_r($details->getReceivers(), true));
        $this->logger->info(__METHOD__ . ': Senders are ' . print_r($details->getSenders(), true));
        
        // check owner (sender mid)
        if (!ilECSParticipantSettings::getInstanceByServerId($a_server->getServerId())->isImportAllowed($details->getSenders())) {
            $this->logger->info('Ignoring disabled participant. MID: ' . $details->getOwner());
            return true;
        }
        
        // new mids
        foreach (array_intersect($a_mids, $details->getReceivers()) as $mid) {
            try {
                $connector = new ilECSConnector($a_server);
                $res = $connector->getResource($this->getECSObjectType(), $a_econtent_id);
                if ($res->getHTTPCode() === ilECSConnector::HTTP_CODE_NOT_FOUND) {
                    continue;
                }
                $json = $res->getResult();
                $this->logger->info(__METHOD__ . ': Received json: ' . print_r($json, true));
                if (!is_object($json)) {
                    // try as array (workaround for invalid content)
                    $json = $json[0];
                    if (!is_object($json)) {
                        throw new ilECSConnectorException('invalid json');
                    }
                }
            } catch (ilECSConnectorException $exc) {
                $this->logger->error(__METHOD__ . ': Error parsing result. ' . $exc->getMessage());
                $this->logger->logStack(ilLogLevel::ERROR);
                return false;
            }
            
            // Update existing
            
            // Check receiver mid
            if ($obj_id = ilECSImportManager::getInstance()->_isImported($a_server->getServerId(), $a_econtent_id, $mid)) {
                $this->logger->info(__METHOD__ . ': Handling update for existing object');
                $remote = ilObjectFactory::getInstanceByObjId($obj_id, false);
                if (!$remote instanceof self) {
                    $this->logger->info(__METHOD__ . ': Cannot instantiate remote object. Got object type ' . $remote->getType());
                    continue;
                }
                $remote->updateFromECSContent($a_server, $json, $details->getMySender());
            } else {
                $this->logger->info(__METHOD__ . ': my sender ' . $details->getMySender() . 'vs mid' . $mid);
                
                $this->logger->info(__METHOD__ . ': Handling create for non existing object');
                $this->createFromECSEContent($a_server, $json, $details->getMySender());
                                
                // update import status
                $this->logger->info(__METHOD__ . ': Updating import status');
                $import = new ilECSImport($a_server->getServerId(), $this->getId());
                $import->setEContentId($a_econtent_id);
                // Store receiver mid
                $import->setMID($mid);
                $import->save();
                
                $this->logger->info(__METHOD__ . ': Sending notification');
                $this->sendNewContentNotification($a_server->getServerId());
            }
        }
        
        $this->logger->info(__METHOD__ . ': done');
        return true;
    }
    
    /**
     * send notifications about new EContent
     */
    protected function sendNewContentNotification($a_server_id) : void
    {
        $settings = ilECSSetting::getInstanceByServerId($a_server_id);
        if (!count($settings->getEContentRecipients())) {
            return;
        }
        
        $lang = ilLanguageFactory::_getLanguage();
        $lang->loadLanguageModule('ecs');

        $mail = new ilMail(self::MAIL_SENDER);
        $message = $lang->txt('ecs_' . $this->getType() . '_created_body_a') . "\n\n";
        $message .= $lang->txt('title') . ': ' . $this->getTitle() . "\n";
        if ($desc = ($this->getDescription() !== '')) {
            $message .= $lang->txt('desc') . ': ' . $desc . "\n";
        }

        $href = ilLink::_getStaticLink($this->getRefId(), $this->getType(), true);
        $message .= $lang->txt("perma_link") . ': ' . $href . "\n\n";
        $message .= ilMail::_getAutoGeneratedMessageString();

        $mail->enqueue(
            $settings->getEContentRecipientsAsString(),
            '',
            '',
            $lang->txt('ecs_new_econtent_subject'),
            $message,
            array()
        );
    }
    
    /**
     * Handle delete event
     *
     * called by ilTaskScheduler
     */
    public function handleDelete(ilECSSetting $a_server, int $a_econtent_id, $a_mid = 0) : bool
    {
        // there is no information about the original mid anymore.
        // Therefor delete any remote objects with given econtent id
        $obj_ids = ilECSImportManager::getInstance()->_lookupObjIds($a_server->getServerId(), $a_econtent_id);
        $this->logger->info(__METHOD__ . ': Received obj_ids ' . print_r($obj_ids, true));

        foreach ($obj_ids as $obj_id) {
            $references = ilObject::_getAllReferences($obj_id);
            foreach ($references as $ref_id) {
                if ($tmp_obj = ilObjectFactory::getInstanceByRefId($ref_id, false)) {
                    $this->logger->info(__METHOD__ . ': Deleting obsolete remote course: ' . $tmp_obj->getTitle());
                    $tmp_obj->delete();
                    $this->logger->info(print_r($this->tree->getNodeData($ref_id), true));
                    $this->tree->deleteTree($this->tree->getNodeData($ref_id));
                }
                unset($tmp_obj);
            }
        }
        return true;
    }
    
    /**
     * Get all available resources
     */
    public function getAllResourceIds(ilECSSetting $a_server, bool $a_sender_only = false) : array
    {
        try {
            $connector = new ilECSConnector($a_server);
            $connector->addHeader('X-EcsQueryStrings', $a_sender_only ? 'sender=true' : 'all=true'); // #11301
            $list = $connector->getResourceList($this->getECSObjectType());
            if ($list instanceof ilECSResult) {
                return $list->getResult()->getLinkIds();
            }
        } catch (ilECSConnectorException $exc) {
            $this->logger->error(__METHOD__ . ': Error getting resource list. ' . $exc->getMessage());
        }

        return [];
    }
}
