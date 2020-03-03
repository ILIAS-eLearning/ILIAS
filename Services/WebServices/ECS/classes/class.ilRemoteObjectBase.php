<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Object/classes/class.ilObject2.php";

/**
 * Remote object app base class
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @ingroup ServicesWebServicesECS
*/

abstract class ilRemoteObjectBase extends ilObject2
{
    protected $local_information;
    protected $remote_link;
    protected $organization;
    protected $mid;
    protected $auth_hash = '';
    
    protected $realm_plain = '';
    
    const MAIL_SENDER = 6;
    const OBJECT_OWNER = 6;

    /**
     * Constructor
     *
     * @param int $a_id
     * @param bool $a_call_by_reference
     * @return ilObject
     */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        parent::__construct($a_id, $a_call_by_reference);
        $this->db = $ilDB;
    }
    
    /**
     * Get instance by ilECSEvent(QueueReader) type
     *
     * @param int $a_type
     * @return ilRemoteObjectBase
     */
    public static function getInstanceByEventType($a_type)
    {
        switch ($a_type) {
            case ilECSEventQueueReader::TYPE_REMOTE_COURSE:
                include_once 'Modules/RemoteCourse/classes/class.ilObjRemoteCourse.php';
                return new ilObjRemoteCourse();
                
            case ilECSEventQueueReader::TYPE_REMOTE_CATEGORY:
                include_once 'Modules/RemoteCategory/classes/class.ilObjRemoteCategory.php';
                return new ilObjRemoteCategory();
                
            case ilECSEventQueueReader::TYPE_REMOTE_FILE:
                include_once 'Modules/RemoteFile/classes/class.ilObjRemoteFile.php';
                return new ilObjRemoteFile();
                
            case ilECSEventQueueReader::TYPE_REMOTE_GLOSSARY:
                include_once 'Modules/RemoteGlossary/classes/class.ilObjRemoteGlossary.php';
                return new ilObjRemoteGlossary();
                
            case ilECSEventQueueReader::TYPE_REMOTE_GROUP:
                include_once 'Modules/RemoteGroup/classes/class.ilObjRemoteGroup.php';
                return new ilObjRemoteGroup();
                
            case ilECSEventQueueReader::TYPE_REMOTE_LEARNING_MODULE:
                include_once 'Modules/RemoteLearningModule/classes/class.ilObjRemoteLearningModule.php';
                return new ilObjRemoteLearningModule();
                
            case ilECSEventQueueReader::TYPE_REMOTE_WIKI:
                include_once 'Modules/RemoteWiki/classes/class.ilObjRemoteWiki.php';
                return new ilObjRemoteWiki();
                
            case ilECSEventQueueReader::TYPE_REMOTE_TEST:
                include_once 'Modules/RemoteTest/classes/class.ilObjRemoteTest.php';
                return new ilObjRemoteTest();
        }
    }
    
    public function beforeCreate()
    {
        $this->setOwner(self::OBJECT_OWNER);
        return parent::beforeCreate();
    }
    
    /**
     * Get db table name
     *
     * @return string
     */
    abstract protected function getTableName();
    
    /**
     * Get ECS resource identifier, e.g. "/campusconnect/courselinks"
     *
     * @return string
     */
    abstract protected function getECSObjectType();
    
    /**
     * lookup organization
     *
     * @param int $a_obj_id
     * @return string
     */
    public static function _lookupOrganization($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT organization FROM " . static::DB_TABLE_NAME .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " ";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->organization;
        }
        return '';
    }
    
    /**
     * Get realm plain
     * @return type
     */
    public function getRealmPlain()
    {
        return $this->realm_plain;
    }

    /**
     * set organization
     *
     * @param string $a_organization
     */
    public function setOrganization($a_organization)
    {
        $this->organization = $a_organization;
    }
    
    /**
     * get organization
     *
     * @return string
     */
    public function getOrganization()
    {
        return $this->organization;
    }
    
    /**
     * get local information
     *
     * @return string
     */
    public function getLocalInformation()
    {
        return $this->local_information;
    }
    
    /**
     * set local information
     *
     * @param string $a_info
     */
    public function setLocalInformation($a_info)
    {
        $this->local_information = $a_info;
    }
    
    /**
     * get mid
     *
     * @return int
     */
    public function getMID()
    {
        return $this->mid;
    }
    
    /**
     * set mid
     *
     * @param int $a_mid mid
     */
    public function setMID($a_mid)
    {
        $this->mid = $a_mid;
    }
    
    /**
     * lookup owner mid
     *
     * @param int $a_obj_id obj_id
     * @return int
     */
    public static function _lookupMID($a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "SELECT mid FROM " . static::DB_TABLE_NAME .
            " WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " ";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->mid;
        }
        return 0;
    }
    
    /**
     * set remote link
     *
     * @param string $a_link link to original object
     */
    public function setRemoteLink($a_link)
    {
        $this->remote_link = $a_link;
    }
    
    /**
     * get remote link
     *
     * @return string
     */
    public function getRemoteLink()
    {
        return $this->remote_link;
    }
    
    /**
     * get full remote link
     * Including ecs generated hash and auth mode
     *
     * @return string
     * @throws ilECSConnectorException
     */
    public function getFullRemoteLink()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        
        
        include_once './Services/WebServices/ECS/classes/class.ilECSImport.php';
        $server_id = ilECSImport::lookupServerId($this->getId());
        $server = ilECSSetting::getInstanceByServerId($server_id);
        
        include_once('./Services/WebServices/ECS/classes/class.ilECSUser.php');
        $user = new ilECSUser($ilUser);
        $ecs_user_data = $user->toGET();
        $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Using ecs user data ' . $ecs_user_data);
        
        // check token mechanism enabled
        include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSetting.php';
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
        $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': ECS full link: ' . $link);
        return $link;
    }
    
    /**
     * create authentication resource on ecs server
     *
     * @return bool
     * @throws ilECSConnectorException
     */
    public function createAuthResource($a_plain_realm)
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];
        
        include_once './Services/WebServices/ECS/classes/class.ilECSAuth.php';
        include_once './Services/WebServices/ECS/classes/class.ilECSConnector.php';
        include_once './Services/WebServices/ECS/classes/class.ilECSImport.php';
        include_once './Services/WebServices/ECS/classes/class.ilECSSetting.php';

        try {
            $server_id = ilECSImport::lookupServerId($this->getId());
            $import_info = new ilECSImport($server_id, $this->getId());
            
            $connector = new ilECSConnector(ilECSSetting::getInstanceByServerId($server_id));
            $auth = new ilECSAuth();
            $auth->setPid($import_info->getMID());
            // URL is deprecated
            $auth->setUrl($this->getRemoteLink());
            $realm = sha1($a_plain_realm);
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Using realm ' . $a_plain_realm);
            $auth->setRealm($realm);
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ' Mid is ' . $this->getMID());
            $this->auth_hash = $connector->addAuth(@json_encode($auth), $this->getMID());
            return $this->auth_hash;
        } catch (ilECSConnectorException $exc) {
            $ilLog->write(__METHOD__ . ': Caught error from ECS Auth resource: ' . $exc->getMessage());
            return false;
        }
    }
    
    /**
     * Create remote object
     */
    public function doCreate()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $fields = array(
            "obj_id" => array("integer", $this->getId()),
            "local_information" => array("text", ""),
            "remote_link" => array("text", ""),
            "mid" => array("integer", 0),
            "organization" => array("text", "")
        );
        
        $this->doCreateCustomFields($fields);
    
        $ilDB->insert($this->getTableName(), $fields);
    }
    
    /**
     * Add custom fields to db insert
     * @param array $a_fields
     */
    protected function doCreateCustomFields(array &$a_fields)
    {
    }

    /**
     * Update remote object
     */
    public function doUpdate()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $fields = array(
            "local_information" => array("text", $this->getLocalInformation()),
            "remote_link" => array("text", $this->getRemoteLink()),
            "mid" => array("integer", $this->getMID()),
            "organization" => array("text", $this->getOrganization())
        );
        
        $this->doUpdateCustomFields($fields);
        
        $where = array("obj_id" => array("integer", $this->getId()));
        
        $ilDB->update($this->getTableName(), $fields, $where);
    }
    
    /**
     * Add custom fields to db update
     * @param array $a_fields
     */
    protected function doUpdateCustomFields(array &$a_fields)
    {
    }

    /**
     * Delete remote object
     */
    public function doDelete()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        //put here your module specific stuff
        include_once('./Services/WebServices/ECS/classes/class.ilECSImport.php');
        ilECSImport::_deleteByObjId($this->getId());
        
        $query = "DELETE FROM " . $this->getTableName() .
            " WHERE obj_id = " . $this->db->quote($this->getId(), 'integer') . " ";
        $ilDB->manipulate($query);
    }
    
    /**
     * read settings
     */
    public function doRead()
    {
        $query = "SELECT * FROM " . $this->getTableName() .
            " WHERE obj_id = " . $this->db->quote($this->getId(), 'integer') . " ";
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setLocalInformation($row->local_information);
            $this->setRemoteLink($row->remote_link);
            $this->setMID($row->mid);
            $this->setOrganization($row->organization);
            
            $this->doReadCustomFields($row);
        }
    }
    
    /**
     * Read custom fields from db row
     * @param object $a_row
     */
    protected function doReadCustomFields($a_row)
    {
    }
    
    /**
     * create remote object from ECSContent object
     *
     * @param ilECSSetting $a_server
     * @param object $a_ecs_content object with object settings
     * @param int $a_owner
     */
    public function createFromECSEContent(ilECSSetting $a_server, $a_ecs_content, $a_owner)
    {
        $this->create();
                                                
        // won't work for personal workspace
        $this->createReference();
        $this->setPermissions($a_server->getImportId());
                
        include_once './Services/WebServices/ECS/classes/class.ilECSUtils.php';
        $matchable_content = ilECSUtils::getMatchableContent(
            $this->getECSObjectType(),
            $a_server->getServerId(),
            $a_ecs_content,
            $a_owner
        );
        
        include_once './Services/WebServices/ECS/classes/class.ilECSCategoryMapping.php';
        $this->putInTree(ilECSCategoryMapping::getMatchingCategory(
            $a_server->getServerId(),
            $matchable_content
        ));
                        
        $this->updateFromECSContent($a_server, $a_ecs_content, $a_owner);
    }
    
    /**
     * update remote object settings from ecs content
     *
     * @param ilECSSetting $a_server
     * @param object $a_ecs_content object with object settings
     * @param int $a_owner
     */
    public function updateFromECSContent(ilECSSetting $a_server, $a_ecs_content, $a_owner)
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];
        
        $ilLog->write('updateFromECSContent: ' . print_r($a_ecs_content, true));
        
        // Get organisation for owner (ObjectListGUI performance)
        $organisation = null;
        if ($a_owner) {
            include_once './Services/WebServices/ECS/classes/class.ilECSUtils.php';
            $organisation = ilECSUtils::lookupParticipantName($a_owner, $a_server->getServerId());
            $ilLog->write('found organisation: ' . $organisation);
        }
        
        $this->setMID($a_owner); // obsolete?
        $this->setOrganization($organisation);
        $this->setTitle($a_ecs_content->title);
        $this->setDescription($a_ecs_content->abstract);
        $this->setRemoteLink($a_ecs_content->url);
                    
        $ilLog->write('updateCustomFromECSContent');
        $this->updateCustomFromECSContent($a_server, $a_ecs_content);

        // we are updating late so custom values can be set
        
        $ilLog->write('ilObject->update()');
        $this->update();
        
        include_once './Services/WebServices/ECS/classes/class.ilECSUtils.php';
        $matchable_content = ilECSUtils::getMatchableContent(
            $this->getECSObjectType(),
            $a_server->getServerId(),
            $a_ecs_content,
            $a_owner
        );
                        
        // rule-based category mapping
        include_once './Services/WebServices/ECS/classes/class.ilECSCategoryMapping.php';
        ilECSCategoryMapping::handleUpdate(
            $this->getId(),
            $a_server->getServerId(),
            $matchable_content
        );
    }
    
    /**
     * Add advanced metadata to json (export)
     *
     * @param object $a_json
     * @param ilECSSetting $a_server
     * @param array $a_definition
     * @param int $a_mapping_mode
     */
    protected function importMetadataFromJson($a_json, ilECSSetting $a_server, array $a_definition, $a_mapping_mode)
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];
        
        $ilLog->write("importing metadata from json: " . print_r($a_json, true));
        
        include_once('./Services/WebServices/ECS/classes/class.ilECSDataMappingSettings.php');
        include_once('./Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php');
        include_once('./Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
        
        $mappings = ilECSDataMappingSettings::getInstanceByServerId($a_server->getServerId());
        $values_records = ilAdvancedMDValues::getInstancesForObjectId($this->getId(), $this->getType());
        foreach ($values_records as $values_record) {
            // this correctly binds group and definitions
            $values_record->read();
        }
        
        $do_save = false;
        
        foreach ($a_definition as $id => $type) {
            if (is_array($type)) {
                $target = $type[1];
                $type = $type[0];
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
                
                $raw_value = $a_json->$target;
                        
                if ($type == ilECSUtils::TYPE_TIMEPLACE) {
                    if (!is_object($timePlace)) {
                        include_once('./Services/WebServices/ECS/classes/class.ilECSTimePlace.php');
                        if (is_object($raw_value)) {
                            $timePlace = new ilECSTimePlace();
                            $timePlace->loadFromJSON($raw_value);
                        } else {
                            $timePlace = new ilECSTimePlace();
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
                        $additional[$element_id] = array("disabled"=>array("integer", 1));
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
    protected function updateCustomFromECSContent(ilECSSetting $a_server, $ecs_content)
    {
    }
    
    /**
     * Is remote object from same installation?
     *
     * @return boolean
     */
    public function isLocalObject()
    {
        include_once('./Services/WebServices/ECS/classes/class.ilECSExport.php');
        include_once('./Services/WebServices/ECS/classes/class.ilECSImport.php');
        if (ilECSExport::_isRemote(
            ilECSImport::lookupServerId($this->getId()),
            ilECSImport::_lookupEContentId($this->getId())
        )) {
            return false;
        }
        return true;
    }
    
    /**
     * Handle creation
     *
     * called by ilTaskScheduler
     *
     * @param ilECSSetting $a_server
     * @param type $a_econtent_id
     * @param array $a_mids
     */
    public function handleCreate(ilECSSetting $a_server, $a_econtent_id, array $a_mids)
    {
        return $this->handleUpdate($a_server, $a_econtent_id, $a_mids);
    }
    
    /**
     * Handle update event
     *
     * called by ilTaskScheduler
     *
     * @param ilECSSetting $a_server
     * @param int $a_econtent_id
     * @param array $a_mids
     * @return boolean
     */
    public function handleUpdate(ilECSSetting $a_server, $a_econtent_id, array $a_mids)
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];

        // get content details
        include_once('./Services/WebServices/ECS/classes/class.ilECSEContentDetails.php');
        $details = ilECSEContentDetails::getInstance(
            $a_server->getServerId(),
            $a_econtent_id,
            $this->getECSObjectType()
        );
        if (!$details instanceof ilECSEContentDetails) {
            $this->handleDelete($a_server, $a_econtent_id);
            $ilLog->write(__METHOD__ . ': Handling delete of deprecated remote object. DONE');
            return;
        }
        
        $ilLog->write(__METHOD__ . ': Receivers are ' . print_r($details->getReceivers(), true));
        $ilLog->write(__METHOD__ . ': Senders are ' . print_r($details->getSenders(), true));
        
        // check owner (sender mid)
        include_once('./Services/WebServices/ECS/classes/class.ilECSParticipantSettings.php');
        if (!ilECSParticipantSettings::getInstanceByServerId($a_server->getServerId())->isImportAllowed($details->getSenders())) {
            $ilLog->write('Ignoring disabled participant. MID: ' . $details->getOwner());
            return true;
        }
        
        // new mids
        include_once 'Services/WebServices/ECS/classes/class.ilECSImport.php';
        include_once 'Services/WebServices/ECS/classes/class.ilECSConnector.php';
        foreach (array_intersect($a_mids, $details->getReceivers()) as $mid) {
            try {
                $connector = new ilECSConnector($a_server);
                $res = $connector->getResource($this->getECSObjectType(), $a_econtent_id);
                if ($res->getHTTPCode() == ilECSConnector::HTTP_CODE_NOT_FOUND) {
                    continue;
                }
                $json = $res->getResult();
                $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Received json: ' . print_r($json, true));
                if (!is_object($json)) {
                    // try as array (workaround for invalid content)
                    $json = $json[0];
                    if (!is_object($json)) {
                        throw new ilECSConnectorException('invalid json');
                    }
                }
            } catch (ilECSConnectorException $exc) {
                $ilLog->write(__METHOD__ . ': Error parsing result. ' . $exc->getMessage());
                $ilLog->logStack();
                return false;
            }
            
            // Update existing
            
            // Check receiver mid
            if ($obj_id = ilECSImport::_isImported($a_server->getServerId(), $a_econtent_id, $mid)) {
                $ilLog->write(__METHOD__ . ': Handling update for existing object');
                $remote = ilObjectFactory::getInstanceByObjId($obj_id, false);
                if (!$remote instanceof ilRemoteObjectBase) {
                    $ilLog->write(__METHOD__ . ': Cannot instantiate remote object. Got object type ' . $remote->getType());
                    continue;
                }
                $remote->updateFromECSContent($a_server, $json, $details->getMySender());
            } else {
                $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': my sender ' . $details->getMySender() . 'vs mid' . $mid);
                
                $ilLog->write(__METHOD__ . ': Handling create for non existing object');
                $this->createFromECSEContent($a_server, $json, $details->getMySender());
                                
                // update import status
                $ilLog->write(__METHOD__ . ': Updating import status');
                include_once('./Services/WebServices/ECS/classes/class.ilECSImport.php');
                $import = new ilECSImport($a_server->getServerId(), $this->getId());
                $import->setEContentId($a_econtent_id);
                // Store receiver mid
                $import->setMID($mid);
                $import->save();
                
                $ilLog->write(__METHOD__ . ': Sending notification');
                $this->sendNewContentNotification($a_server->getServerId());
            }
        }
        
        $ilLog->write(__METHOD__ . ': done');
        return true;
    }
    
    /**
     * send notifications about new EContent
     */
    protected function sendNewContentNotification($a_server_id)
    {
        include_once('Services/WebServices/ECS/classes/class.ilECSSetting.php');
        $settings = ilECSSetting::getInstanceByServerId($a_server_id);
        if (!count($rcps = $settings->getEContentRecipients())) {
            return;
        }
        
        include_once('./Services/Mail/classes/class.ilMail.php');
        include_once('./Services/Language/classes/class.ilLanguageFactory.php');

        $lang = ilLanguageFactory::_getLanguage();
        $lang->loadLanguageModule('ecs');

        $mail = new ilMail(self::MAIL_SENDER);
        $message = $lang->txt('ecs_' . $this->getType() . '_created_body_a') . "\n\n";
        $message .= $lang->txt('title') . ': ' . $this->getTitle() . "\n";
        if (strlen($desc = $this->getDescription())) {
            $message .= $lang->txt('desc') . ': ' . $desc . "\n";
        }

        include_once('./Services/Link/classes/class.ilLink.php');
        $href = ilLink::_getStaticLink($this->getRefId(), $this->getType(), true);
        $message .= $lang->txt("perma_link") . ': ' . $href . "\n\n";
        $message .= ilMail::_getAutoGeneratedMessageString();

        $mail->sendMail(
            $settings->getEContentRecipientsAsString(),
            '',
            '',
            $lang->txt('ecs_new_econtent_subject'),
            $message,
            array(),
            array('normal')
        );
    }
    
    /**
     * Handle delete event
     *
     * called by ilTaskScheduler
     *
     * @param ilECSSetting $a_server
     * @param int $a_econtent_id
     * @param int $a_mid
     * @return boolean
     */
    public function handleDelete(ilECSSetting $a_server, $a_econtent_id, $a_mid = 0)
    {
        global $DIC;

        $tree = $DIC['tree'];
        $ilLog = $DIC['ilLog'];
        

        include_once('./Services/WebServices/ECS/classes/class.ilECSImport.php');
        
        // there is no information about the original mid anymore.
        // Therefor delete any remote objects with given econtent id
        $obj_ids = ilECSImport::_lookupObjIds($a_server->getServerId(), $a_econtent_id);
        $ilLog->write(__METHOD__ . ': Received obj_ids ' . print_r($obj_ids, true));

        foreach ($obj_ids as $obj_id) {
            $references = ilObject::_getAllReferences($obj_id);
            foreach ($references as $ref_id) {
                if ($tmp_obj = ilObjectFactory::getInstanceByRefId($ref_id, false)) {
                    $ilLog->write(__METHOD__ . ': Deleting obsolete remote course: ' . $tmp_obj->getTitle());
                    $tmp_obj->delete();
                    $tree->deleteTree($tree->getNodeData($ref_id));
                }
                unset($tmp_obj);
            }
        }
        return true;
    }
    
    /**
     * Get all available resources
     *
     * @param ilECSSetting $a_server
     * @param bool $a_sender_only
     * @return array
     */
    public function getAllResourceIds(ilECSSetting $a_server, $a_sender_only = false)
    {
        global $DIC;

        $ilLog = $DIC['ilLog'];
        
        try {
            include_once './Services/WebServices/ECS/classes/class.ilECSConnector.php';
            $connector = new ilECSConnector($a_server);
            $connector->addHeader('X-EcsQueryStrings', $a_sender_only ? 'sender=true' : 'all=true'); // #11301
            $list = $connector->getResourceList($this->getECSObjectType());
            if ($list instanceof ilECSResult) {
                return $list->getResult()->getLinkIds();
            }
        } catch (ilECSConnectorException $exc) {
            $ilLog->write(__METHOD__ . ': Error getting resource list. ' . $exc->getMessage());
        }
    }
}
