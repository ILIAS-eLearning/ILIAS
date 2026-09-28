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
    public function __construct(
        ilLTIConsumerServiceBase $service,
        private readonly ilLTIConsumerLineItemRepository $line_item_repository
    ) {
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
        $context_id = (int) ($params['context_id'] ?? 0);
        $item_id = (int) ($params['item_id'] ?? 0);

        ilObjLTIConsumer::getLogger()->info("contextId: " . $context_id);
        ilObjLTIConsumer::getLogger()->info("objId: " . $item_id);
        ilObjLTIConsumer::getLogger()->info("request data: " . $response->getRequestData());

        $scope = ilLTIConsumerGradeService::SCOPE_GRADESERVICE_SCORE;
        try {
            $token = $this->checkTool(array($scope));
            if (is_null($token)) {
                throw ilLTIConsumerHttpException::unauthorized();
            }
            if (ilObject::_lookupType($item_id) === 'lti') {
                $this->checkToolMatchesObject($item_id, $token);
                $this->checkScore($response->getRequestData(), $item_id);
            } else {
                $this->checkStoredLineItemScore($response->getRequestData(), $context_id, $item_id, $token);
            }
            $response->setCode(204);
        } catch (ilLTIConsumerHttpException $e) {
            $response->setCode($e->getCode());
            $response->setReason($e->getMessage());
        } catch (Throwable $e) {
            $response->setCode(500);
            $response->setReason($e->getMessage());
        }
    }

    protected function checkToolMatchesObject(int $objId, ilLTIConsumerAccessToken $token): void
    {
        $clientId = $token->getClientId();

        if (ilObject::_lookupType($objId) !== 'lti') {
            throw ilLTIConsumerHttpException::notFound('Tool for Object not available');
        }

        $ltiObject = new ilObjLTIConsumer($objId, false);
        $provider = $ltiObject->getProvider();
        if (!$provider instanceof ilLTIConsumeProvider) {
            throw ilLTIConsumerHttpException::notFound('Tool for Object not available');
        }

        if ($provider->getClientId() !== $clientId) {
            throw ilLTIConsumerHttpException::forbidden('invalid clientId');
        }

        if (!$provider->isGradeSynchronization() && !$provider->getHasOutcome()) {
            throw ilLTIConsumerHttpException::forbidden('grade synchronization not enabled');
        }
    }

    protected function checkStoredLineItemScore(
        string $request_data,
        int $context_id,
        int $item_id,
        ilLTIConsumerAccessToken $token
    ): void {
        $stored_line_item = $this->line_item_repository->get(-$item_id, $context_id, $token->getClientId());
        if ($item_id >= 0 || $stored_line_item === null) {
            throw ilLTIConsumerHttpException::notFound('LineItem not found');
        }

        $resource_link_ref_id = (int) $stored_line_item->resourceLinkId;
        if ($resource_link_ref_id <= 0) {
            throw ilLTIConsumerHttpException::notFound('Resource link not found');
        }

        global $DIC;
        $tree = $DIC->repositoryTree();
        $node_data = $tree->getNodeData($resource_link_ref_id);
        if (!isset($node_data['type']) || $node_data['type'] !== 'lti' ||
            !in_array($context_id, $tree->getPathId($resource_link_ref_id), true)) {
            throw ilLTIConsumerHttpException::notFound('Resource link not found');
        }

        $lti_object_id = ilObject::_lookupObjId($resource_link_ref_id);
        if (!$lti_object_id) {
            throw ilLTIConsumerHttpException::notFound('Resource link not found');
        }

        $this->checkToolMatchesObject($lti_object_id, $token);
        $this->checkScore($request_data, $lti_object_id, $item_id);
    }

    protected function checkScore(string $requestData, int $objId, ?int $grade_item_id = null): void
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */

        $logger = $DIC->logger()->forComponent('lti');

        $logger->info('checkScore');
        $score = json_decode($requestData);
        $activityProgress = is_object($score) && is_string($score->activityProgress ?? null)
            ? ilLTIConsumerActivityProgress::tryFrom($score->activityProgress) : null;
        $gradingProgress = is_object($score) && is_string($score->gradingProgress ?? null)
            ? ilLTIConsumerGradingProgress::tryFrom($score->gradingProgress) : null;
        if (!is_object($score) || json_last_error() !== JSON_ERROR_NONE ||
            !isset($score->userId) ||
            !isset($score->gradingProgress) ||
            !isset($score->activityProgress) ||
            !isset($score->timestamp) ||
            !is_scalar($score->userId) ||
            !is_scalar($score->timestamp) ||
            $activityProgress === null ||
            $gradingProgress === null ||
            !self::validate_iso8601_date((string) $score->timestamp) ||
            (isset($score->scoreGiven) && !is_numeric($score->scoreGiven)) ||
            (isset($score->scoreGiven) && !isset($score->scoreMaximum)) ||
            (isset($score->scoreMaximum) && (!is_numeric($score->scoreMaximum) || (float) $score->scoreMaximum <= 0)) ||
            (isset($score->scoreGiven) && (float) $score->scoreGiven < 0) ||
            ($gradingProgress === ilLTIConsumerGradingProgress::FULLY_GRADED && (!isset($score->scoreGiven) || !isset($score->scoreMaximum)))
        ) {
            ilObjLTIConsumer::getLogger()->info('Incorrect score received');
            ilObjLTIConsumer::getLogger()->dump($score);
            throw ilLTIConsumerHttpException::badRequest('Incorrect score received');
        }

        $userId = $this->resolveUserIdFromLtiIdent($objId, (string) $score->userId);
        if ($userId == null) {
            ilObjLTIConsumer::getLogger()->info('User not available');
            throw ilLTIConsumerHttpException::notFound('User not available');
        }

        $lti_timestamp = DateTimeImmutable::createFromFormat(DateTimeInterface::RFC3339_EXTENDED, (string) $score->timestamp);
        if (!$lti_timestamp) { //moodle 4
            $lti_timestamp = DateTimeImmutable::createFromFormat(DateTimeInterface::ISO8601, (string) $score->timestamp);
        }
        if (!$lti_timestamp) { //for example nothing
            $lti_timestamp = new DateTimeImmutable('now');
        }

        $grade_item_id_for_query = $grade_item_id ?? $objId;
        $latest_grade_query = 'SELECT lti_timestamp FROM lti_consumer_grades'
            . ' WHERE obj_id = ' . $DIC->database()->quote($grade_item_id_for_query, 'integer')
            . ' AND usr_id = ' . $DIC->database()->quote($userId, 'integer')
            . ' ORDER BY lti_timestamp DESC, stored DESC';
        $latest_grade_result = $DIC->database()->query($latest_grade_query);
        $latest_grade = $DIC->database()->fetchAssoc($latest_grade_result);
        $is_current_score = $latest_grade === null ||
            $lti_timestamp >= new DateTimeImmutable($latest_grade['lti_timestamp']);

        $scoreGiven = isset($score->scoreGiven) ? (float) $score->scoreGiven : null;
        $scoreMaximum = isset($score->scoreMaximum) ? (float) $score->scoreMaximum : null;

        $result = null;
        $scoreProgress = null;
        if ($scoreGiven !== null && $scoreMaximum !== null) {
            $scoreProgress = $scoreGiven / $scoreMaximum;
        }
        if ($gradingProgress === ilLTIConsumerGradingProgress::FULLY_GRADED) {
            $result = $scoreProgress;
        }

        if ($grade_item_id !== null) {
            $this->storeScore(
                $grade_item_id,
                $userId,
                $scoreGiven,
                $scoreMaximum,
                $activityProgress,
                $gradingProgress,
                $lti_timestamp
            );
            return;
        }

        $ltiObjRes = new ilLTIConsumerResultService();

        $ltiObjRes->readProperties($objId);
        // check the object status
        if (!$ltiObjRes->isAvailable()) {
            throw ilLTIConsumerHttpException::notFound('Tool for Object not available');
        }

        $lp_status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
        $updateLpStatus = false;

        if ($activityProgress->isInProgress() ||
            ($activityProgress === ilLTIConsumerActivityProgress::SUBMITTED && $gradingProgress->isPending())) {
            $lp_status = ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
            $updateLpStatus = true;
        } elseif ($activityProgress->isSubmittedOrCompleted()) {
            if ($gradingProgress === ilLTIConsumerGradingProgress::FULLY_GRADED && $result !== null) {
                if ($result >= $ltiObjRes->getMasteryScore()) {
                    $lp_status = ilLPStatus::LP_STATUS_COMPLETED_NUM;
                } else {
                    $lp_status = ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
                }
            } else {
                $lp_status = ilLPStatus::LP_STATUS_IN_PROGRESS_NUM;
            }
            $updateLpStatus = true;
        }

        $lp_percentage = $scoreProgress === null ? 0 : max(0, min(100, (int) round(100 * $scoreProgress)));
        $resultForLog = $result === null ? 'null' : (string) $result;

        ilObjLTIConsumer::getLogger()->info("lp_status: $lp_status, lp_percentage: $lp_percentage, result: $resultForLog, mastery_score: " . $ltiObjRes->getMasteryScore());

        $should_update_result = $result !== null || $scoreGiven === null;
        if ($is_current_score && $should_update_result) {
            $consRes = ilLTIConsumerResult::getByKeys($objId, $userId, false);
            if (empty($consRes) && $result !== null) {
                $DIC->database()->insert(
                    'lti_consumer_results',
                    array(
                        'id' => array('integer', $DIC->database()->nextId('lti_consumer_results')),
                        'obj_id' => array('integer', $objId),
                        'usr_id' => array('integer', $userId),
                        'result' => array('float', $result)
                    )
                );
            } elseif (!empty($consRes)) {
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

        if ($is_current_score && $updateLpStatus) {
            ilLPStatus::writeStatus($objId, $userId, $lp_status, $lp_percentage, true);
        }

        $this->storeScore(
            $objId,
            $userId,
            $scoreGiven,
            $scoreMaximum,
            $activityProgress,
            $gradingProgress,
            $lti_timestamp
        );
    }

    protected function storeScore(
        int $item_id,
        int $user_id,
        ?float $score_given,
        ?float $score_maximum,
        ilLTIConsumerActivityProgress $activity_progress,
        ilLTIConsumerGradingProgress $grading_progress,
        DateTimeImmutable $lti_timestamp
    ): void {
        global $DIC;

        $grade_values = [
            'id' => array('integer', $DIC->database()->nextId('lti_consumer_grades')),
            'obj_id' => array('integer', $item_id),
            'usr_id' => array('integer', $user_id),
            'score_given' => array('float', $score_given),
            'score_maximum' => array('float', $score_maximum),
            'activity_progress' => array('text', $activity_progress->value),
            'grading_progress' => array('text', $grading_progress->value),
            'lti_timestamp' => array('timestamp', $lti_timestamp->format('Y-m-d H:i:s')),
            'stored' => array('timestamp', date("Y-m-d H:i:s"))
        ];
        $DIC->database()->insert('lti_consumer_grades', $grade_values);
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
