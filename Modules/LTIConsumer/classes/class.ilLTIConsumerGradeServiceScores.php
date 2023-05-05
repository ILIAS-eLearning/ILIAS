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

/**
 * Class ilLTIConsumerGradeServiceScores
 *
 * @author      Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @author      Stefan Schneider <info@eqsoft.de>
 *
 * @package     Module/LTIConsumer
 */

class ilLTIConsumerGradeServiceScores extends ilLTIConsumerResourceBase
{
    public function __construct(ilLTIConsumerServiceBase $service)
    {
        parent::__construct($service);
        $this->id = 'Score.collection';
        $this->template = '/{context_id}/lineitems/{item_id}/lineitem/scores';
        $this->variables[] = 'Scores.url';
        $this->formats[] = 'application/vnd.ims.lis.v1.scorecontainer+json';
        $this->formats[] = 'application/vnd.ims.lis.v1.score+json';
        $this->methods[] = 'POST';
    }

    /**
     * Execute the request for this resource.
     */
    public function execute(ilLTIConsumerServiceResponse $response): void
    {
        $params = $this->parseTemplate();
        $contextId = $params['context_id'];
        $itemId = $params['item_id'];

        ilObjLTIConsumer::getLogger()->debug("contextId: " . $contextId);
        ilObjLTIConsumer::getLogger()->debug("objId: " . $itemId);
        ilObjLTIConsumer::getLogger()->debug("request data: " . $response->getRequestData());

        // GET is disabled by the moment, but we have the code ready
        // for a future implementation.

        //$container = empty($contentType) || ($contentType === $this->formats[0]);

        $typeid = 0;

        $scope = ilLTIConsumerGradeService::SCOPE_GRADESERVICE_SCORE;
        try {
            $token = $this->checkTool(array($scope));
            if (is_null($token)) {
                throw new Exception('invalid request', 401);
            }

            // Bug in Moodle as tool provider, should accept only "204 No Content" but schedules grade sync task will notices a failed status if not exactly 200
            // see: http://www.imsglobal.org/spec/lti-ags/v2p0#score-service-scope-and-allowed-http-methods
            //$response->setCode(204); // correct
            $returnCode = 200;
            $returnCode = $this->checkScore($response->getRequestData(), (int) $itemId);
            $response->setCode($returnCode); // not really correct
        } catch (Exception $e) {
            $response->setCode($e->getCode());
            $response->setReason($e->getMessage());
        }
    }

    protected function checkScore(string $requestData, int $objId): int
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $score = json_decode($requestData);
        //prüfe Userid
        $userId = ilCmiXapiUser::getUsrIdForObjectAndUsrIdent($objId, $score->userId);
        if ($userId == null) {
            ilObjLTIConsumer::getLogger()->debug('User not available');
            throw new Exception('User not available', 404);
            return 404;
        }

        if (empty($score) ||
            !isset($score->userId) ||
            !isset($score->gradingProgress) ||
            !isset($score->activityProgress) ||
            !isset($score->timestamp) ||
//            isset($score->timestamp) && !:validate_iso8601_date($score->timestamp) ||
            (isset($score->scoreGiven) && !is_numeric($score->scoreGiven)) ||
            (isset($score->scoreGiven) && !isset($score->scoreMaximum)) ||
            (isset($score->scoreMaximum) && !is_numeric($score->scoreMaximum))
        ) {
            ilObjLTIConsumer::getLogger()->debug('Incorrect score received');
            throw new Exception('Incorrect score received', 400);
            return 400;
        }
        //Achtung Ggfs. Timestamp prüfen falls schon was ankam
        if (!isset($score->scoreMaximum)) {
            $score->scoreMaximum = 1;
        }
        if (isset($score->scoreGiven)) {
            if ($score->gradingProgress != 'FullyGraded') {
                $score->scoreGiven = null;
            }
        }
        $result = (float)$score->scoreGiven / (float)$score->scoreMaximum;
        ilObjLTIConsumer::getLogger()->debug("result: " . $result);

        $ltiObjRes = new ilLTIConsumerResultService();

        $ltiObjRes->readProperties($objId);
        // check the object status
        if (!$ltiObjRes->isAvailable()) {
            throw new Exception('Tool for Object not available', 404);
            return 404;
        }

        if ($result >= $ltiObjRes->getMasteryScore()) {
            $lp_status = ilLPStatus::LP_STATUS_COMPLETED_NUM;
        } else {
            $lp_status = ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
        }
        $lp_percentage = (int) round(100 * $result);

        $consRes = ilLTIConsumerResult::getByKeys($objId, $userId, false);
        if (empty($consRes)) {
            ilObjLTIConsumer::getLogger()->debug("lti_consumer_results_id not found!");
//            throw new Exception('lti_consumer_results_id not found!', 404);
//            return 404;
        }
        if (!isset($consRes->id)) {
            $consRes->id = $DIC->database()->nextId('lti_consumer_results');
        }
        $DIC->database()->replace(
            'lti_consumer_results',
            array(
                'id' => array('integer', $consRes->id)
            ),
            array(
                'obj_id' => array('integer', $objId),
                'usr_id' => array('integer', $userId),
                'result' => array('float', $result)
            )
        );

        ilLPStatus::writeStatus($objId, $userId, $lp_status, $lp_percentage, true);

        return 200;
    }
}
