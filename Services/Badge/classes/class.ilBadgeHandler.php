<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

/**
 * Class ilBadgeHandler
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilBadgeHandler
{
    protected ilComponentRepository $component_repository;
    protected ilDBInterface $db;
    protected ilTree $tree;
    protected ilLanguage $lng;
    protected ilSetting $settings;
    protected static ?ilBadgeHandler $instance = null;
    
    protected function __construct()
    {
        global $DIC;

        if (isset($DIC["component.repository"])) {
            $this->component_repository = $DIC["component.repository"];
        }
        $this->db = $DIC->database();
        if (isset($DIC["tree"])) {
            $this->tree = $DIC->repositoryTree();
        }
        $this->settings = new ilSetting("bdga");
    }
    
    public static function getInstance() : self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    
    //
    // setter/getter
    //
    
    public function isActive() : bool
    {
        return (bool) $this->settings->get("active", "");
    }
    
    public function setActive(bool $a_value) : void
    {
        $this->settings->set("active", (string) $a_value);
    }
    
    public function isObiActive() : bool
    {
        // see bug #20124
        return false;

        //return $this->settings->get("obi_active", false);
    }
    
    public function setObiActive(bool $a_value) : void
    {
        $this->settings->set("obi_active", $a_value);
    }
    
    public function getObiOrganistation() : string
    {
        return $this->settings->get("obi_organisation", "");
    }
    
    public function setObiOrganisation(string $a_value) : void
    {
        $this->settings->set("obi_organisation", trim($a_value));
    }
    
    public function getObiContact() : string
    {
        return $this->settings->get("obi_contact", "");
    }
    
    public function setObiContact(string $a_value) : void
    {
        $this->settings->set("obi_contact", trim($a_value));
    }
    
    public function getObiSalt() : string
    {
        return $this->settings->get("obi_salt", "");
    }
    
    public function setObiSalt(string $a_value) : void
    {
        $this->settings->set("obi_salt", trim($a_value));
    }
    
    public function getComponents() : array
    {
        $components = $this->settings->get("components", null);
        if ($components) {
            return unserialize($components);
        }
        return array();
    }
    
    public function setComponents(array $a_components = null) : void
    {
        if (is_array($a_components) &&
            !sizeof($a_components)) {
            $a_components = null;
        }
        $this->settings->set("components", $a_components !== null
            ? serialize(array_unique($a_components))
            : "");
    }
            
    
    //
    // component handling
    //
    
    protected function getComponent(string $a_id) : ?array
    {
        if (!$this->component_repository->hasComponentId($a_id)) {
            return null;
        }
        $component = $this->component_repository->getComponentById($a_id);
        return [
            "type" => $component->getType(),
            "name" => $component->getName()
        ];
    }
    
    public function getProviderInstance(string $a_component_id) : ?ilBadgeProvider
    {
        $comp = $this->getComponent($a_component_id);
        if ($comp) {
            $class = "il" . $comp["name"] . "BadgeProvider";
            $file = $comp["type"] . "/" . $comp["name"] . "/classes/class." . $class . ".php";
            if (file_exists($file)) {
                $obj = new $class();
                if ($obj instanceof ilBadgeProvider) {
                    return $obj;
                }
            }
        }
        return null;
    }
    
    public function getComponentCaption(string $a_component_id) : string
    {
        $comp = $this->getComponent($a_component_id);
        if ($comp) {
            return $comp["type"] . "/" . $comp["name"];
        }
        return "";
    }
    
    //
    // types
    //
    
    public function getUniqueTypeId(
        string $a_component_id,
        ilBadgeType $a_badge
    ) : string {
        return $a_component_id . "/" . $a_badge->getId();
    }
    
    /**
     * Get type instance by unique id (component, type)
     */
    public function getTypeInstanceByUniqueId(
        string $a_id
    ) : ?ilBadgeType {
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
        return null;
    }
    
    public function getInactiveTypes() : array
    {
        $types = $this->settings->get("inactive_types", null);
        if ($types) {
            return unserialize($types);
        }
        return array();
    }
    
    public function setInactiveTypes(array $a_types = null) : void
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
     * @return ilBadgeType[]
     */
    public function getAvailableTypes() : array
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
     * @return ilBadgeType[]
     */
    public function getAvailableTypesForObjType(string $a_object_type) : array
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
     * @return array<int,string>
     */
    public function getAvailableManualBadges(
        int $a_parent_obj_id,
        string $a_parent_obj_type = null
    ) : array {
        $res = array();
        
        if (!$a_parent_obj_type) {
            $a_parent_obj_type = ilObject::_lookupType($a_parent_obj_id);
        }

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
     */
    public static function updateFromXML(string $a_component_id) : void
    {
        $handler = self::getInstance();
        $components = $handler->getComponents();
        $components[] = $a_component_id;
        $handler->setComponents($components);
    }
    
    /**
     * Remove component definition
     */
    public static function clearFromXML(string $a_component_id) : void
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
    
    public function isObjectActive(
        int $a_obj_id,
        ?string $a_obj_type = null
    ) : bool {
        if (!$this->isActive()) {
            return false;
        }
        
        if (!$a_obj_type) {
            $a_obj_type = ilObject::_lookupType($a_obj_id);
        }

        if ($a_obj_type != "bdga") {
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
    
    public function triggerEvaluation(
        string $a_type_id,
        int $a_user_id,
        array $a_params = null
    ) : void {
        if (!$this->isActive() ||
            in_array($a_type_id, $this->getInactiveTypes())) {
            return;
        }
                        
        $type = $this->getTypeInstanceByUniqueId($a_type_id);
        if (!$type instanceof ilBadgeAuto) {
            return;
        }
        
        $new_badges = array();
        foreach (ilBadge::getInstancesByType($a_type_id) as $badge) {
            if ($badge->isActive()) {
                // already assigned?
                if (!ilBadgeAssignment::exists($badge->getId(), $a_user_id)) {
                    if ($type->evaluate($a_user_id, (array) $a_params, (array) $badge->getConfiguration())) {
                        $ass = new ilBadgeAssignment($badge->getId(), $a_user_id);
                        $ass->store();
                        
                        $new_badges[$a_user_id][] = $badge->getId();
                    }
                }
            }
        }
        
        $this->sendNotification($new_badges);
    }
    
    public function getUserIds(
        int $a_parent_ref_id,
        int $a_parent_obj_id = null,
        string $a_parent_type = null
    ) : array {
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
                $member_obj = ilCourseParticipants::_getInstanceByObjId($a_parent_obj_id);
                return $member_obj->getMembers();

            case "grp":
                $member_obj = ilGroupParticipants::_getInstanceByObjId($a_parent_obj_id);
                return $member_obj->getMembers();
            
            default:
                // walk path to find course or group object and use members of that object
                /* this does not work since getParticipantsForObject does not exist
                $path = $tree->getPathId($a_parent_ref_id);
                array_pop($path);
                foreach (array_reverse($path) as $path_ref_id) {
                    $type = ilObject::_lookupType($path_ref_id, true);
                    if ($type == "crs" || $type == "grp") {
                        return $this->getParticipantsForObject($path_ref_id, null, $type);
                    }
                }*/
                break;
        }
        return [];
    }
    
    
    //
    // PATH HANDLING (PUBLISHING)
    //
    
    protected function getBasePath() : string
    {
        return ilUtil::getWebspaceDir() . "/pub_badges/";
    }
    
    public function getInstancePath(ilBadgeAssignment $a_ass) : string
    {
        $hash = md5($a_ass->getBadgeId() . "_" . $a_ass->getUserId());
        
        $path = $this->getBasePath() . "instances/" .
            $a_ass->getBadgeId() . "/" .
            floor($a_ass->getUserId() / 1000) . "/";
        
        ilUtil::makeDirParents($path);
        
        $path .= $hash . ".json";
        
        return $path;
    }

    public function countStaticBadgeInstances(ilBadge $a_badge) : int
    {
        $path = $this->getBasePath() . "instances/" . $a_badge->getId();
        $cnt = 0;
        if (is_dir($path)) {
            $this->countStaticBadgeInstancesHelper($cnt, $path);
        }
        return $cnt;
    }
    
    protected function countStaticBadgeInstancesHelper(
        int &$a_cnt,
        string $a_path
    ) : void {
        foreach (glob($a_path . "/*") as $item) {
            if (is_dir($item)) {
                $this->countStaticBadgeInstancesHelper($a_cnt, $item);
            } elseif (substr($item, -5) == ".json") {
                $a_cnt++;
            }
        }
    }
    
    public function getBadgePath(ilBadge $a_badge) : string
    {
        $hash = md5($a_badge->getId());
        
        $path = $this->getBasePath() . "badges/" .
            floor($a_badge->getId() / 100) . "/" .
            $hash . "/";
        
        ilUtil::makeDirParents($path);
        
        return $path;
    }
    
    protected function prepareIssuerJson(string $a_url) : stdClass
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
    
    public function getIssuerStaticUrl() : string
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
    
    public function rebuildIssuerStaticUrl() : void
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
    
    public function sendNotification(
        array $a_user_map,
        int $a_parent_ref_id = null
    ) : void {
        $badges = array();

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
                $mail->enqueue(
                    ilObjUser::_lookupEmail($user_id),
                    null,
                    null,
                    $lng->txt("badge_notification_subject"),
                    $ntf->composeAndGetMessage($user_id, null, "read", true),
                    []
                );
                
                
                // osd
                // bug #24562
                if (ilContext::hasHTML()) {
                    $osd_params = array("badge_list" => "<br />" . implode("<br />", $user_badges));

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
