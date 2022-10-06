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
 * @package     Modules/TestQuestionPool
 */
class ilAssIncompleteQuestionPurger
{
    /**
     * @var ilDBInterface
     */
    protected $db;

    protected $ownerId;

    private $ignoredContainerObjectTypes;

    public function __construct(ilDBInterface $db)
    {
        $this->db = $db;

        $this->ignoredContainerObjectTypes = array('lm');
    }

    public function getOwnerId()
    {
        return $this->ownerId;
    }

    public function setOwnerId($ownerId): void
    {
        $this->ownerId = $ownerId;
    }

    public function purge(): void
    {
        $questionIds = $this->getPurgableQuestionIds();
        $this->purgeQuestionIds($questionIds);
    }

    private function getPurgableQuestionIds(): array
    {
        $INtypes = $this->db->in('object_data.type', $this->getIgnoredContainerObjectTypes(), true, 'text');

        $query = "
			SELECT qpl_questions.question_id
			FROM qpl_questions
			INNER JOIN object_data
			ON object_data.obj_id = qpl_questions.obj_fi
			AND $INtypes
			WHERE qpl_questions.owner = %s
			AND qpl_questions.tstamp = %s
		";

        $res = $this->db->queryF($query, array('integer', 'integer'), array($this->getOwnerId(), 0));

        $questionIds = array();

        while ($row = $this->db->fetchAssoc($res)) {
            $questionIds[] = $row['question_id'];
        }

        return $questionIds;
    }

    private function purgeQuestionIds($questionIds): void
    {
        require_once 'Modules/TestQuestionPool/classes/class.assQuestion.php';

        foreach ($questionIds as $questionId) {
            $question = assQuestion::_instantiateQuestion($questionId);
            $question->delete($questionId);
        }
    }

    protected function setIgnoredContainerObjectTypes($ignoredContainerObjectTypes): void
    {
        $this->ignoredContainerObjectTypes = $ignoredContainerObjectTypes;
    }

    protected function getIgnoredContainerObjectTypes(): array
    {
        return $this->ignoredContainerObjectTypes;
    }
}
