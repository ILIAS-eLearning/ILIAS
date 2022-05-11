<?php

namespace ILIAS\LTI\ToolProvider\Service;

use ILIAS\LTI\ToolProvider\User;
use ILIAS\LTI\ToolProvider\Outcome;

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
 * Class to implement the Result service
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class Result extends AssignmentGrade
{

    /**
     * Access scope.
     */
    public static string $SCOPE = 'https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly';

    /**
     * Default limit on size of container to be returned from requests.
     */
    public static int $defaultLimit = 500;

    /**
     * Limit on size of container to be returned from requests.
     *
     * A limit of null (or zero) will disable paging of requests
     *
     * @var int|null  $limit
     */
    private ?int $limit;

    /**
     * Whether requests should be made one page at a time when a limit is set.
     *
     * When false, all objects will be requested, even if this requires several requests based on the limit set.
     *
     * @var boolean  $pagingMode
     */
    private bool $pagingMode;

    /**
     * Class constructor.
     * @param \ILIAS\LTI\ToolProvider\Platform $platform   Platform object for this service request
     * @param string   $endpoint   Service endpoint
     * @param int|null $limit      Limit of results to be returned in each request, null for all
     * @param boolean  $pagingMode True if only a single page should be requested when a limit is set
     */
    public function __construct(\ILIAS\LTI\ToolProvider\Platform $platform, string $endpoint, int $limit = null, bool $pagingMode = false)
    {
        parent::__construct($platform, $endpoint, '/results');
        $this->limit = $limit;
        $this->pagingMode = $pagingMode;
        $this->scope = self::$SCOPE;
        $this->mediaType = 'application/vnd.ims.lis.v2.resultcontainer+json';
    }

    /**
     * Retrieve all outcomes for a line item.
     * @param int|null $limit Limit of results to be returned in each request, null for service default
     * @return Outcome[]|bool  Array of Outcome objects or false on error
     */
    public function getAll(int $limit = null)
    {
        $params = array();
        if (is_null($limit)) {
            $limit = $this->limit;
        }
        if (is_null($limit)) {
            $limit = self::$defaultLimit;
        }
        if (!empty($limit)) {
            $params['limit'] = $limit;
        }
        $outcomes = array();
        $endpoint = $this->endpoint;
        do {
            $http = $this->send('GET', $params);
            $url = '';
            if ($http->ok) {
                if (!empty($http->responseJson)) {
                    foreach ($http->responseJson as $outcome) {
                        $outcomes[] = self::getOutcome($outcome);
                    }
                }
                if (!$this->pagingMode && $http->hasRelativeLink('next')) {
                    $url = $http->getRelativeLink('next');
                    $this->endpoint = $url;
                    $params = array();
                }
            } else {
                $outcomes = false;
            }
        } while ($url);
        $this->endpoint = $endpoint;

        return $outcomes;
    }

    /**
     * Retrieve an outcome for a user.
     * @param User $user User object
     * @return Outcome|null|bool  Outcome object, or null if none, or false on error
     */
    public function get(User $user)
    {
        $params = array('user_id' => $user->ltiUserId);
        $http = $this->send('GET', $params);
        if ($http->ok) {
            if (!empty($http->responseJson)) {
                $outcome = self::getOutcome(reset($http->responseJson));
            } else {
                $outcome = null;
            }
            return $outcome;
        } else {
            return false;
        }
    }

    ###
    ###  PRIVATE METHOD
    ###

    private static function getOutcome(object $json) : Outcome
    {
        $outcome = new Outcome();
        $outcome->ltiUserId = $json->userId;
        if (isset($json->resultScore)) {
            $outcome->setValue($json->resultScore);
        }
        if (isset($json->resultMaximum)) {
            $outcome->setPointsPossible($json->resultMaximum);
        }
        if (isset($json->comment)) {
            $outcome->comment = $json->comment;
        }

        return $outcome;
    }
}
