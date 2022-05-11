<?php

namespace ILIAS\LTI\ToolProvider\Service;

use ILIAS\LTI\ToolProvider;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Class to implement the Score service
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class Score extends AssignmentGrade
{

    /**
     * Access scope.
     */
    public static string $SCOPE = 'https://purl.imsglobal.org/spec/lti-ags/scope/score';

    /**
     * Class constructor.
     * @param \ILIAS\LTI\ToolProvider\Platform $platform Platform object for this service request
     * @param string   $endpoint Service endpoint
     */
    public function __construct(\ILIAS\LTI\ToolProvider\Platform $platform, string $endpoint)
    {
        parent::__construct($platform, $endpoint, '/scores');
        $this->scope = self::$SCOPE;
        $this->mediaType = 'application/vnd.ims.lis.v1.score+json';
    }

    /**
     * Submit an outcome for a user.
     * @param ToolProvider\Outcome $ltiOutcome Outcome object //UK: Changed from LTI\Outcome
     * @param ToolProvider\User    $user       User object
     * @return bool  True if successful, otherwise false
     */
    public function submit(ToolProvider\Outcome $ltiOutcome, ToolProvider\User $user) : bool
    {
        $score = $ltiOutcome->getValue();
        if (!is_null($score)) {
            $json = array(
                'scoreGiven' => $score,
                'scoreMaximum' => $ltiOutcome->getPointsPossible(),
                'comment' => $ltiOutcome->comment,
                'activityProgress' => $ltiOutcome->activityProgress,
                'gradingProgress' => $ltiOutcome->gradingProgress
            );
        } else {
            $json = array(
                'activityProgress' => 'Initialized',
                'gradingProgress' => 'NotReady'
            );
        }
        $json['userId'] = $user->ltiUserId;
        $date = new \DateTime();
        $json['timestamp'] = date_format($date, 'Y-m-d\TH:i:s.uP');
        $data = json_encode($json);
        $http = $this->send('POST', null, $data);

        return $http->ok;
    }
}
