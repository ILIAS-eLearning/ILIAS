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

declare(strict_types=1);

namespace ILIAS\BookingManager\Setup;

class ilBookingManagerDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        if (!$this->db->indexExistsByFields('booking_reservation', ['context_obj_id'])) {
            $this->db->addIndex('booking_reservation', ['context_obj_id'], 'i5');
        }
    }

    public function step_2(): void
    {
        if (!$this->db->indexExistsByFields('booking_schedule', ['pool_id'])) {
            $this->db->addIndex('booking_schedule', ['pool_id'], 'i1');
        }
    }

    public function step_3(): void
    {
        if (!$this->db->indexExistsByFields('booking_object', ['schedule_id'])) {
            $this->db->addIndex('booking_object', ['schedule_id'], 'i2');
        }
    }

    public function step_4(): void
    {
        $db = $this->db;
        if (!$db->tableExists("book_sel_object")) {
            $fields = array(
                "user_id" => array(
                    "type" => "integer",
                    "notnull" => true,
                    "length" => 4,
                    "default" => 0
                ),
                "object_id" => array(
                    "type" => "integer",
                    "notnull" => true,
                    "length" => 4,
                    "default" => 0
                )
            );
            $db->createTable("book_sel_object", $fields);
            $db->addPrimaryKey("book_sel_object", ["user_id", "object_id"]);
        }
    }

    public function step_5(): void
    {
        $db = $this->db;
        if (!$db->tableColumnExists("book_sel_object", "pool_id")) {
            $db->addTableColumn("book_sel_object", "pool_id", [
                "type" => "integer",
                "notnull" => true,
                "length" => 4,
                "default" => 0
            ]);
        }
    }

    public function step_6(): void
    {
        $db = $this->db;
        if (!$db->tableColumnExists("booking_settings", "messages")) {
            $db->addTableColumn("booking_settings", "messages", [
                "type" => "integer",
                "notnull" => true,
                "length" => 4,
                "default" => 0
            ]);
        }
    }

    public function step_7(): void
    {
        $db = $this->db;
        if (!$db->tableColumnExists("booking_reservation", "message")) {
            $db->addTableColumn("booking_reservation", "message", [
                "type" => "text",
                "notnull" => true,
                "length" => 4000,
                "default" => ""
            ]);
        }
    }

    public function step_8(): void
    {
        if (!$this->db->tableColumnExists('booking_object', 'obj_info_rid')) {
            $this->db->addTableColumn(
                'booking_object',
                'obj_info_rid',
                [
                    'type' => 'text',
                    'notnull' => false,
                    'length' => 64,
                    'default' => null
                ]
            );
        }
    }

    public function step_9(): void
    {
        if (!$this->db->tableColumnExists('booking_object', 'book_info_rid')) {
            $this->db->addTableColumn(
                'booking_object',
                'book_info_rid',
                [
                    'type' => 'text',
                    'notnull' => false,
                    'length' => 64,
                    'default' => null
                ]
            );
        }
    }

}
