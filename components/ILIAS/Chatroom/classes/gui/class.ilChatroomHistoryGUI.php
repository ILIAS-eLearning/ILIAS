<?php

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

declare(strict_types=1);

class ilChatroomHistoryGUI extends ilChatroomGUIHandler
{
    /**
     * @param ilTemplate|ilGlobalTemplate $room_tpl
     */
    private function renderDateTimeInformation(
        $room_tpl,
        ?ilDateTime &$prev_date_time,
        ilDateTime $message_date_time,
        ilDate $message_date,
        ?string &$prev_date_time_presentation,
        string $message_date_time_presentation,
        string $time_format
    ): void {
        $render_parts = [];

        if (null === $prev_date_time ||
            date('d', $prev_date_time->get(IL_CAL_UNIX)) !== date('d', $message_date_time->get(IL_CAL_UNIX)) ||
            date('m', $prev_date_time->get(IL_CAL_UNIX)) !== date('m', $message_date_time->get(IL_CAL_UNIX)) ||
            date('Y', $prev_date_time->get(IL_CAL_UNIX)) !== date('Y', $message_date_time->get(IL_CAL_UNIX))
        ) {
            $render_parts['MESSAGEDATE'] = ilDatePresentation::formatDate($message_date);
            $prev_date_time = $message_date_time;
        }

        if ($prev_date_time_presentation !== $message_date_time_presentation) {
            $date_string = match ($time_format) {
                (string) ilCalendarSettings::TIME_FORMAT_24 => $message_date_time->get(
                    IL_CAL_FKT_DATE,
                    'H:i',
                    $this->ilUser->getTimeZone()
                ),
                default => $message_date_time->get(IL_CAL_FKT_DATE, 'g:ia', $this->ilUser->getTimeZone()),
            };

            $render_parts['MESSAGETIME'] = $date_string;
            $prev_date_time_presentation = $message_date_time_presentation;
        }

        if ($render_parts !== []) {
            $room_tpl->setCurrentBlock('datetime_line');
            foreach ($render_parts as $key => $value) {
                $room_tpl->setVariable($key, $value);
            }
            $room_tpl->parseCurrentBlock();
        }
    }

    public function byDayExport(): void
    {
        $this->tabs->activateSubTab('byday');
        $this->byDay(true);
    }

    public function byDay(bool $export = false): void
    {
        $room = ilChatroom::byObjectId($this->gui->getObject()->getId());
        $this->exitIfNoRoomExists($room);

        $chat_user = new ilChatroomUser($this->ilUser, $room);
        $formFactory = new ilChatroomFormFactory();

        $durationForm = $formFactory->getPeriodForm();
        $durationForm->setTitle($this->ilLng->txt('history_byday_title'));
        $durationForm->addCommandButton('history-byDayExport', $this->ilLng->txt('export'));
        $durationForm->addCommandButton('history-byDay', $this->ilLng->txt('show'));
        $durationForm->setFormAction($this->ilCtrl->getFormAction($this->gui, 'history-byDay'));

        $messages = [];
        $submit_request = strtolower($this->http->request()->getServerParams()['REQUEST_METHOD']) === 'post';
        $from = null;
        $to = null;

        if ($submit_request) {
            if ($durationForm->checkInput()) {
                /** @var ilDateDurationInputGUI $period */
                $period = $durationForm->getItemByPostVar('timeperiod');

                $messages = $room->getHistory(
                    $from = $period->getStart(),
                    $to = $period->getEnd(),
                    $chat_user->getUserId()
                );
            } else {
                $export = false;
            }

            $durationForm->setValuesByPost();
        }

        $this->showMessages($messages, $durationForm, $export, $from, $to, $room);
    }

