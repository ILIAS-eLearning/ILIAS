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

namespace ILIAS\Wiki\Setup;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilWikiDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $db = $this->db;
        foreach (["int_links", "ext_links", "footnotes", "num_ratings", "num_words", "avg_rating", "deleted"] as $field) {
            $db->modifyTableColumn('wiki_stat_page', $field, array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true,
                'default' => 0
            ));
        }
    }

    public function step_2(): void
    {
        $db = $this->db;
        foreach (["num_chars"] as $field) {
            $db->modifyTableColumn('wiki_stat_page', $field, array(
                'type' => 'integer',
                'length' => 8,
                'notnull' => true,
                'default' => 0
            ));
        }
    }

    public function step_3(): void
    {
        $db = $this->db;
        foreach (["num_pages", "del_pages", "avg_rating"] as $field) {
            $db->modifyTableColumn('wiki_stat', $field, array(
                'type' => 'integer',
                'length' => 4,
                'notnull' => true,
                'default' => 0
            ));
        }
    }
}
