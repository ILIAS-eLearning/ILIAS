<?php declare(strict_types=1);

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
 * Class ilChatroomUser
 * @author  Andreas Kordosz <akordosz@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomUser
{
    private ilObjUser $user;
    private ilChatroom $room;
    private string $username = '';

    public function __construct(ilObjUser $user, ilChatroom $chatroom)
    {
        $this->user = $user;
        $this->room = $chatroom;
    }
    
    public function enabledBroadcastTyping() : bool
    {
        return ilUtil::yn2tf((string) $this->user->getPref('chat_broadcast_typing'));
    }

    /**
     * Returns Ilias User ID. If user is anonymous, a random negative User ID
     * is created, stored in SESSION, and returned.
     * @return int
     */
    public function getUserId() : int
    {
        $user_id = $this->user->getId();

        if ($this->user->isAnonymous()) {
            $session = ilSession::get('chat');
            if (isset($session[$this->room->getRoomId()]['user_id'])) {
                return $session[$this->room->getRoomId()]['user_id'];
            }

            $user_id = random_int(-99999, -20);

            $session[$this->room->getRoomId()]['user_id'] = $user_id;
            ilSession::set('chat', $session);
        }

        return $user_id;
    }

    /**
     * Returns username from Object or SESSION. If no Username is set, the login name
     * will be returned.
     * @return string
     */
    public function getUsername() : string
    {
        if ($this->username) {
            return $this->username;
        }

        $session = ilSession::get('chat');
        if (
            is_array($session) &&
            isset($session[$this->room->getRoomId()]['username']) &&
            $session[$this->room->getRoomId()]['username']
        ) {
            return $session[$this->room->getRoomId()]['username'];
        }

        return $this->user->getLogin();
    }

    /**
     * Sets and stores given username in SESSION
     * @param string $username
     */
    public function setUsername(string $username) : void
    {
        $this->username = htmlspecialchars($username);

        $session = ilSession::get('chat');
        $session[$this->room->getRoomId()]['username'] = $this->username;
        ilSession::set('chat', $session);
    }

    /**
     * Returns an array of chat-name suggestions
     * @return array<string, string>
     */
    public function getChatNameSuggestions() : array
    {
        $options = [];

        if ($this->user->isAnonymous()) {
            $options['anonymousName'] = $this->buildAnonymousName();
        } else {
            $options['fullname'] = $this->buildFullname();
            $options['shortname'] = $this->buildShortname();
            $options['login'] = $this->buildLogin();
        }

        return $options;
    }

    public function buildAnonymousName() : string
    {
        return str_replace(
            '#',
            (string) random_int(0, 10000),
            $this->room->getSetting('autogen_usernames')
        );
    }

    public function buildFullname() : string
    {
        $tmp = $this->user->getPref('public_profile');
        $this->user->setPref('public_profile', 'y');
        $public_name = $this->user->getPublicName();
        $this->user->setPref('public_profile', $tmp);

        return $public_name;
    }

    /**
     * Returns first letter of users firstname, followed by dot lastname
     * @return string
     */
    public function buildShortname() : string
    {
        $firstname = $this->user->getFirstname();

        return $firstname[0] . '. ' . $this->user->getLastname();
    }

    public function buildLogin() : string
    {
        return $this->user->getLogin();
    }

    public function buildUniqueUsername(string $username) : string
    {
        global $DIC;

        $username = htmlspecialchars(trim($username));
        $usernames = [];
        $uniqueName = $username;

        $rset = $DIC->database()->query(
            'SELECT * FROM chatroom_users WHERE ' .
            $DIC->database()->like('userdata', 'text', '%"login":"' . $username . '%') .
            ' AND room_id = ' . $DIC->database()->quote($this->room->getRoomId(), 'integer')
        );

        while (($row = $DIC->database()->fetchAssoc($rset))) {
            $json = json_decode($row['userdata'], true, 512, JSON_THROW_ON_ERROR);
            $usernames[] = $json['login'];
        }

        for ($index = 1, $indexMax = count($usernames); $index <= $indexMax; $index++) {
            if (in_array($uniqueName, $usernames, true)) {
                $uniqueName = sprintf('%s_%d', $username, $index);
            }
        }

        return $uniqueName;
    }

    /**
     * @param int[] $usrIds
     * @param int|null $roomId
     * @return array
     */
    public static function getUserInformation(array $usrIds, ?int $roomId = null) : array
    {
        global $DIC;

        $users = [];

        $query = '
			SELECT userdata
			FROM chatroom_users WHERE ' . $DIC->database()->in('user_id', $usrIds, false, 'integer');

        if (null !== $roomId) {
            $query .= ' AND room_id = ' . $DIC->database()->quote($roomId, 'integer');
        }

        $res = $DIC->database()->query($query);
        while ($row = $DIC->database()->fetchAssoc($res)) {
            $users[] = json_decode($row['userdata'], false, 512, JSON_THROW_ON_ERROR);
        }

        return $users;
    }
}
