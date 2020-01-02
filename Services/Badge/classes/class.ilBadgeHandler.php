<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBadgeHandler
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @package ServicesBadge
 */
class ilBadgeHandler
{
    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilLanguage
     */
    protected $lng;

    protected $settings; // [ilSetting]
    
    protected static $instance; // [ilBadgeHandler]
    
    /**
     * Constructor
     *
     * @return self
     */
    protected function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        if (isset($DIC["tree"])) {
            $this->tree = $DIC->repositoryTree();
        }
        $this->settings = new ilSetting("bdga");
    }
    
    /**
     * Constructor
     *
     * @return self
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    
    //
    // setter/getter
    //
    
    public function isActive()
    {
        return $this->settings->get("active", false);
    }
    
    public function setActive($a_value)
    {
        $this->settings->set("active", (bool) $a_value);
    }
    
    public function isObiActive()
    {
        // see bug #20124
        return false;

        return $this->settings->get("obi_active", false);
    }
    
    public function setObiActive($a_value)
    {
        $this->settings->set("obi_active", (bool) $a_value);
    }
    
    public function getObiOrganistation()
    {
        return $this->settings->get("obi_organisation", null);
    }
    
    public function setObiOrganisation($a_value)
    {
        $this->settings->set("obi_organisation", trim($a_value));
    }
    
    public function getObiContact()
    {
        return $this->settings->get("obi_contact", null);
    }
    
    public function setObiContact($a_value)
    {
        $this->settings->set("obi_contact", trim($a_value));
    }
    
    public function getObiSalt()
    {
        return $this->settings->get("obi_salt", null);
    }
    
    public function setObiSalt($a_value)
    {
        $this->settings->set("obi_salt", trim($a_value));
    }
    
    public function getComponents()
    {
        $components = $this->settings->get("components", null);
        if ($components) {
            return unserialize($components);
        }
        return array();
    }
    
    public function setComponents(array $a_components = null)
    {
        if (is_array($a_components) &&
            !sizeof($a_components)) {
            $a_components = null;
        }
        $this->settings->set("components", $a_components !== null
            ? serialize(array_unique($a_components))
            : null);
    }
            
    
    //
    // component handling
    //
    
    protected function getComponent($a_id)
    {
        $ilDB = $this->db;
        
        // see ilCtrl
        $set = $ilDB->query("SELECT * FROM il_component" .
            " WHERE id = " . $ilDB->quote($a_id, "text"));
        $rec = $ilDB->fetchAssoc($set);
        if ($rec["type"]) {
            return $rec;
        }
    }
    
    /**
     * Get provider instance
     *
     * @param string $a_component_id
     * @return ilBadgeProvider
     */
    public function getProviderInstance($a_component_id)
    {
        $comp = $this->getComponent($a_component_id);
        if ($comp) {
            $class = "il" . $comp["name"] . "BadgeProvider";
            $file = $comp["type"] . "/" . $comp["name"] . "/classes/class." . $class . ".php";
            if (file_exists($file)) {
                include_once $file;
                $obj = new $class;
                if ($obj instanceof ilBadgeProvider) {
                    return $obj;
                }
            }
        }
    }
    
    public function getComponentCaption($a_component_id)
    {
        $comp = $this->getComponent($a_component_id);
        if ($comp) {
            return $comp["type"] . "/" . $comp["name"];
        }
    }
    
    //
    // types
    //
    
    public function getUniqueTypeId($a_component_id, ilBadgeType $a_badge)
    {
        return $a_component_id . "/" . $a_badge->getId();
    }
    
    /**
     * Get type instance by unique id (component, type)
     * @param string $a_id
     * @return ilBadgeType
     */
    public function getTypeInstanceByUniqueId($a_id)
    {
        $parts = explode("/", $a_id);
        $comp_id = $parts[0];
        $type_id = $parts[1];
        $provider = $this->getProviderInstance($comp_id);
        if ($provider) {
            foreach ($provider->getBadgeTypes() as $type) {
                if ($type->getId() == $type_id) {
                    return $type;
                }
            }
        }
    }
    
    public function getInactiveTypes()
    {
        $types = $this->settings->get("inactive_types", null);
        if ($types) {
            return unserialize($types);
        }
        return array();
    }
    
    public function setInactiveTypes(array $a_types = null)
    {
        if (is_array($a_types) &&
            !sizeof($a_types)) {
            $a_types = null;
        }
        $this->settings->set("inactive_types", $a_types !== null
            ? serialize(array_unique($a_types))
            : null);
    }
    
    /**
     * Get badges types
     *
     * @return ilBadgeType[]
     */
    public function getAvailableTypes()
    {
        $res = array();
        
        $inactive = $this->getInactiveTypes();
        foreach ($this->getComponents() as $component_id) {
            $provider = $this->getProviderInstance($component_id);
            if ($provider) {
                foreach ($provider->getBadgeTypes() as $type) {
                    $id = $this->getUniqueTypeId($component_id, $type);
                    if (!in_array($id, $inactive)) {
                        $res[$id] = $type;
                    }
                }
            }
        }

        return $res;
    }
    
    /**
     * Get valid badges types for object type
     *
     * @param string $a_object_type
     * @return ilBadgeType[]
     */
    public function getAvailableTypesForObjType($a_object_type)
    {
        $res = array();
        
        foreach ($this->getAvailableTypes() as $id => $type) {
            if (in_array($a_object_type, $type->getValidObjectTypes())) {
                $res[$id] = $type;
            }
        }
        
        return $res;
    }
        
    /**
     * Get available manual badges for object id
     *
     * @param int $a_parent_obj_id
     * @param string $a_parent_obj_type
     * @return array id,title
     */
    public function getAvailableManualBadges($a_parent_obj_id, $a_parent_obj_type = null)
    {
        $res = array();
        
        if (!$a_parent_obj_type) {
            $a_parent_obj_type = ilObject::_lookupType($a_parent_obj_id);
        }
        
        include_once "./Services/Badge/classes/class.ilBadge.php";
        $badges = ilBadge::getInstancesByParentId($a_parent_obj_id);
        foreach (ilBadgeHandler::getInstance()->getAvailableTypesForObjType($a_parent_obj_type) as $type_id => $type) {
            if (!$type instanceof ilBadgeAuto) {
                foreach ($badges as $badge) {
                    if ($badge->getTypeId() == $type_id &&
                        $badge->isActive()) {
                        $res[$badge->getId()] = $badge->getTitle();
                    }
                }
            }
        }
        
        asort($res);
        return $res;
    }
    
    
    
    //
    // service/module definition
    //
    
    /**
     * Import component definition
     *
     * @param string $a_component_id
     */
    public static function updateFromXML($a_component_id)
    {
        $handler = self::getInstance();
        $components = $handler->getComponents();
        $components[] = $a_component_id;
        $handler->setComponents($components);
    }
    
    /**
     * Remove component definition
     *
     * @param string $a_component_id
     */
    public static function clearFromXML($a_component_id)
    {
        $handler = self::getInstance();
        $components = $handler->getComponents();
        foreach ($components as $idx => $component) {
            if ($component == $a_component_id) {
                unset($components[$idx]);
            }
        }
        $handler->setComponents($components);
    }
    
    
    //
    // helper
    //
    
    public function isObjectActive($a_obj_id, $a_obj_type = null)
    {
        if (!$this->isActive()) {
            return false;
        }
        
        if (!$a_obj_type) {
            $a_obj_type = ilObject::_lookupType($a_obj_id);
        }

        if ($a_obj_type != "bdga") {
            include_once 'Services/Container/classes/class.ilContainer.php';
            include_once 'Services/Object/classes/class.ilObjectServiceSettingsGUI.php';
            if (!ilContainer::_lookupContainerSetting(
                $a_obj_id,
                ilObjectServiceSettingsGUI::BADGES,
                false
            )) {
                return false;
            }
        }
                
        return true;
    }
    
    public function triggerEvaluation($a_type_id, $a_user_id, array $a_params = null)
    {
        if (!$this->isActive() ||
            in_array($a_type_id, $this->getInactiveTypes())) {
            return;
        }
                        
        $type = $this->getTypeInstanceByUniqueId($a_type_id);
        if (!$type ||
            !$type instanceof ilBadgeAuto) {
            return;
        }
        
        include_once "Services/Badge/classes/class.ilBadge.php";
        include_once "Services/Badge/classes/class.ilBadgeAssignment.php";
        $new_badges = array();
        foreach (ilBadge::getInstancesByType($a_type_id) as $badge) {
            if ($badge->isActive()) {
                // already assigned?
                if (!ilBadgeAssignment::exists($badge->getId(), $a_user_id)) {
                    if ((bool) $type->evaluate($a_user_id, (array) $a_params, (array) $badge->getConfiguration())) {
                        $ass = new ilBadgeAssignment($badge->getId(), $a_user_id);
                        $ass->store();
                        
                        $new_badges[$a_user_id][] = $badge->getId();
                    }
                }
            }
        }
        
        $this->sendNotification($new_badges);
    }
    
    public function getUserIds($a_parent_ref_id, $a_parent_obj_id = null, $a_parent_type = null)
    {
        $tree = $this->tree;
                
        if (!$a_parent_obj_id) {
            $a_parent_obj_id = ilObject::_lookupObjectId($a_parent_ref_id);
        }
        if (!$a_parent_type) {
            $a_parent_type = ilObject::_lookupType($a_parent_obj_id);
        }
        
        // try to get participants from (parent) course/group
        switch ($a_parent_type) {
            case "crs":
                include_once "Modules/Course/classes/class.ilCourseParticipants.php";
                $member_obj = ilCourseParticipants::_getInstanceByObjId($a_parent_obj_id);
                return $member_obj->getMembers();

            case "grp":
                include_once "Modules/Group/classes/class.ilGroupParticipants.php";
                $member_obj = ilGroupParticipants::_getInstanceByObjId($a_parent_obj_id);
                return $member_obj->getMembers();
            
            default:
                // walk path to find course or group object and use members of that object
                $path = $tree->getPathId($a_parent_ref_id);
                array_pop($path);
                foreach (array_reverse($path) as $path_ref_id) {
                    $type = ilObject::_lookupType($path_ref_id, true);
                    if ($type == "crs" || $type == "grp") {
                        return $this->getParticipantsForObject($path_ref_id, null, $type);
                    }
                }
                break;
        }
    }
    
    
    //
    // PATH HANDLING (PUBLISHING)
    //
    
    protected function getBasePath()
    {
        return ilUtil::getWebspaceDir() . "/pub_badges/";
    }
    
    public function getInstancePath(ilBadgeAssignment $a_ass)
    {
        $hash = md5($a_ass->getBadgeId() . "_" . $a_ass->getUserId());
        
        $path = $this->getBasePath() . "instances/" .
            $a_ass->getBadgeId() . "/" .
            floor($a_ass->getUserId()/1000) . "/";
        
        ilUtil::makeDirParents($path);
        
        $path .= $hash . ".json";
        
        return $path;
    }

    public function countStaticBadgeInstances(ilBadge $a_badge)
    {
        $path = $this->getBasePath() . "instances/" . $a_badge->getId();
        $cnt = 0;
        if (is_dir($path)) {
            $this->countStaticBadgeInstancesHelper($cnt, $path);
        }
        return $cnt;
    }
    
    protected function countStaticBadgeInstancesHelper(&$a_cnt, $a_path)
    {
        foreach (glob($a_path . "/*") as $item) {
            if (is_dir($item)) {
                $this->countStaticBadgeInstancesHelper($a_cnt, $item);
            } elseif (substr($item, -5) == ".json") {
                $a_cnt++;
            }
        }
    }
    
    public function getBadgePath(ilBadge $a_badge)
    {
        $hash = md5($a_badge->getId());
        
        $path = $this->getBasePath() . "badges/" .
            floor($a_badge->getId()/100) . "/" .
            $hash . "/";
        
        ilUtil::makeDirParents($path);
        
        return $path;
    }
    
    protected function prepareIssuerJson($a_url)
    {
        $json = new stdClass();
        $json->{"@context"} = "https://w3id.org/openbadges/v1";
        $json->type = "Issuer";
        $json->id = $a_url;
        $json->name = $this->getObiOrganistation();
        $json->url = ILIAS_HTTP_PATH . "/";
        $json->email = $this->getObiContact();
        
        return $json;
    }
    
    public function getIssuerStaticUrl()
    {
        $path = $this->getBasePath() . "issuer/";
        ilUtil::makeDirParents($path);
        $path .= "issuer.json";
        
        $url = ILIAS_HTTP_PATH . substr($path, 1);
        
        if (!file_exists($path)) {
            $json = json_encode($this->prepareIssuerJson($url));
            file_put_contents($path, $json);
        }
        
        return $url;
    }
    
    public function rebuildIssuerStaticUrl()
    {
        $path = $this->getBasePath() . "issuer/issuer.json";
        if (file_exists($path)) {
            unlink($path);
        }
        $this->getIssuerStaticUrl();
    }
    
    
    //
    // notification
    //
    
    public function sendNotification(array $a_user_map, $a_parent_ref_id = null)
    {
        $badges = array();
        
        include_once "Services/Badge/classes/class.ilBadge.php";
        include_once "Services/Badge/classes/class.ilBadgeAssignment.php";
        include_once "Services/Notification/classes/class.ilSystemNotification.php";
        include_once "Services/Link/classes/class.ilLink.php";
        
        foreach ($a_user_map as $user_id => $badge_ids) {
            $user_badges = array();
            
            foreach ($badge_ids as $badge_id) {
                // making extra sure
                if (!ilBadgeAssignment::exists($badge_id, $user_id)) {
                    continue;
                }
                
                if (!array_key_exists($badge_id, $badges)) {
                    $badges[$badge_id] = new ilBadge($badge_id);
                }
                
                $badge = $badges[$badge_id];
                
                $user_badges[] = $badge->getTitle();
            }
            
            if (sizeof($user_badges)) {
                // compose and send mail
                
                $ntf = new ilSystemNotification(false);
                $ntf->setLangModules(array("badge"));
                
                $ntf->setRefId($a_parent_ref_id);
                $ntf->setGotoLangId("badge_notification_parent_goto");
                
                // user specific language
                $lng = $ntf->getUserLanguage($user_id);
                
                $ntf->setIntroductionLangId("badge_notification_body");
                                
                $ntf->addAdditionalInfo("badge_notification_badges", implode("\n", $user_badges), true);
                
                $url = ilLink::_getLink($user_id, "usr", array(), "_bdg");
                $ntf->addAdditionalInfo("badge_notification_badges_goto", $url);
                            
                $ntf->setReasonLangId("badge_notification_reason");

                // force email
                $mail = new ilMail(ANONYMOUS_USER_ID);
                $mail->enableSOAP(false);
                $mail->sendMail(
                    ilObjUser::_lookupEmail($user_id),
                    null,
                    null,
                    $lng->txt("badge_notification_subject"),
                    $ntf->composeAndGetMessage($user_id, null, "read", true),
                    null,
                    array("system")
                );
                
                
                // osd
                // bug #24562
                if (ilContext::hasHTML()) {
                    $osd_params = array("badge_list" => "<br />" . implode("<br />", $user_badges));

                    require_once "Services/Notifications/classes/class.ilNotificationConfig.php";
                    $notification = new ilNotificationConfig("osd_main");
                    $notification->setTitleVar("badge_notification_subject", array(), "badge");
                    $notification->setShortDescriptionVar("badge_notification_osd", $osd_params, "badge");
                    $notification->setLongDescriptionVar("", $osd_params, "");
                    $notification->setAutoDisable(false);
                    $notification->setLink($url);
                    $notification->setIconPath(ilUtil::getImagePath('icon_bdga.svg'));
                    $notification->setValidForSeconds(ilNotificationConfig::TTL_SHORT);
                    $notification->setVisibleForSeconds(ilNotificationConfig::DEFAULT_TTS);
                    $notification->notifyByUsers(array($user_id));
                }
            }
        }
    }
}
