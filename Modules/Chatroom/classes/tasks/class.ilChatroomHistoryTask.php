<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilChatroom
 *
 * Keeps methods to prepare and display the history task.
 *
 * @author Jan Posselt <jposselt@databay.de>
 * @version $Id$
 *
 * @ingroup ModulesChatroom
 */
class ilChatroomHistoryTask extends ilDBayTaskHandler
{

	private $gui;

	/**
	 * Constructor
	 *
	 * Requires needed classes and sets $this->gui using given $gui.
	 *
	 * @param ilDBayObjectGUI $gui
	 */
	public function __construct(ilDBayObjectGUI $gui)
	{
		require_once 'Modules/Chatroom/classes/class.ilChatroomFormFactory.php';
		require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
		require_once 'Modules/Chatroom/classes/class.ilChatroomUser.php';

		$this->gui = $gui;
	}

	/**
	 * Prepares history table and displays it.
	 *
	 * @global ilTemplate $tpl
	 * @global ilLanguage $lng
	 * @param array $messages
	 * @param ilPropertyFormGUI $durationForm
	 */
	private function showMessages($messages, $durationForm, $export = false)
	{
		//global $tpl, $ilUser, $ilCtrl, $lng;
		global $tpl, $lng;

		$this->gui->switchToVisibleMode();

		$tpl->addCSS( 'Modules/Chatroom/templates/default/style.css' );

		$roomTpl = new ilTemplate( 'tpl.history'.($export ? '_export' : '').'.html', true, true, 'Modules/Chatroom' );

		$scopes = array();

		if ($export) {
			ilDatePresentation::setUseRelativeDates(false);
		}

		$prevDate = '';
		foreach( $messages as $message )
		{
			$message['message']->message = json_decode( $message['message']->message );

			switch($message['message']->type)
			{
				case 'message':
					$scopes[$message['message']->sub] = true;


					if (($_REQUEST['scope'] && $message['message']->sub == $_REQUEST['scope']) || (!$_REQUEST['scope'] && !$message['message']->sub)) {
						$dateTime = new ilDateTime($message['timestamp'], IL_CAL_UNIX);
						$currentDate = ilDatePresentation::formatDate($dateTime);

						$roomTpl->setCurrentBlock( 'MESSAGELINE' );
						$roomTpl->setVariable( 'MESSAGECONTENT', $message['message']->message->content ); // oops... it is a message? ^^
						$roomTpl->setVariable( 'MESSAGESENDER', $message['message']->user->username );
						if ($prevDate != $currentDate) {
							$roomTpl->setVariable( 'MESSAGEDATE',  $currentDate);
							$prevDate = $currentDate;
						}
						else {
							$roomTpl->setVariable( 'MESSAGEDATE',  "&nbsp;&nbsp;");
						}
						$roomTpl->parseCurrentBlock();
					}
					break;
			}
		}

		foreach(array_keys($scopes) as $scope) {
			if ($scope != '') {
				$scopes[$scope] = ilChatRoom::lookupPrivateRoomTitle($scope);
			}
				
		}

		if (isset($scopes[''])) {
			unset($scopes['']);
		}

		asort($scopes, SORT_STRING);

		$scopes = array($lng->txt('main')) + $scopes;


		if (count($scopes) > 1) {
			$select = new ilSelectInputGUI($lng->txt('scope'), 'scope');
			$select->setOptions($scopes);

			if (isset($_REQUEST['scope'])) {
				$select->setValue($_REQUEST['scope']);
			}

			$durationForm->addItem($select);
		}

		$roomTpl->setVariable( 'ROOM_TITLE', $scopes[(int)$_REQUEST['scope']]);

		if ($export) {
			header("Content-Type: text/html");
			header("Content-Disposition: attachment; filename=\"".  urlencode( $scopes[(int)$_REQUEST['scope']] . '.html' ) ."\"");
			echo $roomTpl->get();
			exit;
		}

		$roomTpl->setVariable( 'PERIOD_FORM', $durationForm->getHTML() );

		$tpl->setVariable( 'ADM_CONTENT', $roomTpl->get() );
	}

	public function byDayExport() {
		$this->byDay(true);
	}

