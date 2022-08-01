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


namespace ILIAS\LTI\ToolProvider\Service;

use ILIAS\LTI\ToolProvider;
use ILIAS\LTI\ToolProvider\Context;
use ILIAS\LTI\ToolProvider\ResourceLink;

/**
 * Class to implement the Membership service
 *
 * @author  Stephen P Vickers <stephen@spvsoftwareproducts.com>
 * @copyright  SPV Software Products
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3
 */
class Membership extends Service
{

    /**
     * Media type for version 1 of Memberships service.
     */
    const MEDIA_TYPE_MEMBERSHIPS_V1 = 'application/vnd.ims.lis.v2.membershipcontainer+json';

    /**
     * Media type for Names and Role Provisioning service.
     */
    const MEDIA_TYPE_MEMBERSHIPS_NRPS = 'application/vnd.ims.lti-nrps.v2.membershipcontainer+json';

    /**
     * Access scope.
     */
    public static string $SCOPE = 'https://purl.imsglobal.org/spec/lti-nrps/scope/contextmembership.readonly';

    /**
     * Default limit on size of container to be returned from requests.
     */
    public static int $defaultLimit = 100;

    /**
     * The object to which the memberships apply (ResourceLink or Context).
     *
     * @var Context|ResourceLink $source
     */
    private $source = null;

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
     * @param object   $source     The object to which the memberships apply (ResourceLink or Context)
     * @param string   $endpoint   Service endpoint
     * @param string   $format     Format to request
     * @param int|null $limit      Limit of line items to be returned in each request, null for all
     * @param boolean  $pagingMode True if only a single page should be requested when a limit is set
     */
    public function __construct($source, string $endpoint, string $format = self::MEDIA_TYPE_MEMBERSHIPS_V1, int $limit = null, bool $pagingMode = false)
    {
        $platform = $source->getPlatform();
        parent::__construct($platform, $endpoint);
        $this->scope = self::$SCOPE;
        $this->mediaType = $format;
        $this->source = $source;
        $this->limit = $limit;
        $this->pagingMode = $pagingMode;
    }

    /**
     * Get the memberships.
     * @param string|null $role  Role for which memberships are to be requested (optional, default is all roles)
     * @param int|null    $limit Limit on the number of memberships to be returned in each request, null for service default (optional)
     * @return mixed The array of UserResult objects if successful, otherwise false
     */
    public function get(string $role = null, int $limit = null)
    {
        return $this->getMembers(false, $role, $limit);
    }

    /**
     * Get the memberships.
     * @param string|null $role  Role for which memberships are to be requested (optional, default is all roles)
     * @param int|null    $limit Limit on the number of memberships to be returned in each request, null for service default (optional)
     * @return mixed The array of UserResult objects if successful, otherwise false
     */
    public function getWithGroups(string $role = null, int $limit = null)
    {
        return $this->getMembers(true, $role, $limit);
    }

