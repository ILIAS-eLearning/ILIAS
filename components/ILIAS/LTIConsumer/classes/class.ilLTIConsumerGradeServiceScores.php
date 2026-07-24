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

        ilObjLTIConsumer::getLogger()->info("contextId: " . $contextId);
        ilObjLTIConsumer::getLogger()->info("objId: " . $itemId);
        ilObjLTIConsumer::getLogger()->info("request data: " . $response->getRequestData());

        $scope = ilLTIConsumerGradeService::SCOPE_GRADESERVICE_SCORE;
        try {
            $token = $this->checkTool(array($scope));
            if (is_null($token)) {
                throw new Exception('invalid request', 401);
            }
            $this->checkToolMatchesObject((int) $itemId, $token);

            // Bug in Moodle as tool provider, should accept only "204 No Content" but schedules grade sync task will notices a failed status if not exactly 200
            // see: http://www.imsglobal.org/spec/lti-ags/v2p0#score-service-scope-and-allowed-http-methods
            //$response->setCode(204); // correct
            $returnCode = $this->checkScore($response->getRequestData(), (int) $itemId);
            $response->setCode($returnCode); // not really correct
        } catch (Exception $e) {
            $code = $e->getCode();
            $response->setCode($code >= 400 && $code < 600 ? $code : 500);
            $response->setReason($e->getMessage());
        }
    }

    protected function checkToolMatchesObject(int $objId, object $token): void
    {
        $clientId = $token->sub ?? '';
        if (!is_string($clientId) || $clientId === '') {
            throw new Exception('invalid request', 401);
        }

        if (ilObject::_lookupType($objId) !== 'lti') {
            throw new Exception('Tool for Object not available', 404);
        }

        $ltiObject = new ilObjLTIConsumer($objId, false);
        $provider = $ltiObject->getProvider();
        if (!$provider instanceof ilLTIConsumeProvider) {
            throw new Exception('Tool for Object not available', 404);
        }

        if ($provider->getClientId() !== $clientId) {
            throw new Exception('invalid clientId', 403);
        }

        if (!$provider->isGradeSynchronization() && !$provider->getHasOutcome()) {
            throw new Exception('grade synchronization not enabled', 403);
        }
    }

    protected function checkScore(string $requestData, int $objId): int
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $logger = $DIC->logger()->root();

        $logger->info('checkScore');
        $score = json_decode($requestData);
        if (!is_object($score) || json_last_error() !== JSON_ERROR_NONE ||
            !isset($score->userId) ||
            !isset($score->gradingProgress) ||
            !isset($score->activityProgress) ||
            !isset($score->timestamp) ||
            !is_scalar($score->userId) ||
            !is_string($score->gradingProgress) ||
            !is_string($score->activityProgress) ||
            !is_scalar($score->timestamp) ||
            !self::isValidActivityProgress($score->activityProgress) ||
            !self::isValidGradingProgress($score->gradingProgress) ||
            !self::validate_iso8601_date((string) $score->timestamp) ||
            (isset($score->scoreGiven) && !is_numeric($score->scoreGiven)) ||
            (isset($score->scoreGiven) && !isset($score->scoreMaximum)) ||
            (isset($score->scoreMaximum) && (!is_numeric($score->scoreMaximum) || (float) $score->scoreMaximum <= 0)) ||
            (isset($score->scoreGiven) && (float) $score->scoreGiven < 0) ||
            ($score->gradingProgress === 'FullyGraded' && (!isset($score->scoreGiven) || !isset($score->scoreMaximum)))
        ) {
            ilObjLTIConsumer::getLogger()->info('Incorrect score received');
            ilObjLTIConsumer::getLogger()->dump($score);
            throw new Exception('Incorrect score received', 400);
        }

        $userId = $this->resolveUserIdFromLtiIdent($objId, (string) $score->userId);
        if ($userId == null) {
            ilObjLTIConsumer::getLogger()->info('User not available');
            throw new Exception('User not available', 404);
        }

        $scoreGiven = isset($score->scoreGiven) ? (float) $score->scoreGiven : null;
        $scoreMaximum = isset($score->scoreMaximum) ? (float) $score->scoreMaximum : null;

        $result = null;
        $scoreProgress = null;
        if ($scoreGiven !== null && $scoreMaximum !== null) {
            $scoreProgress = $scoreGiven / $scoreMaximum;
        }
        if ($score->gradingProgress === 'FullyGraded') {
            $result = $scoreProgress;
        }

        $ltiObjRes = new ilLTIConsumerResultService();

        $ltiObjRes->readProperties($objId);
        // check the object status
        if (!$ltiObjRes->isAvailable()) {
            throw new Exception('Tool for Object not available', 404);
        }

        $lp_status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
        $updateLpStatus = false;

        if (in_array($score->activityProgress, ['Started', 'InProgress'], true) ||
            ($score->activityProgress === 'Submitted' && $score->gradingProgress !== 'FullyGraded')) {
            $lp_status = ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
            $updateLpStatus = true;
        } elseif (in_array($score->activityProgress, ['Submitted', 'Completed'], true)) {
            if ($score->gradingProgress === 'FullyGraded' && $result !== null) {
                if ($result >= $ltiObjRes->getMasteryScore()) {
                    $lp_status = ilLPStatus::LP_STATUS_COMPLETED_NUM;
                } else {
                    $lp_status = ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
                }
            } elseif ($score->gradingProgress === 'Failed') {
                $lp_status = ilLPStatus::LP_STATUS_FAILED_NUM;
            } else {
                $lp_status = ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
            }
            $updateLpStatus = true;
        }

        $lp_percentage = $scoreProgress === null ? 0 : (int) round(100 * $scoreProgress);
        $resultForLog = $result === null ? 'null' : (string) $result;

        ilObjLTIConsumer::getLogger()->info("lp_status: $lp_status, lp_percentage: $lp_percentage, result: $resultForLog, mastery_score: " . $ltiObjRes->getMasteryScore());

        if ($result !== null) {
            $consRes = ilLTIConsumerResult::getByKeys($objId, $userId, false);
            if (empty($consRes)) {
                $DIC->database()->insert(
                    'lti_consumer_results',
                    array(
                        'id' => array('integer', $DIC->database()->nextId('lti_consumer_results')),
                        'obj_id' => array('integer', $objId),
                        'usr_id' => array('integer', $userId),
                        'result' => array('float', $result)
                    )
                );
            } else {
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
            }
        }

        if ($updateLpStatus) {
            ilLPStatus::writeStatus($objId, $userId, $lp_status, $lp_percentage, true);
        }

        $ltiTimestamp = DateTimeImmutable::createFromFormat(DateTimeInterface::RFC3339_EXTENDED, (string) $score->timestamp);
        if (!$ltiTimestamp) { //moodle 4
            $ltiTimestamp = DateTimeImmutable::createFromFormat(DateTimeInterface::ISO8601, (string) $score->timestamp);
        }
        if (!$ltiTimestamp) { //for example nothing
            $ltiTimestamp = new DateTime('now');
        }
        $gradeValues = [
            'id' => array('integer', $DIC->database()->nextId('lti_consumer_grades')),
            'obj_id' => array('integer', $objId),
            'usr_id' => array('integer', $userId),
            'score_given' => array('float', $scoreGiven),
            'score_maximum' => array('float', $scoreMaximum),
            'activity_progress' => array('text', $score->activityProgress),
            'grading_progress' => array('text', $score->gradingProgress),
            'lti_timestamp' => array('timestamp',$ltiTimestamp->format("Y-m-d H:i:s")),
            'stored' => array('timestamp', date("Y-m-d H:i:s"))
        ];
        $DIC->database()->insert('lti_consumer_grades', $gradeValues);



        return 200;
    }

    protected static function isValidActivityProgress(string $activityProgress): bool
    {
        return in_array($activityProgress, [
            'Initialized',
            'Started',
            'InProgress',
            'Submitted',
            'Completed'
        ], true);
    }

    protected static function isValidGradingProgress(string $gradingProgress): bool
    {
        return in_array($gradingProgress, [
            'FullyGraded',
            'Pending',
            'PendingManual',
            'Failed',
            'NotReady'
        ], true);
    }

    public static function validate_iso8601_date(string $date): bool
    {
        if (preg_match('/^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])' .
                '(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))' .
                '([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)' .
                '?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/', $date) > 0) {
            return true;
        }
        return false;
    }
}
