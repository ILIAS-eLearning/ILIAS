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
 * Class to implement the Assessment Control service
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class AssessmentControl extends Service
{

    /**
     * Access scope.
     */
    public static string $SCOPE = 'https://purl.imsglobal.org/spec/lti-ap/scope/control.all';

    /**
     * Resource link for this service request.
     *
     * @var \ILIAS\LTI\ToolProvider\ResourceLink  $resourceLink
     */
    private ?\ILIAS\LTI\ToolProvider\ResourceLink $resourceLink = null;

    /**
     * Class constructor.
     * @param \ILIAS\LTI\ToolProvider\ResourceLink $resourceLink Resource link object for this service request
     * @param string       $endpoint     Service endpoint
     */
    public function __construct(\ILIAS\LTI\ToolProvider\ResourceLink $resourceLink, string $endpoint)
    {
        parent::__construct($resourceLink->getPlatform(), $endpoint);
        $this->resourceLink = $resourceLink;
        $this->scope = self::$SCOPE;
        $this->mediaType = 'application/vnd.ims.lti-ap.v1.control+json';
    }

    /**
     * Submit an assessment control action.
     * @param \ILIAS\LTI\ToolProvider\AssessmentControlAction $assessmentControlAction AssessmentControlAction object
     * @param \ILIAS\LTI\ToolProvider\User                    $user                    User object
     * @param int                         $attemptNumber           Attempt number
     * @return string|bool  Value of the status response, or false if not successful
     */
//    public function submitAction($assessmentControlAction, $user, $attemptNumber)
    public function submitAction(\ILIAS\LTI\ToolProvider\AssessmentControlAction $assessmentControlAction, \ILIAS\LTI\ToolProvider\User $user, int $attemptNumber)
    {
        $status = false;
        $json = array(
            'user' => array('iss' => $this->resourceLink->getPlatform()->platformId, 'sub' => $user->ltiUserId),
            'resource_link' => array('id' => $this->resourceLink->ltiResourceLinkId),
            'attempt_number' => $attemptNumber,
            'action' => $assessmentControlAction->getAction(),
//            'incident_time' => $assessmentControlAction->getDate()->format('Y-m-d\TH:i:s\Z'), //UK:changed
            'incident_time' => date(('Y-m-d\TH:i:s\Z'), $assessmentControlAction->getDate()),
            'incident_severity' => $assessmentControlAction->getSeverity()
        );
        if (!empty($assessmentControlAction->extraTime)) {
            $json['extra_time'] = $assessmentControlAction->extraTime;
        }
        if (!empty($assessmentControlAction->code)) {
            $json['reason_code'] = $assessmentControlAction->code;
        }
        if (!empty($assessmentControlAction->message)) {
            $json['reason_msg'] = $assessmentControlAction->message;
        }
        $data = json_encode($json);
        $http = $this->send('POST', null, $data);
        if ($http->ok) {
            $http->ok = !empty($http->responseJson->status);
            if ($http->ok) {
                $status = $http->responseJson->status;
            }
        }

        return $status;
    }
}
