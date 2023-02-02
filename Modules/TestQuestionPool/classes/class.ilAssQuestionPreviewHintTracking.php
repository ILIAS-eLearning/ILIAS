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
class ilAssQuestionPreviewHintTracking
{
    /**
     * @var ilDBInterface
     */
    private $db;

    /**
     * @var ilAssQuestionPreviewSession
     */
    private $previewSession;

    public function __construct(ilDBInterface $db, ilAssQuestionPreviewSession $previewSession)
    {
        $this->db = $db;
        $this->previewSession = $previewSession;
    }

    public function requestsExist(): bool
    {
        return (
            $this->previewSession->getNumRequestedHints() > 0
        );
    }

    public function requestsPossible(): bool
    {
        $query = "
			SELECT		COUNT(qht_hint_id) cnt_available
			FROM		qpl_hints
			WHERE		qht_question_fi = %s
		";

        $res = $this->db->queryF(
            $query,
            array('integer'),
            array($this->previewSession->getQuestionId())
        );

        $row = $this->db->fetchAssoc($res);

        if ($row['cnt_available'] > $this->previewSession->getNumRequestedHints()) {
            return true;
        }

        return false;
    }

    public function getNextRequestableHint(): ilAssQuestionHint
    {
        $query = "
			SELECT		qht_hint_id
			
			FROM		qpl_hints
			
			WHERE		qht_question_fi = %s
			
			ORDER BY	qht_hint_index ASC
		";

        $res = $this->db->queryF(
            $query,
            array('integer'),
            array($this->previewSession->getQuestionId())
        );

        while ($row = $this->db->fetchAssoc($res)) {
            if (!$this->isRequested($row['qht_hint_id'])) {
                return ilAssQuestionHint::getInstanceById($row['qht_hint_id']);
            }
        }

        throw new ilTestException(
            "no next hint found for questionId={$this->previewSession->getQuestionId()}, userId={$this->previewSession->getUserId()}"
        );
    }

    public function storeRequest(ilAssQuestionHint $questionHint): void
    {
        $this->previewSession->addRequestedHint($questionHint->getId());
    }

    public function isRequested($hintId): bool
    {
        return $this->previewSession->isHintRequested($hintId);
    }

    public function getNumExistingRequests(): int
    {
        return $this->previewSession->getNumRequestedHints();
    }

    public function getRequestedHintsList(): ilAssQuestionHintList
    {
        $hintIds = $this->previewSession->getRequestedHints();

        $requestedHintsList = ilAssQuestionHintList::getListByHintIds($hintIds);

        return $requestedHintsList;
    }

    public function getRequestStatisticData(): ilAssQuestionHintRequestStatisticData
    {
        $count = 0;
        $points = 0;

        foreach ($this->getRequestedHintsList() as $hint) {
            $count++;
            $points += $hint->getPoints();
        }

        $requestsStatisticData = new ilAssQuestionHintRequestStatisticData();
        $requestsStatisticData->setRequestsCount($count);
        $requestsStatisticData->setRequestsPoints($points);

        return $requestsStatisticData;
    }
}