    private function showMessages(
        array $messages,
        ilPropertyFormGUI $durationForm,
        bool $export = false,
        ?ilDateTime $from = null,
        ?ilDateTime $to = null,
        $room = null
    ): void {
        $this->redirectIfNoPermission('read');

        $this->gui->switchToVisibleMode();

        // should be able to grep templates
        if ($export) {
            $roomTpl = new ilGlobalTemplate('tpl.history_export.html', true, true, 'components/ILIAS/Chatroom');
        } else {
            $roomTpl = new ilTemplate('tpl.history.html', true, true, 'components/ILIAS/Chatroom');
        }

        if ($export) {
            ilDatePresentation::setUseRelativeDates(false);
        }

        $time_format = $this->ilUser->getTimeFormat();

        $num_messages_shown = 0;
        $prev_date_time_presentation = null;
        $prev_date_time = null;
        if ($export) {
            foreach ($messages as $message) {
                switch ($message['message']->type) {
                    case 'message':
                        $message_date = new ilDate($message['timestamp'], IL_CAL_UNIX);
                        $message_date_time = new ilDateTime($message['timestamp'], IL_CAL_UNIX);
                        $message_date_time_presentation = ilDatePresentation::formatDate($message_date_time);

                        $this->renderDateTimeInformation(
                            $roomTpl,
                            $prev_date_time,
                            $message_date_time,
                            $message_date,
                            $prev_date_time_presentation,
                            $message_date_time_presentation,
                            $time_format
                        );

                        $roomTpl->setCurrentBlock('message_line');
                        $roomTpl->setVariable('MESSAGECONTENT', htmlspecialchars($message['message']->content, ENT_QUOTES | ENT_SUBSTITUTE, 'utf-8')); // oops... it is a message? ^^
                        $roomTpl->setVariable('MESSAGESENDER', htmlspecialchars($message['message']->from->username, ENT_QUOTES | ENT_SUBSTITUTE, 'utf-8'));
                        $roomTpl->parseCurrentBlock();

                        $roomTpl->setCurrentBlock('row');
                        $roomTpl->parseCurrentBlock();

                        ++$num_messages_shown;
                        break;
                }
            }
        }

        if (!$num_messages_shown) {
            $roomTpl->setVariable('LBL_NO_MESSAGES', $this->ilLng->txt('no_messages'));
        }

        $scope = $this->ilLng->txt('main');

        $prevUseRelDates = ilDatePresentation::useRelativeDates();
        ilDatePresentation::setUseRelativeDates(false);

        if ($from instanceof ilDateTime && $to instanceof ilDateTime) {
            $unixFrom = $from->getUnixTime();
            $unixTo = $to->getUnixTime();

            if ($unixFrom === $unixTo) {
                $message_date = new ilDate($unixFrom, IL_CAL_UNIX);
                $date_sub = ilDatePresentation::formatDate($message_date);
            } else {
                $date1 = new ilDate($unixFrom, IL_CAL_UNIX);
                $date2 = new ilDate($unixTo, IL_CAL_UNIX);
                $date_sub = ilDatePresentation::formatPeriod($date1, $date2);
            }
            ilDatePresentation::setUseRelativeDates($prevUseRelDates);

            $roomTpl->setVariable(
                'ROOM_TITLE',
                sprintf($this->ilLng->txt('history_title_general'), $this->gui->getObject()->getTitle()) . ' (' . $date_sub . ')'
            );
        }

        if ($export) {
            ilUtil::deliverData(
                $roomTpl->get(),
                ilFileUtils::getASCIIFilename($scope . '.html'),
                'text/html'
            );
        }

        $roomTpl->setVariable('PERIOD_FORM', $durationForm->getHTML());

        if ($room && $messages !== []) {
            ilLinkifyUtil::initLinkify($this->mainTpl);
            $this->mainTpl->addJavaScript('assets/js/socket.io.min.js');
            $this->mainTpl->addJavaScript('assets/js/Chatroom.min.js');
            $roomTpl->setVariable('CHAT', (new ilChatroomViewGUI($this->gui))->readOnlyChatWindow($room, array_column($messages, 'message'))->get());
        } else {
            $roomTpl->setVariable('CHAT', '');
        }
        $this->mainTpl->setVariable('ADM_CONTENT', $roomTpl->get());
    }

    public function executeDefault(string $requestedMethod): void
    {
        $this->byDay();
    }
}
