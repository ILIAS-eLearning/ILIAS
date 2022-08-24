<?php

declare(strict_types=1);

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
 * Class ilChatroom
 * Keeps methods to prepare and display the history task.
 * @author  Jan Posselt <jposselt@databay.de>
 * @version $Id$
 * @ingroup ModulesChatroom
 */
class ilChatroomHistoryGUI extends ilChatroomGUIHandler
{
    public function byDayExport(): void
    {
        $this->tabs->activateSubTab('byday');
        $this->byDay(true);
    }

    public function byDay(bool $export = false): void
    {
        $room = ilChatroom::byObjectId($this->gui->getObject()->getId());
        $this->exitIfNoRoomExists($room);

        $scope = $room->getRoomId();

        $chat_user = new ilChatroomUser($this->ilUser, $room);
        $formFactory = new ilChatroomFormFactory();

        $durationForm = $formFactory->getPeriodForm();
        $durationForm->setTitle($this->ilLng->txt('history_byday_title'));
        $durationForm->addCommandButton('history-byDayExport', $this->ilLng->txt('export'));
        $durationForm->addCommandButton('history-byDay', $this->ilLng->txt('show'));
        $durationForm->setFormAction($this->ilCtrl->getFormAction($this->gui, 'history-byDay'));

        $messages = [];
        $psessions = [];
        $submit_request = strtolower($this->http->request()->getServerParams()['REQUEST_METHOD']) === 'post';
        $from = null;
        $to = null;

        if ($submit_request) {
            if ($durationForm->checkInput()) {
                $period = $durationForm->getItemByPostVar('timeperiod');

                $messages = $room->getHistory(
                    $from = $period->getStart(),
                    $to = $period->getEnd(),
                    $chat_user->getUserId(),
                    $this->getRequestValue('scope', $this->refinery->kindlyTo()->int(), 0)
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

    private function showMessages(
        array $messages,
        ilPropertyFormGUI $durationForm,
        bool $export = false,
        array $psessions = [],
        ?ilDateTime $from = null,
        ?ilDateTime $to = null
    ): void {
        $this->redirectIfNoPermission('read');

        $this->gui->switchToVisibleMode();

        $this->mainTpl->addCss('Modules/Chatroom/templates/default/style.css');

        // should be able to grep templates
        if ($export) {
            $roomTpl = new ilGlobalTemplate('tpl.history_export.html', true, true, 'Modules/Chatroom');
        } else {
            $roomTpl = new ilTemplate('tpl.history.html', true, true, 'Modules/Chatroom');
        }

        $scopes = [];
        $isScopeRequest = $this->hasRequestValue('scope');
        $requestScope = $this->getRequestValue('scope', $this->refinery->kindlyTo()->int(), 0);

        if ($export) {
            ilDatePresentation::setUseRelativeDates(false);
        }

        $time_format = $this->ilUser->getTimeFormat();

        $prevDate = '';
        $messagesShown = 0;
        $lastDateTime = null;
        foreach ($messages as $message) {
            switch ($message['message']->type) {
                case 'message':
                    if (
                        !$message['message']->subRoomId ||
                        (
                            $isScopeRequest &&
                            (int) $message['message']->subRoomId === $requestScope
                        )
                    ) {
                        $date = new ilDate($message['timestamp'], IL_CAL_UNIX);
                        $dateTime = new ilDateTime($message['timestamp'], IL_CAL_UNIX);
                        $currentDate = ilDatePresentation::formatDate($dateTime);

                        $roomTpl->setCurrentBlock('MESSAGELINE');
                        $roomTpl->setVariable('MESSAGECONTENT', $message['message']->content); // oops... it is a message? ^^
                        $roomTpl->setVariable('MESSAGESENDER', $message['message']->from->username);
                        if (null === $lastDateTime ||
                            date('d', $lastDateTime->get(IL_CAL_UNIX)) !== date('d', $dateTime->get(IL_CAL_UNIX)) ||
                            date('m', $lastDateTime->get(IL_CAL_UNIX)) !== date('m', $dateTime->get(IL_CAL_UNIX)) ||
                            date('Y', $lastDateTime->get(IL_CAL_UNIX)) !== date('Y', $dateTime->get(IL_CAL_UNIX))
                        ) {
                            $roomTpl->setVariable('MESSAGEDATE', ilDatePresentation::formatDate($date));
                        }

                        if ($prevDate !== $currentDate) {
                            switch ($time_format) {
                                case ilCalendarSettings::TIME_FORMAT_24:
                                    $date_string = $dateTime->get(IL_CAL_FKT_DATE, 'H:i', $this->ilUser->getTimeZone());
                                    break;
                                case ilCalendarSettings::TIME_FORMAT_12:
                                default:
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
            $roomTpl->setVariable('LBL_NO_MESSAGES', $this->ilLng->txt('no_messages'));
        }

        asort($scopes, SORT_STRING);

        $scopes = [$this->ilLng->txt('main')] + $scopes;

        if (count($scopes) > 1) {
            $select = new ilSelectInputGUI($this->ilLng->txt('scope'), 'scope');
            $select->setOptions($scopes);

            if ($isScopeRequest) {
                $select->setValue($requestScope);
            }

            $durationForm->addItem($select);
        }

        $prevUseRelDates = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(false);

        if ($from instanceof ilDateTime && $to instanceof ilDateTime) {
            $unixFrom = $from->getUnixTime();
            $unixTo = $to->getUnixTime();

            if ($unixFrom === $unixTo) {
                $date = new ilDate($unixFrom, IL_CAL_UNIX);
                $date_sub = ilDatePresentation::formatDate($date);
            } else {
                $date1 = new ilDate($unixFrom, IL_CAL_UNIX);
                $date2 = new ilDate($unixTo, IL_CAL_UNIX);
                $date_sub = ilDatePresentation::formatPeriod($date1, $date2);
            }
            ilDatePresentation::setUseRelativeDates($prevUseRelDates);

            $isPrivateRoom = $isScopeRequest;
            if ($isPrivateRoom) {
                $roomTpl->setVariable(
                    'ROOM_TITLE',
                    sprintf(
                        $this->ilLng->txt('history_title_private_room'),
                        $scopes[$requestScope]
                    ) . ' (' . $date_sub . ')'
                );
            } else {
                $roomTpl->setVariable(
                    'ROOM_TITLE',
                    sprintf($this->ilLng->txt('history_title_general'), $this->gui->getObject()->getTitle()) . ' (' . $date_sub . ')'
                );
            }
        }

        if ($export) {
            ilUtil::deliverData(
                $roomTpl->get(),
                ilFileUtils::getASCIIFilename($scopes[$requestScope] . '.html'),
                'text/html'
            );
        }

        $roomTpl->setVariable('PERIOD_FORM', $durationForm->getHTML());

        $this->mainTpl->setVariable('ADM_CONTENT', $roomTpl->get());
    }

    public function bySessionExport(): void
    {
        $this->tabs->activateSubTab('bysession');
        $this->bySession(true);
    }

    public function bySession(bool $export = false): void
    {
        $room = ilChatroom::byObjectId($this->gui->getObject()->getId());
        $this->exitIfNoRoomExists($room);

        $scope = $room->getRoomId();

        $chat_user = new ilChatroomUser($this->ilUser, $room);

        $formFactory = new ilChatroomFormFactory();
        $durationForm = $formFactory->getSessionForm($room->getSessions($chat_user));
        $durationForm->setTitle($this->ilLng->txt('history_bysession_title'));
        $durationForm->addCommandButton('history-bySessionExport', $this->ilLng->txt('export'));
        $durationForm->addCommandButton('history-bySession', $this->ilLng->txt('show'));
        $durationForm->setFormAction(
            $this->ilCtrl->getFormAction($this->gui, 'history-bySession')
        );

        if (strtolower($this->http->request()->getServerParams()['REQUEST_METHOD']) === 'post') {
            $session = $this->getRequestValue('session', $this->refinery->kindlyTo()->string());
            $durationForm->checkInput();
            $postVals = explode(',', $session);
            $durationForm->setValuesByArray([
                'session' => $session
            ]);

            $messages = $room->getHistory(
                $from = new ilDateTime($postVals[0], IL_CAL_UNIX),
                $to = new ilDateTime($postVals[1], IL_CAL_UNIX),
                $chat_user->getUserId(),
                $this->getRequestValue('scope', $this->refinery->kindlyTo()->int(), 0)
            );
        } else {
            $last_session = $room->getLastSession($chat_user);

            if ($last_session) {
                $from = new ilDateTime($last_session['connected'], IL_CAL_UNIX);
                $to = new ilDateTime($last_session['disconnected'], IL_CAL_UNIX);
            } else {
                $from = null;
                $to = null;
            }

            $messages = $room->getHistory(
                $from,
                $to,
                $chat_user->getUserId(),
                $this->getRequestValue('scope', $this->refinery->kindlyTo()->int(), 0)
            );
        }

        $from = new ilDateTime();
        $to = new ilDateTime();
        $psessions = [];
        if ($from && $to) {
            $psessions = $room->getPrivateRoomSessions(
                $from,
                $to,
                $chat_user->getUserId(),
                $scope
            );
        }

        $this->showMessages($messages, $durationForm, $export, $psessions, $from, $to);
    }

    public function executeDefault(string $requestedMethod): void
    {
        $this->byDay();
    }
}
