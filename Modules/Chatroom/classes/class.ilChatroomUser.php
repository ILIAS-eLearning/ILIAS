<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroomUser
 *
 * @author Andreas Kordosz <akordosz@databay.de>
 * @version $Id$
 *
 * @ingroup ModulesChatroom
 */
class ilChatroomUser
{
	/**
	 *
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
	 *
	 * Requires ilObjUser and sets $this->user and $this->room using given
	 * $user and $chatroom.
	 *
	 * @param ilObjUser $user
	 * @param ilChatroom $chatroom
	 */
	public function __construct(ilObjUser $user, ilChatroom $chatroom)
	{
	    require_once 'Services/User/classes/class.ilObjUser.php';

	    $this->user = $user;
	    $this->room = $chatroom;
	}

	/**
	 * Returns Ilias User ID. If user is anonymous, a random negative User ID
	 * is created, stored in SESSION, and returned.
	 *
	 * @param ilObjUser $user
	 * @return integer
	 */
	public function getUserId()
	{
		$user_id = $this->user->getId();

		if($this->user->isAnonymous())
		{
			if(isset($_SESSION['chat'][$this->room->getRoomId()]['user_id']))
			{
				return $_SESSION['chat'][$this->room->getRoomId()]['user_id'];
			}
			else
			{
				$user_id                                               = mt_rand(-99999, -20);
				$_SESSION['chat'][$this->room->getRoomId()]['user_id'] = $user_id;
				return $user_id;
			}
		}
		else
		{
			return $user_id;
		}
	}

	/**
	 * Sets and stores given username in SESSION
	 *
	 * @param string $username
	 */
	public function setUsername($username)
	{
	    $this->username = htmlspecialchars( $username );
	    $_SESSION['chat'][$this->room->getRoomId()]['username'] = $this->username;
	}

	/**
	 * Returns username from Object or SESSION. If no Username is set, the login name
	 * will be returned.
	 *
	 * @return string
	 */
	public function getUsername()
	{
		if($this->username)
		{
			return $this->username;
		}
		else if($_SESSION['chat'][$this->room->getRoomId()]['username'])
		{
			return $_SESSION['chat'][$this->room->getRoomId()]['username'];
		}
		else
		{
			return $this->user->getLogin();
		}
	}

	/**
	 * Returns an array of chat-name suggestions
	 *
	 * @return array
	 */
	public function getChatNameSuggestions()
	{
		$options = array();

		if($this->user->isAnonymous())
		{
			$options['anonymousName'] = $this->buildAnonymousName();
		}
		else
		{
			$options['fullname']  = $this->buildFullname();
			$options['shortname'] = $this->buildShortname();
			$options['login']     = $this->buildLogin();
		}

		return $options;
	}

	/**
	 * Returns an anonymous username containing a random number.
	 *
	 * @return string
	 */
	public function buildAnonymousName()
	{
	    $anonymous_name = str_replace(
		    '#', mt_rand( 0, 10000 ), $this->room->getSetting('autogen_usernames')
	    );

	    return $anonymous_name;
	}

	/**
	 * Returns user login
	 *
	 * @return string
	 */
	public function buildLogin()
	{
	    return $this->user->getLogin();
	}

	/**
	 * Returns users first & lastname
	 *
	 * @return string
	 */
	public function buildFullname()
	{
	    return $this->user->getPublicName();
	}

	/**
	 * Returns first letter of users firstname, followed by dot lastname
	 *
	 * @return string
	 */
	public function buildShortname()
	{
	    $firstname = $this->user->getFirstname();

	    return $firstname{0} . '. ' . $this->user->getLastname();
	}
}