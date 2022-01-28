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

namespace ILIAS\Style\Content\Setup;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilStyleDBUpdateSteps implements \ilDatabaseUpdateSteps
{
    protected \ilDBInterface $db;

    public function prepare(\ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function step_1() : void
    {
        if (!$this->db->tableExists('style_char_title')) {
            $fields = [
                'type' => [
                    'type' => 'text',
                    'length' => 30,
                    'notnull' => true
                ],
                'characteristic' => [
                    'type' => 'text',
                    'length' => 30,
                    'notnull' => true
                ],
                'lang' => [
                    'type' => 'text',
                    'length' => 2,
                    'notnull' => true
                ],
                'title' => [
                    'type' => 'text',
                    'length' => 200,
                    'notnull' => false
                ]
            ];

            $this->db->createTable('style_char_title', $fields);
            $this->db->addPrimaryKey('style_char_title', ['type', 'characteristic', 'lang']);
        }
    }

    public function step_2()
    {
        $this->db->dropPrimaryKey('style_char_title');
        if (!$this->db->tableColumnExists('style_char_title', 'style_id')) {
            $this->db->addTableColumn('style_char_title', 'style_id', array(
                "type" => "integer",
                "notnull" => true,
                "length" => 4
            ));
        }
        $this->db->addPrimaryKey('style_char_title', ['style_id', 'type', 'characteristic', 'lang']);
    }

    public function step_3()
    {
        if (!$this->db->tableColumnExists('style_char', 'order_nr')) {
            $this->db->addTableColumn('style_char', 'order_nr', array(
                "type" => "integer",
                "notnull" => true,
                "length" => 4,
                "default" => 0
            ));
        }
    }

    public function step_4()
    {
        if (!$this->db->tableColumnExists('style_char', 'deprecated')) {
            $this->db->addTableColumn('style_char', 'deprecated', array(
                "type" => "integer",
                "notnull" => true,
                "length" => 1,
                "default" => 0
            ));
        }
    }

    public function step_5()
    {
        $this->db->renameTableColumn('style_char', "deprecated", 'outdated');
    }
}
