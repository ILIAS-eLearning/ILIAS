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
 * @author		Björn Heyser <bheyser@databay.de>
 * @version		$Id$
 *
 * @package     Modules/Test
 */
class ilTestSkillLevelThreshold
{
    /**
     * @var ilDBInterface
     */
    private $db;

    /**
     * @var integer
     */
    private $testId;

    /**
     * @var integer
     */
    private $skillBaseId;

    /**
     * @var integer
     */
    private $skillTrefId;

    /**
     * @var integer
     */
    private $skillLevelId;

    /**
     * @var integer
     */
    private $threshold;

    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;
    }

    public function loadFromDb()
    {
        $query = "
			SELECT test_fi, skill_base_fi, skill_tref_fi, skill_level_fi, threshold
			FROM tst_skl_thresholds
			WHERE test_fi = %s
			AND skill_base_fi = %s
			AND skill_tref_fi = %s
			AND skill_level_fi = %s
		";

        $res = $this->db->queryF(
            $query,
            array('integer', 'integer', 'integer', 'integer'),
            array($this->getTestId(), $this->getSkillBaseId(), $this->getSkillTrefId(), $this->getSkillLevelId())
        );

        $row = $this->db->fetchAssoc($res);

        if (is_array($row)) {
            $this->setThreshold($row['threshold']);
        }
    }

    public function saveToDb()
    {
        if ($this->dbRecordExists()) {
            $this->db->update(
                'tst_skl_thresholds',
                array(
                    'threshold' => array('integer', $this->getThreshold())
                ),
                array(
                    'test_fi' => array('integer', $this->getTestId()),
                    'skill_base_fi' => array('integer', $this->getSkillBaseId()),
                    'skill_tref_fi' => array('integer', $this->getSkillTrefId()),
                    'skill_level_fi' => array('integer', $this->getSkillLevelId())
                )
            );
        } else {
            $this->db->insert('tst_skl_thresholds', array(
                'test_fi' => array('integer', $this->getTestId()),
                'skill_base_fi' => array('integer', $this->getSkillBaseId()),
                'skill_tref_fi' => array('integer', $this->getSkillTrefId()),
                'skill_level_fi' => array('integer', $this->getSkillLevelId()),
                'threshold' => array('integer', $this->getThreshold())
            ));
        }
    }

    public function deleteFromDb()
    {
        $query = "
			DELETE FROM tst_skl_thresholds
			WHERE test_fi = %s
			AND skill_base_fi = %s
			AND skill_tref_fi = %s
			AND skill_level_fi = %s
		";

        $this->db->manipulateF(
            $query,
            array('integer', 'integer', 'integer', 'integer'),
            array($this->getTestId(), $this->getSkillBaseId(), $this->getSkillTrefId(), $this->getSkillLevelId())
        );
    }

    public function dbRecordExists(): bool
    {
        $query = "
			SELECT COUNT(*) cnt
			FROM tst_skl_thresholds
			WHERE test_fi = %s
			AND skill_base_fi = %s
			AND skill_tref_fi = %s
			AND skill_level_fi = %s
		";

        $res = $this->db->queryF(
            $query,
            array('integer', 'integer', 'integer', 'integer'),
            array($this->getTestId(), $this->getSkillBaseId(), $this->getSkillTrefId(), $this->getSkillLevelId())
        );

        $row = $this->db->fetchAssoc($res);

        return $row['cnt'] > 0;
    }

    /**
     * @param int $testId
     */
    public function setTestId($testId)
    {
        $this->testId = $testId;
    }

    /**
     * @return int
     */
    public function getTestId(): int
    {
        return $this->testId;
    }

    /**
     * @param int $skillBaseId
     */
    public function setSkillBaseId($skillBaseId)
    {
        $this->skillBaseId = $skillBaseId;
    }

    /**
     * @return int
     */
    public function getSkillBaseId(): int
    {
        return $this->skillBaseId;
    }

    /**
     * @param int $skillTrefId
     */
    public function setSkillTrefId($skillTrefId)
    {
        $this->skillTrefId = $skillTrefId;
    }

    /**
     * @return int
     */
    public function getSkillTrefId(): int
    {
        return $this->skillTrefId;
    }

    /**
     * @param int $skillLevelId
     */
    public function setSkillLevelId($skillLevelId)
    {
        $this->skillLevelId = $skillLevelId;
    }

    /**
     * @return int
     */
    public function getSkillLevelId(): int
    {
        return $this->skillLevelId;
    }

    /**
     * @param int $threshold
     */
    public function setThreshold($threshold)
    {
        $this->threshold = $threshold;
    }

    /**
     * @return int
     */
    public function getThreshold(): int
    {
        return $this->threshold;
    }
}
