<?php
require_once __DIR__ . '/../interfaces/interface.ilCalendarScheduleFilter.php';
require_once __DIR__ . '/../../../Modules/Session/classes/class.ilObjSession.php';
require_once __DIR__ . '/../../../Services/Object/classes/class.ilObject.php'; 
// It's good practice to ensure these are available if not autoloaded reliably by ILIAS for these specific paths
require_once __DIR__ . '/class.ilCalendarCategoryAssignments.php';
require_once __DIR__ . '/class.ilCalendarCategories.php';


class ilCalendarScheduleFilterRegisteredSessions implements ilCalendarScheduleFilter
{
    protected int $user_id;
    protected ilLogger $logger;

    public function __construct(int $user_id)
    {
        global $DIC;
        $this->user_id = $user_id;
        $this->logger = $DIC->logger()->cal();
    }

    public function filterCategories(array $categories): array
    {
        // This filter does not operate on categories directly
        return $categories;
    }

    public function modifyEvent(ilCalendarEntry $event)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];

        $cal_entry_id = $event->getEntryId();

        $this->logger->debug("Processing event with cal_entry_id {$cal_entry_id} and context_obj_id {$context_obj_id}.");

        // Query to get obj_id from category
        $set = $ilDB->queryF(
            "SELECT cat.obj_id FROM cal_entries ce
            JOIN cal_cat_assignments ca ON (ca.cal_id = ce.cal_id)
            JOIN cal_categories cat ON (cat.cat_id = ca.cat_id)
            WHERE ce.cal_id = %s",
            ['integer'],
            [$cal_entry_id]
        );

        $row = $ilDB->fetchAssoc($set);
        $obj_id = $row ? (int)$row['obj_id'] : 0;
        $obj_type = ilObject::_lookupType($obj_id);
        $this->logger->debug("Event (cal_id {$cal_entry_id}) resolved obj_id: {$obj_id}, type: {$obj_type}");

        if ($obj_id > 0) {
            $obj_type = ilObject::_lookupType($obj_id);

            if ($obj_type === 'sess') {
                if (ilObjSession::_exists($obj_id, false)) {
                    try {
                        $session_obj = new ilObjSession($obj_id, false);

                        // Allow sessions with "Without Registration" procedure to always show
                        if (method_exists($session_obj, 'getRegistrationType') &&
                            defined('ilMembershipRegistrationSettings::TYPE_NONE') &&
                            $session_obj->getRegistrationType() == \ilMembershipRegistrationSettings::TYPE_NONE) {
                            $this->logger->debug("Session obj_id {$obj_id} is 'Without Registration'. Always showing event (cal_id {$cal_entry_id}).");
                            return $event;
                        }

                        $participants = $session_obj->getMembersObject();

                        if ($participants->isAssigned($this->user_id)) {
                            $this->logger->debug("User {$this->user_id} IS registered for session obj_id {$obj_id} (cal_id {$cal_entry_id}). Keeping event.");
                            return $event;
                        } else {
                            $this->logger->info("User {$this->user_id} NOT registered for session obj_id {$obj_id} (cal_id {$cal_entry_id}). Hiding event.");
                            return false;
                        }
                    } catch (Exception $e) {
                        $this->logger->warning("Error loading session obj_id {$obj_id} or its participants (cal_id {$cal_entry_id}): " . $e->getMessage() . ". Keeping event to be safe.");
                        return $event;
                    }
                } else {
                    $this->logger->debug("Session object (obj_id {$obj_id}, cal_id {$cal_entry_id}) does not exist. Keeping event.");
                    return $event;
                }
            } else {
                $this->logger->debug("Event (cal_id {$cal_entry_id}) obj_id {$obj_id} is not a session (type: {$obj_type}). Keeping event.");
                return $event;
            }
        } else {
            $this->logger->debug("Event (cal_id {$cal_entry_id}) has no obj_id from category. Keeping event.");
            return $event;
        }
    }

    public function addCustomEvents(ilDate $start, ilDate $end, array $categories): array
    {
        // This filter does not add custom events
        return [];
    }
}