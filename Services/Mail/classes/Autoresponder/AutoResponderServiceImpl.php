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

namespace ILIAS\Mail\Autoresponder;

use DateInterval;
use ilMailOptions;
use DateTimeZone;
use DateTimeImmutable;

final class AutoresponderServiceImpl implements AutoresponderService
{
    /** @var bool $auto_responder_status */
    private $auto_responder_status;
    /** @var AutoresponderRepository $auto_responder_repository */
    private $auto_responder_repository;
    /** @var callable $mail_action */
    private $mail_action;
    /** @var DateInterval $idle_time_interval */
    private $idle_time_interval;

    /** @var ilMailOptions[] $auto_responder_data */
    protected $auto_responder_data = [];

    public function __construct(
        int $global_idle_time_interval,
        bool $initial_auto_responder_status,
        AutoresponderRepository $auto_responder_repository,
        ?callable $mail_action = null
    ) {
        $this->auto_responder_status = $initial_auto_responder_status;
        $this->auto_responder_repository = $auto_responder_repository;
        $this->mail_action = $mail_action;

        $this->idle_time_interval = new DateInterval('P' . $global_idle_time_interval . 'D');
    }

    public function isAutoresponderEnabled() : bool
    {
        return $this->auto_responder_status;
    }

    public function enableAutoresponder() : void
    {
        $this->auto_responder_status = true;
    }

    public function disableAutoresponder() : void
    {
        $this->auto_responder_status = false;
    }

    public function handleAutoresponderMails(int $auto_responder_receiver_usr_id) : void
    {
        if ($this->auto_responder_data === []) {
            return;
        }

        foreach ($this->auto_responder_data as $auto_responder_sender_usr_id => $mail_options) {
            if ($this->auto_responder_repository->exists(
                $auto_responder_sender_usr_id,
                $auto_responder_receiver_usr_id
            )) {
                $auto_responder = $this->auto_responder_repository->findBySenderIdAndReceiverId(
                    $auto_responder_sender_usr_id,
                    $auto_responder_receiver_usr_id
                );
            } else {
                $auto_responder = new AutoresponderDto(
                    $auto_responder_sender_usr_id,
                    $auto_responder_receiver_usr_id,
                    $this->normalizeDateTimezone(new DateTimeImmutable('NOW', new DateTimeZone('UTC')))
                         ->sub($this->idle_time_interval)
                         ->modify('-1 second')
                );
            }

            if ($this->shouldSendAutoresponder($auto_responder)) {
                $auto_responder = $auto_responder->withSentTime(
                    $this->normalizeDateTimezone(new DateTimeImmutable('NOW', new DateTimeZone('UTC')))
                );

                if ($this->mail_action !== null) {
                    ($this->mail_action)(
                        $auto_responder_sender_usr_id,
                        $mail_options,
                        $auto_responder->getSentTime()->add($this->idle_time_interval)
                    );
                } else {
                    $mail = new AutoresponderNotification(
                        $mail_options,
                        $auto_responder_receiver_usr_id,
                        $auto_responder->getSentTime()->add($this->idle_time_interval)
                    );
                    $mail->send();
                }

                $this->auto_responder_repository->store($auto_responder);
            }
        }
    }

    private function normalizeDateTimezone(DateTimeImmutable $date_time) : DateTimeImmutable
    {
        return $date_time->setTimezone(new DateTimeZone('UTC'));
    }

    private function shouldSendAutoresponder(AutoresponderDto $auto_responder) : bool
    {
        // Normalize timezones
        $last_send_time_with_added_interval = $this
            ->normalizeDateTimezone($auto_responder->getSentTime())
            ->add($this->idle_time_interval);

        $now = $this->normalizeDateTimezone(new DateTimeImmutable('NOW', new DateTimeZone('UTC')));

        // Don't compare the objects because of microseconds
        return $last_send_time_with_added_interval->format('Y-m-d H:i:s') <= $now->format('Y-m-d H:i:s');
    }

    public function enqueueAutoresponderIfEnabled(
        int $sender_id,
        ilMailOptions $mail_receiver_options,
        ilMailOptions $mail_sender_options
    ) : void {
        if ($this->auto_responder_status && $mail_receiver_options->isAbsent()) {
            $this->auto_responder_data[$sender_id] = $mail_receiver_options;
        }
    }

    public function emptyAutoresponderData() : void
    {
        $this->auto_responder_data = [];
    }
}