    /**
     * Get the memberships.
     * @param bool        $withGroups True is group information is to be requested as well
     * @param string|null $role       Role for which memberships are to be requested (optional, default is all roles)
     * @param int|null    $limit      Limit on the number of memberships to be returned in each request, null for service default (optional)
     * @return mixed The array of UserResult objects if successful, otherwise false
     */
    private function getMembers(bool $withGroups, string $role = null, int $limit = null)
    {
        //added
        $oldUsers = [];

        $isLink = is_a($this->source, 'ceLTIc\LTI\ResourceLink');
        $parameters = array();
        if (!empty($role)) {
            $parameters['role'] = $role;
        }
        if (is_null($limit)) {
            $limit = $this->limit;
        }
        if (is_null($limit)) {
            $limit = self::$defaultLimit;
        }
        if (!empty($limit)) {
            $parameters['limit'] = strval($limit);
        }
        if ($isLink) {
            $context = $this->source->getContext();
            if (!empty($this->source->getId())) {
                $parameters['rlid'] = $this->source->getId();
            }
            if ($withGroups && ($this->mediaType === self::MEDIA_TYPE_MEMBERSHIPS_NRPS) && !empty($context)) {
                $context->getGroups();
                $this->source->groupSets = $context->groupSets;
                $this->source->groups = $context->groups;
                $parameters['groups'] = 'true';
            }
        } elseif ($withGroups && ($this->mediaType === self::MEDIA_TYPE_MEMBERSHIPS_NRPS)) {
            $this->source->getGroups();
            $parameters['groups'] = 'true';
        }
        $userResults = array();
        $memberships = array();
        $endpoint = $this->endpoint;
        do {
            $http = $this->send('GET', $parameters);
            $url = '';
            if (!empty($http) && $http->ok) {
                $isjsonld = false;
                if (isset($http->responseJson->pageOf) && isset($http->responseJson->pageOf->membershipSubject) &&
                    isset($http->responseJson->pageOf->membershipSubject->membership)) {
                    $isjsonld = true;
                    $memberships = array_merge($memberships, $http->responseJson->pageOf->membershipSubject->membership);
                    if (!empty($http->responseJson->nextPage)) {
                        $http->relativeLinks['next'] = $http->responseJson->nextPage;
                    }
                } elseif (isset($http->responseJson->members)) {
                    $memberships = array_merge($memberships, $http->responseJson->members);
                }
                if (!$this->pagingMode && $http->hasRelativeLink('next')) {
                    $url = $http->getRelativeLink('next');
                    $this->endpoint = $url;
                    $parameters = array();
                }
            } else {
                $userResults = false;
            }
        } while ($url);
        $this->endpoint = $endpoint;
        if ($userResults !== false) {
            if ($isLink) {
//                $oldUsers = $this->source->getUserResultSourcedIDs(true, LTI\Tool::ID_SCOPE_RESOURCE);
                $oldUsers = $this->source->getUserResultSourcedIDs(true, \ILIAS\LTI\ToolProvider\Tool::ID_SCOPE_RESOURCE);
            }
            foreach ($memberships as $membership) {
                if ($isjsonld) {
                    $member = $membership->member;
                    if ($isLink) {
//                        $userresult = LTI\UserResult::fromResourceLink($this->source, $member->userId);
                        $userresult = \ILIAS\LTI\ToolProvider\UserResult::fromResourceLink($this->source, $member->userId);
                    } else {
//                        $userresult = new LTI\UserResult();
                        $userresult = new \ILIAS\LTI\ToolProvider\UserResult();
                        $userresult->ltiUserId = $member->userId;
                    }

                    // Set the user name
                    $firstname = (isset($member->givenName)) ? $member->givenName : '';
                    $lastname = (isset($member->familyName)) ? $member->familyName : '';
                    $fullname = (isset($member->name)) ? $member->name : '';
                    $userresult->setNames($firstname, $lastname, $fullname);

                    // Set the sourcedId
                    if (isset($member->sourcedId)) {
                        $userresult->sourcedId = $member->sourcedId;
                    }

                    // Set the username
                    if (isset($member->ext_username)) {
                        $userresult->username = $member->ext_username;
                    } elseif (isset($member->ext_user_username)) {
                        $userresult->username = $member->ext_user_username;
                    } elseif (isset($member->custom_username)) {
                        $userresult->username = $member->custom_username;
                    } elseif (isset($member->custom_user_username)) {
                        $userresult->username = $member->custom_user_username;
                    }

                    // Set the user email
                    $email = (isset($member->email)) ? $member->email : '';
                    $userresult->setEmail($email, $this->source->getPlatform()->defaultEmail);

                    // Set the user roles
                    if (isset($membership->role)) {
                        $roles = $this->parseContextsInArray($http->responseJson->{'@context'}, $membership->role);
//                        $userresult->roles = LTI\Tool::parseRoles($roles, LTI\Util::LTI_VERSION2);
                        $userresult->roles = \ILIAS\LTI\ToolProvider\Tool::parseRoles($roles, \ILIAS\LTI\ToolProvider\Util::LTI_VERSION2);
                    }

                    // If a result sourcedid is provided save the user
                    if ($isLink) {
                        $doSave = false;
                        if (isset($membership->message)) {
                            foreach ($membership->message as $message) {
                                if (isset($message->message_type) && (($message->message_type === 'basic-lti-launch-request') || ($message->message_type) === 'LtiResourceLinkRequest')) {
                                    if (isset($message->lis_result_sourcedid)) {
                                        $userresult->ltiResultSourcedId = $message->lis_result_sourcedid;
                                        $doSave = true;
                                    }
                                    if (isset($message->ext)) {
                                        if (empty($userresult->username)) {
                                            if (!empty($message->ext->username)) {
                                                $userresult->username = $message->ext->username;
                                            } elseif (!empty($message->ext->user_username)) {
                                                $userresult->username = $message->ext->user_username;
                                            }
                                        }
                                    }
                                    if (isset($message->custom)) {
                                        if (empty($userresult->username)) {
                                            if (!empty($message->custom->username)) {
                                                $userresult->username = $message->custom->username;
                                            } elseif (!empty($message->custom->user_username)) {
                                                $userresult->username = $message->custom->user_username;
                                            }
                                        }
                                    }
                                    break;
                                }
                            }
                        }
                        if (!$doSave && isset($member->resultSourcedId)) {
                            $userresult->setResourceLinkId($this->source->getId());
                            $userresult->ltiResultSourcedId = $member->resultSourcedId;
                            $doSave = true;
                        }
                        if ($doSave) {
                            $userresult->save();
                        }
                    }
                    $userResults[] = $userresult;

                    // Remove old user (if it exists)
                    if ($isLink) {
//                        unset($oldUsers[$userresult->getId(LTI\Tool::ID_SCOPE_RESOURCE)]);
                        unset($oldUsers[$userresult->getId(\ILIAS\LTI\ToolProvider\Tool::ID_SCOPE_RESOURCE)]);
                    }
                } else {  // Version 2
                    $member = $membership;
                    if ($isLink) {
//                        $userresult = LTI\UserResult::fromResourceLink($this->source, $member->user_id);
                        $userresult = \ILIAS\LTI\ToolProvider\UserResult::fromResourceLink($this->source, $member->user_id);
                    } else {
//                        $userresult = new LTI\UserResult();
                        $userresult = new \ILIAS\LTI\ToolProvider\UserResult();
                        $userresult->ltiUserId = $member->user_id;
                    }

                    // Set the user name
                    $firstname = (isset($member->given_name)) ? $member->given_name : '';
                    $lastname = (isset($member->family_name)) ? $member->family_name : '';
                    $fullname = (isset($member->name)) ? $member->name : '';
                    $userresult->setNames($firstname, $lastname, $fullname);

                    // Set the sourcedId
                    if (isset($member->lis_person_sourcedid)) {
                        $userresult->sourcedId = $member->lis_person_sourcedid;
                    }

                    // Set the user email
                    $email = (isset($member->email)) ? $member->email : '';
                    $userresult->setEmail($email, $this->source->getPlatform()->defaultEmail);

                    // Set the user roles
                    if (isset($member->roles)) {
//                        $userresult->roles = LTI\Tool::parseRoles($member->roles, LTI\Util::LTI_VERSION2);
                        $userresult->roles = \ILIAS\LTI\ToolProvider\Tool::parseRoles($member->roles, \ILIAS\LTI\ToolProvider\Util::LTI_VERSION2);
                    }

                    // If a result sourcedid is provided save the user
                    if ($isLink) {
                        $doSave = false;
                        if (isset($member->message)) {
                            foreach ($member->message as $message) {
                                if (isset($message->{'https://purl.imsglobal.org/spec/lti/claim/message_type'}) && (($message->{'https://purl.imsglobal.org/spec/lti/claim/message_type'} === 'basic-lti-launch-request') || ($message->{'https://purl.imsglobal.org/spec/lti/claim/message_type'}) === 'LtiResourceLinkRequest')) {
                                    if (isset($message->{'https://purl.imsglobal.org/spec/lti-bo/claim/basicoutcome'}) &&
                                        isset($message->{'https://purl.imsglobal.org/spec/lti-bo/claim/basicoutcome'}->lis_result_sourcedid)) {
                                        $userresult->ltiResultSourcedId = $message->{'https://purl.imsglobal.org/spec/lti-bo/claim/basicoutcome'}->lis_result_sourcedid;
                                        $doSave = true;
                                    }
                                    if (isset($message->{'https://purl.imsglobal.org/spec/lti/claim/ext'})) {
                                        if (empty($userresult->username)) {
                                            if (!empty($message->{'https://purl.imsglobal.org/spec/lti/claim/ext'}->username)) {
                                                $userresult->username = $message->{'https://purl.imsglobal.org/spec/lti/claim/ext'}->username;
                                            } elseif (!empty($message->{'https://purl.imsglobal.org/spec/lti/claim/ext'}->user_username)) {
                                                $userresult->username = $message->{'https://purl.imsglobal.org/spec/lti/claim/ext'}->user_username;
                                            }
                                        }
                                    }
                                    if (isset($message->{'https://purl.imsglobal.org/spec/lti/claim/custom'})) {
                                        if (empty($userresult->username)) {
                                            if (!empty($message->{'https://purl.imsglobal.org/spec/lti/claim/custom'}->username)) {
                                                $userresult->username = $message->{'https://purl.imsglobal.org/spec/lti/claim/custom'}->username;
                                            } elseif (!empty($message->{'https://purl.imsglobal.org/spec/lti/claim/custom'}->user_username)) {
                                                $userresult->username = $message->{'https://purl.imsglobal.org/spec/lti/claim/custom'}->user_username;
                                            }
                                        }
                                    }
                                    break;
                                }
                            }
                        }
                        if ($doSave) {
                            $userresult->save();
                        }
                    }
                    $userResults[] = $userresult;
                    if (isset($member->group_enrollments)) {
                        $userresult->groups = array();
                        foreach ($member->group_enrollments as $group) {
                            $groupId = $group->group_id;
                            if (!array_key_exists($groupId, $this->source->groups)) {
                                $this->source->groups[$groupId] = array('title' => "Group {$groupId}");
                            }
                            if (!empty($this->source->groups[$groupId]['set'])) {
                                $this->source->groupSets[$this->source->groups[$groupId]['set']]['num_members']++;
                                if ($userresult->isStaff()) {
                                    $this->source->groupSets[$this->source->groups[$groupId]['set']]['num_staff']++;
                                }
                                if ($userresult->isLearner()) {
                                    $this->source->groupSets[$this->source->groups[$groupId]['set']]['num_learners']++;
                                }
                            }
                            $userresult->groups[] = $groupId;
                        }
                    }

                    // Remove old user (if it exists)
                    if ($isLink) {
//                        unset($oldUsers[$userresult->getId(LTI\Tool::ID_SCOPE_RESOURCE)]);
                        unset($oldUsers[$userresult->getId(\ILIAS\LTI\ToolProvider\Tool::ID_SCOPE_RESOURCE)]);
                    }
                }
            }

            /// Delete any old users which were not in the latest list from the platform if request is not paged
            if ($isLink && !$this->pagingMode) {
                foreach ($oldUsers as $id => $userresult) {
                    $userresult->delete();
                }
            }
        }

        return $userResults;
    }
}
