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

use ILIAS\Setup;
use ILIAS\Setup\Environment;
use ILIAS\Setup\CLI\IOWrapper;

class PRGUpdateCRSRefLPSettingMigration implements Setup\Migration
{
    private const DEFAULT_AMOUNT_OF_STEPS = 1000;
    private ilDBInterface $db;

    /**
     * @var IOWrapper
     */
    private mixed $io;

    public function getLabel() : string
    {
        return "Update LP Settings of Course References";
    }

    public function getDefaultAmountOfStepsPerRun() : int
    {
        return self::DEFAULT_AMOUNT_OF_STEPS;
    }

    public function getPreconditions(Environment $environment) : array
    {
        return [
            new ilDatabaseInitializedObjective(),
            new ilDatabaseUpdatedObjective()
        ];
    }

    public function prepare(Environment $environment) : void
    {
        $this->db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
    }

    /**
     * @throws Exception
     */
    public function step(Environment $environment) : void
    {
        $query = "SELECT distinct od.obj_id AS objid " . PHP_EOL .
            "FROM object_reference oref " . PHP_EOL .
            "JOIN object_data od ON od.obj_id = oref.obj_id AND od.type = 'crsr'" . PHP_EOL .
            "JOIN tree ON oref.ref_id = tree.child" . PHP_EOL .
            "JOIN tree t2 ON t2.path > tree.path" . PHP_EOL .
            "JOIN object_reference oref2 ON oref2.ref_id = t2.child" . PHP_EOL .
            "JOIN object_data od2 ON od2.obj_id = oref2.obj_id AND od2.type = 'prg'" . PHP_EOL .
            "WHERE od.obj_id NOT IN (" . PHP_EOL .
            "SELECT obj_id FROM ut_lp_settings WHERE obj_type = 'crsr' AND u_mode = "
            . ilLPObjSettings::LP_MODE_COURSE_REFERENCE . PHP_EOL .
            ")" . PHP_EOL .
            "LIMIT 1";

        $result = $this->db->query($query);
        $row = $this->db->fetchAssoc($result);
        $q = 'DELETE FROM ut_lp_settings WHERE obj_id = ' . (int) $row['objid'];
        $this->db->manipulate($q);

        $q = 'INSERT INTO ut_lp_settings (obj_id, obj_type, u_mode)' . PHP_EOL .
            'VALUES (' .
            (int) $row['objid'] .
            ', "crsr", ' .
            ilLPObjSettings::LP_MODE_COURSE_REFERENCE .
            ');';
        $this->db->manipulate($q);
    }

    public function getRemainingAmountOfSteps() : int
    {
        $query = "SELECT count(distinct od.obj_id) AS cnt " . PHP_EOL .
            "FROM object_reference oref " . PHP_EOL .
            "JOIN object_data od ON od.obj_id = oref.obj_id AND od.type = 'crsr'" . PHP_EOL .
            "JOIN tree ON oref.ref_id = tree.child" . PHP_EOL .
            "JOIN tree t2 ON t2.path > tree.path" . PHP_EOL .
            "JOIN object_reference oref2 ON oref2.ref_id = t2.child" . PHP_EOL .
            "JOIN object_data od2 ON od2.obj_id = oref2.obj_id AND od2.type = 'prg'" . PHP_EOL .
            "WHERE od.obj_id NOT IN (" . PHP_EOL .
            "SELECT obj_id FROM ut_lp_settings WHERE obj_type = 'crsr' AND u_mode = "
            . ilLPObjSettings::LP_MODE_COURSE_REFERENCE . PHP_EOL .
            ")";
        $result = $this->db->query($query);
        $row = $this->db->fetchAssoc($result);

        return (int) $row['cnt'];
    }
}