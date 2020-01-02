<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/User/classes/class.ilObjUser.php';

/**
 * Class ilChatroomUser
 * @author  Andreas Kordosz <akordosz@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomUser
{
    /**
     * @var ilObjUser
     */
    private $user;

    /**
     * @var string
     */
    private $username;

    /**
     * @var ilChatroom
     */
    private $room;

    /**
     * Constructor
     * Requires ilObjUser and sets $this->user and $this->room using given
     * $user and $chatroom.
     * @param ilObjUser  $user
     * @param ilChatroom $chatroom
     */
    public function __construct(ilObjUser $user, ilChatroom $chatroom)
    {
        $this->user = $user;
        $this->room = $chatroom;
    }

    /**
     * Returns Ilias User ID. If user is anonymous, a random negative User ID
     * is created, stored in SESSION, and returned.
     * @param ilObjUser $user
     * @return integer
     */
    public function getUserId()
    {
        $user_id = $this->user->getId();

        if ($this->user->isAnonymous()) {
            if (isset($_SESSION['chat'][$this->room->getRoomId()]['user_id'])) {
                return $_SESSION['chat'][$this->room->getRoomId()]['user_id'];
            } else {
                $user_id                                               = mt_rand(-99999, -20);
                $_SESSION['chat'][$this->room->getRoomId()]['user_id'] = $user_id;
                return $user_id;
            }
        } else {
            return $user_id;
        }
    }

    /**
     * Returns username from Object or SESSION. If no Username is set, the login name
     * will be returned.
     * @return string
     */
    public function getUsername()
    {
        if ($this->username) {
            return $this->username;
        } elseif ($_SESSION['chat'][$this->room->getRoomId()]['username']) {
            return $_SESSION['chat'][$this->room->getRoomId()]['username'];
        } else {
            return $this->user->getLogin();
        }
    }

    /**
     * Sets and stores given username in SESSION
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username                                         = htmlspecialchars($username);
        $_SESSION['chat'][$this->room->getRoomId()]['username'] = $this->username;
    }

    /**
     * Returns an array of chat-name suggestions
     * @return array
     */
    public function getChatNameSuggestions()
    {
        $options = array();

        if ($this->user->isAnonymous()) {
            $options['anonymousName'] = $this->buildAnonymousName();
        } else {
            $options['fullname']  = $this->buildFullname();
            $options['shortname'] = $this->buildShortname();
            $options['login']     = $this->buildLogin();
        }

        return $options;
    }

    /**
     * Returns an anonymous username containing a random number.
     * @return string
     */
    public function buildAnonymousName()
    {
        $anonymous_name = str_replace(
            '#',
            mt_rand(0, 10000),
            $this->room->getSetting('autogen_usernames')
        );

        return $anonymous_name;
    }

    /**
     * Returns users first & lastname
     * @return string
     */
    public function buildFullname()
    {
        $tmp = $this->user->getPref('public_profile');
        $this->user->setPref('public_profile', 'y');
        $pn = $this->user->getPublicName();
        $this->user->setPref('public_profile', $tmp);
        return $pn;
    }

    /**
     * Returns first letter of users firstname, followed by dot lastname
     * @return string
     */
    public function buildShortname()
    {
        $firstname = $this->user->getFirstname();

        return $firstname{0} . '. ' . $this->user->getLastname();
    }

    /**
     * Returns user login
     * @return string
     */
    public function buildLogin()
    {
        return $this->user->getLogin();
    }

    /**
     * @param string $username
     * @return string
     */
    public function buildUniqueUsername($username)
    {
        global $DIC;

        $username   = htmlspecialchars(trim($username));
        $usernames  = array();
        $uniqueName = $username;

        $rset = $DIC->database()->query(
            'SELECT * FROM chatroom_users WHERE '
            . $DIC->database()->like('userdata', 'text', '%"login":"' . $username . '%')
            . ' AND room_id = ' . $DIC->database()->quote($this->room->getRoomId(), 'integer')
        );

        while (($row = $DIC->database()->fetchAssoc($rset))) {
            $json        = json_decode($row['userdata'], true);
            $usernames[] = $json['login'];
        }

        for ($index = 1; $index <= \count($usernames); $index++) {
            if (in_array($uniqueName, $usernames)) {
                $uniqueName = sprintf('%s_%d', $username, $index);
            }
        }

        return $uniqueName;
    }

    /**
     * @param array    $usrIds
     * @param int|null $roomId
     * @return array
     */
    public static function getUserInformation(array $usrIds, int $roomId = null) : array
    {
        global $DIC;

        $users = [];

        $query = '
			SELECT userdata
			FROM chatroom_users WHERE ' . $DIC->database()->in('user_id', $usrIds, false, 'integer');

        if (null !== $roomId) {
            $query .= ' AND room_id = ' . $DIC->database()->quote($roomId, 'integer');
        }

        $res  = $DIC->database()->query($query);
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $users[] = json_decode($row['userdata']);
        }

        return $users;
    }
}
