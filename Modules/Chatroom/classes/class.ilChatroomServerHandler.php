<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Modules/Chatroom/classes/class.ilChatroom.php';

/**
 * Class ilChatroomServerHandler
 *
 * @author Jan Posselt <jposselt@databay.de>
 * @version $Id$
 *
 * @ingroup ModulesChatroom
 */
class ilChatroomServerHandler
{

	/**
	 * Constructor
	 *
	 * Calls $this->handleCommand method.
	 */
	public function __construct()
	{
		$this->handleCommand( $_REQUEST['task'] );
		exit;
	}

	/**
	 * Returns connector
	 *
	 * Instantiates ilChatroomServerConnector with server settings and
	 * returns it.
	 *
	 * @return ilChatroomServerConnector
	 */
	public function getConnector()
	{
		require_once 'Modules/Chatroom/classes/class.ilChatroomServerConnector.php';
		require_once 'Modules/Chatroom/classes/class.ilChatroomServerSettings.php';
		require_once 'Modules/Chatroom/classes/class.ilChatroomAdmin.php';

		//$settings = new ilChatroomServerSettings();
		$settings = ilChatroomAdmin::getDefaultConfiguration()->getServerSettings();
		$connector = new ilChatroomServerConnector( $settings );

		return $connector;
	}

	/**
	 * Calls command depending on given $task
	 *
	 * @param string $task
	 */
	private function handleCommand($task)
	{
		switch($task)
		{
			case 'disconnectedUsers':
				if (isset($_REQUEST['scope'])) {
					$this->disconnectedUsers( $_REQUEST['scope'] );
				}
				$this->cleanupPrivateRooms();
				break;
			case 'serverStarted':
				$this->serverStarted();
				break;
		}
	}

	public function cleanupPrivateRooms() {
		$deletableRooms = ilChatroom::findDeletablePrivateRooms();

		$connector = $this->getConnector();

		foreach($deletableRooms as $deletableRoom) {

			$room = ilChatroom::byObjectId($deletableRoom['object_id']);

			$params['user'] =  -1; //$chat_user->getUserId();
			$room->closePrivateRoom($deletableRoom['proom_id'], $chat_user, $settings);
			$query = http_build_query( $params );

			$message = json_encode( array(
                                'type'		=> 'private_room_deleted',
                                'timestamp' => date( 'c' ),
                                'public' => 1,
                                'id' => $deletableRoom['proom_id'],
                                'proom_id' => $deletableRoom['proom_id'],
                                'message' => array(
                                    'message'=> 'room deleted',
                                    'public' => '1',
                                    'user' => 'system'
                                    )
                                    ) );

                                    $result = $connector->sendMessage( $room->getRoomId(), $message, array('public' => 1) );

		}
	}

	/**
	 * Calls $chatroom->disconnectUsers for every given user in every
	 * given scope ($usersByScope), sends corresponding status messages to
	 * chatroom and adds event in history.
	 *
	 * @param array $usersByScope
	 */
	private function disconnectedUsers($usersByScope)
	{
		require_once 'Modules/Chatroom/classes/class.ilChatroom.php';

		foreach( $usersByScope as $scope => $users )
		{
			$users = explode( ',', $users );
			$chatroom = ilChatroom::byRoomId( $scope );

			if( $chatroom instanceof ilChatroom && is_array( $users ) )
			{
				$users = array_filter( $users );
				$userDetails = $this->getUserInformation($users);
				$message = json_encode( array(
						'type'		=> 'disconnected',
						'users'	    => $userDetails,
						'timestamp' => date( 'c' )
				) );

				$chatroom->disconnectUsers( $users );
				
				if(!isset($_REQUEST['handledAction']) || $_REQUEST['handledAction'] != 'kick')
				{
					$this->getConnector()->sendMessage( $chatroom->getRoomId(), $message );
				}

				if( true || $chatroom->getSetting( 'enable_history' ) ) {
					$messageObject = array(
						'type'		=> 'disconnected',
						'users'		=> $userDetails,
						'timestamp' => date( 'c' )
					);

					$chatroom->addHistoryEntry( $messageObject );
				}
			}
		}
	}

	/**
	 * Requires ilChatroom and calls its disconnectAllUsersFromAllRooms()
	 * method.
	 */
	private function serverStarted()
	{
		require_once 'Modules/Chatroom/classes/class.ilChatroom.php';

		ilChatroom::disconnectAllUsersFromAllRooms();
	}

	private function getUserInformation($user_ids) {
	    global $ilDB;
	    
	    $rset = $ilDB->query('SELECT userdata FROM chatroom_users WHERE ' . $ilDB->in('user_id', $user_ids, false, 'integer'));
	    $users = array();
	    while($row = $ilDB->fetchAssoc($rset)) {
		$users[] = json_decode($row['userdata']);
	    }
	    return $users;
	}
}

?>