	public function bySessionExport() {
		$this->bySession(true);
	}
	/**
	 * Prepares and displays history period form by day.
	 *
	 * @global ilLanguage $lng
	 * @global ilCtrl2 $ilCtrl
	 * @global ilObjUser $ilUser
	 */
	public function byDay($export = false)
	{
		global $lng, $ilCtrl, $ilUser, $tpl;

		$room = ilChatroom::byObjectId( $this->gui->object->getId() );

		$tpl->addJavaScript('./Services/Form/js/date_duration.js');

		$scope = $room->getRoomId();

		$chat_user = new ilChatroomUser( $ilUser, $room );
		$formFactory = new ilChatroomFormFactory();

		$durationForm = $formFactory->getPeriodForm();
		$durationForm->setTitle( $lng->txt('history_byday_title') );
		$durationForm->addCommandButton( 'history-byDayExport', $lng->txt( 'export' ) );
		$durationForm->addCommandButton( 'history-byDay', $lng->txt( 'update' ) );
		$durationForm->setFormAction( $ilCtrl->getFormAction( $this->gui ), 'history-byDay' );

		if( strtolower( $_SERVER['REQUEST_METHOD'] ) == 'post' )
		{
			$durationForm->checkInput();
			$period = $durationForm->getItemByPostVar( 'timeperiod' );
			$messages = $room->getHistory(
			$period->getStart(), $period->getEnd(),
			$room->getSetting( 'restrict_history' ) ? $chat_user->getUserId() : null
			);
		}
		else
		{
			$from = new ilDateTime( time() - 60 * 60, IL_CAL_UNIX );
			$to = new ilDateTime( time(), IL_CAL_UNIX );

			$period = $durationForm->getItemByPostVar( 'timeperiod' );
			$period->setStart( $from );
			$period->setEnd( $to );

			$messages = $room->getHistory(
			$from, $to,
			$room->getSetting( 'restrict_history' ) ? $chat_user->getUserId() : null
			);
		}

		$this->showMessages( $messages, $durationForm, $export );
	}

	/**
	 * Prepares and displays history period form by session.
	 *
	 * @global ilLanguage $lng
	 * @global ilCtrl2 $ilCtrl
	 * @global ilObjUser $ilUser
	 */
	public function bySession($export = false)
	{
		global $lng, $ilCtrl, $ilUser;

		$room = ilChatroom::byObjectId( $this->gui->object->getId() );

		$scope = $room->getRoomId();

		$chat_user = new ilChatroomUser( $ilUser, $room );

		$formFactory = new ilChatroomFormFactory();
		$durationForm = $formFactory->getSessionForm( $room->getSessions( $chat_user ) );
		$durationForm->setTitle( $lng->txt('history_bysession_title') );
		$durationForm->addCommandButton( 'history-bySessionExport', $lng->txt( 'export' ) );
		$durationForm->addCommandButton( 'history-bySession', $lng->txt( 'update' ) );
		$durationForm->setFormAction(
		$ilCtrl->getFormAction( $this->gui ), 'history-bySession'
		);

		if( strtolower( $_SERVER['REQUEST_METHOD'] ) == 'post' )
		{
			$durationForm->checkInput();
			$postVals = explode( ',', $_POST['session'] );
			$durationForm->setValuesByArray( array('session' => $_POST['session']) );

			$messages = $room->getHistory(
			new ilDateTime( $postVals[0], IL_CAL_UNIX ),
			new ilDateTime( $postVals[1], IL_CAL_UNIX ),
			$room->getSetting( 'restrict_history' ) ?
			$chat_user->getUserId() : null
			);
		}
		else
		{
			$last_session = $room->getLastSession( $chat_user );

			if( $last_session )
			{
				$from = new ilDateTime( $last_session['connected'], IL_CAL_UNIX );
				$to = new ilDateTime( $last_session['disconnected'], IL_CAL_UNIX );
			}
			else
			{
				$from = null;
				$to = null;
			}

			$messages = $room->getHistory(
			$from, $to,
			$room->getSetting( 'restrict_history' ) ?
			$chat_user->getUserId() : null
			);
		}

		$this->showMessages( $messages, $durationForm, $export );
	}

	/**
	 * Calls $this->byDay method.
	 *
	 * @param string $method
	 */
	public function executeDefault($method)
	{
		$this->byDay();
	}

}

?>