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
        $userId = self::getUsrIdForObjectAndUsrIdent($objId, $score->userId);
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
            isset($score->timestamp) && !self::validate_iso8601_date($score->timestamp) ||
            (isset($score->scoreGiven) && !is_numeric($score->scoreGiven)) ||
            (isset($score->scoreGiven) && !isset($score->scoreMaximum)) ||
            (isset($score->scoreMaximum) && !is_numeric($score->scoreMaximum))
        ) {
            ilObjLTIConsumer::getLogger()->debug('Incorrect score received');
            ilObjLTIConsumer::getLogger()->dump($score);
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

        $ltiTimestamp = DateTimeImmutable::createFromFormat(DateTimeInterface::RFC3339_EXTENDED, $score->timestamp);
        if (!$ltiTimestamp) { //moodle 4
            $ltiTimestamp = DateTimeImmutable::createFromFormat(DateTimeInterface::ISO8601, $score->timestamp);
        }
        if (!$ltiTimestamp) { //for example nothing
            $ltiTimestamp = new DateTime('now');
        }
        $gradeValues = [
            'id' => array('integer', $DIC->database()->nextId('lti_consumer_grades')),
            'obj_id' => array('integer', $objId),
            'usr_id' => array('integer', $userId),
            'score_given' => array('float', $score->scoreGiven),
            'score_maximum' => array('float', $score->scoreMaximum),
            'activity_progress' => array('text', $score->activityProgress),
            'grading_progress' => array('text', $score->gradingProgress),
            'lti_timestamp' => array('timestamp',$ltiTimestamp->format("Y-m-d H:i:s")),
            'stored' => array('timestamp', date("Y-m-d H:i:s"))
        ];
        $DIC->database()->insert('lti_consumer_grades', $gradeValues);



        return 200;
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

    protected static function getUsrIdForObjectAndUsrIdent(int $objId, string $userIdent): ?int
    {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $atExist = strpos($userIdent, '@');

        $query = "SELECT usr_id FROM cmix_users WHERE obj_id = " . $DIC->database()->quote($objId, 'integer');

        if ($atExist > 1) {
            $query .= " AND usr_ident = " . $DIC->database()->quote($userIdent, 'text');
        } else { //LTI 1.1
            $query .= " AND" . $DIC->database()->like('usr_ident', 'text', $userIdent . '@%');
        }
        $res = $DIC->database()->query($query);

        $usrId = null;
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $usrId = (int) $row['usr_id'];
        }

        return $usrId;
    }

}
