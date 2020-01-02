<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilChatroom
 * Keeps methods to prepare and display the history task.
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomHistoryGUI extends ilChatroomGUIHandler
{
    /**
     * {@inheritdoc}
     */
    public function __construct(ilChatroomObjectGUI $gui)
    {
        parent::__construct($gui);
        require_once 'Modules/Chatroom/classes/class.ilChatroomFormFactory.php';
        require_once 'Modules/Chatroom/classes/class.ilChatroom.php';
        require_once 'Modules/Chatroom/classes/class.ilChatroomUser.php';
    }

    public function byDayExport()
    {
        $this->tabs->activateSubTab('byday');
        $this->byDay(true);
    }

    /**
     * Prepares and displays history period form by day.
     * @param bool $export
     */
    public function byDay($export = false)
    {
        $room = ilChatroom::byObjectId($this->gui->object->getId());

        $this->mainTpl->addJavaScript('./Services/Form/js/date_duration.js');

        $scope = $room->getRoomId();

        $chat_user   = new ilChatroomUser($this->ilUser, $room);
        $formFactory = new ilChatroomFormFactory();

        $durationForm = $formFactory->getPeriodForm();
        $durationForm->setTitle($this->ilLng->txt('history_byday_title'));
        $durationForm->addCommandButton('history-byDayExport', $this->ilLng->txt('export'));
        $durationForm->addCommandButton('history-byDay', $this->ilLng->txt('show'));
        $durationForm->setFormAction($this->ilCtrl->getFormAction($this->gui), 'history-byDay');

        $messages           = array();
        $psessions          = array();
        $submit_request     = strtolower($_SERVER['REQUEST_METHOD']) == 'post';
        $from               = null;
        $to                 = null;

        if ($submit_request) {
            if ($durationForm->checkInput()) {
                $period   = $durationForm->getItemByPostVar('timeperiod');

                $messages = $room->getHistory(
                    $from = $period->getStart(),
                    $to   = $period->getEnd(),
                    $chat_user->getUserId(),
                    isset($_REQUEST['scope']) ? $_REQUEST['scope'] : 0
                );

                $psessions = $room->getPrivateRoomSessions(
                    $from,
                    $to,
                    $chat_user->getUserId(),
                    $scope
                );
            } else {
                $export = false;
            }

            $durationForm->setValuesByPost();
        }

        $this->showMessages($messages, $durationForm, $export, $psessions, $from, $to);
    }

    /**
     * Prepares history table and displays it.
     * @param       $messages
     * @param       $durationForm
     * @param bool  $export
     * @param array $psessions
     * @param       $from
     * @param       $to
     */
    private function showMessages($messages, $durationForm, $export = false, $psessions = array(), $from, $to)
    {
        include_once 'Modules/Chatroom/classes/class.ilChatroom.php';

        $this->redirectIfNoPermission('read');

        $this->gui->switchToVisibleMode();

        $this->mainTpl->addCSS('Modules/Chatroom/templates/default/style.css');

        // should be able to grep templates
        if ($export) {
            $roomTpl = new ilTemplate('tpl.history_export.html', true, true, 'Modules/Chatroom');
        } else {
            $roomTpl = new ilTemplate('tpl.history.html', true, true, 'Modules/Chatroom');
        }

        $scopes = array();

        if ($export) {
            ilDatePresentation::setUseRelativeDates(false);
        }

        $time_format = $this->ilUser->getTimeFormat();

        $prevDate      = '';
        $messagesShown = 0;
        $lastDateTime  = null;
        foreach ($messages as $message) {
            //$message['message']->content = json_decode($message['message']->content);

            switch ($message['message']->type) {
                case 'message':
                    if (($_REQUEST['scope'] && $message['message']->subRoomId == $_REQUEST['scope']) || (!$_REQUEST['scope'] && !$message['message']->subRoomId)) {
                        $date        = new ilDate($message['timestamp'], IL_CAL_UNIX);
                        $dateTime    = new ilDateTime($message['timestamp'], IL_CAL_UNIX);
                        $currentDate = ilDatePresentation::formatDate($dateTime);

                        $roomTpl->setCurrentBlock('MESSAGELINE');
                        $roomTpl->setVariable('MESSAGECONTENT', $message['message']->content); // oops... it is a message? ^^
                        $roomTpl->setVariable('MESSAGESENDER', $message['message']->from->username);
                        if (null == $lastDateTime ||
                            date('d', $lastDateTime->get(IL_CAL_UNIX)) != date('d', $dateTime->get(IL_CAL_UNIX)) ||
                            date('m', $lastDateTime->get(IL_CAL_UNIX)) != date('m', $dateTime->get(IL_CAL_UNIX)) ||
                            date('Y', $lastDateTime->get(IL_CAL_UNIX)) != date('Y', $dateTime->get(IL_CAL_UNIX))
                        ) {
                            $roomTpl->setVariable('MESSAGEDATE', ilDatePresentation::formatDate($date));
                        }

                        if ($prevDate != $currentDate) {
                            switch ($time_format) {
                                case ilCalendarSettings::TIME_FORMAT_24:
                                    $date_string = $dateTime->get(IL_CAL_FKT_DATE, 'H:i', $this->ilUser->getTimeZone());
                                    break;
                                case ilCalendarSettings::TIME_FORMAT_12:
                                    $date_string = $dateTime->get(IL_CAL_FKT_DATE, 'g:ia', $this->ilUser->getTimeZone());
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

        foreach ($psessions as $session) {
            $scopes[$session['proom_id']] = $session['title'];
        }

        if (isset($scopes[''])) {
            unset($scopes['']);
        }

        if (!$messagesShown) {
            //$roomTpl->touchBlock('NO_MESSAGES');
            $roomTpl->setVariable('LBL_NO_MESSAGES', $this->ilLng->txt('no_messages'));
        }

        asort($scopes, SORT_STRING);

        $scopes = array($this->ilLng->txt('main')) + $scopes;

        if (count($scopes) > 1) {
            $select = new ilSelectInputGUI($this->ilLng->txt('scope'), 'scope');
            $select->setOptions($scopes);

            if (isset($_REQUEST['scope'])) {
                $select->setValue($_REQUEST['scope']);
            }

            $durationForm->addItem($select);
        }

        $room = ilChatroom::byObjectId($this->gui->object->getId());
        //if ($room->getSetting('private_rooms_enabled')) {

        $prevUseRelDates = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(false);

        if ($from instanceof ilDateTime && $to instanceof ilDateTime) {
            $unixFrom = $from->getUnixTime();
            $unixTo   = $to->getUnixTime();

            if ($unixFrom == $unixTo) {
                $date     = new ilDate($unixFrom, IL_CAL_UNIX);
                $date_sub = ilDatePresentation::formatDate($date);
            } else {
                $date1    = new ilDate($unixFrom, IL_CAL_UNIX);
                $date2    = new ilDate($unixTo, IL_CAL_UNIX);
                $date_sub = ilDatePresentation::formatPeriod($date1, $date2);
            }
            ilDatePresentation::setUseRelativeDates($prevUseRelDates);

            $isPrivateRoom = (boolean) ((int) $_REQUEST['scope']);
            if ($isPrivateRoom) {
                $roomTpl->setVariable('ROOM_TITLE', sprintf($this->ilLng->txt('history_title_private_room'), $scopes[(int) $_REQUEST['scope']]) . ' (' . $date_sub . ')');
            } else {
                $roomTpl->setVariable('ROOM_TITLE', sprintf($this->ilLng->txt('history_title_general'), $this->gui->object->getTitle()) . ' (' . $date_sub . ')');
            }
        }

        if ($export) {
            header("Content-Type: text/html");
            header("Content-Disposition: attachment; filename=\"" . urlencode($scopes[(int) $_REQUEST['scope']] . '.html') . "\"");
            echo $roomTpl->get();
            exit;
        }

        $roomTpl->setVariable('PERIOD_FORM', $durationForm->getHTML());

        $this->mainTpl->setVariable('ADM_CONTENT', $roomTpl->get());
    }

    public function bySessionExport()
    {
        $this->tabs->activateSubTab('bysession');
        $this->bySession(true);
    }

    /**
     * Prepares and displays history period form by session.
     * @param bool $export
     */
    public function bySession($export = false)
    {
        $room = ilChatroom::byObjectId($this->gui->object->getId());

        $scope = $room->getRoomId();

        $chat_user = new ilChatroomUser($this->ilUser, $room);

        $formFactory  = new ilChatroomFormFactory();
        $durationForm = $formFactory->getSessionForm($room->getSessions($chat_user));
        $durationForm->setTitle($this->ilLng->txt('history_bysession_title'));
        $durationForm->addCommandButton('history-bySessionExport', $this->ilLng->txt('export'));
        $durationForm->addCommandButton('history-bySession', $this->ilLng->txt('show'));
        $durationForm->setFormAction(
            $this->ilCtrl->getFormAction($this->gui),
            'history-bySession'
        );

        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            $durationForm->checkInput();
            $postVals = explode(',', $_POST['session']);
            $durationForm->setValuesByArray(array('session' => $_POST['session']));

            $messages = $room->getHistory(
                $from = new ilDateTime($postVals[0], IL_CAL_UNIX),
                $to = new ilDateTime($postVals[1], IL_CAL_UNIX),
                $chat_user->getUserId(),
                isset($_REQUEST['scope']) ? $_REQUEST['scope'] : 0
            );
        } else {
            $last_session = $room->getLastSession($chat_user);

            if ($last_session) {
                $from = new ilDateTime($last_session['connected'], IL_CAL_UNIX);
                $to   = new ilDateTime($last_session['disconnected'], IL_CAL_UNIX);
            } else {
                $from = null;
                $to   = null;
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
        } else {
            $from      = new ilDateTime();
            $to        = new ilDateTime();
            $psessions = array();
        }

        $psessions = $room->getPrivateRoomSessions(
            $from,
            $to,
            $chat_user->getUserId(),
            $scope
        );

        $this->showMessages($messages, $durationForm, $export, $psessions, $from, $to);
    }
    
    /**
     * {@inheritdoc}
     */
    public function executeDefault($method)
    {
        $this->byDay();
    }
}
