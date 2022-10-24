<?php

declare(strict_types=1);

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

namespace ILIAS\BookingManager\Setup;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
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
}
