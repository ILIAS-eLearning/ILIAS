<?php

namespace ILIAS\LTI\ToolProvider;

use ILIAS\LTI\ToolProvider\DataConnector\DataConnector;

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
class User
{

/**
 * User's first name.
 *
 * @var string $firstname
 */
    public string $firstname = '';
    /**
     * User's last name (surname or family name).
     *
     * @var string $lastname
     */
    public string $lastname = '';
    /**
     * User's fullname.
     *
     * @var string $fullname
     */
    public string $fullname = '';
    /**
     * User's email address.
     *
     * @var string $email
     */
    public string $email = '';
    /**
     * User's image URI.
     *
     * @var string $image
     */
    public string $image = '';
    /**
     * Roles for user.
     *
     * @var array $roles
     */
    public array $roles = array();
    /**
     * Groups for user.
     *
     * @var array $groups
     */
    public array $groups = array();
    /**
     * User's result sourcedid.
     *
     * @var string $ltiResultSourcedId
     */
    public ?string $ltiResultSourcedId = null;
    /**
     * Date/time the record was created.
     *
     * @var int|null $created
     */
    public ?int $created = null;
    /**
     * Date/time the record was last updated.
     *
     * @var int|null $updated
     */
    public ?int $updated = null;

    /**
         * Resource link object.
         */
    private ?\ILIAS\LTI\ToolProvider\ResourceLink $resourceLink = null;
    /**
         * Resource link record ID.
         */
    private ?int $resourceLinkId = null;
    /**
         * User record ID value.
         */
    private ?string $id = null;
    /**
     * user ID as supplied in the last connection request.
     *
     * @var string $ltiUserId
     */
    public ?string $ltiUserId = null;
    /**
     * Data connector object or string.
     *
     * @var mixed $dataConnector
     */
    private $dataConnector = null;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->initialize();
    }

    /**
     * Initialise the user.
     */
    public function initialize() : void
    {
        $this->firstname = '';
        $this->lastname = '';
        $this->fullname = '';
        $this->email = '';
        $this->image = '';
        $this->roles = array();
        $this->groups = array();
        $this->ltiResultSourcedId = null;
        $this->created = null;
        $this->updated = null;
    }

    /**
     * Initialise the user.
     *
     * Pseudonym for initialize().
     */
    public function initialise() : void
    {
        $this->initialize();
    }

    /**
     * Save the user to the database.
     *
     * @return boolean True if the user object was successfully saved
     */
    public function save() : bool
    {
        if (!empty($this->ltiResultSourcedId) && !is_null($this->resourceLinkId)) {
            $ok = $this->getDataConnector()->saveUser($this);
        } else {
            $ok = true;
        }

        return $ok;
    }

    /**
     * Delete the user from the database.
     *
     * @return boolean True if the user object was successfully deleted
     */
    public function delete() : bool
    {
        $ok = $this->getDataConnector()->deleteUser($this);

        return $ok;
    }

    /**
     * Get resource link.
     *
     * @return ResourceLink Resource link object
     */
    public function getResourceLink() : ?\ILIAS\LTI\ToolProvider\ResourceLink
    {
        if (is_null($this->resourceLink) && !is_null($this->resourceLinkId)) {
            $this->resourceLink = ResourceLink::fromRecordId($this->resourceLinkId, $this->getDataConnector());
        }

        return $this->resourceLink;
    }

    /**
     * Get record ID of user.
     *
     * @return int Record ID of user
     */
    public function getRecordId() : ?string
    {
        return $this->id;
    }

    /**
     * Set record ID of user.
     *
     * @param int $id  Record ID of user
     */
    public function setRecordId(int $id) : void
    {
        $this->id = (string) $id;
    }

    /**
     * Set resource link ID of user.
     *
     * @param int $resourceLinkId  Resource link ID of user
     */
    public function setResourceLinkId(int $resourceLinkId) : void
    {
        $this->resourceLinkId = $resourceLinkId;
    }

    /**
     * Get the data connector.
     *
     * @return mixed Data connector object or string
     */
    public function getDataConnector()
    {
        return $this->dataConnector;
    }

    /**
     * Get the user ID (which may be a compound of the tool consumer and resource link IDs).
     * @param int|null $idScope Scope to use for user ID (optional, default is null for consumer default setting)
     * @return string User ID value
     */
    public function getId(int $idScope = null) : string
    {
        if (empty($idScope)) {
            if (!is_null($this->resourceLink)) {
                $idScope = $this->resourceLink->getConsumer()->idScope;
            } else {
                $idScope = ToolProvider::ID_SCOPE_ID_ONLY;
            }
        }
        switch ($idScope) {
            case ToolProvider::ID_SCOPE_GLOBAL:
                $id = $this->getResourceLink()->getKey() . ToolProvider::ID_SCOPE_SEPARATOR . $this->ltiUserId;
                break;
            case ToolProvider::ID_SCOPE_CONTEXT:
                $id = $this->getResourceLink()->getKey();
                if ($this->resourceLink->getContextId()) {
                    $id .= ToolProvider::ID_SCOPE_SEPARATOR . $this->resourceLink->getContextId();
                }
                $id .= ToolProvider::ID_SCOPE_SEPARATOR . $this->ltiUserId;
                break;
            case ToolProvider::ID_SCOPE_RESOURCE:
                $id = $this->getResourceLink()->getKey();
                if ($this->resourceLink->ltiResourceLinkId) {
                    $id .= ToolProvider::ID_SCOPE_SEPARATOR . $this->resourceLink->ltiResourceLinkId;
                }
                $id .= ToolProvider::ID_SCOPE_SEPARATOR . $this->ltiUserId;
                break;
            default:
                $id = $this->ltiUserId;
                break;
        }

        return $id;
    }

    /**
     * Set the user's name.
     *
     * @param string $firstname User's first name.
     * @param string $lastname User's last name.
     * @param string $fullname User's full name.
     */
    public function setNames(string $firstname, string $lastname, string $fullname) : void
    {
        $names = array(0 => '', 1 => '');
        if (!empty($fullname)) {
            $this->fullname = trim($fullname);
            $names = preg_split("/[\s]+/", $this->fullname, 2);
        }
        if (!empty($firstname)) {
            $this->firstname = trim($firstname);
            $names[0] = $this->firstname;
        } elseif (!empty($names[0])) {
            $this->firstname = $names[0];
        } else {
            $this->firstname = 'User';
        }
        if (!empty($lastname)) {
            $this->lastname = trim($lastname);
            $names[1] = $this->lastname;
        } elseif (!empty($names[1])) {
            $this->lastname = $names[1];
        } else {
            $this->lastname = $this->ltiUserId;
        }
        if (empty($this->fullname)) {
            $this->fullname = "{$this->firstname} {$this->lastname}";
        }
    }

    /**
     * Set the user's email address.
     * @param string      $email        Email address value
     * @param string|null $defaultEmail Value to use if no email is provided (optional, default is none)
     */
    public function setEmail(string $email, ?string $defaultEmail = null) : void
    {
        if (!empty($email)) {
            $this->email = $email;
        } elseif (!empty($defaultEmail)) {
            $this->email = $defaultEmail;
            if (substr($this->email, 0, 1) === '@') {
                $this->email = $this->getId() . $this->email;
            }
        } else {
            $this->email = '';
        }
    }

    /**
     * Check if the user is an administrator (at any of the system, institution or context levels).
     *
     * @return boolean True if the user has a role of administrator
     */
    public function isAdmin() : bool
    {
        return $this->hasRole('Administrator') || $this->hasRole('urn:lti:sysrole:ims/lis/SysAdmin') ||
               $this->hasRole('urn:lti:sysrole:ims/lis/Administrator') || $this->hasRole('urn:lti:instrole:ims/lis/Administrator');
    }

    /**
     * Check if the user is staff.
     *
     * @return boolean True if the user has a role of instructor, contentdeveloper or teachingassistant
     */
    public function isStaff() : bool
    {
        return ($this->hasRole('Instructor') || $this->hasRole('ContentDeveloper') || $this->hasRole('TeachingAssistant'));
    }

    /**
     * Check if the user is a learner.
     *
     * @return boolean True if the user has a role of learner
     */
    public function isLearner() : bool
    {
        return $this->hasRole('Learner');
    }

    /**
     * Load the user from the database.
     *
     * @param int $id     Record ID of user
     * @param DataConnector   $dataConnector    Database connection object
     *
     * @return User  User object
     */
    public static function fromRecordId(int $id, \ILIAS\LTI\ToolProvider\DataConnector\DataConnector $dataConnector) : \ILIAS\LTI\ToolProvider\User
    {
        $user = new User();
        $user->dataConnector = $dataConnector;
        $user->load($id);

        return $user;
    }

    /**
         * Class constructor from resource link.
         * @param ResourceLink $resourceLink Resource_Link object
         * @param string              $ltiUserId    User ID value
         */
    public static function fromResourceLink(ResourceLink $resourceLink, string $ltiUserId) : \ILIAS\LTI\ToolProvider\User
    {
        $user = new User();
        $user->resourceLink = $resourceLink;
//        if (!is_null($resourceLink)) {
            $user->resourceLinkId = $resourceLink->getRecordId();
            $user->dataConnector = $resourceLink->getDataConnector();
//        }
        $user->ltiUserId = $ltiUserId;
        if (!empty($ltiUserId)) {
            $user->load();
        }

        return $user;
    }

    ###
    ###  PRIVATE METHODS
    ###

    /**
     * Check whether the user has a specified role name.
     *
     * @param string $role Name of role
     *
     * @return boolean True if the user has the specified role
     */
    private function hasRole(string $role) : bool
    {
        if (substr($role, 0, 4) !== 'urn:') {
            $role = 'urn:lti:role:ims/lis/' . $role;
        }

        return in_array($role, $this->roles);
    }

    /**
     * Load the user from the database.
     * @param int|null $id Record ID of user (optional, default is null)
     * @return boolean True if the user object was successfully loaded
     */
    private function load(?int $id = null) : bool
    {
        $this->initialize();
        $this->id = $id;
        $dataConnector = $this->getDataConnector();
        if (!is_null($dataConnector)) {
            return $dataConnector->loadUser($this);
        }

        return false;
    }
}
