<?php

declare(strict_types=0);
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
 * Class ilLOEditorGUI
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
class ilLORandomTestQuestionPools
{
    protected int $container_id = 0;
    protected int $objective_id = 0;
    protected int $test_type = 0;
    protected int $test_id = 0;
    protected int $qpl_seq = 0;
    protected int $limit = 50;

    protected ilDBInterface $db;

    public function __construct(int $a_container_id, int $a_objective_id, int $a_test_type, int $a_qpl_sequence)
    {
        global $DIC;

        $this->container_id = $a_container_id;
        $this->objective_id = $a_objective_id;
        $this->test_type = $a_test_type;
        $this->qpl_seq = $a_qpl_sequence;

        $this->read();
    }

    public static function lookupLimit(int $a_container_id, int $a_objective_id, int $a_test_type): int
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = 'SELECT * FROM loc_rnd_qpl ' .
            'WHERE container_id = ' . $ilDB->quote($a_container_id, 'integer') . ' ' .
            'AND objective_id = ' . $ilDB->quote($a_objective_id, 'integer') . ' ' .
            'AND tst_type = ' . $ilDB->quote($a_test_type, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return $row->percentage;
        }
        return 0;
    }

    public static function lookupSequences(int $a_container_id, int $a_objective_id, int $a_test_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = 'SELECT * FROM loc_rnd_qpl ' .
            'WHERE container_id = ' . $ilDB->quote($a_container_id, 'integer') . ' ' .
            'AND objective_id = ' . $ilDB->quote($a_objective_id, 'integer') . ' ' .
            'AND tst_id = ' . $ilDB->quote($a_test_id, 'integer');

        $res = $ilDB->query($query);
        $sequences = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $sequences[] = $row->qp_seq;
        }
        return $sequences;
    }

    public static function lookupSequencesByType(
        int $a_container_id,
        int $a_objective_id,
        int $a_test_id,
        int $a_test_type
    ): array {
        global $DIC;

        $ilDB = $DIC->database();
        $query = 'SELECT * FROM loc_rnd_qpl ' .
            'WHERE container_id = ' . $ilDB->quote($a_container_id, 'integer') . ' ' .
            'AND objective_id = ' . $ilDB->quote($a_objective_id, 'integer') . ' ' .
            'AND tst_id = ' . $ilDB->quote($a_test_id, 'integer') . ' ' .
            'AND tst_type = ' . $ilDB->quote($a_test_type, 'integer');

        $res = $ilDB->query($query);
        $sequences = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $sequences[] = $row->qp_seq;
        }
        return $sequences;
    }

    public static function lookupObjectiveIdsBySequence(int $a_container_id, int $a_seq_id): array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = 'SELECT objective_id FROM loc_rnd_qpl ' .
            'WHERE container_id = ' . $ilDB->quote($a_container_id, 'integer') . ' ' .
            'AND qp_seq = ' . $ilDB->quote($a_seq_id, 'integer');
        $res = $ilDB->query($query);
        $objectiveIds = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $objectiveIds[] = $row->objective_id;
        }
        return $objectiveIds;
    }

    public function setContainerId(int $a_id): void
    {
        $this->container_id = $a_id;
    }

    public function getContainerId(): int
    {
        return $this->container_id;
    }

    public function setObjectiveId(int $a_id): void
    {
        $this->objective_id = $a_id;
    }

    public function getObjectiveId(): int
    {
        return $this->objective_id;
    }

    public function setTestType(int $a_type): void
    {
        $this->test_type = $a_type;
    }

    public function getTestType(): int
    {
        return $this->test_type;
    }

    public function setTestId(int $a_id): void
    {
        $this->test_id = $a_id;
    }

    public function getTestId(): int
    {
        return $this->test_id;
    }

    public function setQplSequence(int $a_id): void
    {
        $this->qpl_seq = $a_id;
    }

    public function getQplSequence(): int
    {
        return $this->qpl_seq;
    }

    public function setLimit(int $a_id): void
    {
        $this->limit = $a_id;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function copy(int $a_copy_id, int $a_new_course_id, int $a_new_objective_id): void
    {
        $options = ilCopyWizardOptions::_getInstance($a_copy_id);
        $mappings = $options->getMappings();

        foreach (self::lookupSequences(
            $this->getContainerId(),
            $this->getContainerId(),
            $this->getTestId()
        ) as $sequence) {
            // not nice
            $this->setQplSequence($sequence);
            $this->read();

            $mapped_id = 0;
            $test_ref_id = 0;
            foreach (ilObject::_getAllReferences($this->getTestId()) as $ref_id) {
                $test_ref_id = $ref_id;
                $mapped_id = $mappings[$ref_id];
            }
            if (!$mapped_id) {
                ilLoggerFactory::getLogger('crs')->debug('No test mapping found for random question pool assignment: ' . $this->getTestId() . ' ' . $sequence);
                continue;
            }

            // Mapping for sequence
            $new_question_info = $mappings[$test_ref_id . '_rndSelDef_' . $this->getQplSequence()];
            $new_question_arr = explode('_', $new_question_info);
            if (!isset($new_question_arr[2]) || !$new_question_arr[2]) {
                //ilLoggerFactory::getLogger('crs')->debug(print_r($mappings,TRUE));
                ilLoggerFactory::getLogger('crs')->debug('Found invalid or no mapping format of random question id mapping: ' . print_r(
                    $new_question_arr,
                    true
                ));
                continue;
            }

            $new_ass = new self(
                $a_new_course_id,
                $a_new_objective_id,
                $this->getTestType(),
                $new_question_arr[2]
            );
            $new_ass->setTestId($mapped_id);
            $new_ass->setLimit($this->getLimit());
            $new_ass->create();
        }
    }

    public function read(): void
    {
        $query = 'SELECT * FROM loc_rnd_qpl ' .
            'WHERE container_id = ' . $this->db->quote($this->getContainerId(), 'integer') . ' ' .
            'AND objective_id = ' . $this->db->quote($this->getObjectiveId(), 'integer') . ' ' .
            'AND tst_type = ' . $this->db->quote($this->getTestType(), 'integer') . ' ' .
            'AND qp_seq = ' . $this->db->quote($this->getQplSequence(), 'integer');

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setLimit($row->percentage);
            $this->setTestId($row->tst_id);
        }
    }

    public function delete(): void
    {
        $query = 'DELETE FROM loc_rnd_qpl ' .
            'WHERE container_id = ' . $this->db->quote($this->getContainerId(), 'integer') . ' ' .
            'AND objective_id = ' . $this->db->quote($this->getObjectiveId(), 'integer') . ' ' .
            'AND tst_type = ' . $this->db->quote($this->getTestType(), 'integer') . ' ' .
            'AND qp_seq = ' . $this->db->quote($this->getQplSequence(), 'integer');
        $this->db->manipulate($query);
    }

    public static function deleteForObjectiveAndTestType(int $a_course_id, int $a_objective_id, int $a_tst_type): void
    {
        $db = $GLOBALS['DIC']->database();

        $query = 'DELETE FROM loc_rnd_qpl ' .
            'WHERE container_id = ' . $db->quote($a_course_id, 'integer') . ' ' .
            'AND objective_id = ' . $db->quote($a_objective_id, 'integer') . ' ' .
            'AND tst_type = ' . $db->quote($a_tst_type, 'integer');
        $db->manipulate($query);
    }

    public function create(): void
    {
        $query = 'INSERT INTO loc_rnd_qpl ' .
            '(container_id, objective_id, tst_type, tst_id, qp_seq, percentage) ' .
            'VALUES ( ' .
            $this->db->quote($this->getContainerId(), 'integer') . ', ' .
            $this->db->quote($this->getObjectiveId(), 'integer') . ', ' .
            $this->db->quote($this->getTestType(), 'integer') . ', ' .
            $this->db->quote($this->getTestId(), 'integer') . ', ' .
            $this->db->quote($this->getQplSequence(), 'integer') . ', ' .
            $this->db->quote($this->getLimit(), ilDBConstants::T_INTEGER) . ' ' .
            ')';
        $this->db->manipulate($query);
    }

    public static function toXml(ilXmlWriter $writer, int $a_objective_id): void
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = 'SELECT * FROM loc_rnd_qpl ' .
            'WHERE objective_id = ' . $ilDB->quote($a_objective_id, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $writer->xmlElement(
                'Test',
                array(
                    'type' => ilLOXmlWriter::TYPE_TST_RND,
                    'objId' => $row->tst_id,
                    'testType' => $row->tst_type,
                    'limit' => $row->percentage,
                    'poolId' => $row->qp_seq
                )
            );
        }
    }
    // end-patch optes_lok_export
}
