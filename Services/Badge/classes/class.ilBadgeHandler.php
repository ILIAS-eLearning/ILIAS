<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\Notifications\Model\ilNotificationConfig;
use ILIAS\Notifications\Model\ilNotificationLink;
use ILIAS\Notifications\Model\ilNotificationParameter;
use ILIAS\Badge\GlobalScreen\BadgeNotificationProvider;

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

    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }


    //
    // setter/getter
    //

    public function isActive(): bool
    {
        return (bool) $this->settings->get("active", "");
    }

    public function setActive(bool $a_value): void
    {
        $this->settings->set("active", (string) $a_value);
    }

    /**
     * @return string[]
     */
    public function getComponents(): array
    {
        $components = $this->settings->get("components", null);
        if ($components) {
            return unserialize($components, ["allowed_classes" => false]);
        }

        return [];
    }


    /**
     * @param string[]|null $a_components
     * @return void
     */
    public function setComponents(array $a_components = null): void
    {
        if (isset($a_components) && count($a_components) === 0) {
            $a_components = null;
        }
        $this->settings->set("components", $a_components !== null
            ? serialize(array_unique($a_components))
            : "");
    }


    //
    // component handling
    //

    protected function getComponent(string $a_id): ?array
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

    public function getProviderInstance(string $a_component_id): ?ilBadgeProvider
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

    public function getComponentCaption(string $a_component_id): string
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
    ): string {
        return $a_component_id . "/" . $a_badge->getId();
    }

    /**
     * Get type instance by unique id (component, type)
     */
    public function getTypeInstanceByUniqueId(
        string $a_id
    ): ?ilBadgeType {
        $parts = explode("/", $a_id);
        $comp_id = $parts[0];
        $type_id = $parts[1];

        $provider = $this->getProviderInstance($comp_id);
        if ($provider) {
            foreach ($provider->getBadgeTypes() as $type) {
                if ($type->getId() === $type_id) {
                    return $type;
                }
            }
        }
        return null;
    }

    /**
     * @return string[]
     */
    public function getInactiveTypes(): array
    {
        $types = $this->settings->get("inactive_types", null);
        if ($types) {
            return unserialize($types, ["allowed_classes" => false]);
        }

        return [];
    }

    /**
     * @param string[]|null $a_types
     * @return void
     */
    public function setInactiveTypes(array $a_types = null): void
    {
        if (is_array($a_types) &&
            !count($a_types)) {
            $a_types = null;
        }
        $this->settings->set("inactive_types", $a_types !== null
            ? serialize(array_unique($a_types))
            : "");
    }

    /**
     * Get badges types
     * @return array<string, ilBadgeType>
     */
    public function getAvailableTypes(): array
    {
        $res = [];

        $inactive = $this->getInactiveTypes();
        foreach ($this->getComponents() as $component_id) {
            $provider = $this->getProviderInstance($component_id);
            if ($provider) {
                foreach ($provider->getBadgeTypes() as $type) {
                    $id = $this->getUniqueTypeId($component_id, $type);
                    if (!in_array($id, $inactive, true)) {
                        $res[$id] = $type;
                    }
                }
            }
        }

        return $res;
    }

    /**
     * Get valid badges types for object type
     * @return array<string, ilBadgeType>
     */
    public function getAvailableTypesForObjType(string $a_object_type): array
    {
        $res = [];

        foreach ($this->getAvailableTypes() as $id => $type) {
            if (in_array($a_object_type, $type->getValidObjectTypes(), true)) {
                $res[$id] = $type;
            }
        }

        return $res;
    }

    /**
     * Get available manual badges for object id
     * @return array<int, string>
     */
    public function getAvailableManualBadges(
        int $a_parent_obj_id,
        string $a_parent_obj_type = null
    ): array {
        $res = [];

        if (!$a_parent_obj_type) {
            $a_parent_obj_type = ilObject::_lookupType($a_parent_obj_id);
        }

        $badges = ilBadge::getInstancesByParentId($a_parent_obj_id);
        foreach (self::getInstance()->getAvailableTypesForObjType($a_parent_obj_type) as $type_id => $type) {
            if (!$type instanceof ilBadgeAuto) {
                foreach ($badges as $badge) {
                    if ($badge->getTypeId() === $type_id &&
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
    public static function updateFromXML(string $a_component_id): void
    {
        $handler = self::getInstance();
        $components = $handler->getComponents();
        $components[] = $a_component_id;
        $handler->setComponents($components);
    }

    /**
     * Remove component definition
     */
    public static function clearFromXML(string $a_component_id): void
    {
        $handler = self::getInstance();
        $components = $handler->getComponents();
        foreach ($components as $idx => $component) {
            if ($component === $a_component_id) {
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
    ): bool {
        if (!$this->isActive()) {
            return false;
        }

        if (!$a_obj_type) {
            $a_obj_type = ilObject::_lookupType($a_obj_id);
        }

        if ($a_obj_type !== "bdga" && !ilContainer::_lookupContainerSetting(
            $a_obj_id,
            ilObjectServiceSettingsGUI::BADGES,
            false
        )) {
            return false;
        }

        return true;
    }

    public function triggerEvaluation(
        string $a_type_id,
        int $a_user_id,
        array $a_params = null
    ): void {
        if (!$this->isActive() || in_array($a_type_id, $this->getInactiveTypes(), true)) {
            return;
        }

        $type = $this->getTypeInstanceByUniqueId($a_type_id);
        if (!$type instanceof ilBadgeAuto) {
            return;
        }

        $new_badges = [];
        foreach (ilBadge::getInstancesByType($a_type_id) as $badge) {
            if ($badge->isActive()) {
                // already assigned?
                if (!ilBadgeAssignment::exists($badge->getId(), $a_user_id)) {
                    if ($type->evaluate($a_user_id, (array) $a_params, $badge->getConfiguration())) {
                        $ass = new ilBadgeAssignment($badge->getId(), $a_user_id);
                        $ass->store();

                        $new_badges[$a_user_id][] = $badge->getId();
                    }
                }
            }
        }

        $this->sendNotification($new_badges);
    }

    /**
     * @param int $a_parent_ref_id
     * @param int|null $a_parent_obj_id
     * @param string|null $a_parent_type
     * @return int[]
     */
    public function getUserIds(
        int $a_parent_ref_id,
        int $a_parent_obj_id = null,
        string $a_parent_type = null
    ): array {
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

    protected function getBasePath(): string
    {
        return ilFileUtils::getWebspaceDir() . "/pub_badges/";
    }

    public function getInstancePath(ilBadgeAssignment $a_ass): string
    {
        $hash = md5($a_ass->getBadgeId() . "_" . $a_ass->getUserId());

        $path = $this->getBasePath() . "instances/" .
            $a_ass->getBadgeId() . "/" .
            floor($a_ass->getUserId() / 1000) . "/";

        ilFileUtils::makeDirParents($path);

        $path .= $hash . ".json";

        return $path;
    }

    public function countStaticBadgeInstances(ilBadge $a_badge): int
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
    ): void {
        foreach (glob($a_path . "/*") as $item) {
            if (is_dir($item)) {
                $this->countStaticBadgeInstancesHelper($a_cnt, $item);
            } elseif (substr($item, -5) === ".json") {
                $a_cnt++;
            }
        }
    }

    public function getBadgePath(ilBadge $a_badge): string
    {
        $hash = md5($a_badge->getId());

        $path = $this->getBasePath() . "badges/" .
            floor($a_badge->getId() / 100) . "/" .
            $hash . "/";

        ilFileUtils::makeDirParents($path);

        return $path;
    }


    //
    // notification
    //

    public function sendNotification(
        array $a_user_map,
        int $a_parent_ref_id = null
    ): void {
        $badges = [];

        foreach ($a_user_map as $user_id => $badge_ids) {
            $user_badges = [];

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

            if (count($user_badges)) {
                // compose and send mail

                $ntf = new ilSystemNotification(false);
                $ntf->setLangModules(["badge"]);

                if (isset($a_parent_ref_id)) {
                    $ntf->setRefId($a_parent_ref_id);
                }
                $ntf->setGotoLangId("badge_notification_parent_goto");

                // user specific language
                $lng = $ntf->getUserLanguage($user_id);

                $ntf->setIntroductionLangId("badge_notification_body");

                $ntf->addAdditionalInfo("badge_notification_badges", implode("\n", $user_badges), true);

                $url = ilLink::_getLink($user_id, "usr", [], "_bdg");
                $ntf->addAdditionalInfo("badge_notification_badges_goto", $url);

                $ntf->setReasonLangId("badge_notification_reason");

                // force email
                $mail = new ilMail(ANONYMOUS_USER_ID);
                $mail->enqueue(
                    ilObjUser::_lookupEmail($user_id),
                    "",
                    "",
                    $lng->txt("badge_notification_subject"),
                    $ntf->composeAndGetMessage($user_id, null, "read", true),
                    []
                );


                // osd
                // bug #24562
                if (ilContext::hasHTML()) {
                    $url = new ilNotificationLink(new ilNotificationParameter('badge_notification_badges_goto', [], 'badge'), $url);
                    $osd_params = ["badge_list" => implode(", ", $user_badges)];

                    $notification = new ilNotificationConfig(BadgeNotificationProvider::NOTIFICATION_TYPE);
                    $notification->setTitleVar("badge_notification_subject", [], "badge");
                    $notification->setShortDescriptionVar("badge_notification_osd", $osd_params, "badge");
                    $notification->setLongDescriptionVar("");
                    $notification->setLinks([$url]);
                    $notification->setIconPath(ilUtil::getImagePath('icon_bdga.svg'));
                    $notification->setValidForSeconds(ilNotificationConfig::TTL_SHORT);
                    $notification->setVisibleForSeconds(ilNotificationConfig::DEFAULT_TTS);
                    $notification->notifyByUsers([$user_id]);
                }
            }
        }
    }
}
