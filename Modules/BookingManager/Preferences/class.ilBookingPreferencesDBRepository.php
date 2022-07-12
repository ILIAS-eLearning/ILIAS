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

/**
 * Booking preferences repo
 * @author Alexander Killing <killing@leifos.de>
 */
class ilBookingPreferencesDBRepository
{
    protected ilDBInterface $db;
    protected ILIAS\BookingManager\InternalDataService $data;

    public function __construct(
        ILIAS\BookingManager\InternalDataService $data,
        ilDBInterface $db = null
    ) {
        global $DIC;

        $this->db = ($db)
            ?: $DIC->database();
        $this->data = $data;
    }

    /**
     * Get booking preferences for a pool id
     */
    public function getPreferences(int $a_pool_id) : ilBookingPreferences
    {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT * FROM booking_preferences " .
            " WHERE book_pool_id = %s ",
            array("integer"),
            array($a_pool_id)
        );
        $preferences = [];
        while ($rec = $db->fetchAssoc($set)) {
            if (!isset($preferences[$rec["user_id"]]) || !in_array($rec["book_obj_id"], $preferences[$rec["user_id"]], true)) {
                $preferences[$rec["user_id"]][] = $rec["book_obj_id"];
            }
        }
        return $this->data->preferences($preferences);
    }

    /**
     * Get booking preferences for a pool id
     */
    public function getPreferencesOfUser(
        int $a_pool_id,
        int $a_user_id
    ) : ilBookingPreferences {
        $db = $this->db;

        $set = $db->queryF(
            "SELECT * FROM booking_preferences " .
            " WHERE book_pool_id = %s " .
            " AND user_id = %s ",
            array("integer", "integer"),
            array($a_pool_id, $a_user_id)
        );
        $preferences = [];
        while ($rec = $db->fetchAssoc($set)) {
            if (!isset($preferences[$rec["user_id"]]) || !in_array($rec["book_obj_id"], $preferences[$rec["user_id"]], true)) {
                $preferences[$rec["user_id"]][] = $rec["book_obj_id"];
            }
        }
        return $this->data->preferences($preferences);
    }

    /**
     * Save all preferences of a pool
     */
    public function savePreferences(
        int $a_pool_id,
        ilBookingPreferences $preferences
    ) : void {
        $db = $this->db;
        
        $db->manipulateF(
            "DELETE FROM booking_preferences WHERE " .
            " book_pool_id = %s",
            array("integer"),
            array($a_pool_id)
        );

        foreach ($preferences as $user_id => $obj_ids) {
            if (is_array($obj_ids) && $user_id > 0) {
                foreach ($obj_ids as $obj_id) {
                    $db->insert("booking_preferences", array(
                        "book_pool_id" => array("integer", $a_pool_id),
                        "user_id" => array("integer", $user_id),
                        "book_obj_id" => array("integer", $obj_id),
                    ));
                }
            }
        }
    }

    /**
     * Save all preferences of a user for a pool
     */
    public function savePreferencesOfUser(
        int $a_pool_id,
        int $a_user_id,
        ilBookingPreferences $preferences
    ) : void {
        $db = $this->db;

        $db->manipulateF(
            "DELETE FROM booking_preferences WHERE " .
            " book_pool_id = %s" .
            " AND user_id = %s",
            array("integer","integer"),
            array($a_pool_id, $a_user_id)
        );

        foreach ($preferences->getPreferences() as $user_id => $obj_ids) {
            if (is_array($obj_ids) && $user_id === $a_user_id) {
                foreach ($obj_ids as $obj_id) {
                    $db->insert("booking_preferences", array(
                        "book_pool_id" => array("integer", $a_pool_id),
                        "user_id" => array("integer", $user_id),
                        "book_obj_id" => array("integer", $obj_id),
                    ));
                }
            }
        }
    }
}
