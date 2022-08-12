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

namespace ILIAS\Mail\AutoResponder;

use DateInterval;
use ilFormatMail;
use ilMailOptions;
use ILIAS\Data\Clock\ClockInterface;
use DateTimeZone;
use DateTimeImmutable;

final class AutoResponderServiceImpl implements AutoResponderService
{
    /** @var callable  */
    private $loginByUsrIdCallable;
    private bool $auto_responder_status;
    private AutoResponderRepository $auto_responder_repository;
    private ClockInterface $clock;
    private int $global_idle_time_interval;
    /** @var callable */
    private $mail_action;
    
    /** @var ilMailOptions[] $auto_responder_data */
    protected array $auto_responder_data = [];

    public function __construct(
        callable $loginByUsrIdCallable,
        int $global_idle_time_interval,
        bool $initial_auto_responder_status,
        AutoResponderRepository $auto_responder_repository,
        ClockInterface $clock,
        ?callable $mail_action = null
    ) {
        $this->loginByUsrIdCallable = $loginByUsrIdCallable;
        $this->global_idle_time_interval = $global_idle_time_interval;
        $this->auto_responder_status = $initial_auto_responder_status;
        $this->auto_responder_repository = $auto_responder_repository;
        $this->clock = $clock;
        $this->mail_action = $mail_action;
    }

    private function normalizeDateTimezone(DateTimeImmutable $date_time) : DateTimeImmutable
    {
        return $date_time->setTimezone(new DateTimeZone('UTC'));
    }

    private function shouldSendAutoResponder(AutoResponder $auto_responder) : bool
    {
        // Normalize timezones
        $last_send_time_with_added_interval = $this
            ->normalizeDateTimezone($auto_responder->getSentTime())
            ->add(new DateInterval('P' . $this->global_idle_time_interval . 'D'));

        $now = $this->normalizeDateTimezone($this->clock->now());

        // Don't compare the objects because of microseconds
        return $last_send_time_with_added_interval->format('Y-m-d H:i:s') <= $now->format('Y-m-d H:i:s');
    }

    public function isAutoResponderEnabled() : bool
    {
        return $this->auto_responder_status;
    }

    public function enableAutoResponder() : void
    {
        $this->auto_responder_status = true;
    }

    public function disableAutoResponder() : void
    {
        $this->auto_responder_status = false;
    }

    public function handleAutoResponderMails(int $auto_responder_receiver_usr_id) : void
    {
        if ($this->auto_responder_data === []) {
            return;
        }

        foreach ($this->auto_responder_data as $auto_responder_sender_usr_id => $mail_options) {
            if ($this->auto_responder_repository->exists($auto_responder_sender_usr_id, $auto_responder_receiver_usr_id)) {
                $auto_responder = $this->auto_responder_repository->findBySenderIdAndReceiverId(
                    $auto_responder_sender_usr_id,
                    $auto_responder_receiver_usr_id
                );
            } else {
                $auto_responder = new AutoResponder(
                    $auto_responder_sender_usr_id,
                    $auto_responder_receiver_usr_id,
                    $this->normalizeDateTimezone($this->clock->now())
                         ->sub(new DateInterval('P' . $this->global_idle_time_interval . 'D'))->modify('-1 second')
                );
            }

            if ($this->shouldSendAutoResponder($auto_responder)) {
                $subject = $mail_options->getAbsenceAutoResponderSubject();
                $message = $mail_options->getAbsenceAutoResponderBody() . chr(13) . chr(10) . $mail_options->getSignature();
                $recipient = ($this->loginByUsrIdCallable)($auto_responder_receiver_usr_id);

                if ($this->mail_action !== null) {
                    ($this->mail_action)(
                        $auto_responder_sender_usr_id,
                        $auto_responder_receiver_usr_id,
                        $recipient,
                        $subject,
                        $message
                    );
                } else {
                    $mail = new ilFormatMail($auto_responder_sender_usr_id);
                    $mail->setSaveInSentbox(false);
                    $mail->sendMail(
                        $recipient,
                        '',
                        '',
                        $subject,
                        $message,
                        [],
                        false
                    );
                }

                $this->auto_responder_repository->store(
                    $auto_responder->withSentTime($this->clock->now())
                );
            }
        }
    }

    public function enqueueAutoResponderIfEnabled(ilMailOptions $mail_recipient_mail_options) : void
    {
        if ($this->auto_responder_status && $mail_recipient_mail_options->isAbsent()) {
            $this->auto_responder_data[$mail_recipient_mail_options->getUsrId()] = $mail_recipient_mail_options;
        }
    }

    public function emptyAutoResponderData() : void
    {
        $this->auto_responder_data = [];
    }
}
