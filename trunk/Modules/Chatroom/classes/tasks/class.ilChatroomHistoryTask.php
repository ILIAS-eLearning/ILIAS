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
class ilChatroomHistoryTask extends ilChatroomTaskHandler
{

    private $gui;

    /**
     * Constructor
     *
     * Requires needed classes and sets $this->gui using given $gui.
     *
     * @param ilChatroomObjectGUI $gui
     */
    public function __construct(ilChatroomObjectGUI $gui)
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
	private function showMessages($messages, $durationForm, $export = false, $psessions = array(), $from, $to)
	{
		//global $tpl, $ilUser, $ilCtrl, $lng;
		global $tpl, $lng, $ilCtrl;

		include_once 'Modules/Chatroom/classes/class.ilChatroom.php';

		if(!ilChatroom::checkUserPermissions('read', $this->gui->ref_id))
		{
			$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", ROOT_FOLDER_ID);
			$ilCtrl->redirectByClass("ilrepositorygui", "");
		}

		$this->gui->switchToVisibleMode();

		$tpl->addCSS('Modules/Chatroom/templates/default/style.css');

		// should be able to grep templates 
		if($export)
		{
			$roomTpl = new ilTemplate('tpl.history_export.html', true, true, 'Modules/Chatroom');
		}
		else
		{
			$roomTpl = new ilTemplate('tpl.history.html', true, true, 'Modules/Chatroom');
		}

		$scopes = array();

		if($export)
		{
			ilDatePresentation::setUseRelativeDates(false);
		}

		global $ilUser;
		$time_format = $ilUser->getTimeFormat();

		$prevDate      = '';
		$messagesShown = 0;
		$lastDateTime = null;
		foreach($messages as $message)
		{
			$message['message']->message = json_decode($message['message']->message);

			switch($message['message']->type)
			{
				case 'message':
					if(($_REQUEST['scope'] && $message['message']->sub == $_REQUEST['scope']) || (!$_REQUEST['scope'] && !$message['message']->sub))
					{
						$date        = new ilDate($message['timestamp'], IL_CAL_UNIX);
						$dateTime    = new ilDateTime($message['timestamp'], IL_CAL_UNIX);
						$currentDate = ilDatePresentation::formatDate($dateTime);

						$roomTpl->setCurrentBlock('MESSAGELINE');
						$roomTpl->setVariable('MESSAGECONTENT', $message['message']->message->content); // oops... it is a message? ^^
						$roomTpl->setVariable('MESSAGESENDER', $message['message']->user->username);
						if(null == $lastDateTime ||
						   date('d', $lastDateTime->get(IL_CAL_UNIX)) != date('d', $dateTime->get(IL_CAL_UNIX)) ||
						   date('m', $lastDateTime->get(IL_CAL_UNIX)) != date('m', $dateTime->get(IL_CAL_UNIX)) ||
						   date('Y', $lastDateTime->get(IL_CAL_UNIX)) != date('Y', $dateTime->get(IL_CAL_UNIX)))
						{
							$roomTpl->setVariable('MESSAGEDATE', ilDatePresentation::formatDate($date));
						}
						
						if($prevDate != $currentDate)
						{
							switch($time_format)
							{
								case ilCalendarSettings::TIME_FORMAT_24:
									$date_string = $dateTime->get(IL_CAL_FKT_DATE, 'H:i', $ilUser->getTimeZone());
									break;
								case ilCalendarSettings::TIME_FORMAT_12:
									$date_string = $dateTime->get(IL_CAL_FKT_DATE, 'g:ia', $ilUser->getTimeZone());
									break;
							}

							$roomTpl->setVariable('MESSAGETIME', $date_string);
							$prevDate = $currentDate;
						}
			
						$roomTpl->parseCurrentBlock();

						$lastDateTime = $dateTime;

						++$messagesShown;
					}
					break;
			}
		}

		foreach($psessions as $session)
		{
			$scopes[$session['proom_id']] = $session['title'];
		}

		if(isset($scopes['']))
		{
			unset($scopes['']);
		}

		if(!$messagesShown)
		{
			//$roomTpl->touchBlock('NO_MESSAGES');
			$roomTpl->setVariable('LBL_NO_MESSAGES', $lng->txt('no_messages'));
		}

		asort($scopes, SORT_STRING);

		$scopes = array($lng->txt('main')) + $scopes;

		if(count($scopes) > 1)
		{
			$select = new ilSelectInputGUI($lng->txt('scope'), 'scope');
			$select->setOptions($scopes);

			if(isset($_REQUEST['scope']))
			{
				$select->setValue($_REQUEST['scope']);
			}

			$durationForm->addItem($select);
		}

		$room = ilChatroom::byObjectId($this->gui->object->getId());
		//if ($room->getSetting('private_rooms_enabled')) {

		$prevUseRelDates = ilDatePresentation::useRelativeDates();
		ilDatePresentation::setUseRelativeDates(false);

		$unixFrom = $from->getUnixTime();
		$unixTo   = $to->getUnixTime();

		if($unixFrom == $unixTo)
		{
			$date     = new ilDate($unixFrom, IL_CAL_UNIX);
			$date_sub = ilDatePresentation::formatDate($date);
		}
		else
		{
			$date1    = new ilDate($unixFrom, IL_CAL_UNIX);
			$date2    = new ilDate($unixTo, IL_CAL_UNIX);
			$date_sub = ilDatePresentation::formatPeriod($date1, $date2);
		}
		ilDatePresentation::setUseRelativeDates($prevUseRelDates);

		$isPrivateRoom = (boolean)((int)$_REQUEST['scope']);
		if($isPrivateRoom)
		{
			$roomTpl->setVariable('ROOM_TITLE', sprintf($lng->txt('history_title_private_room'), $scopes[(int)$_REQUEST['scope']]) . ' (' . $date_sub . ')');
		}
		else
		{
			$roomTpl->setVariable('ROOM_TITLE', sprintf($lng->txt('history_title_general'), $this->gui->object->getTitle()) . ' (' . $date_sub . ')');
		}

		//}

		if($export)
		{
			header("Content-Type: text/html");
			header("Content-Disposition: attachment; filename=\"" . urlencode($scopes[(int)$_REQUEST['scope']] . '.html') . "\"");
			echo $roomTpl->get();
			exit;
		}

		$roomTpl->setVariable('PERIOD_FORM', $durationForm->getHTML());

		$tpl->setVariable('ADM_CONTENT', $roomTpl->get());
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
	    $durationForm->addCommandButton( 'history-byDay', $lng->txt( 'show' ) );
	    $durationForm->setFormAction( $ilCtrl->getFormAction( $this->gui ), 'history-byDay' );

	    if( strtolower( $_SERVER['REQUEST_METHOD'] ) == 'post' )
	    {
		$durationForm->checkInput();
		$period = $durationForm->getItemByPostVar( 'timeperiod' );
		$messages = $room->getHistory(
			$from = $period->getStart(),
			$to = $period->getEnd(),
			/*$room->getSetting( 'restrict_history' ) ?*/ $chat_user->getUserId() /*: null*/,
			isset($_REQUEST['scope']) ? $_REQUEST['scope'] : 0
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
			$from,
			$to,
			$chat_user->getUserId(),
			isset($_REQUEST['scope']) ? $_REQUEST['scope'] : 0
		    );
	    }

	$psessions = $room->getPrivateRoomSessions(
		$from,
		$to,
		$chat_user->getUserId(),
		$scope
	);

	$this->showMessages( $messages, $durationForm, $export, $psessions, $from, $to );
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
	$durationForm->addCommandButton( 'history-bySession', $lng->txt( 'show' ) );
	$durationForm->setFormAction(
		$ilCtrl->getFormAction( $this->gui ), 'history-bySession'
	);

	if( strtolower( $_SERVER['REQUEST_METHOD'] ) == 'post' )
	{
	    $durationForm->checkInput();
	    $postVals = explode( ',', $_POST['session'] );
	    $durationForm->setValuesByArray( array('session' => $_POST['session']) );

	    $messages = $room->getHistory(
		$from = new ilDateTime( $postVals[0], IL_CAL_UNIX ),
		$to = new ilDateTime( $postVals[1], IL_CAL_UNIX ),
		$chat_user->getUserId(),
		isset($_REQUEST['scope']) ? $_REQUEST['scope'] : 0
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
		$from,
		$to,
		$chat_user->getUserId(),
		isset($_REQUEST['scope']) ? $_REQUEST['scope'] : 0
	    );
	}

        if ($from && $to) {
                $psessions = $room->getPrivateRoomSessions(
                        $from,
                        $to,
                        $chat_user->getUserId(),
                        $scope
                );
        }
        else {
                $from = new ilDateTime();
                $to = new ilDateTime();
                $psessions =  array();
        }
	
	$psessions = $room->getPrivateRoomSessions(
		$from,
		$to,
		$chat_user->getUserId(),
		$scope
	);

	$this->showMessages( $messages, $durationForm, $export, $psessions, $from, $to );
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