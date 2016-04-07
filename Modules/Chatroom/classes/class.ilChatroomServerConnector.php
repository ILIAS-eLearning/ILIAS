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
	 * @var null|bool
	 */
	protected static $connection_status = null;

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
	 * @return string|false
	 */
	protected function file_get_contents($url, array $stream_context_params = null)
	{
		$credentials = $this->settings->getAuthKey() . ':' . $this->settings->getAuthSecret();
		$header = "Connection: close\r\n" .
					"Content-Type: application/json; charset=utf-8\r\n".
				  "Authorization: Basic ". base64_encode($credentials);

		$ctx = array(
			'http'  => array(
				'method' => 'GET',
				'header' => $header
			),
			'https' => array(
				'method' => 'GET',
				'header' => $header
			)
		);

		if(is_array($stream_context_params))
		{
			$ctx = array_merge_recursive($ctx, $stream_context_params);
		}

		set_error_handler(function($severity, $message, $file, $line) {
			throw new ErrorException($message, $severity, $severity, $file, $line);
		});

		try
		{
			$response = file_get_contents($url, null, stream_context_create($ctx));
			restore_error_handler();
			return $response;
		}
		catch(Exception $e)
		{
			restore_error_handler();
			ilLoggerFactory::getLogger('chatroom')->alert($e->getMessage());
		}

		return false;
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
			$this->settings->getURL('Connect', $scope) . '/' . $userId
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
	 * @param int	$scope
	 * @param int	$subScope
	 * @param int	$user
	 * @return mixed
	 */
	public function sendCreatePrivateRoom($scope, $subScope, $user, $title)
	{
		return $this->file_get_contents(
			$this->settings->getURL('CreatePrivateRoom', $scope) . '/' . $subScope . '/' . $user . '/' . rawurlencode($title)
		);
	}

	/**
	 * @param int	$scope
	 * @param int	$subScope
	 * @param int	$user
	 * @return mixed
	 */
	public function sendEnterPrivateRoom($scope, $subScope, $user)
	{
		return $this->file_get_contents(
			$this->settings->getURL('EnterPrivateRoom', $scope) . '/' . $subScope . '/' . $user
		);
	}

	/**
	 * @param int $scope
	 * @param int $subScope
	 * @param int $user
	 *
	 * @return mixed
	 */
	public function sendLeavePrivateRoom($scope, $subScope, $user)
	{
		return $this->file_get_contents(
			$this->settings->getURL('LeavePrivateRoom', $scope) . '/' . $subScope . '/' . $user
		);
	}

	/**
	 * @param int	$scope
	 * @param int	$subScope
	 * @param int	$user
	 * @return mixed
	 */
	public function sendDeletePrivateRoom($scope, $subScope, $user)
	{
		return $this->file_get_contents(
				$this->settings->getURL('DeletePrivateRoom', $scope) . '/' . $subScope . '/' . $user
		);
	}

	/**
	 * @param int	$scope
	 * @param int	$subScope
	 * @param int	$user
	 * @return mixed
	 *
	 * @deprecated Please use sendEnterPrivateRoom instead
	 */
	public function enterPrivateRoom($scope, $subScope, $user)
	{
		return $this->sendEnterPrivateRoom($scope, $subScope, $user);
	}

	/**
	 * @param int $scope
	 * @param int $subScope
	 * @param int $user
	 *
	 * @return string
	 */
	public function sendClearMessages($scope, $subScope, $user)
	{
		return $this->file_get_contents(
			$this->settings->getURL('ClearMessages', $scope) . '/' . $subScope . '/' . $user
		);
	}

	/**
	 * @param string $scope
	 * @param int    $subScope
	 * @param int    $user
	 *
	 * @return mixed
	 *
	 * @deprecated; Use sendLeavePrivateRoom instead
	 */
	public function leavePrivateRoom($scope, $subScope, $user)
	{
		return $this->sendLeavePrivateRoom($scope, $subScope, $user);
	}

	/**
	 * @param int $scope
	 * @param int $subScope
	 * @param int $user
	 *
	 * @return string
	 */
	public function sendKick($scope, $subScope, $user)
	{
		return $this->kick($scope, $subScope, $user);
	}

	/**
	 * Returns kick URL
	 * Creates kick URL using given $scope and $query and returns it.
	 * @param string $scope
	 * @param int	 $user
	 * @return mixed
	 */
	public function kick($scope, $subScope, $user)
	{
		return $this->file_get_contents(
			$this->settings->getURL('Kick', $scope) . '/' . $subScope. '/' . $user
		);
	}

	public function sendBan($scope, $subScope, $user)
	{
		return $this->file_get_contents(
			$this->settings->getURL('Ban', $scope) . '/' . $subScope. '/' . $user
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
	 * @param int        $subScope
	 * @param ilChatroomUser  $inviter
	 * @param int        $invited_id
	 *
	 * @return array
	 *
	 * @deprecated
	 */
	public function inviteToPrivateRoom(ilChatRoom $room, $subScope, ilChatroomUser $inviter, $invited_id)
	{
		die('DEPRECATED');

		/*$user_id   = $chat_user->getUserId();

		if($subScope)
		{
			$room->inviteUserToPrivateRoom($invited_id, $subScope);
			$message = json_encode(array(
				'type'      => 'private_room_created',
				'users'     => $invited_id, //$users,
				'timestamp' => date('c'),
				'public'    => 0,
				'title'     => ilChatroom::lookupPrivateRoomTitle($subScope),
				'proom_id'  => $subScope,
				'message'   => array(
					'public' => '0',
					'user'   => 'system',
					'owner'  => $user_id
				)
			));

			//$this->sendMessage($room->getRoomId(), $message, array('public' => 0, 'recipients' => $invited_id));
		}

		if($room->isSubscribed($user_id))
		{
			$message = json_encode(array(
				'type'     => 'user_invited',
				'title'    => ilChatroom::lookupPrivateRoomTitle($subScope),
				'proom_id' => $subScope,
				'inviter'  => $inviter->getId(),
				'invited'  => $invited_id
			));

			//$this->sendMessage($room->getRoomId(), $message, array('public' => 0, 'recipients' => $invited_id));
		}

		return array('success' => true, 'message' => 'users invited');*/
	}

	/**
	 * @param int $scope
	 * @param int $subScope
	 * @param int $user
	 * @param int $invited_id
	 *
	 * @return mixed
	 */
	public function sendInviteToPrivateRoom($scope, $subScope, $user, $invited_id) {
		return $this->file_get_contents(
				$this->settings->getURL('InvitePrivateRoom', $scope) . '/' . $subScope . '/' . $user . '/' . $invited_id
		);
	}

	/**
	 * @param ilChatroom     $room
	 * @param string         $title
	 * @param ilChatroomUser $owner
	 * @return mixed
	 *
	 * @deprecated
	 */
	public function createPrivateRoom(ilChatroom $room, $title, ilChatroomUser $owner)
	{
		die('DEPRECATED');
		/*$settings = array(
			'public' => false,
		);

		$scope = $room->getRoomId();
		$subScope = $room->addPrivateRoom($title, $owner, $settings);*/

		$response       = $this->sendCreatePrivateRoom($scope, $subScope, $owner->getUserId());
		$responseObject = json_decode($response);

		if($responseObject->success == true)
		{
			/*$message = json_encode(array(
				'type'      => 'private_room_created',
				'timestamp' => date('c'),
				'public'    => 0,
				'title'     => $title,
				'id'        => $responseObject->id,
				'proom_id'  => $responseObject->id,
				'owner'     => $owner->getUserId(),
			));

			$result = $this->sendMessage($room->getRoomId(), $message, array('public' => 0, 'recipients' => $owner->getUserId()));*/

			//$query    = http_build_query($params);
			$response = $this->enterPrivateRoom($scope, $subScope, $owner->getUserId());

			$room->subscribeUserToPrivateRoom($subScope, $owner->getUserId());

			/*$message = json_encode(array(
				'type'      => 'private_room_entered',
				'user'      => $owner->getUserId(),
				'timestamp' => date('c'),
				'sub'       => $responseObject->id
			));
			$this->sendMessage($room->getRoomId(), $message);*/
		}
		return $responseObject;
	}

	/**
	 * @return bool
	 *
	 * @TODO Extract Status Code to Static
	 */
	public function isServerAlive()
	{
		$response = $this->file_get_contents(
			$this->settings->getURL('Heartbeat'),
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

		return $responseObject->status == 200;
	}

	public function createUniqueScopeId($roomId, $pRoomId = null)
	{
		if($pRoomId != null)
		{
			$roomId .= '-' . $pRoomId;
		}

		return $roomId;
	}

	/**
	 * @param bool|true $use_cache
	 * @return bool
	 */
	public static function checkServerConnection($use_cache = true)
	{
		if($use_cache && self::$connection_status !== null)
		{
			return self::$connection_status;
		}

		require_once 'Modules/Chatroom/classes/class.ilChatroomAdmin.php';
		$connector = new self(ilChatroomAdmin::getDefaultConfiguration()->getServerSettings());
		self::$connection_status = (bool)$connector->isServerAlive();

		return self::$connection_status;
	}
}