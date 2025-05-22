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

namespace ILIAS\Repository\Setup;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class RepositoryDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $this->db->manipulateF('DELETE FROM desktop_item WHERE item_id = %s', ['integer'], [1]);
        $this->db->manipulateF('DELETE FROM rep_rec_content_role WHERE ref_id = %s', ['integer'], [1]);
    }

    public function step_2(): void
    {
        $this->db->manipulateF('DELETE FROM il_new_item_grp WHERE type = %s', ['integer'], [2]);
        $this->db->dropTableColumn('il_new_item_grp', 'type');
    }
}
