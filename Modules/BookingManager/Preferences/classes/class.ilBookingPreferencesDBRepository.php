<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Booking preferences repo
 *
 * @author killing@leifos.de
 */
class ilBookingPreferencesDBRepository
{
    /**
     * @var ilDBInterface
     */
    protected $db;

    /**
     * @var ilBookingPreferencesFactory
     */
    protected $factory;

    /**
     * Constructor
     */
    public function __construct(ilBookingPreferencesFactory $factory, ilDBInterface $db = null)
    {
        global $DIC;

        $this->db = ($db)
            ? $db
            : $DIC->database();
        $this->factory = $factory;
    }

    /**
     * Get booking preferences for a pool id
     *
     * @param int $a_pool_id
     * @return ilBookingPreferences
     */
    public function getPreferences(int $a_pool_id)
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
            if (!is_array($preferences[$rec["user_id"]]) || !in_array($rec["book_obj_id"], $preferences[$rec["user_id"]])) {
                $preferences[$rec["user_id"]][] = $rec["book_obj_id"];
            }
        }
        return $this->factory->preferences($preferences);
    }

    /**
     * Get booking preferences for a pool id
     *
     * @param int $a_pool_id
     * @param int $a_user_id
     * @return ilBookingPreferences
     */
    public function getPreferencesOfUser(int $a_pool_id, int $a_user_id)
    {
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
            if (!is_array($preferences[$rec["user_id"]]) || !in_array($rec["book_obj_id"], $preferences[$rec["user_id"]])) {
                $preferences[$rec["user_id"]][] = $rec["book_obj_id"];
            }
        }
        return $this->factory->preferences($preferences);
    }

    /**
     * Save all preferences of a pool
     *
     * @param int $a_pool_id
     * @param ilBookingPreferences $
     */
    public function savePreferences(int $a_pool_id, ilBookingPreferences $preferences)
    {
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
     *
     * @param int $a_pool_id
     * @param int $a_user_id
     * @param ilBookingPreferences $preferences
     */
    public function savePreferencesOfUser(int $a_pool_id, int $a_user_id, ilBookingPreferences $preferences)
    {
        $db = $this->db;

        $db->manipulateF(
            "DELETE FROM booking_preferences WHERE " .
            " book_pool_id = %s" .
            " AND user_id = %s",
            array("integer","integer"),
            array($a_pool_id, $a_user_id)
        );

        foreach ($preferences->getPreferences() as $user_id => $obj_ids) {
            if (is_array($obj_ids) && $user_id == $a_user_id) {
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
