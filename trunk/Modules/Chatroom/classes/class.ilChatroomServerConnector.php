<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroomServerConnector
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomServerConnector
{
	/**
	 * @var ilChatroomServerSettings
	 */
	private $settings;

	/**
	 * Constructor
	 * Sets $this->settings using given $settings
	 * @param ilChatroomServerSettings $settings
	 */
	public function __construct(ilChatroomServerSettings $settings)
	{
		$this->settings = $settings;
	}

	/**
	 * @param string $url
	 * @param array  $stream_context_params
	 * @return string
	 */
	private function file_get_contents($url, array $stream_context_params = null)
	{
		$ctx = array(
			'http'  => array(
				'header' => 'Connection: close'
			),
			'https' => array(
				'header' => 'Connection: close'
			)
		);

		if(is_array($stream_context_params))
		{
			$ctx = array_merge_recursive($ctx, $stream_context_params);
		}

		return file_get_contents($url, null, stream_context_create($ctx));
	}

	/**
	 * Returns connect URL
	 * Creates connect URL using given $scope and $userId and returns it.
	 * @param string  $scope
	 * @param integer $userId
	 * @return mixed
	 */
	public function connect($scope, $userId)
	{
		return $this->file_get_contents(
			$this->settings->getURL('Connect', $scope) . '?id=' . $userId
		);
	}

	/**
	 * Returns post URL
	 * Creates post URL using given $scope and $query and returns it.
	 * @param string $scope
	 * @param string $query
	 * @return mixed
	 */
	public function post($scope, $query)
	{
		return $this->file_get_contents(
			$this->settings->getURL('Post', $scope) . '?' . $query
		);
	}

	/**
	 * @param string $scope
	 * @param string $query
	 * @return mixed
	 */
	private function sendCreatePrivateRoom($scope, $query)
	{
		return $this->file_get_contents(
			$this->settings->getURL('CreatePrivateRoom', $scope) . '?' . $query
		);
	}

	/**
	 * @param string $scope
	 * @param string $query
	 * @return mixed
	 */
	public function enterPrivateRoom($scope, $query)
	{
		return $this->file_get_contents(
			$this->settings->getURL('EnterPrivateRoom', $scope) . '?' . $query
		);
	}

	/**
	 * @param string $scope
	 * @param string $query
	 * @return mixed
	 */
	public function leavePrivateRoom($scope, $query)
	{
		return $this->file_get_contents(
			$this->settings->getURL('LeavePrivateRoom', $scope) . '?' . $query
		);
	}

	/**
	 * Returns kick URL
	 * Creates kick URL using given $scope and $query and returns it.
	 * @param string $scope
	 * @param string $query
	 * @return mixed
	 */
	public function kick($scope, $query)
	{
		return $this->file_get_contents(
			$this->settings->getURL('Kick', $scope) . '?' . $query
		);
	}

	/**
	 * Returns $this->settings
	 * @return ilChatroomServerSettings
	 */
	public function getSettings()
	{
		return $this->settings;
	}

	/**
	 * Returns if given message is sucessfully sent.
	 * Calls $this->post using given $scope and $query built by
	 * http_build_query with given $message and returns if message was sent
	 * sucessfully.
	 * @param string $scope
	 * @param string $message
	 * @return stdClass
	 */
	public function sendMessage($scope, $message, $options = array())
	{
		$query    = http_build_query(array('message' => $message) + $options);
		$response = $this->post($scope, $query);
		return @json_decode($response);
	}

	/**
	 * @param ilChatRoom $room
	 * @param int        $scope
	 * @param ilObjUser  $inviter
	 * @param int        $invited_id
	 * @return array
	 */
	public function inviteToPrivateRoom(ilChatRoom $room, $scope, ilObjUser $inviter, $invited_id)
	{
		$chat_user = new ilChatroomUser($inviter, $room);
		$user_id   = $chat_user->getUserId();

		if($scope)
		{
			$room->inviteUserToPrivateRoom($invited_id, $scope);
			$message = json_encode(array(
				'type'      => 'private_room_created',
				'users'     => $invited_id, //$users,
				'timestamp' => date('c'),
				'public'    => 0,
				'title'     => ilChatroom::lookupPrivateRoomTitle($scope),
				'proom_id'  => $scope,
				'message'   => array(
					'public' => '0',
					'user'   => 'system',
					'owner'  => $user_id
				)
			));

			$this->sendMessage($room->getRoomId(), $message, array('public' => 0, 'recipients' => $invited_id));
		}

		if($room->isSubscribed($user_id))
		{
			$message = json_encode(array(
				'type'     => 'user_invited',
				'title'    => ilChatroom::lookupPrivateRoomTitle($scope),
				'proom_id' => $scope,
				'inviter'  => $inviter->getId(),
				'invited'  => $invited_id
			));

			$this->sendMessage($room->getRoomId(), $message, array('public' => 0, 'recipients' => $invited_id));
		}

		return array('success' => true, 'message' => 'users invited');
	}

	/**
	 * @param ilChatroom     $room
	 * @param string         $title
	 * @param ilChatroomUser $owner
	 * @return mixed
	 */
	public function createPrivateRoom(ilChatroom $room, $title, ilChatroomUser $owner)
	{
		$settings = array(
			'public' => false,
		);

		$params['user'] = $owner->getUserId();
		$params['id']   = $room->addPrivateRoom($title, $owner, $settings);

		$query          = http_build_query($params);
		$response       = $this->sendCreatePrivateRoom($room->getRoomId(), $query);
		$responseObject = json_decode($response);
		$return         = $responseObject;
		if($responseObject->success == true)
		{
			$message = json_encode(array(
				'type'      => 'private_room_created',
				'timestamp' => date('c'),
				'public'    => 0,
				'title'     => $title,
				'id'        => $responseObject->id,
				'proom_id'  => $responseObject->id,
				'owner'     => $owner->getUserId(),
			));

			$result = $this->sendMessage($room->getRoomId(), $message, array('public' => 0, 'recipients' => $owner->getUserId()));

			$params         = array();
			$params['user'] = $owner->getUserId();
			$params['sub']  = $responseObject->id;

			$query    = http_build_query($params);
			$response = $this->enterPrivateRoom($room->getRoomId(), $query);

			$room->subscribeUserToPrivateRoom($params['sub'], $params['user']);

			$message = json_encode(array(
				'type'      => 'private_room_entered',
				'user'      => $owner->getUserId(),
				'timestamp' => date('c'),
				'sub'       => $responseObject->id
			));
			$this->sendMessage($room->getRoomId(), $message);
		}
		return $responseObject;
	}

	/**
	 * @return bool
	 */
	public function isServerAlive()
	{
		$response = @$this->file_get_contents(
			$this->settings->getURL('Status', 0),
			array(
				'http'  => array(
					'timeout' => 2
				),
				'https' => array(
					'timeout' => 2
				)
			)
		);

		$responseObject = json_decode($response);

		return $responseObject->success == true;
	}

	/**
	 * @return bool
	 */
	public static function checkServerConnection()
	{
		require_once 'Modules/Chatroom/classes/class.ilChatroomAdmin.php';
		$settings  = ilChatroomAdmin::getDefaultConfiguration()->getServerSettings();
		$connector = new ilChatroomServerConnector($settings);
		return $connector->isServerAlive();
	}
}